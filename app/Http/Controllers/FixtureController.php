<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;


class FixtureController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Determine the matchweek to show
        $targetMatchweek = $request->input('matchweek');

        if (!$targetMatchweek) {
            // If no matchweek is specified, find the first upcoming one
            $nextFixture = Fixture::where('match_datetime', '>', now())
                                  ->orderBy('match_datetime', 'asc')
                                  ->first();
            $targetMatchweek = $nextFixture ? $nextFixture->matchweek : 1; // Default to 1 if season hasn't started
        }

        // 2. Fetch fixtures for that matchweek
        $fixtures = Fixture::with(['homeTeam', 'awayTeam', 'venue'])
            ->where('matchweek', $targetMatchweek)
            ->orderBy('match_datetime', 'asc')
            ->get();

        // 3. Fetch the current user's predictions for these fixtures
        $userPredictions = Auth::user()->predictions()
            ->whereIn('fixture_id', $fixtures->pluck('id'))
            ->get()
            ->keyBy('fixture_id');

        // 4. Attach predictions to each fixture and check if it's locked
        $fixtures->transform(function ($fixture) use ($userPredictions) {
            $fixture->prediction = $userPredictions->get($fixture->id);
            // Predictions lock 1 hour before kickoff
            $fixture->is_locked = Carbon::parse($fixture->match_datetime)->isBefore(now()->addHour());
            return $fixture;
        });

        return Inertia::render('Fixtures/Index', [
            'fixtures' => $fixtures,
            'matchweek' => (int)$targetMatchweek,
        ]);
    }
}
