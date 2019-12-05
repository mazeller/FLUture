//http://stackoverflow.com/questions/1144783/replacing-all-occurrences-of-a-string-in-javascript
String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

//Get Clades
function getCladeOrder(callback) {
        $.ajax({
                url: '/getdata.php',
                type: 'post',
                dataType: 'json',
                data: {'col': "orders"},
                success: function(data, status) {
                        callback(data);
                },
                error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                }
        });
}

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
	var fields;
	if(yComponent.constructor === Array) {
		fields = yComponent.join(",");
	} else {
		fields = yComponent;
	}
	
	//Add in xComponent
	fields = xComponent + "," + fields;

	//Change values
	fields = fields.replaceAll("barcode","accession_id");
	fields = fields.replaceAll("received_date","received_date,day,week,month,year");
	fields = fields.replaceAll("ha_clade","ha_clade,h1_clade,h3_clade,subtype");

	
	//Request data
	$.ajax({
		url: '/getdata.php',
		type: 'post',
		data: {'col': fields,'flags':flags},
		startTime: performance.now(),
 		success: function(data, status) {
			data = JSON.parse(data);
			console.log("Before preprocessing: " + (performance.now()-this.startTime));
                        preProcess(xComponent, yComponent, data, callback);
			console.log("After preprocessing: " + (performance.now()-this.startTime));
		},
 		error: function(xhr, desc, err) {
 			console.log(xhr);
 			console.log("Details: " + desc + "\nError:" + err);
 		}
	});
}

function preProcess(xComponent, yComponent, data, callback, flags = "") {
        //Init array
        var flu = {};
        var xAxis = [];
        var groups = [];
	var yData;
	var xData;

        if(yComponent.constructor === Array) {
		for (var i = 0; i < data.length; i++) {
			for (var j = 0; j < yComponent.length; j++)
			{
                        	//Shorten Variables
                        	yData = data[i][yComponent[j]];
	                        xData = data[i][xComponent];

        	                //Throw out unlabeled
                	        if (yData == null || yData == '')
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

                        	//Keep track of the x axis values & groups. Is this redundant?
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
	           	if (yData == null || yData == '')
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
                for (var index = 0; index < array[i].length; index ++) {
                	if (line != '') 
				line += ',';
				line += array[i][index];
       		}
        	str += line + '\r\n';
        }
        return str;
}

