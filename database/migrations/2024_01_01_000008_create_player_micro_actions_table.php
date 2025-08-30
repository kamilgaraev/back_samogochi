<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_micro_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('player_profiles')->onDelete('cascade');
            $table->foreignId('micro_action_id')->constrained()->onDelete('cascade');
            $table->timestamp('completed_at');
            $table->integer('energy_gained');
            $table->integer('experience_gained');
            $table->timestamps();

            $table->index(['player_id', 'completed_at']);
            $table->index('micro_action_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_micro_actions');
    }
};
