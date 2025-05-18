<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    protected $fillable = [
        'event_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Obtener el evento al que pertenece esta nota.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
