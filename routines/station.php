<?php
include_once("config.php");

function get_startup_time()
{
    $config = new Config("../config.ini");
    if (!$config) return NULL;

    $path = $config->get_server_py();
    if (!file_exists($path)) return NULL;

    $output = NULL;
    exec("python3 $path get_startup_time", $output);
    echo $output;
}

function get_internal_drive_space()
{
    $config = new Config("../config.ini");
    if (!$config) return NULL;

    $path = $config->get_server_py();
    if (!file_exists($path)) return NULL;

    $output = NULL;
    exec("python3 $path get_internal_drive_space", $output);
    echo $output;
}

function get_camera_drive_space()
{
    $config = new Config("../config.ini");
    if (!$config) return NULL;

    $path = $config->get_server_py();
    if (!file_exists($path)) return NULL;

    $output = NULL;
    exec("python3 $path get_camera_drive_space", $output);
    echo $output;
}

if (isset($_GET["cmd"])
{
    $config = new Config("../config.ini");
    if (!$config) return;

    $path = $config->get_server_py();
    if (!file_exists($path)) return;

    if ($_GET["cmd"] == "do_shutdown")
        exec("python3 $path do_shutdown");
    else if ($_GET["cmd"] == "do_restart")
        exec("python3 $path do_restart");
}