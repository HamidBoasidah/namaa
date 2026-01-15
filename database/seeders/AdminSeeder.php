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
            // Preferentially assign super-admin if available, otherwise fall back to admin
            $superRole = Role::where('name', 'super-admin')->first();
            if ($superRole) {
                $main->assignRole($superRole->name);
            } else {
                $main->assignRole($adminRole->name);
            }
        }

        // Ensure the record with ID = 1 (if exists) has the super-admin role
        $firstAdmin = Admin::find(1);
        if ($firstAdmin) {
            $firstAdmin->assignRole('super-admin');
        }

        // Create a few random admins
        Admin::factory(5)->create();
    }
}
