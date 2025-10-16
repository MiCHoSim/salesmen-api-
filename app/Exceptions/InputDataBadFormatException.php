<?php

namespace App\Exceptions;

use Exception;

class InputDataBadFormatException extends Exception
{
    public function __construct(string $field, string $value, string $expectedType)
    {
        $message = "Bad format of input data. Field {$field} {$value} must be of type {$expectedType}.";
        parent::__construct($message, 400);
    }
}
