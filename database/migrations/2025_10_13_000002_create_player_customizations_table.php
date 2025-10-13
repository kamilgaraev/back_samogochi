<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_customizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('player_profiles')->onDelete('cascade');
            $table->string('category_key');
            $table->foreignId('selected_item_id')->nullable()->constrained('customization_items')->onDelete('set null');
            $table->json('unlocked_items')->nullable();
            $table->json('new_unlocked_items')->nullable();
            $table->timestamps();

            $table->unique(['player_id', 'category_key']);
            $table->index('player_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_customizations');
    }
};

