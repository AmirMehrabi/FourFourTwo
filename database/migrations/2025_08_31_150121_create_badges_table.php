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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Config key identifier
            $table->string('name');
            $table->text('description');
            $table->string('icon');
            $table->enum('tier', ['bronze', 'silver', 'gold', 'diamond']);
            $table->enum('category', ['milestone', 'social', 'profile', 'participation', 'timing', 'accuracy', 'streak', 'consistency', 'ranking', 'perfection', 'championship', 'mastery', 'special']);
            $table->json('criteria'); // Store badge requirements
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['tier', 'category']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
