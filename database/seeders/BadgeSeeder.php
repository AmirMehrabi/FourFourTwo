<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding badges from config...');
        
        Badge::syncFromConfig();
        
        $badgeCount = Badge::count();
        $this->command->info("✅ Seeded {$badgeCount} badges successfully!");
    }
}
