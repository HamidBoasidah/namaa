<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_service_details', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('consultant_service_id')
                ->constrained('consultant_services')
                ->cascadeOnDelete();
            
            // النوع: includes (ماذا تشمل), target_audience (لمن هذه الخدمة), deliverables (ما الذي يستلمه العميل)
            $table->enum('type', ['includes', 'target_audience', 'deliverables']);
            
            $table->string('content');
            
            $table->unsignedSmallInteger('sort_order')->default(0);
            
            $table->timestamps();
            
            $table->index(['consultant_service_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_service_details');
    }
};
