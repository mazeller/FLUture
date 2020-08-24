<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/dataloader.js", "/classify/nttoprot.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/c3.min.css');
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->drawHeader();
?>
<div id="summaryChartSpace">
	<h2 id="summaryChartTitle">H3 Antigenic Motif Detection Frequency - Common Motifs</h2>
	<div id="summaryChart"></div>
	<a href="javascript:;" id="grab-all-detected">Download Detection Frequency Data for All Motifs</a><br/>
	<a class="wd-Button" id="update-graph">Show Detection Frequencies of Submitted Motifs</a>
	<a class="wd-Button" id="return-graph">Show Detection Frequencies of Most Common Motifs</a><br/>
	<div id="proportionOption">
		<input type="checkbox" id="account-by-proportion"/>Account by proportion<br/>
	</div><br/>
	
</div>

<h2 id="chartTitle"> H3 Antigenic Motif Tool</h2>
<p>
The H3 Antigenic Motif Tool identifies the antigenic motif of a given
nucleotide or amino acid sequence.</p>

<form id="target">
<textarea rows="16" cols="100" id="sequences" placeholder="Paste sequence(s) in fasta format">
</textarea><br/>
<!--<b>Sequence type</b><br/>
<input type="radio" name="blasttype" value="nt" checked = "checked" />nucleotide<br>
<input type="radio" name="blasttype" value="aa" />amino acid<br/>-->
<a class="button" id="advanced" onclick="toggleOptions()">Advanced Options</a>
<div id="options">
	<fieldset>
		<b>Signal Peptide</b><br/>
		<input type="checkbox" id="signalpeptide" name="signalpeptide" checked="checked"/>included in sequence (will offset positions by 16)<br/>
		<b>Amino Acid Positions</b>
		<input type="text" id="positions" value="145,155,156,158,159,189"></textarea><br/>
	</fieldset>
</div><br/>

<a class="wd-Button" id="submit">Search</a>
</form>

<br/>
<div class="wd-Alert" id="wait">
Please wait, identification in progress...
</div>

<div id="results">
	<div id="results-data-div">
		<h3 id="results-data-title">Submitted Antigenic Motif(s)</h3>
		<div id="results-data"></div>
		<a href="javascript:;" id="grab-results">Download Motif Data</a><br/><br/>
	</div>
	<div id="results-data-freq-div">
		<h3 id="results-data-sum-title">Detection Frequency of Submitted Antigenic Motif(s)</h3>
		<div id ="results-motifs"></div>
		<a href="javascript:;" id="grab-frequency-results">Download Detection Frequency Data</a><br/><br/>
	</div>
	<div id="freq-err"></div>
</div>
<script>

$(document).ready(function() {
	//Hide wait
	
	//in development:
	$("#proportionOption").hide();
	
	$("#wait").hide();
	$("#wrapper").hide();
	$("#options").hide();
	$("#results-data-title").hide();
	$("#results-data-sum-title").hide();
	$("#update-graph").hide();
	$("#return-graph").hide();
	$("#grab-results").hide();
	$("#grab-frequency-results").hide();
	parse();
	$("#account-by-proportion").click(accountByProportion);
        $("#submit").click(getResult);
	$("#grab-all-detected").click(function() {
		downloadResult("frequency",motif_array.slice(1),"detection_frequency_of_all_motifs.csv");
	});
});

function reset(){
	$("#results-data-title").hide();
	$("#results-data-sum-title").hide();
	$("#update-graph").hide();
	$("#return-graph").hide();
	$("#grab-results").hide();
	$("#grab-frequency-results").hide();
	$("#results-motifs").hide();
	$("#freq-err").empty();	
}

function toggleOptions() {
	var x = document.getElementById("options");	
	if (x.style.display === "none"){
		x.style.display = "block";
	}
	else {
		x.style.display = "none";
	}	
	return;
}

function parse() {
	//Retrieve existing aggregate of motif frequency by year
	$.ajax({
		url: '/motif.json',
		dataType: 'json', 
		success: function(data, status){
			var normalize = false;
			motif_data = data;
			motif_array = reformat(Object.entries(motif_data));
			motif_array = cutoff(motif_array);
			var proportionCheckbox = document.getElementById("account-by-proportion");
			if(proportionCheckbox.checked){
				normalize = true;
			}
			plot_motifs(motif_array,normalize);
		},
		error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err );
		}
	});	
}

function reformat(motif_data){
	//reformat into one array for each motif
	var motif_array = [];
	motif_data.forEach(reconstruct_array);
	function reconstruct_array(item, index) {
		motif_array.push([Object.values(item)[0]].concat(Object.values(item[1])));
	}	
	console.log(motif_array);
	return(motif_array);
}

function normalize(motif_array){
	//console.log(motif_array[1][14]);
	num_yrs = motif_array[1].length - 1;
	num_motifs = motif_array.length - 1;
	//console.log(num_motifs);
	
	check1 = motif_array	
	console.log(check1);
	
	//create array of totals for each year
	yearly_totals = new Array(num_yrs).fill(0);
	console.log(yearly_totals);
	//loop through each year
	for (var i = 1; i <= num_yrs; i++){
		//loop through each motif for that year
		for (var j=1; j <= num_motifs; j++){
			yearly_totals[i-1] += motif_array[j][i]
		}
	}
	//console.log(yearly_totals);

	console.log(motif_array);	

	normal_motif_array = motif_array;

	//turn counts into proportions		
	for (var i = 1; i <= num_yrs; i++){
		//loop through each motif for that year
		for (var j=1; j <= num_motifs; j++){
			normal_motif_array[j][i] = (motif_array[j][i] / yearly_totals[i-1]).toFixed(3);
		}
	}
	console.log(motif_array);
	console.log(normal_motif_array);

	return normal_motif_array;
}

function cutoff(motif_array){
	var motif_freq_cutoff = 9;
	var new_motif_array = [];
	//push date header into subset array
	new_motif_array.push(motif_array[0])
	//skip header (dates)
	var i = 1;
	var j = 1;
	for (i; i < motif_array.length;i++){
		for (j=1; j < motif_array[i].length; j++){
			if (motif_array[i][j] >= 9){
				new_motif_array.push(motif_array[i]);
				break;
			}
		}
	}
	return new_motif_array;		
}

//create time series chart from yearly motif counts
function plot_motifs(motif_array, account_by_proportion){
	var groups = [];
	var types = {};
	var yaxis = {padding: {bottom:0}};
	
	console.log(account_by_proportion);
	//var account_by_proportion = true;
	//account_by_proportion = true;
	var plot_motif_array = [];
	if (account_by_proportion){
		plot_motif_array = normalize(motif_array);
		groups[0] = [];
		console.log(groups);
		for (i=1;i < plot_motif_array.length;i++){
			groups[0].push(plot_motif_array[i][0]);
			types[plot_motif_array[i][0]] = "area"
		}
		yaxis = { max: 1,tick:{ format: d3.format('%')}, padding: {top:0,bottom:0} } 
	}
	else {
		plot_motif_array = motif_array;
		console.log(plot_motif_array);
	}
	
	var chart = c3.generate({
		bindto: '#summaryChart',
			data: {
				x: 'x',
				columns: plot_motif_array,
				//to account by proportion, must set up groups and types for all motifs
				groups: groups,
				types: types
				/*	{ NYHNYK: 'area',
					NHNDYR: 'area',
					KTHNFK: 'area',
					NYNNYK: 'area'}*/
			},
			axis: {
				x: {
					type: 'timeseries',
					tick: {
						format: '%Y-%m-%d'
					}	
				},
				y: yaxis
			/*	y: {
					tick: {
						format: d3.format('%')
					}
				}*/
			}
	});
}

function accountByProportion(){
	console.log("Accounting");
	console.log(motif_array);
	plot_motifs(motif_array, true);
}

function getResult() {
	reset();
	$("#wait").slideDown("slow");
	$("#sequences").prop("disabled", true);	
	//disconnect button
	$("#submit").unbind("click");
	

	//process fasta input into specific object structure
	var fastaString = $("#sequences").val();
	var blastType = $('input[name=blasttype]:checked').val();
 	var positions = $('#positions').val();
	console.log(positions);
	standard_motif = false;
	if (positions == "145,155,156,158,159,189") {
		standard_motif = true;
	}
	positions = positions.split(",");

	//check whether to include signal peptide offset	
	var offsetCheckbox = document.getElementById("signalpeptide");
	var offsetincluded = offsetCheckbox.checked;	
	
	console.log(fastaString)
	var splitString = fastaString.split("\n");
	var j = -1;
	var fastaList = new Array();
	//creates array of sequences, with element 0 - header and 1 - seq 
	//sequences MUST be in FASTA format
	for (var i = 0; i < splitString.length; i++) {
		//Check if line is header
		if (splitString[i][0] == '>') {
			j++;
			fastaList[j] = [splitString[i],''];
		}
		else {
			if (j != -1) {
				fastaList[j][1] += splitString[i];
			}
		}
	}	
	
	if (j == -1){
		var error = "Please check that sequence(s) are in fasta format."
		$("#results").html(error);
		$("#wait").hide();
		return;
	}

	//Check that there are no DNA strings, and if so convert to most likely inframe DNA
	for (var i = 0; i < fastaList.length; i++) {
                //if(fastaList[i][1].match("^[ATCG]+$"))
                if(/^[atgcrykmswbdhvn]+$/.test(fastaList[i][1].toLowerCase()))
                {
                        console.log("DNA detected, translating.");
                        //Translate in all three frames and select with least # stop codons
                        var frame0 = convertToAminoAcid(fastaList[i][1], frame = 0);
                        var frame1 = convertToAminoAcid(fastaList[i][1], frame = 1);
                        var frame2 = convertToAminoAcid(fastaList[i][1], frame = 2);
                        var stopcount0 = (frame0.match(/\*/g) || []).length;
                        var stopcount1 = (frame1.match(/\*/g) || []).length;
                        var stopcount2 = (frame2.match(/\*/g) || []).length;
                        var finalFrame = "";
                        if (stopcount0 < stopcount1 & stopcount0 < stopcount2) finalFrame = frame0;
                        if (stopcount1 < stopcount0 & stopcount1 < stopcount2) finalFrame = frame1;
                        if (stopcount2 < stopcount1 & stopcount2 < stopcount0) finalFrame = frame2;
                        fastaList[i][1] = finalFrame.toUpperCase();
               }
        }	

	//Build array of the antigenic motifs for each sequence inputted
	var motifArray = [];
	var motifSummaryArray = [];
	var motifObject = {};
	var containsInvalidPosition = false;
	fastaList.forEach(getMotif);
	function getMotif(item, index){
		motif = "";
		sequence = item[1];
		positions.forEach(addToMotif);
        	function addToMotif(pos_item, pos_index){
                	pos_item = Number(pos_item)
                	if (offsetincluded){
                        	pos_item = pos_item + 16;
                	}
                	pos_item = pos_item - 1;
                	if (sequence[pos_item] !== undefined){
				motif = motif.concat(sequence[pos_item]);
			}
			else{
				containsInvalidPosition = true;
				console.log("INVALID POSITION");
				return;			
			}
        	}
		motifArray.push([item[0].replace('>',''),motif]);
		//build motif summary array
		if (motif !== ""){
			motifObject[motif] = motif_data[motif]
		}	
	}			
	console.log(motif_data);
	console.log(motifObject);
	console.log(Object.entries(motifObject));
	console.log(motifArray);

	

	//error handling for invalid aa positions
	if(containsInvalidPosition){
		var error = "Error: At least one amino acid position is invalid";
		$("#results").html(error);
		$("#wait").hide();
		return;
	}

	//Display table of fasta headers and motifs
	function createTable(tableData) {
		var table = document.createElement('table');
		table.setAttribute("id", "MotifTable");
  		var tableHeader = document.createElement('thead');
		var col1 = document.createElement('th');
		col1.appendChild(document.createTextNode("Strain"));
		tableHeader.appendChild(col1);
		var col2 = document.createElement('th');
		col2.appendChild(document.createTextNode("Antigenic Motif"));
		tableHeader.appendChild(col2);
		var tableBody = document.createElement('tbody');

		tableData.forEach(function(rowData) {
    			var row = document.createElement('tr');

    			rowData.forEach(function(cellData) {
      				var cell = document.createElement('td');
      				cell.appendChild(document.createTextNode(cellData));
      				row.appendChild(cell);
				//row.style.border = 'solid';
   			 });

    			tableBody.appendChild(row);
  		});

		table.appendChild(tableHeader);
  		table.appendChild(tableBody);
  		table.className = 'wd-Table--striped wd-Table--hover';
		document.body.appendChild(table);
		return(table);
	}
	var motifTable = createTable(motifArray);

	//creates detection frequency summary table from submitted motifs
	function createSummaryTable(tableData) {
		var table = document.createElement('table');
		var tableHeader = document.createElement('thead');
		var col1 = document.createElement('th');
		col1.appendChild(document.createTextNode("Antigenic Motif"));
		tableHeader.appendChild(col1);

		//get years from motif data
		yearsArray = Object.values(motif_data['x']);
		console.log(yearsArray);
		yearsArray.forEach(colData);
		function colData(item,index) {
			var col2 = document.createElement('th');
			col2.appendChild(document.createTextNode(item.split('-')[0]));
			tableHeader.appendChild(col2);		
		}
		var tableBody = document.createElement('tbody');

		tableData.forEach(function(rowData) {
    			var row = document.createElement('tr');
			var cell = document.createElement('td');
			cell.appendChild(document.createTextNode(rowData[0]));
			row.appendChild(cell);

		
			rowData[1].forEach(function(cellData) {
      				var cell = document.createElement('td');
      				cell.appendChild(document.createTextNode(cellData));
      				row.appendChild(cell);
				//row.style.border = 'solid'; 
			});

    			tableBody.appendChild(row);
  		});

		table.appendChild(tableHeader);
  		table.appendChild(tableBody);
  		table.className = 'wd-Table--striped wd-Table--hover';
		document.body.appendChild(table);
		return(table);
	}
	
	freqArray = Object.entries(motifObject)
	console.log(freqArray);
	var freqTable = "";
	//error handling for non-standard motif position selection
	if (standard_motif) {
		//error handling for motifs without any VDL detection frequency
		try {
			freqTable = createSummaryTable(freqArray);
		}
		catch (error) {
			$("#freq-err").html("There is no detection frequency data for at least one of your motifs");
			console.log(error);
			standard_motif = false;
			//return;
		}
	}	
	returnData(motifTable,motifArray,freqTable,freqArray);
	return;
}



function returnData(motifTable, motifArray, freqTable,freqArray) {
	//allows user to download motif table as CSV
	if (standard_motif) {
		$("#update-graph").show();
		freqArrayForGraph = reformat(freqArray);
		freqArrayForGraph.unshift(motif_array[0]);
	};
	$("#grab-results").show();
	$("#results-data-title").show();

	$("#results-data").html(motifTable);
	if (standard_motif == true) {
		$("#grab-frequency-results").show();
		$("#results-data-sum-title").show();
		$("#results-motifs").html(freqTable);
		$("#results-motifs").show();
	}

	//updates graph with submitted motifs when clicked
	$("#update-graph").click(updateGraph);
	function updateGraph(){
		//freqArray = reformat(freqArray);
		//freqArray.unshift(motif_array[0]);
		plot_motifs(freqArrayForGraph, false);
		//$('html, body').animate({ scrollTop: 0}, 'fast');
		$("#update-graph").hide();
		$("#return-graph").show();
		$("#summaryChartTitle").html("H3 Antigenic Motif Detection Frequency - Submitted Motifs");
	}

	//returns graph to common motifs when clicked
	$("#return-graph").click(function() {
		$("#update-graph").show();
		$("#return-graph").hide();
		plot_motifs(motif_array,false);
		$("#summaryChartTitle").html("H3 Antigenic Motif Detection Frequency - Common Motifs");
		});

	$("#grab-results").click(function() {
		downloadResult("motif", motifArray, "submitted_antigenic_motifs.csv")});
	$("#grab-frequency-results").click(function() {
		downloadResult("frequency", freqArray, "detection_frequency_of_submitted_motifs.csv")});
	setTimeout(function() {
	$("#wait").slideUp("slow");
	$("#sequences").prop("disabled", false);

	//reconnect button
	$("#submit").click(getResult);	

	}, 10);
}

//download CSV file of chosen results
function downloadResult(type,dataArray,filename){
	text = "";
	if (type == "motif"){
		text = "Strain,Motif\n";
	}
	if (type == "frequency"){
		var yearsArray = motif_array[0];
		text = "Antigenic Motif," + yearsArray.slice(1) + "\n";
	}
	//text = "Strain,Motif\n"
	dataArray.forEach(convertToCSV)
	function convertToCSV(item, index){
		text = text.concat(item.toString());
		text = text.concat("\n");
		console.log(text)
	}
	//var text = dataArray[0].toString();
	//console.log(text);
	download(filename,text);
}



</script>



    
<?php
$theme->drawFooter();
