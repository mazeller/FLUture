<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->addStyle(<<<CSS
#wrapper {
    width: 100%;
    overflow: hidden;
}
.chartChild {
    width: 50%;
    float:left;
}
.lds-ring {
  display: inline-block;
  position: relative;
  width: 40px;
  height: 40px;
}
.lds-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 32px;
  height: 32px;
  margin: 4px;
  border: 4px solid #ff0;
  border-radius: 50%;
  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: #f00 transparent transparent transparent;
}
.lds-ring div:nth-child(1) {
  animation-delay: -0.45s;
}
.lds-ring div:nth-child(2) {
  animation-delay: -0.3s;
}
.lds-ring div:nth-child(3) {
  animation-delay: -0.15s;
}
@keyframes lds-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.collapsible {background-color: #777; color: white; cursor: pointer; margin-bottom: 2px; padding: 10px; width: 100%; border: 1px solid black; text-align: left; outline: none; font-size: 14px;}
.active, .collapsible:hover { background-color: #555;}
.collapsible:after {content: '+'; color: white; font-weight: bold; float: right; margin-left: 5px;}
.active:after {content: '-';}
.content {padding: 0 12px; height: 0; overflow: hidden; transition: height 0.2s ease-out; background-color: #f1f1f1;}

CSS
, 'style');
$theme->drawHeader();
?>

<h2 id="chartTitle">Multisequence Identity Tool</h2>

<p>
The HA-NA sequence identity tool uses BLAST to find similar hemagglutinin/neuraminidase sequences in the ISU-VDL data. Results are limited to &ge; 96% identity, with up to 10 results returned per query sequence. For a more complex analysis, please refer to the <a href="https://www.fludb.org/brc/blast.spg?method=ShowCleanInputPage&decorator=influenza">IRD BLAST tool</a>. 
</p>

<form id="target">
<textarea rows="16" cols="100" id="sequences" placeholder="Paste sequences (fasta/plain text) in the following format:\n\n>defline1\nATCAAATTTTCCCCGGGG\n\n>defline2\nAAATTTTTCCCGGGCTGA">
</textarea><br/>
<b>Sequence type</b><br/>
<input type="radio" name="blasttype" value="nt" checked="checked" />nucleotide<br>
<input type="radio" name="blasttype" value="aa" />amino acid<br>
<label class="wd-Button--default">
        <input id="fileUploader" type="file" name="filename" hidden/>
        <i class="fa fa-cloud-upload"></i>Upload
</label>
<a class="wd-Button" id="submit">Search</a>
<a href="javascript:;" class="wd-Button" id="download">Download Results Data</a> 
</form>

<br/>
<div class="wd-Alert" id="waitUpload">
Please wait, Sequence File is being uploaded...
</div>
<br/>
<div class="wd-Alert" id="wait">
Please wait, BLAST in progress...
</div>

<div class="lds-ring" id="spinner"><div></div><div></div><div></div><div></div></div>

<div id="results"></div>

<br/>
<div>
<small>
<h3>References</h3>
<ol>
<!--<li>Altschul, S.F., Gish, W., Miller, W., Myers, E.W. & Lipman, D.J. (1990) "Basic local alignment search tool." J. Mol. Biol. 215:403-410.</li>
<li>Zhang Z., Schwartz S., Wagner L., & Miller W. (2000), "A greedy algorithm for aligning DNA sequences" J Comput Biol 2000; 7(1-2):203-14.</li>
<li>Chang, J., Anderson, T.K., Zeller, M.A., Gauger, P.C., and Vincent, A.L. (2019). “octoFLU: Automated classification to evolutionary origin of influenza A virus gene sequences detected in U.S. swine” Microbiology Resource Announcements 8(32), e00673-19. <a href="https://github.com/flu-crew/octoFLU">Github repo.</a></li>-->
<li>Altschul, S. F., Gish, W., Miller, W., Myers, E. W., &amp; Lipman, D. J. (1990). Basic local alignment search tool. Journal of molecular biology, 215(3), 403-410.</li>
<li>Zhang, Z., Schwartz, S., Wagner, L., &amp; Miller, W. (2000). A greedy algorithm for aligning DNA sequences. Journal of Computational biology, 7(1-2), 203-214.</li>
<li>Chang, J., Anderson, T. K., Zeller, M. A., Gauger, P. C., &amp; Vincent, A. L. (2019). octoFLU: Automated Classification for the Evolutionary Origin of Influenza A Virus Gene Sequences Detected in US Swine. Microbiology resource announcements, 8(32), e00673-19. <a href="https://github.com/flu-crew/octoFLU">Github repo.</a></li>

</ol>
</small>
</div>
<script>

window.onscroll = function() {scrollFunction()};

// Logic to show and hide back to top button
function scrollFunction() {
        if(document.getElementById('top')) {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                        document.getElementById('top').style.visibility = 'visible';
                } else {
                        document.getElementById('top').style.visibility = 'hidden';
                }
        }
}

// Go back to top of page
function goToTop() {
        window.scrollTo({
                top: 0,
                behavior: 'smooth'
        });
}

$(document).ready(function() {
        var textAreas = document.getElementsByTagName('textarea');

        Array.prototype.forEach.call(textAreas, function(elem) {
                elem.placeholder = elem.placeholder.replace(/\\n/g, '\n');
        });

	//Hide wait
	$("#wait").hide();
        $("#waitUpload").hide();
	$("#wrapper").hide();
	$("#download").hide();
	$("#upload").on("click". uploadData);
       	$("#spinner").hide();
        $("#submit").on("click", getBlastResult);
        document.getElementById('fileUploader').addEventListener('change', addDataToTextField, false);
        //document.getElementById('download').addEventListener('click', download, false);
});

function download() {
	var element = document.createElement('a');
	element.setAttribute('href', 'data:application/octet-stream;charset=utf-8,' + encodeURIComponent(csvData));
	element.setAttribute('download', 'multiSequenceIdentityData.csv');

	element.style.display = 'none';
	document.body.appendChild(element);

	element.click();

	document.body.removeChild(element);
}

function addDataToTextField(data) {
 	var fileName = this.files[0];
        var fileLimit = 1024*1024*1.5; // 1.5Mb

        if(!fileName) {
		$("#results").html("Failed to load file!<br \>");
	}
        else if (fileName.size > fileLimit) {
                $("#results").html("File size is too big: please select a smaller file to process queries<br \>");
        }
	else {
		var reader = new FileReader();
		reader.onload = function(loadedEvent) {
			document.getElementById("sequences").value = loadedEvent.target.result;
		}
		reader.onerror = function() {
                        $("#results").text("Process aborted, the file cannot be uploaded");
                }
		reader.readAsText(fileName);
	}

        //Force pause
        setTimeout(function() {
                //Hide wait
                $("#waitUpload").slideUp("slow");
                $("#spinner").hide();
                $("#sequences").prop("disabled", false);
                //Reconnect button
                $("#upload").on("click", uploadData);
        }, 1000);
}

function uploadData () {
        //Hide form, show wait
        $("#waitUpload").slideDown("slow");
        $("#wrapper").show();
        $("#spinner").show();
        $("#sequences").prop("disabled", true);
        $("#download").hide();

        //Disconnect button
        $("#upload").attr("disabled", false);
        $("#download").attr("disabled", false);

        //Read File and update sequences
	document.getElementById("fileUploader").click();
}

function getBlastResult() {
        $("#results").html("");
	//Hide form, show wait
	$("#wait").slideDown("slow");
	$("#wrapper").show();
        $("#spinner").show();
	$("#sequences").prop("disabled", true);
	
	//Disconnect button
        $("#upload").off("click");
        $("#download").off("click");
        $("#submit").off("click");

        //If showing hide download button
        $("#download").hide();

        //Process fasta input into specific object structure
        var fastaString = $("#sequences").val();
	var blastType = $('input[name=blasttype]:checked').val(); 

        if(!fastaString) {
                $("#results").html("Empty query submitted");
                //Force pause
                setTimeout(function() {
                        //Hide wait
                        $("#wait").slideUp("slow");
                        $("#spinner").hide();
                        $("#sequences").prop("disabled", false);

                        //Reconnect button
                        $("#submit").on("click",getBlastResult);
                 }, 1000);
                return;
        }

        if(fastaString.indexOf('>') == -1) {
                fastaString = ">test\n".concat(fastaString);
        }

        //Request data
        $.ajax({
                url: '/getmultisequenceblastresult.php',
                type: 'post',
                data: {'seq': fastaString, 'blast': blastType},
                success: function(data, status) {
                        var JSONres = $.parseJSON(data);
                        
                        var data = JSONres.data;

                        if(data.length) {
                                //Global variable to download data
                                var wholeTable = "<input type='button' value='Expand All' class='show_hide wd-Button--block' style='margin: 0 0 5px 0;'></input>";
                                wholeTable += "<input type='button' id='top' value='Top' onclick='goToTop();' class='wd-Button--small wd-Button--danger' style='float: right; margin-left: 25px; position: fixed; visibility: hidden;'></input>";
                                csvData = "Defline,Subtype,US Clade,Global Clade\n";
                                for(var i=0; i < data.length; i++) {
                                        var tableData = "";

                                        if(data[i].type == "other") {
                                                tableData += "<button class='collapsible'>" + data[i].Defline + " <br/><span style='padding-left: 80%; font-weight:bold;'>" + data[i].subtype + " " + data[i].usCladeOther + " " + data[i].globalCladeOther + "</span></button>";
                                                csvData += data[i].Defline + "," + data[i].subtype + "," + data[i].usCladeOther + "," + data[i].globalCladeOther + "\n";
                                        }
                                        if(data[i].type == "ha") {
                                                tableData += "<button class='collapsible'>" + data[i].Defline + " <br/><span style='padding-left: 80%; font-weight:bold;'>" + data[i].subtype + " " + data[i].usCladeHA + " " + data[i].globalCladeHA + "</span></button>";
                                                csvData += data[i].Defline + "," + data[i].subtype + "," + data[i].usCladeHA + "," + data[i].globalCladeHA + "\n";
                                        }
                                        if(data[i].type == "na") {
                                                tableData += "<button class='collapsible'>" + data[i].Defline + " <br/><span style='padding-left: 80%; font-weight:bold;'>" + data[i].subtype + " " + data[i].usCladeNA + " " + data[i].globalCladeNA + "</span></button>";
                                                csvData += data[i].Defline + "," + data[i].subtype + "," + data[i].usCladeNA + "," + data[i].globalCladeNA + "\n";
                                        }
                                        if(data[i].type == "error") {
                                                tableData += "<button class='collapsible'>" + data[i].Defline + "</button>";
                                                csvData += data[i].Defline + ",,,\n";
                                        }

                                        if(data[i].message) {
                                                tableData += "<div class='content'>" + data[i].message + "</div>";
                                        }
                                        if(data[i].children.length) {
                                                tableData += "<div class='content'>";
                                                tableData += "<table class=\"wd-Table--striped wd-Table--hover\"><th>USDA Barcode</th><th>Received date</th><th>State</th><th>Subtype</th><th>HA clade</th><th>NA clade</th><th>HA Global clade</th><th>NA Global clade</th><th>% identity</th></thead>";


                                                //Skip the first 2 columns for nested result
                                                for(var hit_index = 0; hit_index < data[i].children.length; hit_index++) {
                                                        var hits = data[i].children[hit_index];
                                                        if(hits.length) {
                                                                tableData += "<tr><td>";
                                                                tableData += hits.join('</td><td>');
                                                                tableData += "</td></tr>";
                                                        }
                                                }
                                                tableData += "</table>";
                                        }
                                        if(data[i].pie) {
                                                tableData += data[i].pie;
                                        }
                                        // Encapsulate the piechart in the content as well
                                        tableData += "</div>";
                                        wholeTable += tableData;
                                }

                                resultData(wholeTable);

				// Find all elements that have collapsible class
				var coll = document.getElementsByClassName('collapsible');
				// Toggle the following div element to expand or collapse
				for (var i = 0; i < coll.length; i++) {
					coll[i].addEventListener('click', function() {
						this.classList.toggle('active');
						var content = this.nextElementSibling;
						if (content.style.height) {
							content.style.height = null;
							$('.show_hide').val( $('.collapsible').length == $('.collapsible.active').length ? 'Collapse All' : 'Expand All' );
						} else {
							content.style.height = 'auto';//content.scrollHeight + 'px';
							$('.show_hide').val( $('.collapsible').length == $('.collapsible.active').length ? 'Collapse All' : 'Expand All' );
						}
					});
				}

                                // Expand or collapse all result divs at once
				$('.show_hide').on('click', function(){
					if($(this).val() == 'Expand All') {
						$('.collapsible').addClass('active');
						$('div.content').css('height', 'auto');
					}
					else {
						$('.collapsible').removeClass('active');
						$('div.content').css('height', '');
					}
					// Change the button text on expansion nd collapse
					$(this).val( $(this).val() == 'Expand All' ? 'Collapse All' : 'Expand All' );
				});


				//$("#download").on("click", download);
				$("#download").on("click", download);
				$("#download").show();

                        }
                        else {
                                if(JSONres.error) {
                                        $("#results").html(JSONres.error);
                                }
                                $("#wait").slideUp("slow");
                                $("#spinner").hide();
                                $("#sequences").prop("disabled", false);
                                //Reconnect button
                                $("#submit").on("click",getBlastResult);
                        }
                },
                error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                        $("#wait").slideUp("slow");
                        $("#sequences").prop("disabled", false);
                        $("#spinner").hide();
			$("#results").html("Server Error");
	        }
        });
}

function resultData(data) {
	//Show results as soon as they come in
	$("#results").html(data);

	//Force pause
	setTimeout(function() {
		//Hide wait
		$("#wait").slideUp("slow");
		$("#sequences").prop("disabled", false);
		$("#spinner").hide();

		//Reconnect button	
		$("#submit").on("click", getBlastResult);
	}, 1000);
}

</script>
<?php
$theme->drawFooter();


