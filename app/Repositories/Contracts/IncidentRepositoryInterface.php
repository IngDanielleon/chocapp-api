<?php

namespace App\Repositories\Contracts;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface IncidentRepositoryInterface
{
    public function findByUser(User $user, array $filters = []): LengthAwarePaginator;
    public function findById(string $id): ?Incident;
    public function create(array $data): Incident;
    public function update(Incident $incident, array $data): Incident;
    public function delete(Incident $incident): bool;
}
