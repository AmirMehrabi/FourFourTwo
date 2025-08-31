<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Badge;

class SyncBadges extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'badges:sync';

    /**
     * The console command description.
     */
    protected $description = 'Sync badge definitions from config to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing badges from config...');

        try {
            Badge::syncFromConfig();
            $this->info('✅ Badges synced successfully!');
            
            $badgeCount = Badge::active()->count();
            $this->info("📊 Total active badges: {$badgeCount}");
            
        } catch (\Exception $e) {
            $this->error("❌ Error syncing badges: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
