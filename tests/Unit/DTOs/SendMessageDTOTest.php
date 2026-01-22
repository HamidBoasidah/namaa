<?php

namespace Tests\Unit\DTOs;

use App\DTOs\SendMessageDTO;
use App\Exceptions\ValidationException;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SendMessageDTOTest extends TestCase
{
    public function test_creates_dto_with_body_only(): void
    {
        $dto = new SendMessageDTO(
            conversation_id: 1,
            sender_id: 10,
            body: 'Test message',
            files: []
        );

        $this->assertEquals(1, $dto->conversation_id);
        $this->assertEquals(10, $dto->sender_id);
        $this->assertEquals('Test message', $dto->body);
        $this->assertEmpty($dto->files);
    }

    public function test_creates_dto_with_files_only(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $dto = new SendMessageDTO(
            conversation_id: 1,
            sender_id: 10,
            body: null,
            files: [$file]
        );

        $this->assertEquals(1, $dto->conversation_id);
        $this->assertEquals(10, $dto->sender_id);
        $this->assertNull($dto->body);
        $this->assertCount(1, $dto->files);
    }

    public function test_creates_dto_with_body_and_files(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $dto = new SendMessageDTO(
            conversation_id: 1,
            sender_id: 10,
            body: 'Test message',
            files: [$file]
        );

        $this->assertEquals(1, $dto->conversation_id);
        $this->assertEquals(10, $dto->sender_id);
        $this->assertEquals('Test message', $dto->body);
        $this->assertCount(1, $dto->files);
    }

    public function test_throws_exception_when_both_body_and_files_are_empty(): void
    {
        $this->expectException(ValidationException::class);

        new SendMessageDTO(
            conversation_id: 1,
            sender_id: 10,
            body: null,
            files: []
        );
    }

    public function test_throws_exception_when_body_is_empty_string_and_no_files(): void
    {
        $this->expectException(ValidationException::class);

        new SendMessageDTO(
            conversation_id: 1,
            sender_id: 10,
            body: '',
            files: []
        );
    }

    public function test_throws_exception_when_files_array_contains_invalid_type(): void
    {
        $this->expectException(ValidationException::class);

        new SendMessageDTO(
            conversation_id: 1,
            sender_id: 10,
            body: null,
            files: ['not-a-file']
        );
    }
}
