<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatFilterTier;
use App\Models\ChatFilterViolation;
use App\Models\ChatFilterWord;
use App\Models\User;
use App\Services\ChatFilterService;
use Illuminate\Http\Request;

/**
 * chat_filter_module: admin panel for the dynamic chat filter.
 * Manage tiers (actions/toggles), the word dictionary (CRUD) and the
 * violations watchlist (review + unmute/unblock).
 */
class ChatFilterController extends Controller
{
    // chat_filter_module: tiers overview + per-tier settings.
    public function index()
    {
        $tiers = ChatFilterTier::withCount(['words', 'activeWords'])
            ->orderBy('tier_number')
            ->get();

        $stats = [
            'total_words' => ChatFilterWord::count(),
            'active_words' => ChatFilterWord::where('is_active', true)->count(),
            'pending_watchlist' => ChatFilterViolation::where('action_taken', 'flagged')->where('is_reviewed', false)->count(),
            'muted_users' => User::whereNotNull('chat_muted_until')->where('chat_muted_until', '>', now())->count(),
        ];

        return view('admin.chat-filter.index', compact('tiers', 'stats'));
    }

    // chat_filter_module: update a tier's action / toggle / timeout settings.
    public function updateTier(Request $request, ChatFilterTier $tier)
    {
        $data = $request->validate([
            'action' => 'required|in:ban,timeout,watchlist,hard_block',
            'is_enabled' => 'nullable|boolean',
            'delete_message' => 'nullable|boolean',
            'timeout_minutes' => 'required|integer|min:1|max:1440',
            'timeout_after_offenses' => 'required|integer|min:1|max:100',
        ]);

        $tier->update([
            'action' => $data['action'],
            'is_enabled' => $request->boolean('is_enabled'),
            'delete_message' => $request->boolean('delete_message'),
            'timeout_minutes' => $data['timeout_minutes'],
            'timeout_after_offenses' => $data['timeout_after_offenses'],
        ]);

        ChatFilterService::flushCache();

        return redirect()->route('admin.chat-filter.index')->with('success', 'Tier updated.');
    }

    // chat_filter_module: word dictionary, optionally filtered by tier.
    public function words(Request $request)
    {
        $tiers = ChatFilterTier::orderBy('tier_number')->get();
        $selectedTier = $request->integer('tier');
        $search = trim((string) $request->get('q'));

        $words = ChatFilterWord::with('tier')
            ->when($selectedTier, fn ($query) => $query->where('chat_filter_tier_id', $selectedTier))
            ->when($search !== '', fn ($query) => $query->where('term', 'like', '%'.$search.'%'))
            ->orderBy('chat_filter_tier_id')
            ->orderBy('term')
            ->paginate(50)
            ->withQueryString();

        return view('admin.chat-filter.words', compact('tiers', 'words', 'selectedTier', 'search'));
    }

    public function storeWord(Request $request)
    {
        $data = $this->validateWord($request);

        ChatFilterWord::create($data);
        ChatFilterService::flushCache();

        return back()->with('success', 'Word added.');
    }

    public function updateWord(Request $request, ChatFilterWord $word)
    {
        $data = $this->validateWord($request, $word->id);

        $word->update($data);
        ChatFilterService::flushCache();

        return back()->with('success', 'Word updated.');
    }

    public function destroyWord(ChatFilterWord $word)
    {
        $word->delete();
        ChatFilterService::flushCache();

        return back()->with('success', 'Word deleted.');
    }

    // chat_filter_module: quick toggle of a word's active state.
    public function toggleWord(ChatFilterWord $word)
    {
        $word->update(['is_active' => ! $word->is_active]);
        ChatFilterService::flushCache();

        return back()->with('success', 'Word status updated.');
    }

    // chat_filter_module: violations log + Tier 4 watchlist.
    public function watchlist(Request $request)
    {
        $filter = $request->get('action');

        $violations = ChatFilterViolation::with(['user', 'word'])
            ->when($filter, fn ($query) => $query->where('action_taken', $filter))
            ->when($request->boolean('pending'), fn ($query) => $query->where('is_reviewed', false))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.chat-filter.watchlist', compact('violations', 'filter'));
    }

    public function reviewViolation(ChatFilterViolation $violation)
    {
        $violation->update(['is_reviewed' => true]);

        return back()->with('success', 'Marked as reviewed.');
    }

    // chat_filter_module: lift a temporary mute (timeout) from a user.
    public function unmute(User $user)
    {
        $user->forceFill(['chat_muted_until' => null])->save();

        return back()->with('success', 'User unmuted.');
    }

    // chat_filter_module: lift a permanent ban (mirrors the players panel unblock).
    public function unblock(User $user)
    {
        $user->forceFill(['is_blocked' => false, 'blocked_at' => null])->save();
        $user->blockedLiveShows()->detach();

        return back()->with('success', 'User unblocked.');
    }

    // chat_filter_module: shared validation for create/update of a word.
    private function validateWord(Request $request, $ignoreId = null): array
    {
        $data = $request->validate([
            'chat_filter_tier_id' => 'required|exists:chat_filter_tiers,id',
            'term' => 'required|string|max:255',
            'match_type' => 'required|in:literal,phrase,regex',
            'whole_word' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'action_override' => 'nullable|in:ban,timeout,watchlist,hard_block',
            'note' => 'nullable|string|max:255',
        ]);

        $data['whole_word'] = $request->boolean('whole_word');
        $data['is_active'] = $request->boolean('is_active');
        $data['action_override'] = $data['action_override'] ?? null;

        return $data;
    }
}
