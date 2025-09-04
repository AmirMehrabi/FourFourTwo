<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Modify the existing enum column
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
                'comment_reply',
                'comment_mention', 
                'comment_reaction',
                'friend_request',
                'match_update',
                'badge_awarded',
                'new_follower',
                'mention'
            ) NOT NULL");
        } else {
            // SQLite/PostgreSQL: Drop indexes first, then column, then recreate
            Schema::table('notifications', function (Blueprint $table) {
                // Drop indexes that reference the type column
                $table->dropIndex(['user_id', 'type']);
            });
            
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('type');
            });
            
            Schema::table('notifications', function (Blueprint $table) {
                $table->string('type', 50)->after('user_id');
                // Recreate the index
                $table->index(['user_id', 'type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Revert back to original enum values
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
                'comment_reply',
                'comment_mention',
                'comment_reaction', 
                'friend_request',
                'match_update'
            ) NOT NULL");
        } else {
            // SQLite/PostgreSQL: Drop indexes, drop column, recreate as enum-like
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'type']);
            });
            
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('type');
            });
            
            Schema::table('notifications', function (Blueprint $table) {
                $table->string('type', 50)->after('user_id');
                $table->index(['user_id', 'type']);
            });
        }
    }
};
