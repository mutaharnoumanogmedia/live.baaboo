<?php

namespace Database\Seeders;

use App\Models\LiveShow;
use App\Models\LiveShowQuiz;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Models\UserQuiz;
use App\Models\UserQuizResponse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LiveShowQuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        for ($i = 0; $i < 50; $i++) {
            $liveshowId = LiveShow::inRandomOrder()->first()->id;

            //user live show
            $userId = User::skip(0)->inRandomOrder()->first()->id;

            UserLiveShow::firstOrCreate([
                'user_id' => $userId,
                'live_show_id' => $liveshowId,
            ], [
                'created_at' => now(),
                'updated_at' => now()
            ]);


            // Create between 3 to 8 quizzes for each live show
            for ($j = 0; $j < rand(3, 8); $j++) {

                $liveShowQuiz =  LiveShowQuiz::create([
                    'live_show_id' => $liveshowId,
                    'question' => fake()->sentence(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);



                // Create 4 options for each quiz
                for ($j = 0; $j < 4; $j++) {
                    $liveShowQuiz->options()->create([
                        'option_text' => fake()->word(),
                        'is_correct' => $j === 0, // Make the first option correct for simplicity
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                // Create a UserQuiz entry
                $userQuiz = UserQuiz::create([
                    'user_id' => $userId,
                    'live_show_id' => $liveshowId,
                    'quiz_id' => $liveShowQuiz->id,
                    'total_questions' => 1,
                    'correct_answers' => 0,
                    'score_percentage' => 0,
                    'status' => 'incomplete',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                // Simulate answering the question
                $selectedOption = $liveShowQuiz->options()->inRandomOrder()->first();
                UserQuizResponse::create([
                    'user_quiz_id' => $userQuiz->id,
                    'quiz_option_id' => $selectedOption->id,
                    'quiz_id' => $liveShowQuiz->id,
                    'user_id' => $userId,
                    'is_correct' => $selectedOption->is_correct,
                    'user_response' => $selectedOption->option_text,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
