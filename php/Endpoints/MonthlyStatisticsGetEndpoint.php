<?php
namespace Aws\Endpoints;

use Aws\Endpoint;
use Aws\Response;
use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\ValidationException;
use Aws\HttpException;


class MonthlyStatisticsGetEndpoint extends Endpoint
{
    public function __invoke(): Response
    {
        $this->validateUrlParams();
        return $this->readMonthlyStatistics();
    }

    private function validateUrlParams(): void
    {
        $validator = V
            ::key("start", V::dateTime("Y-m"))
            ->key("end", V::dateTime("Y-m"));

        try { $validator->check($_GET); }
        catch (ValidationException $ex)
        {
            throw new HttpException(400, $ex->getMessage());
        }

        $start = \DateTime::createFromFormat("Y-m", $_GET["start"]);
        $end = \DateTime::createFromFormat("Y-m", $_GET["end"]);

        if ($start >= $end)
            throw new HttpException(400, "end must be later than start");
    }

    private function readMonthlyStatistics(): Response
    {
        $start = \DateTime::createFromFormat("Y-m", $_GET["start"]);
        $end = \DateTime::createFromFormat("Y-m", $_GET["end"]);

        $sql = "SELECT * FROM monthlyStats " .
            "WHERE (year + month) BETWEEN (? + ?) AND (? + ?)" . 
            "ORDER BY year, month";

        $values = [$start->format("Y"), $start->format("m"), $end->format("Y"),
            $end->format("m")];
        $query = database_query($this->pdo, $sql, $values);

        for ($i = 0; $i < count($query); $i++)
            $query[$i] = cast_monthly_statistics($query[$i]);

        return (new Response(200))->setBody(json_encode($query));
    }
}