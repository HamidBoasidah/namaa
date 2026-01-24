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
        // Add foreign key for read marker to conversation_participants
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->foreign('last_read_message_id')
                  ->references('id')
                  ->on('messages')
                  ->onDelete('set null');
        });
        
        // Add composite index to messages table for efficient unread queries
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'id'], 'idx_messages_conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key from conversation_participants
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropForeign(['last_read_message_id']);
        });
        
        // Drop composite index from messages table
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_messages_conversation_id');
        });
    }
};
