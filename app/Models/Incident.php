<?php

namespace App\Models;

use App\Enums\IncidentStatusEnum;
use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'user_id', 'vehicle_id', 'title', 'description',
        'incident_date', 'incident_time', 'location_address',
        'latitude', 'longitude', 'weather_condition', 'road_condition',
        'police_report_number', 'status', 'report_pdf_url',
    ];

    protected $appends = ['cover_photo_url'];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'status'        => IncidentStatusEnum::class,
            'latitude'      => 'decimal:8',
            'longitude'     => 'decimal:8',
        ];
    }

    protected function coverPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $front = $this->photos()
                    ->where('angle', 'FRONT')
                    ->first();
                return $front?->image_url ?? $this->photos()->first()?->image_url;
            }
        );
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** @return HasMany<IncidentPhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(IncidentPhoto::class);
    }

    /** @return HasMany<ThirdParty, $this> */
    public function thirdParties(): HasMany
    {
        return $this->hasMany(ThirdParty::class);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}
