<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_type'); // prediction, badge_earned, comment, follow
            $table->json('data'); // Activity-specific data
            $table->timestamp('activity_date');
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'activity_date']);
            $table->index(['activity_type', 'activity_date']);
            $table->index(['is_public', 'activity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_feeds');
    }
};
