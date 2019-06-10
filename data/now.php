<?php
date_default_timezone_set("UTC");
include_once("../routines/config.php");
include_once("../routines/database.php");
include_once("../routines/analysis.php");

// Array for return data
$data = array_fill_keys(["Time", "AirT", "ExpT", "RelH", "DewP",
    "WSpd", "WDir", "WGst", "SunD", "SunD_PHr", "Rain", "Rain_PHr",
     "StaP", "MSLP", "StaP_PTH", "ST10", "ST30", "ST00"], null);

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
    }
    catch(Exception $e) { echo json_encode($data); exit(); }
}
else { echo json_encode($data); exit(); }

// Get record for specified time
$result = record_for_time($pdo, $url_time, DbTable::REPORTS);
if ($result === false) { echo json_encode($data); exit(); }

if ($result === NULL)
{
    // Go back a minute if no record and not absolute mode
    if (!isset($_GET["abs"]))
    {
        $url_time->sub(new DateInterval("PT1M"));
        $result = record_for_time($pdo, $url_time, DbTable::REPORTS);

        if ($result !== false && $result !== NULL)
        {
            // Add result data to return data
            foreach ($result as $key=>$value)
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
    foreach ($result as $key=>$value)
    {
        if (array_key_exists($key, $data))
            $data[$key] = $value;
    }
}

// Calculate total sunshine duration over past hour
$SunD_PHr_result = past_hour_total($pdo, $url_time, "SunD");

if ($SunD_PHr_result !== false && $SunD_PHr_result !== NULL)
{
    if ($SunD_PHr_result["SunD_PHr"] !== NULL)
        $data["SunD_PHr"] = $SunD_PHr_result["SunD_PHr"];
}

// Calculate total rainfall over past hour
$Rain_PHr_result = past_hour_total($pdo, $url_time, "Rain");

if ($Rain_PHr_result !== false && $Rain_PHr_result !== NULL)
{
    if ($Rain_PHr_result["Rain_PHr"] != null)
        $data["Rain_PHr"] = round($Rain_PHr_result["Rain_PHr"], 3);
}

// Calculate three hour pressure tendency
if ($data["StaP"] !== NULL)
{
    $three_hours_ago = clone $url_time;
    $three_hours_ago->sub(new DateInterval("PT3H"));
    
    $StaP_PTH_result = record_for_time(
        $pdo, $three_hours_ago, DbTable::REPORTS);

    if ($StaP_PTH_result !== false && $StaP_PTH_result !== NULL)
    {
        $StaP_PTH = $StaP_PTH_result["StaP"];
        if ($StaP_PTH !== NULL)
            $data["StaP_PTH"] = round($data["StaP"] - $StaP_PTH, 1);
    }
}

$data["Time"] = $url_time->format("Y-m-d H:i:s");
echo json_encode($data, JSON_NUMERIC_CHECK);