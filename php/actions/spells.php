<?php
/**
 * Spells — Character Generator
 *
 * Actions:
 *   cg_install_spells      — Create table + seed all spell data (idempotent).
 *   cg_get_spells_for_gifts — Return spells available to the character's gift set.
 */
require_once __DIR__ . '/../includes/db.php';

// ---------------------------------------------------------------------------
// Table helpers
// ---------------------------------------------------------------------------

function cg_spells_table(): string {
    return cg_prefix() . 'customtables_table_spells';
}

function cg_ensure_spells_table(): void {
    $t = cg_spells_table();
    cg_exec("CREATE TABLE IF NOT EXISTS {$t} (
        ct_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ct_name       VARCHAR(255)  NOT NULL DEFAULT '',
        ct_equip      VARCHAR(255)  NOT NULL DEFAULT '',
        ct_range      VARCHAR(100)  NOT NULL DEFAULT '',
        ct_attack_dice TEXT         NOT NULL,
        ct_effect     TEXT          NOT NULL,
        ct_descriptors TEXT         NOT NULL,
        ct_gift_name  VARCHAR(255)  NOT NULL DEFAULT '',
        ct_sort       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        INDEX idx_gift_name (ct_gift_name(100))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// ---------------------------------------------------------------------------
// Seed data  (name, equip, range, attack_dice, effect, descriptors, gift_name, sort)
// gift_name = 'Common Magic' is treated specially — included for ANY magic gift.
// All other gift_name values must exactly match ct_gifts_name in the gifts table.
// ---------------------------------------------------------------------------

function cg_spell_rows(): array {
    return [
        // ── Common Magic Spells (Apprentice) ────────────────────────────────
        ['Ataxia',        'Off hand', 'Medium', 'Mind, Will vs. 3',                          'Slowed',                                   'Magic, Psychic, Apprentice, Mystic',              'Common Magic', 10],
        ['Create Air',    'Off hand', 'Medium', 'Speed, Weather Sense vs. defense',           'Damage +2 Critical, Counters Earth',        'Magic, Air, Apprentice, Mystic',                  'Common Magic', 20],
        ['Create Earth',  'Off hand', 'Medium', 'Body, Digging vs. defense',                  'Damage +2 Critical, Counters Air',          'Magic, Earth, Apprentice, Mystic',                'Common Magic', 30],
        ['Create Water',  'Off hand', 'Medium', 'Mind, Swimming vs. defense',                 'Damage +2 Critical, Counters Fire',         'Magic, Water, Apprentice, Mystic',                'Common Magic', 40],
        ['Eyebite',       'Off hand', 'Medium', 'Mind, Gossip vs. 3',                         'Enraged',                                  'Magic, Psychic, Apprentice, Mystic',              'Common Magic', 50],
        ['Ignite Fire',   'Off hand', 'Medium', 'Will, Presence vs. defense',                 'Damage +1 Critical, On Fire, Counters Water','Magic, Fire, Apprentice, Mystic',                'Common Magic', 60],
        ['Misfortune',    'Off hand', 'Medium', 'Mind, Supernatural vs. 3',                   'Disarm, Counters Magic',                   'Magic, Theurgic, Apprentice, Mystic',             'Common Magic', 70],
        ['Move Air',      'Off hand', 'Medium', 'Speed, Weather Sense vs. 3',                 'Damage +0, Push 1, Counters Air',          'Magic, Air, Apprentice, Environmental',           'Common Magic', 80],
        ['Move Earth',    'Off hand', 'Medium', 'Body, Digging vs. 3',                        'Damage +1, Counters Earth',                'Magic, Earth, Apprentice, Environmental',         'Common Magic', 90],
        ['Move Fire',     'Off hand', 'Medium', 'Will, Presence vs. 3',                       'Damage +0, On Fire, Counters Fire',        'Magic, Fire, Apprentice, Environmental',          'Common Magic', 100],
        ['Move Water',    'Off hand', 'Medium', 'Mind, Swimming vs. 3',                       'Damage +2, Weak, Counters Water',          'Magic, Water, Apprentice, Environmental',         'Common Magic', 110],
        ['Perplex',       'Off hand', 'Medium', 'Mind, Negotiation vs. 3',                    'Confusion',                                'Magic, Psychic, Apprentice, Mystic',              'Common Magic', 120],
        ['Rebuke',        'Off hand', 'Short',  'Will, Leadership vs. 3',                     'Damage +0, Penetrating, Sweep Near, Counters Unholy', 'Magic, Holy, Apprentice, Mystic',      'Common Magic', 130],
        ['Repudiation',   'Off hand', 'Short',  'Will, Academics vs. 3',                      'Confused, Counters Magic',                 'Magic, Holy, Apprentice, Mystic',                 'Common Magic', 140],
        ['Silence',       'Off hand', 'Medium', 'Mind, Will, Supernatural vs. 3',             'Silenced, Counters Magic',                 'Magic, Theurgic, Unreal, Apprentice, Mystic',     'Common Magic', 150],

        // ── Journeyman Air Magic ────────────────────────────────────────────
        ['Gust of Wind',  'Off hand', 'Medium', 'Speed, Weather Sense vs. 3',                 'Damage +1, Push 3, Explosion: Short',      'Magic, Air, Journeyman, Loud',                    'Journeyman Air Magic', 10],
        ['Thunderclap',   'Off hand', 'Medium', 'Speed, Weather Sense vs. 3',                 'Damage +2, Explosion: Near',               'Magic, Air, Journeyman, Loud',                    'Journeyman Air Magic', 20],
        ['Lightning Bolt','Off hand', 'Medium', 'Speed, Weather Sense vs. defense',           'Damage +2 Critical, Penetrating, Confusion','Magic, Air, Journeyman, Loud',                   'Journeyman Air Magic', 30],

        // ── Master Air Magic ────────────────────────────────────────────────
        ['Chain Lightning','Off hand','Medium', 'Speed, Weather Sense vs. defense',           'Damage +3 Critical, Confusion, Sweep Short','Magic, Air, Master, Loud',                       'Master Air Magic', 10],
        ['The Tempest',   'Off hand', 'Medium', 'Speed, Weather Sense vs. 3',                 'Damage +1 Penetrating, Push 3, Explosion: Short or Medium', 'Magic, Air, Master, Loud',      'Master Air Magic', 20],

        // ── Journeyman Earth Magic ──────────────────────────────────────────
        ['Landslide',     'Off hand', 'Medium', 'Body, Digging vs. 3',                        'Damage +2, Explosion: Near',               'Magic, Earth, Journeyman, Loud',                  'Journeyman Earth Magic', 10],
        ['Quake',         'Off hand', 'Medium', 'Body, Digging vs. 3',                        'Damage +1 Penetrating, Explosion: Near',   'Magic, Earth, Journeyman, Loud',                  'Journeyman Earth Magic', 20],
        ['Stone Hurlant', 'Off hand', 'Medium', 'Body, Digging vs. defense',                  'Damage +3 Critical, Knockdown',            'Magic, Earth, Journeyman, Loud',                  'Journeyman Earth Magic', 30],

        // ── Master Earth Magic ──────────────────────────────────────────────
        ['Sand Blast',    'Off hand', 'Medium', 'Body, Digging vs. defense',                  'Damage +3 Critical, Penetrating, Sweep Short', 'Magic, Earth, Master, Loud',                 'Master Earth Magic', 10],
        ['Upheaval',      'Off hand', 'Medium', 'Body, Digging vs. 3',                        'Damage +2, Grappled, Explosion: Short or Medium', 'Magic, Earth, Master, Loud',              'Master Earth Magic', 20],

        // ── Journeyman Fire Magic ───────────────────────────────────────────
        ['Fire Ball',     'Off hand', 'Medium', 'Will, Presence vs. 3',                       'Damage +3, Explosion: Reach',              'Magic, Fire, Journeyman, Loud',                   'Journeyman Fire Magic', 10],
        ['Pilum of Fire', 'Off hand', 'Medium', 'Will, Presence vs. defense',                 'Damage +3 Critical, On Fire',              'Magic, Fire, Journeyman, Loud',                   'Journeyman Fire Magic', 20],
        ['Sunburst',      'Off hand', 'Medium', 'Will, Presence vs. 3',                       'Damage +1, Blinded, Explosion: Near',      'Magic, Fire, Journeyman, Loud',                   'Journeyman Fire Magic', 30],

        // ── Master Fire Magic ───────────────────────────────────────────────
        ['Meteor Swarm',  'Off hand', 'Medium', 'Will, Presence vs. 3',                       'Damage +3, On Fire, Explosion: Near or Short', 'Magic, Fire, Master, Loud',                  'Master Fire Magic', 10],
        ['Whips of Flame','Off hand', 'Medium', 'Will, Presence vs. defense',                 'Damage +2 Penetrating, On Fire, Sweep Short', 'Magic, Fire, Master, Loud',                   'Master Fire Magic', 20],

        // ── Journeyman Water Magic ──────────────────────────────────────────
        ['Freeze Arrow',  'Off hand', 'Medium', 'Mind, Swimming vs. defense',                 'Damage +2 Critical, Slowed',               'Magic, Water, Journeyman, Loud',                  'Journeyman Water Magic', 10],
        ['Ice Storm',     'Off hand', 'Medium', 'Mind, Swimming vs. 3',                       'Damage +1 Penetrating, Immobilized, Explosion: Near', 'Magic, Water, Journeyman, Loud',      'Journeyman Water Magic', 20],
        ['Maelstrom',     'Off hand', 'Medium', 'Mind, Swimming vs. 3',                       'Damage +1 Penetrating, Confusion, Explosion: Near', 'Magic, Water, Journeyman, Loud',        'Journeyman Water Magic', 30],

        // ── Master Water Magic ──────────────────────────────────────────────
        ['Aegir',         'Off hand', 'Medium', 'Mind, Swimming vs. defense',                 'Damage +2 Critical Penetrating, Sweep Short', 'Magic, Water, Master, Loud',                  'Master Water Magic', 10],
        ['Hoarfrost',     'Off hand', 'Medium', 'Mind, Swimming vs. 3',                       'Damage +2 Penetrating, Confused, Slowed, Explosion: Short or Medium', 'Magic, Water, Master, Loud', 'Master Water Magic', 20],

        // ── Journeyman White Magic ──────────────────────────────────────────
        ['Fulguration',   'Rod, Wand, or Calendar Sword', 'Medium', 'Will, Leadership vs. defense', 'Damage +4 Weak, Sweep Short, Counters Unholy', 'Magic, Holy, Journeyman',              'Journeyman White Magic', 10],

        // ── Journeyman Cognoscente Magic ────────────────────────────────────
        ['Blindness',     'Off hand', 'Medium', 'Mind, Will vs. 3',                           'Blinded',                                  'Magic, Psyche, Journeyman, Thought, Mystic',      'Journeyman Cognoscente Magic', 10],
        ['Mesmerism',     'Off hand', 'Medium', 'Mind, Will vs. 3',                           'Mesmerized',                               'Magic, Psyche, Journeyman, Emotion, Mystic',      'Journeyman Cognoscente Magic', 20],
        ['Paralysis',     'Off hand', 'Medium', 'Mind, Will vs. 3',                           'Immobilized',                              'Magic, Psyche, Journeyman, Function, Mystic',     'Journeyman Cognoscente Magic', 30],

        // ── Secret Star Magic of the Dunwasser Academy ──────────────────────
        ['Comet Fall',    'Off hand', 'Medium', 'Body, Speed, Mind, Will, Academics vs. 3',   'Damage +0 Penetrating, Slaying, Explosion: Reach, Near, Short, or Medium', 'Magic, Air, Earth, Fire, Water, Star, Loud', 'Secret Star Magic of the Dunwasser Academy', 10],
        ['Starlight',     'Off hand', 'Medium', 'Body, Speed, Mind, Will, Academics vs. defense', 'Damage +0 Penetrating, Slaying',       'Magic, Air, Earth, Fire, Water, Star, Loud',      'Secret Star Magic of the Dunwasser Academy', 20],

        // ── Necromantic Weapons ─────────────────────────────────────────────
        ['Curse',         'Off hand', 'Medium', 'Will, Deceit, 3d6 vs. special',              'See page 313',                             'Magic, Unholy, Journeyman, Mystic, Proscribed, Counters Magic', 'Necromancy', 10],
        ['Horror',        'Off hand', 'Medium', 'Mind, Deceit, 2d6 vs. target\'s Mind, Will, Inquiry, Supernatural', 'Afraid',           'Magic, Unholy, Apprentice, Mystic, Proscribed, Counters Magic', 'Necromancy', 20],
        ['Illness',       'Off hand', 'Medium', 'Will, Negotiation, 2d6 vs. target\'s Body, Mind, Will, Endurance, Supernatural', 'Fatigued', 'Magic, Unholy, Apprentice, Mystic, Proscribed, Counters Magic', 'Necromancy', 30],
        ['Mass Agony',    'Off hand', 'Medium', 'Will, Leadership, 3d6 vs. 3',                'Damage +1 Critical, Penetrating, Weak, Explosion: Near', 'Magic, Unholy, Journeyman, Mystic, Proscribed, Counters Magic', 'Necromancy', 40],
        ['Torment',       'Off hand', 'Medium', 'Will, Leadership, 2d6 vs. target\'s Mind, Supernatural', 'Damage +1 Critical, Penetrating, Weak', 'Magic, Unholy, Apprentice, Mystic, Proscribed, Counters Magic', 'Necromancy', 50],
        ['Unmaking',      'Off hand', 'Medium', 'Will, Supernatural, 3d6 vs. special',        'See page 315',                             'Magic, Unholy, Journeyman, Mystic, Proscribed, Counters Magic', 'Necromancy', 60],
    ];
}

// ---------------------------------------------------------------------------
// cg_install_spells — idempotent: create table + upsert all rows
// ---------------------------------------------------------------------------

function cg_install_spells(): void {
    cg_ensure_spells_table();

    $t    = cg_spells_table();
    $rows = cg_spell_rows();

    // Truncate first so re-running is always idempotent
    cg_exec("TRUNCATE TABLE {$t}");

    $sql = "INSERT INTO {$t}
                (ct_name, ct_equip, ct_range, ct_attack_dice, ct_effect, ct_descriptors, ct_gift_name, ct_sort)
            VALUES (?,?,?,?,?,?,?,?)";

    $inserted = 0;
    foreach ($rows as $r) {
        try {
            cg_exec($sql, $r);
            $inserted++;
        } catch (Throwable $e) {
            error_log("[CG spells] insert failed for '{$r[0]}': " . $e->getMessage());
        }
    }

    cg_json(['success' => true, 'data' => "Installed {$inserted} spells."]);
}

// ---------------------------------------------------------------------------
// cg_get_spells_for_gifts
//
// POST params:
//   gift_ids[]  — array of gift IDs (ints) from the character's full gift set
//                 (free choices + career + species + xp gifts).
//
// Matching strategy (OR of all conditions):
//   1. ct_name matches a gift name exactly — each spell is its own gift
//      (e.g. gift "Fire Ball" → returns the "Fire Ball" spell row).
//   2. ct_gift_name matches a gift name — school-level gift grants all spells
//      in that school (e.g. a gift literally named "Journeyman Fire Magic").
//   3. ct_gift_name = 'Common Magic' when any gift contains the word "Magic".
// ---------------------------------------------------------------------------

function cg_get_spells_for_gifts(): void {
    $p = cg_prefix();
    $t = cg_spells_table();

    // Ensure the table exists — silently create it if this is the first call
    cg_ensure_spells_table();

    // Parse gift IDs from POST
    $raw = $_POST['gift_ids'] ?? $_POST['gift_ids[]'] ?? [];
    if (is_string($raw)) {
        $decoded = json_decode($raw, true);
        $raw = is_array($decoded) ? $decoded : [];
    }
    if (!is_array($raw)) $raw = [];

    $giftIds = array_values(array_filter(array_map('intval', $raw)));

    if (empty($giftIds)) {
        cg_json(['success' => true, 'data' => []]);
        return;
    }

    // Fetch gift names for all provided IDs
    $inPlaceholders = implode(',', array_fill(0, count($giftIds), '?'));
    $giftRows = cg_query(
        "SELECT ct_id AS id, ct_gifts_name AS name
         FROM {$p}customtables_table_gifts
         WHERE ct_id IN ({$inPlaceholders})",
        $giftIds
    );

    $giftNames = array_values(array_filter(array_column($giftRows, 'name')));
    $hasMagic  = (bool) array_filter($giftNames, fn($n) => stripos((string)$n, 'magic') !== false);

    if (empty($giftNames)) {
        cg_json(['success' => true, 'data' => []]);
        return;
    }

    $conditions = [];
    $params     = [];
    $nPh        = implode(',', array_fill(0, count($giftNames), '?'));

    // 1. Spell name matches a gift name (each spell is its own gift in the DB)
    $conditions[] = "ct_name IN ({$nPh})";
    $params       = array_merge($params, $giftNames);

    // 2. School-level gift: ct_gift_name matches a gift name
    $conditions[] = "ct_gift_name IN ({$nPh})";
    $params       = array_merge($params, $giftNames);

    // 3. Common Magic spells whenever the character has any Magic gift
    if ($hasMagic) {
        $conditions[] = "ct_gift_name = 'Common Magic'";
    }

    $where  = implode(' OR ', $conditions);
    $spells = cg_query(
        "SELECT ct_id AS id, ct_name AS name, ct_equip AS equip, ct_range AS `range`,
                ct_attack_dice AS attack_dice, ct_effect AS effect,
                ct_descriptors AS descriptors, ct_gift_name AS gift_name, ct_sort AS sort
         FROM {$t}
         WHERE {$where}
         ORDER BY ct_gift_name = 'Common Magic' DESC, ct_gift_name, ct_sort, ct_name",
        $params
    );

    cg_json(['success' => true, 'data' => $spells]);
}
