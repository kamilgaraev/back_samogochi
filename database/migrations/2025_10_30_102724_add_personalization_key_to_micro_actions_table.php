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
        Schema::table('micro_actions', function (Blueprint $table) {
            $table->string('personalization_key')->nullable()->after('position');
            $table->index('personalization_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('micro_actions', function (Blueprint $table) {
            $table->dropIndex(['personalization_key']);
            $table->dropColumn('personalization_key');
        });
    }
};
