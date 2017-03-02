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

<!-- <p class="wd-Alert--error">
<strong>Warning:</strong> This website is currently under construction. Webpages and text may change between viewings. If you find a part of the site not working or information missing from this site, you may contact the developer through the Contact Us link.
</p> -->

<h2 id="chartTitle">Submitted Flu Positive Cases per Year</h2>
<div id="chart" style="height:300px"></div>

<p>
IS<sub>Flu</sub>View is an interactive web-based tool developed to provide  diagnostic information from an Influenza A Virus database of test results, metadata, and sequences collected at the Iowa State University Veterinary Diagnostic Laboratory. The goal of ISFluView is to allow veterinarians, swine producers, and researchers to seek out and find trends in the data that will allow them to make informed decisions regarding influenza and swine health. IS<sub>Flu</sub>View data is derived from diagnostic samples submitted from a diverse array of swine farms and production systems around the United States and North America. The metadata used at IS<sub>Flu</sub>View is dependent on submitters supplying accurate and thorough information provided on submission forms with diagnostic samples to the Iowa State University Veterinary Diagnostic Laboratory.
</p>

<!--<p>
IS<sub>Flu</sub>View is an interactive tool developed to provide statistical information from an Influenza A Virus database of diagnostic results, metadata, and sequences collected at the Iowa State University Veterinary Diagnostic Laboratory. The goal of IS<sub>Flu</sub>View is to allow veterinarians, swine producers, and researchers to seek out and find trends in the data that will allow them to make informed decisions about influenza and swine health. IS<sub>Flu</sub>View data is derived from samples submitted by both large- and small-scale swine farms around the United States. The completeness of the metadata used at IS<sub>Flu</sub>View is dependent on submitters supplying the information on submissions forms.
</p>-->

<h2>Suite of Tools</h2>
<p>
<strong>Correlations</strong><br>
Over 100 unique correlation graphs can be generated to search for trends from variables stored in the database.<br>
<strong>Time Series</strong><br>
The incidence of the variables in the database can be viewed over a period of time<br>
<strong>Regional</strong><br>
The incidence of flu positive cases that have been processed by the USDA/ISU-VDL can be viewed by geographic location, over a specified period of time.<br>
<strong>Heat Map</strong><br>
Demonstrates the distribution of the hemagglutinin and neuraminidase subtype combinations over a period of time.<br>
</p>

<h2>Variables</h2>
<p>
The IS<sub>Flu</sub>View database curates information related to the individual swine cases. An explanation of each of the variables that IS<sub>Flu</sub>View allows searching for can be found below.<br/><br/>

<strong>Age</strong><br/>
The age of the pig at the time the flu positive sample was taken.<br/>

<strong>Day</strong><br/>
The day of the year that the flu positive sample was taken.<br/>

<strong>HA Clade</strong><br/>
The phylogenetic clade that a flu positive sample is part of, based on the hemagglutinin sequence. Currently the IS<sub>Flu</sub>View database tracks only H1 and H3 subtype hemagglutinin, thus clades will be derived from one of these subtypes.<br/>

<strong>HA Sequence</strong><br/>
The genetic sequence of the hemagglutinin of a specific influenza virus case. Sequencing is only attempted for sample with cycle threshold (CT) values less then or equal to 38.<br/>

<strong>Month</strong><br/>
The month of the year that the flu positive sample was taken.<br/>

<strong>NA Clade</strong><br/>
The phylogenetic clade that a flu positive sample is part of, based on neuraminidase. Currently the IS<sub>Flu</sub>View database tracks only N1 and N2 subtype neuraminidase, thus clades will be derived from one of these subtypes. Only samples with cycle threshold (CT) values less then or equal to 25 are sequenced, and are applicable for this type of data.<br/>

<strong>PCR Specimen</strong><br/>
The specimen from which the subtyping RT-PCR was derived from.<br/>

<strong>Sequence Specimen</strong><br/>
The specimen that was used for attempting sequencing.<br/>

<strong>Site State</strong><br/>
The state that the pig was located in when the sample was taken for submission to the veterinary diagnostic laboratory.<br/>

<strong>Subtype</strong><br/>
The subtype of the influenza virus, based on PCR identificatin of the hemagglutinin and neuraminidase proteins.<br/>

<strong>Testing Facility</strong><br/>
The stream that handeled the sequencing of the sample. Samples with cycle threshold (CT) values less then or equal to 25 are handled by the USDA stream, while samples with CT values less then or equal to 38 are processed by the ISU VDL stream. Samples with CT values above 38 are not sequenced.<br/>

<strong>Week</strong><br/>
The week of the year that the flu positive sample was taken.<br/>

<strong>Year</strong><br/>
The year that the flu positive sample was taken.<br/>
</p>

<script>

//Page load
$(document).ready(function() {
        //Load in data one time
        requestData();
});

//Pull out data specific to Type xData State
function requestData() {
    var xComponent = "counts";
    var yComponent = "counts";

    getJsonData(xComponent, yComponent, parse, flags="count");
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
