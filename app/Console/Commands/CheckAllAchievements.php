<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AchievementService;
use App\Models\User;

class CheckAllAchievements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'achievements:check-all {--user-id= : Check achievements for specific user ID}';

    /**
     * The console command description.
     */
    protected $description = 'Check and award achievements for all users or specific user';

    protected $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        parent::__construct();
        $this->achievementService = $achievementService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            return $this->checkSingleUser($userId);
        }

        return $this->checkAllUsers();
    }

    /**
     * Check achievements for a single user.
     */
    private function checkSingleUser(int $userId)
    {
        $this->info("Checking achievements for user ID: {$userId}");
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $awardedBadges = $this->achievementService->checkUserAchievements($user, 'manual_check');
        
        if (empty($awardedBadges)) {
            $this->info("✅ No new badges awarded for user: {$user->name}");
        } else {
            $this->info("🏆 Awarded " . count($awardedBadges) . " badge(s) to user: {$user->name}");
            foreach ($awardedBadges as $badgeKey) {
                $this->line("  - {$badgeKey}");
            }
        }

        return 0;
    }

    /**
     * Check achievements for all users.
     */
    private function checkAllUsers()
    {
        $this->info('Checking achievements for all users...');
        
        $results = $this->achievementService->bulkCheckAchievements('bulk_manual_check');
        
        $totalUsers = count($results);
        $totalBadges = array_sum(array_map('count', $results));
        
        $this->info("✅ Completed checking all users!");
        $this->info("📊 Stats:");
        $this->info("  - Users who received badges: {$totalUsers}");
        $this->info("  - Total badges awarded: {$totalBadges}");
        
        if ($totalBadges > 0) {
            $this->info("\n🏆 Badge Awards:");
            foreach ($results as $userId => $badges) {
                $user = User::find($userId);
                $this->line("  {$user->name}: " . implode(', ', $badges));
            }
        }

        return 0;
    }
}
