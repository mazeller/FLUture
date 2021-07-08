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

.lds-ring {
  display: inline-block;
  position: relative;
  width: 40px;
  height: 40px;
}
.lds-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 32px;
  height: 32px;
  margin: 4px;
  border: 4px solid #ff0;
  border-radius: 50%;
  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: #f00 transparent transparent transparent;
}
.lds-ring div:nth-child(1) {
  animation-delay: -0.45s;
}
.lds-ring div:nth-child(2) {
  animation-delay: -0.3s;
}
.lds-ring div:nth-child(3) {
  animation-delay: -0.15s;
}
@keyframes lds-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

CSS
, 'style');
$theme->drawHeader();
?>

<h2 id="chartTitle">HA Sequence Identity Tool</h2>

<p>
The HA sequence identity tool uses BLAST to find similair hemagglutinin sequences in the ISU VDL data. Results are limited to greater than 96% identity, with at max 100 results returned. For a more complex analysis, please refer to the <a href="https://www.fludb.org/brc/blast.spg?method=ShowCleanInputPage&decorator=influenza">IRD BLAST tool</a>. 
</p>

<form id="target">
<textarea rows="16" cols="100" id="sequences" placeholder="Paste sequences (fasta/plain text) in the following format:\n\n>defline1\nATCAAATTTTCCCCGGGG\n\n>defline2\nAAATTTTTCCCGGGCTGA">
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

<div class="lds-ring" id="spinner"><div></div><div></div><div></div><div></div></div>
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
        //Placeholder text multiline
        var textAreas = document.getElementsByTagName('textarea');

        Array.prototype.forEach.call(textAreas, function(elem) {
                elem.placeholder = elem.placeholder.replace(/\\n/g, '\n');
        });

	//Hide wait
	$("#wait").hide();
	$("#wrapper").hide();
	$("#spinner").hide();
        $("#submit").click(getBlastResult);
});

function getBlastResult() {
	//Hide form, show wait
	$("#wait").slideDown("slow");
	$("#wrapper").show();
        $("#results").html("");
        $("#spinner").show();
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
                        $("#spinner").hide();
		}
        });

        return;
}

function returnData(data) {
        $("#spinner").hide();
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


