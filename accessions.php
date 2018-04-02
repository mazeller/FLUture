<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/dataloader.js","/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/c3.min.css');
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->drawHeader();
?>
<h2>Retrieve Accessions</h2>
<div>
	<a href="javascript:;" id="grabData" class="wd-Button--default">Download Graph Data</a>
</div>

<script>
//Global access to data
var data;

//Page load
$(document).ready(function() {
       //Load in datddda one time
	requestData();   

	//Bind button to csv generating function 
	$("#grabData").click(grabData);    
});

//Preload accessions
function requestData() {
    //Preoload genbank accession data
    getAccessions(parse);
}

//Download Data Summaries
function grabData() {
    download("data.csv",data);
}

//Pull out data specific to Type xData State
function parse(rdata) {
	data = rdata;
}

var data = "";

</script>
<?php
$theme->drawFooter();
