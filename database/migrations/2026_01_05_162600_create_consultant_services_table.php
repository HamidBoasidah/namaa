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
            Schema::create('consultant_services', function (Blueprint $table) {
                $table->id();

                $table->foreignId('consultant_id')
                    ->constrained('consultants')
                    ->cascadeOnDelete();

                // optional category for the service
                $table->foreignId('category_id')
                    ->nullable()
                    ->constrained('categories')
                    ->nullOnDelete();

                $table->string('title', 255);
                $table->text('description')->nullable();

                $table->decimal('price', 10, 2);

                $table->unsignedSmallInteger('duration_minutes')->default(60);

                $table->boolean('is_active')->default(true);

                $table->softDeletes();
                $table->timestamps();

                $table->unique(['consultant_id', 'title', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultant_services');
    }
};
