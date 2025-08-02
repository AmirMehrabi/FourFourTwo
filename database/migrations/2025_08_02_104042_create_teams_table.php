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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_fa')->nullable(); // Farsi name
            $table->string('short_name', 10)->nullable();
            $table->string('short_name_fa', 10)->nullable(); // Farsi short name
            $table->string('logo_url')->nullable();
            $table->string('slug')->unique(); // URL-friendly identifier
            $table->string('primary_color', 7)->nullable(); // Hex color code
            $table->string('secondary_color', 7)->nullable(); // Hex color code
            $table->string('founded_year', 4)->nullable();
            $table->string('stadium_name')->nullable();
            $table->string('stadium_name_fa')->nullable(); // Farsi stadium name
            $table->integer('stadium_capacity')->nullable();
            $table->string('city')->nullable();
            $table->string('city_fa')->nullable(); // Farsi city name
            $table->string('manager')->nullable();
            $table->string('manager_fa')->nullable(); // Farsi manager name
            $table->string('website_url')->nullable();
            $table->integer('api_id')->nullable(); // External API reference
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active', 'name']);
            $table->index('api_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
