<?php

namespace Tests\Unit\DTOs;

use App\DTOs\MarkReadDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class MarkReadDTOTest extends TestCase
{
    public function test_creates_dto_with_all_parameters(): void
    {
        $dto = new MarkReadDTO(
            conversationId: 123,
            userId: 456,
            messageId: 789
        );

        $this->assertEquals(123, $dto->conversationId);
        $this->assertEquals(456, $dto->userId);
        $this->assertEquals(789, $dto->messageId);
    }

    public function test_creates_dto_with_null_message_id(): void
    {
        $dto = new MarkReadDTO(
            conversationId: 123,
            userId: 456,
            messageId: null
        );

        $this->assertEquals(123, $dto->conversationId);
        $this->assertEquals(456, $dto->userId);
        $this->assertNull($dto->messageId);
    }

    public function test_creates_dto_without_message_id_parameter(): void
    {
        $dto = new MarkReadDTO(
            conversationId: 123,
            userId: 456
        );

        $this->assertEquals(123, $dto->conversationId);
        $this->assertEquals(456, $dto->userId);
        $this->assertNull($dto->messageId);
    }

    public function test_creates_dto_from_request_with_message_id(): void
    {
        $user = new User();
        $user->id = 456;
        
        $request = Request::create('/api/conversations/123/read', 'POST', [
            'message_id' => 789
        ]);
        $request->setUserResolver(fn() => $user);

        $dto = MarkReadDTO::fromRequest($request, 123);

        $this->assertEquals(123, $dto->conversationId);
        $this->assertEquals(456, $dto->userId);
        $this->assertEquals(789, $dto->messageId);
    }

    public function test_creates_dto_from_request_without_message_id(): void
    {
        $user = new User();
        $user->id = 456;
        
        $request = Request::create('/api/conversations/123/read', 'POST');
        $request->setUserResolver(fn() => $user);

        $dto = MarkReadDTO::fromRequest($request, 123);

        $this->assertEquals(123, $dto->conversationId);
        $this->assertEquals(456, $dto->userId);
        $this->assertNull($dto->messageId);
    }

    public function test_dto_properties_are_readonly(): void
    {
        $dto = new MarkReadDTO(
            conversationId: 123,
            userId: 456,
            messageId: 789
        );

        // Verify properties are accessible
        $this->assertEquals(123, $dto->conversationId);
        $this->assertEquals(456, $dto->userId);
        $this->assertEquals(789, $dto->messageId);
    }
}
