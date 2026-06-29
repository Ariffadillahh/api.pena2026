<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionRequirement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'competition_id',
        'requirement',
        'order_number',
    ];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
