<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersByRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // Create Admin User
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@baaboo.com',
            'password' => bcrypt('baaboo123'),
        ]);
        $admin->assignRole('admin');

        //create 500 users with role user 
        User::factory(1000)->create()->each(function ($user) {
            $user->assignRole('user');
        });
    }
}
