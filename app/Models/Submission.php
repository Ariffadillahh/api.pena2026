<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'file_path',
        'original_filename',
        'gdrive_link'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
