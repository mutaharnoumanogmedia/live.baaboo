<?php

namespace App\Http\Controllers;

use App\Services\ZegoChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * HTTP controller dedicated to the ZEGOCLOUD In-App Chat (ZIM) integration.
 *
 * Currently exposes a single endpoint that issues a short-lived Token04 for the
 * authenticated user so the browser SDK can connect to ZIM without ever seeing
 * the ZEGO server secret.
 */
class ZegoChatController extends Controller
{
    public function __construct(protected ZegoChatService $chat) {}

    /**
     * Issue a ZEGO chat token for the currently authenticated user.
     *
     * Response shape:
     * {
     *   "token":     "04...",
     *   "appID":     1747053464,
     *   "userID":    "42",
     *   "userName":  "Jane Doe",
     *   "expiresIn": 3600
     * }
     */
    public function getToken(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'error' => 'Unauthenticated.',
            ], 401);
        }

        $userId = (string) Auth::id();
        $userName = (string) ($user->name ?? $user->user_name ?? ('user-'.$userId));

        try {
            $tokenData = $this->chat->generateToken($userId);
        } catch (Throwable $e) {
            Log::error('ZegoChat token generation failed.', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'token' => $tokenData['token'],
            'appID' => $this->chat->appId(),
            'userID' => $userId,
            'userName' => $userName,
            'expiresIn' => $tokenData['expires_in'],
        ]);
    }
}
