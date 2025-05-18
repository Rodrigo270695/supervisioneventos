<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSecurity extends Model
{
    protected $table = 'event_security';

    protected $fillable = [
        'event_id',
        'user_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relación con el evento
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    // Relación con el usuario (personal de seguridad)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
