<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Time extends Model
{
    protected $fillable = [
        'event_id',
        'time_type_id',
        'start_time',
        'end_time',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::addGlobalScope('ordered', function ($query) {
            $query->orderBy('start_time', 'asc');
        });
    }

    /**
     * Obtener el evento al que pertenece este tiempo.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Obtener el tipo de tiempo.
     */
    public function timeType(): BelongsTo
    {
        return $this->belongsTo(TimeType::class);
    }
}
