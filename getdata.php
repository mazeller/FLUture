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
	$rows = db_select("SELECT LEFT(accession_isu,4) AS 'flu_year',COUNT(LEFT(accession_isu,4)) AS 'flu_count' FROM flu GROUP BY LEFT(accession_isu,4);");
	echo json_encode($rows);
	return;	
}

//Check Flags
$whereClause = "";
if($_POST['flags'] == "nu")
	$whereClause = "WHERE NOT site_state = 'USA'";
if($_POST['flags'] == "hc")
	$whereClause = "WHERE ha_clade != \"\" AND na_clade != \"\"";
//Sanitize
//var_dump(mysqli_real_escape_string($columns));

$rows = db_select("SELECT $columns FROM `flu` " . $whereClause . ";"); 
echo json_encode($rows);

#send data back to requester
