<?php
namespace Aws\Endpoints;

use Aws\Endpoint;
use Aws\Response;
use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\ValidationException;
use Aws\HttpException;


class ObservationGetEndpoint extends Endpoint
{
    public function __invoke(): Response
    {
        // The format is already valid but it could still be an impossible date/time
        if (\DateTime::createFromFormat("Y-m-d\TH-i-s", $this->resParams["time"]) === false)
            throw new HttpException(404);

        $this->validateUrlParams();
        return $this->readObservation();
    }

    private function validateUrlParams(): void
    {
        $validator = V
            ::key("auto", V::in(["true", "false"], true), false)
            ->key("extras", V::in(["true", "false"], true), false);

        try { $validator->check($_GET); }
        catch (ValidationException $ex)
        {
            throw new HttpException(400, $ex->getMessage());
        }
    }

    private function readObservation(): Response
    {
        $time = \DateTime::createFromFormat("Y-m-d\TH-i-s", $this->resParams["time"]);

        $sql = "SELECT * FROM observations WHERE time = ? LIMIT 1";
        $query = database_query($this->pdo, $sql, [$time->format("Y-m-d H:i:s")]);

        if (count($query) > 0)
        {
            if (key_exists_matches("extras", "true", $_GET))
                $observation = $this->addExtras($query[0], clone $time);
            else $observation = $query[0];

            return (new Response(200))->setBody(json_encode(cast_observation($observation)));
        }
        else
        {
            // Because there can be a delay between data being recorded and being made available,
            // if the requested observation does not exist and auto is true, try getting the
            // observation for one minute earlier
            if (key_exists_matches("auto", "true", $_GET))
            {
                $time->sub(new \DateInterval("PT1M"));
                $query = database_query($this->pdo, $sql, [$time->format("Y-m-d H:i:s")]);

                if (count($query) > 0)
                {
                    if (key_exists_matches("extras", "true", $_GET))
                        $observation = $this->addExtras($query[0], clone $time);
                    else $observation = $query[0];

                    return (new Response(200))->setBody(json_encode(cast_observation($observation)));
                }
                else throw new HttpException(404);
            }
            else throw new HttpException(404);
        }
    }

    private function addExtras(array $observation, \DateTime $time): array
    {
        $hourAgo = clone $time;
        $hourAgo->sub(new \DateInterval("PT59M"));

        // Past hour rainfall
        $sql = "SELECT SUM(rainfall) FROM observations WHERE time BETWEEN ? AND ?";
        $query = database_query($this->pdo, $sql,
            [$hourAgo->format("Y-m-d H:i:s"), $time->format("Y-m-d H:i:s")]);

        if ($query[0]["SUM(rainfall)"] !== null)
            $observation["rainfallPastHour"] = (double)$query[0]["SUM(rainfall)"];
        else $observation["rainfallPastHour"] = null;

        // Past hour sunshine duration
        $sql = "SELECT SUM(sunDur) FROM observations WHERE time BETWEEN ? AND ?";
        $query = database_query($this->pdo, $sql,
            [$hourAgo->format("Y-m-d H:i:s"), $time->format("Y-m-d H:i:s")]);

        if ($query[0]["SUM(sunDur)"] !== null)
            $observation["sunDurPastHour"] = (int)$query[0]["SUM(sunDur)"];
        else $observation["sunDurPastHour"] = null;

        // Pressure tendency
        if ($observation["mslPres"] !== null)
        {
            $sql = "SELECT mslPres FROM observations WHERE time = ? LIMIT 1";
            $tendTime = (clone $time)->sub(new \DateInterval("PT3H"));

            $query = database_query($this->pdo, $sql,
                [$tendTime->format("Y-m-d H:i:s")]);

            if (count($query) > 0 && $query[0]["mslPres"] !== null)
            {
                $observation["mslPresTendency"] =
                    round($query[0]["mslPres"] - $observation["mslPres"], 1);
            }
            else $observation["mslPresTendency"] = null;
        }
        else $observation["mslPresTendency"] = null;

        return $observation;
    }
}