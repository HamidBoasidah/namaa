<?php

namespace Tests\Feature\Api\Mobile;

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
 * Property-based tests for Mobile Consultant Service API
 * 
 * Feature: consultant-services-api
 * 
 * @property Property 8: Services List Returns Only Active Services
 * @property Property 9: Service Details Returns Complete Information
 * @property Property 10: Pagination Works Correctly
 * @validates Requirements 6.1, 6.2, 6.3, 6.5, 7.1, 7.2, 7.3, 7.4, 7.5
 */
class ConsultantServiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected Consultant $consultant;
    protected Category $category;
    protected ConsultationType $consultationType;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create required related models
        $this->consultationType = ConsultationType::factory()->create([
            'name' => 'استشارات قانونية',
        ]);
        
        $this->user = User::factory()->create([
            'user_type' => 'consultant',
            'first_name' => 'أحمد',
            'last_name' => 'محمد',
            'avatar' => 'avatars/user123.jpg',
        ]);
        
        $this->consultant = Consultant::factory()->create([
            'user_id' => $this->user->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);
        
        $this->category = Category::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
        ]);
    }

    /**
     * Helper to create a service with given attributes
     */
    protected function createService(array $overrides = []): ConsultantService
    {
        return ConsultantService::create(array_merge([
            'consultant_id' => $this->consultant->id,
            'category_id' => $this->category->id,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'icon_path' => 'consultant-services/icons/test.png',
            'price' => fake()->randomFloat(2, 10, 1000),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'consultation_method' => fake()->randomElement(['video', 'audio', 'text']),
            'delivery_time' => 'خلال 24 ساعة',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Helper to add service details
     */
    protected function addServiceDetails(ConsultantService $service): void
    {
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
    }

    /**
     * Helper to add consultant experiences
     */
    protected function addConsultantExperiences(): void
    {
        ConsultantExperience::create([
            'consultant_id' => $this->consultant->id,
            'name' => 'محامي',
            'is_active' => true,
        ]);

        ConsultantExperience::create([
            'consultant_id' => $this->consultant->id,
            'name' => 'مستشار قانوني',
            'is_active' => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 8: Services List Returns Only Active Services
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: For any request to services list, the response must contain only 
     * active services (is_active = true) with fields: id, icon_url, title, description.
     * 
     * **Validates: Requirements 6.1, 6.2, 6.3**
     */
    public function test_services_list_returns_only_active_services(): void
    {
        // Create mix of active and inactive services
        $activeServices = [];
        $inactiveServices = [];
        
        // Create random number of active services (1-5)
        $activeCount = fake()->numberBetween(1, 5);
        for ($i = 0; $i < $activeCount; $i++) {
            $activeServices[] = $this->createService(['is_active' => true]);
        }
        
        // Create random number of inactive services (1-5)
        $inactiveCount = fake()->numberBetween(1, 5);
        for ($i = 0; $i < $inactiveCount; $i++) {
            $inactiveServices[] = $this->createService(['is_active' => false]);
        }

        // Make API request
        $response = $this->getJson('/api/mobile/consultant-services');

        // Assert success response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status_code' => 200,
            ]);

        // Get returned data
        $data = $response->json('data');
        
        // Assert only active services are returned
        $this->assertCount($activeCount, $data);
        
        // Assert each returned service is active and has required fields
        $activeServiceIds = collect($activeServices)->pluck('id')->toArray();
        $inactiveServiceIds = collect($inactiveServices)->pluck('id')->toArray();
        
        foreach ($data as $serviceData) {
            // Assert required fields exist
            $this->assertArrayHasKey('id', $serviceData);
            $this->assertArrayHasKey('icon_url', $serviceData);
            $this->assertArrayHasKey('title', $serviceData);
            $this->assertArrayHasKey('description', $serviceData);
            
            // Assert only these 4 fields are returned (no extra fields)
            $this->assertCount(4, $serviceData, 'List response should only contain 4 fields');
            
            // Assert service is from active services
            $this->assertContains($serviceData['id'], $activeServiceIds);
            
            // Assert service is NOT from inactive services
            $this->assertNotContains($serviceData['id'], $inactiveServiceIds);
        }
    }

    /**
     * Property: When no active services exist, the API returns an empty list with success response.
     * 
     * **Validates: Requirements 6.4**
     */
    public function test_services_list_returns_empty_when_no_active_services(): void
    {
        // Create only inactive services
        $inactiveCount = fake()->numberBetween(1, 3);
        for ($i = 0; $i < $inactiveCount; $i++) {
            $this->createService(['is_active' => false]);
        }

        // Make API request
        $response = $this->getJson('/api/mobile/consultant-services');

        // Assert success response with empty data
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'data' => [],
            ]);
    }

    /**
     * Property: Services list returns correct fields for each service.
     * 
     * **Validates: Requirements 6.2**
     */
    public function test_services_list_returns_correct_fields(): void
    {
        $service = $this->createService([
            'title' => 'استشارة قانونية',
            'description' => 'استشارة قانونية شاملة',
            'icon_path' => 'consultant-services/icons/test.png',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/mobile/consultant-services');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        
        $serviceData = $data[0];
        
        // Assert exact fields
        $this->assertEquals($service->id, $serviceData['id']);
        $this->assertEquals($service->title, $serviceData['title']);
        $this->assertEquals($service->description, $serviceData['description']);
        $this->assertNotNull($serviceData['icon_url']);
        
        // Assert no extra fields
        $this->assertArrayNotHasKey('price', $serviceData);
        $this->assertArrayNotHasKey('duration_minutes', $serviceData);
        $this->assertArrayNotHasKey('consultation_method', $serviceData);
        $this->assertArrayNotHasKey('consultant', $serviceData);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 9: Service Details Returns Complete Information
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: For any existing active service, the details response must contain:
     * complete service info, service details (includes, target_audience, deliverables),
     * and consultant info (avatar, first_name, last_name, consultation_type_name, experiences).
     * 
     * **Validates: Requirements 7.1, 7.2, 7.3, 7.4, 7.5**
     */
    public function test_service_details_returns_complete_information(): void
    {
        // Add consultant experiences
        $this->addConsultantExperiences();
        
        // Create active service with all details
        $service = $this->createService([
            'title' => 'استشارة قانونية',
            'description' => 'استشارة قانونية شاملة',
            'price' => 150.00,
            'duration_minutes' => 60,
            'consultation_method' => 'video',
            'delivery_time' => 'خلال 24 ساعة',
            'is_active' => true,
        ]);
        
        $this->addServiceDetails($service);

        // Make API request
        $response = $this->getJson("/api/mobile/consultant-services/{$service->id}");

        // Assert success response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status_code' => 200,
            ]);

        $data = $response->json('data');

        // Assert service fields (Requirements 7.2)
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('icon_url', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('duration_minutes', $data);
        $this->assertArrayHasKey('consultation_method', $data);
        $this->assertArrayHasKey('delivery_time', $data);

        // Assert service details (Requirements 7.3)
        $this->assertArrayHasKey('includes', $data);
        $this->assertArrayHasKey('target_audience', $data);
        $this->assertArrayHasKey('deliverables', $data);
        
        $this->assertIsArray($data['includes']);
        $this->assertIsArray($data['target_audience']);
        $this->assertIsArray($data['deliverables']);
        
        $this->assertContains('مراجعة المستندات', $data['includes']);
        $this->assertContains('الشركات الناشئة', $data['target_audience']);
        $this->assertContains('تقرير استشاري', $data['deliverables']);

        // Assert consultant information (Requirements 7.4)
        $this->assertArrayHasKey('consultant', $data);
        $this->assertNotNull($data['consultant']);
        
        $consultant = $data['consultant'];
        $this->assertArrayHasKey('avatar', $consultant);
        $this->assertArrayHasKey('first_name', $consultant);
        $this->assertArrayHasKey('last_name', $consultant);
        $this->assertArrayHasKey('consultation_type_name', $consultant);
        $this->assertArrayHasKey('experiences', $consultant);
        
        $this->assertEquals('أحمد', $consultant['first_name']);
        $this->assertEquals('محمد', $consultant['last_name']);
        $this->assertEquals('استشارات قانونية', $consultant['consultation_type_name']);

        // Assert experiences structure (Requirements 7.5)
        $this->assertIsArray($consultant['experiences']);
        $this->assertNotEmpty($consultant['experiences']);
        
        foreach ($consultant['experiences'] as $experience) {
            $this->assertArrayHasKey('name', $experience);
            $this->assertArrayHasKey('organization', $experience);
            $this->assertArrayHasKey('years', $experience);
        }
    }

    /**
     * Property: If the service_id does not exist, the API returns a 404 error response.
     * 
     * **Validates: Requirements 7.6**
     */
    public function test_service_details_returns_404_for_nonexistent_service(): void
    {
        // Use a non-existent ID
        $nonExistentId = 99999;

        $response = $this->getJson("/api/mobile/consultant-services/{$nonExistentId}");

        $response->assertStatus(404);
    }

    /**
     * Property: If the service is not active, the API returns a 404 error response.
     * 
     * **Validates: Requirements 7.7**
     */
    public function test_service_details_returns_404_for_inactive_service(): void
    {
        // Create inactive service
        $service = $this->createService(['is_active' => false]);

        $response = $this->getJson("/api/mobile/consultant-services/{$service->id}");

        $response->assertStatus(404);
    }

    /**
     * Property: Service details returns correct values for all fields.
     * 
     * **Validates: Requirements 7.1, 7.2**
     */
    public function test_service_details_returns_correct_values(): void
    {
        $service = $this->createService([
            'title' => 'استشارة قانونية متخصصة',
            'description' => 'وصف تفصيلي للخدمة',
            'price' => 250.50,
            'duration_minutes' => 90,
            'consultation_method' => 'audio',
            'delivery_time' => 'خلال 48 ساعة',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/mobile/consultant-services/{$service->id}");

        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        $this->assertEquals($service->id, $data['id']);
        $this->assertEquals('استشارة قانونية متخصصة', $data['title']);
        $this->assertEquals('وصف تفصيلي للخدمة', $data['description']);
        $this->assertEquals('250.50', $data['price']);
        $this->assertEquals(90, $data['duration_minutes']);
        $this->assertEquals('audio', $data['consultation_method']);
        $this->assertEquals('خلال 48 ساعة', $data['delivery_time']);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 10: Pagination Works Correctly
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: For any valid per_page value, the API list must return the specified 
     * number of items with correct pagination info.
     * 
     * **Validates: Requirements 6.5**
     */
    public function test_pagination_works_correctly(): void
    {
        // Create 15 active services
        $totalServices = 15;
        for ($i = 0; $i < $totalServices; $i++) {
            $this->createService(['is_active' => true]);
        }

        // Test with different per_page values
        $perPageValues = [5, 10, 15, 20];
        
        foreach ($perPageValues as $perPage) {
            $response = $this->getJson("/api/mobile/consultant-services?per_page={$perPage}");

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'status_code' => 200,
                ]);

            // Assert pagination info exists
            $response->assertJsonStructure([
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);

            $pagination = $response->json('pagination');
            $data = $response->json('data');

            // Assert pagination values
            $this->assertEquals(1, $pagination['current_page']);
            $this->assertEquals($perPage, $pagination['per_page']);
            $this->assertEquals($totalServices, $pagination['total']);
            $this->assertEquals(ceil($totalServices / $perPage), $pagination['last_page']);

            // Assert correct number of items returned
            $expectedCount = min($perPage, $totalServices);
            $this->assertCount($expectedCount, $data);
        }
    }

    /**
     * Property: Pagination returns correct items for different pages.
     * 
     * **Validates: Requirements 6.5**
     */
    public function test_pagination_returns_correct_items_per_page(): void
    {
        // Create 10 active services
        $services = [];
        for ($i = 0; $i < 10; $i++) {
            $services[] = $this->createService(['is_active' => true]);
        }

        $perPage = 3;
        
        // Get first page
        $response1 = $this->getJson("/api/mobile/consultant-services?per_page={$perPage}&page=1");
        $response1->assertStatus(200);
        $page1Data = $response1->json('data');
        $this->assertCount(3, $page1Data);
        
        // Get second page
        $response2 = $this->getJson("/api/mobile/consultant-services?per_page={$perPage}&page=2");
        $response2->assertStatus(200);
        $page2Data = $response2->json('data');
        $this->assertCount(3, $page2Data);
        
        // Get third page
        $response3 = $this->getJson("/api/mobile/consultant-services?per_page={$perPage}&page=3");
        $response3->assertStatus(200);
        $page3Data = $response3->json('data');
        $this->assertCount(3, $page3Data);
        
        // Get fourth page (should have only 1 item)
        $response4 = $this->getJson("/api/mobile/consultant-services?per_page={$perPage}&page=4");
        $response4->assertStatus(200);
        $page4Data = $response4->json('data');
        $this->assertCount(1, $page4Data);

        // Assert no duplicate IDs across pages
        $allIds = array_merge(
            array_column($page1Data, 'id'),
            array_column($page2Data, 'id'),
            array_column($page3Data, 'id'),
            array_column($page4Data, 'id')
        );
        
        $this->assertCount(10, $allIds);
        $this->assertCount(10, array_unique($allIds), 'No duplicate IDs should exist across pages');
    }

    /**
     * Property: Default pagination uses per_page = 10.
     * 
     * **Validates: Requirements 6.5**
     */
    public function test_default_pagination_uses_10_per_page(): void
    {
        // Create 15 active services
        for ($i = 0; $i < 15; $i++) {
            $this->createService(['is_active' => true]);
        }

        // Request without per_page parameter
        $response = $this->getJson('/api/mobile/consultant-services');

        $response->assertStatus(200);
        
        $pagination = $response->json('pagination');
        $data = $response->json('data');

        // Assert default per_page is 10
        $this->assertEquals(10, $pagination['per_page']);
        $this->assertCount(10, $data);
    }

    /**
     * Property: Pagination info is correct when total items is less than per_page.
     * 
     * **Validates: Requirements 6.5**
     */
    public function test_pagination_with_fewer_items_than_per_page(): void
    {
        // Create only 3 active services
        for ($i = 0; $i < 3; $i++) {
            $this->createService(['is_active' => true]);
        }

        $response = $this->getJson('/api/mobile/consultant-services?per_page=10');

        $response->assertStatus(200);
        
        $pagination = $response->json('pagination');
        $data = $response->json('data');

        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(10, $pagination['per_page']);
        $this->assertEquals(3, $pagination['total']);
        $this->assertEquals(1, $pagination['last_page']);
        $this->assertCount(3, $data);
    }

    /**
     * Property: Empty page returns empty data with correct pagination.
     * 
     * **Validates: Requirements 6.5**
     */
    public function test_pagination_empty_page_returns_empty_data(): void
    {
        // Create 5 active services
        for ($i = 0; $i < 5; $i++) {
            $this->createService(['is_active' => true]);
        }

        // Request page beyond available data
        $response = $this->getJson('/api/mobile/consultant-services?per_page=10&page=2');

        $response->assertStatus(200);
        
        $pagination = $response->json('pagination');
        $data = $response->json('data');

        $this->assertEquals(2, $pagination['current_page']);
        $this->assertEquals(5, $pagination['total']);
        $this->assertEmpty($data);
    }
}
