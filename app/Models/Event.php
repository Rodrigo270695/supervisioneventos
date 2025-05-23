<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\EventStatus;
use App\Models\Plan;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_type_id',
        'name',
        'capacity',
        'event_date',
        'start_time',
        'end_time',
        'address',
        'description',
        'status'
    ];

    protected $casts = [
        'event_date' => 'date:Y-m-d',
        'start_time' => 'string',
        'end_time' => 'string',
        'status' => EventStatus::class,
    ];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d');
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id', 'id');
    }

    /**
     * Obtener los anfitriones del evento.
     */
    public function hosts(): HasMany
    {
        return $this->hasMany(Host::class);
    }

    /**
     * Obtener los tiempos del evento.
     */
    public function times(): HasMany
    {
        return $this->hasMany(Time::class);
    }

    /**
     * Obtener las notas del evento.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Obtener los invitados del evento.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    // Relación con el personal de seguridad
    public function security(): HasMany
    {
        return $this->hasMany(EventSecurity::class);
    }

    // Relación con los usuarios de seguridad a través de event_security
    public function securityUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_security')
            ->withPivot('active')
            ->withTimestamps();
    }

    public function guestAccesses(): HasMany
    {
        return $this->hasMany(GuestAccess::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }
}
