<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Fixture extends Model
{
    use HasFactory;
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
        'score_details',
        'api_id',
    ];

    protected $casts = [
        'match_datetime' => 'datetime',
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];

    protected static function booted()
    {
        // Update league table when fixture status or scores change
        static::updated(function ($fixture) {
            if ($fixture->wasChanged(['status', 'home_score', 'away_score'])) {
                app(\App\Services\LeagueTableService::class)->updateTableForSeason($fixture->season_id);
            }
        });

        // Auto-update status to in_play when match time arrives
        static::saving(function ($fixture) {
            if ($fixture->status === 'scheduled' && 
                $fixture->match_datetime && 
                Carbon::now()->gte($fixture->match_datetime)) {
                $fixture->status = 'in_play';
            }
        });
    }

    /**
     * Scope for live matches
     */
    public function scopeLive($query)
    {
        return $query->where('status', 'in_play');
    }

    /**
     * Scope for finished matches
     */
    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }

    /**
     * Check if the match is currently live
     */
    public function isLive()
    {
        return $this->status === 'in_play';
    }

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

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    /**
     * Get all comments for this fixture.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
                   ->whereNull('parent_id') // Only top-level comments
                   ->orderBy('created_at', 'desc');
    }

    /**
     * Get all comments including replies for this fixture.
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
