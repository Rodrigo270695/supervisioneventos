<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestAccess extends Model
{
    protected $fillable = [
        'guest_id',
        'people_count',
        'access_datetime',
        'access_type',
        'observations',
    ];

    protected $casts = [
        'access_datetime' => 'datetime',
        'people_count' => 'integer',
    ];

    // Relaciones
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
