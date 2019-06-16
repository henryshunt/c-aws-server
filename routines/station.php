<?php
include_once("config.php");

function get_static_info()
{
    try { $config = new Config("config.ini"); }
    catch (Exception $e) { return NULL; }

    $path = $config->get_local_software();
    if (!file_exists($path)) return NULL;

    // Execute the server info gather function in the C-AWS software
    try
    {
        $command = "cd " . $path . " && python3 -c \""
            . "import routines.server as server; server.get_static_info()\"";

        $result = NULL;
        exec($command, $result);
        return $result;
    }
    catch (Exception $e) { return NULL; }
}

if (isset($_GET["cmd"]))
{
    try { $config = new Config("../config.ini"); }
    catch (Exception $e) { exit(1); }

    $path = $config->get_local_software();
    if (!file_exists($path)) exit(1);

    try
    {
        $command = NULL;
        if ($_GET["cmd"] == "shutdown")
        {
            $command = "cd " . $path . " && python3 -c \""
                . "import routines.server as server; server.operation_shutdown()\"";
        }
        else if ($_GET["cmd"] == "restart")
        {
            $command = "cd " . $path . " && python3 -c \""
                . "import routines.server as server; server.operation_restart()\"";
        }

        exec($command);
    }
    catch (Exception $e) { exit(1); }
}