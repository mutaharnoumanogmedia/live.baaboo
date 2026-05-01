<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;
use ZEGO\ZegoErrorCodes;
use ZEGO\ZegoServerAssistant;

/**
 * Encapsulates everything related to the ZEGOCLOUD ZIM (In-App Chat) integration:
 * configuration access, validation, and Token04 generation via the bundled
 * ZEGO PHP Server Assistant.
 *
 * The server secret is ALWAYS kept on the server side - never expose it to the browser.
 */
class ZegoChatService
{
    protected int $appId;

    protected string $serverSecret;

    protected int $tokenTtl;

    public function __construct()
    {
        $this->appId = (int) config('services.zego.chat.app_id');
        $this->serverSecret = (string) config('services.zego.chat.server_secret');
        $this->tokenTtl = (int) config('services.zego.chat.token_ttl', 3600);
    }

    /**
     * The ZEGO chat AppID (safe to expose to the browser).
     */
    public function appId(): int
    {
        return $this->appId;
    }

    /**
     * Token validity, in seconds.
     */
    public function tokenTtl(): int
    {
        return $this->tokenTtl;
    }

    /**
     * Whether the service is properly configured. The server secret must be a
     * 32-byte string per the ZEGO Token04 specification.
     */
    public function isConfigured(): bool
    {
        return $this->appId > 0 && strlen($this->serverSecret) === 32;
    }

    /**
     * Generate a ZEGO Token04 for the given userID.
     *
     * @return array{token: string, expires_in: int}
     *
     * @throws InvalidArgumentException When userID is empty.
     * @throws RuntimeException When the service is not configured or generation fails.
     */
    public function generateToken(string $userId, string $payload = ''): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'ZEGO chat is not configured. Please set ZEGO_CHAT_APP_ID and a 32-character ZEGO_CHAT_SERVER_SECRET.'
            );
        }

        $userId = trim($userId);
        if ($userId === '') {
            throw new InvalidArgumentException('A non-empty userID is required to generate a ZEGO chat token.');
        }

        $result = ZegoServerAssistant::generateToken04(
            $this->appId,
            $userId,
            $this->serverSecret,
            $this->tokenTtl,
            $payload
        );

        if ($result->code !== ZegoErrorCodes::success) {
            throw new RuntimeException(
                $result->message !== '' ? $result->message : 'Unable to generate ZEGO chat token.'
            );
        }

        return [
            'token' => $result->token,
            'expires_in' => $this->tokenTtl,
        ];
    }
}
