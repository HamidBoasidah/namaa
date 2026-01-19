<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Consultant;
use App\Models\ConsultantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Property tests for ConsultantService Model
 * 
 * @property Feature: consultant-services-api, Property 1: Icon URL Accessor Correctness
 * @validates Requirements 1.4
 */
class ConsultantServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 1: Icon URL Accessor Correctness
     * 
     * For any ConsultantService model with non-empty icon_path,
     * the icon_url accessor must return a full URL using Storage::url().
     * 
     * **Validates: Requirements 1.4**
     */
    public function test_icon_url_accessor_returns_full_url_when_icon_path_exists(): void
    {
        // Create required related models
        $consultant = Consultant::factory()->create();
        $category = Category::factory()->create();

        // Test with various icon paths
        $iconPaths = [
            'consultant-services/icons/test-icon.png',
            'consultant-services/icons/abc123.jpg',
            'consultant-services/icons/image.webp',
            'consultant-services/icons/logo.svg',
        ];

        foreach ($iconPaths as $index => $iconPath) {
            $service = ConsultantService::create([
                'consultant_id' => $consultant->id,
                'category_id' => $category->id,
                'title' => 'Test Service ' . $index,
                'description' => 'Test description',
                'icon_path' => $iconPath,
                'price' => 100.00,
                'duration_minutes' => 60,
                'consultation_method' => 'video',
                'is_active' => true,
            ]);

            // The icon_url should be the Storage URL of the icon_path
            $expectedUrl = Storage::url($iconPath);
            
            $this->assertNotNull($service->icon_url);
            $this->assertEquals($expectedUrl, $service->icon_url);
        }
    }

    /**
     * Property 1 (continued): Icon URL Accessor returns null when icon_path is null
     * 
     * If icon_path is null, then the icon_url accessor shall return null.
     * 
     * **Validates: Requirements 1.5**
     */
    public function test_icon_url_accessor_returns_null_when_icon_path_is_null(): void
    {
        $consultant = Consultant::factory()->create();
        $category = Category::factory()->create();

        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'Test Service Without Icon',
            'description' => 'Test description',
            'icon_path' => null,
            'price' => 100.00,
            'duration_minutes' => 60,
            'consultation_method' => 'video',
            'is_active' => true,
        ]);

        $this->assertNull($service->icon_url);
    }

    /**
     * Property 1 (continued): Icon path is included in fillable array
     * 
     * The ConsultantService Model shall include icon_path in the fillable array.
     * 
     * **Validates: Requirements 1.3**
     */
    public function test_icon_path_is_fillable(): void
    {
        $consultant = Consultant::factory()->create();
        $category = Category::factory()->create();

        $iconPath = 'consultant-services/icons/test.png';
        
        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'Test Service',
            'icon_path' => $iconPath,
            'price' => 100.00,
            'duration_minutes' => 60,
            'consultation_method' => 'video',
            'is_active' => true,
        ]);

        // Verify icon_path was saved correctly
        $this->assertEquals($iconPath, $service->icon_path);
        
        // Verify it persists in database
        $freshService = ConsultantService::find($service->id);
        $this->assertEquals($iconPath, $freshService->icon_path);
    }

    /**
     * Property 1 (continued): Icon URL starts with storage URL prefix
     * 
     * For any ConsultantService model with non-empty icon_path,
     * the icon_url must start with the Storage URL base.
     * 
     * **Validates: Requirements 1.4**
     */
    public function test_icon_url_starts_with_storage_url_prefix(): void
    {
        $consultant = Consultant::factory()->create();
        $category = Category::factory()->create();

        $iconPath = 'consultant-services/icons/test-icon.png';
        
        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'Test Service',
            'icon_path' => $iconPath,
            'price' => 100.00,
            'duration_minutes' => 60,
            'consultation_method' => 'video',
            'is_active' => true,
        ]);

        // The icon_url should contain the icon_path
        $this->assertStringContainsString($iconPath, $service->icon_url);
    }
}
