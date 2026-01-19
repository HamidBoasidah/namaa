<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\ConsultationType;
use App\Models\User;
use App\Services\ConsultantServicesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Property tests for ConsultantServicesService Icon Management
 * 
 * Feature: consultant-services-api
 * 
 * @property Property 3: Icon Upload Creates File
 * @property Property 4: Icon Replacement Deletes Old File
 * @property Property 5: Remove Icon Flag Deletes File
 * @property Property 6: Service Deletion Cleans Up Icon
 * @validates Requirements 3.1, 3.2, 3.3, 3.4
 */
class ConsultantServicesServiceIconTest extends TestCase
{
    use RefreshDatabase;

    protected ConsultantServicesService $service;
    protected Consultant $consultant;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->service = app(ConsultantServicesService::class);
        
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
     * Helper to create a valid service attributes array
     */
    protected function createServiceAttributes(array $overrides = []): array
    {
        return array_merge([
            'consultant_id' => $this->consultant->id,
            'category_id' => $this->category->id,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'consultation_method' => fake()->randomElement(['video', 'audio', 'text']),
            'is_active' => true,
        ], $overrides);
    }

    /**
     * Helper to create a fake uploaded image file
     */
    protected function createFakeIcon(string $extension = 'png'): UploadedFile
    {
        return UploadedFile::fake()->image("icon.{$extension}", 100, 100);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 3: Icon Upload Creates File
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: For any valid image file, when creating a service with that file,
     * the file must be saved in storage and icon_path must point to an existing file.
     * 
     * **Validates: Requirements 3.1**
     */
    public function test_icon_upload_creates_file(): void
    {
        // Generate random valid image extensions
        $validExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
        
        foreach ($validExtensions as $extension) {
            $icon = $this->createFakeIcon($extension);
            
            $attributes = $this->createServiceAttributes([
                'icon' => $icon,
            ]);
            
            $service = $this->service->create($attributes);
            
            // Assert icon_path is set
            $this->assertNotNull($service->icon_path, "icon_path should be set for {$extension} file");
            
            // Assert file exists in storage
            Storage::disk('public')->assertExists($service->icon_path);
            
            // Assert icon_path starts with correct directory
            $this->assertStringStartsWith('consultant-services/icons/', $service->icon_path);
            
            // Assert file has correct extension
            $this->assertStringEndsWith(".{$extension}", $service->icon_path);
        }
    }

    /**
     * Property: Creating a service without an icon should not create any file
     * and icon_path should be null.
     * 
     * **Validates: Requirements 3.1**
     */
    public function test_service_without_icon_has_null_icon_path(): void
    {
        $attributes = $this->createServiceAttributes();
        
        $service = $this->service->create($attributes);
        
        // Assert icon_path is null
        $this->assertNull($service->icon_path);
        
        // Assert no files were created in the icons directory
        $files = Storage::disk('public')->files('consultant-services/icons');
        $this->assertEmpty($files, 'No icon files should be created when no icon is provided');
    }

    // ─────────────────────────────────────────────────────────────
    // Property 4: Icon Replacement Deletes Old File
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: For any service with an existing icon, when updating with a new icon,
     * the old file must be deleted and the new file must be saved.
     * 
     * **Validates: Requirements 3.2**
     */
    public function test_icon_replacement_deletes_old_file(): void
    {
        // Create service with initial icon
        $oldIcon = $this->createFakeIcon('png');
        $attributes = $this->createServiceAttributes([
            'icon' => $oldIcon,
        ]);
        
        $service = $this->service->create($attributes);
        $oldIconPath = $service->icon_path;
        
        // Verify old icon exists
        Storage::disk('public')->assertExists($oldIconPath);
        
        // Update with new icon
        $newIcon = $this->createFakeIcon('jpg');
        $updatedService = $this->service->update($service->id, [
            'icon' => $newIcon,
        ]);
        
        // Assert old file is deleted
        Storage::disk('public')->assertMissing($oldIconPath);
        
        // Assert new file exists
        $this->assertNotNull($updatedService->icon_path);
        Storage::disk('public')->assertExists($updatedService->icon_path);
        
        // Assert paths are different
        $this->assertNotEquals($oldIconPath, $updatedService->icon_path);
        
        // Assert new file has correct extension
        $this->assertStringEndsWith('.jpg', $updatedService->icon_path);
    }

    /**
     * Property: Updating a service without an icon should not affect existing icon.
     * 
     * **Validates: Requirements 3.2**
     */
    public function test_update_without_icon_preserves_existing_icon(): void
    {
        // Create service with icon
        $icon = $this->createFakeIcon('png');
        $attributes = $this->createServiceAttributes([
            'icon' => $icon,
        ]);
        
        $service = $this->service->create($attributes);
        $originalIconPath = $service->icon_path;
        
        // Update without icon
        $updatedService = $this->service->update($service->id, [
            'title' => 'Updated Title',
        ]);
        
        // Assert icon_path is unchanged
        $this->assertEquals($originalIconPath, $updatedService->icon_path);
        
        // Assert file still exists
        Storage::disk('public')->assertExists($originalIconPath);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 5: Remove Icon Flag Deletes File
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: For any service with an existing icon, when updating with remove_icon = true,
     * the file must be deleted and icon_path must be set to null.
     * 
     * **Validates: Requirements 3.3**
     */
    public function test_remove_icon_flag_deletes_file(): void
    {
        // Create service with icon
        $icon = $this->createFakeIcon('png');
        $attributes = $this->createServiceAttributes([
            'icon' => $icon,
        ]);
        
        $service = $this->service->create($attributes);
        $iconPath = $service->icon_path;
        
        // Verify icon exists
        Storage::disk('public')->assertExists($iconPath);
        
        // Update with remove_icon flag
        $updatedService = $this->service->update($service->id, [
            'remove_icon' => true,
        ]);
        
        // Assert file is deleted
        Storage::disk('public')->assertMissing($iconPath);
        
        // Assert icon_path is null
        $this->assertNull($updatedService->icon_path);
    }

    /**
     * Property: remove_icon flag on service without icon should not cause errors.
     * 
     * **Validates: Requirements 3.3**
     */
    public function test_remove_icon_flag_on_service_without_icon(): void
    {
        // Create service without icon
        $attributes = $this->createServiceAttributes();
        $service = $this->service->create($attributes);
        
        // Update with remove_icon flag - should not throw
        $updatedService = $this->service->update($service->id, [
            'remove_icon' => true,
        ]);
        
        // Assert icon_path is still null
        $this->assertNull($updatedService->icon_path);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 6: Service Deletion Cleans Up Icon
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: For any service with an icon, when the service is deleted,
     * the icon file must be deleted from storage.
     * 
     * **Validates: Requirements 3.4**
     */
    public function test_service_deletion_cleans_up_icon(): void
    {
        // Create service with icon
        $icon = $this->createFakeIcon('png');
        $attributes = $this->createServiceAttributes([
            'icon' => $icon,
        ]);
        
        $service = $this->service->create($attributes);
        $iconPath = $service->icon_path;
        $serviceId = $service->id;
        
        // Verify icon exists
        Storage::disk('public')->assertExists($iconPath);
        
        // Delete service
        $this->service->delete($serviceId);
        
        // Assert file is deleted
        Storage::disk('public')->assertMissing($iconPath);
        
        // Assert service is deleted
        $this->assertNull(ConsultantService::find($serviceId));
    }

    /**
     * Property: Deleting a service without an icon should not cause errors.
     * 
     * **Validates: Requirements 3.4**
     */
    public function test_service_deletion_without_icon(): void
    {
        // Create service without icon
        $attributes = $this->createServiceAttributes();
        $service = $this->service->create($attributes);
        $serviceId = $service->id;
        
        // Delete service - should not throw
        $result = $this->service->delete($serviceId);
        
        // Assert service is deleted
        $this->assertTrue($result);
        $this->assertNull(ConsultantService::find($serviceId));
    }

    /**
     * Property: Multiple services can have icons and each deletion only removes its own icon.
     * 
     * **Validates: Requirements 3.4**
     */
    public function test_multiple_services_icon_isolation(): void
    {
        // Create multiple services with icons
        $services = [];
        $iconPaths = [];
        
        for ($i = 0; $i < 3; $i++) {
            $icon = $this->createFakeIcon('png');
            $attributes = $this->createServiceAttributes([
                'icon' => $icon,
            ]);
            
            $service = $this->service->create($attributes);
            $services[] = $service;
            $iconPaths[] = $service->icon_path;
        }
        
        // Verify all icons exist
        foreach ($iconPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }
        
        // Delete first service
        $this->service->delete($services[0]->id);
        
        // Assert first icon is deleted
        Storage::disk('public')->assertMissing($iconPaths[0]);
        
        // Assert other icons still exist
        Storage::disk('public')->assertExists($iconPaths[1]);
        Storage::disk('public')->assertExists($iconPaths[2]);
    }
}
