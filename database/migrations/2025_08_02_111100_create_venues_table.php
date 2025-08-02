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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_fa')->nullable();
            $table->string('city');
            $table->string('city_fa')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('address')->nullable();
            $table->string('address_fa')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('slug')->unique();
            $table->integer('api_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
