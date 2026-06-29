<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'division',
        'pj_competition_id',
        'pj_category',
        'pj_competition_id_2', 
        'pj_category_2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function handledCompetition()
    {
        return $this->belongsTo(Competition::class, 'pj_competition_id');
    }

    public function handledCompetition2()
    {
        return $this->belongsTo(Competition::class, 'pj_competition_id_2');
    }
}
