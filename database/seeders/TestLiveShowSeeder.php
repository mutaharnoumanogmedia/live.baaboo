<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestLiveShowSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Adjust scheduled time as needed
            $scheduledAt = Carbon::parse('2026-01-09 20:00:00');

            // 1) Create Live Show (table name assumed: live_shows)
            $liveShowId = DB::table('live_shows')->insertGetId([
                'title'        => 'Friday Night Mixed Trivia: Geo • Fun • Brands',
                'stream_id'    => 'stream_20260109_001',
                'description'  => 'A fast-paced live quiz with 10 questions across geography, fun facts, and well-known brands.',
                'scheduled_at' => $scheduledAt,
                'status'       => 'scheduled',
                'thumbnail'    => 'https://cdn.example.com/thumbnails/liveshow-trivia-20260109.jpg',
                'stream_link'  => 'https://example.com/live/stream_20260109_001',
                'host_name'    => 'Mutahar Nouman',
                'prize_amount' => 1000.00,
                'currency'     => 'EUR',
                'created_by'   => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // 2) Questions + Options (tables assumed: live_show_quizzes, quiz_options)
            $items = [
                [
                    'question' => 'Which country has the largest land area in the world?',
                    'options'  => [
                        ['text' => 'Canada',         'correct' => false],
                        ['text' => 'Russia',         'correct' => true],
                        ['text' => 'China',          'correct' => false],
                        ['text' => 'United States',  'correct' => false],
                    ],
                ],
                [
                    'question' => 'Which company is best known for the iPhone?',
                    'options'  => [
                        ['text' => 'Apple',   'correct' => true],
                        ['text' => 'Samsung', 'correct' => false],
                        ['text' => 'Nokia',   'correct' => false],
                        ['text' => 'Sony',    'correct' => false],
                    ],
                ],
                [
                    'question' => 'What is the common name for a group of crows?',
                    'options'  => [
                        ['text' => 'A flock',   'correct' => false],
                        ['text' => 'A murder',  'correct' => true],
                        ['text' => 'A pack',    'correct' => false],
                        ['text' => 'A school',  'correct' => false],
                    ],
                ],
                [
                    'question' => 'Which river runs through Paris?',
                    'options'  => [
                        ['text' => 'Thames', 'correct' => false],
                        ['text' => 'Seine',  'correct' => true],
                        ['text' => 'Rhine',  'correct' => false],
                        ['text' => 'Danube', 'correct' => false],
                    ],
                ],
                [
                    'question' => 'Which brand slogan is "Just Do It"?',
                    'options'  => [
                        ['text' => 'Adidas', 'correct' => false],
                        ['text' => 'Nike',   'correct' => true],
                        ['text' => 'Puma',   'correct' => false],
                        ['text' => 'Reebok', 'correct' => false],
                    ],
                ],
                [
                    'question' => 'How many sides does a hexagon have?',
                    'options'  => [
                        ['text' => '5', 'correct' => false],
                        ['text' => '6', 'correct' => true],
                        ['text' => '7', 'correct' => false],
                        ['text' => '8', 'correct' => false],
                    ],
                ],
                [
                    'question' => 'Mount Kilimanjaro is located in which country?',
                    'options'  => [
                        ['text' => 'Kenya',     'correct' => false],
                        ['text' => 'Tanzania',  'correct' => true],
                        ['text' => 'Uganda',    'correct' => false],
                        ['text' => 'Ethiopia',  'correct' => false],
                    ],
                ],
                [
                    'question' => 'Which company is associated with the PlayStation brand?',
                    'options'  => [
                        ['text' => 'Microsoft', 'correct' => false],
                        ['text' => 'Nintendo',  'correct' => false],
                        ['text' => 'Sony',      'correct' => true],
                        ['text' => 'Valve',     'correct' => false],
                    ],
                ],
                [
                    'question' => 'Which planet is known as the Red Planet?',
                    'options'  => [
                        ['text' => 'Venus',   'correct' => false],
                        ['text' => 'Mars',    'correct' => true],
                        ['text' => 'Jupiter', 'correct' => false],
                        ['text' => 'Mercury', 'correct' => false],
                    ],
                ],
                [
                    'question' => 'Which city is the capital of Australia?',
                    'options'  => [
                        ['text' => 'Sydney',    'correct' => false],
                        ['text' => 'Melbourne', 'correct' => false],
                        ['text' => 'Canberra',  'correct' => true],
                        ['text' => 'Perth',     'correct' => false],
                    ],
                ],
            ];

            foreach ($items as $item) {
                $quizId = DB::table('live_show_quizzes')->insertGetId([
                    'live_show_id' => $liveShowId,
                    'question'     => $item['question'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                $optionsPayload = [];
                foreach ($item['options'] as $opt) {
                    $optionsPayload[] = [
                        'quiz_id'     => $quizId,
                        'option_text' => $opt['text'],
                        'is_correct'  => $opt['correct'] ? 1 : 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }

                DB::table('quiz_options')->insert($optionsPayload);
            }
        });
    }
}



/* 
  Assumptions about table names/columns (adjust if yours differ):
  - live_shows: id (AI PK), title, stream_id, description, scheduled_at, status, thumbnail, stream_link,
               host_name, prize_amount, currency, created_by, created_at, updated_at
  - live_show_quizzes: id (AI PK), live_show_id, question, created_at, updated_at
  - quiz_options: id (AI PK), quiz_id, option_text, is_correct, created_at, updated_at
*/

// START TRANSACTION;

// -- 1) Create the Live Show
// INSERT INTO live_shows
//   (title, stream_id, description, scheduled_at, status, thumbnail, stream_link, host_name, prize_amount, currency, created_by, created_at, updated_at)
// VALUES
//   (
//     'Friday Night Mixed Trivia: Geo • Fun • Brands',
//     'stream_20260109_001',
//     'A fast-paced live quiz with 10 questions across geography, fun facts, and well-known brands.',
//     '2026-01-09 20:00:00',
//     'scheduled',
//     'https://cdn.example.com/thumbnails/liveshow-trivia-20260109.jpg',
//     'https://example.com/live/stream_20260109_001',
//     'Alex Morgan',
//     1000.00,
//     'AED',
//     1,
//     NOW(),
//     NOW()
//   );

// SET @live_show_id := LAST_INSERT_ID();

// -- 2) Insert 10 questions + options (4 options each; exactly one correct)

// -- Q1 (Geography)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'Which country has the largest land area in the world?', NOW(), NOW());
// SET @q1 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q1, 'Canada', 0, NOW(), NOW()),
// (@q1, 'Russia', 1, NOW(), NOW()),
// (@q1, 'China', 0, NOW(), NOW()),
// (@q1, 'United States', 0, NOW(), NOW());

// -- Q2 (Brands)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'Which company is best known for the iPhone?', NOW(), NOW());
// SET @q2 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q2, 'Apple', 1, NOW(), NOW()),
// (@q2, 'Samsung', 0, NOW(), NOW()),
// (@q2, 'Nokia', 0, NOW(), NOW()),
// (@q2, 'Sony', 0, NOW(), NOW());

// -- Q3 (Fun)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'What is the common name for a group of crows?', NOW(), NOW());
// SET @q3 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q3, 'A flock', 0, NOW(), NOW()),
// (@q3, 'A murder', 1, NOW(), NOW()),
// (@q3, 'A pack', 0, NOW(), NOW()),
// (@q3, 'A school', 0, NOW(), NOW());

// -- Q4 (Geography)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'Which river runs through Paris?', NOW(), NOW());
// SET @q4 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q4, 'Thames', 0, NOW(), NOW()),
// (@q4, 'Seine', 1, NOW(), NOW()),
// (@q4, 'Rhine', 0, NOW(), NOW()),
// (@q4, 'Danube', 0, NOW(), NOW());

// -- Q5 (Brands)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'Which brand slogan is "Just Do It"?', NOW(), NOW());
// SET @q5 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q5, 'Adidas', 0, NOW(), NOW()),
// (@q5, 'Nike', 1, NOW(), NOW()),
// (@q5, 'Puma', 0, NOW(), NOW()),
// (@q5, 'Reebok', 0, NOW(), NOW());

// -- Q6 (Fun)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'How many sides does a hexagon have?', NOW(), NOW());
// SET @q6 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q6, '5', 0, NOW(), NOW()),
// (@q6, '6', 1, NOW(), NOW()),
// (@q6, '7', 0, NOW(), NOW()),
// (@q6, '8', 0, NOW(), NOW());

// -- Q7 (Geography)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'Mount Kilimanjaro is located in which country?', NOW(), NOW());
// SET @q7 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q7, 'Kenya', 0, NOW(), NOW()),
// (@q7, 'Tanzania', 1, NOW(), NOW()),
// (@q7, 'Uganda', 0, NOW(), NOW()),
// (@q7, 'Ethiopia', 0, NOW(), NOW());

// -- Q8 (Brands)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'Which company is associated with the PlayStation brand?', NOW(), NOW());
// SET @q8 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q8, 'Microsoft', 0, NOW(), NOW()),
// (@q8, 'Nintendo', 0, NOW(), NOW()),
// (@q8, 'Sony', 1, NOW(), NOW()),
// (@q8, 'Valve', 0, NOW(), NOW());

// -- Q9 (Fun)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'Which planet is known as the Red Planet?', NOW(), NOW());
// SET @q9 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q9, 'Venus', 0, NOW(), NOW()),
// (@q9, 'Mars', 1, NOW(), NOW()),
// (@q9, 'Jupiter', 0, NOW(), NOW()),
// (@q9, 'Mercury', 0, NOW(), NOW());

// -- Q10 (Geography)
// INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at)
// VALUES (@live_show_id, 'Which city is the capital of Australia?', NOW(), NOW());
// SET @q10 := LAST_INSERT_ID();

// INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
// (@q10, 'Sydney', 0, NOW(), NOW()),
// (@q10, 'Melbourne', 0, NOW(), NOW()),
// (@q10, 'Canberra', 1, NOW(), NOW()),
// (@q10, 'Perth', 0, NOW(), NOW());

// COMMIT;

// /* Optional: verify inserts */
// -- SELECT * FROM live_shows WHERE id = @live_show_id;
// -- SELECT q.id, q.question FROM live_show_quizzes q WHERE q.live_show_id = @live_show_id;
// -- SELECT o.* FROM quiz_options o
// -- JOIN live_show_quizzes q ON q.id = o.quiz_id
// -- WHERE q.live_show_id = @live_show_id
// -- ORDER BY q.id, o.id;
