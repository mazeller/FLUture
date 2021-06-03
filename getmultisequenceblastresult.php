<?php

//Generate javascript to draw C3 pie graph
function generatePieChart($arr, $bindto)
{
	//Create string for props
	$stringProp = "";

	foreach ($arr as $key => $value) {
		//Skip empty values
		if($value == "")
			continue;
 		$stringProp .= "['$key', $value],";
	}

	$jsCode = <<<EOF
var chart = c3.generate({
    bindto: '#$bindto',
    data: {
        // iris data from R
        columns: [
EOF;
	$jsCode .= $stringProp;
	$jsCode .= <<<EOF
        ],
        type : 'pie'
    }
});
EOF;
	
	//Return data
	return $jsCode;
}

// clade subtype array
$cladeArray = json_decode(file_get_contents("./js/orders.json"), true);

//Get sequence data in question
$seq_input = $_POST['seq'];
$blast_type = $_POST['blast'];

#Remove ndels if present in the sequence input
#$seq_input = str_replace("-","", $seq_input);

#Verify sequence input
#if(!preg_match('/^[atgcrykmswbdhvn]+$/' , strtolower($seq_input)) && !preg_match('/^[knimrst*ylfcwedvgaqhp]+$/' , strtolower($seq_input)))
#{
#        echo "<p>The input sequence consists of invalid characters.</p>";
#        return;
#}

// Grab deflines for table
preg_match_all('(>.*)',$seq_input,$matches);

preg_match_all("(.*)" ,$seq_input, $seq_token);
//$seq_input = explode(";",ltrim(str_replace(">", ";>", $seq_input), ";"));

//Create new array of just query sequence
$seq_input = array();
for($x=0, $str = ""; $x < count($seq_token[0]); $x++) {
        if(strlen($seq_token[0][$x]) && $seq_token[0][$x][0] == '>') {
                if(strlen($str))
                        array_push($seq_input, $str);
                $str = $seq_token[0][$x]. "\n\r";
        }
        else {
                $str .= $seq_token[0][$x];
        }
}

array_push($seq_input, $str);
//echo implode(" ", $seq_input);

// Set up table
// Style html style for the result
// TODO: Check other options. transition ease-out works with max-height but not height attribute, but height auto resolves bug. Other way might be to calculate scrollHeight for every DIV then set maxHeight to it.
$wholeTable = "<style>
        .collapsible {background-color: #777; color: white; cursor: pointer; margin-bottom: 2px; padding: 10px; width: 100%; border: 1px solid black; text-align: left; outline: none; font-size: 14px;}
        .active, .collapsible:hover { background-color: #555;}
        .collapsible:after {content: '+'; color: white; font-weight: bold; float: right; margin-left: 5px;}
        .active:after {content: '-';}
        .content { padding: 0 12px; height: 0; overflow: hidden; transition: height 0.2s ease-out; background-color: #f1f1f1;}
        </style>";
// Script to collapse and expand content
$wholeTable .= "<script>
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
        </script>";

// Expand or collapse all result divs at once
$wholeTable .= "<script>
        $(document).ready(function(){
                $('.show_hide').click(function(){
                        //var maxDivHeight = $('div.content')[0].scrollHeight + 'px';
                        if($(this).val() == 'Expand All') {
                                $('.collapsible').addClass('active');
                                $('div.content').css('height', 'auto');
                        }
                        else {
                                $('.collapsible').removeClass('active');
                                $('div.content').css('height', '0');
                        }
                        // Change the button text on expansion nd collapse
                        $(this).val( $(this).val() == 'Expand All' ? 'Collapse All' : 'Expand All' );
                });
        });
        </script>";

// Display floating back to top button whle start scrolling
$wholeTable .= "<script>
        window.onscroll = function() {scrollFunction()};
        // Logic to show and hide back to top button
        function scrollFunction() {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                        document.getElementById('top').style.visibility = 'visible';
                } else {
                        document.getElementById('top').style.visibility = 'hidden';
                }
        }
        // Go back to top of page
        function goToTop() {
                window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                });
        }

        </script>";

// Setup result summary
$wholeTable .= "<input type='button' value='Expand All' class='show_hide wd-Button--block' style='margin: 0 0 5px 0;'></input>";
$wholeTable .= "<input type='button' id='top' value='Top' onclick='goToTop();' class='wd-Button--small wd-Button--danger' style='float: right; margin-left: 25px; position: fixed; visibility: hidden;'></input>";

//Setup download data summary
$csvData = "Defline,Subtype,US Clade,Global Clade\n";

// For every sequence perform blast
for ($index = 0; $index < count($seq_input); $index++) {
        //Create temporary file
        $temp = tmpfile();
        fwrite($temp, $seq_input[$index]);
        fseek($temp, 0);

        //Send to BLAST
        $path = stream_get_meta_data($temp);
        $path = $path['uri'];

        //Default NT, unless type = AA
        if($blast_type == "aa")
        {
                $result = shell_exec("/opt/ncbi-blast-2.9.0+/bin/blastp -query " . $path . " -db /var/www/BLASTdbMultiSequence/vdl_flu_aa -outfmt \"6 sseqid pident\" -num_alignments=10 2>&1");
        }
        else
        {
	        $result = shell_exec("/opt/ncbi-blast-2.9.0+/bin/blastn -query " . $path . " -db /var/www/BLASTdbMultiSequence/vdl_flu_nt -outfmt \"6 sseqid pident\" -perc_identity 96 -num_alignments=10 2>&1");
        }

        //Delete temp
        fclose($temp); // this removes the file

        $tableData = "";
        //Explode into expected
	$result = str_replace("\t","+",$result);
	$blastHits = explode("\n", $result);


        //Broad error handeling
        if(count($blastHits) < 2)
        {
	        //Send error message
	        $tableData .= "<div class='content'>";
	        $tableData .= "<p>No results were returned.</p>";
		$tableData .= "</div>";
        
                $tableHeader = "<button class='collapsible'>" . $matches[0][$index] . "</span></button>";
                $wholeTable .= $tableHeader;
                $wholeTable .= $tableData;

                //Add Download data
                $csvData .= '"' . str_replace(",",";",$matches[0][$index]) . '"' . "," . "," . "," . "\n";

	        //Exit gracefully
	        continue;
        }

        //Finer error handeling - Empty search
        if($blastHits[0] == "Warning: [blastn] Query is Empty!" | $blastHits[0] == "Warning: [blastp] Query is Empty!" | trim($blastHits[0]) == "BLAST engine error: Warning: Sequence contains no data" )
        {
	        //Send error message
	        $tableData .= "<div class='content'>";
	        $tableData .= "<p>Empty query submitted.</p>";
		$tableData .= "</div>";

                $tableHeader = "<button class='collapsible'>" . $matches[0][$index] . "</span></button>";
                $wholeTable .= $tableHeader;
                $wholeTable .= $tableData;

                //Add Download data
                $csvData .= '"' . str_replace(",",";",$matches[0][$index]) . '"' . "," . "," . "," . "\n";

	        //Exit gracefully
	        continue;
        }

        //Invalid searches
        if(strpos($blastHits[0], "FASTA-Reader: Ignoring invalid residues at position") !== false)
        {
                //Send error message
	        $tableData .= "<div class='content'>";
                $tableData .= "<p>Potentially invalid characters detected.</p>";
		$tableData .= "</div>";

                $tableHeader = "<button class='collapsible'>" . $matches[0][$index] . "</span></button>";
                $wholeTable .= $tableHeader;
                $wholeTable .= $tableData;

                //Add Download data
                $csvData .= '"' . str_replace(",",";",$matches[0][$index]) . '"' . "," . "," . "," . "\n";

                //Exit gracefully
                continue;	
        }

        //Non HA & Non NA sequences
        if(substr($blastHits[0],0,2) != "ha" and substr($blastHits[0],0,2) != "na")
        {
                $hits = explode("+", $blastHits[0]);
                $tableData .= "<div class='content'>";
                $tableData .= "This sequence has the best BLAST match to the <span style='color:red'>" . $hits[4] . "</span> gene.<h2>";
                $tableData .= "</div>";
                $tableHeader = "<button class='collapsible'>" . $matches[0][$index] . " <br/><span style='padding-left: 60%; font-weight:bold;'>" . $hits[4] . " " . $hits[4] . "</span></button>";
                $wholeTable .= $tableHeader;
                $wholeTable .= $tableData;

                //Add Download data
                $csvData .= '"' . str_replace(",",";",$matches[0][$index]) . '"' . "," . $hits[4] . "," . $hits[4] . "," . $hits[4] . "\n";
                continue;
        }

        //Init arrays of interest
        $state = array();
        $haClade = array();
        $naClade = array();
        $haGlobalClade = array();
        $naGlobalClade = array();
        $subtype = array ();

        //Set up table
	$tableData .= "<div class='content'>";
        $tableData .= "<table class=\"wd-Table--striped wd-Table--hover\">";
        $tableData .= "<thead><th>USDA Barcode</th><th>Received date</th><th>State</th><th>Subtype</th><th>HA clade</th><th>NA clade</th><th>HA Global clade</th><th>NA Global clade</th><th>% identity</th></thead>";

	//Fill table and begin to calculate percentages
	for ($i = 0; $i <= count($blastHits); $i++)
	{
		//Table row
		$tableData .= "<tr>";
		
		$no_result_flag = 0;
		$hits = explode("+", $blastHits[$i]);

		for ($j = 1; $j < count($hits); $j++)	//This j starts at 1 to leave out db id, less then the count to leave off blank row
		{
			//If the %identity is too low, break assuming results are in order. This is mainly for blastp, which perc_identity does not work
			if((float)$hits[9] < 96.0)
			{
				if($i ==0)
					$no_result_flag = 1;
				break;
			}

			$tableData .= "<td>"; 
			
			//Add in an ncbi link if on the first item
			if($j == 1 && $hits[$j] != "")
			{
				$tableData .= "<a href=\"https://www.ncbi.nlm.nih.gov/nuccore/?term=" . $hits[$j] . "\" target=\"_blank\">" . $hits[$j] . "</a></td>";
			}
			elseif($j == 1 && $hits[$j] == "")
			{
				$tableData .= "ISU VDL" . "</td>";
			}
			else
			{
				$tableData .= $hits[$j] . "</td>";		
			}

			//Add items to array to calculate propotion
			if($hits[$j] == "")
				 $hits[$j] = "Not Tested";
			if($j == 3) //state
				$state[] = $hits[$j];
			if($j == 4) //subtype
				$subtype[] = $hits[$j];
			if($j == 5)
				$haClade[] = $hits[$j];
			if($j == 6)
				$naClade[] = $hits[$j];
			if($j == 7)
				$haGlobalClade[] = $hits[$j];
			if($j == 8)
				$naGlobalClade[] = $hits[$j];
		}
		if($no_result_flag == 1)
		{
                        $tableHeader = "<button class='collapsible'>" . $matches[0][$index] . "</span></button>";
                        $wholeTable .= $tableHeader;
                        $tableData = "<div class='content'>";
			$tableData .= "No results were returned";
                        $tableData .= "</div>";
                        $wholeTable .= $tableData;

                        //Add Download data
                        $csvData .= '"' . str_replace(",",";",$matches[0][$index]) . '"' . "," . "," . "," . "\n";

			continue 2;
		}

		//Close row
		$tableData .= "</tr>";
	}

	$tableData .= "</table>";

        $stateChartID = "";
        $pairedChartID = "";
        //Check if first BLAST hit is not HA, use alternative action
        if (substr($blastHits[0],0,2) == "ha")
        {
		//Process percentages
		$stateProp = array_count_values($state);
		//$haClade = array_count_values($haClade);
		$naProp = array_count_values($naClade);
		//$subtype = array_count_values($subtype);
                $stateChartID = "stateChart" . $index;
		$stateCode = generatePieChart($stateProp,$stateChartID);
                $pairedChartID = "naChart" . $index;
		$naCode = generatePieChart($naProp,$pairedChartID);

		// find the first non-empty clade value
		$topClade = "";
                $topGlobalClade = "";
		foreach($haClade as $idx => &$val) {
			if($val != "" and $val != "Not Tested") {
				$topClade = $val;
                                $topGlobalClade = $haGlobalClade[$idx];
				break;
			}
		}
        }
        elseif (substr($blastHits[0],0,2) == "na")
        { 
		//Process percentages
		$stateProp = array_count_values($state);
		$haProp = array_count_values($haClade);
		//$subtype = array_count_values($subtype);
                $stateChartID = "stateChart" . $index;
		$stateCode = generatePieChart($stateProp,$stateChartID);
                $pairedChartID = "haChart" . $index;
		$haCode = generatePieChart($haProp,$pairedChartID);

		// find the first non-empty clade value
		$topClade = "";
                $topGlobalClade = "";
		foreach($naClade as $idx => &$val) {
			if($val != "" and $val != "Not Tested") {
				$topClade = $val;
                                $topGlobalClade = $naGlobalClade[$idx];
				break;
			}
		}
        }
        //non ha na is already taken into consideration before blast result is converted to results
        else
        {
                $topSubtype = "";
		foreach($subtype as $idx => &$val) {
			if($val != "") {
				$topSubtype = $val;
				break;
			}
		}
                $tableHeader = "<button class='collapsible'>" . $matches[0][$index] . " <br/><span style='padding-left: 60%; font-weight:bold;'>" .  $topSubtype . "</span></button>";
                $wholeTable .= $tableHeader;
                $wholeTable .= $tableData;

                //Add Download data
                $csvData .= '"' . str_replace(",",";",$matches[0][$index]) . '"' . "," . $topSubtype . ","  . "," . "\n";

                continue;
        }

        $topSubtype = "";

        #Assign subtype based on the top clade
        if(in_array($topClade, $cladeArray["h1_clade"]))
                $topSubtype = "H1";
        if(in_array($topClade, $cladeArray["h3_clade"]))
                $topSubtype = "H3";
        if(in_array($topClade, $cladeArray["n1_clade"]))
                $topSubtype = "N1";
        if(in_array($topClade, $cladeArray["n2_clade"]))
                $topSubtype = "N2";

        $tableData .= count($state) . " sequences above 96% identity threshold<br/>";

	//Send back to server
	$tableData .= ' 
	<div id="wrapper' . $index . '">
		<h2>Influenza cases in ISU FLUture with 96% or greater similarity to query sequence</h2> 
		<div class="chartChild"> 
			<h3>State of Detection</h3> 
			<div id=' . $stateChartID . ' class="chartChild"></div> 
		</div>';
        if (substr($blastHits[0],0,2) == "ha") {
		$tableData .= ' 
			<div class="chartChild"> 
				<h3>Paired Neuraminidase</h3> 
				<div id=' . $pairedChartID . ' class="chartChild"></div> 
			</div> 
		</div>
		';
        }
        else {
		$tableData .= ' 
			<div class="chartChild"> 
				<h3>Paired Hemagglutinin</h3> 
				<div id=' . $pairedChartID . ' class="chartChild"></div> 
			</div> 
		</div>
		';
        }

	$tableData .= "<script>";
	$tableData .= $stateCode;

        if (substr($blastHits[0],0,2) == "ha") {
                $tableData .= $naCode;
        }
        else {
                $tableData .=$haCode;
        }
	$tableData .= "</script>";
       	$tableData .= "</div>";

        //Add Download data
        $csvData .= '"' . str_replace(",",";",$matches[0][$index]) . '"' . "," . $topSubtype . "," . $topClade . "," . $topGlobalClade . "\n";

        //Add headers
        $tableHeader = "<button class='collapsible'>" . $matches[0][$index] . " <br/><span style='padding-left: 60%; font-weight:bold;'>" . $topSubtype . " " . $topClade . " " . $topGlobalClade . "</span></button>";
        $wholeTable .= $tableHeader;
        $wholeTable .= $tableData;
}

//Compression
ob_start('ob_gzhandler');
echo json_encode(array($wholeTable,$csvData));
ob_end_flush();
return;
#send data back to requester
