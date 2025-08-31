or issu<?php

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
            // SQLite/PostgreSQL: Drop and recreate the column
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('type');
            });
            
            Schema::table('notifications', function (Blueprint $table) {
                $table->string('type', 50)->after('user_id');
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
            // SQLite/PostgreSQL: Just keep the string column (no easy way to revert)
            // The column will remain as string type
        }
    }
};
