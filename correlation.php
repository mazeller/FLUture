<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/dataloader.js","/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/drawgraphflu.js");
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
				    <a href="/#variables">Description of Variables</a>
                                    <br>
                                    <b>X Axis</b><br>
                                    <select id="axisx">
                                        <option value="age_days">Age</option>
					<option value="diag_code">Bacterial Coinfection</option>
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
					<option value="diag_code">Bacterial Coinfection</option>
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
                                    <input type="checkbox" id="stack" value="stack" checked>Stack columns<br>
                                    <input type="checkbox" id="normalize" value="normalize" checked>Account by Proportion<br>
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
	$("#grabData").click(grabData);
	$("#grabBarcode").click(grabBarcode);
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

    text += "\n\n\"If you use data provided by ISU FLUture in your work, please credit in the following format;\"\n\"Zeller, M. A., Anderson, T. K., Walia, R. W., Vincent, A. L., &amp; Gauger, P. C. (2018). ISU FLUture: a veterinary diagnostic laboratory web-based platform to monitor the temporal genetic patterns of Influenza A virus in swine. BMC bioinformatics, 19(1), 397.\"\n\"(data retrieved <?php echo (new DateTime())->format('d M, Y');?>).\"";
    download("data.csv",text);
}

//Download Data Summaries
function grabBarcode() {
    //Convert JSON to CSV format (https://stackoverflow.com/questions/11257062/converting-json-object-to-csv-format-in-javascript)
    var barcodeCSV = JSON.stringify(barcodeData);
    barcodeCSV = ConvertToCSV(barcodeCSV);
    var text = "," + xAxis.toString() + "\n" + barcodeCSV;
    text += "\n\n\"If you use data provided by ISU FLUture in your work, please credit in the following format;\"\n\"Zeller, M. A., Anderson, T. K., Walia, R. W., Vincent, A. L., &amp; Gauger, P. C. (2018). ISU FLUture: a veterinary diagnostic laboratory web-based platform to monitor the temporal genetic patterns of Influenza A virus in swine. BMC bioinformatics, 19(1), 397.\"\n\"(data retrieved <?php echo (new DateTime())->format('d M, Y');?>).\"";

    download("barcode.csv",text);
}

//Pull out data specific to Type xData State
function requestData() {
    var xComponent = "ha_clade";
    var yComponent = ["barcode","na_clade","H1","H3","N1","N2","received_date","age_days","weight_pounds","site_state","testing_facility","sequence_specimen","pcr_specimen","diag_code"];

    getJsonData(xComponent, yComponent, parse, flags="");
}

var data = {};
var orders = {};

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
    var barcode = {};
    var skipList = ["","-1","USA",[],undefined];
    
    for (var key in rdata) {
	if(skipList.indexOf(rdata[key][xComponent]) != -1 || skipList.indexOf(rdata[key][yComponent]) != -1)
		continue;
	var xData = [];
	var yData = [];
	if (rdata[key][xComponent].constructor.name != 'Array') {
		if (rdata[key][xComponent].indexOf(",") != -1)
		{
			xData = rdata[key][xComponent].split(",");
		} else {
			xData.push(rdata[key][xComponent]);
		}
	} else {
		xData = rdata[key][xComponent]; 
	}

        if (rdata[key][yComponent].constructor.name != 'Array') {
		if (rdata[key][yComponent].includes(","))
		{
			yData = rdata[key][yComponent].split(",");
		} else {
			yData.push(rdata[key][yComponent]);
		}
        } else {
                yData = rdata[key][yComponent];
        }


	for (var j in yData) {
		if(skipList.indexOf(yData[j]) != -1)
                	continue;
                if (!flu.hasOwnProperty(yData[j])) {
                	flu[yData[j]] = {};
                        barcode[yData[j]] = {};
                        groups.push(yData[j].toString());
		}
                for (var i in xData) {
                	//Skip certain subsets
                        if(skipList.indexOf(xData[i]) != -1)
                        	continue;

                        //Make sure y axis exists
                        if (!flu[yData[j]].hasOwnProperty(xData[i])) {
                        	flu[yData[j]][xData[i]] = 0;
                                barcode[yData[j]][xData[i]] = "\"";
                                //If unique, add to x axis
                                if (xAxis.indexOf(xData[i]) == -1)
                                	xAxis.push(xData[i]);
                        }
                        flu[yData[j]][xData[i]]++;

                        //Add barcode to list
                        if(skipList.indexOf(rdata[key].accession_id) == -1){
                        	barcode[yData[j]][xData[i]]+= rdata[key].accession_id + ",";
                        }
                }
	}

    }

    //Correctly sort per data type (numerical | lexigraphical | colloquial )
    if (xComponent == "day" || xComponent == "month" || xComponent == "week" || xComponent == "year")
	    xAxis.sort(sortNumber);
    if (xComponent == "sequence_specimen" || xComponent == "site_state" || xComponent == "subtype" || xComponent == "pcr_specimen")
	    xAxis.sort();
    if (xComponent == "age_days")
	    xAxis.sort(sortAge);
    if (xComponent == "weight_pounds")
	    xAxis.sort(sortWeight);
    if (xComponent == "h1_clade" || xComponent == "h3_clade" || xComponent == "ha_clade")
	    xAxis.sort(sortHaClade);
    if (xComponent == "na_clade")
            xAxis.sort(sortNaClade);
    if (xComponent == "diag_code")
	    xAxis.sort(sortDiag);

    //Turn off groups if unchecked
    if (normalize == true) {
        //Collapse the structure into data for c3 charts
        graphData = [];
        xScore = [];
        for (var key in flu) {
            var obj = flu[key];

            //Gather count datafor normalization
            for (var i in xAxis) {
                if (obj[xAxis[i]] != null) {
                    if (xScore[xAxis[i]] == null)
                        xScore[xAxis[i]] = 0;
                    xScore[xAxis[i]] = xScore[xAxis[i]] + obj[xAxis[i]];
                }
            }
        }
        for (var key in flu) {
            tempData = [];
            tempData.push(key);
            var obj = flu[key];

            for (var i in xAxis) {
                if (obj[xAxis[i]] != null)
                    tempData.push((obj[xAxis[i]] * 100 / xScore[xAxis[i]]).toFixed(3));
                else
                    tempData.push(null);
            }
            graphData.push(tempData);
        } 
    } else {
        //Collapse the structure into data for c3 charts
        graphData = [];

        for (var key in flu) {
            tempData = [];
            tempData.push(key);
            var obj = flu[key];

            for (var i in xAxis) {
                if (obj[xAxis[i]] != null) {
                    tempData.push(obj[xAxis[i]]);
                } else {
                    tempData.push(0);
                }
            }
            graphData.push(tempData);
        }
    }

    //Put barcodes in correct format
    barcodeData = [];
        for (var key in barcode) {
            tempData = [];
            tempData.push(key);
            var obj = barcode[key];

            for (var i in xAxis) {
                if (obj[xAxis[i]] != null)
                {
		    obj[xAxis[i]] += "\"";
                    tempData.push(obj[xAxis[i]]);
                }
                else
                    tempData.push(null);
            }
            barcodeData.push(tempData);
    }

    //Graph it
    console.log(graphData);
    graphFlu(graphData, xAxis, groups, xComponent, yComponent);
}

//Draw our data
function graphFlu(data, xAxis, groups, xComponent, yComponent) {
    // updates to have correct label order
    var stack = $("#stack").is(":checked");
    var normalize = $("#normalize").is(":checked");
    var tool = "correlation";
    var keywordMap = new Map();
    keywordMap.set('normalize', normalize);
    keywordMap.set('tool', tool);
    keywordMap.set('stack', stack);
    // helper js function to draw the graph for the tools
    drawGraphFlu(data, xAxis, groups, xComponent, yComponent, keywordMap);
}
</script>
<?php
$theme->drawFooter();
