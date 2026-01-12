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

            // Hourly price
            $table->decimal('price_per_hour', 10, 2)->nullable()->default(0);

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
