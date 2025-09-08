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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();               // users.view, users.create, etc.
            $table->string('display_name');                 // View Users
            $table->text('description')->nullable();         // Permission description
            $table->string('category')->default('general'); // users, situations, configs, etc.
            $table->boolean('is_active')->default(true);    // Can be disabled
            $table->json('metadata')->nullable();           // Additional permission data
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['name', 'is_active']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
