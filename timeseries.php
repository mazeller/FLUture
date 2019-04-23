<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/dataloader.js","/js/jQDateRangeSlider-withRuler-min.js","/js/drawgraphflu.js");
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
				    <a href="/#variables">Description of Variables</a>
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
				    <a href="javascript:;" id="grabData">Download Graph Data</a><br>
			    <a href="javascript:;" id="grabBarcode">Download Sequence Identifiers</a>
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
	$("#grabBarcode").click(grabBarcode);

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
    text += "\n\n\"If you use data provided by ISU FLUture in your work, please credit in the following format;\"\n\"Zeller, M. A., Anderson, T. K., Walia, R. W., Vincent, A. L., &amp; Gauger, P. C. (2018). ISU FLUture: a veterinary diagnostic laboratory web-based platform to monitor the temporal genetic patterns of Influenza A virus in swine. BMC bioinformatics, 19(1), 397.\"\n\"(data retrieved <?php echo (new DateTime())->format('d M, Y');?>).\"";

    download("data.csv",text);
}

//Download Data Summaries
function grabBarcode() {
    //Convert JSON to CSV format (https://stackoverflow.com/questions/11257062/converting-json-object-to-csv-format-in-javascript)
    var barcodeCSV = JSON.stringify(barcodeData);
    barcodeCSV = ConvertToCSV(barcodeCSV);
    var text = xAxis.toString() + "\n" + barcodeCSV;
    text += "\n\n\"If you use data provided by ISU FLUture in your work, please credit in the following format;\"\n\"Zeller, M. A., Anderson, T. K., Walia, R. W., Vincent, A. L., &amp; Gauger, P. C. (2018). ISU FLUture: a veterinary diagnostic laboratory web-based platform to monitor the temporal genetic patterns of Influenza A virus in swine. BMC bioinformatics, 19(1), 397.\"\n\"(data retrieved <?php echo (new DateTime())->format('d M, Y');?>).\"";

    download("barcode.csv",text);
}

//Pull out data specific to Type xData State
function requestData() {
    var xComponent = "ha_clade";
    var yComponent = ["barcode","na_clade","H1","H3","N1","N2","received_date","age_days","site_state","testing_facility","sequence_specimen","pcr_specimen"];
        
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
    var barcode = {};
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
		barcode[rdata[key][yComponent]] = {};
		groups.push(rdata[key][yComponent]);
	}		
	//Make sure y axis exists
        if (!flu[rdata[key][yComponent]].hasOwnProperty(useDate)){
                flu[rdata[key][yComponent]][useDate] = 0;
                barcode[rdata[key][yComponent]][useDate] = "";
		//If unique, add to x axis
		if(xAxis.indexOf(useDate) == -1)
	               xAxis.push(useDate);
        }
	flu[rdata[key][yComponent]][useDate]++;
        //Add barcode to list
        if(skipList.indexOf(rdata[key]["accession_id"]) == -1){
        	barcode[rdata[key][yComponent]][useDate] += rdata[key]["accession_id"] + ",";
        }
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

    //Put barcodes in correct format
    barcodeData = [];
        for (var key in barcode) {
            tempData = [];
            if (barcode.hasOwnProperty(key)) {
                tempData.push(key);
                var obj = barcode[key];

                for (var i in xAxis) {
                    if (obj[xAxis[i]] != null)
		    {
			obj[xAxis[i]] = "\"" + obj[xAxis[i]] + "\"";
                        tempData.push(obj[xAxis[i]]);
		    }
                    else
                        tempData.push(null);
                }
            }
            barcodeData.push(tempData);
    }

    //Graph it
    graphFlu(graphData, xAxis, groups, xComponent, yComponent);
}

function graphFlu(graphData, xAxis, groups, xComponent, yComponent) {
    var normalize = $("#normalize").is(":checked");
    var tool = "timeseries";
    // helper js function to draw the graph for the tools
    drawGraphFlu(graphData, xAxis, groups, xComponent, yComponent, tool, normalize);
}
</script>
<?php
$theme->drawFooter();
