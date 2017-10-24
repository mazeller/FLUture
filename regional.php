<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/dataloader.js","js/raphael.js","js/jquery.usmap.js","/js/jQDateRangeSlider-withRuler-min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/c3.min.css');
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->addStyle('{{asset_path}}/css/iThing-min.css');

$theme->addStyle(<<<CSS
.legend i {
    width: 18px;
    height: 18px;
    float: left;
    margin-right: 8px;
}
CSS
, 'style');
$theme->drawHeader();
?>
<h2 id="chartTitle"></h2>
<div style="float: right; position:absolute;z-index:10;background:#FFFFFF;opacity:0.7;" class="legend">
	<i style="background:#FFEDA0"></i><b id="p1"> &gt; 0%</b><br>
	<i style="background:#FED976"></i><b id="p2"> &gt; 14%</b><br>
	<i style="background:#FEB24C"></i><b id="p3"> &gt; 28%</b><br>
	<i style="background:#FD8D3C"></i><b id="p4"> &gt; 42%</b><br>
	<i style="background:#FC4E2A"></i><b id="p5"> &gt; 56%</b><br>
	<i style="background:#E31A1C"></i><b id="p6"> &gt; 71%</b><br>
	<i style="background:#BD0026"></i><b id="p7"> &gt; 85%</b><br>
	<i style="background:#800026"></i><b id="p8"> &gt; 99%</b>
</div>
<div id="map" style="height: 600px;"></div>
<div id="slider"></div>
<div><a href="javascript:;" id="grabData">Download Graph Data</a></div>
<div id="dataTable"></div>
<!-- Leaflet legend, hardcoded because lazy -->

<script>
//Global access to data
var data;

//Page load
$(document).ready(function() {
	//Setup map
	$('#map').usmap({
		stateStyles: {fill: 'white'}
	});

	//Make Slider
	$("#slider").dateRangeSlider({
		bounds:{
			min: new Date(2014, 0, 1),
			max: new Date()
		},
                defaultValues:{
                        min: new Date(2014, 0, 1),
                        max: new Date()
                }
	});
	
	//Allow user to acquire data
	$("#grabData").click(grabData);
	
	//Load in data one time
	requestData();

	//Bind the date change
	$("#slider").on("valuesChanging", parse);
});

//Download Data Summaries
function grabData() {
    //Convert JSON to CSV format (https://stackoverflow.com/questions/11257062/converting-json-object-to-csv-format-in-javascript)
    var graphCSV = JSON.stringify(tempData);
    
    //Manual Rearrangements
    graphCSV = graphCSV.replace(/,/g,'\n');
    graphCSV = graphCSV.replace(/:/g,',');
    graphCSV = graphCSV.replace('{','');
    graphCSV = graphCSV.replace('}','');

    //var text = graphCSV;
    download("data.csv",graphCSV);
}

//Pull out data specific to Type xData State
function requestData() {
    var xComponent = "site_state";
    var yComponent = "received_date";
	
    getJsonData(xComponent, yComponent, parse, flags="nu");
}

//Structure data for drawing
function parse(requestData) {
	//Gather numbers between dates to color states
	tempData = {};
	states = [];
	max = 0;

	//Sort by dates
	var sliderBounds = $("#slider").dateRangeSlider("values");
	
	for (var key in flu)
	{
        	if (flu.hasOwnProperty(key)) {
                	var obj = flu[key];

			states = arrayUnique(states.concat(Object.keys(flu[key])));
	
			//Skip if dates outside range
			sampleDate = new Date(key);
			if(sampleDate < sliderBounds.min) continue;
			if(sampleDate > sliderBounds.max) continue;		

			for (var prop in obj) {
				if(obj.hasOwnProperty(prop)) {	

					//Init if property is not present
					if (!tempData.hasOwnProperty(prop)){
						tempData[prop] = 0;
					}
					
					//Add Data
					tempData[prop] += obj[prop] + 1;

					//Add to max
					if(tempData[prop] > max) {
						max = tempData[prop];
					}
				}
			}
            	}	
	}
	
	//fill in the map
	styles = [];
	
	for (var state in states)
	{
		percentPop = parseInt(tempData[states[state]]/max*100);
		fillcolor = 'beige';
		if(percentPop > 0) fillcolor = '#FFEDA0';
		if(percentPop > 14) fillcolor = '#FED976';
		if(percentPop > 28) fillcolor = '#FEB24C';
		if(percentPop > 42) fillcolor = '#FD8D3C';
		if(percentPop > 56) fillcolor = '#FC4E2A';
		if(percentPop > 71) fillcolor = '#E31A1C';
		if(percentPop > 85) fillcolor = '#BD0026';
		if(percentPop > 98) fillcolor = '#800026';
		$("#p1").text("> 0");
		$("#p2").text("> " + parseInt(max*0.142));
                $("#p3").text("> " + parseInt(max*0.284));
                $("#p4").text("> " + parseInt(max*0.426));
                $("#p5").text("> " + parseInt(max*0.568));
                $("#p6").text("> " + parseInt(max*0.71));
                $("#p7").text("> " + parseInt(max*0.852));
                $("#p8").text("> " + parseInt(max*0.99));	
		styles[states[state]] = {fill:fillcolor};
	}

	//Sort and draw table
	fluStates = Object.keys(tempData);
	fluStates.sort();
	drawTable(fluStates, tempData);

	//Set the stateSpecificStyles property
	$('#map').usmap('stateSpecificStyles',styles);

	//Update Title
	var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	$("#chartTitle").text("Incidence of Influenza Positive Cases in Swine Between " + monthNames[sliderBounds.min.getMonth()] + " " + sliderBounds.min.getDate() + ", " + sliderBounds.min.getFullYear() + " to " + monthNames[sliderBounds.max.getMonth()] + " " + sliderBounds.max.  getDate() + ", " + sliderBounds.max.getFullYear());
}

function arrayUnique(array) {
    var a = array.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
}

function drawTable(state, value)
{
	var stateTable = "<table class=\"wd-Table--striped wd-Table--hover\">";
        for (var key in state) {
        	stateTable += "<tr><th>" + state[key] + "</th>";
		stateTable += "<td>" + value[state[key]] + "</td></tr>";       
        }
	stateTable += "</table>";
	$("#dataTable").html(stateTable);	
}

</script>
<?php
$theme->drawFooter();
