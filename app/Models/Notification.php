<?php

namespace App\Models;

use App\Enums\NotificationTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'user_id', 'title', 'body', 'type', 'data', 'is_read', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'type'     => NotificationTypeEnum::class,
            'data'     => 'array',
            'is_read'  => 'boolean',
            'read_at'  => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
