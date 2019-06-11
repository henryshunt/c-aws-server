<?php
date_default_timezone_set("UTC");
include_once("../routines/config.php");
include_once("../routines/database.php");
include_once("../routines/analysis.php");

$data = array_fill_keys(["AirT_Avg_Year", "AirT_Min_Year",
    "AirT_Max_Year"], null);

$fill_value = array_fill_keys(["1", "2", "3", "4", "5", "6", "7",
    "8", "9", "10", "11", "12"], null);
$data["AirT_Avg_Months"] = $fill_value;
$data["AirT_Min_Months"] = $fill_value;
$data["AirT_Max_Months"] = $fill_value;
$data["RelH_Avg_Months"] = $fill_value;
$data["WSpd_Avg_Months"] = $fill_value;
$data["WSpd_Max_Months"] = $fill_value;
$data["WDir_Avg_Months"] = $fill_value;
$data["WGst_Max_Months"] = $fill_value;
$data["SunD_Ttl_Months"] = $fill_value;
$data["Rain_Ttl_Months"] = $fill_value;
$data["MSLP_Avg_Months"] = $fill_value;
$data["ST10_Avg_Months"] = $fill_value;
$data["ST30_Avg_Months"] = $fill_value;
$data["ST00_Avg_Months"] = $fill_value;

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

// Get climate data for that year
$result = stats_for_year($config, $pdo, $local_time->format("Y"));

if ($result !== false && $result !== NULL)
{
    // Add record data to return data
    foreach ($result as $key => $value)
    {
        if (array_key_exists($key, $data))
            $data[$key] = $value;
    }
}

// Get climate data for that year per month
$result = stats_for_months($config, $pdo, $local_time->format("Y"));

if ($result !== false && $result !== NULL)
{
    // Add record data to return data
    foreach ($result as $row)
    {
        foreach ($row as $key => $value)
        {
            if (array_key_exists($key, $data))
                $data[$key][ltrim($row["Month"], "0")] = $value;
        }
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);