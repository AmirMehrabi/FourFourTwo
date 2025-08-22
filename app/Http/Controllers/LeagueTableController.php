<?php
use Illuminate\Support\Str;
<?php

namespace App\Http\Controllers;

use App\Models\LeagueTable;
use App\Models\Season;
use App\Services\LeagueTableService;
use Illuminate\Http\Request;

class LeagueTableController extends Controller
{
    protected $leagueTableService;
    
    public function __construct(LeagueTableService $leagueTableService)
    {
        $this->leagueTableService = $leagueTableService;
    }
    
    /**
     * Get the current season's league table with live updates
     */
    public function index(Request $request)
    {
        // Get current season or default to the latest active season
        $season = Season::where('is_active', true)->first();
        
        if (!$season) {
            return response()->json(['error' => 'No active season found'], 404);
        }

        // Get live table data
        $liveTable = $this->leagueTableService->getLiveTable($season->id);
        
        $formattedTable = $liveTable->map(function ($row) {
            return [
                'position' => $row['position'],
                'live_position' => $row['live_position'],
                'team' => [
                    'id' => $row['team']['id'],
                    'slug' => $row['team']['slug'] ?? Str::slug($row['team']['name']),
                    'name' => $row['team']['name'],
                    'name_fa' => $row['team']['name_fa'],
                    'display_name' => $row['team']['display_name'],
                    'logo' => $row['team']['logo_url'],
                ],
                'played' => $row['played'],
                'won' => $row['won'],
                'drawn' => $row['drawn'],
                'lost' => $row['lost'],
                'goals_for' => $row['goals_for'],
                'goals_against' => $row['goals_against'],
                'goal_difference' => $row['goal_difference'],
                'points' => $row['points'],
                'live_points' => $row['live_points'],
                'live_goals_for' => $row['live_goals_for'],
                'live_goals_against' => $row['live_goals_against'],
                'live_goal_difference' => $row['live_goal_difference'],
                'form' => is_string($row['form']) ? str_split($row['form']) : $row['form'],
                'live_adjustments' => $row['live_adjustments'],
                'is_playing' => $row['live_adjustments']['is_playing'],
                'has_live_match' => $row['has_live_match'],
            ];
        });

        return response()->json([
            'season' => $season,
            'table' => $formattedTable,
            'has_live_matches' => $formattedTable->contains('is_playing', true),
            'last_updated' => now()->toISOString(),
        ]);
    }

    /**
     * Force update the league table
     */
    public function update(Request $request)
    {
        $season = Season::where('is_active', true)->first();
        
        if (!$season) {
            return response()->json(['error' => 'No active season found'], 404);
        }

        $this->leagueTableService->updateTableForSeason($season->id);

        return response()->json(['message' => 'League table updated successfully']);
    }
    
    private function getTeamLogo($teamName)
    {
        $logoPath = "/assets/team-logos/{$teamName}.png";
        $fullPath = public_path($logoPath);
        
        if (file_exists($fullPath)) {
            return $logoPath;
        }
        
        return '/assets/team-logos/default.png'; // fallback
    }
    
    private function formatForm($form)
    {
        if (!$form) return [];
        
        return str_split($form);
    }
}
