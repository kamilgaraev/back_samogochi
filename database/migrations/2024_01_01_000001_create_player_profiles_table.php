<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('level')->default(1);
            $table->integer('total_experience')->default(0);
            $table->integer('energy')->default(100);
            $table->integer('stress')->default(50);
            $table->integer('anxiety')->default(30);
            $table->timestamp('last_login')->nullable();
            $table->timestamp('last_daily_reward')->nullable();
            $table->integer('consecutive_days')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_profiles');
    }
};
