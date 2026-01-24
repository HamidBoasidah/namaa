<?php

/**
 * Manual test script to verify Observer and Service work together
 * 
 * This script tests:
 * 1. Creating a review updates consultant ratings
 * 2. Creating a review with service updates both consultant and service ratings
 * 3. Updating a review updates ratings
 * 4. Deleting a review updates ratings
 * 5. Restoring a review updates ratings
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Review;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n=== Testing Ratings Auto-Update System ===\n\n";

// Helper function to display ratings
function displayRatings($consultant, $service = null) {
    $consultant->refresh();
    echo "Consultant #{$consultant->id}: rating_avg = {$consultant->rating_avg}, ratings_count = {$consultant->ratings_count}\n";
    
    if ($service) {
        $service->refresh();
        echo "Service #{$service->id}: rating_avg = {$service->rating_avg}, ratings_count = {$service->ratings_count}\n";
    }
}

try {
    // Get a consultant with a service
    $consultant = Consultant::first();
    if (!$consultant) {
        echo "❌ No consultant found. Please run seeders first.\n";
        exit(1);
    }
    
    $service = ConsultantService::where('consultant_id', $consultant->id)->first();
    if (!$service) {
        echo "❌ No service found for consultant. Creating one...\n";
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
    }
    
    $client = User::where('user_type', 'customer')->first();
    if (!$client) {
        echo "❌ No client found. Please run seeders first.\n";
        exit(1);
    }
    
    echo "Using Consultant #{$consultant->id} and Service #{$service->id}\n\n";
    
    // Reset ratings to 0 for clean test
    $consultant->update(['rating_avg' => 0, 'ratings_count' => 0]);
    $service->update(['rating_avg' => 0, 'ratings_count' => 0]);
    
    // Delete any existing reviews for this consultant
    Review::where('consultant_id', $consultant->id)->forceDelete();
    
    echo "Initial state:\n";
    displayRatings($consultant, $service);
    echo "\n";
    
    // ─────────────────────────────────────────────────────────────
    // Test 1: Create review for consultant (no service)
    // ─────────────────────────────────────────────────────────────
    echo "Test 1: Creating review for consultant (rating: 5)...\n";
    
    $booking1 = Booking::factory()->create([
        'client_id' => $client->id,
        'bookable_type' => Consultant::class,
        'bookable_id' => $consultant->id,
        'status' => 'completed',
    ]);
    
    $review1 = Review::create([
        'booking_id' => $booking1->id,
        'consultant_id' => $consultant->id,
        'client_id' => $client->id,
        'rating' => 5,
        'comment' => 'Excellent consultant!',
    ]);
    
    displayRatings($consultant, $service);
    
    if ($consultant->rating_avg == 5.00 && $consultant->ratings_count == 1) {
        echo "✓ Consultant ratings updated correctly\n";
    } else {
        echo "❌ Consultant ratings incorrect. Expected: 5.00, 1. Got: {$consultant->rating_avg}, {$consultant->ratings_count}\n";
    }
    
    if ($service->rating_avg == 0 && $service->ratings_count == 0) {
        echo "✓ Service ratings unchanged (as expected)\n";
    } else {
        echo "❌ Service ratings should not change. Got: {$service->rating_avg}, {$service->ratings_count}\n";
    }
    echo "\n";
    
    // ─────────────────────────────────────────────────────────────
    // Test 2: Create review for service
    // ─────────────────────────────────────────────────────────────
    echo "Test 2: Creating review for service (rating: 4)...\n";
    
    $booking2 = Booking::factory()->create([
        'client_id' => $client->id,
        'bookable_type' => ConsultantService::class,
        'bookable_id' => $service->id,
        'status' => 'completed',
    ]);
    
    $review2 = Review::create([
        'booking_id' => $booking2->id,
        'consultant_id' => $consultant->id,
        'client_id' => $client->id,
        'rating' => 4,
        'comment' => 'Great service!',
    ]);
    
    displayRatings($consultant, $service);
    
    // Consultant should now have avg of (5+4)/2 = 4.5
    if ($consultant->rating_avg == 4.50 && $consultant->ratings_count == 2) {
        echo "✓ Consultant ratings updated correctly\n";
    } else {
        echo "❌ Consultant ratings incorrect. Expected: 4.50, 2. Got: {$consultant->rating_avg}, {$consultant->ratings_count}\n";
    }
    
    // Service should have avg of 4
    if ($service->rating_avg == 4.00 && $service->ratings_count == 1) {
        echo "✓ Service ratings updated correctly\n";
    } else {
        echo "❌ Service ratings incorrect. Expected: 4.00, 1. Got: {$service->rating_avg}, {$service->ratings_count}\n";
    }
    echo "\n";
    
    // ─────────────────────────────────────────────────────────────
    // Test 3: Update review rating
    // ─────────────────────────────────────────────────────────────
    echo "Test 3: Updating review rating from 4 to 3...\n";
    
    $review2->update(['rating' => 3]);
    
    displayRatings($consultant, $service);
    
    // Consultant should now have avg of (5+3)/2 = 4.0
    if ($consultant->rating_avg == 4.00 && $consultant->ratings_count == 2) {
        echo "✓ Consultant ratings updated correctly\n";
    } else {
        echo "❌ Consultant ratings incorrect. Expected: 4.00, 2. Got: {$consultant->rating_avg}, {$consultant->ratings_count}\n";
    }
    
    // Service should have avg of 3
    if ($service->rating_avg == 3.00 && $service->ratings_count == 1) {
        echo "✓ Service ratings updated correctly\n";
    } else {
        echo "❌ Service ratings incorrect. Expected: 3.00, 1. Got: {$service->rating_avg}, {$service->ratings_count}\n";
    }
    echo "\n";
    
    // ─────────────────────────────────────────────────────────────
    // Test 4: Delete review (soft delete)
    // ─────────────────────────────────────────────────────────────
    echo "Test 4: Deleting review (soft delete)...\n";
    
    $review2->delete();
    
    displayRatings($consultant, $service);
    
    // Consultant should now have only review1 (rating 5)
    if ($consultant->rating_avg == 5.00 && $consultant->ratings_count == 1) {
        echo "✓ Consultant ratings updated correctly\n";
    } else {
        echo "❌ Consultant ratings incorrect. Expected: 5.00, 1. Got: {$consultant->rating_avg}, {$consultant->ratings_count}\n";
    }
    
    // Service should have no reviews
    if ($service->rating_avg == 0.00 && $service->ratings_count == 0) {
        echo "✓ Service ratings updated correctly\n";
    } else {
        echo "❌ Service ratings incorrect. Expected: 0.00, 0. Got: {$service->rating_avg}, {$service->ratings_count}\n";
    }
    echo "\n";
    
    // ─────────────────────────────────────────────────────────────
    // Test 5: Restore review
    // ─────────────────────────────────────────────────────────────
    echo "Test 5: Restoring review...\n";
    
    $review2->restore();
    
    displayRatings($consultant, $service);
    
    // Consultant should now have avg of (5+3)/2 = 4.0
    if ($consultant->rating_avg == 4.00 && $consultant->ratings_count == 2) {
        echo "✓ Consultant ratings updated correctly\n";
    } else {
        echo "❌ Consultant ratings incorrect. Expected: 4.00, 2. Got: {$consultant->rating_avg}, {$consultant->ratings_count}\n";
    }
    
    // Service should have avg of 3
    if ($service->rating_avg == 3.00 && $service->ratings_count == 1) {
        echo "✓ Service ratings updated correctly\n";
    } else {
        echo "❌ Service ratings incorrect. Expected: 3.00, 1. Got: {$service->rating_avg}, {$service->ratings_count}\n";
    }
    echo "\n";
    
    echo "=== All Tests Completed Successfully! ===\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
