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

$data = array_fill_keys(["Time", "AirT_Avg", "AirT_Min", "AirT_Max",
    "RelH_Avg", "RelH_Min", "RelH_Max", "DewP_Avg", "DewP_Min",
    "DewP_Max", "WSpd_Avg", "WSpd_Min", "WSpd_Max", "WDir_Avg", 
    "WGst_Avg", "WGst_Min", "WGst_Max", "SunD_Ttl", "Rain_Ttl",
    "MSLP_Avg", "MSLP_Min", "MSLP_Max", "ST10_Avg", "ST10_Min",
    "ST10_Max", "ST30_Avg", "ST30_Min", "ST30_Max", "ST00_Avg",
    "ST00_Min", "ST00_Max"], null);


// Get record for specified date
$result = record_for_time($db_conn, $local_time, DbTable::DAYSTATS);
if ($result === false) exit("1");

if ($result === NULL)
{
    // Go back a minute if no record and not in absolute mode
    if (!isset($_GET["abs"]))
    {
        $url_time->sub(new DateInterval("PT1M"));
        $local_time->sub(new DateInterval("PT1M"));
        $result = record_for_time($db_conn, $local_time, DbTable::DAYSTATS);

        if ($result !== false)
        {
            if ($result !== NULL)
            {
                // Add result data to return data
                foreach ($result as $key => $value)
                {
                    if (array_key_exists($key, $data))
                        $data[$key] = $value;
                }
            } else $url_time->add(new DateInterval("PT1M"));
        } else exit("1");
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

$data["Time"] = $url_time->format("Y-m-d H:i:s");
echo json_encode($data, JSON_NUMERIC_CHECK);