<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_holidays', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consultant_id')
                ->constrained('consultants')
                ->cascadeOnDelete();

            $table->date('holiday_date');
            $table->string('name', 150)->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['consultant_id', 'holiday_date', 'deleted_at'], 'uq_consultant_holiday_date');
            $table->index(['consultant_id', 'holiday_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_holidays');
    }
};
