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
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();

            // Foreign keys to users and fixtures
            // onDelete('cascade') means if a user or fixture is deleted, their predictions are also deleted.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('fixture_id')->constrained()->onDelete('cascade');

            // The user's predicted score
            $table->unsignedTinyInteger('home_score_predicted');
            $table->unsignedTinyInteger('away_score_predicted');

            // This will be calculated later when you process the results
            $table->unsignedTinyInteger('points_awarded')->nullable();

            $table->timestamps();

            // This ensures a user can only predict on a specific match once.
            $table->unique(['user_id', 'fixture_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
