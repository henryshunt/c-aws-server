<?php
date_default_timezone_set("UTC");
include_once("../routines/config.php");
include_once("../routines/database.php");
include_once("../routines/analysis.php");

try {
    $config = new Config("../config.ini");
} catch (Exception $e) { exit("1"); }

$db_conn = new_db_conn($config);
if ($db_conn === false) exit("1");

if (!isset($_GET["time"])) exit("1");
if (!isset($_GET["fields"])) exit("1");

try
{
    $url_time = date_create_from_format("Y-m-d\TH-i-s", $_GET["time"]);
    $local_time = clone $url_time;
    $local_time->setTimezone(
        new DateTimeZone($config->get_aws_time_zone()));
}
catch (Exception $e) { exit("1"); }

// Validate fields specified in URL
if (!preg_match("/^[a-zA-Z0-9,_]+$/", $_GET["fields"])) exit("1");

$data = [];


$range_start = clone $local_time;
$range_start->sub(new DateInterval("PT6H"));
$range_start->setTimezone(new DateTimeZone("UTC"));
$range_end = clone $url_time;

// Get data in range for specified parameters
$result = fields_in_range($db_conn, $range_start,
    $range_end, "Time," . $_GET["fields"], DbTable::ENVREPORTS);
if ($result === false) exit("1");

if ($result !== NULL)
{
    // Fill return data with empty array for each field
    $fields = explode(",", $_GET["fields"]);
    $data = array_fill(0, count($fields), []);

    // Generate each series from retrieved records
    foreach ($result as $record)
    {
        $utc = date_create_from_format("Y-m-d H:i:s", $record["Time"]);

        // Create point and add to relevant series
        for ($field = 0; $field < count($fields); $field++)
        {
            $point = array("x" => $utc
                ->getTimestamp(), "y" => $record[$fields[$field]]);
            array_push($data[$field], $point);
        }
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);