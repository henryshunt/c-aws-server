<?php
date_default_timezone_set("UTC");
include_once("../routines/config.php");

$data = array_fill_keys(["Time", "CImg", "SRis", "SSet", "Noon"], null);

try { $config = new Config("../config.ini"); }
catch (Exception $e)
{
    echo json_encode($data);
    exit(); 
}

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

// Get image path for specified time
$image_path = "camera/"
    . $url_time->format("Y/m/d/Y-m-d\TH-i-s") . ".jpg";

// Go back five minutes if no image and not in absolute mode
if (!file_exists($image_path))
{
    if (!isset($_GET["abs"]))
    {
        $url_time->sub(new DateInterval("PT5M"));
        $image_path = "camera/"
            . $url_time->format("Y/m/d/Y-m-d\TH-i-s") . ".jpg";

        if (file_exists($image_path))
            $data["CImg"] = "data/" . $image_path;
        else $url_time->add(new DateInterval("PT5M"));
    }
}
else $data["CImg"] = "data/" . $image_path;

// Calculate sunrise and sunset times
$solar_info = date_sun_info($local_time->getTimestamp(),
    $config->get_aws_latitude(), $config->get_aws_longitude());

if ($solar_info["sunrise"] !== true && $solar_info["sunrise"] !== false)
{
    $sunrise = $solar_info["sunrise"];
    $data["SRis"] = (new DateTime("@$sunrise"))->format("Y-m-d H:i:s");
}

if ($solar_info["sunset"] !== true && $solar_info["sunset"] !== false)
{
    $sunset = $solar_info["sunset"];
    $data["SSet"] = (new DateTime("@$sunset"))->format("Y-m-d H:i:s");
}

if ($solar_info["transit"] !== true && $solar_info["transit"] !== false)
{
    $noon = $solar_info["transit"];
    $data["Noon"] = (new DateTime("@$noon"))->format("Y-m-d H:i:s");
}

$data["Time"] = $url_time->format("Y-m-d H:i:s");
echo json_encode($data, JSON_NUMERIC_CHECK);