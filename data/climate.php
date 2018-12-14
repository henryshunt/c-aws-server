<?php
date_default_timezone_set("UTC");
include_once("../res/php-config.php");
include_once("../res/php-database.php");
include_once("../res/php-queries.php");

$data = array_fill_keys(array("AirT_Avg_Year", "AirT_Min_Year", "AirT_Max_Year"), null);

$fill_value = array_fill_keys(array(
    "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"), null);
$data["AirT_Avg_Months"] = $fill_value;
$data["AirT_Min_Months"] = $fill_value;
$data["AirT_Max_Months"] = $fill_value;
$data["RelH_Avg_Months"] = $fill_value;
$data["WSpd_Avg_Months"] = $fill_value;
$data["WSpd_Max_Months"] = $fill_value;
$data["WDir_Avg_Months"] = $fill_value;
$data["WGst_Avg_Months"] = $fill_value;
$data["WGst_Max_Months"] = $fill_value;
$data["SunD_Ttl_Months"] = $fill_value;
$data["Rain_Ttl_Months"] = $fill_value;
$data["MSLP_Avg_Months"] = $fill_value;
$data["ST10_Avg_Months"] = $fill_value;
$data["ST30_Avg_Months"] = $fill_value;
$data["ST00_Avg_Months"] = $fill_value;

// Try parsing time specified in URL
if (isset($_GET["time"]) == false) { echo json_encode($data); exit(); }
try {
    $url_time = date_create_from_format("Y-m-d\TH-i-00", $_GET["time"]);
    $local_time = clone $url_time;
    $local_time -> setTimezone(new DateTimeZone($aws_time_zone));
}
catch(Exception $e) { echo json_encode($data); exit(); }

if ($db_conn) {
    // Get climate data for that year
    $record = $db_conn -> query(sprintf(
        $GENERATE_YEAR_STATS, $local_time -> format("Y")));

    if ($record) {
        if ($record -> num_rows == 1) {
            // Add record data to final data
            foreach ($row = $record -> fetch_assoc() as $key => $value) {
                if (array_key_exists($key . "_Year", $data)) {
                    $data[$key . "_Year"] = $value;
                }
            }
        }
    }

    // Get climate data for that year by month
    $records = $db_conn -> query(sprintf(
        $GENERATE_MONTHS_STATS, $local_time -> format("Y")));

    if ($records) {
        if ($records -> num_rows >= 1) {
            // Add record data to final data
            while ($row = mysqli_fetch_array($records, MYSQLI_ASSOC)) {
                foreach ($row as $key => $value) {
                    if (array_key_exists($key . "_Months", $data)) {
                        $data[$key . "_Months"][$row["Month"]] = $value;
                    }
                }
            }
        }
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);