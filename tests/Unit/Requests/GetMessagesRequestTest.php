<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Api\GetMessagesRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetMessagesRequestTest extends TestCase
{
    protected function validateRequest(array $data): \Illuminate\Validation\Validator
    {
        $request = new GetMessagesRequest();
        return Validator::make($data, $request->rules());
    }

    public function test_cursor_accepts_valid_string(): void
    {
        $data = ['cursor' => 'eyJpZCI6MTAxfQ=='];
        $validator = $this->validateRequest($data);
        
        $this->assertFalse($validator->fails());
    }

    public function test_cursor_accepts_null(): void
    {
        $data = [];
        $validator = $this->validateRequest($data);
        
        $this->assertFalse($validator->fails());
    }

    public function test_per_page_accepts_valid_integer(): void
    {
        $data = ['per_page' => 50];
        $validator = $this->validateRequest($data);
        
        $this->assertFalse($validator->fails());
    }

    public function test_per_page_accepts_null(): void
    {
        $data = [];
        $validator = $this->validateRequest($data);
        
        $this->assertFalse($validator->fails());
    }

    public function test_per_page_rejects_value_less_than_one(): void
    {
        $data = ['per_page' => 0];
        $validator = $this->validateRequest($data);
        
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('per_page'));
    }

    public function test_per_page_rejects_value_greater_than_100(): void
    {
        $data = ['per_page' => 101];
        $validator = $this->validateRequest($data);
        
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('per_page'));
    }

    public function test_per_page_accepts_boundary_value_1(): void
    {
        $data = ['per_page' => 1];
        $validator = $this->validateRequest($data);
        
        $this->assertFalse($validator->fails());
    }

    public function test_per_page_accepts_boundary_value_100(): void
    {
        $data = ['per_page' => 100];
        $validator = $this->validateRequest($data);
        
        $this->assertFalse($validator->fails());
    }

    public function test_per_page_rejects_non_integer(): void
    {
        $data = ['per_page' => 'not-a-number'];
        $validator = $this->validateRequest($data);
        
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('per_page'));
    }

    public function test_both_parameters_can_be_provided_together(): void
    {
        $data = [
            'cursor' => 'eyJpZCI6MTAxfQ==',
            'per_page' => 25,
        ];
        $validator = $this->validateRequest($data);
        
        $this->assertFalse($validator->fails());
    }
}
