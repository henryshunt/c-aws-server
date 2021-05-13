<?php
require_once "vendor/autoload.php";
require_once "php/autoload.php";
require_once "php/utilities.php";

use Aws\Response;
use Aws\HttpException;
use Aws\Config;


try
{
    $config = load_config("config.json");
    if ($config === false)
        throw new HttpException(500, "Failed to load configuration file");

    $pdo = database_connect($config);

    if ($_SERVER["REQUEST_METHOD"] !== "GET" &&
        !api_check_auth($config["uploadUsername"], $config["uploadPassword"]))
    {
        header("WWW-Authenticate: Basic");
        throw new HttpException(401);
    }


    $router = new AltoRouter();
    $router->setBasePath($_SERVER["SCRIPT_NAME"]);
    $router->addMatchTypes(["dt" => "[0-9]{4}-[0-9]{2}-[0-9]{2}T([0-9]{2}-){2}[0-9]{2}"]); // DateTime
    $router->addMatchTypes(["da" => "[0-9]{4}-[0-9]{2}-[0-9]{2}"]); // Date

    $router->map("GET", "/observations", Aws\Endpoints\ObservationsGetEndpoint::class);
    $router->map("GET", "/observations/[dt:time]", Aws\Endpoints\ObservationGetEndpoint::class);
    $router->map("GET", "/statistics/daily", Aws\Endpoints\StatisticsDailyGetEndpoint::class);
    $router->map("GET", "/statistics/daily/[da:date]", Aws\Endpoints\StatisticDailyGetEndpoint::class);


    $match = $router->match();
    if ($match === false)
        throw new HttpException(404);

    $response = new $match["target"]($match["params"], $pdo);
    api_respond($response());
}
catch (HttpException $ex)
{
    if ($ex->getStatus() === 500)
        error_log($ex);
    
    $response = new Response($ex->getStatus());

    if ($ex->getStatus() !== 500 && strlen($ex->getMessage()) > 0)
        $response->setError($ex->getMessage());

    api_respond($response);
}
catch (Exception | Error $ex)
{
    error_log($ex);
    api_respond(new Response(500));
}