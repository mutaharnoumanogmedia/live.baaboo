<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

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
        // all permission to admin

        // create 500 users with role user
        for ($i = 1; $i <= 50; $i++) {

            $user = User::create([
                'name' => "player{$i}",         // username
                'email' => "p{$i}@baaboo.com",
                'password' => Hash::make('password123'), // or random: Str::random(10)
            ]);
            $user->assignRole('user');
        }
    }
}
