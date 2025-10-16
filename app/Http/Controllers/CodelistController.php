<?php

namespace App\Http\Controllers;

use App\Services\CodelistService;
use Illuminate\Http\JsonResponse;

class CodelistController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'marital_statuses' => CodelistService::getMaritalStatuses(),
            'genders' => CodelistService::getGenders(),
            'titles_before' => CodelistService::getTitlesBefore(),
            'titles_after' => CodelistService::getTitlesAfter(),
        ]);
    }
}
