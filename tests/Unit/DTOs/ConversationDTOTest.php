<?php

namespace Tests\Unit\DTOs;

use App\DTOs\ConversationDTO;
use App\DTOs\MessageDTO;
use Tests\TestCase;

class ConversationDTOTest extends TestCase
{
    public function test_creates_dto_with_all_parameters(): void
    {
        $participants = [
            ['id' => 1, 'name' => 'John Client', 'avatar' => null],
            ['id' => 2, 'name' => 'Jane Consultant', 'avatar' => null]
        ];

        $lastMessage = new MessageDTO(
            id: 789,
            conversation_id: 123,
            sender_id: 2,
            sender_name: 'Jane Consultant',
            body: 'Test message',
            type: 'text',
            context: 'general',
            attachments: [],
            created_at: '2024-01-15T14:30:00'
        );

        $dto = new ConversationDTO(
            id: 123,
            booking_id: 456,
            participants: $participants,
            last_message: $lastMessage,
            unread_count: 3,
            created_at: '2024-01-10T10:00:00',
            updated_at: '2024-01-15T14:30:00'
        );

        $this->assertEquals(123, $dto->id);
        $this->assertEquals(456, $dto->booking_id);
        $this->assertEquals($participants, $dto->participants);
        $this->assertInstanceOf(MessageDTO::class, $dto->last_message);
        $this->assertEquals(3, $dto->unread_count);
        $this->assertEquals('2024-01-10T10:00:00', $dto->created_at);
        $this->assertEquals('2024-01-15T14:30:00', $dto->updated_at);
    }

    public function test_creates_dto_with_null_last_message(): void
    {
        $participants = [
            ['id' => 1, 'name' => 'John Client', 'avatar' => null]
        ];

        $dto = new ConversationDTO(
            id: 123,
            booking_id: 456,
            participants: $participants,
            last_message: null,
            unread_count: 0,
            created_at: '2024-01-10T10:00:00',
            updated_at: '2024-01-15T14:30:00'
        );

        $this->assertEquals(123, $dto->id);
        $this->assertNull($dto->last_message);
        $this->assertEquals(0, $dto->unread_count);
    }

    public function test_creates_dto_with_zero_unread_count(): void
    {
        $participants = [
            ['id' => 1, 'name' => 'John Client', 'avatar' => null]
        ];

        $dto = new ConversationDTO(
            id: 123,
            booking_id: 456,
            participants: $participants,
            last_message: null,
            unread_count: 0,
            created_at: '2024-01-10T10:00:00',
            updated_at: '2024-01-15T14:30:00'
        );

        $this->assertEquals(0, $dto->unread_count);
    }

    public function test_converts_dto_to_array(): void
    {
        $participants = [
            ['id' => 1, 'name' => 'John Client', 'avatar' => null]
        ];

        $lastMessage = new MessageDTO(
            id: 789,
            conversation_id: 123,
            sender_id: 2,
            sender_name: 'Jane Consultant',
            body: 'Test message',
            type: 'text',
            context: 'general',
            attachments: [],
            created_at: '2024-01-15T14:30:00'
        );

        $dto = new ConversationDTO(
            id: 123,
            booking_id: 456,
            participants: $participants,
            last_message: $lastMessage,
            unread_count: 5,
            created_at: '2024-01-10T10:00:00',
            updated_at: '2024-01-15T14:30:00'
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(123, $array['id']);
        $this->assertEquals(456, $array['booking_id']);
        $this->assertEquals($participants, $array['participants']);
        $this->assertIsArray($array['last_message']);
        $this->assertEquals(5, $array['unread_count']);
        $this->assertEquals('2024-01-10T10:00:00', $array['created_at']);
        $this->assertEquals('2024-01-15T14:30:00', $array['updated_at']);
    }

    public function test_converts_dto_to_array_with_null_last_message(): void
    {
        $participants = [
            ['id' => 1, 'name' => 'John Client', 'avatar' => null]
        ];

        $dto = new ConversationDTO(
            id: 123,
            booking_id: 456,
            participants: $participants,
            last_message: null,
            unread_count: 0,
            created_at: '2024-01-10T10:00:00',
            updated_at: '2024-01-15T14:30:00'
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertNull($array['last_message']);
        $this->assertEquals(0, $array['unread_count']);
    }

    public function test_unread_count_defaults_to_zero(): void
    {
        $participants = [
            ['id' => 1, 'name' => 'John Client', 'avatar' => null]
        ];

        $dto = new ConversationDTO(
            id: 123,
            booking_id: 456,
            participants: $participants,
            last_message: null,
            unread_count: 0,
            created_at: '2024-01-10T10:00:00',
            updated_at: '2024-01-15T14:30:00'
        );

        $this->assertEquals(0, $dto->unread_count);
    }

    public function test_unread_count_can_be_set_to_any_value(): void
    {
        $participants = [
            ['id' => 1, 'name' => 'John Client', 'avatar' => null]
        ];

        $dto = new ConversationDTO(
            id: 123,
            booking_id: 456,
            participants: $participants,
            last_message: null,
            unread_count: 42,
            created_at: '2024-01-10T10:00:00',
            updated_at: '2024-01-15T14:30:00'
        );

        $this->assertEquals(42, $dto->unread_count);
    }

    public function test_includes_updated_at_field(): void
    {
        $participants = [
            ['id' => 1, 'name' => 'John Client', 'avatar' => null]
        ];

        $dto = new ConversationDTO(
            id: 123,
            booking_id: 456,
            participants: $participants,
            last_message: null,
            unread_count: 0,
            created_at: '2024-01-10T10:00:00',
            updated_at: '2024-01-15T14:30:00'
        );

        $this->assertEquals('2024-01-15T14:30:00', $dto->updated_at);
        
        $array = $dto->toArray();
        $this->assertArrayHasKey('updated_at', $array);
        $this->assertEquals('2024-01-15T14:30:00', $array['updated_at']);
    }
}
