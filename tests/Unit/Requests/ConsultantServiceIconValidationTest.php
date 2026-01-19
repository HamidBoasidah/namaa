<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreConsultantServiceRequest;
use App\Http\Requests\UpdateConsultantServiceRequest;
use App\Models\Category;
use App\Models\Consultant;
use App\Models\ConsultationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Property tests for Icon Validation in ConsultantService Requests
 * 
 * Feature: consultant-services-api
 * 
 * @property Property 7: Icon Validation Accepts Only Valid Types
 * @validates Requirements 4.3, 5.3
 */
class ConsultantServiceIconValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Consultant $consultant;
    protected Category $category;

    /**
     * Valid image MIME types that should be accepted
     */
    protected array $validMimeTypes = ['jpeg', 'png', 'gif', 'svg', 'webp'];

    /**
     * Invalid file types that should be rejected
     */
    protected array $invalidMimeTypes = ['pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'mp3', 'mp4', 'avi', 'exe', 'php', 'js', 'html', 'css', 'json', 'xml', 'csv', 'bmp', 'tiff'];

    /**
     * Maximum allowed file size in KB
     */
    protected int $maxFileSizeKB = 2048;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        // Create required related models
        $consultationType = ConsultationType::factory()->create();
        $user = User::factory()->create(['user_type' => 'consultant']);
        $this->consultant = Consultant::factory()->create([
            'user_id' => $user->id,
            'consultation_type_id' => $consultationType->id,
        ]);
        $this->category = Category::factory()->create([
            'consultation_type_id' => $consultationType->id,
        ]);
    }

    /**
     * Helper to create base valid request data
     */
    protected function createBaseRequestData(): array
    {
        return [
            'consultant_id' => $this->consultant->id,
            'category_id' => $this->category->id,
            'title' => fake()->unique()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'consultation_method' => fake()->randomElement(['video', 'audio', 'text']),
            'is_active' => true,
        ];
    }

    /**
     * Helper to create a fake uploaded image file with specific extension
     */
    protected function createFakeImage(string $extension = 'png', int $sizeKB = 100): UploadedFile
    {
        // SVG files need special handling as they are not raster images
        if ($extension === 'svg') {
            return $this->createFakeSvgFile($sizeKB);
        }
        
        return UploadedFile::fake()->image("icon.{$extension}", 100, 100)->size($sizeKB);
    }

    /**
     * Helper to create a fake SVG file
     */
    protected function createFakeSvgFile(int $sizeKB = 100): UploadedFile
    {
        $svgContent = '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="blue"/></svg>';
        
        // Pad the content to reach the desired size
        $targetBytes = $sizeKB * 1024;
        $currentBytes = strlen($svgContent);
        
        if ($currentBytes < $targetBytes) {
            // Add XML comments to pad the file
            $padding = str_repeat('<!-- padding -->', (int)(($targetBytes - $currentBytes) / 16));
            $svgContent = str_replace('</svg>', $padding . '</svg>', $svgContent);
        }
        
        return UploadedFile::fake()->createWithContent('icon.svg', $svgContent);
    }

    /**
     * Helper to create a fake uploaded file (non-image)
     */
    protected function createFakeFile(string $extension, int $sizeKB = 100): UploadedFile
    {
        return UploadedFile::fake()->create("file.{$extension}", $sizeKB);
    }

    /**
     * Helper to validate request data against StoreConsultantServiceRequest rules
     */
    protected function validateStoreRequest(array $data): \Illuminate\Validation\Validator
    {
        $request = new StoreConsultantServiceRequest();
        return Validator::make($data, $request->rules());
    }

    /**
     * Helper to validate request data against UpdateConsultantServiceRequest rules
     */
    protected function validateUpdateRequest(array $data): \Illuminate\Validation\Validator
    {
        $request = new UpdateConsultantServiceRequest();
        return Validator::make($data, $request->rules());
    }

    // ─────────────────────────────────────────────────────────────
    // Property 7: Icon Validation Accepts Only Valid Types
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: For any uploaded file as icon, it must be accepted only if it is
     * one of the valid types: jpeg, png, gif, svg, webp.
     * 
     * **Validates: Requirements 4.3, 5.3**
     */
    public function test_valid_image_types_are_accepted_in_store_request(): void
    {
        foreach ($this->validMimeTypes as $mimeType) {
            $data = $this->createBaseRequestData();
            $data['icon'] = $this->createFakeImage($mimeType);
            
            $validator = $this->validateStoreRequest($data);
            
            $this->assertFalse(
                $validator->fails(),
                "StoreRequest should accept {$mimeType} files. Errors: " . json_encode($validator->errors()->toArray())
            );
        }
    }

    /**
     * Property: For any uploaded file as icon, it must be accepted only if it is
     * one of the valid types: jpeg, png, gif, svg, webp.
     * 
     * **Validates: Requirements 4.3, 5.3**
     */
    public function test_valid_image_types_are_accepted_in_update_request(): void
    {
        foreach ($this->validMimeTypes as $mimeType) {
            $data = $this->createBaseRequestData();
            $data['icon'] = $this->createFakeImage($mimeType);
            
            $validator = $this->validateUpdateRequest($data);
            
            $this->assertFalse(
                $validator->fails(),
                "UpdateRequest should accept {$mimeType} files. Errors: " . json_encode($validator->errors()->toArray())
            );
        }
    }

    /**
     * Property: Invalid file types (pdf, txt, doc, etc.) must be rejected.
     * 
     * **Validates: Requirements 4.3, 5.3**
     */
    public function test_invalid_file_types_are_rejected_in_store_request(): void
    {
        foreach ($this->invalidMimeTypes as $mimeType) {
            $data = $this->createBaseRequestData();
            // Use unique title for each iteration to avoid unique constraint issues
            $data['title'] = fake()->unique()->sentence(3);
            $data['icon'] = $this->createFakeFile($mimeType);
            
            $validator = $this->validateStoreRequest($data);
            
            $this->assertTrue(
                $validator->fails(),
                "StoreRequest should reject {$mimeType} files"
            );
            
            $this->assertTrue(
                $validator->errors()->has('icon'),
                "StoreRequest should have icon validation error for {$mimeType} files"
            );
        }
    }

    /**
     * Property: Invalid file types (pdf, txt, doc, etc.) must be rejected.
     * 
     * **Validates: Requirements 4.3, 5.3**
     */
    public function test_invalid_file_types_are_rejected_in_update_request(): void
    {
        foreach ($this->invalidMimeTypes as $mimeType) {
            $data = $this->createBaseRequestData();
            // Use unique title for each iteration to avoid unique constraint issues
            $data['title'] = fake()->unique()->sentence(3);
            $data['icon'] = $this->createFakeFile($mimeType);
            
            $validator = $this->validateUpdateRequest($data);
            
            $this->assertTrue(
                $validator->fails(),
                "UpdateRequest should reject {$mimeType} files"
            );
            
            $this->assertTrue(
                $validator->errors()->has('icon'),
                "UpdateRequest should have icon validation error for {$mimeType} files"
            );
        }
    }

    /**
     * Property: Files exceeding 2MB (2048 KB) must be rejected.
     * 
     * **Validates: Requirements 4.4, 5.4**
     */
    public function test_files_exceeding_max_size_are_rejected_in_store_request(): void
    {
        // Test with file sizes exceeding 2MB
        $oversizedFileSizes = [2049, 3000, 5000, 10000];
        
        foreach ($oversizedFileSizes as $sizeKB) {
            $data = $this->createBaseRequestData();
            $data['title'] = fake()->unique()->sentence(3);
            $data['icon'] = $this->createFakeImage('png', $sizeKB);
            
            $validator = $this->validateStoreRequest($data);
            
            $this->assertTrue(
                $validator->fails(),
                "StoreRequest should reject files of size {$sizeKB}KB (exceeds 2048KB limit)"
            );
            
            $this->assertTrue(
                $validator->errors()->has('icon'),
                "StoreRequest should have icon validation error for oversized files ({$sizeKB}KB)"
            );
        }
    }

    /**
     * Property: Files exceeding 2MB (2048 KB) must be rejected.
     * 
     * **Validates: Requirements 4.4, 5.4**
     */
    public function test_files_exceeding_max_size_are_rejected_in_update_request(): void
    {
        // Test with file sizes exceeding 2MB
        $oversizedFileSizes = [2049, 3000, 5000, 10000];
        
        foreach ($oversizedFileSizes as $sizeKB) {
            $data = $this->createBaseRequestData();
            $data['title'] = fake()->unique()->sentence(3);
            $data['icon'] = $this->createFakeImage('png', $sizeKB);
            
            $validator = $this->validateUpdateRequest($data);
            
            $this->assertTrue(
                $validator->fails(),
                "UpdateRequest should reject files of size {$sizeKB}KB (exceeds 2048KB limit)"
            );
            
            $this->assertTrue(
                $validator->errors()->has('icon'),
                "UpdateRequest should have icon validation error for oversized files ({$sizeKB}KB)"
            );
        }
    }

    /**
     * Property: Files within the 2MB limit should be accepted.
     * 
     * **Validates: Requirements 4.4, 5.4**
     */
    public function test_files_within_max_size_are_accepted_in_store_request(): void
    {
        // Test with file sizes within 2MB limit
        $validFileSizes = [100, 500, 1000, 1500, 2000, 2048];
        
        foreach ($validFileSizes as $sizeKB) {
            $data = $this->createBaseRequestData();
            $data['title'] = fake()->unique()->sentence(3);
            $data['icon'] = $this->createFakeImage('png', $sizeKB);
            
            $validator = $this->validateStoreRequest($data);
            
            $this->assertFalse(
                $validator->fails(),
                "StoreRequest should accept files of size {$sizeKB}KB. Errors: " . json_encode($validator->errors()->toArray())
            );
        }
    }

    /**
     * Property: Files within the 2MB limit should be accepted.
     * 
     * **Validates: Requirements 4.4, 5.4**
     */
    public function test_files_within_max_size_are_accepted_in_update_request(): void
    {
        // Test with file sizes within 2MB limit
        $validFileSizes = [100, 500, 1000, 1500, 2000, 2048];
        
        foreach ($validFileSizes as $sizeKB) {
            $data = $this->createBaseRequestData();
            $data['title'] = fake()->unique()->sentence(3);
            $data['icon'] = $this->createFakeImage('png', $sizeKB);
            
            $validator = $this->validateUpdateRequest($data);
            
            $this->assertFalse(
                $validator->fails(),
                "UpdateRequest should accept files of size {$sizeKB}KB. Errors: " . json_encode($validator->errors()->toArray())
            );
        }
    }

    /**
     * Property: Null icon should be accepted (icon is nullable).
     * 
     * **Validates: Requirements 5.1, 5.2**
     */
    public function test_null_icon_is_accepted_in_store_request(): void
    {
        $data = $this->createBaseRequestData();
        // Don't include icon field at all
        
        $validator = $this->validateStoreRequest($data);
        
        $this->assertFalse(
            $validator->fails(),
            "StoreRequest should accept request without icon. Errors: " . json_encode($validator->errors()->toArray())
        );
    }

    /**
     * Property: Null icon should be accepted (icon is nullable).
     * 
     * **Validates: Requirements 5.1, 5.2**
     */
    public function test_null_icon_is_accepted_in_update_request(): void
    {
        $data = $this->createBaseRequestData();
        // Don't include icon field at all
        
        $validator = $this->validateUpdateRequest($data);
        
        $this->assertFalse(
            $validator->fails(),
            "UpdateRequest should accept request without icon. Errors: " . json_encode($validator->errors()->toArray())
        );
    }

    /**
     * Property: remove_icon boolean should be accepted in UpdateRequest.
     * 
     * **Validates: Requirements 5.5**
     */
    public function test_remove_icon_boolean_is_accepted_in_update_request(): void
    {
        $booleanValues = [true, false, 1, 0, '1', '0'];
        
        foreach ($booleanValues as $value) {
            $data = $this->createBaseRequestData();
            $data['title'] = fake()->unique()->sentence(3);
            $data['remove_icon'] = $value;
            
            $validator = $this->validateUpdateRequest($data);
            
            $this->assertFalse(
                $validator->errors()->has('remove_icon'),
                "UpdateRequest should accept remove_icon = " . var_export($value, true)
            );
        }
    }

    /**
     * Property: jpg extension should be treated as jpeg and accepted.
     * 
     * **Validates: Requirements 4.3, 5.3**
     */
    public function test_jpg_extension_is_accepted(): void
    {
        $data = $this->createBaseRequestData();
        $data['icon'] = $this->createFakeImage('jpg');
        
        $storeValidator = $this->validateStoreRequest($data);
        $updateValidator = $this->validateUpdateRequest($data);
        
        $this->assertFalse(
            $storeValidator->fails(),
            "StoreRequest should accept jpg files. Errors: " . json_encode($storeValidator->errors()->toArray())
        );
        
        $this->assertFalse(
            $updateValidator->fails(),
            "UpdateRequest should accept jpg files. Errors: " . json_encode($updateValidator->errors()->toArray())
        );
    }

    /**
     * Property: Multiple valid types should all be accepted in a randomized order.
     * This tests the property across random combinations.
     * 
     * **Validates: Requirements 4.3, 5.3**
     */
    public function test_random_valid_types_are_accepted(): void
    {
        // Shuffle valid types to test in random order
        $shuffledTypes = $this->validMimeTypes;
        shuffle($shuffledTypes);
        
        foreach ($shuffledTypes as $mimeType) {
            $data = $this->createBaseRequestData();
            $data['title'] = fake()->unique()->sentence(3);
            $data['icon'] = $this->createFakeImage($mimeType);
            
            $storeValidator = $this->validateStoreRequest($data);
            $updateValidator = $this->validateUpdateRequest($data);
            
            $this->assertFalse(
                $storeValidator->fails(),
                "StoreRequest should accept {$mimeType} files in random order"
            );
            
            $this->assertFalse(
                $updateValidator->fails(),
                "UpdateRequest should accept {$mimeType} files in random order"
            );
        }
    }

    /**
     * Property: Multiple invalid types should all be rejected in a randomized order.
     * This tests the property across random combinations.
     * 
     * **Validates: Requirements 4.3, 5.3**
     */
    public function test_random_invalid_types_are_rejected(): void
    {
        // Shuffle invalid types and take a sample to test in random order
        $shuffledTypes = $this->invalidMimeTypes;
        shuffle($shuffledTypes);
        $sampleTypes = array_slice($shuffledTypes, 0, 10); // Test 10 random invalid types
        
        foreach ($sampleTypes as $mimeType) {
            $data = $this->createBaseRequestData();
            $data['title'] = fake()->unique()->sentence(3);
            $data['icon'] = $this->createFakeFile($mimeType);
            
            $storeValidator = $this->validateStoreRequest($data);
            $updateValidator = $this->validateUpdateRequest($data);
            
            $this->assertTrue(
                $storeValidator->fails() && $storeValidator->errors()->has('icon'),
                "StoreRequest should reject {$mimeType} files in random order"
            );
            
            $this->assertTrue(
                $updateValidator->fails() && $updateValidator->errors()->has('icon'),
                "UpdateRequest should reject {$mimeType} files in random order"
            );
        }
    }

    /**
     * Property: Boundary test - file exactly at 2048KB should be accepted.
     * 
     * **Validates: Requirements 4.4, 5.4**
     */
    public function test_file_exactly_at_max_size_is_accepted(): void
    {
        $data = $this->createBaseRequestData();
        $data['icon'] = $this->createFakeImage('png', 2048);
        
        $storeValidator = $this->validateStoreRequest($data);
        $updateValidator = $this->validateUpdateRequest($data);
        
        $this->assertFalse(
            $storeValidator->fails(),
            "StoreRequest should accept file exactly at 2048KB. Errors: " . json_encode($storeValidator->errors()->toArray())
        );
        
        $this->assertFalse(
            $updateValidator->fails(),
            "UpdateRequest should accept file exactly at 2048KB. Errors: " . json_encode($updateValidator->errors()->toArray())
        );
    }

    /**
     * Property: Boundary test - file at 2049KB (just over limit) should be rejected.
     * 
     * **Validates: Requirements 4.4, 5.4**
     */
    public function test_file_just_over_max_size_is_rejected(): void
    {
        $data = $this->createBaseRequestData();
        $data['icon'] = $this->createFakeImage('png', 2049);
        
        $storeValidator = $this->validateStoreRequest($data);
        $updateValidator = $this->validateUpdateRequest($data);
        
        $this->assertTrue(
            $storeValidator->fails() && $storeValidator->errors()->has('icon'),
            "StoreRequest should reject file at 2049KB"
        );
        
        $this->assertTrue(
            $updateValidator->fails() && $updateValidator->errors()->has('icon'),
            "UpdateRequest should reject file at 2049KB"
        );
    }
}
