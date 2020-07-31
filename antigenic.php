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
<h2 id="summaryChartTitle">H3 Antigenic Motif Detection Frequency</h2>
<div id="summaryChart"></div>

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
		<input type="checkbox" name="signalpeptide" checked="checked"/>included in sequence (will offset positions by 16)<br/>
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
	$("#wait").hide();
	$("#wrapper").hide();
	$("#options").hide();
	$("#grab-results").hide();
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

var motif_json;
$.ajax({
	url: '/motif_update.php',
	type: 'post',
	data: motif_json,
	success: function(data, status) {
		var motif_data = JSON.parse(data);
		summarize_existing(Object.entries(motif_data));
		},
	error: function(xhr, desc, err) {
		console.log(xhr);
		console.log("Details: " + desc + "\nError:" + err);
		$("#results").html("Server Error");
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
	plot_motifs(motif_array);
}
function plot_motifs(motif_array){

var chart = c3.generate({
    bindto: '#summaryChart',
    data: {
        x: 'x',
	columns: motif_array.slice(0,15)
    },
    axis: {
        x: {
            type: 'timeseries',
            tick: {
                format: '%Y-%m-%d'
            }
        }
    }
});

}

/*
setTimeout(function () {
    chart.groups([['data1', 'data2', 'data3', 'data4']])
}, 1);
*/
function getResult() {
	$("#wait").slideDown("slow");
	$("#sequences").prop("disabled", true);
	
	//disconnect button
	$("#submit").unbind("click");
	

	//process fasta input into specific object structure
	var fastaString = $("#sequences").val();
	var blastType = $('input[name=blasttype]:checked').val();
 	var positions = $('#positions').val();
	if (1==1) { //positions.indexOf(',') != -1) {
		positions = positions.split(",");		
	}
	else {
		var error = "Please check that amino acid positions are separated with commas."
		returnData(error);
		return;		
	}
	//positions = positions.split(",");	
	var offsetincluded =$('input[name=signalpeptide]:checked').val();
	
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

	var motifArray = [];
	fastaList.forEach(getMotif);
	function getMotif(item, index){
		motif = "";
		sequence = item[1];
		/*if(blastType == 'nt'){
			sequence = convertToAminoAcid(sequence);
			sequence = sequence.toUpperCase();
			console.log("nucleotide converted");
		}*/
		positions.forEach(addToMotif);
        	function addToMotif(pos_item, pos_index){
                	pos_item = Number(pos_item)
                	if (offsetincluded){
                        	pos_item = pos_item + 16;
                	}
                	pos_item = pos_item - 1;
                	motif = motif.concat(sequence[pos_item]);
        	}
		motifArray.push([item[0].replace('>',''),motif]);	
	}			
	console.log(motifArray);

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
		//document.getElementById("MotifTable").style.border = "thick solid #0000FF";

  		table.className = 'wd-Table--striped wd-Table--hover';
		document.body.appendChild(table);
		return(table);
	}
	var motifTable = createTable(motifArray);	
	console.log(motifTable);
	returnData(motifTable,motifArray);
	
	return;
}



function returnData(dataTable, dataArray) {
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
