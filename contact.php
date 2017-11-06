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

<div class="wd-l-Content-inner">
						
																			
						
						
						
						
																			<h1>
								Contact Us															</h1>
												
						
							  <div class="region region-content">
    <div id="block-system-main" class="block block-system collapsiblock-processed">

    
  <div class="content">
    <div id="node-6547" class="node node-page clearfix" about="/vdl/about/contact-us" typeof="foaf:Document">

  
      <span property="dc:title" content="Contact Us" class="rdf-meta element-hidden"></span>
  
  <div class="content">
    <div class="field field-name-body field-type-text-with-summary field-label-hidden"><div class="field-items"><div class="field-item even" property="content:encoded"><table align="left" border="0" cellpadding="1" cellspacing="1" style="width: 400px;"><tbody><tr><td>
				<h2>By Phone</h2>
				<p>515-294-1950&nbsp;(Mon-Fri, 8 am - 5 pm CST)</p>
								</td>
		</tr></tbody></table><table border="0" cellpadding="1" cellspacing="1" style="width: 400px;"><tbody><tr><td>
				<h2>By Email</h2>
				<p><span style="unicode-bidi:bidi-override; direction: rtl;">ude.etatsai@rellezam</span></p>

				<h2>By Mail</h2>

				<p>Veterinary Diagnostic Laboratory<br>
					College of Veterinary Medicine<br>
					Iowa State University<br>
					1850 Christensen Dr<br>
					Ames, IA 50011-1134</p>
			</td>
		</tr></tbody></table><p>&nbsp;</p>
</div></div></div>  </div>

  
  
</div>
  </div>
</div>
  </div>
						
				</div>

<?php
$theme->drawFooter();
