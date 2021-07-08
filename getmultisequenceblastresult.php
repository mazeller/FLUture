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

// Result JSON with all data
//$JSONres = array();
$JSONres = new stdClass();
$JSONres->length = 1;
$JSONres->data = array();

//Create temporary file
$temp = tmpfile();
fwrite($temp, $seq_input);
fseek($temp, 0);

//Send to BLAST
$path = stream_get_meta_data($temp);
$path = $path['uri'];

//Default NT, unless type = AA
if($blast_type == "aa")
{
	$result = shell_exec("/opt/ncbi-blast-2.11.0+/bin/blastp -query " . $path . " -db /var/www/BLASTdbMultiSequence/vdl_flu_aa -outfmt \"15 sseqid pident\" -num_alignments=10 2>/dev/null");
}
else
{
	$result = shell_exec("/opt/ncbi-blast-2.11.0+/bin/blastn -query " . $path . " -db /var/www/BLASTdbMultiSequence/vdl_flu_nt -outfmt \"15 sseqid pident\" -perc_identity 96 -num_alignments=10 2>/dev/null");
}

// get tmpfile stats
$fstat = fstat($temp);

//Delete temp
fclose($temp); // this removes the file
// if tmpfile size too big then don't process and return
$fileLimit = 1024*1024*1.5; // 1.5 Mb
if($fstat["size"] > $fileLimit) {
        $JSONres->error = "File size is too big: please select a smaller file to process queries<br \><br \>";
        ob_start('ob_gzhandler');
        echo json_encode($JSONres);
        ob_end_flush();
        return;
}

$res = json_decode($result, true);

// handle case when input consists of invalid short alpha-numeric string
if ($res===null && json_last_error() !== JSON_ERROR_NONE)
{
        $JSONres->error = "Query sequence error: there's a line that doesn't look like plausible data, but it's not marked as defline or comment.<br \><br \>";
        ob_start('ob_gzhandler');
        echo json_encode($JSONres);
        ob_end_flush();
        return;
}

$res = $res["BlastOutput2"];

// For every sequence perform blast
for ($index = 0; $index < count($res); $index++) {
        $data = $res[$index]["report"]["results"]["search"];

        // Class object defined with default results for each sequence
        $seqData = new stdClass();
        $seqData->Defline = ">" . str_replace(",",";",$data["query_title"]);
        $seqData->usCladeHA = "";
        $seqData->globalCladeHA = "";
        $seqData->usCladeNA = "";
        $seqData->globalCladeNA = "";
        $seqData->usCladeOther = "";
        $seqData->globalCladeOther = "";
        $seqData->subtype = "";
        $seqData->pie = "";
        $seqData->children = array();
        $seqData->message = "";
        $seqData->type = "error";

        //Error handling results captured by message
        if(array_key_exists('message', $data))
        {
                //Update object and add to results
                $seqData->message = $data["message"];
                $JSONres->data[] = $seqData;
                continue;
        }

        //Broad error handeling
        if(count($data["hits"]) < 2)
        {
                //Update object and add to results
                $seqData->message = "No results were returned";
                $JSONres->data[] = $seqData;
                continue;
        }

        //Finer error handling - Empty search
        if($data["query_len"] == 0)
        {
                //Update object and add to results
                $seqData->message = "Empty query sumitted";
                $JSONres->data[] = $seqData;
                continue;
        }

        //Init arrays of interest
        $state = array();
        $haClade = array();
        $naClade = array();
        $haGlobalClade = array();
        $naGlobalClade = array();
        $subtype = array ();
        $blastHits = $data["hits"];

        $seqData->type = substr($blastHits[0]["description"][0]["title"],0,2);

        //Non HA & Non NA sequences
        if($seqData->type != "ha" and $seqData->type != "na")
        {
                for ($i = 0; $i < count($blastHits); $i++)
                {
			$no_result_flag = 0;
			$identityLength = $blastHits[$i]["hsps"][0]["identity"];
			$alignedLength = $blastHits[$i]["hsps"][0]["align_len"];
			$identity = round($identityLength * 100 / $alignedLength, 3);
			$hits = explode("+", $blastHits[$i]["description"][0]["title"]);

                        //If the %identity is too low, break assuming results are in order. This is mainly for blastp, which perc_identity does not work
                        if($identity < 96.0)
                        {
                                break;
                        }

                        if($hits[4] && $hits[5]) {
                                //subtype
                                $seqData->subtype = $hits[4];
                                $seqData->usCladeOther = $hits[5];
                                $seqData->globalCladeOther = $hits[5];
                                break;
                        }
                        else {
                                continue;
                        }
                }
                
                $seqData->message = "This sequence has the best BLAST match to the <span style='color:red'>" . $seqData->subtype . "</span> gene.<h2>";
                $seqData->type = "other";
                $JSONres->data[] = $seqData;
                continue;
        }
        else
        {
		//Fill table and begin to calculate percentages
		for ($i = 0; $i < count($blastHits); $i++)
		{
			$no_result_flag = 0;
			$identityLength = $blastHits[$i]["hsps"][0]["identity"];
			$alignedLength = $blastHits[$i]["hsps"][0]["align_len"];
			$identity = number_format($identityLength * 100 / $alignedLength, 3);
			$hits = explode("+", $blastHits[$i]["description"][0]["title"]);
                        array_push($hits, $identity);
			$blastData = array();

			for ($j = 1; $j < count($hits); $j++)	//This j starts at 1 to leave out db id, less then the count to leave off blank row
			{
				//If the %identity is too low, break assuming results are in order. This is mainly for blastp, which perc_identity does not work
				if((float)$hits[9] < 96.0)
				{
					if($i ==0)
						$no_result_flag = 1;
					break;
				}

				//Add in an ncbi link if on the first item
				if($j == 1 && $hits[$j] != "")
				{
					$hits[$j] = "<a href=\"https://www.ncbi.nlm.nih.gov/nuccore/?term=" . $hits[$j] . "\" target=\"_blank\">" . $hits[$j] . "</a>";
				}
				elseif($j == 1 && $hits[$j] == "")
				{
					$hits[$j] = "ISU VDL";
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
				$blastData[] = $hits[$j];
			}
			if($no_result_flag == 1)
			{
				$seqData->message = "No results with 96% or greater similarity were returned";
				continue 2;
			}
			$seqData->children[] = $blastData;
		}
		$stateChartID = "";
		$pairedChartID = "";
		//Check if first BLAST hit is not HA, use alternative action
		if ($seqData->type == "ha")
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
			foreach($haClade as $idx => &$val) {
				if($val != "" and $val != "Not Tested") {
					$seqData->usCladeHA = $val;
					$seqData->globalCladeHA = $haGlobalClade[$idx];
					break;
				}
			}
		}
		elseif ($seqData->type == "na")
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
			foreach($naClade as $idx => &$val) {
				if($val != "" and $val != "Not Tested") {
					$seqData->usCladeNA = $val;
					$seqData->globalCladeNA = $naGlobalClade[$idx];
					break;
				}
			}
		}
		//non ha na is already taken into consideration before blast result is converted to results
		else
		{
			foreach($subtype as $idx => &$val) {
				if($val != "") {
					$seqData->subtype = $val;
					break;
				}
			}
			continue;
		}

		#Assign subtype based on the top clade
		if(in_array($seqData->usCladeHA, $cladeArray["h1_clade"]))
			$seqData->subtype = "H1";
		if(in_array($seqData->usCladeHA, $cladeArray["h3_clade"]))
			$seqData->subtype = "H3";
		if(in_array($seqData->usCladeNA, $cladeArray["n1_clade"]))
			$seqData->subtype = "N1";
		if(in_array($seqData->usCladeNA, $cladeArray["n2_clade"]))
			$seqData->subtype = "N2";

		$seqData->pie = count($state) . " sequences above 96% identity threshold<br/>";

		//Send back to server
		$seqData->pie .= ' 
		<div id="wrapper' . $index . '">
			<h2>Influenza cases in ISU FLUture with 96% or greater similarity to query sequence</h2> 
			<div class="chartChild"> 
				<h3>State of Detection</h3> 
				<div id=' . $stateChartID . ' class="chartChild"></div> 
			</div>';
		if ($seqData->type == "ha") {
			$seqData->pie .= ' 
				<div class="chartChild"> 
					<h3>Paired Neuraminidase</h3> 
					<div id=' . $pairedChartID . ' class="chartChild"></div> 
				</div> 
			</div>
			';
		}
		else {
			$seqData->pie .= ' 
				<div class="chartChild"> 
					<h3>Paired Hemagglutinin</h3> 
					<div id=' . $pairedChartID . ' class="chartChild"></div> 
				</div> 
			</div>
			';
		}

		$seqData->pie .= "<script>" . $stateCode . "</script>";

		if ($seqData->type == "ha") {
			$seqData->pie .= "<script>" . $naCode . "</script>";
		}
		else {
			$seqData->pie .= "<script>" . $haCode . "</script>";
		}

		$JSONres->data[] = $seqData;
	}
}

//Compression
ob_start('ob_gzhandler');
echo json_encode($JSONres);
ob_end_flush();
return;
#send data back to requester
