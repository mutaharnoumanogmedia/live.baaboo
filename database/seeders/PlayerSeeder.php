<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 50; $i++) {

            User::create([
                'name'     => "p{$i}",         // username
                'email'    => "p{$i}@baaboo.com",
                'password' => Hash::make('password123'), // or random: Str::random(10)
            ]);
        }
    }
}
