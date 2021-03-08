<?php
namespace Aws;

/**
 * Represents an HTTP status code exception.
 */
class HttpException extends \Exception
{
    /**
     * The HTTP status code.
     * @var int
     */
    private $status;

    /**
     * Initialises a new instance of the @see HttpException class.
     * @param int $status - The HTTP status code.
     * @param string|null - The exception message.
     * @param Throwable|null $previous - The previous exception.
     */
    public function __construct(
        int $status, ?string $message = null, ?\Throwable $previous = null)
    {
        $this->status = $status;
        parent::__construct($message === null ? "" : $message, $status, $previous);
    }

    /**
     * Gets the HTTP status code.
     * @return int The HTTP status code.
     */
    public function getStatus(): int
    {
        return $this->status;
    }
}