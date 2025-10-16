<?php

namespace App\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    public function __construct(string $message = 'Query execution failed.')
    {
        parent::__construct($message, 400);
    }
}
