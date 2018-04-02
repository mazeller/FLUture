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
	$rows = db_select("SELECT received_date FROM `flu` WHERE research=0 ORDER BY ID DESC LIMIT 1;");
	echo json_encode($rows);
        return;
}
elseif($columns == "accessions")
{
	$rows = db_select("SELECT accession_id from `flu` WHERE accession_id != ''");

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

//Check Flags
$whereClause = "WHERE research=0 ";
if($_POST['flags'] == "nu")
	$whereClause .= "AND NOT site_state = 'USA'";
if($_POST['flags'] == "hc")
	$whereClause .= "AND ha_clade != \"\" AND na_clade != \"\"";
//Sanitize
//var_dump(mysqli_real_escape_string($columns));

$rows = db_select("SELECT $columns FROM `flu` " . $whereClause . ";"); 

//Compression
ob_start('ob_gzhandler');
echo json_encode($rows);
ob_end_flush();
#send data back to requester
