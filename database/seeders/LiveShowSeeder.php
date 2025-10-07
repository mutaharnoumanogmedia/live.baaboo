<?php

namespace Database\Seeders;

use App\Models\LiveShow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LiveShowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $imageArray = [
            "https://fastly.picsum.photos/id/54/3264/2176.jpg?hmac=blh020fMeJ5Ru0p-fmXUaOAeYnxpOPHnhJojpzPLN3g",
            "https://fastly.picsum.photos/id/55/4608/3072.jpg?hmac=ahGhylwdN52ULB37deeMZX6T_G7NiERtoPhwydMvUKQ",
            "https://fastly.picsum.photos/id/56/2880/1920.jpg?hmac=BIplhYgNZ9bsjPXYhD0xx6M1yPgmg4HtthKkCeJp6Fk",
            "https://fastly.picsum.photos/id/4/5000/3333.jpg?hmac=ghf06FdmgiD0-G4c9DdNM8RnBIN7BO0-ZGEw47khHP4",
            "https://fastly.picsum.photos/id/57/2448/3264.jpg?hmac=ewraXYesC6HuSEAJsg3Q80bXd1GyJTxekI05Xt9YjfQ",
            "https://fastly.picsum.photos/id/36/4179/2790.jpg?hmac=OCuYYm0PkDCMwxWhrtoSefG5UDir4O0XCcR2x-aSPjs"
        ];

        for ($i = 0; $i < 30; $i++) {
            $image  = $imageArray[array_rand($imageArray)];
            LiveShow::create([
                'title' => fake()->sentence(),
                'description' => fake()->paragraph(),
                'scheduled_at' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
                'stream_link' => "https://www.youtube.com/watch?v=" . (Str::random(15)),
                'status' => fake()->randomElement(['completed']),
                'created_by' => 1, // Assuming user with ID 1 exists
                'host_name' => fake()->name(),
                'prize_amount' => fake()->randomFloat(2, 0, 500),
                'currency' => 'EUR',
                'thumbnail' => $image,
                'banner' => $image,
            ]);
        }


        for ($i = 0; $i < 20; $i++) {
            $image  = $imageArray[array_rand($imageArray)];
            LiveShow::create([
                'title' => fake()->sentence(),
                'description' => fake()->paragraph(),
                'scheduled_at' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d H:i:s'),
                'stream_link' => "https://www.youtube.com/watch?v=" . (Str::random(15)),
                'status' => fake()->randomElement(['scheduled']),
                'created_by' => 1, // Assuming user with ID 1 exists
                'host_name' => fake()->name(),
                'prize_amount' => fake()->randomFloat(2, 0, 500),
                'currency' => 'EUR',
                'thumbnail' => $image,
                'banner' => $image,
            ]);
        }
    }
}
