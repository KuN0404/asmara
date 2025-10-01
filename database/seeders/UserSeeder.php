<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $user1 = User::create([
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('123'),
        ]);
        $user1->assignRole('super_admin');

        $user2 = User::create([
            'username' => 'admin',
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('123'),
        ]);
        $user2->assignRole('admin');

        $user3 = User::create([
            'username' => 'staff',
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('123'),
        ]);
        $user3->assignRole('staff');
    }
}