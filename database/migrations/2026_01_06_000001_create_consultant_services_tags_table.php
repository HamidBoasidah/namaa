<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consultant_services_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultant_service_id')->constrained('consultant_services')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['consultant_service_id', 'tag_id'], 'consultant_service_tag_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_services_tags');
    }
};
