<?php
namespace Aws\Endpoints;

use Aws\Endpoint;
use Aws\Response;
use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\ValidationException;
use Aws\HttpException;


class ReportGetEndpoint extends Endpoint
{
    public function __invoke(): Response
    {
        // The format is already valid but it could still be an impossible date/time
        if (\DateTime::createFromFormat("Y-m-d\TH-i-s", $this->resParams["time"]) === false)
            throw new HttpException(404);

        $this->validateUrlParams();
        return $this->readReport();
    }

    private function validateUrlParams(): void
    {
        $validator = V
            ::key("auto", V::in(["true", "false"], true), false)
            ->key("extra", V::in(["true", "false"], true), false);

        try { $validator->check($_GET); }
        catch (ValidationException $ex)
        {
            throw new HttpException(400, $ex->getMessage());
        }
    }

    private function readReport(): Response
    {
        $time = \DateTime::createFromFormat("Y-m-d\TH-i-s", $this->resParams["time"]);

        $sql = "SELECT * FROM reports WHERE time = ? LIMIT 1";
        $query = database_query($this->pdo, $sql, [$time->format("Y-m-d H:i:s")]);

        if (count($query) > 0)
            return (new Response(200))->setBody(json_encode(cast_report($query[0])));
        else
        {
            // Because there can be a delay between data being recorded and being made available,
            // if the requested report does not exist and auto is true, try getting the report for
            // one minute earlier
            if (key_exists_matches("auto", "true", $_GET))
            {
                $time->sub(new \DateInterval("PT1M"));
                $query = database_query($this->pdo, $sql, [$time->format("Y-m-d H:i:s")]);

                if (count($query) > 0)
                    return (new Response(200))->setBody(json_encode(cast_report($query[0])));
                else throw new HttpException(404);
            }
            else throw new HttpException(404);
        }
    }
}