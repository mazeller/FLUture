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
else if($columns == "accessions")
{
	$rows = db_select("SELECT accession_id from `flu` WHERE accession_id != '' AND LEFT(accession_id,2) = 'A0';");	#Adding the conditional AND to ensure USDA barcode

	//flatten results
	$accessionList = "";
        $size = count($rows);
	for($i = 0; $i < $size; $i++)
	{
		$accessionList .= $rows[$i]['accession_id'] . ",\n";
	}

	//Send list
	ob_start('ob_gzhandler');
	echo $accessionList;
	ob_end_flush();
	return;
}
else if ($columns == "orders") 
{
        ob_start('ob_gzhandler');
	include('js/orders.json');
        ob_end_flush();
	return;
	// make it only two queries
	$harows = db_select("SELECT us_clade as clade FROM `ha_clade` where subtype != '' order by sort;");
	$narows = db_select("SELECT us_clade as clade FROM `na_clade` where subtype != '' order by sort;");
	$diagInfo = db_select("SELECT diagnostic_code as diag_code, diagnostic_text as diag_text FROM `diagnostic_code` order by diag_code;");
	$orders["ha_clade"] = $harows;
	$orders["na_clade"] = $narows;
	$orders["diag_info"] = $diagInfo; 
        ob_start('ob_gzhandler');
        echo json_encode($orders);
        ob_end_flush();

	return;

}
else
{

        //ob_start('ob_gzhandler');
	//include('getdata_txt.php');
        //ob_end_flush();
	//return;
        //Check Flags
        $whereClause = "WHERE research=0 ";
        if($_POST['flags'] == "nu")
	    $whereClause .= "AND NOT site_state = 'USA' AND NOT site_state = 'Mexico'";
        if($_POST['flags'] == "hc")
	    $whereClause .= "AND ha_clade != \"\" AND na_clade != \"\"";

        $flurows = db_select("SELECT $columns FROM `flu` " . $whereClause . ";"); 

        //Compression
        ob_start('ob_gzhandler');
        echo json_encode($flurows);
        ob_end_flush();
	return;
        #send data back to requester
}
