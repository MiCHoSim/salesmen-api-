<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Http\Requests\SalesmanRequest;
use App\Http\Resources\SalesmanCollection;
use App\Http\Resources\SalesmanResource;
use App\Services\SalesmanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SalesmanController extends Controller
{
    public function __construct(
        private SalesmanService $salesmanService
    ) {}

    public function index(Request $request): SalesmanCollection
    {
        try {
            $perPage = $request->get('per_page', '15');
            $page = $request->get('page', '1');
            $sort = $request->get('sort', '-created_at');

            if (!is_numeric($perPage) || !is_numeric($page)) {
                throw new BadRequestException('Page and per_page parameters must be numeric.');
            }

            $perPage = (int) $perPage;
            $page = (int) $page;

            if (!is_string($sort)) {
                throw new BadRequestException('Sort parameter must be a string.');
            }

            if ($page < 1) {
                throw new BadRequestException('Page parameter must be greater than 0.');
            }

            $salesmen = $this->salesmanService->listSalesmen(
                filters: $request->only(['search']),
                perPage: $perPage,
                sort: $sort
            );

            return new SalesmanCollection($salesmen);

        } catch (Throwable $e) {
            if ($e instanceof BadRequestException) {
                throw $e;
            }
            throw new BadRequestException($e->getMessage());
        }
    }

    public function store(SalesmanRequest $request): JsonResponse
    {
        $salesman = $this->salesmanService->createSalesman($request->validated());

        return (new SalesmanResource($salesman))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): SalesmanResource
    {
        $salesman = $this->salesmanService->getSalesman($id);

        return new SalesmanResource($salesman);
    }

    public function update(SalesmanRequest $request, string $id): SalesmanResource
    {
        $salesman = $this->salesmanService->updateSalesman($id, $request->validated());

        return new SalesmanResource($salesman);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->salesmanService->deleteSalesman($id);

        return response()->json(null, 204);
    }
}
