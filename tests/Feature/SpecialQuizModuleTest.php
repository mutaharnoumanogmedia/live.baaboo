<?php

namespace Tests\Feature;

use App\Events\LiveShowAdminStateEvent;
use App\Events\ShowLiveShowWinnersTabEvent;
use App\Events\ShowSpecialWinnersTabEvent;
use App\Jobs\SendSpecialWinnerEmailJob;
use App\Jobs\SendWinnerEmailJob;
use App\Models\LiveShow;
use App\Models\LiveShowQuiz;
use App\Models\LiveShowWinnerPrize;
use App\Models\SpecialGift;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Models\UserQuiz;
use App\Models\UserQuizResponse;
use App\Models\UserSpecialQuizResponse;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Special Quiz module: verifies the special quiz stays fully isolated from the
 * main quiz (separate scores, ranking, winners, gifts and announcements) and
 * that shows without any special content behave exactly as before.
 *
 * @see \App\Http\Controllers\Admin\LiveShowController::announceSpecialWinners
 * @see \App\Models\UserLiveShow::getSpecialScoreAttribute
 */
class SpecialQuizModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    }

    private function admin(): User
    {
        $admin = User::create([
            'name' => 'Special Admin',
            'email' => 'special-admin@baaboo.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function makeShow(User $admin, int $specialMaxWinners = 2, int $maxWinners = 2): LiveShow
    {
        $airedAt = Carbon::now()->subDay()->setTime(18, 0);

        return LiveShow::create([
            'title' => 'Special Test Show',
            'description' => 'special quiz fixture',
            'scheduled_at' => $airedAt,
            'status' => 'completed',
            'is_test_show' => true,
            'max_winners' => $maxWinners,
            'special_max_winners' => $specialMaxWinners,
            'max_players' => 100,
            'chat_enabled' => true,
            'winners_announced' => false,
            'special_winners_announced' => false,
            'start_time' => $airedAt,
            'created_by' => $admin->id,
        ]);
    }

    private function makeQuestion(LiveShow $show, User $admin, bool $isSpecial): LiveShowQuiz
    {
        $quiz = LiveShowQuiz::create([
            'live_show_id' => $show->id,
            'question' => ($isSpecial ? 'Special' : 'Main').' question?',
            'is_special' => $isSpecial,
            'created_by' => $admin->id,
            'has_shown' => true,
        ]);
        foreach (range(1, 4) as $i) {
            $quiz->options()->create([
                'option_text' => "Option {$i}",
                'is_correct' => $i === 1,
            ]);
        }

        return $quiz->load('options');
    }

    private function makePlayer(LiveShow $show, string $name): User
    {
        $user = User::create([
            'name' => $name,
            'email' => \Illuminate\Support\Str::slug($name).'@baaboo.test',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('user');

        $show->users()->attach($user->id, [
            'status' => 'registered',
            'is_online' => false,
            'is_winner' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function recordMain(LiveShow $show, User $user, LiveShowQuiz $quiz, float $score): void
    {
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
            'quiz_option_id' => $quiz->options->firstWhere('is_correct', true)?->id,
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'is_correct' => true,
            'seconds_to_submit' => 2.0,
            'response_score' => $score,
            'user_response' => 'x',
        ]);
    }

    private function recordSpecial(LiveShow $show, User $user, LiveShowQuiz $quiz, float $score, float $seconds = 2.0): void
    {
        UserSpecialQuizResponse::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'quiz_option_id' => $quiz->options->firstWhere('is_correct', true)?->id,
            'is_correct' => true,
            'seconds_to_submit' => $seconds,
            'response_score' => $score,
            'user_response' => 'x',
        ]);
    }

    /** @test */
    public function special_responses_do_not_affect_the_main_score_and_vice_versa(): void
    {
        $admin = $this->admin();
        $show = $this->makeShow($admin);
        $main = $this->makeQuestion($show, $admin, false);
        $special = $this->makeQuestion($show, $admin, true);

        $user = $this->makePlayer($show, 'Iso User');
        $this->recordMain($show, $user, $main, 100);
        $this->recordSpecial($show, $user, $special, 555);

        $pivot = UserLiveShow::where('live_show_id', $show->id)->where('user_id', $user->id)->firstOrFail();

        $this->assertSame(100.0, (float) $pivot->score, 'Main score must ignore special responses.');
        $this->assertSame(555.0, (float) $pivot->special_score, 'Special score must sum only special responses.');
    }

    /** @test */
    public function scopes_filter_questions_by_type(): void
    {
        $admin = $this->admin();
        $show = $this->makeShow($admin);
        $this->makeQuestion($show, $admin, false);
        $this->makeQuestion($show, $admin, true);
        $this->makeQuestion($show, $admin, true);

        $this->assertSame(1, $show->mainQuizzes()->count());
        $this->assertSame(2, $show->specialQuizzes()->count());
    }

    /** @test */
    public function announcing_special_winners_ranks_by_special_score_and_leaves_main_winners_untouched(): void
    {
        Queue::fake();
        Event::fake([ShowSpecialWinnersTabEvent::class, ShowLiveShowWinnersTabEvent::class, LiveShowAdminStateEvent::class]);

        $admin = $this->admin();
        $show = $this->makeShow($admin, specialMaxWinners: 2, maxWinners: 1);
        $main = $this->makeQuestion($show, $admin, false);
        $special = $this->makeQuestion($show, $admin, true);

        // A: main leader, no special. B: special leader. C: special runner-up.
        $a = $this->makePlayer($show, 'Alpha');
        $b = $this->makePlayer($show, 'Bravo');
        $c = $this->makePlayer($show, 'Charlie');

        $this->recordMain($show, $a, $main, 900);
        $this->recordMain($show, $b, $main, 100);
        $this->recordMain($show, $c, $main, 50);

        $this->recordSpecial($show, $b, $special, 800);
        $this->recordSpecial($show, $c, $special, 400);
        // A has no special response at all.

        SpecialGift::create(['live_show_id' => $show->id, 'rank' => 1, 'name' => '1000 cash', 'type' => 'cash', 'value' => 1000]);
        SpecialGift::create(['live_show_id' => $show->id, 'rank' => 2, 'name' => 'Mystery box', 'type' => 'custom']);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.live-shows.announce-special-winners', $show->id))
            ->assertStatus(200)
            ->assertJson(['success' => true, 'special_winners_announced' => true]);

        $this->assertTrue($show->fresh()->special_winners_announced);

        $bPivot = UserLiveShow::where('live_show_id', $show->id)->where('user_id', $b->id)->firstOrFail();
        $cPivot = UserLiveShow::where('live_show_id', $show->id)->where('user_id', $c->id)->firstOrFail();
        $aPivot = UserLiveShow::where('live_show_id', $show->id)->where('user_id', $a->id)->firstOrFail();

        // B (rank 1) and C (rank 2) are special winners with the ranked gifts.
        $this->assertTrue((bool) $bPivot->is_special_winner);
        $this->assertSame('1000 cash', $bPivot->special_prize_won);
        $this->assertTrue((bool) $cPivot->is_special_winner);
        $this->assertSame('Mystery box', $cPivot->special_prize_won);

        // A is not a special winner, and NO main winners were touched.
        $this->assertFalse((bool) $aPivot->is_special_winner);
        $this->assertFalse((bool) $aPivot->is_winner);
        $this->assertSame(0, UserLiveShow::where('live_show_id', $show->id)->where('is_winner', true)->count());

        Event::assertDispatched(ShowSpecialWinnersTabEvent::class);
        Event::assertNotDispatched(ShowLiveShowWinnersTabEvent::class);
        Queue::assertPushed(SendSpecialWinnerEmailJob::class, 2);
        Queue::assertNotPushed(SendWinnerEmailJob::class);
    }

    /** @test */
    public function announcing_special_winners_can_only_happen_once(): void
    {
        Queue::fake();
        Event::fake();

        $admin = $this->admin();
        $show = $this->makeShow($admin, specialMaxWinners: 1);
        $special = $this->makeQuestion($show, $admin, true);
        $b = $this->makePlayer($show, 'Solo');
        $this->recordSpecial($show, $b, $special, 300);
        SpecialGift::create(['live_show_id' => $show->id, 'rank' => 1, 'name' => 'Prize', 'type' => 'cash']);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.live-shows.announce-special-winners', $show->id))
            ->assertStatus(200);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.live-shows.announce-special-winners', $show->id))
            ->assertStatus(422)
            ->assertJson(['success' => false, 'special_winners_announced' => true]);
    }

    /** @test */
    public function shows_without_special_questions_reject_special_announcement(): void
    {
        Queue::fake();
        Event::fake();

        $admin = $this->admin();
        $show = $this->makeShow($admin);
        $this->makeQuestion($show, $admin, false);
        $this->makePlayer($show, 'Only Main');

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.live-shows.announce-special-winners', $show->id))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertFalse($show->fresh()->special_winners_announced);
        Queue::assertNotPushed(SendSpecialWinnerEmailJob::class);
    }
}
