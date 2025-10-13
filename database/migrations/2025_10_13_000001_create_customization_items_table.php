<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customization_items', function (Blueprint $table) {
            $table->id();
            $table->string('category_key')->index();
            $table->string('category');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('unlock_level')->default(1);
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_key', 'unlock_level']);
            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customization_items');
    }
};

