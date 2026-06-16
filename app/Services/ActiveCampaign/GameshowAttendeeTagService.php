<?php

namespace App\Services\ActiveCampaign;

use App\Models\User;
use App\Models\UserLiveShow;
use Throwable;

class GameshowAttendeeTagService
{
    private const TAG_SLUG = 'gameshow_attended_general';

    public function __construct(private ActiveCampaignClient $client) {}

    /**
     * Add the gameshow_attended_general tag to every user who played a gameshow before.
     *
     * Skips contacts not found in ActiveCampaign or already carrying the tag.
     *
     * @return array{total_players: int, tagged: int, already_tagged: int, not_found: int, failed: int, errors: array<int, array{email: string, message: string}>}
     */
    public function addGameshowAttendedGeneralTagToOldPlayers(bool $dryRun = false): array
    {
        $tagId = $this->client->tagId(self::TAG_SLUG);

        $playerIds = UserLiveShow::query()
            ->distinct()
            ->pluck('user_id');

        $players = User::query()
            ->whereIn('id', $playerIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get(['id', 'email']);

        $tagged = 0;
        $alreadyTagged = 0;
        $notFound = 0;
        $failed = 0;
        $errors = [];

        \Log::info('Players to tag', ['players' => $players]);
      

        foreach ($players as $player) {
            try {
                $contact = $this->client->findContactByEmail($player->email);

                if ($contact === null) {
                    $notFound++;

                    continue;
                }

                $contactId = (int) $contact['id'];

                if ($this->client->contactHasTag($contactId, $tagId)) {
                    $alreadyTagged++;

                    continue;
                }

                if ($dryRun) {
                    $tagged++;

                    continue;
                }

                $this->client->addTag($contactId, $tagId);
                $tagged++;
                usleep(220_000);
            } catch (Throwable $e) {
                $failed++;
                $errors[] = [
                    'email'   => $player->email,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'total_players'   => $players->count(),
            'tagged'          => $tagged,
            'already_tagged'  => $alreadyTagged,
            'not_found'       => $notFound,
            'failed'          => $failed,
            'errors'          => $errors,
        ];
    }
}
