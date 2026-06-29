<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'link_guidebook',
        'poster',
        'is_active',
        'contact_person',
        'max_members',
        'announcement_date',
        'scoreboard_link'
    ];

    public function requirements()
    {
        return $this->hasMany(CompetitionRequirement::class)->orderBy('order_number');
    }

    public function waves()
    {
        return $this->hasMany(RegistrationWave::class)->orderBy('start_date');
    }

    public function teams()
    {
        return $this->hasMany(Team::class, 'competition_id', 'id');
    }

    public function juris()
    {
        return $this->belongsToMany(User::class, 'juri_assignments', 'competition_id', 'user_id');
    }
}
