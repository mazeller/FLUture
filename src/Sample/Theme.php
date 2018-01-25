<?php
namespace Sample;

class Theme extends \IastateTheme\Theme
{
    public function init()
    {
        $this->setOptions(array(
	    'site_title' => 'College of Veterinary Medicine',
	    'show_navbar' => false,
	    /*'navbar' => array(
                array(
                    'label' => 'Home',
                    'uri' => '/',
		    'pages' => '',
                ),
	    ),*/
            'sidebar' => array(
                array(
                    'label' => 'Correlation',
                    'uri' => '/correlation.php',
                ),
                array(
                    'label' => 'Time Series',
                    'uri' => '/timeseries.php',
                ),
                array(
                    'label' => 'Regional',
                    'uri' => '/regional.php',
                ),
                array(
                    'label' => 'Heat Map',
                    'uri' => '/heatmap.php',
              	),
                array(
                    'label' => 'Contact',
                    'uri' => '/contact.php',
                ),
		array(
                    'label' => 'H3N2 Awareness',
                    'uri' => 'https://vetmed.iastate.edu/story/vdl-h3n2-transmission',
                ),

                /*array(
                    'label' => '3D View',
                    'uri' => '/3dview.php',
                ),*/
            ),
	    'head_script' =>array(
		'script' => array(
	            'google_analytics' => "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-92515126-1', 'auto');
  ga('send', 'pageview');",
		),
	    ),
            //'page_footer' => '<p>Unit name, address, (555) 555-5555, '. $this->email('email') .'.</p>',
            'page_footer' => '<div class="footer"><div class="footer-inner">
    <div class="wordmark">
        <a href="http://www.iastate.edu">
            <img src="/img/sprite.png" alt="Iowa State University">
        </a>
    </div>
    
    <div class="social-buttons">
        <div class="social-button">
            <a href="https://www.facebook.com/pages/ISU-College-of-Veterinary-Medicine/164681434126" target="_blank" class="social-link"><img src="/img/icon_facebook.png" alt="Facebook"></a>
        </div>
        <div class="social-button">
            <a href="https://twitter.com/ISUVetMed" target="_blank" class="social-link"><img src="/img/icon_twitter.png" alt="Twitter"></a>
        </div>
    </div>
    <div class="footer-tryptych">
          <div class="region region-footer-triptych-one">
    <div id="block-block-40" class="block block-block collapsiblock-processed">

    
  <div class="content">
    <p align="left"><strong>Veterinary Diagnostic Laboratory</strong><br>
	Iowa State University<br>
	1850 Christensen Drive, Ames, IA 50011-1134<br>
	Phone: 515-294-1950, Fax 515-294-3564, Email: 
	<a href="mailto:isuvdl@iastate.edu">isuvdl@iastate.edu</a>
   </p>
  </div>
</div>
  </div>
    </div>

    <div class="footer-tryptych">
          <div class="region region-footer-triptych-two">
    <div id="block-block-41" class="block block-block collapsiblock-processed">

    
  <div class="content">
    <p><img alt="" src="/img/usda_symbol.png" style="width: 86px; height: 66px;"></p>
  </div>
</div>
  </div>
    </div>
<div class="footer-tryptych">
          <div class="region region-footer-triptych-one">
    <div id="block-block-40" class="block block-block collapsiblock-processed">

    
  <div class="content">
	<strong>United States Department of Agriculture</strong><br>
	Agricultural Research Service<br>
	National Animal Disease Center  
 </p>
  </div>
</div>
  </div>
    </div>
    <div class="footer-tryptych">
          <div class="region region-footer-triptych-two">
    <div id="block-block-41" class="block block-block collapsiblock-processed">

    
  <div class="content">
    <p><img alt="" src="http://cvmvdl-dev.cvm.iastate.edu/sites/default/files/vdl/AAVLD_logo.jpg" style="width: 86px; height: 66px;"></p>
  </div>
</div>
  </div>
    </div>
    <div class="footer-tryptych">
          <div class="region region-footer-triptych-three">
    <div id="block-block-42" class="block block-block collapsiblock-processed">

    
  <div class="content">
    <p><strong>Fully accredited by AAVLD</strong><br>
	American Association of<br>
	Veterinary Laboratory Diagnosticians<br><br>
	Copyright © 2016, All Rights Reserved<br>
	Iowa State University of Science and Technology</p>
  </div>
</div>
  </div>
    </div>
</div></div>'
	    ));
    }
}
