<?php
date_default_timezone_set("UTC");
include_once("../res/php-config.php");
include_once("../res/php-database.php");
include_once("../res/php-queries.php");

$data = array_fill_keys(array("Time", "AirT", "ExpT", "RelH", "DewP", "WSpd",
                              "WDir", "WGst", "SunD", "SunD_PHr", "Rain",
                              "Rain_PHr", "StaP", "MSLP", "StaP_PTH", "ST10",
                              "ST30", "ST00"), null);

// Try parsing time specified in URL
if (isset($_GET["time"]) == false) { echo json_encode($data); exit(); }
try {
    $url_time = date_create_from_format("Y-m-d\TH-i-00", $_GET["time"]);
}
catch(Exception $e) { echo json_encode($data); exit(); }

if ($db_conn) {
    // Get record for that time
    $record = $db_conn -> query(sprintf(
        $SELECT_SINGLE_REPORT, $url_time -> format("Y-m-d H:i:s")));
    
    if ($record) {
        if ($record -> num_rows == 0) {
            // Go back a minute if no record and not in absolute mode
            if (!isset($_GET["abs"])) {
                $url_time -> sub(new DateInterval("PT1M"));
                $record = $db_conn -> query(sprintf(
                    $SELECT_SINGLE_REPORT, $url_time -> format("Y-m-d H:i:s")));

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

    // Calculate total sunshine duration over past hour
    $past_hour = (clone $url_time); $past_hour -> sub(new DateInterval("PT59M"));
    $SunD_PHr_record = $db_conn -> query(sprintf($SELECT_PAST_HOUR_REPORTS, "SunD",
        "SunD", $past_hour -> format("Y-m-d H:i:s"), $url_time -> format("Y-m-d H:i:s")));

    if ($SunD_PHr_record && $SunD_PHr_record -> num_rows == 1) {
        $data["SunD_PHr"] = $SunD_PHr_record -> fetch_assoc()["SunD_PHr"];
    }

    // Calculate total rainfall over past hour
    $Rain_PHr_record = $db_conn -> query(sprintf($SELECT_PAST_HOUR_REPORTS, "Rain",
        "Rain", $past_hour -> format("Y-m-d H:i:s"), $url_time -> format("Y-m-d H:i:s")));

    if ($Rain_PHr_record && $Rain_PHr_record -> num_rows == 1) {
        $data["Rain_PHr"] = round($Rain_PHr_record -> fetch_assoc()["Rain_PHr"], 3);
    }

    // Calculate three hour pressure tendency
    if ($data["StaP"] != null) {
        $three_hours_ago = (clone $url_time);
        $three_hours_ago -> sub(new DateInterval("PT3H"));
        
        $StaP_PTH_record = $db_conn -> query(sprintf(
            $SELECT_SINGLE_REPORT, $three_hours_ago -> format("Y-m-d H:i:s")));

        if ($StaP_PTH_record && $StaP_PTH_record -> num_rows == 1) {
            $StaP_PTH = $StaP_PTH_record -> fetch_assoc()["StaP"];

            if ($StaP_PTH != null) {
                $data["StaP_PTH"] = round($data["StaP"] - $StaP_PTH, 1);
            }
        }
    }
}

$data["Time"] = $url_time -> format("Y-m-d H:i:s");
echo json_encode($data, JSON_NUMERIC_CHECK);