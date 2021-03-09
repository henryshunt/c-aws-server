<?php
namespace Aws\Endpoints;

use Aws\Endpoint;
use Aws\Response;
use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\ValidationException;
use Aws\HttpException;


class StatisticDailyGetEndpoint extends Endpoint
{
    public function __invoke(): Response
    {
        // The format is already valid but it could still be an impossible date
        if (\DateTime::createFromFormat("Y-m-d", $this->resParams["date"]) === false)
            throw new HttpException(404);

        $this->validateUrlParams();
        return $this->readStatistic();
    }

    private function validateUrlParams(): void
    {
        $validator = V
            ::key("auto", V::in(["true", "false"], true), false);

        try { $validator->check($_GET); }
        catch (ValidationException $ex)
        {
            throw new HttpException(400, $ex->getMessage());
        }
    }

    private function readStatistic(): Response
    {
        $date = \DateTime::createFromFormat("Y-m-d", $this->resParams["date"]);

        $sql = "SELECT * FROM dayStats WHERE date = ? LIMIT 1";
        $query = database_query($this->pdo, $sql, [$date->format("Y-m-d")]);

        if (count($query) > 0)
            return (new Response(200))->setBody(json_encode($query[0]));
        else
        {
            // Because there can be a delay between data being recorded and being made available,
            // if the requested statistic does not exist and auto is true, try getting the
            // statistic for one minute earlier
            if (key_exists_matches("auto", "true", $_GET))
            {
                $date->sub(new \DateInterval("PT1M"));
                $query = database_query($this->pdo, $sql, [$date->format("Y-m-d")]);

                if (count($query) > 0)
                    return (new Response(200))->setBody(json_encode($query[0]));
                else throw new HttpException(404);
            }
            else throw new HttpException(404);
        }
    }
}