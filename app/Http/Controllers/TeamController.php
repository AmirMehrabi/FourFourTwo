<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Display the specified team page.
     */
    public function show($slug)
    {
        $team = Team::where('slug', $slug)->firstOrFail();
        // You can eager load relations here as needed
        // $team->load(['homeFixtures', 'awayFixtures', 'leagueTableEntries']);

        // For now, just pass the team data (add more as needed for widgets)
        // Dummy next match data (replace with real query later)
        $nextMatch = [
            'opponent_logo' => '/assets/team-logos/Arsenal.png',
            'opponent_name' => 'آرسنال',
            'matchweek' => 37,
            'date' => '2025-08-17',
            'time' => '18:00',
            'venue' => 'اتحاد',
            'locked' => false,
        ];

        return Inertia::render('Team', [
            'team' => $team,
            'nextMatch' => $nextMatch,
            // Add more props here for widgets as you wire up real data
        ]);
    }
}
