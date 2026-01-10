<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consultant_id')
                ->constrained('consultants')
                ->cascadeOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('rejected_reason')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('document_scan_copy');
            $table->string('document_scan_copy_original_name', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Unique per consultant for active records only (deleted_at NULL). Allow multiple
            // historical records once soft-deleted by including deleted_at in index.
            $table->unique(['consultant_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
