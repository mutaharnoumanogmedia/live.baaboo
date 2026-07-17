<?php

namespace App\Services;

use App\Events\ChatFilterFlaggedEvent;
use App\Events\ChatMessageBlockedEvent;
use App\Events\UserBlockFromLiveShowEvent;
use App\Models\ChatFilterTier;
use App\Models\ChatFilterViolation;
use App\Models\LiveShow;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * chat_filter_module: central chat moderation engine.
 *
 * Every chat message flows through this service. It normalises the text to
 * defeat common evasion tricks (leetspeak, spacing, separators, repeats),
 * matches it against the DB-driven word list, and then enforces the policy for
 * the matched tier (ban / timeout / hard-block / watchlist).
 */
class ChatFilterService
{
    // chat_filter_module: cache key + TTL for the compiled rule set (flushed on admin edits).
    private const CACHE_KEY = 'chat_filter_ruleset';

    private const CACHE_TTL = 300;

    // chat_filter_module: higher number = takes precedence when a message hits several words.
    private const ACTION_SEVERITY = [
        'ban' => 4,
        'timeout' => 3,
        'hard_block' => 2,
        'watchlist' => 1,
    ];

    // chat_filter_module: leetspeak / symbol substitutions applied before matching.
    private const LEET_MAP = [
        '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's',
        '7' => 't', '8' => 'b', '@' => 'a', '$' => 's', '!' => 'i',
    ];

    // chat_filter_module: German user-facing notices per action.
    public const MESSAGES = [
        'ban' => 'Du wurdest gesperrt und kannst nicht mehr an den Shows teilnehmen.',
        'timeout' => 'Du wurdest vorübergehend stummgeschaltet. Bitte halte dich an die Chat-Regeln.',
        'block' => 'Deine Nachricht wurde blockiert, da sie gegen die Chat-Regeln verstößt.',
        'muted' => 'Du bist derzeit stummgeschaltet und kannst keine Nachrichten senden.',
    ];

    /**
     * chat_filter_module: run the message through the filter.
     *
     * @return array|null  null when clean, otherwise
     *                     ['word' => ChatFilterWord, 'tier' => ChatFilterTier,
     *                      'action' => string, 'matched_term' => string]
     */
    public function check(string $message): ?array
    {
        $message = trim($message);
        if ($message === '') {
            return null;
        }

        $forms = $this->buildForms($message);
        $best = null;
        $bestSeverity = 0;

        foreach ($this->ruleset() as $row) {
            [$word, $tier] = $row;

            if (! $this->matchesWord($word, $forms)) {
                continue;
            }

            // chat_filter_module: word override beats the tier's default action
            $action = $word->action_override ?: $tier->action;
            $severity = self::ACTION_SEVERITY[$action] ?? 0;

            if ($severity > $bestSeverity) {
                $bestSeverity = $severity;
                $best = [
                    'word' => $word,
                    'tier' => $tier,
                    'action' => $action,
                    'matched_term' => $word->term,
                ];
            }
        }

        return $best;
    }

    /**
     * chat_filter_module: apply the policy for a matched hit.
     *
     * @return array ['action' => string, 'blocked' => bool, 'message' => ?string, 'muted_until' => ?string]
     */
    public function enforce(User $user, ?LiveShow $liveShow, string $message, array $hit): array
    {
        $action = $hit['action'];
        $liveShowId = $liveShow?->id;

        switch ($action) {
            case 'watchlist':
                // chat_filter_module: Tier 4 - never block, just surface to the mods.
                $violation = $this->logViolation($user, $liveShowId, $hit, $message, 'flagged');
                ChatFilterFlaggedEvent::dispatch([
                    'live_show_id' => $liveShowId,
                    'violation_id' => $violation->id,
                    'user' => ['id' => $user->id, 'name' => $user->name],
                    'matched_term' => $hit['matched_term'],
                    'tier_number' => $hit['tier']->tier_number,
                    'message' => $message,
                    'created_at' => $violation->created_at,
                ]);

                return ['action' => 'watchlist', 'blocked' => false, 'message' => null, 'muted_until' => null];

            case 'ban':
                // chat_filter_module: Tier 1 - delete + immediate global ban, no discussion.
                $this->banUser($user, $liveShowId);
                $this->logViolation($user, $liveShowId, $hit, $message, 'banned');
                ChatMessageBlockedEvent::dispatch($user->id, $liveShowId, 'ban', self::MESSAGES['ban']);

                return ['action' => 'ban', 'blocked' => true, 'message' => self::MESSAGES['ban'], 'muted_until' => null];

            case 'timeout':
                // chat_filter_module: delete now; mute once the user is a repeat offender.
                $priorOffenses = ChatFilterViolation::where('user_id', $user->id)
                    ->where('tier_number', $hit['tier']->tier_number)
                    ->whereIn('action_taken', ['deleted', 'timeout'])
                    ->count();

                $threshold = max(1, (int) $hit['tier']->timeout_after_offenses);

                if ($priorOffenses >= $threshold) {
                    $mutedUntil = now()->addMinutes(max(1, (int) $hit['tier']->timeout_minutes));
                    $user->forceFill(['chat_muted_until' => $mutedUntil])->save();
                    $this->logViolation($user, $liveShowId, $hit, $message, 'timeout');
                    ChatMessageBlockedEvent::dispatch($user->id, $liveShowId, 'timeout', self::MESSAGES['timeout'], $mutedUntil->toIso8601String());

                    return ['action' => 'timeout', 'blocked' => true, 'message' => self::MESSAGES['timeout'], 'muted_until' => $mutedUntil->toIso8601String()];
                }

                $this->logViolation($user, $liveShowId, $hit, $message, 'deleted');
                ChatMessageBlockedEvent::dispatch($user->id, $liveShowId, 'block', self::MESSAGES['block']);

                return ['action' => 'timeout', 'blocked' => true, 'message' => self::MESSAGES['block'], 'muted_until' => null];

            case 'hard_block':
            default:
                // chat_filter_module: link / crypto spam - drop the message, no user penalty.
                $this->logViolation($user, $liveShowId, $hit, $message, 'deleted');
                ChatMessageBlockedEvent::dispatch($user->id, $liveShowId, 'block', self::MESSAGES['block']);

                return ['action' => 'hard_block', 'blocked' => true, 'message' => self::MESSAGES['block'], 'muted_until' => null];
        }
    }

    // chat_filter_module: convenience passthrough used by the controller guard.
    public function isMuted(User $user): bool
    {
        return $user->isChatMuted();
    }

    // chat_filter_module: write one row to the audit log / watchlist.
    public function logViolation(User $user, $liveShowId, array $hit, string $message, string $actionTaken): ChatFilterViolation
    {
        return ChatFilterViolation::create([
            'user_id' => $user->id,
            'live_show_id' => $liveShowId,
            'chat_filter_word_id' => $hit['word']->id ?? null,
            'tier_number' => $hit['tier']->tier_number ?? null,
            'matched_term' => $hit['matched_term'] ?? null,
            'original_message' => $message,
            'action_taken' => $actionTaken,
            'is_reviewed' => false,
        ]);
    }

    // chat_filter_module: mirror the existing global block behaviour used by the admin panel.
    private function banUser(User $user, $liveShowId): void
    {
        if ($liveShowId) {
            $liveShow = LiveShow::find($liveShowId);
            if ($liveShow) {
                $liveShow->blockedUsers()->syncWithoutDetaching($user->id);
            }
        }

        $user->forceFill([
            'is_blocked' => true,
            'blocked_at' => now(),
        ])->save();

        UserBlockFromLiveShowEvent::dispatch($liveShowId, $user->id, true);
    }

    /**
     * chat_filter_module: normalised representations of the message.
     * - lower:      lowercased original (separators intact)
     * - normalized: leetspeak resolved (separators intact)
     * - compact:    normalized with every non-alphanumeric char stripped
     */
    private function buildForms(string $message): array
    {
        $lower = mb_strtolower($message, 'UTF-8');
        $normalized = strtr($lower, self::LEET_MAP);
        $compact = preg_replace('/[^a-z0-9äöüß]/u', '', $normalized);

        return [
            'lower' => $lower,
            'normalized' => $normalized,
            'compact' => $compact,
        ];
    }

    // chat_filter_module: test a single rule against the prepared message forms.
    private function matchesWord($word, array $forms): bool
    {
        $term = mb_strtolower($word->term, 'UTF-8');

        switch ($word->match_type) {
            case 'regex':
                // chat_filter_module: stored patterns already carry (?i) + boundaries.
                $pattern = '~'.$word->term.'~u';

                return @preg_match($pattern, $forms['lower']) === 1;

            case 'phrase':
                // chat_filter_module: multi-word terms keep their spacing.
                return str_contains($forms['lower'], $term)
                    || str_contains($forms['normalized'], strtr($term, self::LEET_MAP));

            case 'literal':
            default:
                if ($word->whole_word) {
                    // chat_filter_module: word-boundary match only (neger, mongo, sex, hure ...)
                    $quoted = preg_quote($term, '~');

                    return @preg_match('~\b'.$quoted.'\b~u', $forms['lower']) === 1
                        || @preg_match('~\b'.$quoted.'\b~u', $forms['normalized']) === 1;
                }

                // chat_filter_module: substring match + evasion-proof compact match
                $termCompact = preg_replace('/[^a-z0-9äöüß]/u', '', strtr($term, self::LEET_MAP));

                if (str_contains($forms['lower'], $term) || str_contains($forms['normalized'], strtr($term, self::LEET_MAP))) {
                    return true;
                }

                return strlen($termCompact) >= 3 && str_contains($forms['compact'], $termCompact);
        }
    }

    /**
     * chat_filter_module: compiled [word, tier] pairs from every enabled tier,
     * cached briefly so live chat does not hit the DB on every message.
     *
     * @return array<int, array{0: \App\Models\ChatFilterWord, 1: \App\Models\ChatFilterTier}>
     */
    private function ruleset(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $rows = [];
            $tiers = ChatFilterTier::where('is_enabled', true)->with('activeWords')->get();

            foreach ($tiers as $tier) {
                foreach ($tier->activeWords as $word) {
                    // chat_filter_module: attach tier so effectiveAction has it without a query
                    $word->setRelation('tier', $tier);
                    $rows[] = [$word, $tier];
                }
            }

            return $rows;
        });
    }

    // chat_filter_module: call after any admin edit so changes go live immediately.
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
