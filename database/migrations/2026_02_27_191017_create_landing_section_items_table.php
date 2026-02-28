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
        Schema::create('landing_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_section_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->json('content')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('link')->nullable();
            $table->string('link_text')->nullable();
            $table->string('background_color')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_section_items');
    }
};
