<?php
class Config
{
    private $aws_name;
    private $aws_time_zone;
    private $aws_latitude;
    private $aws_longitude;
    private $aws_elevation;

    private $is_remote;

    private $local_database;
    private $local_camera_dir;
    private $local_server_py;
    private $remote_host;
    private $remote_database;
    private $remote_username;
    private $remote_password;
    private $remote_camera_dir;

    function __construct($config_file)
    {
        $config = parse_ini_file($config_file);

        $this->aws_name = $config["Name"];
        $this->aws_time_zone = $config["TimeZone"];
        $this->aws_latitude = $config["Latitude"];
        $this->aws_longitude = $config["Longitude"];
        $this->aws_elevation = $config["Elevation"];

        $this->is_remote = $config["IsRemote"];

        $this->local_database = $config["LocalDatabase"];
        $this->local_camera_dir = $config["LocalCameraDir"];
        $this->local_server_py = $config["LocalServerPy"];
        $this->remote_host = $config["RemoteHost"];
        $this->remote_database = $config["RemoteDatabase"];
        $this->remote_username = $config["RemoteUsername"];
        $this->remote_password = $config["RemotePassword"];
        $this->remote_camera_dir = $config["RemoteCameraDir"];
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

    function get_local_database()
    {
        return $this->local_database;
    }

    function get_local_camera_dir()
    {
        return $this->local_camera_dir;
    }

    function get_local_server_py()
    {
        return $this->local_server_py;
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

    function get_remote_camera_dir()
    {
        return $this->remote_camera_dir;
    }
}