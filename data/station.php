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

$data = array_fill_keys(["Time", "EncT", "CPUT"], null);


// Get record for specified time
$result = record_for_time($db_conn, $url_time, DbTable::ENVREPORTS);
if ($result === false) exit("1");

if ($result === NULL)
{
    // Go back a minute if no record and not in absolute mode
    if (!isset($_GET["abs"]))
    {
        $url_time->sub(new DateInterval("PT1M"));
        $result = record_for_time($db_conn, $url_time, DbTable::ENVREPORTS);
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

$data["Time"] = $url_time->format("Y-m-d H:i:s");
echo json_encode($data, JSON_NUMERIC_CHECK);