<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // Core relations
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('consultant_id')->constrained('consultants')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();

            // Review content
            $table->unsignedTinyInteger('rating'); // 1..5 (enforce via validation / optional DB check)
            $table->text('comment')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // One review per booking
            $table->unique(['booking_id']);

            // Fast lookup for profile pages
            $table->index(['consultant_id', 'created_at']);
            $table->index(['client_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};