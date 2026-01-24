<?php

/**
 * Script to check query performance using EXPLAIN
 * This helps verify that indexes are being used correctly
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Consultant;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;

echo "=== Query Performance Analysis for Chat Read State ===\n\n";

// Create test data
echo "Creating test data...\n";
$client = User::factory()->create(['user_type' => 'customer']);
$consultantUser = User::factory()->create(['user_type' => 'consultant']);
$consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);

$booking = Booking::factory()->create([
    'client_id' => $client->id,
    'consultant_id' => $consultant->id,
    'bookable_type' => Consultant::class,
    'bookable_id' => $consultant->id,
    'status' => Booking::STATUS_CONFIRMED,
]);

$conversation = Conversation::factory()->create([
    'booking_id' => $booking->id,
]);

ConversationParticipant::factory()->create([
    'conversation_id' => $conversation->id,
    'user_id' => $client->id,
    'last_read_message_id' => null,
]);

ConversationParticipant::factory()->create([
    'conversation_id' => $conversation->id,
    'user_id' => $consultantUser->id,
    'last_read_message_id' => null,
]);

// Create some messages
for ($i = 0; $i < 10; $i++) {
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'sender_id' => $consultantUser->id,
        'body' => "Test message {$i}",
        'type' => 'text',
        'context' => 'in_session',
    ]);
}

echo "Test data created.\n\n";

// Query 1: Get unread count for a specific conversation
echo "=== Query 1: Get Unread Count (Single Conversation) ===\n";
$query1 = "
    SELECT COUNT(*) as count
    FROM messages
    WHERE conversation_id = {$conversation->id}
      AND sender_id != {$client->id}
      AND id > COALESCE((
          SELECT last_read_message_id 
          FROM conversation_participants 
          WHERE conversation_id = {$conversation->id} 
            AND user_id = {$client->id}
      ), 0)
";

echo "Query:\n{$query1}\n\n";
echo "EXPLAIN:\n";
$explain1 = DB::select("EXPLAIN {$query1}");
foreach ($explain1 as $row) {
    print_r($row);
}
echo "\n";

// Query 2: Get conversations with unread counts
echo "=== Query 2: Get Conversations with Unread Counts ===\n";
$query2 = "
    SELECT 
        c.id,
        c.booking_id,
        c.created_at,
        c.updated_at,
        COUNT(DISTINCT unread_msg.id) as unread_count
    FROM conversations as c
    INNER JOIN conversation_participants as cp ON c.id = cp.conversation_id
    LEFT JOIN messages as unread_msg ON c.id = unread_msg.conversation_id
        AND unread_msg.sender_id != {$client->id}
        AND unread_msg.id > COALESCE(cp.last_read_message_id, 0)
    WHERE cp.user_id = {$client->id}
      AND c.deleted_at IS NULL
    GROUP BY c.id, c.booking_id, c.created_at, c.updated_at
    ORDER BY c.updated_at DESC
";

echo "Query:\n{$query2}\n\n";
echo "EXPLAIN:\n";
$explain2 = DB::select("EXPLAIN {$query2}");
foreach ($explain2 as $row) {
    print_r($row);
}
echo "\n";

// Check indexes
echo "=== Index Information ===\n\n";

echo "Indexes on 'messages' table:\n";
$messageIndexes = DB::select("SHOW INDEX FROM messages");
foreach ($messageIndexes as $index) {
    echo "  - {$index->Key_name} on column {$index->Column_name}\n";
}
echo "\n";

echo "Indexes on 'conversation_participants' table:\n";
$participantIndexes = DB::select("SHOW INDEX FROM conversation_participants");
foreach ($participantIndexes as $index) {
    echo "  - {$index->Key_name} on column {$index->Column_name}\n";
}
echo "\n";

echo "Indexes on 'conversations' table:\n";
$conversationIndexes = DB::select("SHOW INDEX FROM conversations");
foreach ($conversationIndexes as $index) {
    echo "  - {$index->Key_name} on column {$index->Column_name}\n";
}
echo "\n";

// Cleanup
echo "Cleaning up test data...\n";
$booking->delete();
echo "Done!\n";
