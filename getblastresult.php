<?php

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
        $result = shell_exec("/opt/ncbi-blast-2.7.1+/bin/blastp -query " . $path . " -db /var/www/BLASTdb/vdl_flu_aa -outfmt \"6 sseqid pident\" -max_target_seqs=25 2>&1");
}
else
{
	$result = shell_exec("/opt/ncbi-blast-2.7.1+/bin/blastn -query " . $path . " -db /var/www/BLASTdb/vdl_flu_nt -outfmt \"6 sseqid pident\" -max_target_seqs=25 2>&1");
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
	echo "<p>There was a problem with the input provided.</p>";
	//Exit gracefully
	return;
}

for ($i = 0; $i <= count($blastHits); $i++)
{
	//Table row
	$table .= "<tr>";

	$hits = explode("+", $blastHits[$i]);
	for ($j = 1; $j < count($hits); $j++)	//This j starts at 1 to leave out db id, less then the count ot leave off blank row
	{
		$table .= "<td>"; 
		
		#Add in an ncbi link if on the first item
		if($j == 1 && $hits[$j] != "")
		{
			$table .= "<a href=\"https://www.ncbi.nlm.nih.gov/nuccore/?term=" . $hits[$j] . "\">" . $hits[$j] . "</a></td>";
		}
		elseif($j == 1 && $hits[$j] == "")
		{
			$table .= "ISU VDL" . "</td>";
		}
		else
		{
			$table .= $hits[$j] . "</td>";		
		}
	}

	//Clsoe row
	$table .= "</tr>";
}

$table .= "</table>";

//Send back to server
echo $table;

?>
