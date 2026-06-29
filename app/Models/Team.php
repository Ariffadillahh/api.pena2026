<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'competition_id',
        'wave_id',
        'name',
        'institution',
        'score_board',
        'payment_method',
        'status',
        'payment_status',
        'total_score',
        'notes',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function wave()
    {
        return $this->belongsTo(RegistrationWave::class, 'wave_id');
    }

    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function files()
    {
        return $this->hasMany(TeamFile::class, 'team_id');
    }

    public function team_files()
    {
        return $this->hasMany(TeamFile::class, 'team_id', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scores()
    {
        return $this->hasMany(Score::class, 'team_id', 'id');
    }

    public function score()
    {
        return $this->hasOne(Score::class, 'team_id', 'id');
    }

    public function submission()
    {
        return $this->hasOne(Submission::class, 'team_id', 'id');
    }

    public function attendance()
    {
        return $this->hasOne(TeamAttendance::class, 'team_id', 'id');
    }
}
