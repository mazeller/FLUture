<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/phylotree.js","//netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js","https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js");
//$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/phylotree.js","//netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js","https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->addStyle('{{asset_path}}/css/phylotree.css');
$theme->addStyle("/css/bootstrap.min.css");
$theme->drawHeader();
?>

<script src="https://d3js.org/d3-color.v1.min.js"></script>
<script src="https://d3js.org/d3-interpolate.v1.min.js"></script>
<script src="https://d3js.org/d3-scale-chromatic.v1.min.js"></script>
<script src = "https://d3js.org/d3-scale.v1.min.js"></script>

<style>
  .date-text {
    padding: 0.25em;
    text-align: center;
  }
  .size-bubble,
  .size-bubble * {
    fill: #CCC;
    shape-rendering: crispEdges;
    stroke: black;
    stroke-width: 2px;
  }
  .size-label {
    display: block;
    text-align: center;
    font-family: sans-serif;
    font-size: 12pt;
  }
</style>

<h2 id="chartTitle">Phylogenetic Tree</h2>
<div>
	<br><b>Clustered By </b>
	<select id="clusters">
		<option value="clade">Clade</option>
        	<option value="state">State</option>
        	<option value="year">Year</option>
	</select>
</div>
<div class="container">
	<br>
	<svg id="tree_display"></svg>
</div>
<!--div><a href="javascript:;" id="grabData">Download Graph Data</a></div>-->

<script>
// Data Source
//var URL = "flu_test.nwk";
var URL = "large_flu_tree.nwk";
$(document).ready(function() {
  // load graph
  loadTreeFromURL();
  // on change
  $("#clusters").change(loadTreeFromURL);
});
// Cluster Array
var clusters = {};
var state_cluster = {};
var year_cluster = {};
var clade_cluster = {};

// use these formats for parsing and displaying sampling dates
var date_in = d3.time.format("%Y-%m-%d");
var date_year = d3.time.format("%Y");

// default scheme to color 
//var coloring_scheme = d3.scale.category10();
var coloring_scheme = d3.scaleSequential(d3.interpolateRainbow).domain([1,14]);

// global tree object
var tree;

// bubble size scale
var size_scale = d3.scale.pow().exponent(1.0).range([1, 10]).clamp(false).domain([0, 1]);

// determines the size of a node "bubble"
function bubbleSize(node) {
  if (node && node.compartment) {
    return size_scale(200);
  }
  return 1;
}

// This hover affect can be improved
/*function nodeStyler(container, node) {
  if (d3.layout.phylotree.is_leafnode(node)) {
    //var existing_circle = container.selectAll("circle");
    //if (existing_circle.size() == 1) {
    //  existing_circle.remove();
    //}
    var existing_text = container.selectAll("text");
    existing_text.on("mouseover", handleMouseOver);
  }
}

function handleMouseOver() {
    var test1 = document.createElement("Title");
    var test1Text = document.createTextNode("Hello World");
    test1.appendChild(test1Text);
    this.appendChild(test1);
    test1.classList.toggle("show");
    //var test = this.selectAll("title");
    //test.enter().append("title");
    //test.text("Hello world");
}*/

var tempColorSet = [];
function nodeStyler(container, node) {
  coloring_scheme = d3.scaleSequential(d3.interpolateRainbow).domain([1,14]);
  if (d3.layout.phylotree.is_leafnode(node)) {
    var existing_circle = container.selectAll("circle");
    if (existing_circle.size() == 1) {
      existing_circle.remove();
    }
    if (node.compartment) {
      existing_circle = container.selectAll("path.node_shape").data([node.compartment]);
      existing_circle.enter().append("path").classed("node_shape", true);
      //var bubble_size = tree.node_bubble_size(node);
      var bubble_size = bubbleSize(node);
      var label = existing_circle.attr("d", function(d) {
        // draw an area
        return d3.svg.symbol().type("circle").size(bubble_size * bubble_size)();
      }).selectAll("title").data([node.compartment]);
      label.enter().append("title").text(node.compartment+"|"+node.date+"|"+node.state);
      //circle line style
      existing_circle.style("stroke-width", "0.5px").style("stroke", "white");
    }
  }
  if (node.compartment) {
    if (tempColorSet.indexOf(node.compartment) == -1) {
	tempColorSet.push(node.compartment);
    }
    // set the label color
    //var node_color = coloring_scheme(node.compartment);
    var node_color = coloring_scheme(tempColorSet.indexOf(node.compartment));
    container.selectAll("circle").style("fill", node_color);
    container.selectAll("path").style("fill", node_color);
    container.style("fill", node_color);
  }
}

function edgeStyler(container, edge) {
    container.style("stroke", "cluster" in edge.target ? coloring_scheme(tempColorSet.indexOf(edge.target.cluster)) : null);
    //container.style("stroke", "cluster" in edge.target ? coloring_scheme(edge.target.cluster) : null);
}
function drawATree(newick, cluster_name) {
  tree = d3.layout.phylotree()
    .svg(d3.select("#tree_display"))
    .options({
      'selectable': true,
      // make nodes and branches not selectable
      'collapsible': true,
      // turn off the menu on internal nodes
      'transitions': true,
      // turn off d3 animations.
      'draw-size-bubbles': true,
      // draw node size bubbles
      'show-scale': true,
      //zoom: true,
      'left-right-spacing': 'fit-to-size',
      'top-bottom-spacing': 'fit-to-size'
    })
    .size([900, 960])
    .node_span(bubbleSize)
    .style_nodes(nodeStyler)
    .style_edges(edgeStyler)
    .node_circle_size(0) // do not draw clickable circles for internal nodes
    /*.branch_name(function() {
      return ""
    }) // no leaf names
    */
  ;
  /* the next call creates the tree object, and tree nodes */
  tree(d3.layout.newick_parser(newick));

  var attributed_node = null;
  var oldest_date = null; 
  var reroot_node = null;
  var unique_compartments = {};

  _.each(tree.get_nodes(), function(value, key) {
    // check if is leaf
    var attributes = value.name.split('|');

    if (attributes.length >= 4) {
      attributed_node = value;
      // determine the shape, here is the clade
      value.clade = attributes[2];
      // show in the hover, which is the last part of the name
      value.state = attributes[1];
      value.year = date_year(date_in.parse(attributes[3]));
      value.date = attributes[3];

      if (oldest_date == null || oldest_date > value.date) 
      {
	oldest_date = value.date;
	reroot_node = attributed_node;
      }

      // prepare for nofe color
      value.compartment = value[cluster_name];
      unique_compartments[value.compartment] = 1;
      // initialize clusters
      state_cluster[value.name] = value.state; 
      year_cluster[value.name] = value.year; 
      clade_cluster[value.name] = value.clade; 
    } else {
      value.is_reference = true;
    }
  });

  tree.reroot(reroot_node)

  clusters.state = state_cluster;
  clusters.clade = clade_cluster;
  clusters.year = year_cluster;
  tree.spacing_x(15).spacing_y(20);

  size_scale.domain([100, 0]);
  coloring_scheme.domain(_.keys(unique_compartments).sort().map(function(d) {
    return [d];
  }));
  //tree.placenodes().layout();
}

function loadTreeFromURL() {
  var cluster_name = $("#clusters").val();
  d3.text(URL, function(error, newick) {
    drawATree(newick, cluster_name);
    applyAnnotation(clusters[cluster_name]);
  });
}

function applyAnnotation(clustering) {
  coloring_scheme.domain([]); // reset the coloring scheme
  if (tree) {
    tree.traverse_and_compute(function(node) {
        if (node.name in clustering) {
          node.cluster = clustering[node.name];
        } else {
          delete node.cluster;
          var children_clusters = _.keys(_.countBy(node.children, function(d) {
            return d.cluster;
          }));
          if (children_clusters.length == 1 && children_clusters[0]) {
            node.cluster = children_clusters[0];
          }
        }
      },
      "post-order");
    //tree.update();
  }
  sort_nodes(false);
}

function sort_nodes (asc) {
    tree.traverse_and_compute (function (n) {
            var d = 1;
            if (n.children && n.children.length) {
                d += d3.max (n.children, function (d) { return d["count_depth"];});
            }
            n["count_depth"] = d;
        });
        tree.resort_children (function (a,b) {
            return (a["count_depth"] - b["count_depth"]) * (asc ? 1 : -1);
        });
}

</script>

