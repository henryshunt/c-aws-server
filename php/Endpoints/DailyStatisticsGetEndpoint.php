<?php
namespace Aws\Endpoints;

use Aws\Endpoint;
use Aws\Response;
use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\ValidationException;
use Aws\HttpException;


class DailyStatisticsGetEndpoint extends Endpoint
{
    public function __invoke(): Response
    {
        $this->validateUrlParams();
        return $this->readDailyStatistics();
    }

    private function validateUrlParams(): void
    {
        $validator = V
            ::key("start", V::dateTime("Y-m-d"))
            ->key("end", V::dateTime("Y-m-d"));

        try { $validator->check($_GET); }
        catch (ValidationException $ex)
        {
            throw new HttpException(400, $ex->getMessage());
        }

        $start = \DateTime::createFromFormat("Y-m-d", $_GET["start"]);
        $end = \DateTime::createFromFormat("Y-m-d", $_GET["end"]);

        if ($start >= $end)
            throw new HttpException(400, "end must be later than start");
    }

    private function readDailyStatistics(): Response
    {
        $start = \DateTime::createFromFormat("Y-m-d", $_GET["start"]);
        $end = \DateTime::createFromFormat("Y-m-d", $_GET["end"]);

        $sql = "SELECT * FROM dailyStats WHERE date BETWEEN ? AND ? ORDER BY date";
        $query = database_query($this->pdo, $sql,
            [$start->format("Y-m-d"), $end->format("Y-m-d")]);

        for ($i = 0; $i < count($query); $i++)
            $query[$i] = cast_daily_statistics($query[$i]);

        return (new Response(200))->setBody(json_encode($query));
    }
}