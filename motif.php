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


<h2 id="chartTitle">Amino Acid Sequence Motif Viewing Tool</h2>
<p>
The Amino Acid Sequence Motif Viewing Tool identifies the amino acid residue present at the position(s) indicated for the 
nucleotide or amino acid sequence(s) inputted. Regex determines if nucleotide sequence is present and needs to be translated to amino acid. </p>

<form id="target">
<textarea rows="16" cols="100" id="sequences" placeholder="Paste sequence(s) in fasta format">
</textarea><br/><br/>
<h4>Options:</h4>
<div id="options">
	<fieldset>
		<b>Signal Peptide Length</b><br/>
		<input type="text" id="signalpeptide" name="signalpeptide" placeholder="H1=17,H3=16"><br/>
		<b>Amino Acid Positions</b><br/>
		<input type="text" id="positions" value="145,155,156,158,159,189"></textarea><br/>
	</fieldset>
</div><br/>

<a class="wd-Button" id="submit">Submit</a>
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
	<div id="error-report"></div>
</div>
<script>

$(document).ready(function() {
	//Hide wait
	
	//in development:
	$("#proportionOption").hide();
	
	$("#wait").hide();
	$("#wrapper").hide();
	$("#options").show();
	$("#results-data-title").hide();
	$("#results-data-sum-title").hide();
	$("#grab-results").hide();
        $("#submit").click(getResult);
});

function reset(){
	$("#results-data-title").hide();
	$("#results-data-sum-title").hide();
	$("#grab-results").hide();
	$("#grab-frequency-results").hide();
	$("#results-motifs").hide();
	$("#results-data").empty();
	$("#freq-err").empty();	
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
	positions = positions.split(",");

	//check whether to include signal peptide offset	
	//var offsetCheckbox = document.getElementById("signalpeptide");
	//var offsetincluded = offsetCheckbox.checked;	
	
	var offset = $('#signalpeptide').val();
	console.log(offset);	

	//console.log(fastaString)
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
		var fasta_error = "Error: Please check that sequence(s) are in fasta format."
		$("#error-report").html(fasta_error);
		$("#wait").hide();
		setTimeout(function() {
		$("#wait").slideUp("slow");
		$("#sequences").prop("disabled", false);
		//reconnect button
		$("#submit").click(getResult);	
		}, 10);
		return;
	}

	//Check for invalid characters
	for (var i = 0; i < fastaList.length; i++) {
		if(/^[A-Za-z]+$/.test(fastaList[i][1])) {
			console.log("All valid characters");
		}
		else { 
			console.log("Invalid character detected");
			var invalid_char_error = "Error: Invalid character detected in sequence input."
			$("#error-report").html(invalid_char_error);
			$("#wait").hide();
			setTimeout(function() {
			$("#wait").slideUp("slow");
			$("#sequences").prop("disabled", false);
			//reconnect button
			$("#submit").click(getResult);	
			}, 10);
			return;
		}
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
                	pos_item = Number(pos_item) + Number(offset) - 1;
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

	//error handling for invalid aa positions
	if(containsInvalidPosition){
		var position_error = "Error: At least one requested amino acid position is invalid";
		$("#error-report").html(position_error);
		$("#wait").hide();
		setTimeout(function() {
		$("#wait").slideUp("slow");
		$("#sequences").prop("disabled", false);
		//reconnect button
		$("#submit").click(getResult);	
		}, 10);
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
		col2.appendChild(document.createTextNode("Motif"));
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

	
	freqArray = Object.entries(motifObject)
	var freqTable = "";	
	returnData(motifTable,motifArray,freqTable,freqArray);
	return;
}



function returnData(motifTable, motifArray, freqTable,freqArray) {
	//allows user to download motif table as CSV
	$("#grab-results").show();
	$("#results-data-title").show();
	$("#results-data").html(motifTable);
	$("#grab-results").off();
	$("#grab-results").click(function() {
		downloadResult("motif", motifArray, "submitted_antigenic_motifs.csv")});
	setTimeout(function() {
	$("#wait").slideUp("slow");
	$("#sequences").prop("disabled", false);
	//reconnect button
	$("#submit").click(getResult);	
	}, 10);
}

//download CSV file of chosen results
function downloadResult(type,dataArray,filename){
	console.log("hit!");
	text = "";
	if (type == "motif"){
		text = "Strain,Motif\n";
	}
	dataArray.forEach(convertToCSV)
	function convertToCSV(item, index){
		text = text.concat(item.toString());
		text = text.concat("\n");
		//console.log(text)
	}
	download(filename,text);
}



</script>



    
<?php
$theme->drawFooter();
