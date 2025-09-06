<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\Prediction;
use App\Models\Fixture;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AchievementService
{
    /**
     * Check and award all applicable badges for a user.
     */
    public function checkUserAchievements(User $user, string $trigger = null): array
    {
        if (!config('badges.enabled', true)) {
            return [];
        }

        $awardedBadges = [];
        $definitions = config('badges.definitions', []);
        $userStats = $user->getBadgeStats();

        foreach ($definitions as $key => $definition) {
            // Skip if user already has this badge
            if ($user->hasBadge($key)) {
                continue;
            }

            // Check if user meets the criteria
            if ($this->meetsRequirements($user, $definition['criteria'], $userStats)) {
                if ($user->awardBadge($key, ['trigger' => $trigger, 'stats' => $userStats])) {
                    $awardedBadges[] = $key;
                    
                    // Create activity feed entry for badge awards
                    $badge = \App\Models\Badge::findByKey($key);
                    if ($badge) {
                        \App\Models\ActivityFeed::createBadgeActivity($user, $badge, ['trigger' => $trigger]);
                    }
                    
                    Log::info("Badge awarded", [
                        'user_id' => $user->id,
                        'badge_key' => $key,
                        'trigger' => $trigger
                    ]);
                }
            }
        }

        return $awardedBadges;
    }

    /**
     * Check if user meets the requirements for a badge.
     */
    private function meetsRequirements(User $user, array $criteria, array $userStats): bool
    {
        switch ($criteria['type']) {
            case 'count':
                return $this->checkCountCriteria($criteria, $userStats);
            
            case 'multiple':
                return $this->checkMultipleCriteria($criteria, $userStats);
            
            case 'custom':
                return $this->checkCustomCriteria($user, $criteria);
            
            default:
                return false;
        }
    }

    /**
     * Check count-based criteria.
     */
    private function checkCountCriteria(array $criteria, array $userStats): bool
    {
        $field = $criteria['field'];
        $operator = $criteria['operator'];
        $value = $criteria['value'];
        $userValue = $userStats[$field] ?? 0;

        return match ($operator) {
            '>=' => $userValue >= $value,
            '>' => $userValue > $value,
            '<=' => $userValue <= $value,
            '<' => $userValue < $value,
            '=' => $userValue == $value,
            '!=' => $userValue != $value,
            default => false,
        };
    }

    /**
     * Check multiple criteria with AND/OR logic.
     */
    private function checkMultipleCriteria(array $criteria, array $userStats): bool
    {
        $logic = $criteria['logic'] ?? 'and';
        $conditions = $criteria['conditions'] ?? [];
        $results = [];

        foreach ($conditions as $condition) {
            $field = $condition['field'];
            $operator = $condition['operator'];
            $value = $condition['value'];
            $userValue = $userStats[$field] ?? 0;

            $results[] = match ($operator) {
                '>=' => $userValue >= $value,
                '>' => $userValue > $value,
                '<=' => $userValue <= $value,
                '<' => $userValue < $value,
                '=' => $userValue == $value,
                '!=' => $userValue != $value,
                default => false,
            };
        }

        return $logic === 'and' ? !in_array(false, $results) : in_array(true, $results);
    }

    /**
     * Check custom criteria using specific methods.
     */
    private function checkCustomCriteria(User $user, array $criteria): bool
    {
        $method = $criteria['method'];
        $params = $criteria['params'] ?? [];

        return match ($method) {
            'hasCompletedGameweek' => $this->hasCompletedGameweek($user),
            'hasEarlyPrediction' => $this->hasEarlyPrediction($user, $params['hours'] ?? 24),
            'hasActiveDays' => $this->hasActiveDays($user, $params['days'] ?? 30),
            'hasPerfectGameweek' => $this->hasPerfectGameweek($user),
            'hasDerbyPredictions' => $this->hasDerbyPredictions($user, $params['count'] ?? 5),
            'hasUpsetPredictions' => $this->hasUpsetPredictions($user, $params['count'] ?? 3),
            default => false,
        };
    }

    /**
     * Check if user has completed a full gameweek.
     */
    private function hasCompletedGameweek(User $user): bool
    {
        // Get all gameweeks and check if user has predicted all matches in any gameweek
        $gameweeks = Fixture::select('matchweek')
                           ->groupBy('matchweek')
                           ->pluck('matchweek');

        foreach ($gameweeks as $gameweek) {
            $totalMatches = Fixture::where('matchweek', $gameweek)->count();
            $userPredictions = $user->predictions()
                                  ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
                                  ->where('fixtures.matchweek', $gameweek)
                                  ->count();

            if ($userPredictions >= $totalMatches && $totalMatches >= 8) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has made early predictions.
     */
    private function hasEarlyPrediction(User $user, int $hours): bool
    {
        // Get all predictions with their fixture datetime
        $predictions = $user->predictions()
                           ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
                           ->select('predictions.created_at', 'fixtures.match_datetime')
                           ->get();

        foreach ($predictions as $prediction) {
            $predictionTime = Carbon::parse($prediction->created_at);
            $matchTime = Carbon::parse($prediction->match_datetime);
            
            // Check if prediction was made at least X hours before match
            if ($predictionTime->diffInHours($matchTime, false) >= $hours) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has been active for specified days.
     */
    private function hasActiveDays(User $user, int $days): bool
    {
        $cutoffDate = Carbon::now()->subDays($days);
        
        // Count unique days with activity (predictions or comments)
        $predictionDays = $user->predictions()
                              ->where('created_at', '>=', $cutoffDate)
                              ->selectRaw('DATE(created_at) as day')
                              ->distinct()
                              ->pluck('day');

        $commentDays = $user->comments()
                           ->where('created_at', '>=', $cutoffDate)
                           ->selectRaw('DATE(created_at) as day')
                           ->distinct()
                           ->pluck('day');

        $uniqueDays = $predictionDays->merge($commentDays)->unique();
        
        return $uniqueDays->count() >= $days;
    }

    /**
     * Check if user has a perfect gameweek.
     */
    private function hasPerfectGameweek(User $user): bool
    {
        // Get all gameweeks where user has predictions
        $gameweeksWithPredictions = $user->predictions()
                                        ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
                                        ->select('fixtures.matchweek')
                                        ->distinct()
                                        ->pluck('matchweek');

        foreach ($gameweeksWithPredictions as $gameweek) {
            // Get all predictions for this gameweek
            $predictions = $user->predictions()
                              ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
                              ->where('fixtures.matchweek', $gameweek)
                              ->whereNotNull('predictions.points_awarded')
                              ->get();

            // Check if all predictions were correct (points > 0)
            if ($predictions->count() >= 8 && $predictions->every(fn($p) => $p->points_awarded > 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has correct derby predictions.
     */
    private function hasDerbyPredictions(User $user, int $requiredCount): bool
    {
        $derbyMatches = config('badges.derby_matches', []);
        $correctDerbyPredictions = 0;

        foreach ($derbyMatches as $derby) {
            $correctPredictions = $user->predictions()
                                      ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
                                      ->where(function($query) use ($derby) {
                                          $query->where([
                                              'fixtures.home_team_id' => $derby['home'],
                                              'fixtures.away_team_id' => $derby['away']
                                          ]);
                                      })
                                      ->where('predictions.points_awarded', '>', 0)
                                      ->count();

            $correctDerbyPredictions += $correctPredictions;
        }

        return $correctDerbyPredictions >= $requiredCount;
    }

    /**
     * Check if user has predicted upsets correctly.
     */
    private function hasUpsetPredictions(User $user, int $requiredCount): bool
    {
        // Define upset as when a team with lower league position beats higher positioned team
        // This is a simplified implementation - you might want to enhance this logic
        return $user->predictions()
                   ->where('points_awarded', '>', 0)
                   ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
                   ->join('league_tables as home_table', function($join) {
                       $join->on('fixtures.home_team_id', '=', 'home_table.team_id')
                            ->on('fixtures.season_id', '=', 'home_table.season_id');
                   })
                   ->join('league_tables as away_table', function($join) {
                       $join->on('fixtures.away_team_id', '=', 'away_table.team_id')
                            ->on('fixtures.season_id', '=', 'away_table.season_id');
                   })
                   ->where(function($query) {
                       // Away win when home team has better position (lower position number)
                       $query->where('fixtures.away_score', '>', 'fixtures.home_score')
                             ->where('home_table.position', '<', 'away_table.position');
                   })
                   ->orWhere(function($query) {
                       // Home win when away team has better position
                       $query->where('fixtures.home_score', '>', 'fixtures.away_score')
                             ->where('away_table.position', '<', 'home_table.position');
                   })
                   ->count() >= $requiredCount;
    }

    /**
     * Bulk check achievements for all users (for use in commands/jobs).
     */
    public function bulkCheckAchievements(string $trigger = 'bulk_check'): array
    {
        $results = [];
        $users = User::with(['predictions', 'comments', 'badges'])->get();

        foreach ($users as $user) {
            $awardedBadges = $this->checkUserAchievements($user, $trigger);
            if (!empty($awardedBadges)) {
                $results[$user->id] = $awardedBadges;
            }
        }

        return $results;
    }

    /**
     * Check achievements for a specific trigger event.
     */
    public function checkForTrigger(User $user, string $trigger, array $context = []): array
    {
        return $this->checkUserAchievements($user, $trigger);
    }

    /**
     * Get progress towards unearned badges for a user.
     */
    public function getBadgeProgress(User $user): array
    {
        $definitions = config('badges.definitions', []);
        $userStats = $user->getBadgeStats();
        $progress = [];

        foreach ($definitions as $key => $definition) {
            if ($user->hasBadge($key)) {
                continue;
            }

            $progressData = $this->calculateProgress($definition['criteria'], $userStats);
            if ($progressData['percentage'] > 0) {
                $progress[$key] = array_merge($definition, $progressData);
            }
        }

        return $progress;
    }

    /**
     * Calculate progress percentage for a badge.
     */
    private function calculateProgress(array $criteria, array $userStats): array
    {
        if ($criteria['type'] !== 'count' && $criteria['type'] !== 'multiple') {
            return ['percentage' => 0, 'current' => 0, 'required' => 1];
        }

        if ($criteria['type'] === 'count') {
            $field = $criteria['field'];
            $required = $criteria['value'];
            $current = $userStats[$field] ?? 0;
            
            return [
                'percentage' => min(100, round(($current / $required) * 100)),
                'current' => $current,
                'required' => $required,
            ];
        }

        // For multiple criteria, return progress for the first numeric condition
        if ($criteria['type'] === 'multiple') {
            foreach ($criteria['conditions'] as $condition) {
                if (is_numeric($condition['value'])) {
                    $field = $condition['field'];
                    $required = $condition['value'];
                    $current = $userStats[$field] ?? 0;
                    
                    return [
                        'percentage' => min(100, round(($current / $required) * 100)),
                        'current' => $current,
                        'required' => $required,
                    ];
                }
            }
        }

        return ['percentage' => 0, 'current' => 0, 'required' => 1];
    }
}
