<?php
date_default_timezone_set("UTC");

class Config
{
    private $aws_name = NULL;
    private $aws_time_zone = NULL;
    private $aws_latitude = NULL;
    private $aws_longitude = NULL;
    private $aws_elevation = NULL;

    private $is_remote = NULL;

    private $local_data_dir = NULL;
    private $local_software_dir = NULL;
    private $remote_upload_pass = NULL;
    private $remote_host = NULL;
    private $remote_database = NULL;
    private $remote_username = NULL;
    private $remote_password = NULL;

    function __construct($config_file)
    {
        $config = parse_ini_file($config_file);
        if (!$config) throw new Exception("Bad configuration file");

        if ($config["Name"] != NULL)
            $this->aws_name = $config["Name"];
        if ($config["TimeZone"] != NULL)
            $this->aws_time_zone = $config["TimeZone"];
        if ($config["Latitude"] != NULL)
            $this->aws_latitude = $config["Latitude"];
        if ($config["Longitude"] != NULL)
            $this->aws_longitude = $config["Longitude"];
        if ($config["Elevation"] != NULL)
            $this->aws_elevation = $config["Elevation"];

        if ($config["IsRemote"] != NULL)
            $this->is_remote = $config["IsRemote"];

        if ($config["LocalDataDir"] != NULL)
            $this->local_data_dir = $config["LocalDataDir"];
        if ($config["LocalSoftwareDir"] != NULL)
            $this->local_software_dir = $config["LocalSoftwareDir"];
        if ($config["RemoteUploadPass"] != NULL)
            $this->remote_upload_pass = $config["RemoteUploadPass"];
        if ($config["RemoteHost"] != NULL)
            $this->remote_host = $config["RemoteHost"];
        if ($config["RemoteDatabase"] != NULL)
            $this->remote_database = $config["RemoteDatabase"];
        if ($config["RemoteUsername"] != NULL)
            $this->remote_username = $config["RemoteUsername"];
        if ($config["RemotePassword"] != NULL)
            $this->remote_password = $config["RemotePassword"];

        if (!$this->validate()) 
            throw new Exception("Bad configuration file");
    }

    private function validate()
    {
        // Convert empty strings to NULL
        if ($this->get_aws_name() == "") $this->aws_name = NULL;
        if ($this->get_aws_time_zone() == "") $this->aws_time_zone = NULL;
        if ($this->get_aws_latitude() == "") $this->aws_latitude = NULL;
        if ($this->get_aws_longitude() == "") $this->aws_longitude = NULL;
        if ($this->get_aws_elevation() == "") $this->aws_elevation = NULL;
        if ($this->get_is_remote() == "") $this->is_remote = NULL;
        if ($this->get_local_data_dir() == "") $this->local_data_dir = NULL;
        if ($this->get_local_software_dir() == "")
            $this->local_software_dir = NULL;
        if ($this->get_remote_upload_pass() == "")
            $this->remote_upload_pass = NULL;
        if ($this->get_remote_host() == "") $this->remote_host = NULL;
        if ($this->get_remote_database() == "")
            $this->get_remote_database = NULL;
        if ($this->get_remote_password() == "")
            $this->remote_password = NULL;

        // Validate the configuration values
        if ($this->get_aws_name() == NULL) return false;
        if ($this->get_aws_time_zone() == NULL) return false;

        if (!in_array(
            $this->get_aws_time_zone(), DateTimeZone::listIdentifiers()))
            return false;
    
        if ($this->get_aws_latitude() == NULL) return false;
        if (!is_numeric($this->get_aws_latitude())) return false;
        if ($this->get_aws_longitude() == NULL) return false;
        if (!is_numeric($this->get_aws_longitude())) return false;
        if ($this->get_aws_elevation() == NULL) return false;
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

    function get_remote_upload_pass()
    {
        return $this->remote_upload_pass;
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