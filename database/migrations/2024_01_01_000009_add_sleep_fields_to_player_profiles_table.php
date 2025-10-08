<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_profiles', function (Blueprint $table) {
            $table->integer('completed_situations_since_sleep')->default(0)->after('consecutive_days');
            $table->timestamp('sleeping_until')->nullable()->after('completed_situations_since_sleep');
        });
    }

    public function down(): void
    {
        Schema::table('player_profiles', function (Blueprint $table) {
            $table->dropColumn(['completed_situations_since_sleep', 'sleeping_until']);
        });
    }
};

