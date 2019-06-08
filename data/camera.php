<?php
date_default_timezone_set("UTC");
include_once("config.php");
include_once("database.php");

$data = array_fill_keys(array("Time", "CImg", "SRis", "SSet"), null);

// Try parsing time specified in URL
if (isset($_GET["time"]) == false) { echo json_encode($data); exit(); }
try {
    $url_time = date_create_from_format("Y-m-d\TH-i-00", $_GET["time"]);
}
catch(Exception $e) { echo json_encode($data); exit(); }
                              
// Get image for that time
$image_path = "camera/" . $url_time -> format("Y/m/d/Y-m-d\TH-i-s") . ".jpg";

// Go back five minutes if no image and not in absolute mode
if (file_exists($image_path) == false) {
    if (!isset($_GET["abs"])) {
        $url_time -> sub(new DateInterval("PT5M"));
        $image_path = "camera/" . $url_time -> format("Y/m/d/Y-m-d\TH-i-s") . ".jpg";

        if (file_exists($image_path) == true) {
            $data["CImg"] = "data/camera/"
                . $url_time -> format("Y/m/d/Y-m-d\TH-i-s") . ".jpg";
        } 
        else { $url_time -> add(new DateInterval("PT5M")); }
    }
}
else
{
    $data["CImg"] = "data/camera/"
        . $url_time -> format("Y/m/d/Y-m-d\TH-i-s") . ".jpg";
}

$data["Time"] = $url_time -> format("Y-m-d H:i:s");
echo json_encode($data, JSON_NUMERIC_CHECK);