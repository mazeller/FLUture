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
	<h2 id="summaryChartTitle">H3 Antigenic Motif Detection Frequency</h2>
	<div id="summaryChart"></div>
	<div id="proportionOption">
		<input type="checkbox" id="account-by-proportion"/>Account by proportion<br/>
	</div>
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
	<div id="results-data"></div>
	<a id="grab-results">Download Motif Data</a>
</div>
<script>

$(document).ready(function() {
	//Hide wait
	
	//in development:
	$("#proportionOption").hide();

	$("#wait").hide();
	$("#wrapper").hide();
	$("#options").hide();
	$("#grab-results").hide();
	parse();
	$("#account-by-proportion").click(parse);
        $("#submit").click(getResult);
});

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
			motif_data = data;
			motif_array = summarize_existing(Object.entries(motif_data));
			var proportionCheckbox = document.getElementById("account-by-proportion");
			if(proportionCheckbox.checked){
				console.log("normalizing");
				motif_array = normalize(motif_array);
			}
			plot_motifs(motif_array);
		},
		error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err );
		}
	});

	function summarize_existing(motif_data){
		console.log(motif_data);
		var cutoff = motif_data.length * 0.01
		console.log(cutoff);

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
		console.log(num_motifs);
		
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
		console.log(yearly_totals);

		//turn counts into proportions		
		for (var i = 1; i <= num_yrs; i++){
			//loop through each motif for that year
			for (var j=1; j <= num_motifs; j++){
				motif_array[j][i] = (motif_array[j][i] / yearly_totals[i-1] * 100).toFixed(3);
			}
		}
		console.log(motif_array);

		return motif_array;
	}

	//create time series chart from yearly motif counts
	function plot_motifs(motif_array){
		var groups = [];
		var types = {};
		var chart = c3.generate({
    			bindto: '#summaryChart',
    				data: {
        				x: 'x',
					columns: motif_array,
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
				/*	y: {
						tick: {
							format: d3.format('%')
						}
					}*/
    				}
		});
	}
}

function getResult() {
	$("#wait").slideDown("slow");
	$("#sequences").prop("disabled", true);	
	//disconnect button
	$("#submit").unbind("click");
	

	//process fasta input into specific object structure
	var fastaString = $("#sequences").val();
	var blastType = $('input[name=blasttype]:checked').val();
 	var positions = $('#positions').val();
	positions = positions.split(",");	
	

	//error handling if positions are not separated by commas
	/*if ( positions.indexOf(',') != -1) {
		positions = positions.split(",");		
	}
	else {
		var error = "Please check that amino acid positions are separated with commas."
		returnData(error);
		return;		
	}
	*/

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
		returnData(error);
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
	}			
	console.log(motifArray);

	//error handling for invalid aa positions
	if(containsInvalidPosition){
		var error = "Error: At least one amino acid position is invalid";
		returnData(error);
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
	returnData(motifTable,motifArray);
	return;
}



function returnData(dataTable, dataArray) {
	//allows user to download motif table as CSV
	$("#grab-results").show();
	$("#grab-results").click(downloadResult);
	function downloadResult(){
		text = "Strain,Motif\n"
		dataArray.forEach(convertToCSV)
		function convertToCSV(item, index){
			text = text.concat(item.toString());
			text = text.concat("\n");
			console.log(text)
		}
		//var text = dataArray[0].toString();
		//console.log(text);
		download("motifs.csv",text);
	}

	$("#results-data").html(dataTable);
	
	setTimeout(function() {
	$("#wait").slideUp("slow");
	$("#sequences").prop("disabled", false);

	//reconnect button
	$("#submit").click(getResult);	

	}, 10);
}




</script>


    
<?php
$theme->drawFooter();
