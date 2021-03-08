<?php
namespace Aws;

/**
 * Represents an API endpoint.
 */
class Endpoint
{
    /**
     * An associative array of string values containing the parameters from the URL path. These
     * parameters identify the resource being requested and are unrelated to the query string
     * parameters.
     * @var array
     */
    protected $resParams;

    /**
     * The @see PDO object to use when accessing the database.
     * @var PDO
     */
    protected $pdo;

    /**
     * Initialises a new instance of the @see Endpoint class.
     * @param array $resParams - An associative array of string values containing the parameters
     * from the URL path. These parameters identify the resource being requested and are unrelated
     * to the query string parameters.
     * @param PDO @pdo - The @see PDO object to use when accessing the database.
     */
    public function __construct(array $resParams, \PDO $pdo)
    {
        $this->resParams = $resParams;
        $this->pdo = $pdo;
    }
}