<?php

namespace Database\Seeders;

use App\Models\ChatFilterTier;
use App\Models\ChatFilterWord;
use App\Services\ChatFilterService;
use Illuminate\Database\Seeder;

/**
 * chat_filter_module: seeds the four policy tiers and the full BADABING word
 * list. Defaults follow the "policy recommended" live setup:
 *   Tier 1 -> ban, Tier 2/3 -> timeout, Tier 4 -> watchlist (links hard-block).
 * The optional English loanwords, evasion spellings and regex block are seeded
 * but inactive (is_active = false) so they can be flipped on from the admin UI.
 *
 * Run: php artisan db:seed --class=ChatFilterSeeder
 */
class ChatFilterSeeder extends Seeder
{
    public function run(): void
    {
        // chat_filter_module: tiers with their default enforcement policy.
        $t1 = ChatFilterTier::updateOrCreate(['tier_number' => 1], [
            'name' => 'Hate speech / slurs',
            'slug' => 'hate-speech',
            'description' => 'Slurs, hate speech, Nazi content. Delete + ban immediately.',
            'action' => 'ban',
            'delete_message' => true,
            'is_enabled' => true,
            'timeout_minutes' => 10,
            'timeout_after_offenses' => 1,
        ]);

        $t2 = ChatFilterTier::updateOrCreate(['tier_number' => 2], [
            'name' => 'Vulgar / sexual',
            'slug' => 'vulgar-sexual',
            'description' => 'Vulgar / sexual language. Delete, timeout 10 min on repeat.',
            'action' => 'timeout',
            'delete_message' => true,
            'is_enabled' => true,
            'timeout_minutes' => 10,
            'timeout_after_offenses' => 1,
        ]);

        $t3 = ChatFilterTier::updateOrCreate(['tier_number' => 3], [
            'name' => 'Insults / toxic',
            'slug' => 'insults-toxic',
            'description' => 'Insults / toxic. Delete, timeout on repeat. Threats/suicide bait = ban.',
            'action' => 'timeout',
            'delete_message' => true,
            'is_enabled' => true,
            'timeout_minutes' => 10,
            'timeout_after_offenses' => 1,
        ]);

        $t4 = ChatFilterTier::updateOrCreate(['tier_number' => 4], [
            'name' => 'Spam / scam / accusations',
            'slug' => 'spam-scam',
            'description' => 'Watchlist only - do NOT hard block. Link/crypto spam is the exception.',
            'action' => 'watchlist',
            'delete_message' => false,
            'is_enabled' => true,
            'timeout_minutes' => 10,
            'timeout_after_offenses' => 1,
        ]);

        // chat_filter_module: helper to upsert a word (idempotent on term+match_type).
        $add = function ($tierId, $term, $type = 'literal', $active = true, $whole = false, $override = null, $note = null) {
            ChatFilterWord::updateOrCreate(
                ['term' => $term, 'match_type' => $type],
                [
                    'chat_filter_tier_id' => $tierId,
                    'whole_word' => $whole,
                    'is_active' => $active,
                    'action_override' => $override,
                    'note' => $note,
                ]
            );
        };

        // ---------------- TIER 1 - hard block / ban ----------------
        foreach (['negger', 'n3ger', 'kanake', 'kanacke', 'kanaken', 'zigeuner', 'schlitzauge', 'schlitzaugen', 'fidschi', 'kümmeltürke', 'kameltreiber', 'judensau', 'judenschwein', 'vergasen', 'vergast', 'gaskammer', 'siegheil', 'heilhitler', '1488', 'hakenkreuz', 'schwuchtel', 'schwuchteln', 'schwuli', 'tunte', 'kinderficker', 'kinderschänder', 'kinderschaender', 'pädo', 'paedo'] as $w) {
            $add($t1->id, $w);
        }
        // chat_filter_module: word boundary required (neger -> Nigeria false positive)
        $add($t1->id, 'neger', 'literal', true, true, null, 'Word boundary only (avoids "Nigeria").');
        // chat_filter_module: can be self-identification -> send to mod review instead of auto-ban
        $add($t1->id, 'transe', 'literal', true, false, 'watchlist', 'Possible self-identification, route to review.');
        foreach (['jude verrecke', 'ab ins kz', 'ins gas', 'sieg heil', 'heil hitler', 'hitler hatte recht', '14/88', 'juden raus', 'ausländer raus', 'auslaender raus', 'deutschland den deutschen'] as $w) {
            $add($t1->id, $w, 'phrase');
        }

        // ---------------- TIER 2 - vulgar / sexual ----------------
        foreach (['fick', 'ficken', 'fickt', 'fickdich', 'gefickt', 'verfickt', 'wichser', 'wixer', 'wichsen', 'wixxen', 'wichs', 'fotze', 'votze', 'muschi', 'möse', 'moese', 'schwanzlutscher', 'titten', 'titte', 'nippel', 'sperma', 'abspritzen', 'bumsen', 'gebumst', 'vögeln', 'voegeln', 'poppen', 'nutte', 'nutten', 'huren', 'schlampe', 'schlampen', 'porno', 'pornos', 'schwanzvergleich', 'arschficken', 'analfick', 'nacktbilder', 'schwanzbild'] as $w) {
            $add($t2->id, $w);
        }
        // chat_filter_module: word boundary required (hure -> harmless substrings)
        $add($t2->id, 'hure', 'literal', true, true, null, 'Word boundary only.');
        foreach (['fick dich', 'schwanz raus', 'lutsch meinen', 'blas mir'] as $w) {
            $add($t2->id, $w, 'phrase');
        }

        // ---------------- TIER 3 - insults / toxic ----------------
        foreach (['hurensohn', 'hurensöhne', 'hurensoehne', 'hurentochter', 'hurenkind', 'missgeburt', 'mongoloid', 'spast', 'spasti', 'spacko', 'behinderter', 'behindi', 'krüppel', 'krueppel', 'bastard', 'drecksack', 'dreckschwein', 'dreckstück', 'abschaum', 'untermensch', 'untermenschen', 'vollidiot', 'vollpfosten', 'vollhonk', 'hirntot', 'schwachkopf', 'schwachmat', 'gehirnamputiert', 'verpiss', 'haltdiefresse', 'penner', 'assi', 'asozialer'] as $w) {
            $add($t3->id, $w);
        }
        // chat_filter_module: word boundary required (mongo -> Mongolei, MongoDB)
        $add($t3->id, 'mongo', 'literal', true, true, null, 'Word boundary only.');
        // chat_filter_module: threats / suicide bait -> ban (override the tier timeout)
        foreach (['stirb', 'verreck', 'verrecke'] as $w) {
            $add($t3->id, $w, 'literal', true, false, 'ban', 'Suicide bait / death threat.');
        }
        foreach (['verpiss dich', 'halt die fresse', 'fresse halten', 'schnauze du', 'du opfer', 'lappen du'] as $w) {
            $add($t3->id, $w, 'phrase');
        }
        foreach (['bring dich um', 'töte dich', 'toete dich', 'hoffentlich stirbst', 'ich finde dich', 'ich weiß wo du wohnst', 'ich weiss wo du wohnst'] as $w) {
            $add($t3->id, $w, 'phrase', true, false, 'ban', 'Threat -> ban.');
        }

        // ---------------- TIER 4 - spam / scam / accusations (watchlist) ----------------
        foreach (['betrug', 'betrüger', 'abzocke', 'abzocker', 'manipuliert', 'manipulation', 'anwalt', 'verbraucherzentrale', 'anzeige'] as $w) {
            $add($t4->id, $w);
        }
        foreach (['gewinner steht fest', 'alles abgesprochen', 'nie geld gesehen', 'kein gewinn bekommen', 'gutschein nicht angekommen', 'schreib mir privat', 'gratis follower', 'gewinn garantiert', 'folge mir'] as $w) {
            $add($t4->id, $w, 'phrase');
        }
        // chat_filter_module: link / crypto / follower spam is advertising, not criticism -> hard block
        foreach (['tinyurl', 'cashapp', 'telegram', 'bitcoin', 'crypto', 'investment'] as $w) {
            $add($t4->id, $w, 'literal', true, false, 'hard_block', 'Link/crypto spam - hard block.');
        }
        foreach (['t.me/', 'bit.ly', 'paypal.me', 'whatsapp mich'] as $w) {
            $add($t4->id, $w, 'phrase', true, false, 'hard_block', 'Link/crypto spam - hard block.');
        }

        // ---------------- OPTIONAL: English loanwords (seeded, inactive) ----------------
        foreach (['fuck', 'fucking', 'fuk', 'fvck', 'motherfucker', 'bitch', 'b1tch', 'whore', 'slut', 'cunt', 'nudes', 'dickpic', 'onlyfans', 'pornhub'] as $w) {
            $add($t2->id, $w, 'literal', false, false, null, 'Optional English loanword.');
        }
        foreach (['scam', 'fake', 'sub4sub', 'follow4follow', 'f4f'] as $w) {
            $add($t4->id, $w, 'literal', false, false, null, 'Optional English loanword.');
        }
        $add($t3->id, 'kys', 'literal', false, false, 'ban', 'Optional English loanword (suicide bait).');
        $add($t3->id, 'kill yourself', 'phrase', false, false, 'ban', 'Optional English loanword (suicide bait).');

        // ---------------- EVASION SPELLINGS (seeded, inactive) ----------------
        $evasionT3 = ['h u r e n s o h n', 'h-u-r-e-n-s-o-h-n', 'hur3nsohn', 'hurens0hn', 'hs0hn', 'sp4st', 'm0ngo'];
        foreach ($evasionT3 as $w) {
            $add($t3->id, $w, 'literal', false, false, null, 'Evasion spelling.');
        }
        $evasionT2 = ['f1ck', 'f*ck', 'fu ck', 'fu*ck', 'phuck', 'w1chser', 'wixxa', 'sch1ampe', 'fotz3', 'p0rno'];
        foreach ($evasionT2 as $w) {
            $add($t2->id, $w, 'literal', false, false, null, 'Evasion spelling.');
        }
        $add($t1->id, 'n*ger', 'literal', false, false, null, 'Evasion spelling.');
        $add($t1->id, 'sch2uchtel', 'literal', false, false, null, 'Evasion spelling.');

        // ---------------- REGEX BLOCK (seeded, inactive) ----------------
        $add($t3->id, '(?i)\bh+[\W_]*u+[\W_]*r+[\W_]*[e3]+[\W_]*n+[\W_]*s+[\W_]*[o0]+[\W_]*h+[\W_]*n+', 'regex', false, false, null, 'Regex: hurensohn variants.');
        $add($t2->id, '(?i)\bf+[\W_]*[i1!]+[\W_]*c*[\W_]*k+', 'regex', false, false, null, 'Regex: fick variants.');
        $add($t2->id, '(?i)\bw+[\W_]*[i1]+[\W_]*(ch|x)+[\W_]*s+[\W_]*[e3]*[\W_]*r*', 'regex', false, false, null, 'Regex: wichser variants.');
        $add($t1->id, '(?i)\bn+[\W_]*[i1e3]+[\W_]*g+[\W_]*g*[\W_]*[e3a4]+[\W_]*r+', 'regex', false, false, null, 'Regex: n-word variants.');
        $add($t2->id, '(?i)\bf+[\W_]*[o0]+[\W_]*t+[\W_]*z+[\W_]*[e3]*', 'regex', false, false, null, 'Regex: fotze variants.');
        $add($t3->id, '(?i)\bs+[\W_]*p+[\W_]*a+[\W_]*s+[\W_]*t+[\W_]*[i1]*', 'regex', false, false, null, 'Regex: spast variants.');
        $add($t1->id, '(?i)\b(sieg|si3g)[\W_]*heil', 'regex', false, false, null, 'Regex: sieg heil variants.');
        $add($t1->id, '(?i)\b14[\W_]*88\b', 'regex', false, false, null, 'Regex: 1488 variants.');
        $add($t3->id, '(?i)\bk+[\W_]*y+[\W_]*s+\b', 'regex', false, false, 'ban', 'Regex: kys variants.');
        $add($t4->id, '(?i)(t\.me/|bit\.ly/|tinyurl\.com|paypal\.me/)', 'regex', false, false, 'hard_block', 'Regex: link spam.');

        // chat_filter_module: make sure the live rule set picks up the new words.
        ChatFilterService::flushCache();
    }
}
