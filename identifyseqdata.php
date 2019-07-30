<?php

//Get sequence data in question
$caller_type = $_POST['caller'];
$seq_input = $_POST['seq'];
$case = $_POST['case'];

$temp = tmpfile();
fwrite($temp, $seq_input);
fseek($temp, 0);

//Send to blast
$path = stream_get_meta_data($temp);
$path = $path['uri'];

//Default NT to clean the data
$result = shell_exec("/opt/ncbi-blast-2.7.1+/bin/blastn -query " . $path . " -db /var/www/BLASTdb/vdl_flu_nt -outfmt \"6 sseqid pident\" -perc_identity 96 -num_alignments=5 2>&1");

//Delete temp
fclose($temp); // this removes the file

//Set up table
//Explode into expected
$result = str_replace("\t","+",$result);
$blastHits = explode("\n", $result);

//Broad error handeling
if(count($blastHits) < 2)
{
        //Send error message
        echo $case . "," . $caller_type . "," . $seq_input . ",";
        //Exit gracefully
        return;
}

//Finer error handeling - Empty search
if($blastHits[0] == "Warning: [blastn] Query is Empty!" | $blastHits[0] == "Warning: [blastp] Query is Empty!")
{
        //Send error message
        echo $case . "," . $caller_type . "," . $seq_input . ",";
        //Exit gracefully
        return;
}

//Invalid searches
if(strpos($blastHits[0], "FASTA-Reader: Ignoring invalid residues at position") !== false)
{
        //Send error message
        echo $case . "," . $caller_type . "," . $seq_input . ",";
        //Exit gracefully
        return;
}

//Init arrays of interest
$state = array();
$haClade = array();
$naClade = array();
$subtype = array();

//Check if first BLAST hit is not HA, use alternative action
if (substr($blastHits[0],0,2) != "ha")
{
        $hit = explode("+", $blastHits[0]);
        if (substr($hit[0],0,2) == "na")
        {
                echo $case . "," . $caller_type . "," . $seq_input . "," . $hit[6];
        }
        else
        {
                echo $case . ",ERROR," . $seq_input . "This sequence has the best BLAST match to the gene," . $hit[5];
        }
        return;
}

//Fill table and begin to calculate percentages
for ($i = 0; $i <= count($blastHits); $i++)
{
        $no_result_flag = 0;
        $hits = explode("+", $blastHits[$i]);
        for ($j = 1; $j < count($hits); $j++)   //This j starts at 1 to leave out db id, less then the count to leave off blank row
        {
                //If the %identity is too low, break assuming results are in order. This is mainly for blastp, which perc_identity does not work
                if((float)$hits[7] < 96.0)
                {
                        if($i ==0)
                                $no_result_flag = 1;
                        break;
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
        if($no_result_flag == 1)
        {
                echo $case . "," . $caller_type . "," . $seq_input . ",";
                return;
        }
}

//Process percentages
$haClade = array_count_values($haClade);
$subtype = array_count_values($subtype);

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
echo $case . "," . $caller_type . "," . $seq_input . "," . $topClade;
?>
