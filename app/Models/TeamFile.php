<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamFile extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'file_path',
        'original_name',
        'type', 
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
