<?php
date_default_timezone_set("UTC");
include_once("../res/php-config.php");
include_once("../res/php-database.php");
include_once("../res/php-queries.php");

$data = array_fill_keys(array("Time", "AirT_Avg", "AirT_Min", "AirT_Max",
                              "RelH_Avg", "RelH_Min", "RelH_Max", "DewP_Avg",
                              "DewP_Min", "DewP_Max", "WSpd_Avg", "WSpd_Min",
                              "WSpd_Max", "WDir_Avg", "WDir_Min", "WDir_Max",
                              "WGst_Avg", "WGst_Min", "WGst_Max", "SunD_Ttl",
                              "Rain_Ttl", "MSLP_Avg", "MSLP_Min", "MSLP_Max",
                              "ST10_Avg", "ST10_Min", "ST10_Max", "ST30_Avg",
                              "ST30_Min", "ST30_Max", "ST00_Avg", "ST00_Min",
                              "ST00_Max"), null);

// Try parsing time specified in URL
if (isset($_GET["time"]) == false) { echo json_encode($data); exit(); }
try {
    $url_time = date_create_from_format("Y-m-d\TH-i-00", $_GET["time"]);
    $local_time = clone $url_time;
    $local_time -> setTimezone(new DateTimeZone($aws_time_zone));
}
catch(Exception $e) { echo json_encode($data); exit(); }

if ($db_conn) {
    // Get record for that time
    $record = $db_conn -> query(sprintf(
        $SELECT_SINGLE_DAYSTAT, $local_time -> format("Y-m-d")));

    if ($record) {
        if ($record -> num_rows == 0) {
            // Go back a minute if no record and not in absolute mode
            if (!isset($_GET["abs"])) {
                $url_time -> sub(new DateInterval("PT1M"));
                $local_time -> sub(new DateInterval("PT1M"));

                $record = $db_conn -> query(sprintf(
                    $SELECT_SINGLE_DAYSTAT, $local_time -> format("Y-m-d")));

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