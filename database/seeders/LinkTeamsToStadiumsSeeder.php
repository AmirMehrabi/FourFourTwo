<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Venue;
use Illuminate\Support\Str;

class LinkTeamsToStadiumsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔗 Linking existing teams to their stadiums...');

        // Team to stadium mapping
        $teamStadiumMap = [
            'Arsenal' => 'Emirates Stadium',
            'Aston Villa' => 'Villa Park',
            'Bournemouth' => 'Dean Court',
            'Brentford' => 'Brentford Community Stadium',
            'Brighton & Hove Albion' => 'Falmer Stadium',
            'Burnley' => 'Turf Moor',
            'Chelsea' => 'Stamford Bridge',
            'Crystal Palace' => 'Selhurst Park',
            'Everton' => 'Everton Stadium',
            'Fulham' => 'Craven Cottage',
            'Leeds United' => 'Elland Road',
            'Liverpool' => 'Anfield',
            'Manchester City' => 'Etihad Stadium',
            'Manchester United' => 'Old Trafford',
            'Newcastle United' => 'St James\' Park',
            'Nottingham Forest' => 'City Ground',
            'Sunderland' => 'Stadium of Light',
            'Tottenham Hotspur' => 'Tottenham Hotspur Stadium',
            'West Ham United' => 'London Stadium',
            'Wolverhampton Wanderers' => 'Molineux Stadium',
        ];

        $linkedCount = 0;
        $createdVenues = 0;
        $notFoundTeams = [];

        foreach ($teamStadiumMap as $teamName => $stadiumName) {
            // Find the team
            $team = Team::where('name', $teamName)->first();
            
            if (!$team) {
                $notFoundTeams[] = $teamName;
                continue;
            }

            // Find or create the venue
            $venue = Venue::where('name', $stadiumName)->first();
            
            if (!$venue) {
                // Create venue if it doesn't exist
                $venue = Venue::create([
                    'name' => $stadiumName,
                    'slug' => Str::slug($stadiumName),
                    'city' => $team->city ?? 'Unknown',
                    'capacity' => $team->stadium_capacity,
                    'is_active' => true,
                ]);
                $createdVenues++;
                $this->command->info("✨ Created venue: {$stadiumName}");
            }

            // Update team with stadium information
            $team->update([
                'stadium_id' => $venue->id,
                'stadium_name' => $stadiumName,
            ]);

            $linkedCount++;
            $this->command->info("🔗 Linked {$teamName} to {$stadiumName}");
        }

        // Summary
        $this->command->newLine();
        $this->command->info("✅ Stadium linking completed!");
        $this->command->info("   📊 Teams linked: {$linkedCount}");
        $this->command->info("   🏟️  Venues created: {$createdVenues}");
        
        if (!empty($notFoundTeams)) {
            $this->command->warn("⚠️  Teams not found: " . implode(', ', $notFoundTeams));
        }
    }
}
