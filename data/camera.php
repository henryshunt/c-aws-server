<?php
date_default_timezone_set("UTC");
include_once("../routines/config.php");

$data = array_fill_keys(["Time", "CImg", "SRis", "SSet"], null);

$config = new Config("../config.ini");
if (!$config) { echo json_encode($data); exit(); }

// Parse time specified in URL
if (isset($_GET["time"]))
{
    try
    {
        $url_time = date_create_from_format(
            "Y-m-d\TH-i-s", $_GET["time"]);
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
            $data["CImg"] = $image_path;
        else $url_time->add(new DateInterval("PT5M"));
    }
}
else $data["CImg"] = $image_path;

$data["Time"] = $url_time->format("Y-m-d H:i:s");
echo json_encode($data, JSON_NUMERIC_CHECK);