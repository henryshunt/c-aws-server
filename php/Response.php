<?php
namespace Aws;

/**
 * Represents the response from an API endpoint.
 */
class Response
{
    /**
     * The HTTP status code of the response.
     * @var int
     */
    private $status;

    /**
     * The body of the response.
     * @var string|null
     */
    private $body = null;

    /**
     * The error message.
     * @var string|null
     */
    private $error = null;

    /**
     * Initialises a new instance of the @see Response class.
     * @param int $status - The HTTP status code of the response.
     */
    public function __construct(int $status)
    {
        $this->status = $status;
    }

    /**
     * Gets the HTTP status code of the response.
     * @return int The HTTP status code.
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Gets the body of the response.
     * @return string|null The body.
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Gets the error message.
     * @return string|null The error message.
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Sets the body of the response. If the response has an error message then that is set to null.
     * @param string|null body - The body of the request. If it is an empty string then null is
     * used.
     * @return Response The @see Response object.
     */
    public function setBody(?string $body): Response
    {
        if ($body === null || strlen($body) === 0)
            $this->body = null;
        else $this->body = $body;

        return $this;
    }

    /**
     * Sets the error message. If the response has a body then that is set to null.
     * @param string|null error - The error message. If it is an empty string then null is used.
     * @return Response The @see Response object.
     */
    public function setError(?string $error): Response
    {
        if ($error === null || strlen($error) === 0)
            $this->error = null;
        else $this->error = $error;

        return $this;
    }
}