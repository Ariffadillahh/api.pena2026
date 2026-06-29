<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TeamAttendance extends Model
{
    use HasUuids; 
    protected $guarded = [];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
