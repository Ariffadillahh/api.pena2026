<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AttendanceEvent extends Model
{
    use HasFactory, HasUuids; 

    protected $table = 'attendance_events';

    protected $fillable = [
        'id',
        'title',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class, 'event_id', 'id');
    }
}
