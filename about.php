<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/dataloader.js","/js/changelog.js","/js/recentchangelog.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/c3.min.css');
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->drawHeader();
?>

<script>
$(document).ready(function() {
        $('#changelog-recent-info').html(recentLogData);
        $('#changelog-complete-info').html(completeLogData);
        $('#changelog-complete-info').hide();
});

function toggleVisibility(id) {
        if(id==0) {
                $('#changelog-recent-info').show();
                $('#recentCL').addClass('active');
                $('#changelog-complete-info').hide();
                $('#completeCL').removeClass('active');
        }
        if(id==1) {
                $('#changelog-recent-info').hide();
                $('#recentCL').removeClass('active');
                $('#changelog-complete-info').show();
                $('#completeCL').addClass('active');
        }
}
</script>

<!--<ul class="wd-Pagination">
	<li><a href="#variable-info">Variable Information</a></li>
	<li><a href="#contact-info">Contact Information</a></li>
	<li><a href="#conference-info">Conference Information</a></li>
</ul>-->

<div class="toolbar">
	<a class='button' href="#cite-info">Citation</a>
	<a class='button' href="#contact-info">Contact</a>
	<!--<a class='button' href="#changelog-info">Changelog</a>-->
        <span class='button-group'>
                <a id='recentCL' class="button active" href="#changelog-info" onclick="toggleVisibility(0)">Latest Changelog</a>
                <a id='completeCL' class="button" href="#changelog-info" onclick="toggleVisibility(1)">Complete Changelog</a>
        </span>
	<a class='button' href="#variable-info">Variables</a>
	<a class='button' href="#conference-info">Training Workshops</a>
</div>

<div class="content">

<div id='cite-info'>
<h1 class='wd-u-Heading'>How to Cite</h1>
<p>If you use data provided by ISU <i>FLU</i>ture in your work, please credit in the following format:<br/><br/>  
Zeller, M. A., Anderson, T. K., Walia, R. R., Vincent, A. L., &amp; Gauger, P. C. (2018). ISU FLUture: a veterinary diagnostic laboratory web-based platform to monitor the temporal genetic patterns of Influenza A virus in swine. <i>BMC bioinformatics</i>, <i>19</i>(1), 397.
(data retrieved <?php echo (new DateTime())->format('d M, Y');?>).</p>
</div>

<div id='contact-info'>
<h1 class='wd-u-Heading'>Contact</h1>
<h3>By Phone</h3>
<p>515-294-1950&nbsp;(Mon-Fri, 8 am - 5 pm CST)</p>

<h3>By Email</h3>
<p><b>Megan Neveau</b> <span style="unicode-bidi:bidi-override; direction: rtl;">ude.etatsai@uaevenm</span><br/>
<b>Phillip Gauger DVM, PhD</b> <span style="unicode-bidi:bidi-override; direction: rtl;">ude.etatsai@reguagcp</span></p>

<h3>By Mail</h3>
<p>Veterinary Diagnostic Laboratory<br>
                                        College of Veterinary Medicine<br>
                                        Iowa State University<br>
                                        1850 Christensen Dr<br>
                                        Ames, IA 50011-1134</p>

</div>

<div id='changelog-info'>
<h1 class='wd-u-Heading'>Changelog</h1>
<p id='changelog-recent-info'></p>
<p id='changelog-complete-info'></p>
</div>

<a id='variable-info' style="text-decoration:none"><h1 class="wd-u-Heading">Variables</h1></a>
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
<br/>
<div id='conference-info'>
<h1 class='wd-u-Heading'>Training Workshops</h1>
<h3>Files for the 2018 McKean Swine Disease Workshop</h3>
<a href = "/files/tutorial2018.zip">Download</a>
</div>
</div>


<?php
$theme->drawFooter();
