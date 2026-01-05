<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_working_hours', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consultant_id')
                ->constrained('consultants')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('day_of_week');
            // store time as HH:MM (string length 5) to avoid storing seconds
            $table->string('start_time', 5);
            $table->string('end_time', 5);

            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['consultant_id', 'day_of_week']);

            // ✅ يمنع إدخال نفس الفترة مرتين
            $table->unique(
                ['consultant_id', 'day_of_week', 'start_time', 'end_time' , 'deleted_at'],
                'uq_consultant_day_start_end'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_working_hours');
    }
};