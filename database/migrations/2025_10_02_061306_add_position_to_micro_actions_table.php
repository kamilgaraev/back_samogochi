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
            $table->string('position')->default('desktop')->after('is_active');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::table('micro_actions', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn('position');
        });
    }
};
