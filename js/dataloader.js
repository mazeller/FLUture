//http://stackoverflow.com/questions/1144783/replacing-all-occurrences-of-a-string-in-javascript
String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

//Get LR
function getLastRecord(callback){
	//Request Data
        $.ajax({
                url: '/getdata.php',
                type: 'post',
                data: {'col': "lastrecord"},
                success: function(data, status) {
                        data = JSON.parse(data);
                        callback(data[0]['received_date']);
                },
                error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                }
        });
}

//Get acc
function getAccessions(callback){
	//Request Data
        $.ajax({
                url: '/getdata.php',
                type: 'post',
                data: {'col': "accessions"},
                success: function(data, status) {
                        callback(data);
                },
                error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                }
        });
}

//Query for the data
function getJsonData(xComponent, yComponent, callback) {
	//Check if yComponents is array
	if(yComponent.constructor === Array) {
		fields = yComponent.join(",");
	} else {
		fields = yComponent;
	}
	
	//Add in xComponent
	fields = xComponent + "," + fields;

	//Change values
	fields = fields.replaceAll("Day","received_date");
	fields = fields.replaceAll("Month","received_date");
 	fields = fields.replaceAll("Year","received_date");
	fields = fields.replaceAll("WeekNum","received_date");
	fields = fields.replaceAll("H1","H1,H3,N1,N2");
	fields = fields.replaceAll("barcode","accession_id");	
	//Request data
	$.ajax({
		url: '/getdata.php',
		type: 'post',
		data: {'col': fields,'flags':flags},
 		success: function(data, status) {
			data = JSON.parse(data);
			preProcess(xComponent, yComponent, data, callback);
		},
 		error: function(xhr, desc, err) {
 			console.log(xhr);
 			console.log("Details: " + desc + "\nError:" + err);
 		}
	});
}

function preProcess(xComponent, yComponent, data, callback, flags = "") {
        //Init array
        flu = {};
        xAxis = [];
        groups = [];

        if(yComponent.constructor === Array) {
		for (var i = 0; i < data.length; i++) {
			for (var j = 0; j < yComponent.length; j++)
			{
                        	//Shorten Variables
                        	yData = data[i][yComponent[j]];
	                        xData = data[i][xComponent];

        	                //Throw out unlabeled
                	        if (yData == null | yData == '')
                        	        continue;

				//Special case, handle age_days
				if (yComponent[j] == "age_days") //Special case age
				{	
			                age = yData;
			                if (age > 0 & age < 5)
			                    yData = "neonate";
			                if (age >= 5 & age < 22)
			                    yData = "suckling";
			                if (age >= 22 & age < 92)
			                    yData = "nursery";
			                if (age >= 92 & age < 240)
			                    yData = "grow finisher";
			                if (age >= 240)
			                    yData = "adult";
					data[i][yComponent[j]] = yData;
            			}
				// Special case, handle weight_pounds. Author: Siying Lyu
				if (yComponent[j] == "weight_pounds")
				{
					weight = yData;
                                        if (weight > 0 & weight < 50)
                                            yData = "Under\t50";
                                        if (weight >= 50 & weight < 100)
                                            yData = "50\-100";
                                        if (weight >= 100 & weight < 150)
                                            yData = "100\-150";
                                        if (weight >= 150 & weight < 200)
                                            yData = "150\-200";
                                        if (weight >= 200 & weight < 250)
                                            yData = "200\-250";
                                        if (weight >= 250 & weight < 300)
                                            yData = "250\-300";
                                        if (weight >= 300 & weight < 350)
                                            yData = "300\-350";
                                        if (weight >= 350 & weight < 400)
                                            yData = "350\-400";
                                        if (weight >= 400 & weight < 450)
                                            yData = "400\-450";
                                        if (weight >= 450 & weight < 500)
                                            yData = "450\-500";
                                        if (weight >= 500)
                                            yData = "Above\t500";
					data[i][yComponent[j]] = yData;
				}

				//Process Dates
				if (yComponent[j] == "received_date")
				{
					specimenDate = new Date(yData);
					data[i]['year'] = specimenDate.getFullYear();
					data[i]['month'] = (specimenDate.getMonth() + 1);
					
					//Figure out day of year (ignore feb 29th)
					var yearStart = new Date(specimenDate.getFullYear(),0,1);
					data[i]['day'] = Math.ceil((specimenDate - yearStart) / 86400000);
					data[i]['week'] = Math.ceil((specimenDate - yearStart) / 86400000 / 7);
				}

				//Create Subtype Information
				if (yComponent[j] == "H1" || yComponent[j] == "H3")
				{
					subtype = "";
					if (parseInt(data[i]["H1"]) == 1)
						subtype += "H1";
					if (parseInt(data[i]["H3"]) == 1)
						subtype += "H3";
					if (parseInt(data[i]["N1"]) == 1)
						subtype += "N1";
					if (parseInt(data[i]["N2"]) == 1)
						subtype += "N2";
					if (subtype.length != 4)
						subtype = "";
					data[i]['subtype'] = subtype;
				}

				//Create clade information
				if (xComponent == "ha_clade")
				{
					//Define Clades
					var clade = xData;
					var h1clade = ['alpha','beta','gamma','gamma2','gamma2-beta-like','gamma-like','gamma-pdm-like','delta2','delta1a','delta1','delta1b','delta-like','pdmH1'];
					var h3clade = ['cluster_I','cluster_IV','cluster_IVA','cluster_IVB','cluster_IVE','cluster_IVF','cluster_IVD','cluster_IVC','2010.1','2010.2','human-to-swine-2016','human-to-swine-2017','human-to-swine-2018'];
						
					if (h1clade.indexOf(clade) != -1)
						data[i]['h1_clade'] = clade;
					if (h3clade.indexOf(clade) != -1)
						data[i]['h3_clade'] = clade;	
				}

	                        //Create a complex structure
        	                if (flu[yData] == null) {
                	                flu[yData] = [];
	                        }

        	                if (flu[yData][xData] == null) {
                	                flu[yData][xData] = 0;
	                        } else {
        	                        flu[yData][xData] = flu[yData][xData] + 1;
                	        }

                        	//Keep track of the x axis values & groups
	                        if (xAxis.indexOf(data[i][xComponent]) < 0)
        	                        xAxis.push(data[i][xComponent]);
                	        if (groups.indexOf(data[i][yComponent[j]]) < 0)
                        	        groups.push(data[i][yComponent[j]]);
			}
		}
	}
        else {
        	for (var i = 0; i < data.length; i++) {

			//Shorten Variables
			yData = data[i][yComponent];
			xData = data[i][xComponent];

			//Throw out unlabeled
	           	if (yData == null | yData == '')
        	        	continue;

            		//Create a complex structure
	            	if (flu[yData] == null) {
        	        	flu[yData] = [];
	            	}	

			if (flu[yData][xData] == null) {
                		flu[yData][xData] = 0;
	            	} else {
        	        	flu[yData][xData] = flu[yData][xData] + 1;
            		}

			//Keep track of the x axis values & groups
        		if (xAxis.indexOf(data[i][xComponent]) < 0)
        			xAxis.push(data[i][xComponent]);
		        if (groups.indexOf(data[i][yComponent]) < 0)
        		        groups.push(data[i][yComponent]);
		}
	}
	
	//Check structure
	callback(data);
}

//Place holder function for time being
function dateProcess(xData)
{
	//for dates, cull by year
	return xData.substr(0,4);
}

//Write out files to user (stackoverflow)
function download(filename, text) {
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
        element.setAttribute('download', filename);

        element.style.display = 'none';
        document.body.appendChild(element);

        element.click();

        document.body.removeChild(element);
}

// JSON to CSV Converter
function ConvertToCSV(objArray) {
	var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
        var str = '';

        for (var i = 0; i < array.length; i++) {
        	var line = '';
                for (var index in array[i]) {
                	if (line != '') line += ','
			line += array[i][index];
       		}
        	str += line + '\r\n';
        }
        return str;
}

