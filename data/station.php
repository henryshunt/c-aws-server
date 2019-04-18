<?php
date_default_timezone_set("UTC");
include_once("../res/php-config.php");
include_once("../res/php-database.php");
include_once("../res/php-queries.php");

$data = array_fill_keys(array("Time", "EncT", "CPUT"), null);

// Try parsing time specified in URL
if (isset($_GET["time"]) == false) { echo json_encode($data); exit(); }
try {
    $url_time = date_create_from_format("Y-m-d\TH-i-00", $_GET["time"]);
}
catch(Exception $e) { echo json_encode($data); exit(); }

if ($db_conn) {
    // Get record for that time
    $record = $db_conn -> query(sprintf(
        $SELECT_SINGLE_ENVREPORT, $url_time -> format("Y-m-d H:i:s")));
    
    if ($record) {
        if ($record -> num_rows == 0) {
            // Go back a minute if no record and not in absolute mode
            if (!isset($_GET["abs"])) {
                $url_time -> sub(new DateInterval("PT1M"));
                $record = $db_conn -> query(sprintf(
                    $SELECT_SINGLE_ENVREPORT, $url_time -> format("Y-m-d H:i:s")));

                if ($record) {
                    if ($record -> num_rows == 1) {
                        // Add record data to final data
                        foreach ($row = $record -> fetch_assoc() as $key => $value) {
                            if (array_key_exists($key, $data)) { $data[$key] = $value; }
                        }
                    } else { $url_time -> add(new DateInterval("PT1M")); }
                } else { $url_time -> add(new DateInterval("PT1M")); }
            }

        } else {
            // Add record data to final data
            foreach ($row = $record -> fetch_assoc() as $key => $value) {
                if (array_key_exists($key, $data)) { $data[$key] = $value; }
            }
        }
    }
}

$data["Time"] = $url_time -> format("Y-m-d H:i:s");
echo json_encode($data, JSON_NUMERIC_CHECK);