<?php

namespace Tests\E2E;

/**
 * Shared state passed between the ordered E2E test classes.
 *
 * Each step writes the ids/keys later steps read from the database.
 * The suite assumes a single migrate+fresh+seed at the start and no
 * per-test rollbacks, so these values remain valid for the whole run.
 */
final class E2EContext
{
    /** Set to true after the one-off migrate:fresh + E2EBaselineSeeder. */
    public static bool $migratedAndSeeded = false;

    public static ?int $adminUserId = null;

    // --- Registration (01) ---
    public static ?int $directUserId = null;

    public static ?int $referrerUserId = null;

    public static string $referrerUserName = 'partner1';

    public static ?int $referredUserId = null;

    // --- Live show creation (02) ---
    public static ?int $liveShowId = null;

    /** Shopify remote price-rule id for the first voucher prize on the E2E show. */
    public static ?int $voucherShopifyPriceRuleId = null;

    // --- Join (03) ---
    public static ?int $newJoinerUserId = null;

    // --- Winner announcement (04) ---
    public static ?int $topCashWinnerUserId = null;

    public static ?int $topVoucherWinnerUserId = null;

    public const DIRECT_USER_EMAIL = 'max@example.com';

    public const REFERRER_EMAIL = 'partner@example.com';

    public const REFERRED_USER_EMAIL = 'anna@example.com';

    public const NEW_JOINER_EMAIL = 'newcomer@example.com';

    public const LIVE_SHOW_TITLE = 'E2E Live Show';
}
