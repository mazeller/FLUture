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

<p>
Sequences for cases submitted through the USDA<a href ="https://www.aphis.usda.gov/aphis/ourfocus/animalhealth/animal-disease-information/swine-disease-information/ct_swine_health_monitoring_surveillance">swine surveillance system</a> are available through i<a href="https://www.ncbi.nlm.nih.gov/pubmed/23193287">GenBank</a>. The United States Department of Agriculture (USDA) barcodes of these cases are provided for your convenience, and can be quickly accessed through a keyword search in either <a href="https://www.ncbi.nlm.nih.gov/genomes/FLU/Database/nph-select.cgi?go=database">GenBank’s Influenza Virus Resource Tool (IVR)</a> or the <a href="https://www.fludb.org/">Influenza Research Database (IRD)</a>.
</p>

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
