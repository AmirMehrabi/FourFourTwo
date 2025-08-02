<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Venue;
use App\Models\Season;
use App\Models\Fixture;
use Carbon\Carbon;

class FixtureSeeder extends Seeder
{
    public function run(): void
    {
        Fixture::query()->delete();

        $season = Season::where('name', '2025/26')->firstOrFail();
        $teams = Team::all()->keyBy('name');

        // Manual mapping from team to their stadium name for easy lookup
        $teamVenueMap = [
            'Arsenal' => 'Emirates Stadium', 'Aston Villa' => 'Villa Park', 'Bournemouth' => 'Dean Court',
            'Brentford' => 'Brentford Community Stadium', 'Brighton & Hove Albion' => 'Falmer Stadium',
            'Burnley' => 'Turf Moor', 'Chelsea' => 'Stamford Bridge', 'Crystal Palace' => 'Selhurst Park',
            'Everton' => 'Everton Stadium', 'Fulham' => 'Craven Cottage', 'Leeds United' => 'Elland Road',
            'Liverpool' => 'Anfield', 'Manchester City' => 'Etihad Stadium', 'Manchester United' => 'Old Trafford',
            'Newcastle United' => 'St James\' Park', 'Nottingham Forest' => 'City Ground', 'Sunderland' => 'Stadium of Light',
            'Tottenham Hotspur' => 'Tottenham Hotspur Stadium', 'West Ham United' => 'London Stadium',
            'Wolverhampton Wanderers' => 'Molineux Stadium',
        ];
        $venues = Venue::all()->keyBy('name');

        $fixturesData = [
            // Matchweek 1
            ['date' => '2025-08-15 22:30', 'home' => 'Liverpool', 'away' => 'Bournemouth'],
            ['date' => '2025-08-16 15:00', 'home' => 'Aston Villa', 'away' => 'Newcastle United'],
            ['date' => '2025-08-16 17:30', 'home' => 'Brighton & Hove Albion', 'away' => 'Fulham'],
            ['date' => '2025-08-16 17:30', 'home' => 'Sunderland', 'away' => 'West Ham United'],
            ['date' => '2025-08-16 17:30', 'home' => 'Tottenham Hotspur', 'away' => 'Burnley'],
            ['date' => '2025-08-16 20:00', 'home' => 'Wolverhampton Wanderers', 'away' => 'Manchester City'],
            ['date' => '2025-08-17 16:30', 'home' => 'Chelsea', 'away' => 'Crystal Palace'],
            ['date' => '2025-08-17 16:30', 'home' => 'Nottingham Forest', 'away' => 'Brentford'],
            ['date' => '2025-08-17 19:00', 'home' => 'Manchester United', 'away' => 'Arsenal'],
            ['date' => '2025-08-18 22:30', 'home' => 'Leeds United', 'away' => 'Everton'],

            // Matchweek 2
            ['date' => '2025-08-22 22:30', 'home' => 'West Ham United', 'away' => 'Chelsea'],
            ['date' => '2025-08-23 15:00', 'home' => 'Manchester City', 'away' => 'Tottenham Hotspur'],
            ['date' => '2025-08-23 17:30', 'home' => 'Bournemouth', 'away' => 'Wolverhampton Wanderers'],
            ['date' => '2025-08-23 17:30', 'home' => 'Brentford', 'away' => 'Aston Villa'],
            ['date' => '2025-08-23 17:30', 'home' => 'Burnley', 'away' => 'Sunderland'],
            ['date' => '2025-08-23 20:00', 'home' => 'Arsenal', 'away' => 'Leeds United'],
            ['date' => '2025-08-24 16:30', 'home' => 'Crystal Palace', 'away' => 'Nottingham Forest'],
            ['date' => '2025-08-24 16:30', 'home' => 'Everton', 'away' => 'Brighton & Hove Albion'],
            ['date' => '2025-08-24 19:00', 'home' => 'Fulham', 'away' => 'Manchester United'],
            ['date' => '2025-08-25 22:30', 'home' => 'Newcastle United', 'away' => 'Liverpool'],

            // Matchweek 3
            ['date' => '2025-08-29 22:30', 'home' => 'Aston Villa', 'away' => 'Crystal Palace'],
            ['date' => '2025-08-30 15:00', 'home' => 'Chelsea', 'away' => 'Fulham'],
            ['date' => '2025-08-30 17:30', 'home' => 'Manchester United', 'away' => 'Burnley'],
            ['date' => '2025-08-30 17:30', 'home' => 'Sunderland', 'away' => 'Brentford'],
            ['date' => '2025-08-30 17:30', 'home' => 'Tottenham Hotspur', 'away' => 'Bournemouth'],
            ['date' => '2025-08-30 17:30', 'home' => 'Wolverhampton Wanderers', 'away' => 'Everton'],
            ['date' => '2025-08-30 20:00', 'home' => 'Leeds United', 'away' => 'Newcastle United'],
            ['date' => '2025-08-31 16:30', 'home' => 'Brighton & Hove Albion', 'away' => 'Manchester City'],
            ['date' => '2025-08-31 16:30', 'home' => 'Nottingham Forest', 'away' => 'West Ham United'],
            ['date' => '2025-08-31 19:00', 'home' => 'Liverpool', 'away' => 'Arsenal'],
        ];

        // Manually define matchweeks for the data
        $matchweekRanges = [1 => [0, 9], 2 => [10, 19], 3 => [20, 29]];
        foreach ($matchweekRanges as $mw => $range) {
            for ($i = $range[0]; $i <= $range[1]; $i++) {
                $fixtureData = $fixturesData[$i];
                $homeTeamName = $fixtureData['home'];
                $venueName = $teamVenueMap[$homeTeamName];

                Fixture::create([
                    'season_id' => $season->id,
                    'matchweek' => $mw,
                    'home_team_id' => $teams[$homeTeamName]->id,
                    'away_team_id' => $teams[$fixtureData['away']]->id,
                    'venue_id' => $venues[$venueName]->id,
                    // Parse the time as CEST (your local time) and convert to UTC for storage
                    'match_datetime' => Carbon::parse($fixtureData['date'], 'Europe/Amsterdam')->setTimezone('UTC'),
                ]);
            }
        }
    }
}
