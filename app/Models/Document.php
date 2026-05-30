<?php

namespace App\Models;

use App\Enums\DocumentTypeEnum;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'vehicle_id', 'type', 'document_number',
        'issue_date', 'expiry_date', 'pdf_url', 'notes',
    ];

    protected $appends = ['status'];

    protected function casts(): array
    {
        return [
            'issue_date'  => 'date',
            'expiry_date' => 'date',
            'type'        => DocumentTypeEnum::class,
        ];
    }

    /**
     * Computed status — NOT stored in DB.
     * Returns: VIGENTE | VENCE_PRONTO | VENCIDO
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $today = now()->startOfDay();
                $soon  = now()->addDays(30)->startOfDay();

                if ($this->expiry_date->lt($today)) {
                    return 'VENCIDO';
                }
                if ($this->expiry_date->lte($soon)) {
                    return 'VENCE_PRONTO';
                }
                return 'VIGENTE';
            }
        );
    }

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('expiry_date', [
            now()->toDateString(),
            now()->addDays($days)->toDateString(),
        ]);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->toDateString());
    }
}
