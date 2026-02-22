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
        Schema::create('consultants', function (Blueprint $table) {
            $table->id();

            // Relation with users table
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Relation with consultation_types table
            $table->foreignId('consultation_type_id')
                ->constrained('consultation_types')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('years_of_experience')->nullable();

            // Ratings
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('ratings_count')->default(0);

            // Price
            $table->decimal('price', 10, 2)->nullable()->default(0);

            // Default duration for consultations (minutes)
            $table->unsignedSmallInteger('duration_minutes')->default(60);

            // buffer time in minutes for consultant (e.g., prep/cleanup)
            $table->unsignedSmallInteger('buffer')->default(0);

            // Preferred consultation method for direct consultant bookings
            $table->enum('consultation_method', ['video', 'audio', 'text'])->default('video');

            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();

            // Constraints & Indexes
            $table->unique(['user_id', 'deleted_at']);
            $table->index('rating_avg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultants');
    }
};
