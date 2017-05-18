<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/bio-pv.min.js");
$theme->setOption('head_script',$scripts,true);
//$scripts = $theme->getOption('head_script');
//echo "test";
$scripts["file"] = array("/js/jquery.min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle(<<<CSS
 body {
      font-family: Helvetica;
      background-color: #fff;/*#f0f0f0;*/
      font-weight: lighter;
      margin: 0px;
      width:100%;
      height:100%x;
    }
    a {
      color:#393;
    }
    #gl {
      /*position:fixed;
      bottom:0px;
      top:0px;
      left:0px;
      right:0px;*/
width:100%;
height:800px;
    }
    #inspector {
      top:10px;
      right:10px;
      box-shadow: 2px 2px 5px #888888;
      border-radius:8px;
      position:absolute;
      background-color:#fafafa;
      padding:10px;
      border-style:solid;
      border-width:1px;
      border-color:#ccc;
    }
    #inspector ul {
      padding:0px;
    }
    #inspector ul li {
      margin-left:5px;
      margin-right:5px;
      margin-bottom:5px;
      list-style:none;
      cursor: pointer;
      color:#393
    }
    #inspector ul li:hover {
      color:#994444;
    }
    #inspector h1 {
      font-weight:normal;
      font-size:12pt;
    }
CSS
, 'style');
$theme->setOption('head_meta', array(
    'name' => 'viewport',
    'content' => 'width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0',
));
$theme->drawHeader();
//var_dump($theme);
?>
<div id="gl">
</div>
<div id="inspector">
  <h1>Choose Style</h1>
  <ul>
    <li id="cartoon">Cartoon</li>
    <li id="sline">Smooth Line Trace</li>
    <li id="tube">Tube</li>
    <li id="lines">Lines</li>
    <b>Color Options</b>
    <li id="chain">Chain</li>
    <li id="succession">Succession</li>
    <li id="sstruct">Secondary Structure</li>
    <li id="rainbow">Rainbow</li>
    <li id="atomprop">Atom Property</li>
    <li id="resiprop">Residue Property</li>
  </ul>

  <span>Code on <a href="http://github.com/biasmv/pv">github.com</a></span>
</div>
<script src="/js/bio-pv.min.js"></script>
<script>
  var structure;
  var geom;
    var viewer = pv.Viewer(document.getElementById('gl'),
                           { quality : 'medium', width: 'auto', height : 'auto',
                             antialias : true, outline : true});

$.ajax('http://pdb.org/pdb/files/' + '4f3z' + '.pdb')
.done(function(data) {
    // data contains the contents of the PDB file in text form
    structure = pv.io.pdb(data);
    geom = viewer.cartoon('protein', structure, { color: pv.color.ssSuccession() });
    viewer.centerOn(structure);
    viewer.requestRedraw();
});

function cartoon() {
  viewer.clear();
  geom = viewer.cartoon('structure', structure);
  viewer.requestRedraw();
}

function sline() {
  viewer.clear();
  geom = viewer.sline('structure', structure);
  viewer.requestRedraw();
}

function lines() {
  viewer.clear();
  geom = viewer.lines('structure', structure);
  viewer.requestRedraw();
}

function tube() {
  viewer.clear();
  geom = viewer.tube('structure', structure);
  viewer.requestRedraw();
}

function chain() {
  geom.colorBy(pv.color.byChain());
  viewer.requestRedraw();
}

function succession() {
  geom.colorBy(pv.color.ssSuccession());
  viewer.requestRedraw();
}
  
function sstruct() {
  geom.colorBy(pv.color.bySS());
  viewer.requestRedraw();
}
  
function rainbow() {
  geom.colorBy(pv.color.rainbow());
  viewer.requestRedraw();
}
  
function atomprop() {
  geom.colorBy(pv.color.byAtomProp());
  viewer.requestRedraw();
}
  
function resiprop() {
  geom.colorBy(pv.color.byResidueProp());
  viewer.requestRedraw();
}

document.getElementById('cartoon').onclick = cartoon;
document.getElementById('sline').onclick = sline;
document.getElementById('tube').onclick = tube;
document.getElementById('lines').onclick = lines;
document.getElementById('chain').onclick = chain;
document.getElementById('succession').onclick = succession;
document.getElementById('sstruct').onclick = sstruct;
document.getElementById('rainbow').onclick = rainbow;
document.getElementById('atomprop').onclick = atomprop;
document.getElementById('resiprop').onclick = resiprop;

window.onresize = function(event) {
    viewer.fitParent();
}
</script>
<?php
$theme->drawFooter();
