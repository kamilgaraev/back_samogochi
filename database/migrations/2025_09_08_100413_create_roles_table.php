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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();               // super-admin, admin, moderator
            $table->string('display_name');                 // Super Administrator
            $table->text('description')->nullable();         // Role description
            $table->boolean('is_active')->default(true);    // Can be disabled
            $table->integer('priority')->default(0);        // Higher number = higher priority
            $table->json('metadata')->nullable();           // Additional role data
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['name', 'is_active']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
