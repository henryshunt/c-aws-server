<?php
use Aws\Response;
use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Parses and validates JSON-formatted configuration data from a file.
 * @param string $path - The path to the configuration file.
 * @return array|bool An associative array containing the parsed configuration data, or false if
 * there was a parsing error or validation failure.
 */
function load_config(string $path)
{
    $string = file_get_contents($path);
    if ($string === false)
        return false;

    $json = json_decode($string);
    if (gettype($json) !== "object")
        return false;

    $json = (array)$json;

    // $validator = V
    //     ::key("databaseHost", V::stringType()->length(1, null))
    //     ->key("databaseName", V::stringType()->length(1, null))
    //     ->key("databaseUsername", V::stringType()->length(1, null))
    //     ->key("databasePassword", V::stringType()->length(1, null))
    //     ->key("apiUsername", V::stringType()->length(1, null));

    // try
    // {
    //     $validator->check($json);
    //     return $json;
    // }
    // catch (ValidationException $ex)
    // {
    //     return false;
    // }

    return $json;
}

/**
 * Opens a connection to the database using the provided configuration data.
 * @param array $config - An associative array containing the configuration data.
 * @throws PDOException if there is any error.
 * @return PDO The resulting @see PDO object.
 */
function database_connect($config): PDO
{
    $options =
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // The actual AWS hardware uses SQLite and the remote server uses MySQL
    if ($config["isRemote"])
    {
        $host = $config["remoteHost"];
        $database = $config["remoteDatabase"];
        $username = $config["remoteUsername"];
        $password = $config["remotePassword"];

        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
        return new PDO($dsn, $username, $password, $options);
    }
    else
    {
        $database = $config["localDataDir"] . "/data.sq3";
        return new PDO("sqlite:$database", null, null, $options);
    }
}

/**
 * Queries a database and returns the results.
 * @param PDO $pdo - The @see PDO object to access the database with.
 * @param string $sql - The SQL query to run. Any values should be replaced with question marks.
 * @param array|null $values - The values to put into the SQL query. There should be the same
 * number of values as there are question marks in $sql.
 * @throws PDOException if there is any error.
 * @return array|bool The records selected by the query, or true if the query is not a SELECT
 * query.
 */
function database_query(PDO $pdo, string $sql, ?array $values = null)
{
    $query = $pdo->prepare($sql);
    $query->execute($values);

    if (starts_with(strtolower($sql), "select"))
        return $query->fetchAll();
    else return true;
}

/**
 * Finalises the response to an API request. Outputs the content, sets the headers and status code,
 * and terminates the script.
 * @param Response $response - The @see Response object to finalise the response with.
 */
function api_respond(Response $response): void
{
    header("Content-Type: application/json");

    if ($response->getBody() !== null)
        echo $response->getBody();
    else
    {
        $json = ["status" => $response->getStatus()];

        if ($response->getError() !== null)
            $json["error"] = $response->getError();

        echo json_encode($json);
    }

    http_response_code($response->getStatus());
    exit();
}

/**
 * Determines whether the request is authenticated. HTTP basic authentication is used.
 * @param string $correctUsername - The username to authenticate against.
 * @param string $correctPassword - The password to authenticate against.
 * @return bool true if the request credentials match the correct ones, otherwise false.
 */
function api_check_auth(string $correctUsername, string $correctPassword): bool
{
    if (array_key_exists("PHP_AUTH_USER", $_SERVER))
    {
        if ($_SERVER["PHP_AUTH_USER"] === $correctUsername &&
            $_SERVER["PHP_AUTH_PW"] === $correctPassword)
        {
            return true;
        }
    }
    
    return false;
}

/**
 * Determines whether a string starts with another string.
 * @param string $string - The string to check inside of.
 * @param string $start - The string to check for at the start.
 * @return bool true if the string starts with the start string, otherwise false.
 */
function starts_with(string $string, string $start): bool
{
    return substr($string, 0, strlen($start)) === $start;
}

/**
 * Determines whether a string ends with another string.
 * @param string $string - The string to check inside of.
 * @param string $end - The string to check for at the end.
 * @return bool true if the string ends with the start string, otherwise false.
 */
function ends_with(string $string, string $end): bool
{
    if (strlen($end) > 0)
        return substr($string, -strlen($end)) === $end;
    else return true;
}

/**
 * Determines if a key exists in an associative array and has a specific value.
 * @param string $key - The key to check for.
 * @param mixed $value - The value to check that $key has.
 * @param array $array - The associative array to check in.
 * @return bool true if $key exists in $array and has a value matching $value, otherwise false.
 */
function key_exists_matches(string $key, $value, array $array): bool
{
    return array_key_exists($key, $array) && $array[$key] === $value;
}

/**
 * Casts the values of an observation record to their appropriate types.
 * @param array $observation - An associative array containing the attributes found in the
 * observations table of the database.
 * @return array $observation but with the values cast to their appropriate types.
 */
function cast_observation(array $observation): array
{
    if ($observation["airTemp"] !== null)
        $observation["airTemp"] = (double)$observation["airTemp"];
    if ($observation["relHum"] !== null)
        $observation["relHum"] = (double)$observation["relHum"];
    if ($observation["dewPoint"] !== null)
        $observation["dewPoint"] = (double)$observation["dewPoint"];
    if ($observation["windSpeed"] !== null)
        $observation["windSpeed"] = (double)$observation["windSpeed"];
    if ($observation["windDir"] !== null)
        $observation["windDir"] = (int)$observation["windDir"];
    if ($observation["windGust"] !== null)
        $observation["windGust"] = (double)$observation["windGust"];
    if ($observation["rainfall"] !== null)
        $observation["rainfall"] = (double)$observation["rainfall"];
    if ($observation["sunDur"] !== null)
        $observation["sunDur"] = (int)$observation["sunDur"];
    if ($observation["staPres"] !== null)
        $observation["staPres"] = (double)$observation["staPres"];
    if ($observation["mslPres"] !== null)
        $observation["mslPres"] = (double)$observation["mslPres"];

    return $observation;
}

/**
 * Casts the values of a dailyStats record to their appropriate types.
 * @param array $statistics - An associative array containing the attributes found in the dailyStats
 * table of the database.
 * @return array $statistics but with the values cast to their appropriate types.
 */
function cast_daily_statistics(array $statistics): array
{
    if ($statistics["airTempAvg"] !== null)
        $statistics["airTempAvg"] = (double)$statistics["airTempAvg"];
    if ($statistics["airTempMin"] !== null)
        $statistics["airTempMin"] = (double)$statistics["airTempMin"];
    if ($statistics["airTempMax"] !== null)
        $statistics["airTempMax"] = (double)$statistics["airTempMax"];

    if ($statistics["relHumAvg"] !== null)
        $statistics["relHumAvg"] = (double)$statistics["relHumAvg"];
    if ($statistics["relHumMin"] !== null)
        $statistics["relHumMin"] = (double)$statistics["relHumMin"];
    if ($statistics["relHumMax"] !== null)
        $statistics["relHumMax"] = (double)$statistics["relHumMax"];

    if ($statistics["dewPointAvg"] !== null)
        $statistics["dewPointAvg"] = (double)$statistics["dewPointAvg"];
    if ($statistics["dewPointMin"] !== null)
        $statistics["dewPointMin"] = (double)$statistics["dewPointMin"];
    if ($statistics["dewPointMax"] !== null)
        $statistics["dewPointMax"] = (double)$statistics["dewPointMax"];

    if ($statistics["windSpeedAvg"] !== null)
        $statistics["windSpeedAvg"] = (double)$statistics["windSpeedAvg"];
    if ($statistics["windSpeedMin"] !== null)
        $statistics["windSpeedMin"] = (double)$statistics["windSpeedMin"];
    if ($statistics["windSpeedMax"] !== null)
        $statistics["windSpeedMax"] = (double)$statistics["windSpeedMax"];

    if ($statistics["windDirAvg"] !== null)
        $statistics["windDirAvg"] = (int)$statistics["windDirAvg"];

    if ($statistics["windGustAvg"] !== null)
        $statistics["windGustAvg"] = (double)$statistics["windGustAvg"];
    if ($statistics["windGustMin"] !== null)
        $statistics["windGustMin"] = (double)$statistics["windGustMin"];
    if ($statistics["windGustMax"] !== null)
        $statistics["windGustMax"] = (double)$statistics["windGustMax"];

    if ($statistics["rainfallTtl"] !== null)
        $statistics["rainfallTtl"] = (double)$statistics["rainfallTtl"];
    if ($statistics["sunDurTtl"] !== null)
        $statistics["sunDurTtl"] = (int)$statistics["sunDurTtl"];

    if ($statistics["mslPresAvg"] !== null)
        $statistics["mslPresAvg"] = (double)$statistics["mslPresAvg"];
    if ($statistics["mslPresMin"] !== null)
        $statistics["mslPresMin"] = (double)$statistics["mslPresMin"];
    if ($statistics["mslPresMax"] !== null)
        $statistics["mslPresMax"] = (double)$statistics["mslPresMax"];

    return $statistics;
}

/**
 * Casts the values of a monthlyStats record to their appropriate types.
 * @param array $statistics - An associative array containing the attributes found in the
 * monthlyStats table of the database.
 * @return array $statistics but with the values cast to their appropriate types.
 */
function cast_monthly_statistics(array $statistics): array
{
    if ($statistics["year"] !== null)
        $statistics["year"] = (int)$statistics["year"];
    if ($statistics["month"] !== null)
        $statistics["month"] = (int)$statistics["month"];

    if ($statistics["airTempAvg"] !== null)
        $statistics["airTempAvg"] = (double)$statistics["airTempAvg"];
    if ($statistics["airTempMin"] !== null)
        $statistics["airTempMin"] = (double)$statistics["airTempMin"];
    if ($statistics["airTempMax"] !== null)
        $statistics["airTempMax"] = (double)$statistics["airTempMax"];

    if ($statistics["relHumAvg"] !== null)
        $statistics["relHumAvg"] = (double)$statistics["relHumAvg"];
    if ($statistics["relHumMin"] !== null)
        $statistics["relHumMin"] = (double)$statistics["relHumMin"];
    if ($statistics["relHumMax"] !== null)
        $statistics["relHumMax"] = (double)$statistics["relHumMax"];

    if ($statistics["dewPointAvg"] !== null)
        $statistics["dewPointAvg"] = (double)$statistics["dewPointAvg"];
    if ($statistics["dewPointMin"] !== null)
        $statistics["dewPointMin"] = (double)$statistics["dewPointMin"];
    if ($statistics["dewPointMax"] !== null)
        $statistics["dewPointMax"] = (double)$statistics["dewPointMax"];

    if ($statistics["windSpeedAvg"] !== null)
        $statistics["windSpeedAvg"] = (double)$statistics["windSpeedAvg"];
    if ($statistics["windSpeedMin"] !== null)
        $statistics["windSpeedMin"] = (double)$statistics["windSpeedMin"];
    if ($statistics["windSpeedMax"] !== null)
        $statistics["windSpeedMax"] = (double)$statistics["windSpeedMax"];

    if ($statistics["windDirAvg"] !== null)
        $statistics["windDirAvg"] = (int)$statistics["windDirAvg"];

    if ($statistics["windGustAvg"] !== null)
        $statistics["windGustAvg"] = (double)$statistics["windGustAvg"];
    if ($statistics["windGustMin"] !== null)
        $statistics["windGustMin"] = (double)$statistics["windGustMin"];
    if ($statistics["windGustMax"] !== null)
        $statistics["windGustMax"] = (double)$statistics["windGustMax"];

    if ($statistics["rainfallTtl"] !== null)
        $statistics["rainfallTtl"] = (double)$statistics["rainfallTtl"];
    if ($statistics["sunDurTtl"] !== null)
        $statistics["sunDurTtl"] = (int)$statistics["sunDurTtl"];

    if ($statistics["mslPresAvg"] !== null)
        $statistics["mslPresAvg"] = (double)$statistics["mslPresAvg"];
    if ($statistics["mslPresMin"] !== null)
        $statistics["mslPresMin"] = (double)$statistics["mslPresMin"];
    if ($statistics["mslPresMax"] !== null)
        $statistics["mslPresMax"] = (double)$statistics["mslPresMax"];

    return $statistics;
}