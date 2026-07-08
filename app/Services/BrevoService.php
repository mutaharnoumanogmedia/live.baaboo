<?php

namespace App\Services;

use Brevo\Brevo;
use Brevo\Exceptions\BrevoApiException;
use Brevo\Exceptions\BrevoException;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestAttachmentItem;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestBccItem;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestCcItem;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestReplyTo;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;
use DateTime;
use Illuminate\Support\Facades\Log;

class BrevoService
{
    protected Brevo $client;

    /**
     * @param  array<string,mixed>|null  $options  Passed straight to the Brevo SDK. Accepts
     *                                              a PSR-18 'client' (handy for faking HTTP in
     *                                              tests), 'baseUrl', 'timeout', etc.
     */
    public function __construct(?string $apiKey = null, ?array $options = null)
    {
        $this->client = new Brevo(
            apiKey: $apiKey ?? config('brevo.api_key', env('BREVO_API_KEY')),
            options: $options,
        );
    }

    /**
     * Send a transactional email through Brevo.
     *
     * @param  string|array<int|string,mixed>  $to        Recipient email, ['email','name'], or a list of recipients.
     * @param  string|array<string,mixed>|null $sender    A preset key from config('brevo.senders'), an ['email','name'] / ['id'] array, or null for the default preset.
     * @param  array<int,array<string,string>|string>  $cc
     * @param  array<int,array<string,string>|string>  $bcc
     * @param  string|array<string,mixed>|null $replyTo
     * @param  array<int,array<string,string>>  $attachments  Each item: ['url' => ...] or ['content' => base64, 'name' => ...].
     * @param  array<string,mixed>  $params   Template variable substitutions.
     * @param  array<int,string>    $tags
     * @return array{success:bool,status_code:int,message_id:?string,message_ids:?array<int,string>,error:?string}
     */
    public function send(
        string|array $to,
        string $subject,
        string $htmlContent,
        string|array|null $sender = null,
        ?string $textContent = null,
        array $cc = [],
        array $bcc = [],
        string|array|null $replyTo = null,
        array $attachments = [],
        array $params = [],
        array $tags = [],
        ?int $templateId = null,
        ?DateTime $scheduledAt = null,
    ): array {
        try {
            $payload = array_filter([
                'sender' => $this->resolveSender($sender),
                'to' => $this->normalizeRecipients($to, SendTransacEmailRequestToItem::class),
                'subject' => $subject,
                'htmlContent' => $htmlContent,
                'textContent' => $textContent,
                'cc' => $cc ? $this->normalizeRecipients($cc, SendTransacEmailRequestCcItem::class) : null,
                'bcc' => $bcc ? $this->normalizeRecipients($bcc, SendTransacEmailRequestBccItem::class) : null,
                'replyTo' => $this->resolveReplyTo($replyTo),
                'attachment' => $this->normalizeAttachments($attachments),
                'params' => $params ?: null,
                'tags' => $tags ?: null,
                'templateId' => $templateId,
                'scheduledAt' => $scheduledAt,
            ], static fn ($value) => $value !== null);

            $response = $this->client->transactionalEmails->sendTransacEmail(
                new SendTransacEmailRequest($payload),
            );

            return [
                'success' => true,
                'status_code' => $scheduledAt ? 202 : 201,
                'message_id' => $response?->messageId,
                'message_ids' => $response?->messageIds,
                'error' => null,
            ];
        } catch (BrevoApiException $e) {
            Log::error('Brevo email failed: '.$e->getMessage(), [
                'status_code' => $e->getCode(),
                'body' => $e->getBody(),
            ]);

            return [
                'success' => false,
                'status_code' => $e->getCode(),
                'message_id' => null,
                'message_ids' => null,
                'error' => is_string($e->getBody()) ? $e->getBody() : json_encode($e->getBody()),
            ];
        } catch (BrevoException $e) {
            Log::error('Brevo client error: '.$e->getMessage());

            return [
                'success' => false,
                'status_code' => $e->getCode() ?: 0,
                'message_id' => null,
                'message_ids' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Resolve the sender from a preset key, an explicit array, or the default preset.
     *
     * @param  string|array<string,mixed>|null  $sender
     */
    protected function resolveSender(string|array|null $sender): SendTransacEmailRequestSender
    {
        if (is_string($sender) || $sender === null) {
            $key = $sender ?? config('brevo.default_sender', 'default');
            $preset = config("brevo.senders.{$key}");

            if (! is_array($preset)) {
                throw new \InvalidArgumentException("Brevo sender preset [{$key}] is not defined in config/brevo.php.");
            }

            $sender = $preset;
        }

        return new SendTransacEmailRequestSender([
            'email' => $sender['email'] ?? null,
            'name' => $sender['name'] ?? null,
            'id' => $sender['id'] ?? null,
        ]);
    }

    /**
     * @param  string|array<string,mixed>|null  $replyTo
     */
    protected function resolveReplyTo(string|array|null $replyTo): ?SendTransacEmailRequestReplyTo
    {
        if ($replyTo === null) {
            return null;
        }

        if (is_string($replyTo)) {
            $replyTo = ['email' => $replyTo];
        }

        return new SendTransacEmailRequestReplyTo([
            'email' => $replyTo['email'],
            'name' => $replyTo['name'] ?? null,
        ]);
    }

    /**
     * Normalize a recipient (or list of recipients) into the given Brevo item type.
     *
     * @param  string|array<int|string,mixed>  $recipients
     * @param  class-string  $itemClass
     * @return array<int,object>
     */
    protected function normalizeRecipients(string|array $recipients, string $itemClass): array
    {
        if (is_string($recipients)) {
            $recipients = [['email' => $recipients]];
        } elseif (isset($recipients['email'])) {
            $recipients = [$recipients];
        }

        return array_map(function ($recipient) use ($itemClass) {
            if (is_string($recipient)) {
                $recipient = ['email' => $recipient];
            }

            return new $itemClass([
                'email' => $recipient['email'],
                'name' => $recipient['name'] ?? null,
            ]);
        }, $recipients);
    }

    /**
     * @param  array<int,array<string,string>>  $attachments
     * @return array<int,SendTransacEmailRequestAttachmentItem>|null
     */
    protected function normalizeAttachments(array $attachments): ?array
    {
        if ($attachments === []) {
            return null;
        }

        return array_map(static fn (array $attachment) => new SendTransacEmailRequestAttachmentItem([
            'url' => $attachment['url'] ?? null,
            'content' => $attachment['content'] ?? null,
            'name' => $attachment['name'] ?? null,
        ]), $attachments);
    }
}
