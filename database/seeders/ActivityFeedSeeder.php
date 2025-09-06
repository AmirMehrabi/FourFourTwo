<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Badge;
use App\Models\ActivityFeed;
use App\Models\Prediction;
use App\Models\Fixture;

class ActivityFeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::take(10)->get();
        $fixtures = Fixture::with(['homeTeam', 'awayTeam'])->take(20)->get();
        
        // Create some sample badges first
        Badge::syncFromConfig();
        $badges = Badge::take(5)->get();
        
        foreach ($users as $user) {
            // Create some prediction activities
            for ($i = 0; $i < rand(2, 5); $i++) {
                $fixture = $fixtures->random();
                
                $prediction = Prediction::create([
                    'user_id' => $user->id,
                    'fixture_id' => $fixture->id,
                    'home_score_predicted' => rand(0, 4),
                    'away_score_predicted' => rand(0, 4),
                ]);
                
                ActivityFeed::create([
                    'user_id' => $user->id,
                    'activity_type' => 'prediction',
                    'data' => [
                        'prediction_id' => $prediction->id,
                        'fixture_id' => $fixture->id,
                        'home_score_predicted' => $prediction->home_score_predicted,
                        'away_score_predicted' => $prediction->away_score_predicted,
                        'fixture' => [
                            'home_team' => $fixture->homeTeam->name,
                            'away_team' => $fixture->awayTeam->name,
                            'match_datetime' => $fixture->match_datetime,
                        ],
                    ],
                    'activity_date' => now()->subHours(rand(1, 72)),
                    'is_public' => true,
                ]);
            }
            
            // Create some badge activities
            if (rand(1, 3) === 1) {
                $badge = $badges->random();
                
                ActivityFeed::create([
                    'user_id' => $user->id,
                    'activity_type' => 'badge_earned',
                    'data' => [
                        'badge_id' => $badge->id,
                        'badge_key' => $badge->key,
                        'badge_name' => $badge->name,
                        'badge_description' => $badge->description,
                        'badge_icon' => $badge->icon,
                        'badge_tier' => $badge->tier,
                        'badge_category' => $badge->category,
                        'context' => ['trigger' => 'seeder'],
                    ],
                    'activity_date' => now()->subHours(rand(1, 48)),
                    'is_public' => true,
                ]);
            }
            
            // Create some follow activities
            if (rand(1, 4) === 1) {
                $followedUser = $users->where('id', '!=', $user->id)->random();
                
                ActivityFeed::create([
                    'user_id' => $user->id,
                    'activity_type' => 'follow',
                    'data' => [
                        'following_user_id' => $followedUser->id,
                        'following_user_name' => $followedUser->name,
                        'following_user_username' => $followedUser->username,
                        'following_user_avatar' => $followedUser->avatar,
                    ],
                    'activity_date' => now()->subHours(rand(1, 24)),
                    'is_public' => true,
                ]);
            }
        }
        
        $this->command->info('Activity feed seeded with sample data!');
    }
}