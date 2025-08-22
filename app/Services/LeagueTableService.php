<?php

namespace App\Services;

use App\Models\LeagueTable;
use App\Models\Team;
use App\Models\Fixture;
use App\Models\Season;
use Illuminate\Support\Collection;

class LeagueTableService
{
    /**
     * Update the league table for a specific season
     */
    public function updateTableForSeason(int $seasonId): void
    {
        $teams = Team::all();
        
        foreach ($teams as $team) {
            $stats = $this->calculateTeamStats($team->id, $seasonId);
            
            LeagueTable::updateOrCreate(
                ['season_id' => $seasonId, 'team_id' => $team->id],
                $stats
            );
        }
        
        $this->updatePositions($seasonId);
    }
    
    /**
     * Get live league table including in-play matches
     */
    public function getLiveTable(int $seasonId): Collection
    {
        // Get all teams
        $teams = Team::all();
        
        // If no teams exist, return empty collection
        if ($teams->isEmpty()) {
            return collect([]);
        }
        
        // Get current table entries (if any exist)
        $existingTableEntries = LeagueTable::with('team')
            ->where('season_id', $seasonId)
            ->get()
            ->keyBy('team_id');
            
        // Get live matches for this season
        $liveMatches = Fixture::with(['homeTeam', 'awayTeam'])
            ->where('season_id', $seasonId)
            ->where('status', 'in_play')
            ->get();
            
        // Create table data for all teams (with 0 stats if no fixtures played yet)
    $tableData = $teams->map(function ($team, $index) use ($seasonId, $existingTableEntries, $liveMatches) {
            // Get existing stats or create default 0 stats
            $existingEntry = $existingTableEntries->get($team->id);
            
            $teamStats = [
                'team' => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'name_fa' => $team->name_fa,
                    'display_name' => $team->display_name,
                    'logo_url' => $team->logo_url, // Use the model accessor
                ],
                'position' => $existingEntry ? $existingEntry->position : ($index + 1),
                'played' => $existingEntry ? $existingEntry->played : 0,
                'won' => $existingEntry ? $existingEntry->won : 0,
                'drawn' => $existingEntry ? $existingEntry->drawn : 0,
                'lost' => $existingEntry ? $existingEntry->lost : 0,
                'goals_for' => $existingEntry ? $existingEntry->goals_for : 0,
                'goals_against' => $existingEntry ? $existingEntry->goals_against : 0,
                'goal_difference' => $existingEntry ? $existingEntry->goal_difference : 0,
                'points' => $existingEntry ? $existingEntry->points : 0,
                'form' => $existingEntry ? str_split($existingEntry->form) : [],
                'has_live_match' => false,
                'live_adjustments' => [
                    'points' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                    'is_playing' => false,
                    'current_match' => null,
                    'played' => 0,
                    'won' => 0,
                    'drawn' => 0,
                    'lost' => 0,
                ],
                // Preserve base values in case needed on frontend
                'base_stats' => [
                    'played' => $existingEntry ? $existingEntry->played : 0,
                    'won' => $existingEntry ? $existingEntry->won : 0,
                    'drawn' => $existingEntry ? $existingEntry->drawn : 0,
                    'lost' => $existingEntry ? $existingEntry->lost : 0,
                    'goals_for' => $existingEntry ? $existingEntry->goals_for : 0,
                    'goals_against' => $existingEntry ? $existingEntry->goals_against : 0,
                    'goal_difference' => $existingEntry ? $existingEntry->goal_difference : 0,
                    'points' => $existingEntry ? $existingEntry->points : 0,
                ]
            ];
            
            // Check if team is currently playing
            foreach ($liveMatches as $match) {
                if ($match->home_team_id == $team->id || $match->away_team_id == $team->id) {
                    $teamStats['has_live_match'] = true;
                    $teamStats['live_adjustments']['is_playing'] = true;
                    $teamStats['live_adjustments']['current_match'] = [
                        'opponent' => $match->home_team_id == $team->id ? $match->awayTeam->name : $match->homeTeam->name,
                        'is_home' => $match->home_team_id == $team->id,
                        'home_score' => $match->home_score ?? 0,
                        'away_score' => $match->away_score ?? 0,
                    ];
                    
                    // Calculate potential points from current live score
                    // Provisional played increment while match is live
                    $teamStats['live_adjustments']['played'] = 1;

                    if ($match->home_score !== null && $match->away_score !== null) {
                        $isHome = $match->home_team_id == $team->id;
                        $for = $isHome ? $match->home_score : $match->away_score;
                        $against = $isHome ? $match->away_score : $match->home_score;
                        $teamStats['live_adjustments']['goals_for'] = $for;
                        $teamStats['live_adjustments']['goals_against'] = $against;

                        if ($for > $against) {
                            $teamStats['live_adjustments']['won'] = 1;
                            $teamStats['live_adjustments']['points'] = 3;
                            $provisionalForm = 'W';
                        } elseif ($for == $against) {
                            $teamStats['live_adjustments']['drawn'] = 1;
                            $teamStats['live_adjustments']['points'] = 1;
                            $provisionalForm = 'D';
                        } else {
                            $teamStats['live_adjustments']['lost'] = 1;
                            $provisionalForm = 'L';
                        }
                        // Provisional form (most recent first): prepend
                        $currentForm = $teamStats['form'];
                        $teamStats['live_form'] = array_merge([$provisionalForm], $currentForm);
                    }
                    break;
                }
            }
            
            // Calculate live totals (base + adjustments)
            $teamStats['live_points'] = $teamStats['points'] + $teamStats['live_adjustments']['points'];
            $teamStats['live_goals_for'] = $teamStats['goals_for'] + $teamStats['live_adjustments']['goals_for'];
            $teamStats['live_goals_against'] = $teamStats['goals_against'] + $teamStats['live_adjustments']['goals_against'];
            $teamStats['live_goal_difference'] = $teamStats['live_goals_for'] - $teamStats['live_goals_against'];
            $teamStats['live_played'] = $teamStats['played'] + $teamStats['live_adjustments']['played'];
            $teamStats['live_won'] = $teamStats['won'] + $teamStats['live_adjustments']['won'];
            $teamStats['live_drawn'] = $teamStats['drawn'] + $teamStats['live_adjustments']['drawn'];
            $teamStats['live_lost'] = $teamStats['lost'] + $teamStats['live_adjustments']['lost'];

            // Overwrite primary displayed stats with live versions if currently playing
            if ($teamStats['live_adjustments']['is_playing']) {
                $teamStats['played'] = $teamStats['live_played'];
                $teamStats['won'] = $teamStats['live_won'];
                $teamStats['drawn'] = $teamStats['live_drawn'];
                $teamStats['lost'] = $teamStats['live_lost'];
                $teamStats['goals_for'] = $teamStats['live_goals_for'];
                $teamStats['goals_against'] = $teamStats['live_goals_against'];
                $teamStats['goal_difference'] = $teamStats['live_goal_difference'];
                $teamStats['points'] = $teamStats['live_points'];
                if (isset($teamStats['live_form'])) {
                    $teamStats['form'] = $teamStats['live_form'];
                }
                // Set directly on team for UI to place indicator next to name
                $teamStats['team']['is_live'] = true;
            } else {
                $teamStats['team']['is_live'] = false;
            }
            
            return $teamStats;
        });
        
        // Sort by live points, goal difference, etc. using custom sorting
        $sortedTable = $tableData->sort(function ($a, $b) {
            // First by points (descending)
            if ($a['live_points'] !== $b['live_points']) {
                return $b['live_points'] <=> $a['live_points'];
            }
            
            // Then by goal difference (descending)
            if ($a['live_goal_difference'] !== $b['live_goal_difference']) {
                return $b['live_goal_difference'] <=> $a['live_goal_difference'];
            }
            
            // Then by goals for (descending)
            if ($a['live_goals_for'] !== $b['live_goals_for']) {
                return $b['live_goals_for'] <=> $a['live_goals_for'];
            }
            
            // Finally by team name (ascending)
            return $a['team']['name'] <=> $b['team']['name'];
        })->values();
        
        // Add live positions
        $sortedTable = $sortedTable->map(function ($item, $index) {
            $livePos = $index + 1;
            $item['live_position'] = $livePos;
            // Overwrite displayed position with live ordering for all teams
            $item['position'] = $livePos;
            return $item;
        });
        
        return $sortedTable;
    }
    
    /**
     * Calculate team statistics for a season
     */
    private function calculateTeamStats(int $teamId, int $seasonId): array
    {
        $homeFixtures = Fixture::where('season_id', $seasonId)
            ->where('home_team_id', $teamId)
            ->where('status', 'finished')
            ->get();
            
        $awayFixtures = Fixture::where('season_id', $seasonId)
            ->where('away_team_id', $teamId)
            ->where('status', 'finished')
            ->get();

        $stats = [
            'played' => $homeFixtures->count() + $awayFixtures->count(),
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0,
        ];

        // Calculate home stats
        foreach ($homeFixtures as $fixture) {
            $stats['goals_for'] += $fixture->home_score ?? 0;
            $stats['goals_against'] += $fixture->away_score ?? 0;
            
            if ($fixture->home_score > $fixture->away_score) {
                $stats['won']++;
                $stats['points'] += 3;
            } elseif ($fixture->home_score == $fixture->away_score) {
                $stats['drawn']++;
                $stats['points'] += 1;
            } else {
                $stats['lost']++;
            }
        }

        // Calculate away stats
        foreach ($awayFixtures as $fixture) {
            $stats['goals_for'] += $fixture->away_score ?? 0;
            $stats['goals_against'] += $fixture->home_score ?? 0;
            
            if ($fixture->away_score > $fixture->home_score) {
                $stats['won']++;
                $stats['points'] += 3;
            } elseif ($fixture->away_score == $fixture->home_score) {
                $stats['drawn']++;
                $stats['points'] += 1;
            } else {
                $stats['lost']++;
            }
        }

        $stats['goal_difference'] = $stats['goals_for'] - $stats['goals_against'];
        $stats['form'] = $this->calculateForm($teamId, $seasonId);
        
        return $stats;
    }
    
    /**
     * Calculate team form (last 5 games)
     */
    private function calculateForm(int $teamId, int $seasonId): string
    {
        $recentFixtures = Fixture::where('season_id', $seasonId)
            ->where(function ($query) use ($teamId) {
                $query->where('home_team_id', $teamId)
                      ->orWhere('away_team_id', $teamId);
            })
            ->where('status', 'finished')
            ->orderBy('match_datetime', 'desc')
            ->limit(5)
            ->get();
            
        $form = '';
        
        foreach ($recentFixtures as $fixture) {
            if ($fixture->home_team_id == $teamId) {
                // Team played at home
                if ($fixture->home_score > $fixture->away_score) {
                    $form .= 'W';
                } elseif ($fixture->home_score == $fixture->away_score) {
                    $form .= 'D';
                } else {
                    $form .= 'L';
                }
            } else {
                // Team played away
                if ($fixture->away_score > $fixture->home_score) {
                    $form .= 'W';
                } elseif ($fixture->away_score == $fixture->home_score) {
                    $form .= 'D';
                } else {
                    $form .= 'L';
                }
            }
        }
        
        return $form;
    }
    
    /**
     * Update positions based on points, goal difference, etc.
     */
    private function updatePositions(int $seasonId): void
    {
        $tables = LeagueTable::where('season_id', $seasonId)
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc')
            ->get();
            
        foreach ($tables as $index => $table) {
            $table->position = $index + 1;
            $table->save();
        }
    }
}
