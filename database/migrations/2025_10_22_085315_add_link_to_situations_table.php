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
            $table->string('link', 500)->nullable()->after('required_customization_key');
        });
    }

    public function down(): void
    {
        Schema::table('situations', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
};
