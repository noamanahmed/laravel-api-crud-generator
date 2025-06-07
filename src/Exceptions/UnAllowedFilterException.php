<?php

namespace NoamanAhmed\ApiCrudGenerator\Exceptions;

use Exception;

/**
 * Class UnAllowedFilterException
 * Exception thrown when a filter operation is not allowed.
 */
class UnAllowedFilterException extends Exception
{
    /**
     * UnAllowedFilterException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "This filter operation is not allowed.", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
