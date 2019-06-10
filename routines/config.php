<?php
class Config
{
    private $aws_name;
    private $aws_time_zone;
    private $aws_latitude;
    private $aws_longitude;
    private $aws_elevation;

    private $is_remote;

    private $sqlite_database;
    private $mysql_host;
    private $mysql_database;
    private $mysql_username;
    private $mysql_password;

    function __construct()
    {
        $config = parse_ini_file("../config.ini");

        $aws_name = $config["Name"];
        $aws_time_zone = $config["TimeZone"];
        $aws_latitude = $config["Latitude"];
        $aws_longitude = $config["Longitude"];
        $aws_elevation = $config["Elevation"];

        $is_remote = $config["IsRemote"];

        $sqlite_database = $config["SQLiteDatabase"];
        $mysql_host = $config["MySQLHost"];
        $mysql_database = $config["MySQLDatabase"];
        $mysql_username = $config["MySQLUsername"];
        $mysql_password = $config["MySQLPassword"];
    }

    function get_aws_name()
    {
        return $aws_name;
    }

    function get_aws_time_zone()
    {
        return $aws_time_zone;
    }

    function get_aws_latitude()
    {
        return $aws_latitude;
    }

    function get_aws_longitude()
    {
        return $aws_longitude;
    }

    function get_aws_elevation()
    {
        return $aws_elevation;
    }

    function get_is_remote()
    {
        return $is_remote;
    }

    function get_sqlite_database()
    {
        return $sqlite_database;
    }

    function get_mysql_host()
    {
        return $mysql_host;
    }

    function get_mysql_database()
    {
        return $mysql_database;
    }

    function get_mysql_username()
    {
        return $mysql_username;
    }

    function get_mysql_password()
    {
        return $mysql_password;
    }
}