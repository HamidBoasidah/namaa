<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\AttachmentResource;
use App\Models\MessageAttachment;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Booking;
use App\Models\User;
use App\Models\Consultant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachmentResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attachment_resource_transforms_model_correctly(): void
    {
        // Create necessary models
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultant = Consultant::factory()->create();
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => 'confirmed',
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'body' => 'Test message',
            'type' => 'attachment',
            'context' => 'out_of_session',
        ]);

        $attachment = MessageAttachment::factory()->create([
            'message_id' => $message->id,
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1048576,
            'disk' => 'private',
            'path' => 'chat-attachments/1/1/test.pdf',
        ]);

        // Mock the getDownloadUrl method to avoid route dependency
        $attachmentMock = \Mockery::mock($attachment)->makePartial();
        $attachmentMock->shouldReceive('getDownloadUrl')
            ->andReturn('/api/attachments/' . $attachment->id);

        // Transform using resource
        $resource = new AttachmentResource($attachmentMock);
        $array = $resource->toArray(request());

        // Assert structure
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('original_name', $array);
        $this->assertArrayHasKey('mime_type', $array);
        $this->assertArrayHasKey('size_bytes', $array);
        $this->assertArrayHasKey('download_url', $array);

        // Assert values
        $this->assertEquals($attachment->id, $array['id']);
        $this->assertEquals('document.pdf', $array['original_name']);
        $this->assertEquals('application/pdf', $array['mime_type']);
        $this->assertEquals(1048576, $array['size_bytes']);
        $this->assertStringContainsString('/api/attachments/', $array['download_url']);
        $this->assertStringContainsString((string)$attachment->id, $array['download_url']);
    }

    public function test_attachment_resource_handles_different_mime_types(): void
    {
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultant = Consultant::factory()->create();
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => 'confirmed',
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
        ]);

        $attachment = MessageAttachment::factory()->create([
            'message_id' => $message->id,
            'original_name' => 'image.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 524288,
        ]);

        // Mock the getDownloadUrl method to avoid route dependency
        $attachmentMock = \Mockery::mock($attachment)->makePartial();
        $attachmentMock->shouldReceive('getDownloadUrl')
            ->andReturn('/api/attachments/' . $attachment->id);

        $resource = new AttachmentResource($attachmentMock);
        $array = $resource->toArray(request());

        $this->assertEquals('image.jpg', $array['original_name']);
        $this->assertEquals('image/jpeg', $array['mime_type']);
        $this->assertEquals(524288, $array['size_bytes']);
    }
}
