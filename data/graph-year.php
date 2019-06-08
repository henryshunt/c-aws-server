<?php
date_default_timezone_set("UTC");
include_once("config.php");
include_once("database.php");
include_once("queries.php");

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

# Calculate data range in local time
$range_end = clone $local_time; $range_end -> setTime(0, 0, 0);
$range_start = clone $range_end; $range_start -> sub(new DateInterval("P365D"));
$fields = "Date," . $_GET["fields"];

if (!preg_match("/^[a-zA-Z0-9,_]+$/", $fields)) {
    echo json_encode($data, JSON_NUMERIC_CHECK); exit();
}

if ($db_conn) {
    // Get data in range for specified parameters
    $records = $db_conn -> query(sprintf($SELECT_FIELDS_DAYSTATS, $fields,
        $range_start -> format("Y-m-d"), $range_end -> format("Y-m-d")));

    if (!$records || $records -> num_rows == 0) {
        echo json_encode($data); exit();

    } else {
        $fields = explode(",", $fields);
        $data = array_fill(0, count($fields) - 1, array());

        // Generate each series from retrieved records
        while ($record = $records -> fetch_assoc()) {
            $record_time = date_create_from_format(
                "Y-m-d H:i:s", $record["Date"] . "00:00:00");

            // Create point and add to relevant series
            for ($field = 1; $field <= count($fields) - 1; $field++) {
                $point = array("x" => $record_time
                    -> getTimestamp(), "y" => $record[$fields[$field]]);
                array_push($data[$field - 1], $point);
            }
        }
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);