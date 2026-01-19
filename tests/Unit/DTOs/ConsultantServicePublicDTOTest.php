<?php

namespace Tests\Unit\DTOs;

use App\DTOs\ConsultantServicePublicDTO;
use App\Models\Category;
use App\Models\Consultant;
use App\Models\ConsultantExperience;
use App\Models\ConsultantService;
use App\Models\ConsultantServiceDetail;
use App\Models\ConsultationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ConsultantServicePublicDTO
 * 
 * @property Feature: consultant-services-api
 * @validates Requirements 8.1, 8.2, 8.3, 8.4, 8.5
 */
class ConsultantServicePublicDTOTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that toListArray returns only public-facing fields for list display
     * 
     * **Validates: Requirements 8.1, 8.2**
     */
    public function test_to_list_array_returns_only_list_fields(): void
    {
        $consultant = Consultant::factory()->create();
        $category = Category::factory()->create();

        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'استشارة قانونية',
            'description' => 'استشارة قانونية شاملة',
            'icon_path' => 'consultant-services/icons/test.png',
            'price' => 150.00,
            'duration_minutes' => 60,
            'consultation_method' => 'video',
            'delivery_time' => 'خلال 24 ساعة',
            'is_active' => true,
        ]);

        $dto = ConsultantServicePublicDTO::fromModel($service);
        $listArray = $dto->toListArray();

        // Should only contain: id, icon_url, title, description
        $this->assertArrayHasKey('id', $listArray);
        $this->assertArrayHasKey('icon_url', $listArray);
        $this->assertArrayHasKey('title', $listArray);
        $this->assertArrayHasKey('description', $listArray);

        // Should NOT contain other fields
        $this->assertArrayNotHasKey('price', $listArray);
        $this->assertArrayNotHasKey('duration_minutes', $listArray);
        $this->assertArrayNotHasKey('consultation_method', $listArray);
        $this->assertArrayNotHasKey('delivery_time', $listArray);
        $this->assertArrayNotHasKey('includes', $listArray);
        $this->assertArrayNotHasKey('target_audience', $listArray);
        $this->assertArrayNotHasKey('deliverables', $listArray);
        $this->assertArrayNotHasKey('consultant', $listArray);

        // Verify values
        $this->assertEquals($service->id, $listArray['id']);
        $this->assertEquals($service->title, $listArray['title']);
        $this->assertEquals($service->description, $listArray['description']);
    }

    /**
     * Test that toDetailArray returns all service and consultant information
     * 
     * **Validates: Requirements 8.1, 8.3**
     */
    public function test_to_detail_array_returns_all_information(): void
    {
        $user = User::factory()->create([
            'first_name' => 'أحمد',
            'last_name' => 'محمد',
            'avatar' => 'avatars/user123.jpg',
        ]);

        $consultationType = ConsultationType::factory()->create([
            'name' => 'استشارات قانونية',
        ]);

        $consultant = Consultant::factory()->create([
            'user_id' => $user->id,
            'consultation_type_id' => $consultationType->id,
        ]);

        $category = Category::factory()->create();

        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'استشارة قانونية',
            'description' => 'استشارة قانونية شاملة',
            'icon_path' => 'consultant-services/icons/test.png',
            'price' => 150.00,
            'duration_minutes' => 60,
            'consultation_method' => 'video',
            'delivery_time' => 'خلال 24 ساعة',
            'is_active' => true,
        ]);

        // Add service details
        ConsultantServiceDetail::create([
            'consultant_service_id' => $service->id,
            'type' => ConsultantServiceDetail::TYPE_INCLUDES,
            'content' => 'مراجعة المستندات',
            'sort_order' => 1,
        ]);

        ConsultantServiceDetail::create([
            'consultant_service_id' => $service->id,
            'type' => ConsultantServiceDetail::TYPE_TARGET_AUDIENCE,
            'content' => 'الشركات الناشئة',
            'sort_order' => 1,
        ]);

        ConsultantServiceDetail::create([
            'consultant_service_id' => $service->id,
            'type' => ConsultantServiceDetail::TYPE_DELIVERABLES,
            'content' => 'تقرير استشاري',
            'sort_order' => 1,
        ]);

        // Refresh to load relationships
        $service->refresh();

        $dto = ConsultantServicePublicDTO::fromModel($service);
        $detailArray = $dto->toDetailArray();

        // Should contain all fields
        $this->assertArrayHasKey('id', $detailArray);
        $this->assertArrayHasKey('icon_url', $detailArray);
        $this->assertArrayHasKey('title', $detailArray);
        $this->assertArrayHasKey('description', $detailArray);
        $this->assertArrayHasKey('price', $detailArray);
        $this->assertArrayHasKey('duration_minutes', $detailArray);
        $this->assertArrayHasKey('consultation_method', $detailArray);
        $this->assertArrayHasKey('delivery_time', $detailArray);
        $this->assertArrayHasKey('includes', $detailArray);
        $this->assertArrayHasKey('target_audience', $detailArray);
        $this->assertArrayHasKey('deliverables', $detailArray);
        $this->assertArrayHasKey('consultant', $detailArray);

        // Verify service values
        $this->assertEquals($service->id, $detailArray['id']);
        $this->assertEquals($service->title, $detailArray['title']);
        $this->assertEquals('150.00', $detailArray['price']);
        $this->assertEquals(60, $detailArray['duration_minutes']);
        $this->assertEquals('video', $detailArray['consultation_method']);

        // Verify service details
        $this->assertContains('مراجعة المستندات', $detailArray['includes']);
        $this->assertContains('الشركات الناشئة', $detailArray['target_audience']);
        $this->assertContains('تقرير استشاري', $detailArray['deliverables']);
    }

    /**
     * Test that fromModel loads consultant with user, consultationType, and experiences relations
     * 
     * **Validates: Requirements 8.4**
     */
    public function test_from_model_loads_consultant_relations(): void
    {
        $user = User::factory()->create([
            'first_name' => 'أحمد',
            'last_name' => 'محمد',
            'avatar' => 'avatars/user123.jpg',
        ]);

        $consultationType = ConsultationType::factory()->create([
            'name' => 'استشارات قانونية',
        ]);

        $consultant = Consultant::factory()->create([
            'user_id' => $user->id,
            'consultation_type_id' => $consultationType->id,
        ]);

        $category = Category::factory()->create();

        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'استشارة قانونية',
            'price' => 150.00,
            'duration_minutes' => 60,
            'consultation_method' => 'video',
            'is_active' => true,
        ]);

        $dto = ConsultantServicePublicDTO::fromModel($service);
        $detailArray = $dto->toDetailArray();

        // Verify consultant information is loaded
        $this->assertNotNull($detailArray['consultant']);
        $this->assertEquals('avatars/user123.jpg', $detailArray['consultant']['avatar']);
        $this->assertEquals('أحمد', $detailArray['consultant']['first_name']);
        $this->assertEquals('محمد', $detailArray['consultant']['last_name']);
        $this->assertEquals('استشارات قانونية', $detailArray['consultant']['consultation_type_name']);
        $this->assertArrayHasKey('experiences', $detailArray['consultant']);
    }

    /**
     * Test that experiences array includes name, organization, and years for each experience
     * 
     * **Validates: Requirements 8.5**
     */
    public function test_experiences_array_includes_required_fields(): void
    {
        $user = User::factory()->create();
        $consultationType = ConsultationType::factory()->create();

        $consultant = Consultant::factory()->create([
            'user_id' => $user->id,
            'consultation_type_id' => $consultationType->id,
        ]);

        // Create experiences
        ConsultantExperience::create([
            'consultant_id' => $consultant->id,
            'name' => 'محامي',
            'is_active' => true,
        ]);

        ConsultantExperience::create([
            'consultant_id' => $consultant->id,
            'name' => 'مستشار قانوني',
            'is_active' => true,
        ]);

        $category = Category::factory()->create();

        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'استشارة قانونية',
            'price' => 150.00,
            'duration_minutes' => 60,
            'consultation_method' => 'video',
            'is_active' => true,
        ]);

        $dto = ConsultantServicePublicDTO::fromModel($service);
        $detailArray = $dto->toDetailArray();

        // Verify experiences structure
        $this->assertNotEmpty($detailArray['consultant']['experiences']);
        
        foreach ($detailArray['consultant']['experiences'] as $experience) {
            $this->assertArrayHasKey('name', $experience);
            $this->assertArrayHasKey('organization', $experience);
            $this->assertArrayHasKey('years', $experience);
        }

        // Verify first experience
        $this->assertEquals('محامي', $detailArray['consultant']['experiences'][0]['name']);
    }

    /**
     * Test that DTO handles service with consultant that has no user gracefully
     * 
     * **Validates: Requirements 8.1**
     */
    public function test_handles_consultant_without_user(): void
    {
        // Create consultant without user relationship loaded
        $consultant = Consultant::factory()->create();
        $category = Category::factory()->create();

        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'خدمة مع مستشار',
            'price' => 100.00,
            'duration_minutes' => 30,
            'consultation_method' => 'text',
            'is_active' => true,
        ]);

        $dto = ConsultantServicePublicDTO::fromModel($service);
        $detailArray = $dto->toDetailArray();

        // Consultant should be present with user info
        $this->assertNotNull($detailArray['consultant']);
        $this->assertArrayHasKey('avatar', $detailArray['consultant']);
        $this->assertArrayHasKey('first_name', $detailArray['consultant']);
        $this->assertArrayHasKey('last_name', $detailArray['consultant']);
    }

    /**
     * Test that DTO handles service without icon gracefully
     * 
     * **Validates: Requirements 8.1**
     */
    public function test_handles_service_without_icon(): void
    {
        $consultant = Consultant::factory()->create();
        $category = Category::factory()->create();

        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'خدمة بدون أيقونة',
            'icon_path' => null,
            'price' => 100.00,
            'duration_minutes' => 30,
            'consultation_method' => 'text',
            'is_active' => true,
        ]);

        $dto = ConsultantServicePublicDTO::fromModel($service);
        
        $listArray = $dto->toListArray();
        $detailArray = $dto->toDetailArray();

        $this->assertNull($listArray['icon_url']);
        $this->assertNull($detailArray['icon_url']);
    }

    /**
     * Test that DTO handles empty service details gracefully
     * 
     * **Validates: Requirements 8.3**
     */
    public function test_handles_empty_service_details(): void
    {
        $consultant = Consultant::factory()->create();
        $category = Category::factory()->create();

        $service = ConsultantService::create([
            'consultant_id' => $consultant->id,
            'category_id' => $category->id,
            'title' => 'خدمة بدون تفاصيل',
            'price' => 100.00,
            'duration_minutes' => 30,
            'consultation_method' => 'text',
            'is_active' => true,
        ]);

        $dto = ConsultantServicePublicDTO::fromModel($service);
        $detailArray = $dto->toDetailArray();

        $this->assertIsArray($detailArray['includes']);
        $this->assertIsArray($detailArray['target_audience']);
        $this->assertIsArray($detailArray['deliverables']);
        $this->assertEmpty($detailArray['includes']);
        $this->assertEmpty($detailArray['target_audience']);
        $this->assertEmpty($detailArray['deliverables']);
    }
}
