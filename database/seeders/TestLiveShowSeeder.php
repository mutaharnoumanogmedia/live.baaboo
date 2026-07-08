<?php

namespace Database\Seeders;

use App\Models\LiveShow;
use App\Models\LiveShowQuiz;
use App\Models\LiveShowWinnerPrize;
use App\Models\User;
use App\Models\UserQuiz;
use App\Models\UserQuizResponse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * Deterministic fixture for the winner-email test suite.
 *
 * It builds a single, fully-formed live show that mirrors how a real show
 * looks once winners have been decided:
 *
 *  - 10 winner prizes: ranks 1-3 are CASH prizes (is_voucher = 0) and ranks
 *    4-10 are VOUCHER prizes (is_voucher = 1), matching the app's real prize
 *    tiers.
 *  - 10 quiz questions (4 options each, first option correct).
 *  - 10 winner players, each with a distinct random total score between 1000
 *    and 2000 points (materialised as real quiz responses so the computed
 *    {@see \App\Models\UserLiveShow::getScoreAttribute()} matches). The highest
 *    scorer takes rank 1, and so on, so the top three are cash winners and the
 *    rest are voucher winners — exactly what
 *    {@see \App\Http\Controllers\Admin\LiveShowController::updateWinners()}
 *    would compute.
 *  - A handful of extra registered (non-winning) players and a couple of users
 *    who never joined the show, so tests can exercise "not a winner" and "not a
 *    participant" paths.
 *
 * Winners are pre-marked (is_winner = true, winner_prize_id set, voucher
 * winners get a discount_code) but the show is left with
 * winners_announced = false so the announce-winners endpoint can still be
 * exercised against it.
 */
class TestLiveShowSeeder extends Seeder
{
    /** Title used by the tests to locate the seeded show. */
    public const SHOW_TITLE = 'Test Winner Show';

    /** Ranks 1..CASH_WINNER_RANKS are cash prizes; the rest are vouchers. */
    public const CASH_WINNER_RANKS = 3;

    /** Total number of prizes / winners for the show. */
    public const TOTAL_PRIZES = 10;

    /** Number of quiz questions on the show. */
    public const TOTAL_QUESTIONS = 10;

    /** Number of extra registered players who do not win. */
    public const EXTRA_PLAYERS = 5;

    public function run(): void
    {
        // Roles + permissions (and their guards) exactly as the real app sets
        // them up, so User::role('admin') / actingAs(..., 'admin') behave.
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $admin = $this->createAdmin();

        $show = $this->createShow($admin);
        $prizes = $this->createPrizes($show);
        $quizzes = $this->createQuizzes($show, $admin);

        $this->createWinners($show, $prizes, $quizzes);
        $this->createExtraPlayers($show, $quizzes);
        $this->createNonParticipants();
    }

    private function createAdmin(): User
    {
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@baaboo.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function createShow(User $admin): LiveShow
    {
        $airedAt = Carbon::now()->subDay()->setTime(18, 0);

        return LiveShow::create([
            'title' => self::SHOW_TITLE,
            'description' => 'Seeded show used by the winner-email test suite.',
            'scheduled_at' => $airedAt,
            'status' => 'completed',
            'is_test_show' => false,
            'host_name' => 'Test Host',
            'prize_amount' => 1000,
            'currency' => 'EUR',
            'max_winners' => self::TOTAL_PRIZES,
            'max_players' => 100,
            'chat_enabled' => true,
            'winners_announced' => false,
            'start_time' => $airedAt,
            'end_time' => (clone $airedAt)->addMinutes(45),
            'created_by' => $admin->id,
        ]);
    }

    /**
     * Ranks 1-3 => cash prizes (is_voucher = 0), ranks 4-10 => voucher prizes
     * (is_voucher = 1).
     *
     * @return Collection<int, LiveShowWinnerPrize>
     */
    private function createPrizes(LiveShow $show): Collection
    {
        $cashPrizes = ['1000.00', '500.00', '250.00'];      // ranks 1-3
        $voucherAmounts = [100, 90, 80, 70, 60, 50, 40];    // ranks 4-10

        $prizes = collect();

        foreach (range(1, self::TOTAL_PRIZES) as $rank) {
            if ($rank <= self::CASH_WINNER_RANKS) {
                $prizes->push(LiveShowWinnerPrize::create([
                    'live_show_id' => $show->id,
                    'rank' => $rank,
                    'prize' => $cashPrizes[$rank - 1],
                    'is_voucher' => false,
                    'voucher_amount' => null,
                ]));

                continue;
            }

            $amount = $voucherAmounts[$rank - self::CASH_WINNER_RANKS - 1];

            $prizes->push(LiveShowWinnerPrize::create([
                'live_show_id' => $show->id,
                'rank' => $rank,
                'prize' => number_format($amount, 2, '.', ''),
                'is_voucher' => true,
                'voucher_amount' => $amount,
            ]));
        }

        return $prizes;
    }

    /**
     * 10 questions, each with 4 options where the first option is correct.
     *
     * @return Collection<int, LiveShowQuiz>
     */
    private function createQuizzes(LiveShow $show, User $admin): Collection
    {
        return collect(range(1, self::TOTAL_QUESTIONS))->map(function (int $n) use ($show, $admin) {
            $quiz = LiveShowQuiz::create([
                'live_show_id' => $show->id,
                'question' => "Test question {$n}?",
                'created_by' => $admin->id,
                'has_shown' => true,
            ]);

            foreach (range(1, 4) as $optionIndex) {
                $quiz->options()->create([
                    'option_text' => "Q{$n} Option {$optionIndex}",
                    'is_correct' => $optionIndex === 1,
                ]);
            }

            return $quiz->load('options');
        });
    }

    /**
     * Create the 10 winners. The highest score becomes rank 1 (cash) and the
     * lowest of the ten becomes rank 10 (voucher).
     *
     * @param  Collection<int, LiveShowWinnerPrize>  $prizes
     * @param  Collection<int, LiveShowQuiz>  $quizzes
     */
    private function createWinners(LiveShow $show, Collection $prizes, Collection $quizzes): void
    {
        // Distinct random scores in [1000, 2000], highest first => rank order.
        $scores = $this->distinctScores(self::TOTAL_PRIZES, 1000, 2000)->sortDesc()->values();

        foreach (range(1, self::TOTAL_PRIZES) as $rank) {
            $score = (int) $scores[$rank - 1];
            $prize = $prizes->firstWhere('rank', $rank);
            $isVoucher = (bool) $prize->is_voucher;

            $user = User::create([
                'name' => "Winner {$rank}",
                'email' => "winner{$rank}@baaboo.test",
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('user');

            $show->users()->attach($user->id, [
                'score' => $score,
                'status' => 'registered',
                'is_online' => false,
                'is_winner' => true,
                'winner_prize_id' => $prize->id,
                'prize_won' => $prize->prize,
                // Only voucher winners carry a discount code; this is what the
                // SendWinnerEmailJob keys off to send the voucher email.
                'discount_code' => $isVoucher ? "TESTVOUCHER{$rank}" : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->recordGameplay($show, $user, $quizzes, $score);
        }
    }

    /**
     * Registered players who took part but did not finish in the top 10.
     *
     * @param  Collection<int, LiveShowQuiz>  $quizzes
     */
    private function createExtraPlayers(LiveShow $show, Collection $quizzes): void
    {
        foreach (range(1, self::EXTRA_PLAYERS) as $i) {
            $user = User::create([
                'name' => "Player {$i}",
                'email' => "player{$i}@baaboo.test",
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('user');

            // Below every winner's score so they never rank into the top 10.
            $score = rand(100, 500);

            $show->users()->attach($user->id, [
                'score' => $score,
                'status' => 'registered',
                'is_online' => false,
                'is_winner' => false,
                'prize_won' => 'n/a',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->recordGameplay($show, $user, $quizzes, $score);
        }
    }

    /**
     * A couple of users who never joined the show, for "not a participant" paths.
     */
    private function createNonParticipants(): void
    {
        foreach (range(1, 2) as $i) {
            $user = User::create([
                'name' => "Guest {$i}",
                'email' => "guest{$i}@baaboo.test",
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('user');
        }
    }

    /**
     * Persist one UserQuiz + UserQuizResponse per question so the player's
     * computed pivot score sums to (approximately) $score.
     *
     * @param  Collection<int, LiveShowQuiz>  $quizzes
     */
    private function recordGameplay(LiveShow $show, User $user, Collection $quizzes, int $score): void
    {
        $perQuestion = round($score / $quizzes->count(), 2);

        foreach ($quizzes as $quiz) {
            $correctOption = $quiz->options->firstWhere('is_correct', true);

            $userQuiz = UserQuiz::create([
                'user_id' => $user->id,
                'live_show_id' => $show->id,
                'quiz_id' => $quiz->id,
                'total_questions' => 1,
                'correct_answers' => 1,
                'score_percentage' => 100,
                'status' => 'completed',
            ]);

            UserQuizResponse::create([
                'user_quiz_id' => $userQuiz->id,
                'quiz_option_id' => $correctOption?->id,
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'is_correct' => true,
                'seconds_to_submit' => rand(80, 950) / 100,
                'response_score' => $perQuestion,
                'user_response' => $correctOption?->option_text,
            ]);
        }
    }

    /**
     * @return Collection<int, int>  distinct integers in [$min, $max]
     */
    private function distinctScores(int $count, int $min, int $max): Collection
    {
        $scores = collect();

        while ($scores->count() < $count) {
            $candidate = rand($min, $max);

            if (! $scores->contains($candidate)) {
                $scores->push($candidate);
            }
        }

        return $scores;
    }
}
