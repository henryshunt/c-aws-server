<?php
namespace Aws\Endpoints;

use Aws\Endpoint;
use Aws\Response;
use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\ValidationException;
use Aws\HttpException;


class ObservationsGetEndpoint extends Endpoint
{
    public function __invoke(): Response
    {
        $this->validateUrlParams();
        return $this->readObservations();
    }

    private function validateUrlParams(): void
    {
        $validator = V
            ::key("start", V::dateTime("Y-m-d\TH-i-s"))
            ->key("end", V::dateTime("Y-m-d\TH-i-s"));

        try { $validator->check($_GET); }
        catch (ValidationException $ex)
        {
            throw new HttpException(400, $ex->getMessage());
        }

        $start = \DateTime::createFromFormat("Y-m-d\TH-i-s", $_GET["start"]);
        $end = \DateTime::createFromFormat("Y-m-d\TH-i-s", $_GET["end"]);

        if ($start >= $end)
            throw new HttpException(400, "end must be later than start");
    }

    private function readObservations(): Response
    {
        $start = \DateTime::createFromFormat("Y-m-d\TH-i-s", $_GET["start"]);
        $end = \DateTime::createFromFormat("Y-m-d\TH-i-s", $_GET["end"]);

        $sql = "SELECT * FROM observations WHERE time BETWEEN ? AND ? ORDER BY time";
        $query = database_query($this->pdo, $sql,
            [$start->format("Y-m-d H:i:s"), $end->format("Y-m-d H:i:s")]);

        for ($i = 0; $i < count($query); $i++)
            $query[$i] = cast_observation($query[$i]);

        return (new Response(200))->setBody(json_encode($query));
    }
}