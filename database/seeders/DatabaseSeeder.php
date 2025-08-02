<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            VenueSeeder::class, // Venues first
            TeamSeeder::class, // Then Teams
            SeasonSeeder::class, // Then Seasons
            FixtureSeeder::class, // Fixtures last, as it depends on the others
        ]);
    }
}
