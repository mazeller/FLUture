<?php
require 'db.php';

// Connect to the database
$connection = db_connect();

//Get the fields
$columns = $_POST['col'];
if($columns == NULL)
	exit;
else if($columns == "counts,counts")
{
	$rows = db_select("SELECT LEFT(accession_isu,4) AS 'flu_year',COUNT(LEFT(accession_isu,4)) AS 'flu_count' FROM flu WHERE research=0 GROUP BY LEFT(accession_isu,4);");
	echo json_encode($rows);
	return;	
}
else if($columns == "lastrecord")
{
	$rows = db_select("SELECT received_date FROM `flu` WHERE research=0 ORDER BY received_date DESC LIMIT 1;");
	echo json_encode($rows);
        return;
}
elseif($columns == "accessions")
{
	$rows = db_select("SELECT accession_id from `flu` WHERE accession_id != '' AND LEFT(accession_id,2) = 'A0';");	#Adding the conditional AND to ensure USDA barcode

	//flatten results
	$accessionList = "";	
	for($i == 0; $i <= count($rows); $i++)
	{
		$accessionList .= $rows[$i]['accession_id'] . ",\n";
	}

	//Send list
	ob_start('ob_gzhandler');
	echo $accessionList;
	ob_end_flush();
	return;
}
elseif($columns == "cleandata")
{
//        $rows = db_select("SELECT flu.case_name, ha_sequence, ha_clade from flu right join (SELECT case_name FROM `flu` group by case_name having count(case_name) > 1) t using(case_name)");
        $harows = db_select("SELECT distinct ha_sequence as sequence from flu");
        $narows = db_select("SELECT distinct na_sequence as sequence from flu");
        $rows["haseq"] = $harows;
        $rows["naseq"] = $narows;
        echo json_encode($rows);
        return;
}
else
{
//Check Flags
$whereClause = "WHERE research=0 ";
if($_POST['flags'] == "nu")
	$whereClause .= "AND NOT site_state = 'USA'";
if($_POST['flags'] == "hc")
	$whereClause .= "AND ha_clade != \"\" AND na_clade != \"\"";
//Sanitize
//var_dump(mysqli_real_escape_string($columns));

$flurows = db_select("SELECT $columns FROM `flu` " . $whereClause . ";"); 
$h1rows  = db_select("SELECT us_clade as clade FROM `ha_clade` where subtype = 'H1' order by sort;");
$h3rows  = db_select("SELECT us_clade as clade FROM `ha_clade` where subtype = 'H3' order by sort;");

$rows["fludata"] = $flurows;
$rows["h1clade"] = $h1rows;
$rows["h3clade"] = $h3rows;

//Compression
ob_start('ob_gzhandler');
echo json_encode($rows);
ob_end_flush();
#send data back to requester
}
