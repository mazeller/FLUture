<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/dataloader.js","/js/jQDateRangeSlider-withRuler-min.js");
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

table.hTable {
   	border-collapse: collapse;
	table-layout: fixed;
}

th.leftAxis {
	text-align: right;
}

.hTable td {
	border-collapse: collapse;
	border: 1px solid;
	width: 150px;
	text-align: center;
	font-weight: bold;
}

CSS
, 'style');
$theme->drawHeader();
?>

<div id="heatmap"></div>

<div id="slider"></div>
<a href="javascript:;" id="grabData">Download Graph Data</a>

<script>
//Global access to data
var data;

//Page load
$(document).ready(function() {
	//Add hook for data download link
	$("#grabData").click(grabData);

	//Load in data one time
	requestData();
	drawTimeBar()
});

//Download Data Summaries
function grabData() {
	//Output variables
	var text;

	text = "Count of H1 and NA combinations\n";
	text += tabulateData(h1clade, nh1clade, h1Data, "Count  of H1 and NA combinations");
	text += "\n\nCount of H3 and NA combinations\n";	
	text += tabulateData(h3clade, nh3clade, h3Data, "Count of H3 and NA combinations");

	download("data.csv",text);
}


//Generate tabulated plots of data
function tabulateData(haclade, naclade, haData) {
        var haTable = "";

console.log(haData);
	//Sort weirdly/alphabetically
        haclade.sort(function (a, b) {
                if(a[0] == 'g' | a[0] == 'p') { a = 'c' + a; }
                if(b[0] == 'g' | b[0] == 'p') { b = 'c' + b; }  
                return a.toLowerCase().localeCompare(b.toLowerCase());
        });
        naclade.sort(function (a, b) {
                return a.toLowerCase().localeCompare(b.toLowerCase());
        });

        for (var key in haclade)
        {
                haTable += haclade[key] + ",";
                for (var key2 in naclade)
                {
                        //Check if data exists and if so, print it
			console.log(haclade[key] + "." + naclade[key2]);
			console.log(haData[haclade[key] + "." + naclade[key2]]);
			if ( haData[haclade[key] + "." + naclade[key2]]!= null ) {
                                haTable += haData[haclade[key] + "." + naclade[key2]] + ",";
                        }
                        else
                                haTable += ",";
                }
                haTable += "\n";
        }

	haTable += "\n,";
        for (var key2 in naclade)
        {
                //Postfix subtype
                if (naclade[key2] == "1998" | naclade[key2] == "2002"){
                        naclade[key2] = "N2." + naclade[key2];
                }
                if (naclade[key2] == "2010" | naclade[key2] == "2016"){
                        naclade[key2] = "hu-N2." + naclade[key2];
                }
                if (naclade[key2] == "classical" | naclade[key2] == "pandemic"){
                        naclade[key2] = "N1." + naclade[key2];
                }

                haTable += naclade[key2] + ",";
        }
        haTable += "\n";
	return haTable;	
}

//Pull out data specific to Type xData State
function requestData() {
    var xComponent = "ha_clade";
    var yComponent = ["na_clade","H1","H3","N1","N2","received_date"];
	
    getJsonData(xComponent, yComponent, parse, flags="hc");
}

//Structure data for drawing
function parse(requestData) {
	//Store data so only hit db once
	if(requestData[0] == null)
		requestData = flu;
	if("H1" in requestData[0])
		flu = requestData;	

	//Gather numbers between dates to color states
	h3Data = {};
	h1Data = {};
	h1clade = [];
	h3clade = [];
	nh1clade = [];
	nh3clade = [];
	h1Size = 0;
	h3Size = 0;

	//Sort by dates
	//var sliderBounds = $("#slider").dateRangeSlider("values");
	//console.log(sliderBounds.min.toString() + " " + sliderBounds.max.toString());
	var sliderBounds = $("#slider").dateRangeSlider("values");	
	for (var key in requestData)
	{
		fluCase = requestData[key];
		
		//Skip data outside date range	
		sampleDate = new Date(fluCase.received_date);
		if(sampleDate < sliderBounds.min) {continue};
		if(sampleDate > sliderBounds.max) {continue};

		//Add in H1 counts
		if(fluCase.H1 == "1") {
			//Remove specific  clades
			if(fluCase.ha_clade != "cluster_IVA" & fluCase.ha_clade != "cluster_IVE" & fluCase.ha_clade != "2010-human-like" & fluCase.ha_clade != "cluster_IV") {
				if(!(fluCase.ha_clade + "." + fluCase.na_clade in h1Data)){
					h1Data[fluCase.ha_clade + "." + fluCase.na_clade] = 1;	
				}
				else {
					h1Data[fluCase.ha_clade + "." + fluCase.na_clade] += 1;
				}
			
				//Capture H1 Clades
				if(h1clade.indexOf(fluCase.ha_clade) < 0)
		                        h1clade.push(fluCase.ha_clade);
	
	        	        //Capture NA clades
        		        if(nh1clade.indexOf(fluCase.na_clade) < 0)
	                	        nh1clade.push(fluCase.na_clade);
			}
		}
                if(fluCase.H3 == "1") {
                        //Remove specific  clades
                        if(fluCase.ha_clade != "delta1a" & fluCase.ha_clade != "delta1b" & fluCase.ha_clade != "delta2" & fluCase.ha_clade != "gamma-like" & fluCase.ha_clade != "gamma" & fluCase.ha_clade != "alpha" & fluCase.ha_clade != "beta") {
	                        if(!(fluCase.ha_clade + "." + fluCase.na_clade in h3Data)){
        	                        h3Data[fluCase.ha_clade + "." + fluCase.na_clade] = 1;
                	        }
	                        else {
        	                        h3Data[fluCase.ha_clade + "." + fluCase.na_clade] += 1;
                	        }

	                        //Capture H3 Clades
        	                if(h3clade.indexOf(fluCase.ha_clade) < 0)
                	                h3clade.push(fluCase.ha_clade);
                
		                //Capture NA clades
		                if(nh3clade.indexOf(fluCase.na_clade) < 0)
		                        nh3clade.push(fluCase.na_clade);
			}
		}
	}

	//Draw tables and tools
	$("#heatmap").empty();
	drawTable(h1clade, nh1clade.slice(), h1Data, "Count  of H1 and NA combinations");
	drawLegend(h1Data, "h1legend");
	drawTable(h3clade, nh3clade.slice(), h3Data, "Count of H3 and NA combinations");
	drawLegend(h3Data, "h3legend");
}

function drawTimeBar()
{
	$("#slider" ).dateRangeSlider({
                bounds:{
                        min: new Date(2010, 0, 1),
                        max: new Date()
                },
                defaultValues:{
                        min: new Date(2010, 0, 1),
                        max: new Date()
                }
        });
	
	//Bind the date change
        $("#slider").on("valuesChanging", parse);
}

function drawLegend(haData, id)
{
	//Add a div element
	canvasTag = "<canvas id=\"" + id + "\" width=\"150\" height=\"60\">";
	$("#heatmap").append(canvasTag);
	max = maxSubtype(haData);
	sum = sumSubtype(haData);

	var c = document.getElementById(id);
	var ctx = c.getContext("2d");
	var grd = ctx.createLinearGradient(0, 0, 150, 0);
	grd.addColorStop(0, "#0099FF");
	grd.addColorStop(1, "#FFFFFF");
	ctx.fillStyle = grd;
	ctx.fillRect(5, 15, 150, 20);
	ctx.font = "bold 12px Arial";
	ctx.fillStyle = "black";
	ctx.fillText("Raw Count",5,10);
	ctx.fillText(max,5,50);
	ctx.fillText(Math.round(max/2),70,50);
	ctx.fillText(0,138,50);
}

function drawTable(haclade, naclade, haData, title)
{
	//Sort weirdly/alphabetically
	haclade.sort(function (a, b) {
		if(a[0] == 'g' | a[0] == 'p') { a = 'c' + a; }
		if(b[0] == 'g' | b[0] == 'p') { b = 'c' + b; }	
    		return a.toLowerCase().localeCompare(b.toLowerCase());
	});
	naclade.sort(function (a, b) {
    		return a.toLowerCase().localeCompare(b.toLowerCase());
	});

	//Grab max for color
	max = maxSubtype(haData);
	//Draw inside the heatmap div
	haTable = "<h2>" + title + "</h2>";
	haTable += "<table class=\"hTable\">";
	for (var key in haclade)
	{
		haTable += "<tr><th class=\"leftAxis\">" + haclade[key] + "</th>";
		for (var key2 in naclade)
		{
			//Check if data exists and if so, print it
			if ( haData[haclade[key] + "." + naclade[key2]]!= null ) {
				color = getColor( haData[haclade[key] + "." + naclade[key2]], max);
				haTable += "<td bgcolor=\"" + color + "\">" + haData[haclade[key] + "." + naclade[key2]] +"</td>";
			}
			else
				haTable += "<td></td>";
		}
		haTable += "</tr>";
	}
	//Draw legend on last line
	haTable += "<tr><th></th>";
        for (var key2 in naclade)
        {
		//Postfix subtype
		if (naclade[key2] == "1998" | naclade[key2] == "2002"){
			naclade[key2] = "N2." + naclade[key2];
		}
		if (naclade[key2] == "2010" | naclade[key2] == "2016"){
			naclade[key2] = "hu-N2." + naclade[key2];
		}
                if (naclade[key2] == "classical" | naclade[key2] == "pandemic"){
                        naclade[key2] = "N1." + naclade[key2];
		}

		haTable += "<th>" + naclade[key2] + "</th>";
        }
	haTable += "</tr>";
	haTable += "</table>";
	$("#heatmap").append(haTable);
}

function getColor(sub, max)
{
	colorInitial = [255, 255, 255];
	colorFinal = [0, 153, 255];
	red = Math.abs(Math.round(((colorInitial[0] - colorFinal[0]) * (sub / max)) - colorInitial[0] ));
	green =Math.abs( Math.round(((colorInitial[1] - colorFinal[1]) * (sub / max)) - colorInitial[1] ));
	blue = Math.abs(Math.round(((colorInitial[2] - colorFinal[2]) * (sub / max)) - colorInitial[2] ));
	redHex = red.toString(16);
	redHex = pad(redHex, 1);
	greenHex = green.toString(16);
	greenHex = pad(greenHex, 2);
	blueHex = blue.toString(16);
	blueHex = pad(blueHex, 2);
	color = "#" + redHex + greenHex + blueHex;
	return color;
}

//From stackexchange
function pad(value, length) {
    return (value.toString().length < length) ? pad("0"+value, length):value;
}

function sumSubtype(haData)
{
	total = 0;
	for (var property in haData) {
    		if (haData.hasOwnProperty(property)) {
       			total += haData[property];
		}
	}
	return total;
}

function maxSubtype(haData)
{
	max = 0;
	for (var property in haData) {
    		if (haData.hasOwnProperty(property)) {
       			if(haData[property] > max)
				max = haData[property];
		}
	}
	return max;
}

</script>
<?php
$theme->drawFooter();
