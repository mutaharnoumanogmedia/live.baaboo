<?php

namespace App\Http\Controllers\Admin;

use App\Events\BroadcasterTabClaimedEvent;
use App\Events\GameResetEvent;
use App\Events\HideGalleryImageEvent;
use App\Events\HideLiveShowWinnersTabEvent;
use App\Events\HideSpecialWinnersTabEvent;
use App\Events\LiveShowAdminStateEvent;
use App\Events\LiveShowChatStatusUpdatedEvent;
use App\Events\LiveShowMediaHidden;
use App\Events\LiveShowMediaPlayed;
use App\Events\LiveShowMessageEvent;
use App\Events\LiveShowQuizUserResponses;
use App\Events\RemoveLiveShowQuizQuestionEvent;
use App\Events\ResetChatEvent;
use App\Events\SetBroadcastRoomIdEvent;
use App\Events\ShowGalleryImageEvent;
use App\Events\ShowLiveShowQuizQuestionEvent;
use App\Events\ShowLiveShowWinnersTabEvent;
use App\Events\ShowPlayerAsWinnerEvent;
use App\Events\ShowSpecialWinnersTabEvent;
use App\Events\UpdateLiveShowEvent;
use App\Events\UserBlockFromLiveShowEvent;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateSpecialWinnerDiscountCodeJob;
use App\Jobs\GenerateWinnerDiscountCodeJob;
use App\Jobs\SendSpecialWinnerEmailJob;
use App\Jobs\SendWinnerEmailJob;
use App\Jobs\SendWinnerVoucherEmailJob;
use App\Models\GalleryMedia;
use App\Models\LiveShow;
use App\Models\LiveShowEndMedia;
use App\Models\LiveShowGalleryMedia;
use App\Models\LiveShowQuiz;
use App\Models\LiveShowWinnerPrize;
use App\Models\QuizOption;
use App\Models\SpecialGift;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Models\UserQuiz;
use App\Models\UserQuizResponse;
use App\Models\UserSpecialQuizResponse;
use App\Services\LiveShowQuizService;
use App\Services\PushNotificationService;
use App\Services\ShopifyDiscountService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LiveShowController extends Controller
{
    /**
     * Allowed special gift types (value => label) for Special Quiz winners.
     *
     * @var array<string, string>
     */
    protected array $specialGiftTypes = [
        'cash' => 'Cash',
        'voucher' => 'Voucher',
        'custom' => 'Custom',
    ];

    public function __construct()
    {
        $this->middleware('permission:can-manage-live-shows');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
        // Get shows with 'scheduled', then 'live', then 'completed'
        $liveShows = LiveShow::orderByRaw("
                CASE
                    WHEN status = 'live' THEN 0
                    WHEN status = 'scheduled' THEN 1
                    WHEN status = 'completed' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('scheduled_at', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.live-shows.index', compact('liveShows'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $specialGiftTypes = $this->specialGiftTypes;

        return view('admin.live-shows.create', compact('specialGiftTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date',

            'status' => 'required|in:scheduled,live,completed',
            'host_name' => 'nullable|string|max:255',
            'prize_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:5',
            'max_winners' => 'required|integer|min:1|max:50',
            'max_players' => 'required|integer|min:1|max:100000',
            'chat_enabled' => 'required|boolean',
            'winner_prizes' => 'nullable|array',
            'winner_voucher' => 'nullable|array',
            'winner_voucher_amount' => 'nullable|array|min:0',
            'winner_prizes.*' => 'nullable|string|max:255',
            'winner_voucher.*' => 'nullable|integer|max:255',
            'winner_voucher_amount.*' => 'nullable|numeric|min:0',
            'is_test_show' => 'required|boolean',
            'special_max_winners' => 'required|integer|min:0|max:50',
            'special_gifts' => 'nullable|array',
            'special_gifts.*.name' => 'nullable|string|max:255',
            'special_gifts.*.type' => 'nullable|in:'.implode(',', array_keys($this->specialGiftTypes)),
            'special_gifts.*.value' => 'nullable|numeric|min:0',
            'special_gifts.*.voucher_amount' => 'nullable|numeric|min:0',
        ]);

        $validated['created_by'] = Auth::id();
        $maxWinners = (int) $validated['max_winners'];
        $prizes = $request->input('winner_prizes', []);
        $vouchers = $request->input('winner_voucher', []);
        $voucherAmounts = $request->input('winner_voucher_amount', []);
        $specialMaxWinners = (int) $validated['special_max_winners'];
        $specialGifts = $request->input('special_gifts', []);

        $show = LiveShow::create($validated);

        $this->syncWinnerPrizes($show->id, $maxWinners, $prizes, $vouchers, $voucherAmounts);
        $this->syncSpecialGifts($show->id, $specialMaxWinners, $specialGifts);

        return redirect()->route('admin.live-shows.show', $show->id)->with('success', 'Live Show created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(LiveShow $liveShow)
    {
        // eager-load users from pivot table
        $liveShow->load(['creator', 'winnerPrizes', 'users' => function ($query) {
            $query->withPivot(['score', 'status', 'created_at', 'prize_won', 'is_winner', 'is_online', 'created_at'])
                ->orderByDesc('user_live_shows.is_winner')
                ->orderByDesc('user_live_shows.score');
        }]);

        return view('admin.live-shows.show', compact('liveShow'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(LiveShow $liveShow)
    {
        $liveShow->load('winnerPrizes', 'specialGifts');
        $specialGiftTypes = $this->specialGiftTypes;

        return view('admin.live-shows.edit', compact('liveShow', 'specialGiftTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, LiveShow $live_show)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:scheduled,live,completed',
            'max_players' => 'required|integer|min:1|max:100000',
            'chat_enabled' => 'required|boolean',
            'is_test_show' => 'required|boolean',
        ]);

        $live_show->update($validated);

        return redirect()->route('admin.live-shows.edit', $live_show->id)->with('success', 'Live show details updated successfully!');
    }

    public function updateWinnerPrizes(Request $request, LiveShow $live_show)
    {
        $validated = $request->validate([
            'max_winners' => 'required|integer|min:1|max:50',
            'winner_prizes' => 'nullable|array',
            'winner_voucher' => 'nullable|array',
            'winner_voucher_amount' => 'nullable|array|min:0',
            'winner_prizes.*' => 'nullable|string|max:255',
            'winner_voucher.*' => 'nullable|integer|max:255',
            'winner_voucher_amount.*' => 'nullable|numeric|min:0',
        ]);

        $maxWinners = (int) $validated['max_winners'];
        $prizes = $request->input('winner_prizes', []);
        $vouchers = $request->input('winner_voucher', []);
        $voucherAmounts = $request->input('winner_voucher_amount', []);

        $live_show->update(['max_winners' => $maxWinners]);

        $this->syncWinnerPrizes($live_show->id, $maxWinners, $prizes, $vouchers, $voucherAmounts);

        return redirect()->route('admin.live-shows.edit', $live_show->id)->with('success', 'Winner prizes updated successfully!');
    }

    /**
     * Persist the Special Quiz gift list (ranked, per show) and the
     * special_max_winners count. Mirrors updateWinnerPrizes but writes to the
     * independent `special_gifts` table.
     */
    public function updateSpecialGifts(Request $request, LiveShow $live_show)
    {
        $validated = $request->validate([
            'special_max_winners' => 'required|integer|min:0|max:50',
            'special_gifts' => 'nullable|array',
            'special_gifts.*.name' => 'nullable|string|max:255',
            'special_gifts.*.type' => 'nullable|in:'.implode(',', array_keys($this->specialGiftTypes)),
            'special_gifts.*.value' => 'nullable|numeric|min:0',
            'special_gifts.*.voucher_amount' => 'nullable|numeric|min:0',
        ]);

        $maxWinners = (int) $validated['special_max_winners'];
        $gifts = $request->input('special_gifts', []);

        $live_show->update(['special_max_winners' => $maxWinners]);

        $this->syncSpecialGifts($live_show->id, $maxWinners, $gifts);

        return redirect()->route('admin.live-shows.edit', $live_show->id)->with('success', 'Special gifts updated successfully!');
    }

    /**
     * Normalize special gift row input so only the relevant amount field is kept.
     *
     * @param  array<string, mixed>  $giftData
     * @return array{name: string, type: string, value: ?float, voucher_amount: ?float}
     */
    protected function normalizeSpecialGiftRow(array $giftData): array
    {
        $type = (string) ($giftData['type'] ?? 'cash');
        if (! array_key_exists($type, $this->specialGiftTypes)) {
            $type = 'cash';
        }

        $value = $giftData['value'] ?? null;
        $voucherAmount = $giftData['voucher_amount'] ?? null;

        if ($type === 'voucher') {
            $value = null;
            $voucherAmount = ($voucherAmount !== '' && $voucherAmount !== null)
                ? (float) $voucherAmount
                : null;
        } else {
            $voucherAmount = null;
            $value = ($value !== '' && $value !== null)
                ? (float) $value
                : null;
        }

        return [
            'name' => trim((string) ($giftData['name'] ?? '')),
            'type' => $type,
            'value' => $value,
            'voucher_amount' => $voucherAmount,
        ];
    }

    /**
     * Sync ranked special gift rows for a live show. For voucher-type gifts on
     * non-test shows a Shopify price rule is created/updated (mirrors the main
     * winner-prize behaviour) so voucher codes can be generated at announcement.
     */
    protected function syncSpecialGifts(int $liveShowId, int $maxWinners, array $gifts): void
    {
        $liveShow = LiveShow::find($liveShowId);
        $errors = [];

        for ($rank = 1; $rank <= $maxWinners; $rank++) {
            $giftData = $this->normalizeSpecialGiftRow($gifts[$rank] ?? []);
            $name = $giftData['name'];
            $type = $giftData['type'];
            $value = $giftData['value'];
            $voucherAmount = $giftData['voucher_amount'];

            $existing = SpecialGift::where('live_show_id', $liveShowId)->where('rank', $rank)->first();
            $oldGift = $existing;
            if ($existing) {
                $existing->delete();
            }

            if ($name === '') {
                continue;
            }

            $discount_rule_id = null;
            if ($type === 'voucher' && ! $liveShow->is_test_show && $voucherAmount) {
                try {
                    $starts_at = Carbon::parse($liveShow->start_time ?? now(), 'CET')->toIso8601String();
                    $ends_at = Carbon::parse($liveShow->start_time ?? now(), 'CET')->addDays(31)->toIso8601String();
                    $prizeRule = [
                        'title' => 'BADABING SPECIAL - '.$liveShow->title.' - Rank '.$rank,
                        'target_type' => 'line_item',
                        'target_selection' => 'entitled',
                        'allocation_method' => 'across',
                        'value_type' => 'fixed_amount',
                        'value' => "-{$voucherAmount}",
                        'customer_selection' => 'all',
                        'starts_at' => $starts_at,
                        'ends_at' => $ends_at,
                        'usage_limit' => 1,
                        'entitled_collection_ids' => [
                            env('VOUCHER_COLLECTION_ID'),
                        ],
                    ];
                    $shopifyPriceRule = new ShopifyDiscountService;
                    if ($oldGift && $oldGift->type === 'voucher' && $oldGift->discount_rule_id && $oldGift->discountRule) {
                        $prizeRule = $shopifyPriceRule->updatePriceRule($oldGift->discountRule->shopify_id, $starts_at, $ends_at, $prizeRule);
                    } else {
                        $prizeRule = $shopifyPriceRule->createPriceRule($prizeRule);
                    }
                    $discount_rule_id = $prizeRule->id;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create/update Shopify price rule for special gift rank {$rank}.";
                    Log::error("Failed to create Shopify price rule for special gift, live show ID {$liveShowId}, rank {$rank}: ".$e->getMessage());
                }
            }

            SpecialGift::create([
                'live_show_id' => $liveShowId,
                'rank' => $rank,
                'name' => $name,
                'type' => $type,
                'value' => $value,
                'voucher_amount' => $voucherAmount,
                'discount_rule_id' => $discount_rule_id,
            ]);
        }

        if (! empty($errors)) {
            session()->flash('error', implode(' ', $errors));
        }
    }

    /**
     * Validate that the first max_winners percentages sum to 100.
     */
    protected function validateWinnerPrizes(int $maxWinners, array $prizes): void
    {
        $sum = 0;
        for ($r = 1; $r <= $maxWinners; $r++) {
            $sum += (float) ($prizes[$r] ?? 0);
        }
        if (abs($sum - 100) > 0.01) {
            throw ValidationException::withMessages([
                'winner_prizes' => ['The prize percentages for the first '.$maxWinners.' winner(s) must total 100%. Current total: '.round($sum, 1).'%.'],
            ]);
        }
    }

    /**
     * Sync winner percentage rows for a live show.
     */
    protected function syncWinnerPrizes(int $liveShowId, int $maxWinners, array $prizes, array $vouchers, array $voucherAmounts): void
    {
        $liveShow = LiveShow::find($liveShowId);
        // if test show or env is local then return
        // if ($liveShow->is_test_show || env('APP_ENV') == 'local') {
        //     return;
        // }

        $errors = [];
        for ($rank = 1; $rank <= $maxWinners; $rank++) {
            $voucher = (string) ($vouchers[$rank] ?? 0);
            $voucherAmount = (string) ($voucherAmounts[$rank] ?? 0);
            $prize = (string) ($prizes[$rank] ?? 0);

            $winnerPrize = LiveShowWinnerPrize::where('live_show_id', $liveShowId)->where('rank', $rank)->first();
            $delWinnerPrize = $winnerPrize;
            if ($winnerPrize) {
                $winnerPrize->delete();
                // dd("Is Deleted",$winnerPrize,$winnerPrize->delete());
            }

            $discount_rule_id = null;
            if (! $liveShow->is_test_show) {
                try {
                    $starts_at = Carbon::parse($liveShow->start_time, 'CET')->toIso8601String();
                    $ends_at = Carbon::parse($liveShow->start_time, 'CET')->addDays(31)->toIso8601String();
                    $prizeRule = [
                        'title' => 'BADABING - '.$liveShow->title.' - Rank '.$rank,
                        'target_type' => 'line_item',
                        'target_selection' => 'entitled',
                        'allocation_method' => 'across',
                        'value_type' => 'fixed_amount',
                        'value' => "-{$voucherAmount}",
                        'customer_selection' => 'all',
                        'starts_at' => $starts_at,
                        'ends_at' => $ends_at,
                        'usage_limit' => 1,
                        'entitled_collection_ids' => [
                            env('VOUCHER_COLLECTION_ID'),
                        ],
                        // 'allocation_limit ' => 1,
                    ];
                    $shopifyPriceRule = new ShopifyDiscountService;
                    if ($delWinnerPrize && $delWinnerPrize->is_voucher && $delWinnerPrize->discount_rule_id) {
                        // dd($delWinnerPrize);
                        $prizeRule = $shopifyPriceRule->updatePriceRule($delWinnerPrize->discountRule->shopify_id, $starts_at, $ends_at, $prizeRule);
                        $discount_rule_id = $prizeRule->id;
                    } elseif ($voucher) {
                        $prizeRule = $shopifyPriceRule->createPriceRule($prizeRule);
                        $discount_rule_id = $prizeRule->id;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to create/update Shopify price rule for live show ID {$liveShowId}, rank {$rank}. Please check the logs for more details.";
                    Log::error("Failed to create Shopify price rule for live show ID {$liveShowId}, rank {$rank}: ".$e->getMessage());
                }
            }

            if ($prize) {
                LiveShowWinnerPrize::create([
                    'prize' => $prize,
                    'live_show_id' => $liveShowId,
                    'rank' => $rank,
                    'is_voucher' => $voucher,
                    'voucher_amount' => $voucherAmount,
                    'discount_rule_id' => $discount_rule_id,
                ]);
            }
        }

        if (! empty($errors)) {
            session()->flash('error', implode(' ', $errors));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(LiveShow $live_show)
    {
        $live_show->quizzes()->delete();
        $live_show->winnerPrizes()->delete();
        // $live_show->galleryMedia()->delete();
        $live_show->users()->detach();
        $live_show->messages()->delete();
        // $live_show->galleryState()->delete();
        $live_show->delete();

        if ($live_show->thumbnail) {
            $thumbnailPath = str_replace(asset('storage/'), '', $live_show->thumbnail);
            Storage::disk('public')->delete($thumbnailPath);
        }
        if ($live_show->banner) {
            $bannerPath = str_replace(asset('storage/'), '', $live_show->banner);
            Storage::disk('public')->delete($bannerPath);
        }

        return redirect()->route('admin.live-shows.index')->with('success', 'Live Show deleted!');
    }

    public function streamManagement($id)
    {
        $liveShow = LiveShow::with([
            'quizzes' => fn ($q) => $q->orderBy('sorting_order')->orderBy('id'),
            'quizzes.options',
            'quizzes.questionMedia',
            'endMedia',
            'users',
            'winnerPrizes',
            'galleryMedia',
        ])->findOrFail($id);
        $allGalleryMedia = GalleryMedia::orderBy('created_at', 'desc')->get();

        return view('admin.live-shows.stream-management', compact('liveShow', 'allGalleryMedia'));
    }

    public function sendQuizQuestion(Request $request, $id, $quizId)
    {
        $request->validate([
            'seconds' => 'integer|min:2|max:120',
            'is_last' => 'nullable|boolean',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        $quizModel = $liveShow->quizzes()->where('id', $quizId)->first();

        if (! $quizModel) {
            return response()->json(['message' => 'Quiz not found for this live show.'], 404);
        }

        if ($quizModel->has_shown) {
            return response()->json([
                'message' => 'This question has already been shown.',
                'already_shown' => true,
            ], 422);
        }

        $quiz = $quizModel->toArray();
        $quizOptions = QuizOption::where('quiz_id', $quizId)->select('id', 'quiz_id', 'option_text')->get()->toArray();

        // Attach quiz options directly to $quiz object for broadcasting
        $quiz['options'] = $quizOptions;

        // Number questions within their own quiz type so Special Quiz questions
        // have their own "2 / 5" sequence, independent of the main quiz.
        $isSpecial = (bool) $quizModel->is_special;
        $scopedQuizzes = $liveShow->quizzes()->where('is_special', $isSpecial)->orderBy('sorting_order')->orderBy('id')->get();
        $totalQuizQuestions = $scopedQuizzes->count();
        $quiz['totalQuizQuestions'] = $totalQuizQuestions;
        $quiz['isSpecial'] = $isSpecial;
        // index of this question within its own quiz type
        $quizQuestionIndex = $scopedQuizzes->pluck('id')->search((int) $quizId);

        // Broadcast the quiz question to users
        ShowLiveShowQuizQuestionEvent::dispatch($quiz, (string) $liveShow->id, $request->seconds ?? 10, $request->is_last ?? false, $quizQuestionIndex + 1);

        $quizModel->update(['has_shown' => true]);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'quiz', [
            'action' => 'shown',
            'quizId' => (int) $quizModel->id,
            'seconds' => (int) ($request->seconds ?? 10),
        ]);

        return response()->json([
            'message' => 'Quiz question sent successfully!',
            'has_shown' => true,
        ]);
    }

    public function removeQuizQuestion(Request $request, $id, $quizId)
    {
        $liveShow = LiveShow::findOrFail($id);
        $quiz = $liveShow->quizzes()->where('id', $quizId)->first();

        if (! $quiz) {
            return response()->json(['message' => 'Quiz not found for this live show.'], 404);
        }

        // Broadcast an event to remove the quiz question from users
        RemoveLiveShowQuizQuestionEvent::dispatch($quiz->id, (string) $liveShow->id);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'quiz', [
            'action' => 'hidden',
            'quizId' => (int) $quiz->id,
        ]);

        return response()->json(['message' => 'Quiz question removed successfully!']);
    }

    public function resetQuizShownStatus(Request $request, $id, $quizId)
    {
        $liveShow = LiveShow::findOrFail($id);
        $quizModel = $liveShow->quizzes()->where('id', $quizId)->first();

        if (! $quizModel) {
            return response()->json(['message' => 'Quiz not found for this live show.'], 404);
        }

        $quizModel->update(['has_shown' => false]);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'quiz', [
            'action' => 'reset',
            'quizId' => (int) $quizModel->id,
        ]);

        return response()->json([
            'message' => 'Question shown status has been reset.',
            'has_shown' => false,
        ]);
    }

    public function updateWinners(Request $request, $liveShowId)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (! $liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }

        if ($liveShow->winners_announced) {
            return response()->json([
                'success' => false,
                'message' => 'Winners have already been announced for this live show.',
                'winners_announced' => true,
            ], 422);
        }

        // make all users is_winner = false
        UserLiveShow::where('live_show_id', $liveShow->id)->update(['is_winner' => false]);

        $maxWinners = (int) $liveShow->max_winners;
        $topMaxWinnersByScore = DB::table('users')
            ->join('user_live_shows', 'users.id', '=', 'user_live_shows.user_id')
            ->where('user_live_shows.live_show_id', $liveShow->id)
            ->where('score', '>', 0)
            ->orderBy('user_live_shows.score', 'desc')
            ->take($maxWinners)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->user_id,
                    'name' => $user->name,
                    'score' => $user->score,
                ];
            });

        // Update pivot table to set is_winner = true for top three users
        foreach ($topMaxWinnersByScore as $winner) {
            Log::info("Updating winner {$winner['id']} to is_winner = true");

            UserLiveShow::where('user_id', $winner['id'])->where('live_show_id', $liveShow->id)->update(['is_winner' => true]);

        }

        // update each winner prize won
        foreach ($topMaxWinnersByScore as $index => $winner) {
            $prize = $liveShow->winnerPrizes()->where('rank', $index + 1)->first();
            $prizeWon = $prize->prize ?? 'n/a';
            Log::info("Winner {$winner['id']} prize won: {$prizeWon}");
            $isVoucher = $prize->is_voucher ?? false;

            if ($isVoucher && $prize->discount_rule_id) {
                $winner_user = UserLiveShow::where('user_id', $winner['id'])->where('live_show_id', $liveShowId)->first();
                if ($winner_user?->discount_code) {
                    Log::info("User ID {$winner['id']} already has a discount code: {$winner_user->discount_code}");
                } elseif ($winner_user && $prize->discountRule?->shopify_id) {
                    // job added to avoid the delay of the Shopify API
                    try {
                        // delay to avoid the delay of the Shopify API
                        GenerateWinnerDiscountCodeJob::dispatch(
                            $winner['id'],
                            (int) $liveShowId,
                            $prize->discountRule->shopify_id
                        )->delay(now()->addSeconds(20));

                        Log::info("GenerateWinnerDiscountCodeJob dispatched for user ID {$winner['id']}, live show ID {$liveShowId} ".now()->format('d M Y, H:i'));
                    } catch (\Exception $e) {
                        Log::error("Failed to dispatch GenerateWinnerDiscountCodeJob for user ID {$winner['id']}: ".$e->getMessage().' '.now()->format('d M Y, H:i'));
                    }
                }
            }

            $liveShow->users()->updateExistingPivot($winner['id'], ['prize_won' => $prizeWon, 'winner_prize_id' => $prize->id ?? null]);

            // if (! $liveShow->is_test_show) {
            // Dispatch job to send winner email after 30 minutes
            try {
                // SendWinnerEmailJob routes to the correct email 30 minutes later:
                // voucher winners -> WinnerVoucherNotificationMail, cash winners
                // (prize is_voucher = 0) -> WinnerCashNotificationMail.
                SendWinnerEmailJob::dispatch($winner['id'], $prizeWon, $liveShow)->delay(now()->addMinutes((int) env('WINNER_EMAIL_DELAY', 30)));

                Log::info("SendWinnerEmailJob dispatched for user ID {$winner['id']}, live show ID {$liveShowId} and prize won: {$prizeWon} ".now()->format('d M Y, H:i'));
            } catch (\Exception $e) {
                // log the error
                Log::error("Failed to dispatch SendWinnerEmailJob for user ID {$winner['id']}: ".$e->getMessage().' '.now()->format('d M Y, H:i'));
            }
            // }

        }

        $liveShow->update(['winners_announced' => true]);

        $winnersData = $liveShow->users()
            ->wherePivot('is_winner', true)
            ->select('users.id as user_id', 'user_live_shows.prize_won as prize_won')
            ->get();
        $winnersDataArray = $winnersData->map(function ($winner) {
            return [
                'user_id' => $winner->user_id,
                'prize_won' => $winner->prize_won,
            ];
        })->toArray();
        ShowLiveShowWinnersTabEvent::dispatch((string) $liveShow->id, $winnersDataArray);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'winners', [
            'winners_announced' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Winners have been announced. Winner notification emails have been queued for the winners.',
            'winners_announced' => true,
            'winnerUsers' => $topMaxWinnersByScore,
        ]);
    }

    public function reupdateWinners(Request $request, $liveShowId)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (! $liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }

        // if ($liveShow->winners_announced) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Winners have already been announced for this live show.',
        //         'winners_announced' => true,
        //     ], 422);
        // }

        // make all users is_winner = false
        UserLiveShow::where('live_show_id', $liveShow->id)->update(['is_winner' => false]);

        $maxWinners = (int) $liveShow->max_winners;
        $topMaxWinnersByScore =
            $topMaxWinnersByScore = $liveShow->users()
                ->with(['quizResponses' => function ($query) use ($liveShowId) {
                    $query->whereHas('userQuiz.quiz', function ($q) use ($liveShowId) {
                        $q->where('live_show_id', $liveShowId);
                    });
                }])
                ->wherePivot('status', 'registered')
                ->get()
                ->map(function ($user) use ($liveShowId) {

                    // Calculate total score
                    $score = $user->pivot->score ?? 0;
                    // Find the user's quiz responses for this live show and calculate total seconds_to_submit
                    $userQuizResponses = $user->quizResponses()
                        ->whereHas('userQuiz', function ($q) use ($liveShowId) {
                            $q->where('live_show_id', $liveShowId);
                        })
                        ->get();

                    $totalSecondsToSubmit = $userQuizResponses->sum('seconds_to_submit');
                    $firstResponseTime = $userQuizResponses->min('created_at') ?? now();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'score' => $score,

                        'total_seconds_to_submit' => $totalSecondsToSubmit,
                        'first_response_time' => $firstResponseTime,
                    ];
                })
                ->sort(function ($a, $b) {
                    // Sort by score descending, then by total_seconds_to_submit ascending
                    if ($a['score'] === $b['score']) {
                        return $a['total_seconds_to_submit'] <=> $b['total_seconds_to_submit'];
                    }

                    return $b['score'] <=> $a['score'];
                })
                ->values()
                ->take($maxWinners);

        // Update pivot table to set is_winner = true for top three users
        foreach ($topMaxWinnersByScore as $winner) {
            Log::info("Updating winner {$winner['id']} to is_winner = true");
            // if score is greater than 0, then update is_winner = true
            if ($winner['score'] > 0) {
                $liveShow->users()->updateExistingPivot($winner['id'], ['is_winner' => true]);
            }
        }

        // update each winner prize won
        foreach ($topMaxWinnersByScore as $index => $winner) {
            $prize = $liveShow->winnerPrizes()->where('rank', $index + 1)->first();
            $prizeWon = $prize->prize ?? 'n/a';
            Log::info("Winner {$winner['id']} prize won: {$prizeWon}");
            $isVoucher = $prize->is_voucher ?? false;
            if ($isVoucher && $prize->discount_rule_id) {
                $winner_user = UserLiveShow::where('user_id', $winner['id'])->where('live_show_id', $liveShowId)->first();
                if ($winner_user?->discount_code) {
                    Log::info("User ID {$winner['id']} already has a discount code: {$winner_user->discount_code}");
                } elseif ($winner_user && $prize->discountRule?->shopify_id) {
                    try {
                        GenerateWinnerDiscountCodeJob::dispatch(
                            $winner['id'],
                            (int) $liveShowId,
                            $prize->discountRule->shopify_id
                        )->delay(now()->addMinutes(2));
                        Log::info("GenerateWinnerDiscountCodeJob dispatched for user ID {$winner['id']}, live show ID {$liveShowId}");
                    } catch (\Exception $e) {
                        Log::error("Failed to dispatch GenerateWinnerDiscountCodeJob for user ID {$winner['id']}: ".$e->getMessage());
                    }
                }
            }
            $liveShow->users()->updateExistingPivot($winner['id'], ['prize_won' => $prizeWon, 'winner_prize_id' => $prize->id ?? null]);
            // ShowPlayerAsWinnerEvent::dispatch($winner['id'], (string) $liveShowId);
            // Log::info("ShowPlayerAsWinnerEvent dispatched for user ID {$winner['id']}, live show ID {$liveShowId} and prize won: {$prizeWon}");

        }

        $liveShow->update(['winners_announced' => true]);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'winners', [
            'winners_announced' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Winners Regenerated and Able to be Announced Again who have Voucher prizes.',
            'winners_announced' => true,
            'winnerUsers' => $topMaxWinnersByScore,
        ]);
    }

    public function resendVoucherWinners($liveShowId)
    {
        $liveShow = LiveShow::find($liveShowId);
        if (! $liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }
        $voucherWinners = $liveShow->users()->wherePivot('is_winner', true)->wherePivot('discount_code', 'IS NOT', null)->get();

        foreach ($voucherWinners as $winner) {
            $prizeWon = $winner->pivot->prize_won;
            // Dispatch job to send winner email after 30 minutes
            try {
                SendWinnerVoucherEmailJob::dispatch($winner, $winner->pivot);
                Log::info("SendWinnerVoucherEmailJob dispatched for user ID {$winner->id}, live show ID {$liveShowId} and prize won: {$prizeWon}");
            } catch (\Exception $e) {
                // log the error
                Log::error("Failed to dispatch SendWinnerVoucherEmailJob for user ID {$winner->id}: ".$e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Resent voucher emails to winners with voucher prizes.',
        ]);
    }

    public function unannounceWinners(Request $request, $liveShowId): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (! $liveShow) {
            return response()->json(['success' => false, 'message' => 'Live show not found.'], 404);
        }

        $liveShow->update(['winners_announced' => false]);
        UserLiveShow::where('live_show_id', $liveShow->id)->update(['is_winner' => false]);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'winners', [
            'winners_announced' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Winners announcement has been cleared. You can announce winners again when ready.',
            'winners_announced' => false,
        ]);
    }

    /**
     * Announce the Special Quiz winners. Fully independent of the main winner
     * flow: ranks players by their special_score, assigns ranked special_gifts,
     * and writes only the special_* pivot fields.
     */
    public function announceSpecialWinners(Request $request, $liveShowId)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (! $liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }

        if ($liveShow->specialQuizzes()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'This show has no Special Quiz questions.',
            ], 422);
        }

        if ($liveShow->special_winners_announced) {
            return response()->json([
                'success' => false,
                'message' => 'Special Quiz winners have already been announced for this live show.',
                'special_winners_announced' => true,
            ], 422);
        }

        // Reset any previous special winners (independent of the main winners).
        UserLiveShow::where('live_show_id', $liveShow->id)->update([
            'is_special_winner' => false,
            'special_gift_id' => null,
            'special_prize_won' => null,
        ]);

        $maxWinners = (int) $liveShow->special_max_winners;
        if ($maxWinners <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Set "Max special winners" for this show before announcing special winners.',
            ], 422);
        }

        $topWinners = DB::table('users')
            ->join('user_live_shows', 'users.id', '=', 'user_live_shows.user_id')
            ->where('user_live_shows.live_show_id', $liveShow->id)
            ->where('user_live_shows.special_score', '>', 0)
            ->orderBy('user_live_shows.special_score', 'desc')
            ->take($maxWinners)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->user_id,
                    'name' => $user->name,
                    'score' => $user->special_score,
                ];
            });

        foreach ($topWinners as $index => $winner) {
            if ($winner['score'] <= 0) {
                continue;
            }

            $gift = $liveShow->specialGifts()->where('rank', $index + 1)->first();
            $prizeWon = $gift->name ?? 'n/a';

            $liveShow->users()->updateExistingPivot($winner['id'], [
                'is_special_winner' => true,
                'special_gift_id' => $gift->id ?? null,
                'special_prize_won' => $prizeWon,
            ]);

            // Voucher-type gifts: generate a Shopify discount code first.
            if ($gift && $gift->type === 'voucher' && $gift->discount_rule_id && $gift->discountRule?->shopify_id) {
                $showUser = UserLiveShow::where('user_id', $winner['id'])->where('live_show_id', $liveShowId)->first();
                if ($showUser && ! $showUser->special_discount_code) {
                    try {
                        GenerateSpecialWinnerDiscountCodeJob::dispatch(
                            $winner['id'],
                            (int) $liveShowId,
                            $gift->discountRule->shopify_id
                        )->delay(now()->addSeconds(20));
                    } catch (\Exception $e) {
                        Log::error("Failed to dispatch GenerateSpecialWinnerDiscountCodeJob for user ID {$winner['id']}: ".$e->getMessage());
                    }
                }
            }

            try {
                SendSpecialWinnerEmailJob::dispatch($winner['id'], (int) $liveShowId)
                    ->delay(now()->addMinutes((int) env('WINNER_EMAIL_DELAY', 30)));
            } catch (\Exception $e) {
                Log::error("Failed to dispatch SendSpecialWinnerEmailJob for user ID {$winner['id']}: ".$e->getMessage());
            }
        }

        $liveShow->update(['special_winners_announced' => true]);

        $winnersData = $liveShow->users()
            ->wherePivot('is_special_winner', true)
            ->select('users.id as user_id', 'user_live_shows.special_prize_won as prize_won')
            ->get()
            ->map(fn ($w) => ['user_id' => $w->user_id, 'prize_won' => $w->prize_won])
            ->toArray();

        ShowSpecialWinnersTabEvent::dispatch((string) $liveShow->id, $winnersData);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'special_winners', [
            'special_winners_announced' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Special Quiz winners have been announced. Special winner emails have been queued.',
            'special_winners_announced' => true,
            'winnerUsers' => $topWinners,
        ]);
    }

    public function unannounceSpecialWinners(Request $request, $liveShowId): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (! $liveShow) {
            return response()->json(['success' => false, 'message' => 'Live show not found.'], 404);
        }

        $liveShow->update(['special_winners_announced' => false]);
        UserLiveShow::where('live_show_id', $liveShow->id)->update([
            'is_special_winner' => false,
            'special_gift_id' => null,
            'special_prize_won' => null,
        ]);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'special_winners', [
            'special_winners_announced' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Special Quiz winners announcement has been cleared.',
            'special_winners_announced' => false,
        ]);
    }

    public function showSpecialWinnersTab($id): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($id);

        $winnersData = $liveShow->users()
            ->wherePivot('is_special_winner', true)
            ->select('users.id as user_id', 'user_live_shows.special_prize_won as prize_won')
            ->get()
            ->map(fn ($w) => ['user_id' => $w->user_id, 'prize_won' => $w->prize_won])
            ->toArray();

        ShowSpecialWinnersTabEvent::dispatch((string) $liveShow->id, $winnersData);

        return response()->json([
            'success' => true,
            'message' => 'Special winners tab shown for participants.',
        ]);
    }

    public function hideSpecialWinnersTab($id): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($id);
        HideSpecialWinnersTabEvent::dispatch((string) $liveShow->id);

        return response()->json([
            'success' => true,
            'message' => 'Special winners tab hidden for participants.',
        ]);
    }

    /**
     * Send a web-push notification to every player of the given live show.
     *
     * Triggered from the stream-management screen so an admin can nudge all
     * registered players (e.g. "the show is starting") on their devices.
     */
    public function notifyPlayers(Request $request, $liveShowId): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (! $liveShow) {
            return response()->json(['success' => false, 'message' => 'Live show not found.'], 404);
        }

        // Allow the admin to customise the copy, but fall back to sensible
        // German defaults that work for the typical "show is live" reminder.
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:500',
        ]);

        $title = $validated['title'] ?? null;
        $title = is_string($title) && trim($title) !== ''
            ? trim($title)
            : 'Badabing Live-Show';

        $message = $validated['message'] ?? null;
        $message = is_string($message) && trim($message) !== ''
            ? trim($message)
            : 'Die Live-Show läuft jetzt – steig ein und sichere dir deine Gewinnchance!';

        // Collect the IDs of every player attached to this live show.
        $playerIds = $liveShow->users()->pluck('users.id')->all();

        if (empty($playerIds)) {
            return response()->json([
                'success' => false,
                'message' => 'There are no players attached to this live show yet.',
            ], 422);
        }

        // Queue the push. Only players who actually subscribed to notifications
        // (i.e. have a saved push subscription) will receive it.
        $targetedCount = PushNotificationService::sendToUsers(
            userIds: $playerIds,
            title: $title,
            message: $message,
            data: [
                'url' => url('live-show-play/'.$liveShow->id),
                'tag' => 'live-show-'.$liveShow->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Push notification has been queued for '.$targetedCount.' player(s).',
            'targeted' => $targetedCount,
        ]);
    }

    public function apiGetLiveShowMessages($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $messages = $liveShow->messages()->with('user')->orderBy('created_at', 'desc')->limit(500)->get()->reverse()->values();

        return response()->json($messages);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        $user = Auth::guard('admin')->user();
        if (! $user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }
        $messageText = $request->input('message');

        // saving the message to the database
        $message = $liveShow->messages()->create([
            'user_id' => $user->id,
            'message' => $messageText,
        ]);

        // Broadcast the new message to users

        // Broadcast the message to other users (you can implement this using events and broadcasting)
        $event = LiveShowMessageEvent::dispatch([
            'id' => $message->id,
            'live_show_id' => $liveShow->id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'message' => $message->message,
            'created_at' => $message->created_at,
            'time_ago' => $message->time_ago,
        ]);

        $messageResponse = [
            'id' => $message->id,
            'live_show_id' => $liveShow->id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'message' => $message->message,
            'created_at' => $message->created_at,
            'time_ago' => $message->time_ago,
        ];

        // For simplicity, we'll just return the message in the response
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data' => $messageResponse,
            'user' => $user,
            'event' => $event,
        ], 200);
    }

    public function resetChat($id): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($id);
        $liveShow->messages()->delete();
        ResetChatEvent::dispatch((string) $liveShow->id);

        return response()->json([
            'success' => true,
            'message' => 'Chat reset successfully.',
        ]);
    }

    public function hideWinnersTab($id): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($id);
        HideLiveShowWinnersTabEvent::dispatch((string) $liveShow->id);

        return response()->json([
            'success' => true,
            'message' => 'Winners tab hidden for participants.',
        ]);
    }

    public function showWinnersTab($id): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($id);
        ShowLiveShowWinnersTabEvent::dispatch((string) $liveShow->id);

        return response()->json([
            'success' => true,
            'message' => 'Winners tab shown for participants.',
        ]);
    }

    public function updateChatStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'chat_enabled' => 'required|boolean',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        $chatEnabled = (bool) $request->boolean('chat_enabled');

        $liveShow->chat_enabled = $chatEnabled;
        $liveShow->save();

        LiveShowChatStatusUpdatedEvent::dispatch((string) $liveShow->id, $chatEnabled);

        return response()->json([
            'success' => true,
            'chat_enabled' => $chatEnabled,
            'message' => $chatEnabled ? 'Chat enabled successfully.' : 'Chat disabled successfully.',
        ]);
    }

    public function showGalleryImage(Request $request, $id): JsonResponse
    {
        $request->validate([
            'gallery_media_id' => 'required|integer|exists:gallery_media,id',
            'attachment_type' => 'nullable|in:show,question,end',
            'attachment_id' => 'nullable|integer|min:1',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        $media = GalleryMedia::findOrFail($request->input('gallery_media_id'));

        if (! $liveShow->isGalleryMediaAttached($media->id)) {
            return response()->json([
                'success' => false,
                'message' => 'This media is not attached to this stream.',
            ], 422);
        }

        $this->markGalleryAttachmentPlayed(
            $liveShow,
            (int) $media->id,
            $request->input('attachment_type'),
            $request->input('attachment_id') ? (int) $request->input('attachment_id') : null
        );

        $playbackStartedAt = $media->type === 'video' ? now() : null;
        ShowGalleryImageEvent::dispatch(
            (string) $liveShow->id,
            $media->path,
            $media->type,
            $playbackStartedAt?->toIso8601String(),
            null,
            $media->thumbnail ?? null
        );
        $liveShow->update(['media_visible' => true]);

        LiveShowMediaPlayed::dispatch((string) $liveShow->id);

        return response()->json([
            'success' => true,
            'total_seconds' => $media->total_seconds,
            'message' => 'Image shown on stream.',
        ]);
    }

    /**
     * Mark a specific show or question attachment row as played.
     */
    private function markGalleryAttachmentPlayed(
        LiveShow $liveShow,
        int $galleryMediaId,
        ?string $attachmentType,
        ?int $attachmentId
    ): void {
        if ($attachmentType === 'question' && $attachmentId) {
            LiveShowGalleryMedia::where('id', $attachmentId)
                ->where('live_show_id', $liveShow->id)
                ->where('gallery_media_id', $galleryMediaId)
                ->whereNotNull('before_question')
                ->update(['media_played' => true]);

            return;
        }

        if ($attachmentType === 'end' && $attachmentId) {
            LiveShowEndMedia::where('id', $attachmentId)
                ->where('live_show_id', $liveShow->id)
                ->where('gallery_media_id', $galleryMediaId)
                ->update(['media_played' => true]);

            return;
        }

        if ($attachmentType === 'show' && $attachmentId) {
            LiveShowGalleryMedia::where('id', $attachmentId)
                ->where('live_show_id', $liveShow->id)
                ->where('gallery_media_id', $galleryMediaId)
                ->update(['media_played' => true]);

            return;
        }

        // Covers both show-wide and before-question rows (both live in
        // live_show_gallery_media now).
        LiveShowGalleryMedia::where('live_show_id', $liveShow->id)
            ->where('gallery_media_id', $galleryMediaId)
            ->update(['media_played' => true]);

        LiveShowEndMedia::where('live_show_id', $liveShow->id)
            ->where('gallery_media_id', $galleryMediaId)
            ->update(['media_played' => true]);
    }

    public function hideGalleryImage($id): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($id);
        // $liveShow->galleryState?->update(['is_visible' => false]);
        $liveShow->update(['media_visible' => false]);
        HideGalleryImageEvent::dispatch((string) $liveShow->id);
        LiveShowMediaHidden::dispatch((string) $liveShow->id);

        return response()->json([
            'success' => true,
            'message' => 'Gallery overlay hidden on stream.',
        ]);
    }

    public function LiveShowMediaEvent($event, $liveShowId)
    {
        $liveShow = LiveShow::findOrFail($liveShowId);
        if ($event == 'show') {
            LiveShowMediaPlayed::dispatch((string) $liveShow->id);
        } elseif ($event == 'hide') {
            LiveShowMediaHidden::dispatch((string) $liveShow->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Media event dispatched successfully.',
            'event' => $event,
        ]);
    }

    public function apiGetLiveShowUsers($id, Request $request)
    {
        $liveShow = LiveShow::findOrFail($id);

        $skip = max((int) $request->input('skip', 0), 0);
        $take = max((int) $request->input('take', 100), 1);
        $search = trim((string) $request->input('search', ''));

        try {
            $totalUsers = $liveShow->users()->count();
            $quizService = new LiveShowQuizService;
            $players = $quizService->getSortedByScore($liveShow);

            $usersQuery = $players;
            if ($search !== '') {
                $usersQuery = $usersQuery->filter(function ($user) use ($search) {
                    return stripos($user->name, $search) !== false
                        || stripos($user->email, $search) !== false
                        || stripos($user->user_name ?? '', $search) !== false;
                });
            }

            $filteredUsers = (clone $usersQuery)->count();

            $users = $usersQuery
                ->skip($skip)
                ->take($take)

                ->map(function ($user) use ($id) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_online' => $user->pivot->is_online,
                        'is_winner' => $user->pivot->is_winner ?? null,
                        'status' => $user->pivot->status ?? null,
                        'score' => $user->pivot->score ?? null,
                        'prize_won' => $user->pivot->prize_won ?? null,
                        'joined_at' => $user->pivot->created_at
                            ? Carbon::parse($user->pivot->created_at)->format('d M Y, H:i')
                            : 'N/A',
                        'is_blocked' => $user->blockedLiveShows()
                            ->where('live_show_id', $id)
                            ->exists(),
                    ];
                })
                ->values();

            $playedCount = $players->filter(fn ($p) => ($p->pivot->score > 0 || $p->pivot->is_online))->count();

            return response()->json([
                'users' => $users,
                'totalUsers' => $totalUsers,
                'filteredUsers' => $filteredUsers,
                'playedCount' => $playedCount,
                'notParticipatedCount' => $totalUsers - $playedCount,
                'skip' => $skip,
                'take' => $take,
                'hasMore' => ($skip + $users->count()) < $filteredUsers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching live show users.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Special Quiz player ranking for the host stream-management panel.
     * Mirrors apiGetLiveShowUsers but sorts by special_score and returns
     * special-winner fields only.
     */
    public function apiGetSpecialLiveShowUsers($id, Request $request)
    {
        $liveShow = LiveShow::findOrFail($id);

        $skip = max((int) $request->input('skip', 0), 0);
        $take = max((int) $request->input('take', 100), 1);
        $search = trim((string) $request->input('search', ''));

        try {
            $totalUsers = $liveShow->users()->count();
            $quizService = new LiveShowQuizService;
            $players = $quizService->getSortedSpecialPlayers($liveShow);

            $usersQuery = $players;
            if ($search !== '') {
                $usersQuery = $usersQuery->filter(function ($user) use ($search) {
                    return stripos($user->name, $search) !== false
                        || stripos($user->email, $search) !== false
                        || stripos($user->user_name ?? '', $search) !== false;
                });
            }

            $filteredUsers = (clone $usersQuery)->count();

            $users = $usersQuery
                ->skip($skip)
                ->take($take)
                ->map(function ($user) use ($id) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_online' => $user->pivot->is_online,
                        'is_winner' => $user->pivot->is_special_winner ?? null,
                        'status' => $user->pivot->status ?? null,
                        'score' => $user->pivot->special_score ?? 0,
                        'prize_won' => $user->pivot->special_prize_won ?? null,
                        'joined_at' => $user->pivot->created_at
                            ? Carbon::parse($user->pivot->created_at)->format('d M Y, H:i')
                            : 'N/A',
                        'is_blocked' => $user->blockedLiveShows()
                            ->where('live_show_id', $id)
                            ->exists(),
                    ];
                })
                ->values();

            $playedCount = $players->filter(fn ($p) => ($p->pivot->special_score > 0 || $p->pivot->is_online))->count();

            return response()->json([
                'users' => $users,
                'totalUsers' => $totalUsers,
                'filteredUsers' => $filteredUsers,
                'playedCount' => $playedCount,
                'notParticipatedCount' => $totalUsers - $playedCount,
                'skip' => $skip,
                'take' => $take,
                'hasMore' => ($skip + $users->count()) < $filteredUsers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching special quiz users.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function allPlayers($id)
    {
        $liveShow = LiveShow::findOrFail($id);

        $players = $liveShow->users()
            ->withPivot(['score', 'status', 'is_winner', 'prize_won', 'is_online', 'created_at', 'winner_cash_email_sent_at', 'winner_voucher_email_sent_at', 'winner_email_sent_at', 'winner_email_sent_status', 'winner_voucher_email_sent_status', 'winner_cash_email_sent_status', 'winner_prize_id', 'discount_code'])
            ->withExists(['blockedLiveShows as is_blocked_for_live_show' => function ($query) use ($id) {
                $query->where('live_show_id', $id);
            }])
            ->orderByDesc('user_live_shows.score')
            ->get()->map(function ($player) {
                $player->pivot->winnerPrize = LiveShowWinnerPrize::find($player->pivot->winner_prize_id);

                return $player;
            });

        $hasSpecialQuiz = $liveShow->specialQuizzes()->exists();
        $specialPlayers = collect();
        if ($hasSpecialQuiz) {
            $quizService = new LiveShowQuizService;
            $specialPlayers = $quizService->getSortedSpecialPlayers($liveShow)->map(function ($player) {
                $player->pivot->specialGift = SpecialGift::find($player->pivot->special_gift_id);

                return $player;
            });
        }

        $totalPlayers = $liveShow->users()->count();
        $isGlobalView = false;

        return view('admin.live-shows.all-players', compact('liveShow', 'players', 'specialPlayers', 'hasSpecialQuiz', 'totalPlayers', 'isGlobalView'));
    }

    public function allLiveShowPlayers()
    {
        $blockedPairs = DB::table('live_show_block_users')
            ->select('user_id', 'live_show_id')
            ->get()
            ->mapWithKeys(fn ($row) => ["{$row->user_id}:{$row->live_show_id}" => true]);

        $players = UserLiveShow::query()
            ->with(['user', 'liveShow', 'winnerPrize'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (UserLiveShow $participation) => $this->mapParticipationToPlayerRow($participation, $blockedPairs))
            ->filter()
            ->values();

        $hasSpecialQuiz = LiveShowQuiz::where('is_special', true)->exists();
        $specialPlayers = collect();

        if ($hasSpecialQuiz) {
            $liveShowIdsWithSpecialQuiz = LiveShowQuiz::where('is_special', true)
                ->distinct()
                ->pluck('live_show_id');

            $specialPlayers = UserLiveShow::query()
                ->with(['user', 'liveShow', 'specialGift'])
                ->whereIn('live_show_id', $liveShowIdsWithSpecialQuiz)
                ->get()
                ->map(fn (UserLiveShow $participation) => $this->mapParticipationToPlayerRow($participation, $blockedPairs))
                ->filter()
                ->sortByDesc(fn ($player) => $player->pivot->special_score ?? 0)
                ->values();
        }

        $totalPlayers = UserLiveShow::count();
        $liveShow = null;
        $isGlobalView = true;

        return view('admin.live-shows.all-players', compact('liveShow', 'players', 'specialPlayers', 'hasSpecialQuiz', 'totalPlayers', 'isGlobalView'));
    }

    private function mapParticipationToPlayerRow(UserLiveShow $participation, $blockedPairs)
    {
        $user = $participation->user;
        if (! $user) {
            return null;
        }

        $player = clone $user;
        $player->id = $user->id;
        $player->pivot = $participation;
        $player->pivot->winnerPrize = $participation->winnerPrize;
        $player->pivot->specialGift = $participation->specialGift;
        $player->participant_live_show_id = $participation->live_show_id;
        $player->participant_live_show_title = $participation->liveShow?->title ?? '—';
        $blockKey = "{$participation->user_id}:{$participation->live_show_id}";
        $player->is_blocked_for_live_show = $blockedPairs->has($blockKey);

        return $player;
    }

    public function extractYouTubeId(string $url): ?string
    {
        // Handle HTML entities like &amp; in the URL
        $url = html_entity_decode($url);

        // Match multiple possible YouTube URL formats
        $pattern = '%(?:youtube\.com/(?:.*v=|(?:embed|shorts)/)|youtu\.be/)([^?&/]+)%i';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function calculateEachWinnerPrize(LiveShow $liveShow)
    {
        $winnersCount = $liveShow->users()->wherePivot('status', 'registered')->wherePivot('is_winner', true)->count();
        if ($winnersCount > 0) {
            return $liveShow->prize_amount / $winnersCount;
        }

        return 0;
    }

    public function toggleBlockStatusForPlayer(Request $request, $liveShowId, $userId)
    {
        $request->validate([
            'action' => 'required|string|in:block,unblock',
        ]);

        $action = $request->input('action');
        $liveShow = LiveShow::findOrFail($liveShowId);
        $user = $liveShow->users()->where('user_id', $userId)->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User not found in this live show.'], 404);
        }

        if ($action === 'block') {
            $liveShow->blockedUsers()->syncWithoutDetaching($userId);
        } else {
            $liveShow->blockedUsers()->detach($userId);
        }

        // event to update the player block status
        $isBlocked = $action === 'block' ? true : false;

        UserBlockFromLiveShowEvent::dispatch($liveShowId, $userId, $isBlocked);

        return response()->json(['success' => true, 'message' => 'Player block status updated to '.($isBlocked ? 'blocked' : 'unblocked').'.', 'user' => $user]);
    }

    public function resetPlayerScore($liveShowId, $userId): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($liveShowId);
        $user = $liveShow->users()->where('user_id', $userId)->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User not found in this live show.'], 404);
        }

        $liveShow->users()->updateExistingPivot($userId, [
            'score' => 0,
            'is_winner' => false,
            'prize_won' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Player score reset successfully.',
        ]);
    }

    /**
     * Re-send a single winner email (generic winner, voucher, or cash) to a
     * player. The relevant *_status field is cleared first so the
     * SendWinnerEmailJob will attempt only that email again while skipping the
     * others whose status is still set.
     */
    public function resendPlayerEmail(Request $request, $liveShowId, $userId): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:winner,voucher,cash,special_winner,special_type',
        ]);

        $liveShow = LiveShow::findOrFail($liveShowId);

        $showUser = UserLiveShow::where('live_show_id', $liveShowId)
            ->where('user_id', $userId)
            ->first();

        if (! $showUser) {
            return response()->json(['success' => false, 'message' => 'Player not found in this live show.'], 404);
        }

        $user = $showUser->user;

        if (! $user || ! $user->email) {
            return response()->json(['success' => false, 'message' => 'Player has no email address on file.'], 422);
        }

        $type = $request->input('type');

        if (in_array($type, ['special_winner', 'special_type'], true)) {
            $fieldMap = [
                'special_winner' => ['special_winner_email_sent_status', 'special_winner_email_sent_at'],
                'special_type' => ['special_type_email_sent_status', 'special_type_email_sent_at'],
            ];

            [$statusField, $sentAtField] = $fieldMap[$type];

            $showUser->{$statusField} = null;
            $showUser->{$sentAtField} = null;
            $showUser->save();

            try {
                SendSpecialWinnerEmailJob::dispatchSync((int) $userId, (int) $liveShowId);
            } catch (\Throwable $e) {
                Log::error("Failed to re-send {$type} email for user ID {$userId}, live show ID {$liveShowId}: ".$e->getMessage().' '.now()->format('d M Y, H:i'));

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to re-send email: '.$e->getMessage(),
                ], 500);
            }

            $showUser->refresh();
            $sent = $showUser->{$sentAtField} !== null;

            return response()->json([
                'success' => $sent,
                'message' => $sent
                    ? ucfirst(str_replace('_', ' ', $type)).' email re-sent successfully.'
                    : 'Could not re-send '.$type.' email. '.($showUser->{$statusField} ?? ''),
                'status' => $showUser->{$statusField},
                'sent_at' => $showUser->{$sentAtField},
            ]);
        }

        $fieldMap = [
            'winner' => ['winner_email_sent_status', 'winner_email_sent_at'],
            'voucher' => ['winner_voucher_email_sent_status', 'winner_voucher_email_sent_at'],
            'cash' => ['winner_cash_email_sent_status', 'winner_cash_email_sent_at'],
        ];

        [$statusField, $sentAtField] = $fieldMap[$type];

        // Clear the status/sent-at so the job re-sends only this email.
        $showUser->{$statusField} = null;
        $showUser->{$sentAtField} = null;
        $showUser->save();

        $prizeWon = (string) ($showUser->prize_won ?? 'n/a');

        try {
            // Run synchronously so we can report the outcome back to the admin.
            SendWinnerEmailJob::dispatchSync((int) $userId, $prizeWon, $liveShow);
        } catch (\Throwable $e) {
            Log::error("Failed to re-send {$type} email for user ID {$userId}, live show ID {$liveShowId}: ".$e->getMessage().' '.now()->format('d M Y, H:i'));

            return response()->json([
                'success' => false,
                'message' => 'Failed to re-send email: '.$e->getMessage(),
            ], 500);
        }

        $showUser->refresh();

        $sent = $showUser->{$sentAtField} !== null;

        return response()->json([
            'success' => $sent,
            'message' => $sent
                ? ucfirst($type).' email re-sent successfully.'
                : 'Could not re-send '.$type.' email. '.($showUser->{$statusField} ?? ''),
            'status' => $showUser->{$statusField},
            'sent_at' => $showUser->{$sentAtField},
        ]);
    }

    public function updateLiveShow(Request $request, $id)
    {
        $liveShow = LiveShow::findOrFail($id);

        $updateMessage = '';

        $request->validate([
            'status' => 'required|in:scheduled,live,completed',
        ]);

        $newStatus = $request->input('status');

        if ($newStatus === 'live' && $liveShow->status !== 'live') {
            $liveShow->start_time = now();
        }

        if ($newStatus === 'completed' && $liveShow->status !== 'completed') {
            $liveShow->end_time = now();
            $updateMessage = 'Die Live-Sendung ist beendet. Vielen Dank für Ihre Teilnahme! Die nächste Show ist am '.Carbon::parse($this->getNextScheduledLiveShowDate())->format('d.m.Y H:i').'Uhr statt.';
        }

        $liveShow->status = $newStatus;
        $liveShow->save();

        UpdateLiveShowEvent::dispatch((string) $liveShow->id, $liveShow->status, $updateMessage);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'status', [
            'status' => $liveShow->status,
        ]);

        return response()->json(['message' => 'Live show has been updated successfully.', 'status' => $newStatus]);
    }

    public function reupdateLiveShow(Request $request, $id)
    {
        $liveShow = LiveShow::findOrFail($id);

        $updateMessage = '';

        $request->validate([
            'status' => 'required|in:scheduled,live,completed',
        ]);

        $newStatus = $request->input('status');

        if ($newStatus === 'live' && $liveShow->status !== 'live') {
            $liveShow->start_time = now();
        }

        if ($newStatus === 'completed' && $liveShow->status !== 'completed') {
            $liveShow->end_time = now();
            $updateMessage = 'Die Live-Sendung ist beendet. Vielen Dank für Ihre Teilnahme! Die nächste Show ist am '.Carbon::parse($this->getNextScheduledLiveShowDate())->format('d.m.Y H:i').'Uhr statt.';
        }

        $liveShow->status = $newStatus;
        $liveShow->save();

        UpdateLiveShowEvent::dispatch((string) $liveShow->id, $liveShow->status, $updateMessage);

        LiveShowAdminStateEvent::dispatch((string) $liveShow->id, 'status', [
            'status' => $liveShow->status,
        ]);

        return response()->json(['message' => 'Live show has been updated successfully.', 'status' => $newStatus]);
    }

    public function getUsersQuizResponses(
        Request $request,
        LiveShowQuizService $quizService, //  Inject the service via method injection
        $id,
        $quiz_id
    ): JsonResponse {

        $triggerEvent = $request->input('triggerEvent', 0);

        // 1. VALIDATION & DATA FETCHING
        $liveShow = LiveShow::find($id);
        if (! $liveShow) {
            return response()->json(['success' => false, 'message' => 'Live show not found.'], 404);
        }

        // Find the quiz and eager-load its options, as the service needs them
        $quiz = $liveShow->quizzes()->with('options')->find($quiz_id);
        if (! $quiz) {
            return response()->json(['success' => false, 'message' => 'Quiz not found for this live show.'], 404);
        }

        // Delegate the complex calculation logic to the service. Special
        // questions read their stats from the dedicated special table.
        $statistics = $quiz->is_special
            ? $quizService->calculateSpecialResponseStatistics($quiz)
            : $quizService->calculateResponseStatistics($quiz);

        // Fetch any other data needed for the response
        $userQuizzes = $quiz->userQuizzes()->with('userQuizResponses')->get();
        //  take the correct answer id
        $correctOption = $quiz->options->firstWhere('is_correct', true);
        $correctOptionId = $correctOption ? $correctOption->id : null;

        if ($triggerEvent == 1) {
            // 2. BROADCASTING
            LiveShowQuizUserResponses::dispatch((string) $liveShow->id, (string) $quiz->id, $statistics, $correctOptionId);
        }

        // The controller's job is to format the final JSON response
        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'correct_option_id' => $correctOptionId,
        ]);
    }

    public function resetGame(Request $request, $liveShowId)
    {
        $liveShow = LiveShow::find($liveShowId);
        if (! $liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }
        // remove all user quiz responses

        UserQuizResponse::whereIn('user_quiz_id', function ($query) use ($liveShowId) {
            $query->select('id')
                ->from('user_quizzes')
                ->where('live_show_id', $liveShowId);
        })->delete();
        UserQuiz::where('live_show_id', $liveShowId)->delete();

        // Clear Special Quiz answers for this show's special questions too.
        UserSpecialQuizResponse::whereIn('quiz_id', function ($query) use ($liveShowId) {
            $query->select('id')
                ->from('live_show_quizzes')
                ->where('live_show_id', $liveShowId)
                ->where('is_special', true);
        })->delete();

        // Detach all users from the live show
        $liveShow->users()->detach();
        $liveShow->update(['winners_announced' => false, 'special_winners_announced' => false]);
        LiveShowQuiz::where('live_show_id', $liveShowId)->update(['has_shown' => false]);
        LiveShowGalleryMedia::where('live_show_id', $liveShowId)->update(['media_played' => false]);

        event(new GameResetEvent($liveShowId));

        return response()->json(['success' => true, 'message' => 'Game has been reset successfully.']);
    }

    public function streamBroadcaster($id)
    {
        $liveShow = LiveShow::with(['quizzes.options'])->findOrFail($id);

        // The main host (full control: go live, BGM, remove co-hosts) is a single
        // designated account. Every other admin opening this page joins as a co-host
        // who can still publish camera/mic and play media on the stream.
        $mainHostEmail = 'admin@baaboo.com';
        $isMainHost = auth()->user()->email === $mainHostEmail;

        return view('admin.live-shows.stream-broadcaster', compact('liveShow', 'isMainHost', 'mainHostEmail'));
    }

    /**
     * Claim the broadcaster page for a given live show.
     *
     * The blade view generates a unique `tab_id` per browser tab and calls
     * this endpoint as soon as the page is ready. We persist that id into
     * `live_shows.host_browser_tab` (always overwriting whatever was there
     * before – latest tab wins) and fire a Pusher event so any previously
     * active broadcaster tab can immediately stop streaming and show a
     * "Opened elsewhere" overlay.
     *
     * The endpoint always succeeds: the newest claim is always honoured.
     * The client is expected to compare its own local `tab_id` against the
     * `active_tab_id` we return (and against incoming Pusher events) to
     * decide whether it is still the owner.
     */
    public function claimBroadcasterTab(Request $request, $id): JsonResponse
    {
        $request->validate([
            'tab_id' => 'required|string|max:64',
        ]);

        $liveShow = LiveShow::findOrFail($id);

        $tabId = (string) $request->input('tab_id');

        // Replace the current owner with the newest tab.
        $liveShow->host_browser_tab = $tabId;
        $liveShow->save();

        // Notify every open broadcaster tab on the existing pusher channel.
        // Old tabs (whose local id != this one) will use the payload to
        // kick themselves out.
        event(new BroadcasterTabClaimedEvent($liveShow->id, $tabId));

        return response()->json([
            'success' => true,
            'live_show_id' => $liveShow->id,
            'active_tab_id' => $liveShow->host_browser_tab,
        ]);
    }

    /**
     * Return the currently active broadcaster tab id (if any).
     *
     * Used by the blade view as a polling fallback in case the Pusher
     * `BroadcasterTabClaimedEvent` is missed (e.g. when the tab was
     * throttled in the background and dropped the websocket). If the
     * returned `active_tab_id` does not match the tab's locally generated
     * id, the client treats itself as superseded.
     */
    public function getBroadcasterTab($id): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($id);

        return response()->json([
            'live_show_id' => $liveShow->id,
            'active_tab_id' => $liveShow->host_browser_tab,
        ]);
    }

    /**
     * Return the gallery media attached to a live show, used by the broadcaster
     * page to render a media picker for the canvas video overlay.
     *
     * Optional query params:
     *   ?type=video|image  filter by media type (defaults to video)
     */
    public function getAttachedMedia(Request $request, $id): JsonResponse
    {

        $allowedTypes = ['video', 'image'];

        $liveShow = LiveShow::findOrFail($id);

        $query = $liveShow->galleryMedia()->whereIn('type', $allowedTypes);

        $items = $query->get()->map(function (GalleryMedia $m) {
            return [
                'id' => $m->id,
                'type' => $m->type,
                'title' => $m->title,
                'original_name' => $m->original_name,
                'url' => $m->url,
                'thumbnail' => $m->thumbnail,
                'total_seconds' => $m->total_seconds,
                'mime_type' => $m->mime_type,
                'file_size' => $m->file_size,
                'sort_order' => $m->pivot->sort_order ?? null,
                'path' => $m->path,
            ];
        });

        return response()->json([
            'live_show_id' => $liveShow->id,
            'type' => $allowedTypes,
            'count' => $items->count(),
            'media' => $items,
        ]);
    }

    public function saveRoomID(Request $request, $id)
    {
        $request->validate([
            'room_id' => 'required|string|max:255',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        $liveShow->stream_id = $request->input('room_id');
        $liveShow->save();

        // call event set broadcast room id
        if (auth()->user()->email === 'admin@baaboo.com') {
            event(new SetBroadcastRoomIdEvent($liveShow->id, $liveShow->stream_id));
        }

        return response()->json(['message' => 'Room ID saved successfully!', 'room_id' => $liveShow->stream_id]);
    }

    public function exportAllChatsOfLiveShowAsCSV($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $chats = $liveShow->messages()->get();
        $csv = fopen('php://temp', 'w');
        fputcsv($csv, ['User', 'Message', 'Created At']);
        foreach ($chats as $chat) {
            fputcsv($csv, [$chat->user->name, $chat->message, $chat->created_at]);
        }

        rewind($csv);
        $csvContents = stream_get_contents($csv);
        $filename = 'chats_'.$liveShow->title.'.csv';
        fclose($csv);

        return response($csvContents)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportAllUsersOfLiveShowAsCSV($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        // Fetch users with their UserQuiz results
        $users = $liveShow->users()->with(['quizzes' => function ($query) use ($id) {
            $query->where('live_show_id', $id);
        }])->get();
        $csv = fopen('php://temp', 'w');
        // Header: User info + summary of quizzes
        fputcsv($csv, ['User', 'Email', 'Created At', 'Total Quizzes', 'Total Correct Answers', 'Total Score']);
        foreach ($users as $user) {
            $userQuizzes = $user->quizzes;
            $totalQuizzes = $userQuizzes->count();
            fputcsv($csv, [$user->name, $user->email, $user->created_at, $totalQuizzes, $user->pivot->score, $user->pivot->is_winner ? 'Yes' : 'No', $user->pivot->prize_won]);
        }

        // download the csv file
        rewind($csv);
        $csvContents = stream_get_contents($csv);
        $filename = 'users_'.$liveShow->title.'.csv';
        fclose($csv);

        return response($csvContents)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportAllQuizzesOfLiveShowAsCSV($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $quizzes = $liveShow->quizzes()->get();
        $csv = fopen('php://temp', 'w');
        fputcsv($csv, ['Quiz', 'Question', 'Created At']);
        foreach ($quizzes as $quiz) {
            fputcsv($csv, [$quiz->question, $quiz->created_at]);
        }
        fclose($csv);

        // download the csv file
        return response()->download($csv, 'quizzes'.$liveShow->title.'.csv');
    }

    public function viewDetails($id)
    {
        $liveShow = LiveShow::with(['creator', 'winnerPrizes', 'specialGifts', 'quizzes.options'])->findOrFail($id);
        $totalQuestions = $liveShow->quizzes->count();
        $specialQuestionsCount = $liveShow->quizzes->where('is_special', true)->count();

        return view('admin.live-shows.view-details', compact('liveShow', 'totalQuestions', 'specialQuestionsCount'));
    }

    public function getPlayerResponses($liveShowId, $userId)
    {
        $liveShow = LiveShow::findOrFail($liveShowId);

        $responses = UserQuizResponse::where('user_id', $userId)
            ->whereHas('userQuiz', function ($q) use ($liveShowId) {
                $q->where('live_show_id', $liveShowId);
            })
            ->with(['quiz.options', 'quizOption'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($response) {
                return [
                    'id' => $response->id,
                    'question' => $response->quiz->question ?? 'N/A',
                    'selected_option' => $response->quizOption->option_text ?? 'N/A',
                    'answered_at' => $response->created_at->format('d M Y, H:i:s'),
                    'is_correct' => (bool) $response->is_correct,
                    'seconds_to_submit' => $response->seconds_to_submit,
                    'response_score' => $response->response_score,
                    'options' => $response->quiz->options->map(function ($opt) {
                        return [
                            'id' => $opt->id,
                            'option_text' => $opt->option_text,
                            'is_correct' => (bool) $opt->is_correct,
                        ];
                    }),
                    'created_at' => $response->created_at->format('d M Y, H:i:s'),
                ];
            });

        return response()->json([
            'responses' => $responses,
        ]);
    }

    public function getSpecialPlayerResponses($liveShowId, $userId)
    {
        LiveShow::findOrFail($liveShowId);

        $responses = UserSpecialQuizResponse::where('user_id', $userId)
            ->whereHas('quiz', function ($q) use ($liveShowId) {
                $q->where('live_show_id', $liveShowId)->where('is_special', true);
            })
            ->with(['quiz.options', 'quizOption'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($response) {
                return [
                    'id' => $response->id,
                    'question' => $response->quiz->question ?? 'N/A',
                    'selected_option' => $response->quizOption->option_text ?? 'N/A',
                    'answered_at' => $response->created_at->format('d M Y, H:i:s'),
                    'is_correct' => (bool) $response->is_correct,
                    'seconds_to_submit' => $response->seconds_to_submit,
                    'response_score' => $response->response_score,
                    'options' => $response->quiz->options->map(function ($opt) {
                        return [
                            'id' => $opt->id,
                            'option_text' => $opt->option_text,
                            'is_correct' => (bool) $opt->is_correct,
                        ];
                    }),
                    'created_at' => $response->created_at->format('d M Y, H:i:s'),
                ];
            });

        return response()->json([
            'responses' => $responses,
        ]);
    }

    public function exportAllParticipantsCSV($id)
    {
        $liveShow = LiveShow::findOrFail($id);

        $quizService = new LiveShowQuizService;
        $players = $quizService->getSortedPlayers($liveShow);

        $totalQuestions = $liveShow->quizzes()->count();

        $csv = fopen('php://temp', 'w');
        fputcsv($csv, ['#', 'Name', 'Email', 'Score', 'Correct Answers', 'Total Questions', 'Is Winner', 'Prize Won', 'Status', 'Joined At']);

        foreach ($players as $index => $player) {
            $correctAnswers = UserQuizResponse::where('user_id', $player->id)
                ->whereHas('userQuiz', fn ($q) => $q->where('live_show_id', $id))
                ->where('is_correct', true)
                ->count();

            fputcsv($csv, [
                $index + 1,
                $player->name,
                $player->email,
                $player->pivot->score ?? 0,
                $correctAnswers,
                $totalQuestions,
                $player->pivot->is_winner ? 'Yes' : 'No',
                $player->pivot->prize_won ?? 'N/A',
                ucfirst($player->pivot->status ?? ''),
                $player->pivot->created_at,
            ]);
        }

        rewind($csv);
        $csvContents = stream_get_contents($csv);
        fclose($csv);

        $filename = 'participants_'.str_replace(' ', '_', $liveShow->title).'.csv';

        return response($csvContents)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportWinnersCSV($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $quizService = new LiveShowQuizService;
        $winners = $quizService->getSortedPlayers($liveShow)->where('pivot.is_winner', true);

        $csv = fopen('php://temp', 'w');
        fputcsv($csv, ['#', 'Name', 'Email', 'Score',  'Is Winner', 'Prize Won', 'Voucher Code', 'Status', 'Joined At']);
        foreach ($winners as $index => $winner) {
            fputcsv($csv, [
                $index + 1,
                $winner->name,
                $winner->email,
                $winner->pivot->score ?? 0,
                $winner->pivot->is_winner ? 'Yes' : 'No',
                $winner->pivot->prize_won ?? 'N/A',
                $winner->pivot->discount_code ?? 'N/A',
                ucfirst($winner->pivot->status ?? ''),
                $winner->pivot->created_at,
            ]);
        }
        rewind($csv);
        $csvContents = stream_get_contents($csv);
        fclose($csv);
        $filename = 'winners_'.str_replace(' ', '_', $liveShow->title).'.csv';

        return response($csvContents)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportSpecialWinnersCSV($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $quizService = new LiveShowQuizService;
        $winners = $quizService->getSortedSpecialPlayers($liveShow)
            ->filter(fn ($player) => (bool) $player->pivot->is_special_winner);

        $csv = fopen('php://temp', 'w');
        fputcsv($csv, ['#', 'Name', 'Email', 'Special Score', 'Is Special Winner', 'Prize Won', 'Gift Type', 'Special Discount Code', 'Status', 'Joined At']);

        foreach ($winners as $index => $winner) {
            $gift = SpecialGift::find($winner->pivot->special_gift_id);

            fputcsv($csv, [
                $index + 1,
                $winner->name,
                $winner->email,
                $winner->pivot->special_score ?? 0,
                $winner->pivot->is_special_winner ? 'Yes' : 'No',
                $winner->pivot->special_prize_won ?? 'N/A',
                $gift->type ?? 'N/A',
                $winner->pivot->special_discount_code ?? 'N/A',
                ucfirst($winner->pivot->status ?? ''),
                $winner->pivot->created_at,
            ]);
        }

        rewind($csv);
        $csvContents = stream_get_contents($csv);
        fclose($csv);
        $filename = 'special_winners_'.str_replace(' ', '_', $liveShow->title).'.csv';

        return response($csvContents)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportPlayerCSV($liveShowId, $userId)
    {
        $liveShow = LiveShow::findOrFail($liveShowId);
        $user = User::findOrFail($userId);

        $responses = UserQuizResponse::where('user_id', $userId)
            ->whereHas('userQuiz', fn ($q) => $q->where('live_show_id', $liveShowId))
            ->with(['quiz', 'quizOption'])
            ->orderBy('created_at', 'asc')
            ->get();

        $csv = fopen('php://temp', 'w');
        fputcsv($csv, ['#', 'Question', 'Selected Answer', 'Correct?', 'Time (seconds)', 'Score', 'Answered At']);

        foreach ($responses as $index => $response) {
            fputcsv($csv, [
                $index + 1,
                $response->quiz->question ?? 'N/A',
                $response->quizOption->option_text ?? 'N/A',
                $response->is_correct ? 'Yes' : 'No',
                $response->seconds_to_submit,
                $response->response_score,
                $response->created_at->format('d M Y, H:i:s'),
            ]);
        }

        rewind($csv);
        $csvContents = stream_get_contents($csv);
        fclose($csv);

        $filename = 'player_'.str_replace(' ', '_', $user->name).'_show_'.str_replace(' ', '_', $liveShow->title).'.csv';

        return response($csvContents)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function copyLiveShow($id)
    {
        $liveShow = LiveShow::with('endMedia')->findOrFail($id);
        // change the title to the new title
        $newTitle = 'Copy :'.$liveShow->title.' - '.now()->format('Y-m-d').' - '.uniqid();
        $newLiveShow = $liveShow->replicate();
        $newLiveShow->title = $newTitle;
        $newLiveShow->is_test_show = true;
        $newLiveShow->winners_announced = false;
        $newLiveShow->save();
        // copy the quizzes
        $quizzes = $liveShow->quizzes()->with('questionMedia')->get();
        foreach ($quizzes as $quiz) {
            $newQuiz = LiveShowQuiz::create([
                'live_show_id' => $newLiveShow->id,
                'question' => $quiz->question,
                'is_special' => $quiz->is_special,
                'sorting_order' => $quiz->sorting_order,
            ]);
            $newQuiz->options()->createMany($quiz->options->map(function ($option) {
                return [
                    'option_text' => $option->option_text,
                    'is_correct' => $option->is_correct,
                ];
            }));

            // copy media attached before this question
            foreach ($quiz->questionMedia as $media) {
                LiveShowGalleryMedia::create([
                    'live_show_id' => $newLiveShow->id,
                    'before_question' => $newQuiz->id,
                    'gallery_media_id' => $media->id,
                    'sort_order' => $media->pivot->sort_order ?? 0,
                ]);
            }
        }
        // winners
        $winnerPrizes = $liveShow->winnerPrizes()->get();
        foreach ($winnerPrizes as $winnerPrize) {
            $newWinnerPrize = LiveShowWinnerPrize::create([
                'live_show_id' => $newLiveShow->id,
                'prize' => $winnerPrize->prize,
                'rank' => $winnerPrize->rank,
                'is_voucher' => $winnerPrize->is_voucher,
                'voucher_amount' => $winnerPrize->voucher_amount,
            ]);
        }
        // media gallery
        $mediaGallery = $liveShow->galleryMedia()->get();
        foreach ($mediaGallery as $media) {
            $newMedia = LiveShowGalleryMedia::create([
                'live_show_id' => $newLiveShow->id,
                'gallery_media_id' => $media->id,
                'sort_order' => $media->sort_order ?? 0,
            ]);
        }
        // end-of-show media
        foreach ($liveShow->endMedia as $media) {
            LiveShowEndMedia::create([
                'live_show_id' => $newLiveShow->id,
                'gallery_media_id' => $media->id,
                'sort_order' => $media->pivot->sort_order ?? 0,
                'media_played' => false,
            ]);
        }

        return redirect()->route('admin.live-shows.show', $newLiveShow->id)->with('success', 'Live show copied successfully!');
    }

    private function getNextScheduledLiveShowDate()
    {
        $nextScheduledLiveShow = LiveShow::where('status', 'scheduled')->orderBy('scheduled_at', 'asc')->first();
        if ($nextScheduledLiveShow) {
            return $nextScheduledLiveShow->scheduled_at;
        }

        return null;
    }

    public function mediaHidden($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $liveShow->update(['media_visible' => false]);

        LiveShowMediaHidden::dispatch($liveShow->id);
        HideGalleryImageEvent::dispatch($liveShow->id);

        return response()->json(['message' => 'Media hidden successfully!']);
    }

    public function mediaPlayed($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        LiveShowMediaPlayed::dispatch($liveShow->id);

        return response()->json(['message' => 'Media played successfully!']);
    }

    public function injectMediaStream($liveShowStreamId, $galleryMediaPath)
    {
        // $liveShow = LiveShow::findOrFail($liveShowId);
        // $galleryMedia = $liveShow->galleryMedia()->findOrFail($galleryMediaId);
        $mediaUrl = $galleryMediaPath; // The media URL from your DB
        $roomId = $liveShowStreamId; // Current live Room ID
        $streamId = 'media_'.uniqid(); // Unique ID for this media stream

        $appId = env('ZEGO_APP_ID');
        $serverSecret = env('ZEGO_SERVER_SECRET');
        $timestamp = time();
        $signatureNonce = bin2hex(random_bytes(8));
        $signature = md5($appId.$signatureNonce.$serverSecret.$timestamp);

        // Call the Zego Server API directly
        $response = Http::get('https://rtc-api.zego.im/', [
            'api' => '1',
            'ver' => '1',
            'UserId' => 'media-bot-'.uniqid(),
            'Action' => 'AddStream',
            'AppId' => $appId,
            'SignatureNonce' => $signatureNonce,
            'Timestamp' => $timestamp,
            'Signature' => $signature,
            'SignatureVersion' => '2.0',
            'RoomId' => $roomId,
            'StreamId' => $streamId,
            'StreamUrl' => $mediaUrl, // The URL of the media you want to push
        ]);

        Log::info('Inject Media Stream Response: ', ['response' => $response->json(), 'mediaUrl' => $mediaUrl, 'roomId' => $roomId, 'streamId' => $streamId]);

        return response()->json($response->json());
    }
}
