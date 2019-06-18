<?php
date_default_timezone_set("UTC");
include_once("../routines/config.php");
include_once("../routines/database.php");
include_once("../routines/analysis.php");

$data = [];

try { $config = new Config("../config.ini"); }
catch (Exception $e)
{
    echo json_encode($data);
    exit(); 
}

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

// Fill return data with empty array for each field
$fields = explode(",", $_GET["fields"]);

$fill_array = [];
for ($i = 0; $i < 12; $i++)
    $fill_array[$i] = ["x" => $i + 1, "y" => NULL];

$data = array_fill(0, count($fields), $fill_array);

// Get climate data for that year per month
$result = stats_for_months($config, $pdo, $local_time->format("Y"));

if ($result !== false && $result !== NULL)
{
    // Generate each series from retrieved records
    foreach ($result as $record)
    {
        // Create point and add to relevant series
        for ($field = 0; $field < count($fields); $field++)
        {
            $data[$field][$record["Month"] - 1] = 
                ["x" => $record["Month"], "y" => $record[$fields[$field] . "_Month"]];
        }
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);