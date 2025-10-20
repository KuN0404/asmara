<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Buat Faker Indonesia
        $faker = Faker::create('id_ID');

        // ✅ Super Admin
        $superAdminUser = User::create([
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'whatsapp_number' => '081234567890',
            'address' => 'Bandar Lampung',
        ]);
        $superAdminUser->assignRole('super_admin');

        // ✅ Admin
        $adminUser = User::create([
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'whatsapp_number' => '081234567891',
            'address' => 'Bandar Lampung',
        ]);
        $adminUser->assignRole('admin');

        // ✅ Staff
        $staffUser = User::create([
            'username' => 'staff',
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
            'whatsapp_number' => '081234567892',
            'address' => 'Bandar Lampung',
        ]);
        $staffUser->assignRole('staff');

        // ✅ Tambah 20 user random
        for ($i = 1; $i <= 20; $i++) {
            $user = User::create([
                'username' => 'user' . $i,
                'name' => $faker->name,
                'email' => 'user' . $i . '@example.com',
                'password' => bcrypt('password'),
                'whatsapp_number' => '08' . $faker->numerify('##########'),
                'address' => $faker->city,
            ]);

            // Bisa kasih role default misal "staff"
            $user->assignRole('staff');
        }
    }
}
