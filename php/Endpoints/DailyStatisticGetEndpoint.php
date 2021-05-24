<?php
namespace Aws\Endpoints;

use Aws\Endpoint;
use Aws\Response;
use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\ValidationException;
use Aws\HttpException;


class DailyStatisticGetEndpoint extends Endpoint
{
    public function __invoke(): Response
    {
        // Format is already valid but it could still be an impossible date
        if (\DateTime::createFromFormat("Y-m-d", $this->resParams["date"]) === false)
            throw new HttpException(404);

        return $this->readDailyStatistic();
    }

    private function readDailyStatistic(): Response
    {
        $date = \DateTime::createFromFormat("Y-m-d", $this->resParams["date"]);

        $sql = "SELECT * FROM dailyStats WHERE date = ? LIMIT 1";
        $query = database_query($this->pdo, $sql, [$date->format("Y-m-d")]);

        if (count($query) > 0)
        {
            return (new Response(200))
                ->setBody(json_encode(cast_daily_statistics($query[0])));
        }
        else throw new HttpException(404);
    }
}