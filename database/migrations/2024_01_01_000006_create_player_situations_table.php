<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_situations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('player_profiles')->onDelete('cascade');
            $table->foreignId('situation_id')->constrained()->onDelete('cascade');
            $table->foreignId('selected_option_id')->nullable()->constrained('situation_options')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['player_id', 'completed_at']);
            $table->index('situation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_situations');
    }
};
