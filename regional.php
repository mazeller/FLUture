<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/dataloader.js","/js/jQDateRangeSlider-withRuler-min.js","js/drawgraphflu.js","//cdnjs.cloudflare.com/ajax/libs/d3/3.5.3/d3.min.js","//cdnjs.cloudflare.com/ajax/libs/topojson/1.6.9/topojson.min.js","/js/datamaps.usa.min.js");
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
<style>
svg {
	overflow: hidden;
    	margin-left: -50px;
    	margin-top: -20px;
}
g.datamaps-subunits {
	transform: scale(1.05);
}
g.labels {
	transform: scale(1.05);
}
#map {
	height: 500px;
	position: relative;
	padding: 0;
	top: 0px;
	left: 0px;
}

fieldset {
	font-size: 0px;
	padding: 8px;	
}
.select-bar {
	width: 160px;
	display: inline-block;
	padding: 0;
	margin: 0;
	font-size: 14px;
}
select {
	font-size; 14px;
	width: 145px;
	height: 86.93px;
}
.btn-bar {
	width: 60px; 
	display: inline-block;
	position: relative; 
	bottom: 18px;
}
.btn {
	width: 45px;
	background-color: #555555;
  	color: white;
  	border: 2px solid #555555;
	font-size: 14px;
	padding: 0;
}

.btn:hover {
  	background-color: white;
  	color: black;
  	border: 2px solid #555555;
}

.button1 {
	width: 80px;
  	background-color: #008CBA;
  	color: white;
	border: 2px solid #008CBA;
	font-size: 14px;
}

.button1:hover {
	background-color: white; 
	color: black; 
	border: 2px solid #008CBA;
}

.button2 {
	width: 80px;
  	background-color: #f44336; 
  	color: white; 
  	border: 2px solid #f44336;
	font-size: 14px;
}

.button2:hover {
  	background-color: white; 
  	color: black; 
  	border: 2px solid #f44336;
}

h2 {
	word-wrap: break-word;
}

.hoverinfo table {
    display: table-row-group;
    vertical-align: middle;
    margin: 0px 0x 0px 0px;
    border-spacing: 0px;
    border-collapse: collapse;
}

thead {
    background-color: #aaa;
    font-size: 14px;
    text-align: left;
    color: #FFF;
    //vertical-align: inherit;
    //font-weight: bold;
}

table tr td {
    border-right: 1px dotted #aaa;
    border-bottom: 1px solid #aaa;
}

table tr td:last-child {
    border-right: 0;
}

table tr:last-child td {
    border-bottom: 0;
}
</style>

<body>
<p class="wd-Alert--error">
	<strong>Error:</strong> Currently the querying of this tool is incorrect. The numbers cannot be guaranteed as accurate.
</p>
<h2 id="chartTitle"></h2>
<div style="float: right; position:absolute;z-index:10;background:#FFFFFF;opacity:0.7;" class="legend">
        <i style="background:#009999"></i><b id="p1"> &gt; 0%</b><br>
        <i style="background:#148f8a"></i><b id="p2"> &gt; 14%</b><br>
        <i style="background:#338073"></i><b id="p3"> &gt; 28%</b><br>
        <i style="background:#52705c"></i><b id="p4"> &gt; 42%</b><br>
        <i style="background:#706145"></i><b id="p5"> &gt; 56%</b><br>
        <i style="background:#8f522e"></i><b id="p6"> &gt; 71%</b><br>
        <i style="background:#ad4217"></i><b id="p7"> &gt; 85%</b><br>
        <i style="background:#CC3300"></i><b id="p8"> &gt; 99%</b>
</div>

<div id="map"></div>

<div id="slider"></div>
<div>
        <fieldset>
        <legend style="font-size: 14px;">Options</legend>
	<div style="font-size: 14px;">
        	<a href="about.php#variable-info">Description of Variables</a>
	</div>
	<div class="select-bar" style="width: 200px;">
                <b>Variables</b><br>
                <select id='variables' size="5">
                        <option value="cases">Positive Cases</option>
                        <option value="age_days">Age</option>
                        <option value="diag_code">Bacterial Coinfection</option>
                        <option value="testing_facility">Data Source</option>
                        <option value="ha_clade">HA Clade</option>
                        <option value="h1_clade">H1 Clade</option>
                        <option value="h3_clade">H3 Clade</option>
                        <option value="na_clade">NA Clade</option>
                        <option value="sequence_specimen">Sequence Specimen</option>
                        <option value="pcr_specimen">PCR Specimen</option>
                        <option value="subtype">Subtype</option>
                        <option value="weight_pounds">Weight</option>
                </select>
	</div>
	<div class="select-bar">
                <b>Categories</b><br>
                <select id='categories' multiple="multiple">
                        <option></option>
                </select>
	</div>
	<div class="btn-bar">
		<span id="add"><input type="button" class="btn" value=">>"></span><br />
		<br>
		<span id="remove"><input type="button" class="btn" value="<<"></span>
	</div>
	<div class="select-bar">
                <b>Categories Shown</b><br>
  		<SELECT id="shown" multiple="multiple">
    			<OPTION></OPTION>
  		</SELECT>
	</div>
	</fieldset>
</div>

<div><a href="javascript:;" id="grabData">Download Graph Data</a></div>
<div id="dataTable"></div>
</body>
</html>


<script type="text/javascript">
//Page load
var defaultMapData = {};
$(document).ready(function() {
        //Get the variable
        $("#variables").on("change", function() {
                makeSelect($('#variables').val());
        });

        $("#variables").change(parse);

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

	$("#add").bind('click', function(){
  		$("#shown").append( $("#categories option:selected"));
		labelSort("shown", $('#variables').val());})
		.bind('click', parse);

	$("#remove").bind('click', function(){
  		$("#categories").append( $("#shown option:selected"));
		labelSort("categories", $('#variables').val());})
		.bind('click', parse);
});

function labelSort(selectorId, variable) {
	// Get the orders from databass if orders are not ready
    	if (Object.getOwnPropertyNames(orders).length === 0)
        	getOrder(extractOrders);

	var selectedOptions = $("#"+selectorId+" option");
	
	if (variable == "age_days") {
		selectedOptions.sort(sortAge);
	} else if (variable == "weight_pounds") {
		selectedOptions.sort(sortWeight);
	} else if (variable == "ha_clade" || variable == "h1_clade" || variable == "h3_clade") {
		selectedOptions.sort(sortHaClade);
	} else if (variable == "na_clade") {
		selectedOptions.sort(sortNaClade);
	} else {
		selectedOptions.sort(function(a, b) {
				return a.value > b.value ? 1 : -1;
		});
	}
  	$("#"+selectorId).empty().append(selectedOptions);
}

// Setup map
var containerw = $('#map').width();
var containerh = $('#map').height();

var map = new Datamap({
  element: document.getElementById('map'),
  geographyConfig: {
    highlightBorderColor: '#bada55',
   popupTemplate: function(geography, data) {
      if (data.freq == undefined || data.freq == 0) {return "";}

      //for_hover_string = "";
      body_string = "";
      for_hover = data.hover_table;
      if (for_hover[""] == undefined) {
        for (key in for_hover) {
          if (for_hover[key].freq == 0) 
            continue;
	  body_string += '<tr>';
	  body_string += '<td>' + key + '</td>';
	  body_string += '<td>' + for_hover[key].freq + '</td>';
	  body_string += '<td>' + for_hover[key].percentage + '%</td>';
	  body_string += '</tr>';
        }
      }
      //Add the Category/Freq/Percentage
      firstLine = '<tr>';
      firstLine += '<td>Category</td>';
      firstLine += '<td>Frequency</td>';
      firstLine += '<td>Percentage</td>';
      firstLine += '</tr>';

      //Add the total information
      lastLine = '<tr>';
      lastLine += '<td>Total</td>';
      lastLine += '<td>' + data.freq + '</td>';
      lastLine += '<td>' + data.percentage + '%</td>';
      lastLine += '</tr>';

      title = '<thead><tr><th colspan="3"><strong>' + geography.properties.name + firstLine + '</strong></th></tr></thead>';
      table = '<tbody>' + body_string + lastLine + '</tbody>';
      hover_text = '<div class="hoverinfo"><table>' + title + table + '</table></div>';  
      return hover_text;
    },
    highlightBorderWidth: 3
  },
  scope: 'usa',
  width: containerw,
  height: containerh,
  fills: {
  "color0": "#B7D2CA",
  "color1": "#009999",
  "color2": "#148f8a",
  "color3": "#338073",
  "color4": "#52705c",
  "color5": "#706145",
  "color6": "#8f522e",
  "color7": "#ad4217",
  "color8": "#CC3300",
  defaultFill: '#bebebe'
}
});
map.labels();

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


// Make dynamic select
var optiondata = {};
var ages = ["neonate", "suckling", "nursery", "grow finisher", "adult"];
var weights = ["Under 50", "50-100", "100-150", "150-200", "200-250", "250-300", "300-350", "350-400", "400-450", "450-500", "Above 500"];
var data_source = ["ISU", "USDA"];
var subtype = ["H1N1", "H1N2", "H3N1", "H3N2"];
optiondata["age_days"] = ages;
optiondata["weight_pounds"] = weights;
optiondata["testing_facility"] = data_source;
optiondata["subtype"] = subtype;

// Request orders data
function getOrder(callback) {
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

function selectHelper(data) {
        var rawkeys = Object.keys(data);
        for (var i in rawkeys) {
                var curKey = rawkeys[i];
		var targetKey = curKey;
                if (curKey == 'diag_info') {
			targetKey = 'diag_code';
                }
                if(data.hasOwnProperty(curKey)) {
                        optiondata[targetKey] = [];
                        var indices = Object.keys(data[curKey]);
                        indices.forEach(function(index) {
				if (targetKey == 'diag_code')
				{
                                	optiondata[targetKey].push(data[curKey][index]['diag_code']);
				} else {
                                	optiondata[targetKey].push(data[curKey][index]);
				}
                        });
                }
        }
}

getOrder(selectHelper);

function makeSelect(updated) {
        // clean the options
       	document.getElementById('categories').options.length = 0;
        document.getElementById('shown').options.length = 0;

        var arr = [];
        if (optiondata.hasOwnProperty(updated)) {
                arr = optiondata[updated];
        }
	if (updated == "cases")
        	$('#categories').append("<option></option>");
        for(var i in arr) {
                var select = "<option value='" + arr[i] + "'>";
		optionLabel = arr[i];
		optionLabel = optionLabel.charAt(0).toUpperCase()+ optionLabel.slice(1);
                $("#categories").append(select.concat(optionLabel));
        }
}

//Download Data Summaries
function grabData() {
    //Convert JSON to CSV format (https://stackoverflow.com/questions/11257062/converting-json-object-to-csv-format-in-javascript)
    //Manual Rearrangements
    var graphCSV = "State, Frequency, Percentage\n";
    for (key in tempData) {
        graphCSV += key + ",";
	graphCSV += tempData[key].freq + ",";
	graphCSV += tempData[key].percentage + "\n";
    }

    graphCSV += "\n\n\"If you use data provided by ISU FLUture in your work, please credit in the following format;\"\n\"Zeller, M. A., Anderson, T. K., Walia, R. W., Vincent, A. L., &amp; Gauger, P. C. (2018). ISU FLUture: a veterinary diagnostic laboratory web-based platform to monitor the temporal genetic patterns of Influenza A virus in swine. BMC bioinformatics, 19(1), 397.\"\n\"(data retrieved <?php echo (new DateTime())->format('d M, Y');?>).\"";

    //var text = graphCSV;
    download("data.csv",graphCSV);
}

//Pull out data specific to Type xData State
function requestData() {
    var xComponent = "site_state";
    var yComponent = ["barcode","na_clade","H1","H3","N1","N2","received_date","age_days","weight_pounds","ha_clade","testing_facility","sequence_specimen","pcr_specimen","diag_code"];

    getJsonData(xComponent, yComponent, parse, flags="nu");
}


//Global access to data
var data;
var tempData;
function parse(requestData) {
        //Gather numbers between dates to color states
        if (requestData.constructor.name != 'Array')
                requestData = data;
        data = requestData;

        var variable = $('#variables').val();
	// by default show the positive cases
	if (variable == null)
		variable = "cases";

	var options = [];
	$("#shown option").each(function()
	{
    		options.push($(this).val());
	});

        var skipList = ["","-1","USA",[], undefined, "Unknown"];

        tempData = {};
        states = [];
        max = 0;
	total = 0;

        //Sort by dates
        var sliderBounds = $("#slider").dateRangeSlider("values");

        for (var i in data)
        {
                // make sure in the time range
                sampleDate = new Date(data[i].received_date);
                if(sampleDate < sliderBounds.min) continue;
                if(sampleDate > sliderBounds.max) continue;

		var tempFreq = 0;
		var hover_table = {};
                if (variable != 'cases') {
                        var levelOne = data[i][variable];
                        if (skipList.indexOf(levelOne) != -1)
                                continue;
			for (op in options) 
			{ 
				option = options[op];
				if (!hover_table.hasOwnProperty(option)) {
					hover_table[option] = 0;
				}
				if (!levelOne.includes(option)) {
					continue;
				}
				hover_table[option] += 1;
				tempFreq += 1;
			}
                } else {
			tempFreq = 1;
		}
                states = arrayUnique(states.concat(data[i].site_state));

                //Init if property is not present
                if (!tempData.hasOwnProperty(data[i].site_state)) {
                        tempData[data[i].site_state] = {};
			tempData[data[i].site_state].freq = 0;
			tempData[data[i].site_state].hover_table = {};
			for (op in options) {
				tempData[data[i].site_state].hover_table[options[op]] = {};	
				tempData[data[i].site_state].hover_table[options[op]].freq = 0;	
				tempData[data[i].site_state].hover_table[options[op]].percentage = 0;	
			}
                }
                //Add to Data and count total 
                tempData[data[i].site_state].freq += tempFreq;
		total += tempFreq;
                for (op in options) {
                	tempData[data[i].site_state].hover_table[options[op]].freq += hover_table[options[op]];
                }

                //Add to max
                if (tempData[data[i].site_state].freq > max) {
                        max = tempData[data[i].site_state].freq;
                }
        }
	
	//make data for graph
	if (states.length != 0) {
		for (var state in states) {
			num = tempData[states[state]].freq/max*100; 
			var percentPop = parseInt(num);
			var percentPopDecimal = (num).toFixed(2);
			fillcolor = "color0";
                	if(percentPop > 0) fillcolor = 'color1';
                	if(percentPop > 14) fillcolor = 'color2';
                	if(percentPop > 28) fillcolor = 'color3';
                	if(percentPop > 42) fillcolor = 'color4';
                	if(percentPop > 56) fillcolor = 'color5';
                	if(percentPop > 71) fillcolor = 'color6';
                	if(percentPop > 85) fillcolor = 'color7';
                	if(percentPop > 98) fillcolor = 'color8';
			if(isNaN(percentPopDecimal) || percentPopDecimal == 0.00) fillcolor = 'defaultFill';
			
			tempData[states[state]].fillKey = fillcolor;
			tempData[states[state]].percentPop = percentPopDecimal;
			tempData[states[state]].percentage = (isNaN((tempData[states[state]].freq/total*100).toFixed(2))) ? (0).toFixed(2) : (tempData[states[state]].freq/total*100).toFixed(2);
			// percentage by national total
			hover_table_editing = tempData[states[state]].hover_table;
			for (key in hover_table_editing) {
				hover_table_editing[key].percentage = (isNaN((hover_table_editing[key].freq/total*100).toFixed(2))) ? (0).toFixed(2) : (hover_table_editing[key].freq/total*100).toFixed(2); 
			}
	                $("#p1").text("> 0");
                	$("#p2").text("> " + parseInt(max*0.142));
                	$("#p3").text("> " + parseInt(max*0.284));
                	$("#p4").text("> " + parseInt(max*0.426));
                	$("#p5").text("> " + parseInt(max*0.568));
                	$("#p6").text("> " + parseInt(max*0.71));
                	$("#p7").text("> " + parseInt(max*0.852));
                	$("#p8").text("> " + parseInt(max*0.99));
		}
	}

	if (Object.getOwnPropertyNames(defaultMapData).length === 0 && variable == "cases")
        	defaultMapData = tempData;

	map.updateChoropleth(tempData, {reset: true})

        //Sort and draw tae
        fluStates = Object.keys(tempData);
        fluStates.sort();
        drawTable(fluStates, tempData);

        //Update Title
        var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        if (variable == 'cases') {
                var content = 'Influenza Positive Cases';
        } else {
                var content = translateLabel(variable) + ": " + options.toString();
        }
        var title = "Incidence of " + content + " in Swine Between " + monthNames[sliderBounds.min.getMonth()] + " " + sliderBounds.min.getDate() + ", " + sliderBounds.min.getFullYear() + " to " + monthNames[sliderBounds.max.getMonth()] + " " + sliderBounds.max.  getDate() + ", " + sliderBounds.max.getFullYear();
        $("#chartTitle").text(title);

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
        stateTable += "<tr><th>State</th>";
        stateTable += "<td><strong>Frequence</strong></td>";
        stateTable += "<td><strong>Percentage</strong></td></tr>";
        for (var key in state) {
		if (value[state[key]].freq == 0)
			continue;
                stateTable += "<tr><th>" + state[key] + "</th>";
                stateTable += "<td>" + value[state[key]].freq + "</td>";
                stateTable += "<td>" + value[state[key]].percentage + "%</td></tr>";
        }
        stateTable += "</table>";
        $("#dataTable").html(stateTable);
}


</script>
<?php
$theme->drawFooter();

