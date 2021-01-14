<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->addStyle(<<<CSS
#wrapper {
    width: 100%;
    overflow: hidden;
}
.chartChild {
    width: 50%;
    float:left;
}
CSS
, 'style');
$theme->drawHeader();
?>

<h2 id="chartTitle">Multi Sequence Identity Tool</h2>

<p>
The HA-NA sequence identity tool uses BLAST to find similar hemagglutinin/neuraminidase sequences in the ISU VDL data. Results are limited to greater than 96% identity, with at max 10 results returned per query sequence. For a more complex analysis, please refer to the <a href="https://www.fludb.org/brc/blast.spg?method=ShowCleanInputPage&decorator=influenza">IRD BLAST tool</a>. 
</p>

<form id="target">
<textarea rows="16" cols="100" id="sequences" placeholder="Paste sequences (fasta/plain text)">
</textarea><br/>
<b>Sequence type</b><br/>
<input type="radio" name="blasttype" value="nt" checked="checked" />nucleotide<br>
<input type="radio" name="blasttype" value="aa" />amino acid<br>
<label class="wd-Button--default">
        <input id="fileUploader" type="file" name="filename" hidden/>
        <i class="fa fa-cloud-upload"></i>Upload
</label>
<a class="wd-Button" id="submit">Search</a>
<a href="javascript:;" class="wd-Button--success" id="download">Download Results Data</a> 
</form>

<br/>
<div class="wd-Alert" id="waitUpload">
Please wait, Sequence File is being uploaded...
</div>
<br/>
<div class="wd-Alert" id="wait">
Please wait, BLAST in progress...
</div>

<!-- <div id="wrapper">
	<h2>Influenza cases in ISU FLUture with 96% or greater similarity to query sequence</h2>
	<div class="chartChild">
		<h3>State of Detection</h3>
		<div id="stateChart" class="chartChild"></div>
	</div>
	<div class="chartChild">
		<h3>Paired Neuraminidase</h3>
		<div id="naChart" class="chartChild"></div>
	</div>
</div> -->
<div id="results">

</div>
<br/>
<div>
<small>
<h3>References</h3>
<ol>
<!--<li>Altschul, S.F., Gish, W., Miller, W., Myers, E.W. & Lipman, D.J. (1990) "Basic local alignment search tool." J. Mol. Biol. 215:403-410.</li>
<li>Zhang Z., Schwartz S., Wagner L., & Miller W. (2000), "A greedy algorithm for aligning DNA sequences" J Comput Biol 2000; 7(1-2):203-14.</li>
<li>Chang, J., Anderson, T.K., Zeller, M.A., Gauger, P.C., and Vincent, A.L. (2019). “octoFLU: Automated classification to evolutionary origin of influenza A virus gene sequences detected in U.S. swine” Microbiology Resource Announcements 8(32), e00673-19. <a href="https://github.com/flu-crew/octoFLU">Github repo.</a></li>-->
<li>Altschul, S. F., Gish, W., Miller, W., Myers, E. W., & Lipman, D. J. (1990). Basic local alignment search tool. Journal of molecular biology, 215(3), 403-410.</li>
<li>Zhang, Z., Schwartz, S., Wagner, L., & Miller, W. (2000). A greedy algorithm for aligning DNA sequences. Journal of Computational biology, 7(1-2), 203-214.</li>
<li>Chang, J., Anderson, T. K., Zeller, M. A., Gauger, P. C., & Vincent, A. L. (2019). octoFLU: Automated Classification for the Evolutionary Origin of Influenza A Virus Gene Sequences Detected in US Swine. Microbiology resource announcements, 8(32), e00673-19. <a href="https://github.com/flu-crew/octoFLU">Github repo.</a></li>

</ol>
</small>
</div>
<script>

$(document).ready(function() {
	//Hide wait
	$("#wait").hide();
        $("#waitUpload").hide();
	$("#wrapper").hide();
	$("#download").hide();
	$("#upload").on("click". uploadData);
        $("#submit").on("click", getBlastResult);
        document.getElementById('fileUploader').addEventListener('change', addDataToTextField, false);
        document.getElementById('download').addEventListener('click', download, false);
});

function download() {
	var element = document.createElement('a');
	element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(csvData));
	element.setAttribute('download', 'data.csv');

	element.style.display = 'none';
	document.body.appendChild(element);

	element.click();

	document.body.removeChild(element);
}

function addDataToTextField(data) {
 	var fileName = document.getElementById("fileUploader").files[0];
	var fileData;

	if(fileName) {
		var reader = new FileReader();
		reader.onload = function(loadedEvent) {
			fileData = loadedEvent.target.result;
			document.getElementById("sequences").value = fileData;
		}
		reader.readAsText(fileName);
	} else {
		console.log("Failed to load file!");
	}

	if(!fileData) {
		console.log("We didn't get any data");
	}

        //Force pause
        setTimeout(function() {
                //Hide wait
                $("#waitUpload").slideUp("slow");
                $("#sequences").prop("disabled", false);
                //Reconnect button
                $("#upload").on("click", uploadData);
        }, 1000);
}

function uploadData () {
        //Hide form, show wait
        $("#waitUpload").slideDown("slow");
        $("#wrapper").show();
        $("#sequences").prop("disabled", true);
        $("#download").hide();

        //Disconnect button
        $("#upload").attr("disabled", false);
        $("#download").attr("disabled", false);

        //Read File and update sequences
	document.getElementById("fileUploader").click();
}

function getBlastResult() {
	//Hide form, show wait
	$("#wait").slideDown("slow");
	$("#wrapper").show();
	$("#sequences").prop("disabled", true);
	
	//Disconnect button
        $("#upload").off("click");
        $("#download").off("click");
        $("#submit").off("click");

        //Process fasta input into specific object structure
        var fastaString = $("#sequences").val();
	var blastType = $('input[name=blasttype]:checked').val(); 

        //Request data
        $.ajax({
                url: '/getmultisequenceblastresult.php',
                type: 'post',
                data: {'seq': fastaString, 'blast': blastType},
                success: function(data, status) {
                        var data = $.parseJSON(data);
                        var tableData = data[0];
                        //Global variable to download data
                        csvData = data[1];

                        resultData(tableData);

			//$("#download").click(function() {
			//	download("data.csv",csvData);
		        //});

	                $("#download").on("click", download);
			$("#download").show();
                },
                error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
			$("#results").html("Server Error");
			}
        });

        return;
}

function resultData(data) {
	//Show results as soon as they come in
	$("#results").html(data);

	//Force pause
	setTimeout(function() {

	//Hide wait
        $("#wait").slideUp("slow");
	$("#sequences").prop("disabled", false);

	//Reconnect button	
	$("#submit").on("click", getBlastResult);
	}, 1000);
}

</script>
<?php
$theme->drawFooter();


