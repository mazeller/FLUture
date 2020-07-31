<?php

$filename = "/var/www/BLASTdb/vdl_flu_aa";
$positions = [161, 171, 172, 174, 175, 205];
//$filename = "/var/www/BLASTdb/test_aa";
$aaseqsFile = fopen($filename, "r") or die("Unable to open aa file");

$summary = array();

$sumH3s = 0;
$years = array();
while (!feof($aaseqsFile)){
	$line = fgets($aaseqsFile);
	if (strpos($line, ">") !== false){
		$header = explode("+", $line);
		$subtype = $header[4];
		if (strpos($subtype, "H3") !== false) {
			$year = explode("-", $header[2])[0] . "-01-01";
			$years[] = $year;
			$sumH3s++;
		}
	}
}
fclose($aaseqsFile);
//print_r($summary);



$years = array_unique($years);
$minyear = min($years);
$maxyear = max($years);
$diff = $maxyear - $minyear;
$summary["x"] = $years;
$aaseqsFile = fopen($filename, "r") or die("Unable to open aa file");

while (!feof($aaseqsFile)){
	$line = fgets($aaseqsFile);
	if (strpos($line, ">") !== false){
		$header = explode("+", $line);
		$subtype = $header[4];
		if (strpos($subtype, "H3") !== false) {
			$year = explode("-", $header[2])[0];
			$seq = fgets($aaseqsFile);
		
			$motif = "";
			forEach($positions as $position){
				$motif .= $seq[$position - 1];
			}


 
			//$motif .= "-";
			//$motif .= $year;
			
			if (array_key_exists($motif, $summary)){
				$summary[$motif][$year-$minyear] += 1;
			}
			else{
				$summary[$motif] = array_fill(0, $diff + 1, 0);
				$summary[$motif][$year-$minyear] = 1;
			}
		}
	}
}
fclose($aaseqsFile);

#check that all H3's are used in the motif array
$sumMotifs = 0;
forEach($summary as $motif){
	$sumMotifs += array_sum($motif);
}
$sumMotifs -= array_sum($summary["x"]);

if ($sumH3s != $sumMotifs){
	echo "WARNING: sum of motifs reported is not equal to sum of H3s";
} 



#only keep motifs that represent >1% of H3s over time
forEach($summary as $motifKey => $motif){
	if (array_sum($motif)/$sumH3s < 0.01){
		unset($summary[$motifKey]);
	}
}

chdir("/var/www/html");
$motifJSON = json_encode($summary);
echo $motifJSON;
//print_r($years);
//echo($diff);
?>
