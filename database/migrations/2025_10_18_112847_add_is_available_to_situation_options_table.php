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
        Schema::table('situation_options', function (Blueprint $table) {
            $table->boolean('is_available')->default(true)->after('min_level_required');
        });
    }

    public function down(): void
    {
        Schema::table('situation_options', function (Blueprint $table) {
            $table->dropColumn('is_available');
        });
    }
};
