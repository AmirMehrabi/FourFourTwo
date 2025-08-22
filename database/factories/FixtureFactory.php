<?php

namespace Database\Factories;

use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class FixtureFactory extends Factory
{
    protected $model = Fixture::class;

    public function definition(): array
    {
        $season = Season::factory()->create();
        $home = Team::factory()->create();
        $away = Team::factory()->create();
        return [
            'season_id' => $season->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'venue_id' => null,
            'match_datetime' => Carbon::now()->subMinutes(120),
            'matchweek' => 1,
            'status' => 'scheduled',
            'home_score' => null,
            'away_score' => null,
        ];
    }
}
