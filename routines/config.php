<?php
date_default_timezone_set("UTC");

class Config
{
    private $aws_name;
    private $aws_time_zone;
    private $aws_latitude;
    private $aws_longitude;
    private $aws_elevation;

    private $is_remote;

    private $local_data_dir;
    private $local_software_dir;
    private $remote_host;
    private $remote_database;
    private $remote_username;
    private $remote_password;

    function __construct($config_file)
    {
        $config = parse_ini_file($config_file);

        $this->aws_name = $config["Name"];
        $this->aws_time_zone = $config["TimeZone"];
        $this->aws_latitude = $config["Latitude"];
        $this->aws_longitude = $config["Longitude"];
        $this->aws_elevation = $config["Elevation"];

        $this->is_remote = $config["IsRemote"];

        $this->local_data_dir = $config["LocalDataDir"];
        $this->local_software_dir = $config["LocalSoftwareDir"];
        $this->remote_host = $config["RemoteHost"];
        $this->remote_database = $config["RemoteDatabase"];
        $this->remote_username = $config["RemoteUsername"];
        $this->remote_password = $config["RemotePassword"];

        if (!$this->validate()) 
            throw new Exception("Bad configuration file");
    }

    private function validate()
    {
        if ($this->get_aws_name() == "") return false;
        if ($this->get_aws_time_zone() == "") return false;

        if (!in_array(
            $this->get_aws_time_zone(), DateTimeZone::listIdentifiers()))
            return false;
    
        if ($this->get_aws_latitude() == "") return false;
        if (!is_numeric($this->get_aws_latitude())) return false;
        if ($this->get_aws_longitude() == "") return false;
        if (!is_numeric($this->get_aws_longitude())) return false;
        if ($this->get_aws_elevation() == "") return false;
        if (!is_numeric($this->get_aws_elevation())) return false;

        if ($this->get_is_remote() != "0" &&
            $this->get_is_remote() != "1") return false;

        return true;
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

    function get_local_data_dir()
    {
        return $this->local_data_dir;
    }

    function get_local_software_dir()
    {
        return $this->local_software_dir;
    }

    function get_remote_host()
    {
        return $this->remote_host;
    }

    function get_remote_database()
    {
        return $this->remote_database;
    }

    function get_remote_username()
    {
        return $this->remote_username;
    }

    function get_remote_password()
    {
        return $this->remote_password;
    }
}