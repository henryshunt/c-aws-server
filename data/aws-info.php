<?php
date_default_timezone_set("UTC");
include_once("../routines/config.php");

$data = array_fill_keys(["Name", "TimeZone", "Latitude",
    "Longitude", "Elevation"], null);

try { $config = new Config("../config.ini"); }
catch (Exception $e)
{
    echo "null";
    exit(); 
}

$data["Name"] = $config->get_aws_name();
$data["TimeZone"] = $config->get_aws_time_zone();
$data["Latitude"] = $config->get_aws_latitude();
$data["Longitude"] = $config->get_aws_longitude();
$data["Elevation"] = $config->get_aws_elevation();

echo json_encode($data, JSON_NUMERIC_CHECK);