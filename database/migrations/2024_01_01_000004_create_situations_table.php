<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('situations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['work', 'study', 'personal', 'health']);
            $table->integer('difficulty_level')->default(1);
            $table->integer('min_level_required')->default(1);
            $table->integer('stress_impact');
            $table->integer('experience_reward');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('difficulty_level');
            $table->index('min_level_required');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('situations');
    }
};
