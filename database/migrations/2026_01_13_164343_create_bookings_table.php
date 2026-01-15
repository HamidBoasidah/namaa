<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Parties
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('consultant_id')->constrained('consultants')->cascadeOnDelete();

            /**
             * What is being booked (polymorphic):
             * - ConsultantService (fixed duration)
             * - Consultant (hourly / variable duration)
             */
            $table->morphs('bookable'); // bookable_type, bookable_id

            // Time (store consistently; app treats as KSA single TZ)
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->unsignedSmallInteger('duration_minutes'); // multiples of 5

            // Buffer after the session (snapshot at booking creation, multiples of 5)
            $table->unsignedSmallInteger('buffer_after_minutes')->default(0);

            // Status & expiration for pending holds (15 minutes)
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'expired'])->default('pending');
            $table->dateTime('expires_at')->nullable();

            // Cancellation (polymorphic canceller: User or Admin)
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancel_reason', 500)->nullable();
            $table->nullableMorphs('cancelled_by'); // cancelled_by_type, cancelled_by_id

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for availability queries
            $table->index(['consultant_id', 'start_at']);
            $table->index(['consultant_id', 'end_at']);
            $table->index(['consultant_id', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index(['client_id', 'status']);
            // Note: morphs() already creates index on bookable_type, bookable_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
