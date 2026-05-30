<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'vehicle_id', 'maintenance_date', 'type', 'cost',
        'workshop_name', 'current_mileage', 'notes', 'next_date', 'next_mileage',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'next_date'        => 'date',
            'cost'             => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
