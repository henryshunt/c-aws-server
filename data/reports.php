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

try {
    $url_time = date_create_from_format("Y-m-d\TH-i-s", $_GET["time"]);
} catch (Exception $e) { exit("1"); }

$data = array_fill_keys(["Time", "AirT", "ExpT", "RelH", "DewP", "WSpd",
    "WDir", "WGst", "SunD", "SunD_PHr", "Rain", "Rain_PHr", "StaP",
    "MSLP", "StaP_PTH", "ST10", "ST30", "ST00"], null);
    

// Get record for specified time
$result = record_for_time($db_conn, $url_time, DbTable::REPORTS);
if ($result === false) exit("1");

if ($result === NULL)
{
    // Go back a minute if no record and not absolute mode
    if (!isset($_GET["abs"]))
    {
        $url_time->sub(new DateInterval("PT1M"));
        $result = record_for_time($db_conn, $url_time, DbTable::REPORTS);
        if ($result === false) exit("1");

        if ($result !== NULL)
        {
            // Add result data to return data
            foreach ($result as $key => $value)
            {
                if (array_key_exists($key, $data))
                    $data[$key] = $value;
            }
        } else $url_time->add(new DateInterval("PT1M"));
    }
}
else
{
    // Add result data to return data
    foreach ($result as $key => $value)
    {
        if (array_key_exists($key, $data))
            $data[$key] = $value;
    }
}

// Calculate total sunshine duration over past hour
$result = past_hour_total($db_conn, $url_time, "SunD");
if ($result === false) exit("1");

if ($result !== NULL)
{
    if ($result["SunD_PHr"] !== NULL)
        $data["SunD_PHr"] = $result["SunD_PHr"];
}

// Calculate total rainfall over past hour
$result = past_hour_total($db_conn, $url_time, "Rain");
if ($result === false) exit("1");

if ($result !== NULL)
{
    if ($result["Rain_PHr"] != null)
        $data["Rain_PHr"] = round($result["Rain_PHr"], 3);
}

// Calculate three hour pressure tendency
if ($data["StaP"] !== NULL)
{
    $three_hours_ago = clone $url_time;
    $three_hours_ago->sub(new DateInterval("PT3H"));
    
    $result = record_for_time($db_conn, $three_hours_ago, DbTable::REPORTS);
    if ($result === false) exit("1");

    if ($result !== NULL)
    {
        $StaP_PTH = $result["StaP"];
        if ($StaP_PTH !== NULL)
            $data["StaP_PTH"] = round($data["StaP"] - $StaP_PTH, 1);
    }
}

$data["Time"] = $url_time->format("Y-m-d H:i:s");
echo json_encode($data, JSON_NUMERIC_CHECK);