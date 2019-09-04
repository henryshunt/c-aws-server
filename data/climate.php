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

try
{
    $url_time = date_create_from_format("Y-m-d\TH-i-s", $_GET["time"]);
    $local_time = clone $url_time;
    $local_time->setTimezone(
        new DateTimeZone($config->get_aws_time_zone()));
}
catch (Exception $e) { exit("1"); }

$data = array_fill_keys(["AirT_Avg_Year", "AirT_Min_Year",
    "AirT_Max_Year"], null);

$fill_value = array_fill_keys(["1", "2", "3", "4", "5", "6", "7",
    "8", "9", "10", "11", "12"], null);
$data["AirT_Avg_Month"] = $fill_value;
$data["AirT_Min_Month"] = $fill_value;
$data["AirT_Max_Month"] = $fill_value;
$data["RelH_Avg_Month"] = $fill_value;
$data["WSpd_Avg_Month"] = $fill_value;
$data["WSpd_Max_Month"] = $fill_value;
$data["WDir_Avg_Month"] = $fill_value;
$data["WGst_Max_Month"] = $fill_value;
$data["SunD_Ttl_Month"] = $fill_value;
$data["Rain_Ttl_Month"] = $fill_value;
$data["MSLP_Avg_Month"] = $fill_value;
$data["ST10_Avg_Month"] = $fill_value;
$data["ST30_Avg_Month"] = $fill_value;
$data["ST00_Avg_Month"] = $fill_value;


// Get climate data for that year
$result = stats_for_year($config, $db_conn, $local_time->format("Y"));
if ($result === false) exit("1");

if ($result !== NULL)
{
    // Add record data to return data
    foreach ($result as $key => $value)
    {
        if (array_key_exists($key, $data))
            $data[$key] = $value;
    }
}

// Get climate data for that year per month
$result = stats_for_months($config, $db_conn, $local_time->format("Y"));
if ($result === false) exit("1");

if ($result !== NULL)
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