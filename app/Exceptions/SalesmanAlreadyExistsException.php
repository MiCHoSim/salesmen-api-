<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesmanAlreadyExistsException extends Exception
{
    public function __construct(string $field, string $value)
    {
        $message = "Salesman with such {$field} {$value} is already registered.";
        parent::__construct($message, 409);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'errors' => [
                [
                    'code' => 'PERSON_ALREADY_EXISTS',
                    'message' => $this->getMessage(),
                ]
            ]
        ], $this->getCode());
    }
}
