<?php

namespace App\Repositories;

use App\Models\Salesman;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SalesmanRepository
{
    public function __construct(
        private Salesman $salesman
    ) {}

    public function findById(string $id): ?Salesman
    {
        return $this->salesman->find($id);
    }

    public function findByProsightId(string $prosightId): ?Salesman
    {
        return $this->salesman->where('prosight_id', $prosightId)->first();
    }

    public function findByEmail(string $email): ?Salesman
    {
        return $this->salesman->where('email', $email)->first();
    }

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Salesman>
     */
    public function paginate(int $perPage = 15, array $filters = [], string $sort = '-created_at'): LengthAwarePaginator
    {
        $query = $this->salesman->newQuery();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            if (is_string($search)) {
                $query->where(function ($q) use ($search): void {
                    $q->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            }
        }

        $sortField = ltrim($sort, '-');
        $sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';

        $allowedSortFields = ['first_name', 'last_name', 'email', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSortFields, true)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        /** @var LengthAwarePaginator<int, Salesman> */
        return $query->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Salesman
    {
        return $this->salesman->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Salesman $salesman, array $data): bool
    {
        return $salesman->update($data);
    }

    public function delete(Salesman $salesman): bool
    {
        return (bool) $salesman->delete();
    }
}
