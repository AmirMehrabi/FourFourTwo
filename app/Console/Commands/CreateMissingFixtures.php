<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Season;
use App\Models\Team;
use App\Models\Fixture;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CreateMissingFixtures extends Command
{
    protected $signature = 'fixtures:create-missing 
                            {--season= : Season ID (optional)}
                            {--matchweek= : Matchweek number (optional)}
                            {--date= : Match date (YYYY-MM-DD format, optional)}
                            {--time= : Match time (HH:MM format, optional)}';
    
    protected $description = 'Create missing fixtures manually with interactive team selection UI';

    public function handle()
    {
        $this->info('🏆 Premier League Fixture Creator');
        $this->line('===============================');
        $this->newLine();

        // Get or suggest season
        $season = $this->getOrSuggestSeason();
        if (!$season) {
            return Command::FAILURE;
        }

        // Get or suggest matchweek
        $matchweek = $this->getOrSuggestMatchweek($season);
        if (!$matchweek) {
            return Command::FAILURE;
        }

        // Check if fixtures already exist for this matchweek
        $existingFixtures = Fixture::where('season_id', $season->id)
            ->where('matchweek', $matchweek)
            ->count();

        if ($existingFixtures > 0) {
            if (!$this->confirm("⚠️  {$existingFixtures} fixtures already exist for matchweek {$matchweek}. Continue anyway?", false)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Get match date and time
        $matchDateTime = $this->getMatchDateTime();
        if (!$matchDateTime) {
            return Command::FAILURE;
        }

        // Create fixtures manually
        $this->info("🎯 Creating fixtures manually for {$season->name}, Matchweek {$matchweek}...");
        $this->newLine();

        $fixtures = $this->createFixturesManually($season, $matchweek, $matchDateTime);

        if ($fixtures->isEmpty()) {
            $this->warn('⚠️ No fixtures were created.');
            return Command::SUCCESS;
        }

        // Display created fixtures
        $this->displayCreatedFixtures($fixtures);

        $this->newLine();
        $this->info('✅ Fixtures created successfully!');
        
        return Command::SUCCESS;
    }

    protected function getOrSuggestSeason(): ?Season
    {
        if ($this->option('season')) {
            $season = Season::find($this->option('season'));
            if (!$season) {
                $this->error("❌ Season with ID {$this->option('season')} not found.");
                return null;
            }
            return $season;
        }

        // Get active season or let user choose
        $activeSeason = Season::where('is_active', true)->first();

        $seasons = Season::orderBy('start_date', 'desc')->get();
        
        if ($seasons->isEmpty()) {
            $this->error('❌ No seasons found. Please create a season first.');
            return null;
        }

        if ($activeSeason) {
            $default = $activeSeason->name;
            $this->info("💡 Active season: {$default}");
        }

        $seasonChoices = $seasons->pluck('name', 'id')->toArray();
        $selectedSeasonName = $this->choice(
            '📅 Select a season:', 
            $seasonChoices, 
            $activeSeason ? $activeSeason->id : array_key_first($seasonChoices)
        );

        return $seasons->where('name', $selectedSeasonName)->first();
    }

    protected function getOrSuggestMatchweek(Season $season): ?int
    {
        if ($this->option('matchweek')) {
            return (int) $this->option('matchweek');
        }

        // Find the next missing matchweek
        $latestMatchweek = Fixture::where('season_id', $season->id)->max('matchweek') ?? 0;
        $nextMatchweek = $latestMatchweek + 1;

        // Check if current matchweek is complete (assuming 10 matches per matchweek)
        $currentMatchweekFixtures = Fixture::where('season_id', $season->id)
            ->where('matchweek', $latestMatchweek)
            ->count();

        $teamsCount = Team::where('is_active', true)->count();
        $expectedFixturesPerMatchweek = $teamsCount / 2;

        if ($currentMatchweekFixtures < $expectedFixturesPerMatchweek && $latestMatchweek > 0) {
            $nextMatchweek = $latestMatchweek;
            $this->warn("⚠️  Matchweek {$latestMatchweek} is incomplete ({$currentMatchweekFixtures}/{$expectedFixturesPerMatchweek} fixtures).");
        }



        $this->info("💡 Latest matchweek: {$latestMatchweek}");
        $this->info("💡 Suggested next matchweek: {$nextMatchweek}");

        return $this->ask('🗓️  Enter matchweek number:', $nextMatchweek);
    }

    protected function getMatchDateTime(): ?Carbon
    {
        $date = $this->option('date');
        $time = $this->option('time');

        // Get date
        if (!$date) {
            $suggestedDate = Carbon::now()->next(Carbon::SATURDAY)->format('Y-m-d');
            $this->info("💡 Suggested date (next Saturday): {$suggestedDate}");
            
            $date = $this->ask('📅 Enter match date (YYYY-MM-DD):', $suggestedDate);
        }

        // Validate date
        try {
            $matchDate = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            $this->error('❌ Invalid date format. Please use YYYY-MM-DD format.');
            return null;
        }

        // Get time
        if (!$time) {
            $suggestedTime = '15:00'; // 3 PM is common for football matches
            $this->info("💡 Suggested time: {$suggestedTime}");
            
            $time = $this->ask('⏰ Enter match time (HH:MM):', $suggestedTime);
        }

        // Validate time
        try {
            $timeCarbon = Carbon::createFromFormat('H:i', $time);
            $matchDateTime = $matchDate->setTimeFromTimeString($time);
        } catch (\Exception $e) {
            $this->error('❌ Invalid time format. Please use HH:MM format.');
            return null;
        }

        $this->info("📍 Match date & time: {$matchDateTime->format('l, F j, Y \a\t g:i A')}");
        
        if (!$this->confirm('✅ Confirm this date and time?', true)) {
            $this->warn('Please run the command again with different date/time options.');
            return null;
        }

        return $matchDateTime;
    }

    protected function createFixturesManually(Season $season, int $matchweek, Carbon $matchDateTime): Collection
    {
        $teams = Team::where('is_active', true)->get();
        
        if ($teams->count() % 2 !== 0) {
            $this->error('❌ Odd number of teams. Cannot create fixtures.');
            return collect();
        }

        // Get teams that already have fixtures for this matchweek
        $teamsWithFixtures = $this->getTeamsWithFixtures($season, $matchweek);
        $availableTeams = $teams->reject(function ($team) use ($teamsWithFixtures) {
            return $teamsWithFixtures->contains($team->id);
        });

        if ($availableTeams->count() === 0) {
            $this->info('✅ All teams already have fixtures for this matchweek.');
            return collect();
        }

        if ($availableTeams->count() % 2 !== 0) {
            $this->error('❌ Odd number of available teams. Cannot create fixtures.');
            return collect();
        }

        $this->info("📋 Available teams for matchweek {$matchweek}:");
        $availableTeams->each(function ($team, $index) {
            $this->line("  " . ($index + 1) . ". {$team->name}");
        });
        $this->newLine();

        return $this->interactiveFixtureCreation($season, $matchweek, $matchDateTime, $availableTeams);
    }

    protected function getTeamsWithFixtures(Season $season, int $matchweek): Collection
    {
        $homeTeams = Fixture::where('season_id', $season->id)
            ->where('matchweek', $matchweek)
            ->pluck('home_team_id');
            
        $awayTeams = Fixture::where('season_id', $season->id)
            ->where('matchweek', $matchweek)
            ->pluck('away_team_id');

        return $homeTeams->merge($awayTeams)->unique();
    }

    protected function interactiveFixtureCreation(Season $season, int $matchweek, Carbon $matchDateTime, Collection $availableTeams): Collection
    {
        $fixtures = collect();
        $remainingTeams = $availableTeams->values();
        $fixtureCount = $remainingTeams->count() / 2;

        $this->info("🎯 Creating {$fixtureCount} fixtures manually...");
        $this->newLine();

        for ($i = 0; $i < $fixtureCount; $i++) {
            $this->info("--- Fixture " . ($i + 1) . " of {$fixtureCount} ---");
            
            // Show remaining teams
            $this->info("Available teams:");
            $remainingTeams->each(function ($team, $index) {
                $this->line("  " . ($index + 1) . ". {$team->name}");
            });
            $this->newLine();

            // Select home team
            $homeTeamChoices = $remainingTeams->pluck('name', 'id')->toArray();
            $homeTeamName = $this->choice('🏠 Select HOME team:', $homeTeamChoices);
            $homeTeam = $remainingTeams->firstWhere('name', $homeTeamName);
            
            // Remove home team from remaining teams
            $remainingTeams = $remainingTeams->reject(function ($team) use ($homeTeam) {
                return $team->id === $homeTeam->id;
            })->values();

            // Select away team
            $awayTeamChoices = $remainingTeams->pluck('name', 'id')->toArray();
            $awayTeamName = $this->choice('✈️  Select AWAY team:', $awayTeamChoices);
            $awayTeam = $remainingTeams->firstWhere('name', $awayTeamName);
            
            // Remove away team from remaining teams
            $remainingTeams = $remainingTeams->reject(function ($team) use ($awayTeam) {
                return $team->id === $awayTeam->id;
            })->values();

            // Get home team's stadium automatically
            $venue = $this->getHomeTeamStadium($homeTeam);

            // Get custom match time
            $customTime = $this->getCustomMatchTime($matchDateTime, $i + 1);

            // Show fixture summary and ask for confirmation
            $this->newLine();
            $this->info("📋 Fixture Summary:");
            $this->line("   🏠 Home Team: {$homeTeam->name}");
            $this->line("   ✈️  Away Team: {$awayTeam->name}");
            $this->line("   🏟️  Venue: {$venue->name}");
            $this->line("   📅 Date & Time: {$customTime->format('l, F j, Y \a\t g:i A')}");
            $this->line("   🗓️  Matchweek: {$matchweek}");
            $this->newLine();

            if ($this->confirm('💾 Save this fixture to database?', true)) {
                // Create the fixture
            $fixture = Fixture::create([
                'season_id' => $season->id,
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                    'venue_id' => $venue->id,
                    'match_datetime' => $customTime,
                'matchweek' => $matchweek,
                'status' => 'scheduled',
            ]);

            $fixtures->push($fixture);

                $this->info("✅ Fixture saved successfully!");
                $this->info("   {$homeTeam->name} vs {$awayTeam->name} at {$venue->name}");
            } else {
                $this->warn("❌ Fixture cancelled. Teams returned to available pool.");
                // Return teams back to available pool for next iteration
                $remainingTeams->push($homeTeam);
                $remainingTeams->push($awayTeam);
                $remainingTeams = $remainingTeams->values();
                // Decrement the counter to retry this fixture
                $i--;
            }

            $this->newLine();
            
            // Ask if user wants to continue creating more fixtures (if there are remaining teams)
            if ($remainingTeams->count() >= 2 && $i < $fixtureCount - 1) {
                if (!$this->confirm('Continue creating more fixtures?', true)) {
                    $this->info("🛑 Stopping fixture creation as requested.");
                    break;
                }
            }
        }

        return $fixtures;
    }

    protected function getHomeTeamStadium(Team $homeTeam): Venue
    {
        // Load the stadium relationship if not already loaded
        if (!$homeTeam->relationLoaded('stadium')) {
            $homeTeam->load('stadium');
        }

        // If team has a linked stadium, use it
        if ($homeTeam->stadium) {
            $this->info("🏟️  Using {$homeTeam->name}'s home stadium: {$homeTeam->stadium->name}");
            return $homeTeam->stadium;
        }

        // Fallback: try to find venue by stadium_name (for backward compatibility)
        if ($homeTeam->stadium_name) {
            $venue = Venue::where('name', $homeTeam->stadium_name)->first();
            if ($venue) {
                $this->warn("⚠️  Found venue by name matching. Consider linking {$homeTeam->name} to {$venue->name} properly.");
                return $venue;
            }
        }

        // Final fallback: use first available venue
        $fallbackVenue = Venue::where('is_active', true)->first();
        if ($fallbackVenue) {
            $this->error("❌ No stadium found for {$homeTeam->name}. Using fallback venue: {$fallbackVenue->name}");
            return $fallbackVenue;
        }

        // This should never happen if venues are properly seeded
        throw new \Exception("No venues available in the system. Please run venue seeder first.");
    }

    protected function getCustomMatchTime(Carbon $baseDateTime, int $fixtureNumber): Carbon
    {
        $suggestedTime = $baseDateTime->copy();
        
        // Suggest staggered times: first match at base time, then +2 hours each
        if ($fixtureNumber > 1) {
            $suggestedTime->addHours(($fixtureNumber - 1) * 2);
        }

        $this->info("💡 Suggested time: {$suggestedTime->format('l, F j, Y \a\t g:i A')}");
        
        if ($this->confirm('Use suggested time?', true)) {
            return $suggestedTime;
        }

        // Allow custom date input
        $customDate = $this->ask('📅 Enter custom date (YYYY-MM-DD) or press Enter for same date:', $suggestedTime->format('Y-m-d'));
        $customTime = $this->ask('⏰ Enter custom time (HH:MM):', $suggestedTime->format('H:i'));

        try {
            $matchDate = Carbon::createFromFormat('Y-m-d', $customDate);
            $finalDateTime = $matchDate->setTimeFromTimeString($customTime);
            return $finalDateTime;
        } catch (\Exception $e) {
            $this->error('❌ Invalid date/time format. Using suggested time.');
            return $suggestedTime;
        }
    }

    protected function displayCreatedFixtures(Collection $fixtures): void
    {
        $this->info('📋 Created Fixtures:');
        $this->line('==================');

        $table = [];
        foreach ($fixtures as $fixture) {
            $table[] = [
                $fixture->match_datetime->format('D, M j'),
                $fixture->match_datetime->format('g:i A'),
                $fixture->homeTeam->name,
                'vs',
                $fixture->awayTeam->name,
                $fixture->venue->name ?? 'TBD'
            ];
        }

        $this->table(
            ['Date', 'Time', 'Home', '', 'Away', 'Venue'],
            $table
        );
    }
}