<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('micro_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('energy_reward');
            $table->integer('experience_reward');
            $table->integer('cooldown_minutes')->default(0);
            $table->integer('unlock_level')->default(1);
            $table->enum('category', ['relaxation', 'exercise', 'creativity', 'social']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('unlock_level');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('micro_actions');
    }
};
