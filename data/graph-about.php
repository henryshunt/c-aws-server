<?php
date_default_timezone_set("UTC");
include_once("../res/php-config.php");
include_once("../res/php-database.php");
include_once("../res/php-queries.php");

$data = array();
if (isset($_GET["time"]) == false) { echo json_encode($data); exit(); }
if (isset($_GET["fields"]) == false) { echo json_encode($data); exit(); }

// Try parsing time specified in URL
try {
    $url_time = date_create_from_format("Y-m-d\TH-i-00", $_GET["time"]);
}
catch(Exception $e) { echo json_encode($data); exit(); }

$range_start = clone $url_time; $range_start -> sub(new DateInterval("PT6H"));
$range_end = clone $url_time;
$fields = "Time," . $_GET["fields"];

if (!preg_match("/^[a-zA-Z0-9,_]+$/", $fields)) {
    echo json_encode($data, JSON_NUMERIC_CHECK); exit();
}

if ($db_conn) {
    // Get data in range for specified parameters        
    $records = $db_conn -> query(sprintf($SELECT_FIELDS_ENVREPORTS, $fields,
        $range_start -> format("Y-m-d H:i:s"), $range_end -> format("Y-m-d H:i:s")));

    if (!$records || $records -> num_rows == 0) {
        echo json_encode($data); exit();

    } else {
        $fields = explode(",", $fields);
        $data = array_fill(0, count($fields) - 1, array());

        // Generate each series from retrieved records
        while ($record = $records -> fetch_assoc()) {
            $utc = date_create_from_format("Y-m-d H:i:s", $record["Time"]);

            // Create point and add to relevant series
            for ($field = 1; $field <= count($fields) - 1; $field++) {
                $point = array("x" => $utc
                    -> getTimestamp(), "y" => $record[$fields[$field]]);

                array_push($data[$field - 1], $point);
            }
        }
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);