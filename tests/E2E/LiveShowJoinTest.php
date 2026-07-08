<?php

namespace Tests\E2E;

use App\Models\LiveShow;
use App\Models\LiveShowQuiz;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Models\UserQuiz;
use App\Models\UserQuizResponse;
use Illuminate\Support\Facades\Http;

/**
 * Step 3 of the E2E chain: joining the live show created in step 2.
 */
class LiveShowJoinTest extends E2ETestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*' => Http::response([
                'contacts' => [],
                'status' => false,
                'affiliated' => false,
            ], 200),
        ]);

        // registerUser rejects the request when a web session already exists.
        $this->app['auth']->guard('web')->logout();
    }

    /**
     * @test
     *
     * @depends Tests\E2E\LiveShowCreationTest::test_admin_updates_the_e2e_show_to_live
     */
    public function test_new_visitor_joins_the_live_show_directly(): void
    {
        $show = LiveShow::findOrFail(E2EContext::$liveShowId);

        $response = $this->postJson(route('live-show.user.register', $show->id), [
            'email' => E2EContext::NEW_JOINER_EMAIL,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'User registered successfully.',
            'authStatus' => true,
        ]);

        $user = User::where('email', E2EContext::NEW_JOINER_EMAIL)->firstOrFail();
        E2EContext::$newJoinerUserId = $user->id;

        $this->assertDatabaseHas('user_live_shows', [
            'live_show_id' => $show->id,
            'user_id' => $user->id,
            'status' => 'registered',
            'is_online' => 1,
        ]);
    }

    /**
     * @test
     *
     * @depends test_new_visitor_joins_the_live_show_directly
     */
    public function test_already_registered_user_joins_directly(): void
    {
        $show = LiveShow::findOrFail(E2EContext::$liveShowId);
        $existing = User::findOrFail(E2EContext::$directUserId);

        $response = $this->postJson(route('live-show.user.register', $show->id), [
            'email' => E2EContext::DIRECT_USER_EMAIL,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'User logged in successfully.',
            'authStatus' => true,
        ]);

        $this->assertDatabaseHas('user_live_shows', [
            'live_show_id' => $show->id,
            'user_id' => $existing->id,
            'status' => 'registered',
            'is_online' => 1,
        ]);
    }

    /**
     * @test
     *
     * @depends test_already_registered_user_joins_directly
     */
    public function test_referred_user_joins_via_magic_link(): void
    {
        $show = LiveShow::findOrFail(E2EContext::$liveShowId);
        $player = User::findOrFail(E2EContext::$referredUserId);

        $player->forceFill([
            'referral_link' => $player->referralLink(),
            'magic_link' => $player->magicLink(),
        ])->save();

        $userName = $player->fresh()->user_name;

        $response = $this->get(route('live-show-magic-link', $userName));

        $response->assertRedirect(route('live-show', ['id' => $show->id]));

        $this->assertDatabaseHas('user_live_shows', [
            'live_show_id' => $show->id,
            'user_id' => $player->id,
            'status' => 'registered',
            'is_online' => 1,
        ]);
    }

    /**
     * @test
     *
     * @depends test_referred_user_joins_via_magic_link
     */
    public function test_joined_players_receive_scores_for_winner_ranking(): void
    {
        $show = LiveShow::findOrFail(E2EContext::$liveShowId);
        $admin = User::findOrFail(E2EContext::$adminUserId);

        // Scores are computed from quiz responses (UserLiveShow::getScoreAttribute),
        // not from the pivot score column, so materialise responses for each player.
        $quiz = LiveShowQuiz::create([
            'live_show_id' => $show->id,
            'question' => 'E2E ranking question?',
            'created_by' => $admin->id,
            'has_shown' => true,
        ]);

        $correctOption = $quiz->options()->create([
            'option_text' => 'Correct',
            'is_correct' => true,
        ]);

        $this->recordQuizScore($show->id, $quiz->id, $correctOption->id, E2EContext::$newJoinerUserId, 2000);
        $this->recordQuizScore($show->id, $quiz->id, $correctOption->id, E2EContext::$directUserId, 1500);
        $this->recordQuizScore($show->id, $quiz->id, $correctOption->id, E2EContext::$referredUserId, 1000);

        $this->assertSame(2000.0, UserLiveShow::where('live_show_id', $show->id)
            ->where('user_id', E2EContext::$newJoinerUserId)
            ->first()
            ->score);
    }

    private function recordQuizScore(int $liveShowId, int $quizId, int $optionId, int $userId, float $score): void
    {
        $userQuiz = UserQuiz::create([
            'user_id' => $userId,
            'live_show_id' => $liveShowId,
            'quiz_id' => $quizId,
            'total_questions' => 1,
            'correct_answers' => 1,
            'score_percentage' => 100,
            'status' => 'completed',
        ]);

        UserQuizResponse::create([
            'user_quiz_id' => $userQuiz->id,
            'quiz_option_id' => $optionId,
            'quiz_id' => $quizId,
            'user_id' => $userId,
            'is_correct' => true,
            'seconds_to_submit' => 1.5,
            'response_score' => $score,
            'user_response' => 'Correct',
        ]);

        UserLiveShow::where('live_show_id', $liveShowId)
            ->where('user_id', $userId)
            ->update(['score' => $score]);
    }
}
