<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('situations', function (Blueprint $table) {
            $table->string('required_customization_key')->nullable()->after('position');
            $table->index('required_customization_key');
        });
    }

    public function down(): void
    {
        Schema::table('situations', function (Blueprint $table) {
            $table->dropIndex(['required_customization_key']);
            $table->dropColumn('required_customization_key');
        });
    }
};

