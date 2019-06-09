<?php
abstract class DbTable
{
    const REPORTS = "reports";
    const ENVREPORTS = "envReports";
    const DAYSTATS = "dayStats";
}

function config_is_remote()
{
    $config = parse_ini_file("../config.ini");

    if ($config["IsRemote"]) {
        return true;
    } else { return false; }
}

function new_db_conn()
{
    try
    {
        if (config_is_remote())
        {
            // Use MySQL
            $server = "localhost";
            $username = "***REMOVED***";
            $password = "c-aws";
            $database = "***REMOVED***";
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$server;dbname=$database;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_WARNING,
            ];
            
            return new PDO($dsn, $username, $password, $options);
        }
        else
        {
            // Use SQLite
            return new PDO("sqlite:/home/pi/c-aws-data/records.sq3");
        }
    }
    catch (Exception $e) { return false; }
}

function query_database($pdo, $query, $params)
{
    try
    {
        $query = $pdo->prepare($query);
        if (!$query) return false;

        $query->execute($params);
        if (!$query) return false;

        return $query;
    }
    catch (Exception $e) { return false; }
}


function record_for_time($pdo, $time, $table)
{
    $QUERY = "SELECT * FROM %s WHERE Time = ?";
    $time->setTime($time->format("H"), $time->format("i"), 0);

    try
    {
        $query = query_database($pdo, sprintf($QUERY, $table),
            [$time->format("Y-m-d H:i:s")]);
        
        if ($query)
            return $query->fetch();
        else return false;
    }
    catch (Exception $e) { return false; }
}

function fields_in_range($pdo, $start, $end, $fields, $table)
{
    $QUERY = "SELECT %s FROM %s WHERE Time BETWEEN ? AND ?";
    $start->setTime($start->format("H"), $start->format("i"), 0);
    $end->setTime($end->format("H"), $end->format("i"), 0);

    try
    {
        $query = query_database($pdo, sprintf($QUERY, $fields, $table),
            [$start->format("Y-m-d H:i:s"), $end->format("Y-m-d H:i:s")]);

        if ($query)
            return $query->fetchAll();
        else return false;
    }
    catch (Exception $e) { return false; }
}


$pdo = new_db_conn();
$result = record_for_time($pdo,
    new DateTime("2019-05-08 18:00:00"), DbTable::REPORTS);
$result = fields_in_range($pdo,
    new DateTime("2019-05-08 18:00:00"),
    new DateTime("2019-05-08 19:00:00"), "AirT, ExpT", DbTable::REPORTS);
echo json_encode($result);