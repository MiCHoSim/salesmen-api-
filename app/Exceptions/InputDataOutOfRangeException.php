<?php

namespace App\Exceptions;

use Exception;

class InputDataOutOfRangeException extends Exception
{
    public function __construct(string $field, string $value, string $acceptableRange)
    {
        $message = "Input data out of range. Field {$field} of value {$value} is out of range. Acceptable range for this field is {$acceptableRange}.";
        parent::__construct($message, 416);
    }
}
