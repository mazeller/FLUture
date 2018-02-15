<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/dataloader.js","/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/c3.min.css');
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->drawHeader();
?>
<h2 id="chartTitle"></h2>
<div id="chart" style="height:500px"></div>
	<div>
                        <fieldset>
                            <legend>Options</legend>
                            <!-- Start Date: <input type="text" id="dateStart">
                                End Date: <input type="text" id="dateEnd"> -->
                                    <br>
                                    <b>X Axis</b><br>
                                    <select id="axisx">
                                        <option value="age_days">Age</option>
   					<option value="day">Day of Year</option>
					<option value="testing_facility">Data Source</option> 
                                        <!-- <option value="cultureResult">Coinfection</option> -->
					<option value="ha_clade">HA Clade</option>
					<option value="h1_clade">H1 Clade</option>
					<option value="h3_clade">H3 Clade</option>
					<option value="na_clade">NA Clade</option>
					<option value="sequence_specimen">Sequence Specimen</option>
                                        <option value="month">Month</option>
					<option value="pcr_specimen">PCR Specimen</option>
                                        <option value="site_state">Pig State</option>
                                        <option value="subtype">Subtype</option>
					<option value="week">Week</option>
					<option value="weight_pounds">Weight</option>
                                        <option value="year" selected="selected">Year</option>
                                    </select>
                                    <br>
                                    <b>Y Axis</b><br>
                                    <select id="axisy">
                                        <option value="age_days">Age</option>
                                        <option value="day">Day of Year</option>
                                        <option value="testing_facility">Data Source</option>
					<!-- <option value="cultureResult">Coinfection</option> -->
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
					<option value="weight_pounds">Weight</option>
                                        <option value="year">Year</option>
                                    </select><br>
                                    <strong>Display Options</strong><br>
                                    <input type="checkbox" id="stack" value="stack">Stack columns<br>
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
	$("#grabData").click(grabData);
	$("#normalize").change(parse);

	$('#dateEnd').datepicker({
        dateFormat: 'yy-mm-dd',
        defaultDate: new Date(),
        onSelect: parse
    });
    $('#dateStart').datepicker({
        dateFormat: 'yy-mm-dd',
        defaultDate: new Date(2002, 01, 01),
        onSelect: parse
    }); 
       //Load in data one time
	requestData();        
});

//Download Data Summaries
function grabData() {
    //Convert JSON to CSV format (https://stackoverflow.com/questions/11257062/converting-json-object-to-csv-format-in-javascript)
    var graphCSV = JSON.stringify(graphData);
    graphCSV = ConvertToCSV(graphCSV);
    var text = "," + xAxis.toString() + "\n" + graphCSV;
    download("data.csv",text);
}

//Pull out data specific to Type xData State
function requestData() {
    var xComponent = "ha_clade";
    var yComponent = ["na_clade","H1","H3","N1","N2","received_date","age_days","weight_pounds","site_state","testing_facility","sequence_specimen","pcr_specimen"];
        
    getJsonData(xComponent, yComponent, parse, flags="");
}

var data = {};

//Pull out data specific to Type xData State
function parse(rdata) {
    //Store data so only hit db once
    if(rdata.constructor.name != 'Array')
            rdata = data;
    data = rdata;

    var xComponent = $("#axisx").val();
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

	//Make sure x axis exists
	if (!flu.hasOwnProperty(rdata[key][yComponent])){
		flu[rdata[key][yComponent]] = {};
		groups.push(rdata[key][yComponent]);
	}		
	//Make sure y axis exists
        if (!flu[rdata[key][yComponent]].hasOwnProperty(rdata[key][xComponent])){
                flu[rdata[key][yComponent]][rdata[key][xComponent]] = 0;
		//If unique, add to x axis
		if(xAxis.indexOf(rdata[key][xComponent]) == -1)
	                xAxis.push(rdata[key][xComponent]);
        }
	flu[rdata[key][yComponent]][rdata[key][xComponent]]++;
    }

    //Correctly sort per data type (numerical | lexigraphical | colloquial )
    if (xComponent == "day" || xComponent == "month" || xComponent == "week" || xComponent == "year")
	    xAxis.sort(sortNumber);
    if (xComponent == "h3_clade" || xComponent == "na_clade" || xComponent == "sequence_specimen" || xComponent == "site_state" || xComponent == "subtype" || xComponent == "pcr_specimen")
	    xAxis.sort();
    if (xComponent == "age_days")
	    xAxis.sort(sortAge);
    if (xComponent == "weight_pounds")
	    xAxis.sort(sortNumber);
    if (xComponent == "h1_clade" || xComponent == "h3_clade" || xComponent == "ha_clade")
	    xAxis.sort(sortClade);

    //Turn off groups if unchecked
    if (normalize == true) {
        //Collapse the structure into data for c3 charts
        graphData = [];
        xScore = [];
        for (var key in flu) {
            var obj = flu[key];

            if (flu.hasOwnProperty(key)) {
                //Gather count datafor normalization
                for (var i in xAxis) {
                    if (obj[xAxis[i]] != null) {
                        if (xScore[xAxis[i]] == null)
                            xScore[xAxis[i]] = 0;
                        xScore[xAxis[i]] = xScore[xAxis[i]] + obj[xAxis[i]];
                    }
                }
            }
        }
        for (var key in flu) {
            tempData = [];
            if (flu.hasOwnProperty(key)) {
                tempData.push(key);
                var obj = flu[key];

                for (var i in xAxis) {
                    if (obj[xAxis[i]] != null)
                        tempData.push((obj[xAxis[i]] / xScore[xAxis[i]]).toFixed(3));
                    else
                        tempData.push(null);
                }
            }
            graphData.push(tempData);
        }
    } else {
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
                        tempData.push(null);
            	}
            }
            graphData.push(tempData);
        }
   }
    //Graph it
    graphFlu(graphData, xAxis, groups, xComponent, yComponent);
}

//Draw our data
function graphFlu(data, xAxis, groups, xComponent, yComponent) {
    var stack = $("#stack").is(":checked");
    var normalize = $("#normalize").is(":checked");
    if (normalize == true)
        xAxisText = " Percent";
    else
        xAxisText = " Count";

    //turn off groups if unchecked
    if (stack == false)
        groups = [];

    var chart = c3.generate({
        data: {
            columns: data,
            type: 'bar',
            groups: [groups]
        },
        axis: {
            x: {
                type: 'category',
                categories: xAxis,
		label: {
			text: translateLabel(xComponent),
                        position: 'outer-center',
		},
            },
	    y: {
		label: {
			text: translateLabel(yComponent) + xAxisText,
			position: 'middle',
		}
	    }
        },
        grid: {
            y: {
                lines: [{
                    value: 0
                }]
            }
        }
    });

    //Update Title
    $("#chartTitle").text(translateLabel(yComponent) + " per " + translateLabel(xComponent));
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
		transLabel = "HA Clade Frequency Detection";
	if(label == "h1_clade")
		transLabel = "H1 Clade Frequency Detection";
	if(label == "h3_clade")
		transLabel = "H3 Clade Frequency Detection";
	if(label == "na_clade")
		transLabel = "NA Clade Frequency Detection";
	if(label == "pcr_specimen")
		transLabel = "Specimen used for PCR";
	if(label == "sequence_specimen")
		transLabel = "Specimen used for Sequencing";
	if(label == "site_state")
		transLabel = "Pig's Origin State";
	if(label == "subtype")
		transLabel = "Subtype";
	if(label == "testing_facility")
		transLabel = "Data Source";
	if(label == "weight_pounds")
		transLabel = "Weight";
	return transLabel; 
}

//Numerical sorting
function sortNumber(a,b) {
    return a - b;
}

//Age sorting
function sortAge(a,b) {
        aVal = ageToNumber(a);
        bVal = ageToNumber(b);
        return aVal - bVal;
}

//H1 clade sort
function sortClade(a,b) {
        aVal = cladeToNumber(a);
        bVal = cladeToNumber(b);
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

function weightToBin(weight) {
	binnedWeight = Math.floor(weight/5) * 5;
	return binnedWeight;
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
