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
        Schema::table('player_profiles', function (Blueprint $table) {
            $table->string('favorite_song')->nullable()->after('consecutive_days');
            $table->string('favorite_movie')->nullable()->after('favorite_song');
            $table->string('favorite_book')->nullable()->after('favorite_movie');
            $table->string('favorite_dish')->nullable()->after('favorite_book');
            $table->string('best_friend_name')->nullable()->after('favorite_dish');
        });
    }

    public function down(): void
    {
        Schema::table('player_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'favorite_song',
                'favorite_movie',
                'favorite_book',
                'favorite_dish',
                'best_friend_name'
            ]);
        });
    }
};
