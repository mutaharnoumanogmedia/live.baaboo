<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AffiliateAPIService
{
    public function isUserNameExistingInAffiliate($username): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'f0a97e7aaa3291487316d9c3d9e67b96c32dee09ad2c573af6c272341edb70e7',
            'Origin' => env('FRONTEND_URL'),
        ])->get(env('AFFILIATE_API_ENDPOINT').'/api/user-status', [
            'username' => $username,

        ]);

        // if the response has status : true and affiliated: true, then return the response
        if ($response->json()['status'] == true && $response->json()['affiliated'] == true) {
            // return respnse as array
            return true;
        } else {
            return false;
        }
    }
    
}
