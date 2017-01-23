<?php
require 'autoload.php';

$theme = new Sample\Theme('ISFluView', array('show_sidebar' => false, 'show_page_title' => false));
$theme->addStyle('{{asset_path}}/css/front.css');
$theme->addStyle('{{asset_path}}/css/vdl-front.css');
$theme->drawHeader();
?>

	<div class="wd-l-Content-inner wd-l-Content-inner-FrontPage">
		<!--CONTENT-->
		<div class="wd-Grid--2to1 banner">
			<div class="wd-Grid-cell">
				<div class="region region-banner">
					<div class="block block-views collapsiblock-processed" id="block-views-banner-block-1">
						<div class="content">
							<div class="view view-banner view-id-banner view-display-id-block_1 view-dom-id-f744c3abf14d0e3e89829f384892532b jquery-once-1-processed">
								<div class="view-content">
									<div class="skin-default">
										<img typeof="foaf:Image" src="img/filler.png" alt="">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
<div class="wd-Grid--1col triptychs">
			<div class="wd-Grid-cell">
				<div class="region region-triptych-one">
					<div class="block block-block collapsiblock-processed" id="block-block-28">
						<div class="content">
							<h2>Suite of Tools</h2>
							<p>
<strong>Correlations</strong><br>
105 unique correlation trend graphs can be generated from variables stored in the database.<br>
<strong>Time Series</strong><br>
The incidence of the variables in the database can be viewed as a function of time<br>
<strong>Regional</strong><br>
The incidence of flu positive cases that have been processed by the USDA/VDL can be viewed as a function of location, over a specified period of time.<br>
<strong>Heat Map</strong><br>
Shows the distribution of the hemagglutinin and neuraminidase combinations over a region of time.<br>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
			</div>
			<div class="wd-Grid-cell">
				<div class="region region-banner-sidebar">
					<div class="block block-block collapsiblock-processed" id="block-block-27">
						<div class="content">
							<div class="front-button button-client-login">
								<a href="/correlation.php">Correlation</a>
							</div>
							<div class="front-button button-tests-fees">
								<a href="/timeseries.php">Time Series</a>
							</div>
							<div class="front-button button-forms">
								<a href="/regional.php">Regional</a>
							</div>
							<div class="front-button button-submissions">
								<a href="/heatmap.php">HeatMap</a>
							</div>
							<div class="front-button button-forms">
								<a href="/phylogeny.php">Phylogeny</a>
							</div>
							<div class="front-button button-submissions">
								<a href="/contact.php">Contact Us</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="region region-content">
			<div class="block block-system collapsiblock-processed" id="block-system-main">
				<div class="content">
					<div about="/vdl" class="node node-page clearfix" id="node-6506" typeof="foaf:Document">
						<span class="rdf-meta element-hidden" content="Veterinary Diagnostic Laboratory" property="dc:title"></span>
						<div class="content"></div>
					</div>
				</div>
			</div>
		</div><!--END OF CONTENT-->
	</div>


<?php
$theme->drawFooter();
