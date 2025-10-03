<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
      $superAdminUser = User::create([
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'whatsapp_number' => '081234567890',
            'address' => 'Bandar Lampung',
        ]);
        $superAdminUser->assignRole('super_admin');

        // Create Admin user
        $adminUser = User::create([
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'whatsapp_number' => '081234567891',
            'address' => 'Bandar Lampung',
        ]);
        $adminUser->assignRole('admin');

        // Create Staff user
        $staffUser = User::create([
            'username' => 'staff',
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
            'whatsapp_number' => '081234567892',
            'address' => 'Bandar Lampung',
        ]);
        $staffUser->assignRole('staff');
    }
}
