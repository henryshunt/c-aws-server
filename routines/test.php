<?php
include_once("config.php");
include_once("database.php");
include_once("analysis.php");

$config = new Config();
$pdo = new_db_conn($config);

$result = record_for_time($pdo,
    new DateTime("2019-05-08 18:00:00"), DbTable::REPORTS);
// $result = fields_in_range($pdo,
//     new DateTime("2019-05-08 18:00:00"),
//     new DateTime("2019-05-08 19:00:00"), "AirT, ExpT", DbTable::REPORTS);
echo json_encode($result);