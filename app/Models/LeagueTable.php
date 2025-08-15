<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class LeagueTable extends Model
{
    protected $fillable = [
        'season_id',
        'team_id',
        'position',
        'played',
        'won',
        'drawn',
        'lost',
        'goals_for',
        'goals_against',
        'goal_difference',
        'points',
        'form',
    ];

    protected $casts = [
        'position' => 'integer',
        'played' => 'integer',
        'won' => 'integer',
        'drawn' => 'integer',
        'lost' => 'integer',
        'goals_for' => 'integer',
        'goals_against' => 'integer',
        'goal_difference' => 'integer',
        'points' => 'integer',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Update the entire league table for a season
     */
    public static function updateTableForSeason($seasonId)
    {
        $teams = Team::all();
        
        foreach ($teams as $team) {
            $stats = self::calculateTeamStats($team->id, $seasonId);
            
            self::updateOrCreate(
                ['season_id' => $seasonId, 'team_id' => $team->id],
                $stats
            );
        }
        
        // Update positions based on points, goal difference, etc.
        self::updatePositions($seasonId);
    }

    /**
     * Calculate team statistics including live matches
     */
    private static function calculateTeamStats($teamId, $seasonId)
    {
        // Get all fixtures (finished and in_play) for this team in this season
        $fixtures = Fixture::where('season_id', $seasonId)
            ->where(function ($query) use ($teamId) {
                $query->where('home_team_id', $teamId)
                      ->orWhere('away_team_id', $teamId);
            })
            ->whereIn('status', ['finished', 'in_play'])
            ->orderBy('match_datetime', 'desc')
            ->get();

        $stats = [
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0,
        ];

        $form = [];

        foreach ($fixtures as $fixture) {
            // Only count finished matches for final stats
            if ($fixture->status === 'finished' || 
                ($fixture->status === 'in_play' && $fixture->home_score !== null && $fixture->away_score !== null)) {
                
                $stats['played']++;
                
                $isHome = $fixture->home_team_id == $teamId;
                $teamScore = $isHome ? $fixture->home_score : $fixture->away_score;
                $opponentScore = $isHome ? $fixture->away_score : $fixture->home_score;
                
                $stats['goals_for'] += $teamScore;
                $stats['goals_against'] += $opponentScore;
                
                if ($teamScore > $opponentScore) {
                    $stats['won']++;
                    $stats['points'] += 3;
                    $form[] = 'W';
                } elseif ($teamScore == $opponentScore) {
                    $stats['drawn']++;
                    $stats['points'] += 1;
                    $form[] = 'D';
                } else {
                    $stats['lost']++;
                    $form[] = 'L';
                }
            }
        }

        $stats['goal_difference'] = $stats['goals_for'] - $stats['goals_against'];
        $stats['form'] = implode('', array_slice($form, 0, 5)); // Last 5 games

        return $stats;
    }

    /**
     * Update positions based on Premier League rules
     */
    private static function updatePositions($seasonId)
    {
        $tables = self::where('season_id', $seasonId)
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc')
            ->get();
            
        foreach ($tables as $index => $table) {
            $table->position = $index + 1;
            $table->save();
        }
    }

    /**
     * Get the current league table with live updates
     */
    public static function getCurrentTable($seasonId)
    {
        return self::with(['team'])
            ->where('season_id', $seasonId)
            ->orderBy('position')
            ->get()
            ->map(function ($entry) {
                $entry->team->logo_url = '/assets/team-logos/' . urlencode($entry->team->name) . '.png';
                $entry->has_live_match = self::hasLiveMatch($entry->team_id, $entry->season_id);
                return $entry;
            });
    }

    /**
     * Check if a team has a live match
     */
    private static function hasLiveMatch($teamId, $seasonId)
    {
        return Fixture::where('season_id', $seasonId)
            ->where(function ($query) use ($teamId) {
                $query->where('home_team_id', $teamId)
                      ->orWhere('away_team_id', $teamId);
            })
            ->where('status', 'in_play')
            ->exists();
    }
}
