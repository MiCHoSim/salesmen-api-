<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        // NotFoundHttpException - 404 Not Found (toto je ten problém!)
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                // Ak je to route pre salesmen, vráť náš custom formát
                if (str_contains($request->path(), 'salesmen')) {
                    return response()->json([
                        'errors' => [[
                            'code' => 'PERSON_NOT_FOUND',
                            'message' => "Salesman with such uuid not found.",
                        ]]
                    ], 404);
                }

                // Pre ostatné 404 vráť generic formát
                return response()->json([
                    'errors' => [[
                        'code' => 'NOT_FOUND',
                        'message' => "Resource not found.",
                    ]]
                ], 404);
            }
        });
        // ModelNotFoundException - 404 Not Found
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => [[
                        'code' => 'PERSON_NOT_FOUND',
                        'message' => "Salesman with such uuid not found.",
                    ]]
                ], 404);
            }
        });

        // SalesmanAlreadyExistsException - 409 Conflict
        $this->renderable(function (SalesmanAlreadyExistsException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => [[
                        'code' => 'PERSON_ALREADY_EXISTS',
                        'message' => $e->getMessage(),
                    ]]
                ], 409);
            }
        });

        // BadRequestException - 400 Bad Request
        $this->renderable(function (BadRequestException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => [[
                        'code' => 'BAD_REQUEST',
                        'message' => $e->getMessage(),
                    ]]
                ], 400);
            }
        });

        // ValidationException - 400 Bad Request
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                $errors = [];
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        if (str_contains($message, 'taken')) {
                            $errors[] = [
                                'code' => 'PERSON_ALREADY_EXISTS',
                                'message' => "Salesman with such {$field} is already registered.",
                            ];
                        } else {
                            $errors[] = [
                                'code' => 'INPUT_DATA_BAD_FORMAT',
                                'message' => "Bad format of input data. Field {$field} {$message}",
                            ];
                        }
                    }
                }

                return response()->json(['errors' => $errors], 400);
            }
        });

        // Všeobecná chyba - 500 Internal Server Error
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                \Log::error('Internal server error', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return response()->json([
                    'errors' => [[
                        'code' => 'INTERNAL_ERROR',
                        'message' => 'An internal error occurred.',
                    ]]
                ], 500);
            }
        });
    }
}
