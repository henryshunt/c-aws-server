<?php
date_default_timezone_set("UTC");
include_once("../data/config.php");
include_once("../data/database.php");
include_once("../data/queries.php");

$data = array();
if (isset($_GET["time"]) == false) { echo json_encode($data); exit(); }
if (isset($_GET["fields"]) == false) { echo json_encode($data); exit(); }

// Try parsing time specified in URL
try {
    $url_time = date_create_from_format("Y-m-d\TH-i-00", $_GET["time"]);
    $local_time = clone $url_time;
    $local_time -> setTimezone(new DateTimeZone($aws_time_zone));
}
catch(Exception $e) { echo json_encode($data); exit(); }

$range_start = clone $local_time; $range_start -> setTime(0, 0, 0);
$range_start -> setTimezone(new DateTimeZone("UTC"));
$range_end = clone $local_time; $range_end -> setTime(23, 59, 0);
$range_end -> setTimezone(new DateTimeZone("UTC"));
$fields = "Time," . $_GET["fields"];

if (!preg_match("/^[a-zA-Z0-9,_]+$/", $fields)) {
    echo json_encode($data, JSON_NUMERIC_CHECK); exit();
}

if (strpos($fields, "AirT") !== false || strpos($fields, "RelH") !== false ||
    strpos($fields, "DewP") !== false || strpos($fields, "WSpd") !== false ||
    strpos($fields, "WDir") !== false || strpos($fields, "WGst") !== false ||
    strpos($fields, "SunD") !== false || strpos($fields, "Rain") !== false ||
    strpos($fields, "StaP") !== false || strpos($fields, "MSLP") !== false) {

    $range_start -> add(new DateInterval("PT1M"));
    $range_end -> add(new DateInterval("PT1M"));
}

if ($db_conn) {
    // Get data in range for specified parameters
    $records = $db_conn -> query(sprintf($SELECT_FIELDS_REPORTS, $fields,
        $range_start -> format("Y-m-d H:i:s"), $range_end -> format("Y-m-d H:i:s")));

    if (!$records || $records -> num_rows == 0) {
        echo json_encode($data); exit();

    } else {
        $fields = explode(",", $fields);
        $data = array_fill(0, count($fields) - 1, array());

        if (in_array("Rain", $fields) == true) { $Rain_Ttl = 0; }
        if (in_array("SunD", $fields) == true) { $SunD_Ttl = 0; }

        // Generate each series from retrieved records
        while ($record = $records -> fetch_assoc()) {
            $utc = date_create_from_format("Y-m-d H:i:s", $record["Time"]);

            // Create point and add to relevant series
            for ($field = 1; $field <= count($fields) - 1; $field++) {
                if ($fields[$field] == "Rain") {
                    if ($record[$fields[$field]] != null) {
                        $Rain_Ttl += $record[$fields[$field]];
                    }

                    $point = array("x" => $utc
                        -> getTimestamp(), "y" => round($Rain_Ttl, 3));
                }

                else if ($fields[$field] == "SunD") {
                    if ($record[$fields[$field]] != null) {
                        $SunD_Ttl += $record[$fields[$field]];
                    }

                    $point = array("x" => $utc
                        -> getTimestamp(), "y" => $SunD_Ttl);
                }
                else {
                    $point = array("x" => $utc
                        -> getTimestamp(), "y" => $record[$fields[$field]]);
                }

                array_push($data[$field - 1], $point);
            }
        }
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);