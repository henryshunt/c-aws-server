<?php
date_default_timezone_set("UTC");
include_once("../routines/config.php");
include_once("../routines/database.php");
include_once("../routines/analysis.php");

$data = [];

$config = new Config("../config.ini");
if (!$config) { echo json_encode($data); exit(); }
$pdo = new_db_conn($config);
if (!$pdo) { echo json_encode($data); exit(); }

// Parse time specified in URL
if (isset($_GET["time"]))
{
    try
    {
        $url_time = date_create_from_format(
            "Y-m-d\TH-i-s", $_GET["time"]);

        $local_time = clone $url_time;
        $local_time->setTimezone(
            new DateTimeZone($config->get_aws_time_zone()));
    }
    catch (Exception $e) { echo json_encode($data); exit(); }
}
else { echo json_encode($data); exit(); }

// Validate fields specified in URL
if (isset($_GET["fields"]))
{
    if (!preg_match("/^[a-zA-Z0-9,_]+$/", $_GET["fields"])) {
        echo json_encode($data); exit();
    }
}
else { echo json_encode($data); exit(); }

# Calculate data range in local time
$range_end = clone $local_time;
$range_end -> setTime(0, 0, 0);
$range_start = clone $range_end;
$range_start -> sub(new DateInterval("P365D"));

// Get data in range for specified parameters
$result = fields_in_range($pdo, $range_start,
    $range_end, "Date," . $_GET["fields"], DbTable::DAYSTATS);

if ($result !== false && $result !== NULL)
{
    // Fill return data with empty array for each field
    $fields = explode(",", $_GET["fields"]);
    $data = array_fill(0, count($fields), []);

    // Generate each series from retrieved records
    foreach ($result as $record)
    {
        $local = date_create_from_format(
            "Y-m-d H:i:s", $record["Date"] . "00:00:00");

        // Create point and add to relevant series
        for ($field = 0; $field < count($fields); $field++)
        {
            $point = array("x" => $local
                ->getTimestamp(), "y" => $record[$fields[$field]]);
            array_push($data[$field], $point);
        }
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);