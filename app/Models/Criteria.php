<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assessment_criteria';

    protected $fillable = [
        'competition_id',
        'name',
        'weight',
        'description',
    ];


    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
