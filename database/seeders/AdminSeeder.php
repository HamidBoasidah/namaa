<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure there's at least one admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => json_encode(['en' => 'Admin', 'ar' => 'مشرف'])]);

        // Create a main admin account if not exists
        $mainEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $main = Admin::where('email', $mainEmail)->first();
        if (! $main) {
            $main = Admin::factory()->create([
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'email' => $mainEmail,
                'is_active' => true,
            ]);
            $main->assignRole($adminRole->name);
        }

        // Create a few random admins
        Admin::factory(5)->create();
    }
}
