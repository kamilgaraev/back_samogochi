<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('situation_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('situation_id')->constrained()->onDelete('cascade');
            $table->text('text');
            $table->integer('stress_change');
            $table->integer('experience_reward');
            $table->integer('energy_cost')->default(0);
            $table->integer('min_level_required')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['situation_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('situation_options');
    }
};
