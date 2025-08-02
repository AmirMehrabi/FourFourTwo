<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    protected $fillable = [
        'season_id',
        'home_team_id',
        'away_team_id',
        'venue_id',
        'match_datetime',
        'matchweek',
        'status',
        'home_score',
        'away_score',
        'api_id',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}
