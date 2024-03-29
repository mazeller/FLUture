<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/dataloader.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/c3.min.css');
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->drawHeader();
?>

<h2 id="chartTitle">Influenza PCR positive submissions per year</h2>
<div id="chart" style="height:300px"></div>
<h6 style="text-align:center;">(Last Record from <span id="lr"></span>)</h6>
<p>
ISU <em>FLU</em>ture is an interactive web-based tool developed to provide  diagnostic information from an Influenza A Virus database of test results, metadata, and sequences collected at the Iowa State University Veterinary Diagnostic Laboratory. The goal of ISU <em>FLU</em>ture is to allow veterinarians, swine producers, and researchers to seek out and find trends in the data that will allow them to make informed decisions regarding influenza and swine health. ISU <em>FLU</em>ture data is derived from diagnostic samples submitted from a diverse array of swine farms and production systems around the United States and North America. The metadata used at ISU <em>FLU</em>ture is dependent on submitters supplying accurate and thorough information provided on submission forms with diagnostic samples to the Iowa State University Veterinary Diagnostic Laboratory.
</p>

<h2>Recommended Browsers</h2>
<div>
	<img src="/img/browser_chrome.png" alt="Chrome">
	<img src="/img/browser_firefox.png" alt="Firefox">
	<img src="/img/browser_safari.png" alt="Safari">	
</div>
<br>

<h2>Suite of Tools</h2>
<p>
<a href='correlation.php'><strong>Correlations</strong></a><br>
Over 100 unique correlation graphs can be generated to search for trends from variables stored in the database.<br>
<a href='timeseries.php'><strong>Time Series</strong></a><br>
The incidence of the variables in the database can be viewed over a period of time<br>
<a href='regional.php'><strong>Regional</strong></a><br>
Case metadata may be plotted and explored by region and state.<br>
<a href='heatmap.php'><strong>Heat Map</strong></a><br>
Demonstrates the distribution of the hemagglutinin and neuraminidase subtype combinations over a period of time.<br>
<a href='identity.php'><strong>HA Identity Tool</strong></a><br>
Submitted sequences will be identified to HA genetic clade, matched gene segment, and similar sequences in FLUture are presented.<br>
</p>

<!--<a name="variables"><h2>Variables</h2></a>
<p>
The ISU <em>FLU</em>ture database curates information related to the individual swine cases. An explanation of each of the variables that ISU <em>FLU</em>ture allows searching for can be found below.<br/><br/>

<strong>Age</strong><br/>
The age of the pig at the time the flu positive sample was collected.
<ol class="wd-u-ListUnstyled" style="position: relative; left: 20px;">
	<li>Neonate: 0-5 days</li>
	<li>Suckling: 5-21 days</li>
	<li>Nursery: 3 – 10/11 weeks</li>
	<li>Grow/Finish: 10/11 – 26 weeks</li>
	<li>Adult: > 26 weeks</li>
</ol>

<strong>Bacterial Coinfection</strong><br/>
Bacterial culture results from submitted samples of influenza positive cases. Majority of samples are lung tissue.
<br/>

<strong>Data Source</strong><br/>
The stream that handled sequencing of the sample. Samples with cycle threshold (CT) values <span>&#8804;</span> 25 for lung and nasal swab and <span>&#8804;</span> 20 for oral fluid are routed through the USDA traceable or anonymous stream. Samples with CT values <span>&#8804;</span> 38 are processed by the ISU VDL stream. Samples with CT values above 38 are considered negative.
<br/>

<strong>Day</strong><br/>
The day of the year that the flu positive sample was collected.
<br/>

<strong>HA Clade</strong><br/>
The phylogenetic clade of a corresponding hemagglutinin sequence. Currently, the ISU <em>FLU</em>ture database tracks only H1 and H3 subtype hemagglutinin, thus clades will be derived from one of these subtypes.
<br/>

<strong>HA Sequence</strong><br/>
The genetic sequence of the hemagglutinin gene of a specific influenza virus by case. Sequencing restricted to samples with cycle threshold (CT) values <span>&#8804;</span> 38.
<br/>

<strong>Month</strong><br/>
The month of the year that the flu positive sample was collected.
<br/>

<strong>NA Clade</strong><br/>
The phylogenetic clade of a corresponding neuraminidase. Currently, the ISU <em>FLU</em>ture database tracks only N1 and N2 subtype neuraminidase, thus clades will be derived from one of these subtypes. Only samples with cycle threshold (CT) values <span>&#8804;</span> 25 are sequenced, and applicable for this type of data.
<br/>

<strong>PCR Specimen</strong><br/>
The specimen from which the subtyping RT-PCR was derived.
<br/>

<strong>Sequence Specimen</strong><br/>
The specimen used to attempt sequencing.
<br/>

<strong>Site State</strong><br/>
The state that the pig was located when the sample was collected for submission to the veterinary diagnostic laboratory.
<br/>

<strong>Subtype</strong><br/>
The subtype of the influenza virus, based on PCR detection of the hemagglutinin and neuraminidase proteins.
<br/>

<strong>Week</strong><br/>
The week of the year that the flu positive sample was collected.
<br/>

<strong>Year</strong><br/>
The year that the flu positive sample was collected.
<br/>
</p>
-->

<script>

//Page load
$(document).ready(function() {
        //Load in data one time
        requestData();
});

//Pull out data specific to Type xData State
function requestData() {
    getLastRecord(updateLastRecord);

    var xComponent = "counts";
    var yComponent = "counts";

    getJsonData(xComponent, yComponent, parse, flags="count");
}

//Update time
function updateLastRecord(dateLR) {
     // toUTCString date standard shows correct date, whereas toDateString looses 6 hours for CST/CDT due to being behind GMT thus the date is always one less
     // use UTC or ISO date standards
     dateString = new Date(dateLR);
     dateLR = dateString.toUTCString();
     $("#lr").text (dateLR);	
}

//Pull out data specific to Type xData State
function parse(rdata) {
    //Store data so only hit db once
    if(rdata.constructor.name != 'Array')
            rdata = data;
    data = rdata;

    //Create primary structure
    var flu_year = [];
    var flu_count = [];

    for (var key in rdata) {
        //Make sure x axis exists
	flu_count.push(rdata[key]["flu_count"]);
	flu_year.push(rdata[key]["flu_year"]);
    }
    flu_count.unshift("Flu Cases");

    var chart = c3.generate({
        data: {
            columns: [flu_count],
            type: 'bar',
            //groups: [flu_year]
        },
        axis: {
            x: {
                type: 'category',
                categories: flu_year,
                label: {
                        text: "Year",
                        position: 'outer-center',
                },
            },
            y: {
                label: {
                        text: "Cases",
                        position: 'middle',
                }
            }
        },
        grid: {
            y: {
                lines: [{
                    value: 0
                }]
            }
        },
	legend: {
            show: false,
    	}
    });
}

</script>
<?php
$theme->drawFooter();
