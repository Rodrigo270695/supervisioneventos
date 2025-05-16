<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d');
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id', 'id');
    }
}
