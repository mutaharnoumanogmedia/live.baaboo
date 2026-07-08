# Testing Guide

This project's test suite runs against a **throwaway, in-memory SQLite database**.
Every test that uses `RefreshDatabase` gets a freshly migrated + seeded schema and
rolls everything back afterwards, so the **real MySQL database is never touched**.

## How the test database is configured

The test environment is defined entirely in [`phpunit.xml`](phpunit.xml):

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="DB_FOREIGN_KEYS" value="true"/>
```

- `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:` point the whole test run at an
  ephemeral SQLite database that lives only in RAM for the duration of the process.
- These values are set by PHPUnit **before Laravel boots**, and Laravel's
  `.env` loader will not override already-set environment variables. This means the
  SQLite connection wins regardless of what `.env` / `.env.testing` say, so tests
  can never accidentally run against production MySQL (`live_baaboo`).
- `:memory:` means there is no file to create or clean up. When the test process
  ends, the database disappears.

No `database/database.sqlite` file is required.

## How individual tests use it

Feature tests that need database state use Laravel's `RefreshDatabase` trait and
seed inside `setUp()`:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;

class SomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }
}
```

For each test method:

1. `RefreshDatabase` migrates the schema onto the in-memory SQLite connection
   and opens a transaction.
2. `setUp()` runs `DatabaseSeeder`, populating roles, permissions, users and
   live shows (the data these tests read from).
3. The test body runs inside that transaction, which is rolled back at the end,
   so tests stay isolated from one another.

### Why seed in `setUp()` instead of the `$seed` / `$seeder` properties?

Laravel's built-in `$seed = true` only seeds during the **one-off**
`migrate:fresh` step. That step is shared across the whole test process and is
tied to whichever `RefreshDatabase` test happens to run **first**. Since this
project also has unseeded `RefreshDatabase` tests (the default auth/profile
tests), one of them can migrate first and the winner tests would then start with
an empty database. Seeding in `setUp()` (inside the per-test transaction) makes
the data available for every test regardless of execution order, and it is still
rolled back after each test.

The following tests were migrated to this approach:

- `tests/Feature/SendWinnerEmailJobTest.php`
- `tests/Feature/WinnerEmailSchedulingTest.php`
- `tests/Feature/BrevoServiceTest.php`
- `tests/Feature/ExampleTest.php` (the home page it requests reads seeded tables,
  so it now migrates + seeds the SQLite database too)

## The winner-email fixture: `TestLiveShowSeeder`

The winner-email tests do **not** use the full `DatabaseSeeder`. Instead they
seed `database/seeders/TestLiveShowSeeder.php`, a small, deterministic fixture
purpose-built for these tests. It creates:

- Roles + permissions (via the existing `PermissionSeeder` + `RoleSeeder`) and a
  `Test Admin` user, so `User::role('admin')` and `actingAs(..., 'admin')` work.
- **One** live show titled `Test Winner Show` (`winners_announced = false`,
  `max_winners = 10`), locatable in tests via `TestLiveShowSeeder::SHOW_TITLE`.
- **10 winner prizes**: ranks 1-3 are cash prizes (`is_voucher = 0`) and ranks
  4-10 are voucher prizes (`is_voucher = 1`), matching the app's real tiers.
- **10 quiz questions**, each with 4 options (the first is correct).
- **10 winners**, each with a distinct random total score in `[1000, 2000]`
  (materialised as real quiz responses, so the computed
  `UserLiveShow::getScoreAttribute()` matches). The highest scorer is rank 1, so
  the top three are cash winners and the rest are voucher winners. Winners are
  pre-marked (`is_winner = true`, `winner_prize_id` set) and voucher winners get
  a `discount_code`.
- A handful of extra registered non-winning players and a couple of users who
  never joined the show (for the "not a winner" / "not a participant" paths).

### Why this mirrors reality (winner types)

`SendWinnerEmailJob` sends up to three emails per winner and keys them off the
same fields this fixture sets:

| Winner type | Prize `is_voucher` | `discount_code` | Emails sent |
| ----------- | ------------------ | --------------- | ----------- |
| Cash (ranks 1-3) | `0` | none | generic + **cash** |
| Voucher (ranks 4-10) | `1` | set | generic + **voucher** |

So `SendWinnerEmailJobTest` asserts a cash winner gets the generic + cash emails
(and never the voucher email) and a voucher winner gets the generic + voucher
emails (and never the cash email), exactly as the job behaves in production.

## Sequential E2E suite (Option B: shared state)

For manual / QA-style runs where **one database is seeded once** and tests execute
in strict order (Register → Live Show Creation → Join → Winner announcements),
use the dedicated E2E configuration.

### Database

- File: `database/testing.sqlite` (created automatically if missing).
- Config: [`phpunit.e2e.xml`](phpunit.e2e.xml) sets `DB_DATABASE=database/testing.sqlite`.
- On the **first** E2E test, `E2ETestCase` runs `migrate:fresh` + `E2EBaselineSeeder`
  exactly once. Later tests reuse the same file; there is **no** `RefreshDatabase`
  rollback between steps.

### Shared state

[`tests/E2E/E2EContext.php`](tests/E2E/E2EContext.php) holds ids written by each
step (registered user ids, `liveShowId`, winner ids, etc.) so the next class can
load the records the previous step created.

### Test classes (run in this order)

| Order | Class | Steps |
| ----- | ----- | ----- |
| 1 | `tests/E2E/RegistrationTest.php` | Direct registration, referral registration |
| 2 | `tests/E2E/LiveShowCreationTest.php` | Cash+voucher show, same-time shows, Shopify code, update to live |
| 3 | `tests/E2E/LiveShowJoinTest.php` | New join, existing user join, magic link join, quiz scores |
| 4 | `tests/E2E/WinnerAnnouncementTest.php` | Generate winners, emails, reannounce |

Cross-class `@depends` links each class to the previous one; if a step fails, the
rest of the chain is skipped.

### Commands

```bash
# Sequential E2E chain (shared testing.sqlite)
vendor/bin/phpunit -c phpunit.e2e.xml

# Fast isolated tests (in-memory SQLite, per-test rollback)
php artisan test
```

Do **not** run the E2E suite with `--parallel`. Reset the chain by deleting
`database/testing.sqlite` and re-running.

### Baseline seeder

[`database/seeders/E2EBaselineSeeder.php`](database/seeders/E2EBaselineSeeder.php)
seeds only roles, permissions and an admin user. All business data is created by
the HTTP/admin actions in the ordered tests.

## Isolated scenario feature tests

The following tests in `tests/Feature/` cover the same journeys **independently**
(each test migrates + seeds its own in-memory database). Use these for fast CI;
use the E2E suite above when the tester requires one shared DB and strict order.

### `tests/Feature/UserRegistrationFlowTest.php`

Registration on the badabing website (`HomeController`). External calls
(affiliate username lookup + lead generation) are faked via `Http::fake()` and
the welcome mail via `Mail::fake()`.

- A visitor registers directly on the website (`register-user-via-form-submit`),
  gets the `user` role and their own referral/magic links.
- A visitor registers through a referral link: the landing page resolves the
  referrer by `user_name`, and the new user's `referred_by` is set.
- Registration is rejected when the terms checkbox is not accepted.

### `tests/Feature/LiveShowManagementTest.php`

Admin live show lifecycle (`Admin\LiveShowController`). All Shopify traffic is
faked.

- Creating a show with **both cash and voucher prizes** in one submit; voucher
  ranks provision a Shopify price rule (`syncWinnerPrizes`), cash ranks do not.
- Creating **two shows for the same scheduled time** (allowed — no conflict).
- A **voucher winner's discount code is generated with the Shopify test-shop
  credentials** (`GenerateWinnerDiscountCodeJob` → `ShopifyDiscountService`),
  persisting `user_live_shows.discount_code`.
- Updating an existing show (`admin.live-shows.update`).

### `tests/Feature/LiveShowJoinTest.php`

Joining a live show. Affiliate + ActiveCampaign calls are faked.

- A **brand-new visitor** joins by email (`live-show.user.register`): an account
  is created, the `user` role assigned, and a `registered` pivot row added.
- An **already-registered user** joins directly by email (logged in, pivot
  added, no duplicate account).
- A **registered (referred) user** joins via their personal magic/referral link
  (`live-show-magic-link`), which logs them in and attaches them to the show.

### `tests/Feature/WinnerAnnouncementTest.php`

Winner announcement on the stream-management screen, against
`TestLiveShowSeeder`. `Queue::fake()` prevents the delayed jobs from running.

- Generating winners marks the top players and assigns the cash/voucher prize
  split.
- Announcing winners queues one notification email job per winner.
- The generic winner notification email is sent when `SendWinnerEmailJob` runs.
- The prize-specific emails (cash vs voucher) are sent per winner type.
- Winners cannot be re-announced through `update-winners` (422), but can be
  regenerated via `reupdate-winners` without re-queueing the winner emails.

## Running the tests

```bash
# Sequential E2E chain (one shared testing.sqlite)
vendor/bin/phpunit -c phpunit.e2e.xml

# Run the two winner-email test files
php artisan test tests/Feature/SendWinnerEmailJobTest.php tests/Feature/WinnerEmailSchedulingTest.php

# Run the isolated scenario feature tests
php artisan test tests/Feature/UserRegistrationFlowTest.php tests/Feature/LiveShowManagementTest.php tests/Feature/LiveShowJoinTest.php tests/Feature/WinnerAnnouncementTest.php

# Run the whole isolated suite (in-memory SQLite)
php artisan test
```

## Requirements

- The `pdo_sqlite` PHP extension must be enabled (it ships with the XAMPP PHP
  build used here). Verify with `php -m | grep sqlite`.

## Cross-database compatibility changes

Because production runs on MySQL while the tests run on SQLite, a couple of
MySQL-only constructs were made driver-aware so the schema and code work on both.
Behaviour on MySQL is unchanged.

- **`database/migrations/2025_12_11_115457_create_viewers_table.php`**
  The `updated_at` column used the MySQL-only default
  `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, which SQLite cannot parse. The
  migration now applies that clause only on MySQL/MariaDB and falls back to a
  plain `CURRENT_TIMESTAMP` default on other drivers (e.g. SQLite).

- **`app/Http/Controllers/Admin/LiveShowController.php`**
  `$liveShow->users()->update(['is_winner' => false])` relied on MySQL's
  multi-table `UPDATE ... JOIN` semantics to write the pivot column `is_winner`,
  which SQLite rejects. It was replaced with the equivalent, portable pivot-table
  update `UserLiveShow::where('live_show_id', $liveShow->id)->update([...])`,
  which produces the same result on MySQL.

- **`tests/Feature/SendWinnerEmailJobTest.php`**
  `LiveShow::firstOrFail(1)` was corrected to `LiveShow::findOrFail(1)`. The
  former passes `1` as the *columns* argument (so the model is hydrated without
  its real attributes, including its `id`), which made its `users()` relation
  empty; `findOrFail(1)` correctly loads the seeded show with id `1`.

- **`database/migrations/2026_07_08_100000_add_last_active_at_to_user_live_shows_table.php`**
  The gameplay join flow (`GamePlayController::registerUser`,
  `HomeController::liveShowMagicLink`) writes a `last_active_at` value onto the
  `user_live_shows` pivot, but the original pivot migration only defined
  `last_active`. This column was missing from the schema built from migrations,
  so join-flow tests failed on the fresh SQLite database. The new migration adds
  `last_active_at` (guarded with `Schema::hasColumn`, so it is a safe no-op on
  any environment where the column already exists).
