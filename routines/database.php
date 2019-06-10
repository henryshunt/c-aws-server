<?php
function new_db_conn($config)
{
    try
    {
        if ($config->get_is_remote())
        {
            // Use MySQL
            $host = $config->get_mysql_host();
            $database = $config->get_mysql_database();
            $username = $config->get_mysql_username();
            $password = $config->get_mysql_password();
            $charset = "utf8mb4";

            $dsn = "mysql:host=$host;dbname=$database;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            return new PDO($dsn, $username, $password, $options);
        }
        else
        {
            // Use SQLite
            $database = $config->get_sqlite_database();
            return new PDO("sqlite:$database");
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

abstract class DbTable
{
    const REPORTS = "reports";
    const ENVREPORTS = "envReports";
    const DAYSTATS = "dayStats";
}