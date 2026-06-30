<?php

namespace App\Services;

use App\Models\ShopifyDiscountCode;
use App\Models\ShopifyJob;
use App\Models\ShopifyPriceRule;
use App\Models\User;
use App\Models\UserLiveShow;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopifyDiscountService
{
    public $shop;
    public $token;

    public function __construct($shop = null, $token = null)
    {
        $this->shop = $shop ?? env("SHOPIFY_API_DOMAIN");
        $this->token = $token ?? env("SHOPIFY_API_KEY");
        Log::info('ShopifyDiscountService initialized for shop: ' . $this->shop);
    }

    /**
     * Create a Price Rule
     */
    public function createPriceRule(array $data)
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
        ])->post("{$this->shop}/admin/api/2026-01/price_rules.json", [
            'price_rule' => $data
        ]);

        $result = $response->json();
        if ($response->failed()) {
            Log::error('Error creating price rule: ' . $response->body());
        }

        $result = $result['price_rule'] ?? null;
        $priceRule = null;
        if ($result) {
            $priceRule = ShopifyPriceRule::create([
                'shopify_id' => $result['id'],
                'title' => $result['title'],
                'type' => $result['value_type'],
                'value' => $result['value'],
                'usage_limit' => $result['usage_limit'],
                'starts_at' => $result['starts_at'],
                'ends_at' => $result['ends_at'],
                'active' => true,
                'conditions' => json_encode($result ?? []),
            ]);
        }
        return $priceRule ?? null;
    }

    // public function getPriceRule($query = [])
    // {
    //     $url = "{$this->shop}/admin/api/2026-01/price_rules.json";
    //     if (!empty($query)) {
    //         $url .= '?' . http_build_query($query);
    //     }
    //     // dd($url);
    //     $response = Http::withHeaders([
    //         'X-Shopify-Access-Token' => $this->token,
    //     ])->get($url);

    //     $result = $response->json();
    //     return $result ?? null;
    // }

    /**
     * Generate structured discount codes
     */
    public function generateCodes(int $count, string $prefix = 'LIVE10'): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = ['code' => $prefix . '-' . strtoupper(Str::random(6))];
        }
        return $codes;
    }

    /**
     * Create discount codes in batches (max 100 per request)
     */
    public function createDiscountCodes(int $priceRuleId, array $codes)
    {
        try {
            $chunks = array_chunk($codes, 100);
            foreach ($chunks as $chunk) {
                if ($chunk === []) continue;
                $bulk_req_url = "{$this->shop}/admin/api/2026-01/price_rules/{$priceRuleId}/batch.json";
                Log::info('Shopify Discount Code Batch Req: ' . $bulk_req_url. ' with ' . count($chunk) . ' codes.');

                $resp = Http::withHeaders([
                    'X-Shopify-Access-Token' => $this->token,
                ])->post($bulk_req_url, [
                    'discount_codes' => $chunk
                ]);

                Log::info('Shopify Discount Code Batch Response: ' . $resp->body());
                // dd($resp->json(),$chunk);
                if ($resp->failed()) {
                    Log::info('Error creating discount codes batch:'.$resp->body());
                    continue;
                }
                $job = $resp->json();
                $job_id = $job[array_key_first($job)]['id'] ?? null;
                $job_status = $job[array_key_first($job)]['status'] ?? null;


                ShopifyJob::create([
                    'job_type' => array_key_first($job),
                    'job_id' => $job_id,
                    'payload' => $resp->body(),
                    'status' => $job_status,
                ]);

                $code_list = Http::withHeaders([
                    'X-Shopify-Access-Token' => $this->token,
                ])->get("{$this->shop}/admin/api/2026-01/price_rules/{$priceRuleId}/batch/{$job_id}/discount_codes.json");

                $code_list = $code_list->json();
                // Log::info('Shopify Discount Code List Response: ' . json_encode($code_list));
                $discountCodes = $code_list['discount_codes'] ?? [];

                foreach ($discountCodes as $dc) {
                    $existingCode = ShopifyDiscountCode::where('code', $dc['code'])->whereNull('shopify_id')->first();
                    if ($existingCode) {
                        if ($dc['id']) {
                            $existingCode->update([
                                'shopify_id' => $dc['id'],
                                'active' => true,
                            ]);
                        }
                    } else {
                        Log::info('Discount code not found in database / Already in active state for code: ' . $dc['code']);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('CreateDiscountService Exception in createDiscountCodes: ' . $e->getMessage());
        }
    }

    /**
     * Toggle a Price Rule (affects all codes)
     */
    // public function togglePriceRule(int $priceRuleId, bool $enable)
    // {
    //     $endsAt = $enable
    //         ? now()->addYears(1)->toIso8601String()
    //         : now()->subMinute()->toIso8601String();

    //     $query = '
    //         mutation priceRuleUpdate($id: ID!, $endsAt: DateTime!) {
    //             priceRuleUpdate(id: $id, priceRule: { endsAt: $endsAt }) {
    //                 userErrors { message }
    //             }
    //         }
    //     ';

    //     $variables = [
    //         'id' => "gid://shopify/PriceRule/{$priceRuleId}",
    //         'endsAt' => $endsAt
    //     ];

    //     $response = Http::withHeaders([
    //         'X-Shopify-Access-Token' => $this->token,
    //     ])->post("{$this->shop}/admin/api/2026-01/graphql.json", [
    //         'query' => $query,
    //         'variables' => $variables
    //     ]);

    //     return $response->json();
    // }

    public function updatePriceRule(int $priceRuleId, $startsAt, $endsAt, $others): bool
    {
        // dd($priceRuleId, $startsAt, $endsAt, $others);
        $payload = [
            'price_rule' => array_merge($others, [
                'starts_at' => \Carbon\Carbon::parse($startsAt)->format('Y-m-d\TH:i:s\Z'),
                'ends_at'   => \Carbon\Carbon::parse($endsAt)->format('Y-m-d\TH:i:s\Z'),
            ]),
        ];

        // dd( $payload);

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->put(
            "{$this->shop}/admin/api/2024-10/price_rules/{$priceRuleId}.json",
            $payload
        );

        if ($response->failed()) {
            Log::error('Error updating price rule', [
                'price_rule_id' => $priceRuleId,
                'others' => $others,
                'response' => $response->json()
            ]);
            return false;
        }
        $others['collection_ids'] = $others['entitled_collection_ids'];
        //unset entitled_collection_ids from $others
        unset($others['entitled_collection_ids']);

        // Sync locally
        $priceRule = ShopifyPriceRule::where('shopify_id', $priceRuleId)->update(array_merge($others, [
            'starts_at' => $startsAt,
            'ends_at'   => $endsAt,
        ]));

        Log::info('Price rule updated successfully', [
            'price_rule_id' => $priceRuleId
        ]);

        return $priceRule;
    }


    /**
     * Generate Shopify discount URL for a code
     */
    public function generateDiscountUrl(string $code, string $redirect = '/cart'): string
    {
        $shop_url = env("SHOPIFY_DOMAIN","https://baaboo.com");
        return "{$shop_url}/discount/{$code}?redirect=" . urlencode($redirect);
    }

    public function checkDiscountCode($code)
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
        ])->get("{$this->shop}/admin/api/2026-04/discount_codes/lookup.json", [
            'code' => $code,
        ]);

        if ($response->failed()) {
            Log::error('Error checking discount code', [
                'code' => $code,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return false;
        }

        $result = $response->json();
        return !empty($result['discount_code']['id']);
    }

    public function setUserDiscountCode($shopify_rule_id, UserLiveShow $user)
    {
        $priceRuleId = $shopify_rule_id;
        if ($priceRuleId == null) {
            Log::error("No active price rule found while setting discount code for ID: " . $user->id);
            throw new Exception("No active price rule found.");
        }


        $code = strtoupper(
            substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 14)
        );

        while($this->checkDiscountCode($code)){
            $code = strtoupper(
                substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 14)
            );
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->post("{$this->shop}/admin/api/2026-04/price_rules/{$priceRuleId}/discount_codes.json", [
            'discount_code' => [
                'code' => $code,
            ],
        ]);

        if ($response->failed()) {
            Log::error('Error creating discount code for user', [
                'user_id' => $user->id,
                'price_rule_id' => $priceRuleId,
                'code' => $code,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return null;
        }

        $discountCode = $response->json('discount_code');
        if (!$discountCode || empty($discountCode['id'])) {
            Log::error('Invalid discount code response from Shopify', [
                'user_id' => $user->id,
                'price_rule_id' => $priceRuleId,
                'response' => $response->body(),
            ]);
            return null;
        }

        $user->discount_code = $discountCode['code'] ?? $code;
        $user->save();

        return $discountCode['code'] ?? $code;

    }

    public function removeDiscountCode(string $code): bool
    {
        $existingCode = ShopifyDiscountCode::where('code', $code)->first();
        if ($existingCode) {

            $priceRuleId = $existingCode->pricerule->shopify_id;        // your price rule ID
            $discountCodeId = $existingCode->shopify_id;     // the code ID you want to remove
            // dd($priceRuleId, $discountCodeId);

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->token,
            ])->delete("{$this->shop}/admin/api/2026-01/price_rules/{$priceRuleId}/discount_codes/{$discountCodeId}.json");

            if ($response->successful()) {
                Log::info("Discount code removed successfully. Code: {$code}");
            } else {
                Log::error("Error removing code: " . $response->body());
            }

            $existingCode->active = false;
            $existingCode->save();
            return true;
        }
        return false;
    }

    public function processDiscountCodeCreationJob($priceRuleId, $jobId)
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
        ])->get("{$this->shop}/admin/api/2026-01/price_rules/{$priceRuleId}/batch/{$jobId}.json");

        if($response->failed()){
            Log::error('Error fetching job status for job '.$jobId. ': ' . $response->body());
            return;
        }
        $result = $response->json();
        $status = $result['discount_code_creation']['status'] ?? 'queued';

        if($status !== 'queued'){
            ShopifyJob::where('job_id', $jobId)->update([
                'status' => $status,
            ]);
            Log::info('Job '.$jobId.' is not yet completed. Current status: '.$status);
            // return;
        }


        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
        ])->get("{$this->shop}/admin/api/2026-01/price_rules/{$priceRuleId}/batch/{$jobId}/discount_codes.json");

        if($response->failed()){
            Log::error('Error fetching discount codes for job '.$jobId. ': ' . $response->body());
            return;
        }
        $result = $response->json();
        $discountCodes = $result['discount_codes'] ?? [];

        foreach ($discountCodes as $dc) {
            $existingCode = ShopifyDiscountCode::where('code', $dc['code'])->whereNull('shopify_id')->first();
            if ($existingCode) {
                if ($dc['id']) {
                    $existingCode->update([
                        'shopify_id' => $dc['id'],
                        'active' => true,
                    ]);
                }
            } else {
                Log::info('Discount code not found in database / Already in active state for code: ' . $dc['code']);
            }
        }

        ShopifyJob::where('job_id', $jobId)->update([
            'status' => $status,
        ]);


    }
}
