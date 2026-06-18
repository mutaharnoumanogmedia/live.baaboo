<?php

namespace App\Services\ActiveCampaign;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Lightweight, self-contained ActiveCampaign API client.
 *
 * Talks to the ActiveCampaign v3 REST API using Laravel's HTTP client.
 * It intentionally has no database or model dependencies so it can be
 * reused anywhere in the application.
 */
class ActiveCampaignClient
{
    private PendingRequest $http;

    public function __construct(?string $baseUrl = null, ?string $apiToken = null)
    {
        $baseUrl ??= config('services.activecampaign.url');
        $apiToken ??= config('services.activecampaign.key');

        if (empty($baseUrl) || empty($apiToken)) {
            throw new RuntimeException('ActiveCampaign is not configured (missing url/key).');
        }

        $this->http = Http::baseUrl(rtrim($baseUrl, '/').'/api/3')
            ->withHeaders(['Api-Token' => $apiToken])
            ->acceptJson()
            ->asJson()
            ->retry(3, 250)
            ->timeout(30);
    }

    /**
     * Resolve a tag id from its configured slug (e.g. 'gameshow_attended').
     */
    public function tagId(string $slug): int
    {
        $id = collect(config('services.activecampaign.tags', []))
            ->firstWhere('slug', $slug)['id'] ?? null;

        if (! $id) {
            throw new RuntimeException("Tag '{$slug}' not found in ActiveCampaign configuration.");
        }

        return (int) $id;
    }

    /**
     * Lazily yield every contact carrying the given tag id, handling pagination.
     *
     * @return Generator<int, array<string, mixed>>
     */
    public function contactsByTag(int $tagId, int $limit = 100): Generator
    {
        $offset = 0;

        do {
            $payload = $this->http->get('contacts', [
                'tagid'  => $tagId,
                'limit'  => $limit,
                'offset' => $offset,
            ])->throw()->json();

            $contacts = $payload['contacts'] ?? [];

            foreach ($contacts as $contact) {
                yield $contact;
            }

            $total = (int) ($payload['meta']['total'] ?? 0);
            $offset += $limit;
        } while ($offset < $total && $contacts !== []);
    }

    /**
     * Add a tag to a contact. ActiveCampaign is idempotent here: re-adding an
     * existing tag does not create duplicates.
     *
     * @return array<string, mixed>
     */
    public function addTag(int $contactId, int $tagId): array
    {
        \Log::info('Adding tag to contact', ['contactId' => $contactId, 'tagId' => $tagId]);
        return $this->http->post('contactTags', [
            'contactTag' => ['contact' => $contactId, 'tag' => $tagId],
        ])->throw()->json('contactTag', []);
    }

    /**
     * Find a contact by email address.
     *
     * @return array<string, mixed>|null
     */
    public function findContactByEmail(string $email): ?array
    {
        $payload = $this->http->get('contacts', ['email' => $email])
            ->throw()
            ->json();

        return $payload['contacts'][0] ?? null;
    }

    /**
     * Check whether a contact already has a specific tag.
     */
    public function contactHasTag(int $contactId, int $tagId): bool
    {
        $tags = $this->http->get("contacts/{$contactId}/contactTags")
            ->throw()
            ->json('contactTags', []);

        return collect($tags)->contains(fn (array $contactTag) => (int) ($contactTag['tag'] ?? 0) === $tagId);
    }

    /**
     * Find a contact by email and add the tag if they do not already have it.
     *
     * @return bool true when the tag was added, false when the contact was not found or already tagged
     */
    public function ensureTagByEmail(string $email, string $tagSlug): bool
    {
        \Log::info('Ensuring tag by email', ['email' => $email, 'tagSlug' => $tagSlug]);
        $contact = $this->findContactByEmail($email);

        if ($contact === null) {
            return false;
        }

        $contactId = (int) $contact['id'];
        $tagId = $this->tagId($tagSlug);

        if ($this->contactHasTag($contactId, $tagId)) {
            return false;
        }

        $this->addTag($contactId, $tagId);

        return true;
    }


}
