<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LeadGenerationService
{
    public function leadGeneration($requestPayload)
    {
        $response = Http::withHeaders([
            'Authorization' => 'f0a97e7aaa3291487316d9c3d9e67b96c32dee09ad2c573af6c272341edb70e7',
            'Origin' => env('FRONTEND_URL'),
        ])->asForm()->post(env('AFFILIATE_API_ENDPOINT').'/api/lead-generation', [
            'name' => $requestPayload['name'],
            'email' => $requestPayload['email'],
            'partner_username' => $requestPayload['partner_username'] ?? null,
            'magic_link' => $requestPayload['magic_link'],
            'referral_link' => $requestPayload['referral_link'],
            'is_joined' => $requestPayload['is_joined'] ?? 0,
        ]);

        return $response->json();
    }
}
