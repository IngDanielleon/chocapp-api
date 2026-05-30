<?php

namespace App\Repositories;

use App\Models\Incident;
use App\Models\User;
use App\Repositories\Contracts\IncidentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentRepository implements IncidentRepositoryInterface
{
    public function findByUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Incident::forUser($user->id)
            ->with(['vehicle', 'photos'])
            ->latest('incident_date');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('incident_date', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('incident_date', '<=', $filters['to']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function findById(string $id): ?Incident
    {
        return Incident::find($id);
    }

    public function create(array $data): Incident
    {
        return Incident::create($data);
    }

    public function update(Incident $incident, array $data): Incident
    {
        $incident->update($data);
        return $incident->fresh();
    }

    public function delete(Incident $incident): bool
    {
        return $incident->delete();
    }
}
