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
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();

            // Add the link to the seasons table
            $table->foreignId('season_id')->constrained('seasons');

            // Foreign keys for the two teams
            $table->foreignId('home_team_id')->constrained('teams');
            $table->foreignId('away_team_id')->constrained('teams');

            // Match Time & Round
            $table->dateTime('match_datetime');
            $table->unsignedTinyInteger('matchweek')->comment('The match week number, e.g., 1, 2, 3...');

            // Status of the match (e.g., scheduled, in_play, finished, postponed, cancelled)
            $table->string('status', 50)->default('scheduled');

            // Venue Information
            $table->string('venue_id')->nullable();

            // Final scores (nullable as they are unknown before the match ends)
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->string('score_details')->nullable()->comment('e.g., (AET), (P)'); // Extra time, penalties

            // System & API Fields
            $table->integer('api_id')->nullable()->comment('External API reference ID for this fixture');
            $table->boolean('is_active')->default(true)->comment('To easily toggle visibility');

            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'match_datetime']);
            $table->index('api_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
