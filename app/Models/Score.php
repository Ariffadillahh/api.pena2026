<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'scores';

    protected $fillable = [
        'team_id',
        'juri_id',
        'criteria_id',
        'score',
        'notes',
    ];


    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'juri_id', 'id');
    }

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }
}
