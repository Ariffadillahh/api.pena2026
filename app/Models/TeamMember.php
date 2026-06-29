<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'name',
        'email',
        'phone',
        'role',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
