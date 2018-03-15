<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->drawHeader();
?>

<h2 id="chartTitle">BLAST Tool</h2>

<div class="wd-Alert" id="wait">
Please wait, BLAST in progress...
</div>
<form id="target">
<textarea rows="16" cols="100" id="sequences" placeholder="Paste sequences (fasta/plain text)">
</textarea>
<b>BLAST type</b><br/>
<input type="radio" name="blasttype" value="nt" checked="checked" />nucleotide<br>
<input type="radio" name="blasttype" value="aa" />amino acid<br>
<a class="wd-Button" id="submit">BLAST</a>
</form>

<div id="results">

</div>

<script>

$(document).ready(function() {
	//Hide wait
	$("#wait").hide();
        $("#submit").click(getBlastResult);
});

function getBlastResult() {
	//Hide form, show wait
	$("#wait").slideDown("slow");
	$("#target").slideUp("slow");

        //Process fasta input into specific object structure
        var fastaString = $("#sequences").val();
	var blastType = $('input[name=blasttype]:checked').val(); 
	console.log(blastType);
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
                }
        });

        return;
}

function returnData(data) {
	//Hide wait
        $("#wait").slideUp("slow");
	$('#results').html(data);
}

</script>
<?php
$theme->drawFooter();


