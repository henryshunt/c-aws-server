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
    $fields = "Time," . $_GET["fields"];
    if (!preg_match("/^[a-zA-Z0-9,_]+$/", $fields)) {
        echo json_encode($data); exit();
    }
}
else { echo json_encode($data); exit(); }

// Get local day bounds in UTC
$range_start = clone $local_time;
$range_start->setTime(0, 0, 0);
$range_start->setTimezone(new DateTimeZone("UTC"));
$range_end = clone $local_time;
$range_end->setTime(23, 59, 0);
$range_end->setTimezone(new DateTimeZone("UTC"));

// Averaged/totalled parameters are valid for previous minute, not current,
// and we don't want anything from previous day
if (strpos($fields, "AirT") !== false || strpos($fields, "RelH") !== false ||
    strpos($fields, "DewP") !== false || strpos($fields, "WSpd") !== false ||
    strpos($fields, "WDir") !== false || strpos($fields, "WGst") !== false ||
    strpos($fields, "SunD") !== false || strpos($fields, "Rain") !== false ||
    strpos($fields, "StaP") !== false || strpos($fields, "MSLP") !== false)
{
    $range_start -> add(new DateInterval("PT1M"));
    $range_end -> add(new DateInterval("PT1M"));
}

// Get data in range for specified parameters
$result = fields_in_range(
    $pdo, $range_start, $range_end, $fields, DbTable::REPORTS);

if ($result !=== false && $result != NULL)
{
    // Fill return data with empty array for each field
    $fields = explode(",", $fields);
    $data = array_fill(0, count($fields) - 1, []);

    if (in_array("Rain", $fields)) $Rain_Ttl = 0;
    if (in_array("SunD", $fields)) $SunD_Ttl = 0;

    // Generate each series from retrieved records
    foreach ($record in $result)
    {
        $utc = date_create_from_format("Y-m-d H:i:s", $record["Time"]);

        // Create point and add to relevant series
        for ($field = 1; $field <= count($fields) - 1; $field++)
        {
            if ($fields[$field] == "Rain")
            {
                if ($record[$fields[$field]] != null)
                    $Rain_Ttl += $record[$fields[$field]];

                $point = array("x" => $utc
                    ->getTimestamp(), "y" => round($Rain_Ttl, 3));
            }
            else if ($fields[$field] == "SunD")
            {
                if ($record[$fields[$field]] != null)
                    $SunD_Ttl += $record[$fields[$field]];

                $point = array("x" => $utc
                    ->getTimestamp(), "y" => $SunD_Ttl);
            }
            else
            {
                $point = array("x" => $utc
                    ->getTimestamp(), "y" => $record[$fields[$field]]);
            }

            array_push($data[$field - 1], $point);
        }
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);