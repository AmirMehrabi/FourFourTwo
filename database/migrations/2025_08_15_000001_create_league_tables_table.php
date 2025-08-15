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
        Schema::create('league_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons');
            $table->foreignId('team_id')->constrained('teams');
            
            // League position
            $table->unsignedTinyInteger('position')->nullable();
            
            // Match statistics
            $table->unsignedTinyInteger('played')->default(0);
            $table->unsignedTinyInteger('won')->default(0);
            $table->unsignedTinyInteger('drawn')->default(0);
            $table->unsignedTinyInteger('lost')->default(0);
            
            // Goal statistics
            $table->unsignedSmallInteger('goals_for')->default(0);
            $table->unsignedSmallInteger('goals_against')->default(0);
            $table->smallInteger('goal_difference')->default(0);
            
            // Points
            $table->unsignedTinyInteger('points')->default(0);
            
            // Form (last 5 games)
            $table->string('form', 5)->nullable(); // e.g., "WDLWW"
            
            $table->timestamps();
            
            // Unique constraint and indexes
            $table->unique(['season_id', 'team_id']);
            $table->index(['season_id', 'position']);
            $table->index(['season_id', 'points', 'goal_difference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_tables');
    }
};
