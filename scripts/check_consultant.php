<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get user by email or ID
$email = $argv[1] ?? null;

if (!$email) {
    echo "Usage: php scripts/check_consultant.php <email>\n";
    exit(1);
}

$user = \App\Models\User::where('email', $email)->first();

if (!$user) {
    echo "User not found with email: $email\n";
    exit(1);
}

echo "User ID: {$user->id}\n";
echo "User Name: {$user->name}\n";
echo "User Type: {$user->user_type}\n";
echo "Email: {$user->email}\n\n";

$consultant = \App\Models\Consultant::where('user_id', $user->id)->first();

if ($consultant) {
    echo "✓ Consultant record found!\n";
    echo "Consultant ID: {$consultant->id}\n";
    echo "Consultation Type ID: {$consultant->consultation_type_id}\n";
    echo "Is Active: " . ($consultant->is_active ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ No consultant record found for this user!\n";
    echo "\nTo fix this, you need to:\n";
    echo "1. Create a consultant record for this user\n";
    echo "2. Or use a different user account that has a consultant record\n";
}
