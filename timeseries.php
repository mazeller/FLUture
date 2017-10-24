<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/dataloader.js","/js/jQDateRangeSlider-withRuler-min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/c3.min.css');
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->addStyle('{{asset_path}}/css/iThing-min.css');
$theme->drawHeader();
?>
<h2 id="chartTitle"></h2>
<div id="chart" style="height:500px"></div>
<div id="slider"></div>
	<div>
                        <fieldset>
                            <legend>Options</legend>
                            <!-- Start Date: <input type="text" id="dateStart">
                                End Date: <input type="text" id="dateEnd"> -->
                                    <br>
                                    <b>Granularity</b><br>
                                    <select id="axisx">
   					<option value="day">Day</option> 
					<option value="week">Week</option> 
                                        <option value="month" selected="selected">Month</option>
                                       <option value="year">Year</option>
                                    </select>
                                    <br>
                                    <b>Y Axis</b><br>
                                    <select id="axisy">
                                        <option value="age_days">Age</option>
					<!-- <option value="cultureResult">Coinfection</option> -->
                                        <option value="testing_facility">Data Source</option>
                                        <option value="ha_clade">HA Clade</option>
					<option value="h1_clade">H1 Clade</option>
					<option value="h3_clade">H3 Clade</option>
                                        <option value="na_clade">NA Clade</option>
                                        <option value="sequence_specimen">Sequence Specimen</option>
                                        <option value="month">Month</option>
					<option value="pcr_specimen">PCR Specimen</option>
                                        <option value="site_state">Pig State</option>
                                        <option value="subtype" selected="selected">Subtype</option>
					<option value="week">Week</option>
                                        <option value="year">Year</option>
                                    </select><br>
				    <strong>Display Options</strong><br>
				    <input type="checkbox" id="normalize" value="normalize">Account by Proportion<br>
				    <a href="javascript:;" id="grabData">Download Graph Data</a>
                                    </fieldset>
                    </div>
<script>
//Global access to data
var data;

//Page load
$(document).ready(function() {
	$("#axisx").change(parse);
	$("#axisy").change(parse);
	$("#stack").change(parse);
	$("#normalize").change(parse);
	$("#area").change(parse);
	$("#grabData").click(grabData);

        //Make Slider
        $("#slider").dateRangeSlider({
                bounds:{
                        min: new Date(2003, 0, 1),
                        max: new Date()
                },
                defaultValues:{
                        min: new Date(2003, 0, 1),
                        max: new Date()
                }
        });

        //Bind the date change
        $("#slider").on("valuesChanging", parse);

       //Load in data one time
	requestData(); 
});

//Download Data Summaries
function grabData() {
    //Convert JSON to CSV format (https://stackoverflow.com/questions/11257062/converting-json-object-to-csv-format-in-javascript)
    var graphCSV = JSON.stringify(graphData);
    graphCSV = ConvertToCSV(graphCSV);
    var text = xAxis.toString() + "\n" + graphCSV;
    download("data.csv",text);
}

//Pull out data specific to Type xData State
function requestData() {
    var xComponent = "ha_clade";
    var yComponent = ["na_clade","H1","H3","N1","N2","received_date","age_days","site_state","testing_facility","sequence_specimen","pcr_specimen"];
        
    getJsonData(xComponent, yComponent, parse, flags="");
}

var data = {};

//Pull out data specific to Type xData State
function parse(rdata) {
    //Store data so only hit db once
    if(rdata.constructor.name != 'Array')
            rdata = data;
    data = rdata;

    var xComponent = "received_date";
    var granularity = $("#axisx").val();
    var yComponent = $("#axisy").val();
    var normalize = $("#normalize").is(":checked");

    //Reset Axis
    xAxis = [];
    var groups = [];

    //Create primary structure
    var flu = {};
    var skipList = ["","-1","USA", undefined];
    
    for (var key in rdata) {
	//Skip certain subsets
	if(skipList.indexOf(rdata[key][xComponent]) != -1 || skipList.indexOf(rdata[key][yComponent]) != -1)
		continue;

	//Month, clip day
	useDate = rdata[key][xComponent]
	caseDate = new Date(useDate);

	//Skip if dates outside range
	var sliderBounds = $("#slider").dateRangeSlider("values");
	if(caseDate < sliderBounds.min) continue;
        if(caseDate > sliderBounds.max) continue;

	if(granularity == "week") {
		caseDate.setDate(caseDate.getDate() - caseDate.getDay());
		useDate = caseDate.getFullYear() + "-" + (caseDate.getMonth() + 1) + "-" + caseDate.getDate();		
	}
	if(granularity == "month")
		useDate = caseDate.getFullYear() + "-" + (caseDate.getMonth() + 1) + "-" + "01";
	if(granularity == "year")
		useDate = caseDate.getFullYear() + "-" + "01" + "-" + "01";


	//Make sure x axis exists
	if (!flu.hasOwnProperty(rdata[key][yComponent])){
		flu[rdata[key][yComponent]] = {};
		groups.push(rdata[key][yComponent]);
	}		
	//Make sure y axis exists
        if (!flu[rdata[key][yComponent]].hasOwnProperty(useDate)){
                flu[rdata[key][yComponent]][useDate] = 0;
		//If unique, add to x axis
		if(xAxis.indexOf(useDate) == -1)
	                xAxis.push(useDate);
        }
	flu[rdata[key][yComponent]][useDate]++;
    }

    //Collapse the structure into data for c3 charts
    graphData = [];
    for (var key in flu) {
        tempData = [];
        if (flu.hasOwnProperty(key)) {
            tempData.push(key);
            var obj = flu[key];

            for (var i in xAxis) {
                if (obj[xAxis[i]] != null)
                    tempData.push(obj[xAxis[i]]);
                else
                    tempData.push(0);
	    	}
        }
        graphData.push(tempData);
    }

    //Handle normalization math
    if (normalize == true) {
	subsets = graphData.length;
	for (value in graphData[0]) {
		//Skip first
		if( value == 0)
			continue;
		
		//Find max
		total = 0;
		for (i = 0; i < subsets; i++)
		{
			total += graphData[i][value];
		}
		
		//Regenerate numbers as percents
                for (i = 0; i < subsets; i++)
                {
                        graphData[i][value] = (graphData[i][value] / total).toFixed(3);
                }
	}
    }

    //Graph it
    graphFlu(graphData, xAxis, groups, xComponent, yComponent);
}

//Draw our data
function graphFlu(data, xAxis, groups, xComponent, yComponent) {
    //Merge xAxis to data
    xAxis.unshift("x");
    var temp = data.slice();
    temp.unshift(xAxis);
    data = temp;

    //Check if normalized for text
    var normalize = $("#normalize").is(":checked");
    var typeData = {};
    if (normalize == true)
    {
	xAxisText = " Percent";
	for (tag in groups) {
	   typeData[groups[tag]] = 'area'; 
	}
    }
    else
    {
	xAxisText = " Count";
	groups = [];
    }

    //Sorting
    if (yComponent == "h3_clade" || yComponent == "na_clade" || yComponent == "sequence_specimen" || yComponent == "site_state" || yComponent == "subtype"   || yComponent == "pcr_specimen")
            data.sort();
    if (yComponent == "age_days")
        data.sort(sortAge);
    if (yComponent == "h1_clade" || yComponent == "h3_clade" || yComponent == "ha_clade")
	data.sort(sortClade);
    //Generate Chart
    var chart = c3.generate({
        data: {
	    x: 'x',
            columns: data,
	    types: typeData,
	    groups: [groups],
        },
	axis: {
                x: {
                    type: 'timeseries',
                    tick: {
                        format: '%Y-%m-%d',
                        rotate: 60,
                        fit: true
                    },
                    label: {
                        text: "Time" ,
                        position: 'outer-center',
                    },
                },
		y: {
                    label: {
                       	text: translateLabel(yComponent) + xAxisText,
                       	position: 'middle',
                    },
            	},
            }
    });

    //Update Title
    $("#chartTitle").text(translateLabel(yComponent) + " over Time");
}

//Make the data labels more readable
function translateLabel(label)
{
	var transLabel = label;
	if(label == "age_days")
		transLabel = "Age Group";
	if(label == "day")
		transLabel = "Day of Year";
	if(label == "ha_clade")
		transLabel = "HA Clade Frequency of Detection";
	if(label == "h1_clade")
		transLabel = "H1 Clade Frequency of Detection";
	if(label == "h3_clade")
		transLabel = "H3 Clade Frequency of Detection";
	if(label == "na_clade")
		transLabel = "NA Clade Frequency of Detection";
	if(label == "pcr_specimen")
		transLabel = "Specimen used for PCR";
	if(label == "sequence_specimen")
		transLabel = "Specimen used for Sequencing";
	if(label == "site_state")
		transLabel = "Pig's Origin State";
	if(label == "subtype")
		transLabel = "Subtype";
	if(label == "testing_facility")
		transLabel = "Source of Data";
	
	return transLabel; 
}

//Numerical sorting
function sortNumber(a,b) {
    return a - b;
}

//Age sorting
function sortAge(a,b) {
        aVal = ageToNumber(a[0]);
        bVal = ageToNumber(b[0]);
        return aVal - bVal;
}

//H1 clade sort
function sortClade(a,b) {
        aVal = cladeToNumber(a[0]);
        bVal = cladeToNumber(b[0]);
        return aVal - bVal;
}

function cladeToNumber(cladeString) {
        clade = -1;
        if (cladeString == "alpha")
                clade = 0;
        if (cladeString == "beta")
                clade = 1;
        if (cladeString == "gamma")
                clade = 2;
        if (cladeString == "gamma2")
                clade = 3;
        if (cladeString == "gamma-like")
                clade = 4;
        if (cladeString == "gamma2-beta-like")
                clade = 5;
        if (cladeString == "delta1")
                clade = 6;
        if (cladeString == "delta1a")
                clade = 7;
        if (cladeString == "delta1b")
                clade = 8;
        if (cladeString == "delta2")
                clade = 9;
        if (cladeString == "delta-like")
                clade = 10;
        if (cladeString == "pdmH1")
                clade = 11;
        if (cladeString == "cluster_IV")
                clade = 12;
        if (cladeString == "cluster_IVA")
                clade = 13;
        if (cladeString == "cluster_IVB")
                clade = 14;
        if (cladeString == "cluster_IVC")
                clade = 15;
        if (cladeString == "cluster_IVD")
                clade = 16;
        if (cladeString == "cluster_IVE")
                clade = 17;
        if (cladeString == "cluster_IVF")
                clade = 18;
        if (cladeString == "2010-human-like")
                clade = 19;
	if (cladeString == "2016-human-like")
		clade = 20;
        return clade;
}

function ageToNumber(ageString) {
        age = -1;
        if (ageString == "neonate")
                age = 0;
        if (ageString == "suckling")
                age = 1;
        if (ageString == "nursery")
                age = 2;
        if (ageString == "grow finisher")
                age = 3;
        if (ageString == "adult")
                age = 4;
	return age;
}

//Find unique values
function uniqueValues(dataObject, field)
{
	var result = [];
	for (var key in dataObject) {
		if (result.indexOf(dataObject[key][field]) == -1) {
			result.push(dataObject[key][field]);
		}
        }	
	return result;
}
</script>
<?php
$theme->drawFooter();
