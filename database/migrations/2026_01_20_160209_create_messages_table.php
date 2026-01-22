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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->enum('type', ['text', 'attachment', 'mixed'])->default('text');
            $table->enum('context', ['in_session', 'out_of_session']);
            $table->softDeletes();
            $table->timestamps();

            // Composite index on (conversation_id, created_at) for efficient message listing
            $table->index(['conversation_id', 'created_at']);

            // Composite index on (conversation_id, sender_id, context) for message limit enforcement
            $table->index(['conversation_id', 'sender_id', 'context']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
