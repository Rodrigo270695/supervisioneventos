<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function times(): HasMany
    {
        return $this->hasMany(Time::class);
    }
}
