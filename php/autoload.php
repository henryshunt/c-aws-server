<?php
spl_autoload_register(function ($className)
{
    if (!starts_with($className, "Aws\\"))
        return false;

    $className = str_replace("\\", "/", $className);
    $className = str_replace("Aws/", "", $className);
    $className = __DIR__ . "/$className.php";

    if (file_exists($className))
    {
        require_once $className;
        return true;
    }
    else return false;
});