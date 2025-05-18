<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    protected $fillable = [
        'event_id',
        'first_name',
        'last_name',
        'dni',
        'table_number',
        'passes',
        'used_passes',
        'qr_code',
        'last_access',
    ];

    protected $casts = [
        'last_access' => 'datetime',
        'passes' => 'integer',
        'used_passes' => 'integer',
        'table_number' => 'integer',
    ];

    // Relaciones
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(GuestAccess::class);
    }

    // Atributos computados
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getRemainingPassesAttribute(): int
    {
        return $this->passes - $this->used_passes;
    }

    // Métodos de utilidad
    public function canAccess(int $peopleCount): bool
    {
        return ($this->used_passes + $peopleCount) <= $this->passes;
    }

    public function registerAccess(int $peopleCount, string $observations = null): GuestAccess
    {
        if (!$this->canAccess($peopleCount)) {
            throw new \Exception('No hay suficientes pases disponibles');
        }

        $this->used_passes += $peopleCount;
        $this->last_access = now();
        $this->save();

        return $this->accesses()->create([
            'people_count' => $peopleCount,
            'access_datetime' => now(),
            'access_type' => 'entry',
            'observations' => $observations,
        ]);
    }
}
