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
        Schema::table('situations', function (Blueprint $table) {
            $table->string('article_title', 255)->nullable()->after('link');
        });
    }

    public function down(): void
    {
        Schema::table('situations', function (Blueprint $table) {
            $table->dropColumn('article_title');
        });
    }
};
