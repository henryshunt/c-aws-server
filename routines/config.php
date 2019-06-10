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

    function __construct($config_file)
    {
        $config = parse_ini_file($config_file);

        $this->aws_name = $config["Name"];
        $this->aws_time_zone = $config["TimeZone"];
        $this->aws_latitude = $config["Latitude"];
        $this->aws_longitude = $config["Longitude"];
        $this->aws_elevation = $config["Elevation"];

        $this->is_remote = $config["IsRemote"];

        $this->sqlite_database = $config["SQLiteDatabase"];
        $this->mysql_host = $config["MySQLHost"];
        $this->mysql_database = $config["MySQLDatabase"];
        $this->mysql_username = $config["MySQLUsername"];
        $this->mysql_password = $config["MySQLPassword"];
    }

    function get_aws_name()
    {
        return $this->aws_name;
    }

    function get_aws_time_zone()
    {
        return $this->aws_time_zone;
    }

    function get_aws_latitude()
    {
        return $this->aws_latitude;
    }

    function get_aws_longitude()
    {
        return $this->aws_longitude;
    }

    function get_aws_elevation()
    {
        return $this->aws_elevation;
    }

    function get_is_remote()
    {
        return $this->is_remote;
    }

    function get_sqlite_database()
    {
        return $this->sqlite_database;
    }

    function get_mysql_host()
    {
        return $this->mysql_host;
    }

    function get_mysql_database()
    {
        return $this->mysql_database;
    }

    function get_mysql_username()
    {
        return $this->mysql_username;
    }

    function get_mysql_password()
    {
        return $this->mysql_password;
    }
}