<?php
// app/Services/SalesmanService.php

namespace App\Services;

use App\Exceptions\SalesmanAlreadyExistsException;
use App\Models\Salesman;
use App\Repositories\SalesmanRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SalesmanService
{
    public function __construct(
        private SalesmanRepository $salesmanRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function createSalesman(array $data): Salesman
    {
        $prosightId = $data['prosight_id'] ?? '';
        $email = $data['email'] ?? '';

        if (!is_string($prosightId) || !is_string($email)) {
            throw new \InvalidArgumentException('Prosight ID and email must be strings');
        }

        if ($this->salesmanRepository->findByProsightId($prosightId)) {
            throw new SalesmanAlreadyExistsException('prosight_id', $prosightId);
        }

        if ($this->salesmanRepository->findByEmail($email)) {
            throw new SalesmanAlreadyExistsException('email', $email);
        }

        return $this->salesmanRepository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSalesman(string $id, array $data): Salesman
    {
        $salesman = $this->salesmanRepository->findById($id);

        if (!$salesman) {
            throw new ModelNotFoundException("Salesman with ID {$id} not found");
        }

        $this->salesmanRepository->update($salesman, $data);

        $updatedSalesman = $salesman->fresh();
        if (!$updatedSalesman) {
            throw new \RuntimeException('Failed to refresh salesman after update');
        }

        return $updatedSalesman;
    }

    public function getSalesman(string $id): Salesman
    {
        $salesman = $this->salesmanRepository->findById($id);

        if (!$salesman) {
            throw new ModelNotFoundException("Salesman with ID {$id} not found");
        }

        return $salesman;
    }

    public function deleteSalesman(string $id): void
    {
        $salesman = $this->salesmanRepository->findById($id);

        if (!$salesman) {
            throw new ModelNotFoundException("Salesman with ID {$id} not found");
        }

        $this->salesmanRepository->delete($salesman);
    }

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Salesman>
     */
    public function listSalesmen(array $filters = [], int $perPage = 15, string $sort = '-created_at'): LengthAwarePaginator
    {
        if ($perPage < 1 || $perPage > 100) {
            throw new \InvalidArgumentException('Per page must be between 1 and 100');
        }

        return $this->salesmanRepository->paginate($perPage, $filters, $sort);
    }
}
