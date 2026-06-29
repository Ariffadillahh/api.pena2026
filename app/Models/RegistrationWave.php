<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationWave extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'competition_id',
        'wave_name',
        'price',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
