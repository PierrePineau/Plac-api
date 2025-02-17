<?php

namespace App\Core\Exception;

class DeniedException extends \Exception
{
    public function __construct($message = 'access.denied', $code = 403, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}