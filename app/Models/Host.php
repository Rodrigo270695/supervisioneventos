<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Host extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'event_id',
        'host_type_id',
        'nombres',
        'apellidos',
        'dni',
        'edad',
        'correo',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'edad' => 'integer',
    ];

    /**
     * Obtiene el evento al que pertenece el anfitrión.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Obtiene el tipo de anfitrión.
     */
    public function hostType(): BelongsTo
    {
        return $this->belongsTo(HostType::class);
    }

    /**
     * Obtiene el nombre completo del anfitrión.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}
