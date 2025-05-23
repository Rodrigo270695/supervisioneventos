<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'event_id',
        'people_count',
        'access_datetime',
        'access_type',
        'observations'
    ];

    protected $casts = [
        'access_datetime' => 'datetime',
    ];

    // Relaciones
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
