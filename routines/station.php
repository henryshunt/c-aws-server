<?php
include_once("config.php");

function get_static_info()
{
    try { $config = new Config("config.ini"); }
    catch (Exception $e) { return NULL; }

    $path = $config->get_local_software_dir();
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

// Run power command if cmd parameter specified
if (isset($_GET["cmd"]))
{
    try { $config = new Config("../config.ini"); }
    catch (Exception $e) { exit(); }

    $path = $config->get_local_data_dir();
    if (!file_exists($path)) exit();

    try
    {
        // Write file to trigger power command in C-AWS software
        if ($_GET["cmd"] == "shutdown")
        {
            if (!file_exists($path . "/shutdown.cmd"))
            {
                $file = fopen($path . "/shutdown.cmd", "w");
                if (!$file) throw new Exception();

                fwrite($file, "");
                fclose($file);
            }
        }
        else if ($_GET["cmd"] == "restart")
        {
            if (!file_exists($path . "/restart.cmd"))
            {
                $file = fopen($path . "/restart.cmd", "w");
                if (!$file) throw new Exception();

                fwrite($file, "");
                fclose($file);
            }
        }
    }
    catch (Exception $e) { exit(); }
}