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

<h2 id="chartTitle">HA Sequence Identity Tool</h2>

<p>
The HA sequence identity tool uses BLAST to find similair hemagglutinin sequences in the ISU VDL data. Results are limited to greater than 98% identity, with at max 100 results returned. For a more complex analysis, please refer to the <a href="https://www.fludb.org/brc/blast.spg?method=ShowCleanInputPage&decorator=influenza">IRD BLAST tool</a>. 
</p>

<form id="target">
<textarea rows="16" cols="100" id="sequences" placeholder="Paste sequences (fasta/plain text)">
</textarea><br/>
<b>Sequence type</b><br/>
<input type="radio" name="blasttype" value="nt" checked="checked" />nucleotide<br>
<input type="radio" name="blasttype" value="aa" />amino acid<br>
<a class="wd-Button" id="submit">Search</a>
</form>

<br/>
<div class="wd-Alert" id="wait">
Please wait, BLAST in progress...
</div>

<div id="wrapper">
	<h2>Influenza cases in ISU FLUture with 98% or greater similarity to query sequence</h2>
	<div class="chartChild">
		<h3>State of Detection</h3>
		<div id="stateChart" class="chartChild"></div>
	</div>
	<div class="chartChild">
		<h3>Paired Neuraminidase</h3>
		<div id="naChart" class="chartChild"></div>
	</div>
</div>
<div id="results">

</div>
<br/>
<div>
<small>
<h3>References</h3>
<ol>
<li>Altschul, S.F., Gish, W., Miller, W., Myers, E.W. & Lipman, D.J. (1990) "Basic local alignment search tool." J. Mol. Biol. 215:403-410.</li>
<li>Zhang Z., Schwartz S., Wagner L., & Miller W. (2000), "A greedy algorithm for aligning DNA sequences" J Comput Biol 2000; 7(1-2):203-14.</li>
</ol>
</small>
</div>
<script>

$(document).ready(function() {
	//Hide wait
	$("#wait").hide();
	$("#wrapper").hide();
        $("#submit").click(getBlastResult);
});

function getBlastResult() {
	//Hide form, show wait
	$("#wait").slideDown("slow");
	$("#wrapper").show();
	$("#sequences").prop("disabled", true);
	
	//Disconnect button
	$("#submit").unbind("click");

        //Process fasta input into specific object structure
        var fastaString = $("#sequences").val();
	var blastType = $('input[name=blasttype]:checked').val(); 

        //Request data
        $.ajax({
                url: '/getblastresult.php',
                type: 'post',
                data: {'seq': fastaString, 'blast': blastType},
                success: function(data, status) {
                        var data = data;
                        returnData(data);
                },
                error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
			$("#results").html("Server Error");
			}
        });

        return;
}

function returnData(data) {
	//Show results as soon as they come in
	$("#results").html(data);

	//Force pause
	setTimeout(function() {

	//Hide wait
        $("#wait").slideUp("slow");
	$("#sequences").prop("disabled", false);

	//Reconnect button	
	$("#submit").click(getBlastResult);
	}, 1000);
}

</script>
<?php
$theme->drawFooter();


