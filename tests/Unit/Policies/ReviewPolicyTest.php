<?php

namespace Tests\Unit\Policies;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Review;
use App\Models\User;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property tests for ReviewPolicy
 * 
 * Feature: reviews-ratings-backend
 * 
 * @property Property 5: Ownership Authorization
 * @validates Requirements 6.2, 6.3, 6.6
 * 
 * @property Property 6: Booking Ownership for Creation
 * @validates Requirements 2.3, 6.1
 */
class ReviewPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ReviewPolicy $policy;
    protected User $client;
    protected User $otherClient;
    protected Consultant $consultant;
    protected Booking $completedBooking;
    protected Review $review;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new ReviewPolicy();
        
        // Create clients
        $this->client = User::factory()->create();
        $this->otherClient = User::factory()->create();
        
        // Create consultant
        $this->consultant = Consultant::factory()->create();
        
        // Create completed booking
        $this->completedBooking = Booking::factory()
            ->forClient($this->client)
            ->forConsultant($this->consultant)
            ->completed()
            ->create();
        
        // Create review
        $this->review = Review::create([
            'booking_id' => $this->completedBooking->id,
            'consultant_id' => $this->completedBooking->consultant_id,
            'client_id' => $this->completedBooking->client_id,
            'rating' => 5,
            'comment' => 'Great service!',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 5: Ownership Authorization
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Review owner can update their own review
     * 
     * Generate reviews with random client_ids and verify that
     * only the owner can update the review.
     * 
     * @validates Requirements 6.2, 6.6
     */
    public function test_owner_can_update_own_review(): void
    {
        // Test with the setup review
        $this->assertTrue(
            $this->policy->update($this->client, $this->review),
            'Review owner should be able to update their review'
        );
        
        // Generate additional test cases with random clients
        for ($i = 0; $i < 3; $i++) {
            $randomClient = User::factory()->create();
            $randomConsultant = Consultant::factory()->create();
            
            $booking = Booking::factory()
                ->forClient($randomClient)
                ->forConsultant($randomConsultant)
                ->completed()
                ->create();
            
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $booking->consultant_id,
                'client_id' => $booking->client_id,
                'rating' => rand(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);
            
            // Owner should be able to update
            $this->assertTrue(
                $this->policy->update($randomClient, $review),
                "Client {$randomClient->id} should be able to update their review {$review->id}"
            );
        }
    }

    /**
     * Property: Non-owner cannot update review
     * 
     * Verify that users who don't own the review cannot update it.
     * 
     * @validates Requirements 6.2, 6.6
     */
    public function test_non_owner_cannot_update_review(): void
    {
        // Other client cannot update
        $this->assertFalse(
            $this->policy->update($this->otherClient, $this->review),
            'Non-owner should not be able to update review'
        );
        
        // Generate additional test cases
        for ($i = 0; $i < 3; $i++) {
            $owner = User::factory()->create();
            $nonOwner = User::factory()->create();
            $consultant = Consultant::factory()->create();
            
            $booking = Booking::factory()
                ->forClient($owner)
                ->forConsultant($consultant)
                ->completed()
                ->create();
            
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $booking->consultant_id,
                'client_id' => $booking->client_id,
                'rating' => rand(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);
            
            // Non-owner should not be able to update
            $this->assertFalse(
                $this->policy->update($nonOwner, $review),
                "Non-owner {$nonOwner->id} should not be able to update review {$review->id} owned by {$owner->id}"
            );
        }
    }

    /**
     * Property: Review owner can delete their own review
     * 
     * Generate reviews with random client_ids and verify that
     * only the owner can delete the review.
     * 
     * @validates Requirements 6.3, 6.6
     */
    public function test_owner_can_delete_own_review(): void
    {
        // Test with the setup review
        $this->assertTrue(
            $this->policy->delete($this->client, $this->review),
            'Review owner should be able to delete their review'
        );
        
        // Generate additional test cases with random clients
        for ($i = 0; $i < 3; $i++) {
            $randomClient = User::factory()->create();
            $randomConsultant = Consultant::factory()->create();
            
            $booking = Booking::factory()
                ->forClient($randomClient)
                ->forConsultant($randomConsultant)
                ->completed()
                ->create();
            
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $booking->consultant_id,
                'client_id' => $booking->client_id,
                'rating' => rand(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);
            
            // Owner should be able to delete
            $this->assertTrue(
                $this->policy->delete($randomClient, $review),
                "Client {$randomClient->id} should be able to delete their review {$review->id}"
            );
        }
    }

    /**
     * Property: Non-owner cannot delete review
     * 
     * Verify that users who don't own the review cannot delete it.
     * 
     * @validates Requirements 6.3, 6.6
     */
    public function test_non_owner_cannot_delete_review(): void
    {
        // Other client cannot delete
        $this->assertFalse(
            $this->policy->delete($this->otherClient, $this->review),
            'Non-owner should not be able to delete review'
        );
        
        // Generate additional test cases
        for ($i = 0; $i < 3; $i++) {
            $owner = User::factory()->create();
            $nonOwner = User::factory()->create();
            $consultant = Consultant::factory()->create();
            
            $booking = Booking::factory()
                ->forClient($owner)
                ->forConsultant($consultant)
                ->completed()
                ->create();
            
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $booking->consultant_id,
                'client_id' => $booking->client_id,
                'rating' => rand(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);
            
            // Non-owner should not be able to delete
            $this->assertFalse(
                $this->policy->delete($nonOwner, $review),
                "Non-owner {$nonOwner->id} should not be able to delete review {$review->id} owned by {$owner->id}"
            );
        }
    }

    /**
     * Property: Ownership check is consistent for update and delete
     * 
     * Verify that the same ownership rules apply to both update and delete.
     * 
     * @validates Requirements 6.2, 6.3, 6.6
     */
    public function test_ownership_consistent_for_update_and_delete(): void
    {
        // Generate multiple test cases
        for ($i = 0; $i < 5; $i++) {
            $owner = User::factory()->create();
            $nonOwner = User::factory()->create();
            $consultant = Consultant::factory()->create();
            
            $booking = Booking::factory()
                ->forClient($owner)
                ->forConsultant($consultant)
                ->completed()
                ->create();
            
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $booking->consultant_id,
                'client_id' => $booking->client_id,
                'rating' => rand(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);
            
            // Owner should have same permissions for update and delete
            $canUpdate = $this->policy->update($owner, $review);
            $canDelete = $this->policy->delete($owner, $review);
            $this->assertEquals($canUpdate, $canDelete, 'Owner should have consistent update/delete permissions');
            $this->assertTrue($canUpdate && $canDelete, 'Owner should be able to both update and delete');
            
            // Non-owner should have same permissions for update and delete
            $canUpdateNonOwner = $this->policy->update($nonOwner, $review);
            $canDeleteNonOwner = $this->policy->delete($nonOwner, $review);
            $this->assertEquals($canUpdateNonOwner, $canDeleteNonOwner, 'Non-owner should have consistent update/delete permissions');
            $this->assertFalse($canUpdateNonOwner && $canDeleteNonOwner, 'Non-owner should not be able to update or delete');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 6: Booking Ownership for Creation
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Only booking owner can create review for that booking
     * 
     * Generate bookings with random client_ids and verify that only
     * the booking owner can create a review.
     * 
     * @validates Requirements 2.3, 6.1
     */
    public function test_booking_owner_can_create_review(): void
    {
        // Test with completed booking
        $this->assertTrue(
            $this->policy->create($this->client, $this->completedBooking),
            'Booking owner should be able to create review for completed booking'
        );
        
        // Generate additional test cases with random clients
        for ($i = 0; $i < 3; $i++) {
            $randomClient = User::factory()->create();
            $randomConsultant = Consultant::factory()->create();
            
            $booking = Booking::factory()
                ->forClient($randomClient)
                ->forConsultant($randomConsultant)
                ->completed()
                ->create();
            
            // Owner should be able to create review
            $this->assertTrue(
                $this->policy->create($randomClient, $booking),
                "Client {$randomClient->id} should be able to create review for their booking {$booking->id}"
            );
        }
    }

    /**
     * Property: Non-booking-owner cannot create review
     * 
     * Verify that users who don't own the booking cannot create a review for it.
     * 
     * @validates Requirements 2.3, 6.1
     */
    public function test_non_booking_owner_cannot_create_review(): void
    {
        // Other client cannot create review for this booking
        $this->assertFalse(
            $this->policy->create($this->otherClient, $this->completedBooking),
            'Non-booking-owner should not be able to create review'
        );
        
        // Generate additional test cases
        for ($i = 0; $i < 3; $i++) {
            $owner = User::factory()->create();
            $nonOwner = User::factory()->create();
            $consultant = Consultant::factory()->create();
            
            $booking = Booking::factory()
                ->forClient($owner)
                ->forConsultant($consultant)
                ->completed()
                ->create();
            
            // Non-owner should not be able to create review
            $this->assertFalse(
                $this->policy->create($nonOwner, $booking),
                "Non-owner {$nonOwner->id} should not be able to create review for booking {$booking->id} owned by {$owner->id}"
            );
        }
    }

    /**
     * Property: Only completed bookings allow review creation
     * 
     * Verify that the create policy also checks booking status.
     * 
     * @validates Requirements 6.1
     */
    public function test_only_completed_bookings_allow_review_creation(): void
    {
        // Test non-completed statuses
        $nonCompletedStatuses = [
            Booking::STATUS_PENDING,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_CANCELLED,
            Booking::STATUS_EXPIRED,
        ];
        
        foreach ($nonCompletedStatuses as $status) {
            $booking = Booking::factory()
                ->forClient($this->client)
                ->forConsultant($this->consultant)
                ->create(['status' => $status]);
            
            // Even the owner cannot create review for non-completed booking
            $this->assertFalse(
                $this->policy->create($this->client, $booking),
                "Review creation should not be allowed for booking with status '{$status}'"
            );
        }
        
        // Completed booking should allow review creation
        $completedBooking = Booking::factory()
            ->forClient($this->client)
            ->forConsultant($this->consultant)
            ->completed()
            ->create();
        
        $this->assertTrue(
            $this->policy->create($this->client, $completedBooking),
            'Review creation should be allowed for completed booking'
        );
    }

    /**
     * Property: Create authorization requires both ownership and completed status
     * 
     * Verify that both conditions must be met for review creation.
     * 
     * @validates Requirements 2.3, 6.1
     */
    public function test_create_requires_ownership_and_completed_status(): void
    {
        // Generate test cases
        for ($i = 0; $i < 3; $i++) {
            $owner = User::factory()->create();
            $nonOwner = User::factory()->create();
            $consultant = Consultant::factory()->create();
            
            // Completed booking - owner can create, non-owner cannot
            $completedBooking = Booking::factory()
                ->forClient($owner)
                ->forConsultant($consultant)
                ->completed()
                ->create();
            
            $this->assertTrue(
                $this->policy->create($owner, $completedBooking),
                'Owner should be able to create review for completed booking'
            );
            
            $this->assertFalse(
                $this->policy->create($nonOwner, $completedBooking),
                'Non-owner should not be able to create review even for completed booking'
            );
            
            // Non-completed booking - even owner cannot create
            $pendingBooking = Booking::factory()
                ->forClient($owner)
                ->forConsultant($consultant)
                ->create(['status' => Booking::STATUS_PENDING]);
            
            $this->assertFalse(
                $this->policy->create($owner, $pendingBooking),
                'Owner should not be able to create review for non-completed booking'
            );
            
            $this->assertFalse(
                $this->policy->create($nonOwner, $pendingBooking),
                'Non-owner should not be able to create review for non-completed booking'
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // View Authorization
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Reviews are publicly viewable
     * 
     * Verify that any user (or no user) can view reviews.
     * 
     * @validates Requirements 6.4
     */
    public function test_reviews_are_publicly_viewable(): void
    {
        // Authenticated users can view
        $this->assertTrue(
            $this->policy->view($this->client, $this->review),
            'Review owner should be able to view their review'
        );
        
        $this->assertTrue(
            $this->policy->view($this->otherClient, $this->review),
            'Other users should be able to view reviews'
        );
        
        // Unauthenticated (null user) can view
        $this->assertTrue(
            $this->policy->view(null, $this->review),
            'Unauthenticated users should be able to view reviews'
        );
        
        // Test with multiple random reviews
        for ($i = 0; $i < 3; $i++) {
            $randomClient = User::factory()->create();
            $randomConsultant = Consultant::factory()->create();
            
            $booking = Booking::factory()
                ->forClient($randomClient)
                ->forConsultant($randomConsultant)
                ->completed()
                ->create();
            
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $booking->consultant_id,
                'client_id' => $booking->client_id,
                'rating' => rand(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);
            
            // Any user can view
            $anyUser = User::factory()->create();
            $this->assertTrue(
                $this->policy->view($anyUser, $review),
                'Any user should be able to view any review'
            );
            
            // Null user can view
            $this->assertTrue(
                $this->policy->view(null, $review),
                'Unauthenticated users should be able to view any review'
            );
        }
    }
}
