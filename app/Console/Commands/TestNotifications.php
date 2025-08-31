<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Badge;
use App\Models\Notification;
use App\Events\UserFollowed;
use Illuminate\Console\Command;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:test {--type=all : Type of notification to test (badge|follow|all)}';

    /**
     * The console command description.
     */
    protected $description = 'Test notification system for badges and follows';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        
        $this->info('🔔 Testing Notification System...');
        
        if ($type === 'badge' || $type === 'all') {
            $this->testBadgeNotifications();
        }
        
        if ($type === 'follow' || $type === 'all') {
            $this->testFollowNotifications();
        }
        
        $this->info('✅ Notification tests completed!');
        
        return 0;
    }
    
    /**
     * Test badge notifications.
     */
    private function testBadgeNotifications()
    {
        $this->info('🏆 Testing Badge Notifications...');
        
        // Get or create a test user
        $user = User::firstOrCreate(
            ['username' => 'notif_test_user'],
            [
                'name' => 'Notification Test User',
                'email' => 'test@notifications.com',
                'password' => bcrypt('password')
            ]
        );
        
        // Sync badges first
        Badge::syncFromConfig();
        
        // Get initial notification count
        $initialCount = $user->notifications()->where('type', 'badge_awarded')->count();
        
        // Award a test badge
        $success = $user->awardBadge('first_prediction', [
            'trigger' => 'test_command',
            'timestamp' => now()
        ]);
        
        if ($success) {
            $newCount = $user->notifications()->where('type', 'badge_awarded')->count();
            
            if ($newCount > $initialCount) {
                $this->info("  ✅ Badge notification created successfully!");
                
                // Show notification details
                $notification = $user->notifications()->where('type', 'badge_awarded')->latest()->first();
                $this->line("  📋 Notification Data:");
                $this->line("     Badge: " . $notification->data['badge_name']);
                $this->line("     Tier: " . $notification->data['badge_tier']);
                $this->line("     Description: " . $notification->data['badge_description']);
            } else {
                $this->error("  ❌ Badge notification was not created!");
            }
        } else {
            $this->warn("  ⚠️  Badge was not awarded (user may already have it)");
        }
    }
    
    /**
     * Test follow notifications.
     */
    private function testFollowNotifications()
    {
        $this->info('👤 Testing Follow Notifications...');
        
        // Get or create test users
        $follower = User::firstOrCreate(
            ['username' => 'test_follower'],
            [
                'name' => 'Test Follower',
                'email' => 'follower@test.com',
                'password' => bcrypt('password')
            ]
        );
        
        $followed = User::firstOrCreate(
            ['username' => 'test_followed'],
            [
                'name' => 'Test Followed',
                'email' => 'followed@test.com',
                'password' => bcrypt('password')
            ]
        );
        
        // Get initial notification count
        $initialCount = $followed->notifications()->where('type', 'new_follower')->count();
        
        // Check if already following
        if (!$follower->isFollowing($followed)) {
            // Follow the user
            $follower->following()->attach($followed->id);
            
            // Create notification
            Notification::createFollowerNotification($followed->id, $follower);
            
            // Dispatch event
            UserFollowed::dispatch($follower, $followed);
            
            $newCount = $followed->notifications()->where('type', 'new_follower')->count();
            
            if ($newCount > $initialCount) {
                $this->info("  ✅ Follow notification created successfully!");
                
                // Show notification details
                $notification = $followed->notifications()->where('type', 'new_follower')->latest()->first();
                $this->line("  📋 Notification Data:");
                $this->line("     Follower: " . $notification->data['follower_name']);
                $this->line("     Username: @" . $notification->data['follower_username']);
                $this->line("     Predictions: " . $notification->data['follower_stats']['predictions_count']);
            } else {
                $this->error("  ❌ Follow notification was not created!");
            }
        } else {
            $this->warn("  ⚠️  Users are already following each other");
        }
    }
}
