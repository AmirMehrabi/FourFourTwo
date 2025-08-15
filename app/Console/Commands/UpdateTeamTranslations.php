<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Team;

class UpdateTeamTranslations extends Command
{
    protected $signature = 'teams:update-translations';
    protected $description = 'Update team names with Farsi translations';

    public function handle()
    {
        $translations = Team::getTeamTranslations();
        $updated = 0;

        foreach ($translations as $englishName => $farsiName) {
            $team = Team::where('name', $englishName)->first();
            
            if ($team) {
                $team->update(['name_fa' => $farsiName]);
                $this->info("Updated: {$englishName} -> {$farsiName}");
                $updated++;
            } else {
                $this->warn("Team not found: {$englishName}");
            }
        }

        $this->info("Updated {$updated} teams with Farsi translations");
        return 0;
    }
}
