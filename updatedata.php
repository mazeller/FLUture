<?php
require 'db.php';

// Connect to the database
$connection = db_connect('admin');

//Get the fields
$columns = $_POST['col'];
if($columns == NULL)
        exit;
elseif($columns == "fetchrecords")
{
        $harows = db_select("SELECT distinct case_name, ha_sequence as sequence from `flu` where ha_sequence != ''");
        $narows = db_select("SELECT distinct case_name, na_sequence as sequence from `flu` where na_sequence != ''");
        $rows["haseq"] = $harows;
        $rows["naseq"] = $narows;
        echo json_encode($rows);
        return;
}
elseif($columns == "updaterecords")
{
        if($_POST['type'] == "HA")
        {
            $clade = db_select("SELECT ha_clade_id FROM ha_clade WHERE us_clade = \"" . $_POST['clade'] . "\";");
            if($clade)
            {
                $clade_id = intval($clade[0]["ha_clade_id"]);
                $rows = db_select("SELECT ha_sequence_id, ha_clade_id FROM ha_sequence WHERE sequence=\"" . $_POST['seq'] . "\"" . " AND ha_clade_id != " . $clade_id . ";");
                $size = count($rows);
                echo json_encode($rows);

                if($size > 0)
                {
                   // Update the Clade ID and it should automatically remove the duplicates
                    for($i = 0; $i < $size; $i++)
                    {
                        $seq_id = $rows[$i]['ha_sequence_id'];
                        $updt .= db_select("UPDATE ha_sequence SET ha_clade_id = \"" . $clade_id . "\"" . " WHERE ha_sequence_id = \"" . $seq_id . "\";");
                    }
                    echo "Clades Update Status " . json_encode($updt) . "\n";
                }
                else
                    echo "Correct classification done\n";
            }
            else
            {
                echo $_POST['clade'] . "Clade doesn't exist in records";
            }
        }
        else
        {
            $clade = db_select("SELECT na_clade_id FROM na_clade WHERE us_clade = \"" . $_POST['clade'] . "\";");
            if($clade)
            {
                $clade_id = intval($clade[0]["na_clade_id"]);
                $rows = db_select("SELECT na_sequence_id, na_clade_id FROM na_sequence WHERE sequence=\"" . $_POST['seq'] . "\"" . " AND na_clade_id != " . $clade_id . ";");
                $size = count($rows);
                echo json_encode($rows);

                if($size > 0)
                {
                   // Update the Clade ID and it should automatically remove the duplicates
                    for($i = 1; $i < $size; $i++)
                    {
                        $seq_id = $rows[$i]['na_sequence_id'];
                        $updt .= db_select("UPDATE na_sequence SET na_clade_id = \"" . $clade_id . "\"" . " WHERE na_sequence_id = \"" . $seq_id . "\";");
                    }
                    echo "Clades Update Status " . json_encode($updt) . "\n";
                }
                else
                    echo "Correct Classification done\n";
            }
            else
            {
                echo $_POST['clade'] . "Clade doesn't exist in records";
            }
        }

        return;
}
