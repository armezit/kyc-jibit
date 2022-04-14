<?php

namespace Armezit\Kyc\Jibit\Exception;

/**
 * Invalid Response exception.
 *
 * Thrown when provider responded with invalid or unexpected data (for example, a security hash did not match).
 */
class InvalidResponseException extends \Exception
{
    public function __construct($message = "Invalid response from provider", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
