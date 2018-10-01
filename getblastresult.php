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

//Get sequence data in question
$seq_input = $_POST['seq'];
$blast_type = $_POST['blast'];

$temp = tmpfile();
fwrite($temp, $seq_input);
fseek($temp, 0);

//Send to blast
$path = stream_get_meta_data($temp);
$path = $path['uri'];

//Default NT, unless type = AA
if($blast_type == "aa")
{
        $result = shell_exec("/opt/ncbi-blast-2.7.1+/bin/blastp -query " . $path . " -db /var/www/BLASTdb/vdl_flu_aa -outfmt \"6 sseqid pident\" -num_alignments=100 2>&1");
}
else
{
	$result = shell_exec("/opt/ncbi-blast-2.7.1+/bin/blastn -query " . $path . " -db /var/www/BLASTdb/vdl_flu_nt -outfmt \"6 sseqid pident\" -perc_identity 96 -num_alignments=100 2>&1");
}

//Delete temp
fclose($temp); // this removes the file

//Set up table
$table =  "<table class=\"wd-Table--striped wd-Table--hover\">";
$table .= "<thead><th>USDA Barcode</th><th>Received date</th><th>State</th><th>Subtype</th><th>HA clade</th><th>NA clade</th><th>% identity</th></thead>";
//Explode into expected
$result = str_replace("\t","+",$result);
$blastHits = explode("\n", $result);

//Broad error handeling
if(count($blastHits) < 2)
{
	//Send error message
	echo "<p>No results were returned.</p>";
	//Exit gracefully
	return;
}

//Finer error handeling - Empty search
if($blastHits[0] == "Warning: [blastn] Query is Empty!" | $blastHits[0] == "Warning: [blastp] Query is Empty!")
{
	//Send error message
	echo "<p>Empty query submitted.</p>";
	//Exit gracefully
	return;
}

//Invalid searches
if(strpos($blastHits[0], "FASTA-Reader: Ignoring invalid residues at position") !== false)
{
        //Send error message
        echo "<p>Potentially invalid characters detected.</p>";
        //Exit gracefully
        return;	
}

//Init arrays of interest
$state = array();
$haClade = array();
$naClade = array();
$subtype = array ();

//Fill table and begin to calculate percentages
for ($i = 0; $i <= count($blastHits); $i++)
{
	//Table row
	$table .= "<tr>";

	$hits = explode("+", $blastHits[$i]);
	for ($j = 1; $j < count($hits); $j++)	//This j starts at 1 to leave out db id, less then the count to leave off blank row
	{
		//If the %identity is too low, break assuming results are in order. This is mainly for blastp, which perc_identity does not work
		if((float)$hits[7] < 96.0)
		{
			break;
		}

		$table .= "<td>"; 
		
		//Add in an ncbi link if on the first item
		if($j == 1 && $hits[$j] != "")
		{
			$table .= "<a href=\"https://www.ncbi.nlm.nih.gov/nuccore/?term=" . $hits[$j] . "\" target=\"_blank\">" . $hits[$j] . "</a></td>";
		}
		elseif($j == 1 && $hits[$j] == "")
		{
			$table .= "ISU VDL" . "</td>";
		}
		else
		{
			$table .= $hits[$j] . "</td>";		
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
	}

	//Close row
	$table .= "</tr>";
}

$table .= "</table>";

//Process percentages
$stateProp = array_count_values($state);
$haClade = array_count_values($haClade);
$naProp = array_count_values($naClade);
$subtype = array_count_values($subtype);
$stateCode = generatePieChart($stateProp,"stateChart");
$naCode = generatePieChart($naProp,"naChart");

//Get most populous item from array
$topClade = array_search(max($haClade),$haClade);
$topSubtype = array_search(max($subtype),$subtype);

//Heuristic; check if left char is an H, if so take 2 chars, else take none
if($topSubtype[0] == "H")
{
	$topSubtype = substr($topSubtype,0,2);
}
else
	$topSubtype = "";

//Send back to server
echo "<br/><h2>This sequence has the best BLAST match to: <spani style='color:red'>" . $topSubtype . " " . $topClade . "</span></h2><br/>";
echo <<<EOF
<div id="wrapper"> 
        <h2>Influenza cases in ISU FLUture with 96% or greater similarity to query sequence</h2> 
        <div class="chartChild"> 
                <h3>State of Detection</h3> 
                <div id="stateChart" class="chartChild"></div> 
        </div> 
        <div class="chartChild"> 
                <h3>Paired Neuraminidase</h3> 
                <div id="naChart" class="chartChild"></div> 
        </div> 
</div>
EOF;

echo count($state) . " sequences above 96% identity threshold<br/>";
echo $table;

echo "<script>";
echo $stateCode;
echo $naCode;
echo "</script>";
?>
