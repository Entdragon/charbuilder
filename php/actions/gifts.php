<?php
require_once __DIR__ . '/../includes/db.php';

const CG_DEFAULT_LANGUAGES = [
    'Berla Feini','Calabrian','Common','Dwarven','Elven','Goblin','Hesperian',
    'Kawtaw','Magniloquentia','Mordic','Old Calabrian','Orcish','Sylvan','Urathi','Zhonggese',
];

const CG_DEFAULT_PERSONALITIES = [
    'Altruistic','Bold','Charitable','Chaste','Cheerful','Courageous',
    'Determined','Devoted','Diplomatic','Energetic','Faithful','Generous',
    'Honest','Humble','Industrious','Just','Kind','Loyal','Merciful',
    'Modest','Patient','Pious','Prudent','Reckless','Selfless','Tenacious',
    'Valiant','Wise',
];

/**
 * Enrich a gift row with effect text from gift_sections if effect columns are empty.
 * Mutates $row by reference.
 */
function cg_enrich_gift_effect(array &$row, int $giftId): void {
    $effect = trim((string)($row['effect'] ?? ''));
    $effDesc = trim((string)($row['effect_description'] ?? ''));
    if ($effect !== '' || $effDesc !== '') return;

    $p = cg_prefix();
    try {
        $sect = cg_query_one(
            "SELECT ct_body AS body
             FROM {$p}customtables_table_gift_sections
             WHERE ct_gift_id = ? AND ct_section_type = 'rules'
             ORDER BY ct_sort ASC LIMIT 1",
            [$giftId]
        );
        if ($sect && !empty($sect['body'])) {
            $body = trim((string)$sect['body']);
            $short = mb_strlen($body) > 180 ? mb_substr($body, 0, 177) . '…' : $body;
            $row['effect']             = $short;
            $row['effect_description'] = $short;
        }
    } catch (Throwable $e) {
        // non-fatal – gift_sections may not exist on all installs
    }
}

function cg_get_local_knowledge(): void {
    $p   = cg_prefix();
    $row = cg_query_one(
        "SELECT ct_id AS id, ct_gifts_name AS name, ct_gifts_manifold AS ct_gifts_manifold,
                ct_gifts_effect AS effect, ct_gifts_effect_description AS effect_description
         FROM {$p}customtables_table_gifts WHERE ct_id = 242 LIMIT 1"
    );
    if (!$row) {
        cg_json(['success' => false, 'data' => 'Local Knowledge gift not found.']);
        return;
    }
    $id = (int)($row['id'] ?? 242);
    cg_enrich_gift_effect($row, $id);
    cg_json(['success' => true, 'data' => $row]);
}

function cg_get_language_gift(): void {
    $p   = cg_prefix();
    $row = cg_query_one(
        "SELECT ct_id AS id, ct_gifts_name AS name, ct_gifts_manifold AS ct_gifts_manifold,
                ct_gifts_effect AS effect, ct_gifts_effect_description AS effect_description
         FROM {$p}customtables_table_gifts WHERE ct_id = 236 LIMIT 1"
    );
    if (!$row) {
        cg_json(['success' => false, 'data' => 'Language gift not found.']);
        return;
    }
    $id = (int)($row['id'] ?? 236);
    cg_enrich_gift_effect($row, $id);
    cg_json(['success' => true, 'data' => $row]);
}

function cg_get_combat_save(): void {
    $p   = cg_prefix();
    $row = cg_query_one(
        "SELECT ct_id AS id, ct_gifts_name AS name, ct_gifts_manifold AS ct_gifts_manifold,
                ct_gifts_effect AS effect, ct_gifts_effect_description AS effect_description
         FROM {$p}customtables_table_gifts WHERE ct_id = 159 LIMIT 1"
    );
    if (!$row) {
        cg_json(['success' => false, 'data' => 'Combat Save gift not found.']);
        return;
    }
    $id = (int)($row['id'] ?? 159);
    cg_enrich_gift_effect($row, $id);
    cg_json(['success' => true, 'data' => $row]);
}

function cg_get_personality_gift(): void {
    $p = cg_prefix();
    try {
        $row = cg_query_one(
            "SELECT ct_id AS id, ct_gifts_name AS name, ct_gifts_manifold AS ct_gifts_manifold,
                    ct_gifts_effect AS effect, ct_gifts_effect_description AS effect_description
             FROM {$p}customtables_table_gifts
             WHERE LOWER(TRIM(ct_gifts_name)) LIKE '%personality%' AND published = 1
             ORDER BY ct_id ASC LIMIT 1"
        );
        if (!$row) {
            cg_json(['success' => false, 'data' => null]);
            return;
        }
        $id = (int)($row['id'] ?? 0);
        if ($id) cg_enrich_gift_effect($row, $id);
        cg_json(['success' => true, 'data' => $row]);
    } catch (Throwable $e) {
        cg_json(['success' => false, 'data' => null]);
    }
}

function cg_get_free_gifts(): void {
    $p = cg_prefix();
    $g = "{$p}customtables_table_gifts";
    $r = "{$p}customtables_table_gift_requirements";
    $gc = "{$p}customtables_table_giftclass";

    // Try with giftclass join to exclude "natural" class gifts; fall back if table absent
    try {
        $rows = cg_query("
            SELECT
              g.ct_id                              AS id,
              g.ct_gifts_name                      AS name,
              g.ct_gifts_allows_multiple           AS allows_multiple,
              g.ct_gifts_manifold                  AS ct_gifts_manifold,
              gc.ct_class_name                     AS giftclass,
              g.ct_gifts_effect                    AS effect,
              g.ct_gifts_effect_description        AS effect_description,
              g.ct_gift_trigger                    AS gift_trigger
            FROM {$g} AS g
            LEFT JOIN {$gc} AS gc ON gc.ct_id = g.ct_gift_class
            WHERE LOWER(TRIM(COALESCE(gc.ct_class_name, ''))) != 'natural'
              AND g.published = 1
            ORDER BY g.ct_gifts_name ASC
        ");
    } catch (Throwable $e) {
        // giftclass table may not exist — fall back to simple query
        $rows = cg_query("
            SELECT
              ct_id                    AS id,
              ct_gifts_name            AS name,
              ct_gifts_allows_multiple AS allows_multiple,
              ct_gifts_manifold        AS ct_gifts_manifold,
              NULL                     AS giftclass,
              ct_gifts_effect          AS effect,
              ct_gifts_effect_description AS effect_description,
              ct_gift_trigger          AS gift_trigger
            FROM {$g}
            WHERE published = 1
            ORDER BY ct_gifts_name ASC
        ");
    }

    // Index by id for fast lookup
    $byId = [];
    foreach ($rows as $row) {
        $byId[(int) $row['id']] = $row;
    }

    // Enrich effect text from gift_sections where the gift_sections table exists
    try {
        $sects = cg_query(
            "SELECT ct_gift_id AS gift_id, MIN(ct_sort) AS min_sort, ct_body AS body
             FROM {$p}customtables_table_gift_sections
             WHERE ct_section_type = 'rules'
             GROUP BY ct_gift_id"
        );
        foreach ($sects as $s) {
            $gId = (int)$s['gift_id'];
            if (!isset($byId[$gId])) continue;
            $body = trim((string)($s['body'] ?? ''));
            if ($body === '') continue;
            $short = mb_strlen($body) > 180 ? mb_substr($body, 0, 177) . '…' : $body;
            if (trim((string)($byId[$gId]['effect'] ?? '')) === '') {
                $byId[$gId]['effect'] = $short;
            }
            if (trim((string)($byId[$gId]['effect_description'] ?? '')) === '') {
                $byId[$gId]['effect_description'] = $short;
            }
        }
    } catch (Throwable $e) {
        // gift_sections may not exist — skip enrichment
    }

    // Suffix maps for flat ct_gifts_requires* columns (JS legacy path: extractRequiredGiftIds).
    // Indexed by 1-based position within each gift+kind group (NOT by raw ct_sort value).
    $requireSuffixes = [
        1 => '',        2 => '_two',     3 => '_three',   4 => '_four',
        5 => '_five',   6 => '_six',     7 => '_seven',   8 => '_eight',
        9 => '_nine',  10 => '_ten',    11 => '_eleven', 12 => '_twelve',
       13 => '_thirteen', 14 => '_fourteen', 15 => '_fifteen', 16 => '_sixteen',
       17 => '_seventeen', 18 => '_eighteen', 19 => '_nineteen',
    ];

    // Fetch gift_requirements ordered by gift then sort position.
    $reqs = cg_query("
        SELECT ct_gift_id, ct_sort, ct_req_kind, ct_req_ref_id, ct_req_text
        FROM {$r}
        ORDER BY ct_gift_id ASC, ct_sort ASC
    ");

    // Per-gift, per-kind position counters for flat column indexing.
    $posCounters    = [];   // "{$gId}_{$kind}" => int
    // Accumulate special_text lines per gift to join into a single string.
    $specialTexts   = [];   // $gId => string[]

    foreach ($reqs as $req) {
        $gId  = (int) $req['ct_gift_id'];
        $sort = (int) $req['ct_sort'];
        $kind = (string) $req['ct_req_kind'];

        if (!isset($byId[$gId])) continue;

        // (a) Structured requirements array — used by evaluateStructuredPrereqs in JS
        if (!isset($byId[$gId]['requirements'])) {
            $byId[$gId]['requirements'] = [];
        }
        $byId[$gId]['requirements'][] = [
            'gift_id' => $gId,
            'sort'    => $sort,
            'kind'    => $kind,
            'ref_id'  => $req['ct_req_ref_id'] !== null ? (int) $req['ct_req_ref_id'] : null,
            'text'    => $req['ct_req_text'] ?? '',
        ];

        // (b) Flat columns — legacy JS path (extractRequiredGiftIds / requiresSpecialText)
        $posKey = "{$gId}_{$kind}";
        $posCounters[$posKey] = ($posCounters[$posKey] ?? 0) + 1;
        $pos = $posCounters[$posKey];

        if ($kind === 'gift_ref' || $kind === 'legacy_text') {
            // Each gift_ref becomes ct_gifts_requires, ct_gifts_requires_two, ...
            if (isset($requireSuffixes[$pos])) {
                $col = 'ct_gifts_requires' . $requireSuffixes[$pos];
                $val = ($kind === 'gift_ref')
                    ? (int) $req['ct_req_ref_id']
                    : (int) $req['ct_req_text'];
                $byId[$gId][$col] = $val;
            }

        } elseif ($kind === 'special_text') {
            // Accumulate; will be joined into a single ct_gifts_requires_special string below.
            $specialTexts[$gId][] = (string) $req['ct_req_text'];
        }
    }

    // Build ct_gifts_requires_special as a newline-joined string of all special_text values.
    // JS reads this as one block and splits by \n to find "Mystic: X", "Mind of d8+", etc.
    foreach ($specialTexts as $gId => $lines) {
        if (!isset($byId[$gId])) continue;
        $byId[$gId]['ct_gifts_requires_special'] = implode("\n", array_filter($lines));
    }

    // Fetch gift_prereq (structured species/trait/GM prereqs) and attach as `prereqs` array.
    // This table may not exist on all installs — gracefully skip if absent.
    try {
        $prereqRows = cg_query("
            SELECT id, gift_id, slot, kind, raw_text, req_key, req_value,
                   trait_key, die_min, comparator, qty_required
            FROM {$p}customtables_table_gift_prereq
            ORDER BY gift_id ASC, slot ASC
        ");
        foreach ($prereqRows as $pr) {
            $gId = (int) $pr['gift_id'];
            if (!isset($byId[$gId])) continue;
            if (!isset($byId[$gId]['prereqs'])) $byId[$gId]['prereqs'] = [];
            $byId[$gId]['prereqs'][] = $pr;
        }
    } catch (Throwable $e) {
        // gift_prereq table absent — filtering falls back to flat columns and requires_special text
    }

    // Fetch primary rule type per gift from gift_rules (first row by sort order).
    // This table may not exist on all installs — gracefully skip if absent.
    try {
        $ruleRows = cg_query("
            SELECT ct_gift_id AS gift_id, ct_rule_type AS rule_type
            FROM {$p}customtables_table_gift_rules
            ORDER BY ct_gift_id ASC, ct_sort ASC, ct_id ASC
        ");
        foreach ($ruleRows as $rr) {
            $gId = (int) $rr['gift_id'];
            if (!isset($byId[$gId])) continue;
            if (isset($byId[$gId]['rule_type'])) continue; // keep first by sort
            $rt = trim((string) ($rr['rule_type'] ?? ''));
            if ($rt !== '') $byId[$gId]['rule_type'] = $rt;
        }
    } catch (Throwable $e) {
        // gift_rules table absent — rule_type will be null on all gifts
    }

    // Fetch descriptor tags per gift from gift_type_map (flat string array).
    // Schema-tolerant: discovers both the gift FK column and the tag/label column
    // via INFORMATION_SCHEMA, and handles the case where the tag column stores
    // integer FK IDs (resolved via JOIN to referenced table) vs. text labels (direct).
    // Note: INFORMATION_SCHEMA queries run once per request (not in a loop); if per-request
    // latency is a concern in production, resolved column metadata can be stored in a
    // WordPress transient and read here instead of querying INFORMATION_SCHEMA each time.
    // This table may not exist on all installs — gracefully skip if absent.
    try {
        $tmTable = "{$p}customtables_table_gift_type_map";
        $sys     = "('ct_id','ct_sort','published','created_at','updated_at','id','sort','gift_order','ordering')";

        // Discover all non-system columns with their data types
        $allCols = cg_query("
            SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME   = '{$tmTable}'
              AND TABLE_SCHEMA = DATABASE()
              AND COLUMN_NAME NOT IN {$sys}
            ORDER BY ORDINAL_POSITION ASC
        ");

        if (!empty($allCols)) {
            $numericTypes  = ['int','tinyint','smallint','mediumint','bigint','integer'];
            $giftFkCol     = null; // column pointing at gifts table (FK)
            $tagCandidates = []; // columns for the actual tag/label

            foreach ($allCols as $col) {
                $cName  = $col['COLUMN_NAME'];
                $cType  = strtolower($col['DATA_TYPE']);
                $isInt  = in_array($cType, $numericTypes, true);

                // Heuristic: column named *gift_id* is the FK to the gifts table
                if (preg_match('/\bgift_id\b/i', $cName)) {
                    $giftFkCol = $cName;
                } else {
                    $tagCandidates[] = ['name' => $cName, 'isInt' => $isInt];
                }
            }

            // Default gift FK column if heuristic didn't find one
            if ($giftFkCol === null) {
                $giftFkCol = 'ct_gift_id'; // CT plugin convention
            }

            // Pick first tag candidate; prefer text columns over integer ones
            $tagColInfo = null;
            foreach ($tagCandidates as $tc) {
                if (!$tc['isInt']) { $tagColInfo = $tc; break; }
            }
            if ($tagColInfo === null && !empty($tagCandidates)) {
                $tagColInfo = $tagCandidates[0]; // integer column — may be a FK
            }

            if ($tagColInfo !== null) {
                $tagCol = $tagColInfo['name'];

                if ($tagColInfo['isInt']) {
                    // Numeric column: try to resolve via FK constraint to a lookup table
                    $fkInfo = cg_query("
                        SELECT kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                        WHERE kcu.TABLE_NAME        = '{$tmTable}'
                          AND kcu.TABLE_SCHEMA      = DATABASE()
                          AND kcu.COLUMN_NAME       = '{$tagCol}'
                          AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                        LIMIT 1
                    ");

                    if (!empty($fkInfo)) {
                        // Find the first text column in the referenced lookup table
                        $refTable  = $fkInfo[0]['REFERENCED_TABLE_NAME'];
                        $refIdCol  = $fkInfo[0]['REFERENCED_COLUMN_NAME'];
                        $labelCols = cg_query("
                            SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE TABLE_NAME   = '{$refTable}'
                              AND TABLE_SCHEMA = DATABASE()
                              AND DATA_TYPE    IN ('varchar','char','text','tinytext','mediumtext','longtext')
                              AND COLUMN_NAME  NOT IN {$sys}
                            ORDER BY ORDINAL_POSITION ASC
                            LIMIT 1
                        ");
                        if (!empty($labelCols)) {
                            $labelCol = $labelCols[0]['COLUMN_NAME'];
                            $tagRows  = cg_query("
                                SELECT m.`{$giftFkCol}` AS gift_id, l.`{$labelCol}` AS tag
                                FROM `{$tmTable}` m
                                INNER JOIN `{$refTable}` l ON l.`{$refIdCol}` = m.`{$tagCol}`
                                ORDER BY m.`{$giftFkCol}` ASC
                            ");
                            foreach ($tagRows as $tr) {
                                $gId = (int) $tr['gift_id'];
                                if (!isset($byId[$gId])) continue;
                                if (!isset($byId[$gId]['tags'])) $byId[$gId]['tags'] = [];
                                $tag = trim((string) ($tr['tag'] ?? ''));
                                if ($tag !== '') $byId[$gId]['tags'][] = $tag;
                            }
                        }
                        // If no FK info or label column found, skip tags (prefer no chips over ID chips)
                    }
                } else {
                    // Text column — values are already human-readable descriptor labels
                    // (e.g. "Air", "Fire", "Magic"). Use directly.
                    $tagRows = cg_query("
                        SELECT `{$giftFkCol}` AS gift_id, `{$tagCol}` AS tag
                        FROM `{$tmTable}`
                        ORDER BY `{$giftFkCol}` ASC
                    ");
                    foreach ($tagRows as $tr) {
                        $gId = (int) $tr['gift_id'];
                        if (!isset($byId[$gId])) continue;
                        if (!isset($byId[$gId]['tags'])) $byId[$gId]['tags'] = [];
                        $tag = trim((string) ($tr['tag'] ?? ''));
                        if ($tag !== '') $byId[$gId]['tags'][] = $tag;
                    }
                }
            }
        }
    } catch (Throwable $e) {
        // gift_type_map table absent or schema differs — tags will be empty on all gifts
    }

    // Deduplicate and sort descriptor tags per gift for clean chip rendering.
    // Also remap gift_trigger → trigger (TRIGGER is a reserved word in MariaDB/MySQL,
    // so the SQL alias must differ; we normalise back to 'trigger' here for JS consumers).
    foreach ($byId as &$gift) {
        if (!empty($gift['tags'])) {
            $gift['tags'] = array_values(array_unique($gift['tags']));
            sort($gift['tags']);
        }
        if (array_key_exists('gift_trigger', $gift)) {
            $gift['trigger'] = $gift['gift_trigger'];
            unset($gift['gift_trigger']);
        }
    }
    unset($gift);

    cg_json(['success' => true, 'data' => array_values($byId)]);
}

function cg_ensure_language_table(): void {
    $p     = cg_prefix();
    $table = "`{$p}cg_languages`";

    cg_exec("
        CREATE TABLE IF NOT EXISTS {$table} (
          id         INT AUTO_INCREMENT PRIMARY KEY,
          name       VARCHAR(100) NOT NULL,
          sort_order INT NOT NULL DEFAULT 0,
          UNIQUE KEY uq_cg_lang_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Always INSERT IGNORE so newly added default languages appear on existing installs
    $langs = CG_DEFAULT_LANGUAGES;
    $placeholders = implode(', ', array_map(fn($i) => "(?, {$i})", range(0, count($langs) - 1)));
    cg_exec(
        "INSERT IGNORE INTO {$table} (name, sort_order) VALUES {$placeholders}",
        $langs
    );
}

function cg_get_language_list(): void {
    $p = cg_prefix();
    try {
        cg_ensure_language_table();
        $rows = cg_query("SELECT name FROM `{$p}cg_languages` ORDER BY sort_order ASC, name ASC");
        $list = array_values(array_filter(array_map(fn($r) => trim($r['name'] ?? ''), $rows)));
        cg_json(['success' => true, 'data' => $list]);
    } catch (Throwable $e) {
        error_log('[CG] cg_get_language_list DB error: ' . $e->getMessage());
        $fallback = CG_DEFAULT_LANGUAGES;
        sort($fallback);
        cg_json(['success' => true, 'data' => $fallback]);
    }
}

function cg_ensure_personality_table(): void {
    $p     = cg_prefix();
    $table = "`{$p}cg_personality`";

    cg_exec("
        CREATE TABLE IF NOT EXISTS {$table} (
          id         INT AUTO_INCREMENT PRIMARY KEY,
          name       VARCHAR(100) NOT NULL,
          sort_order INT NOT NULL DEFAULT 0,
          UNIQUE KEY uq_cg_pers_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $count = (int) (cg_query_one("SELECT COUNT(*) AS cnt FROM {$table}")['cnt'] ?? 0);
    if ($count === 0) {
        $list = CG_DEFAULT_PERSONALITIES;
        $placeholders = implode(', ', array_map(fn($i) => "(?, {$i})", range(0, count($list) - 1)));
        cg_exec(
            "INSERT IGNORE INTO {$table} (name, sort_order) VALUES {$placeholders}",
            $list
        );
    }
}

function cg_get_personality_list(): void {
    $p = cg_prefix();
    try {
        cg_ensure_personality_table();
        $rows = cg_query("SELECT name FROM `{$p}cg_personality` ORDER BY sort_order ASC, name ASC");
        $list = array_values(array_filter(array_map(fn($r) => trim($r['name'] ?? ''), $rows)));
        if (empty($list)) {
            $list = CG_DEFAULT_PERSONALITIES;
            sort($list);
        }
        cg_json(['success' => true, 'data' => $list]);
    } catch (Throwable $e) {
        $fallback = CG_DEFAULT_PERSONALITIES;
        sort($fallback);
        cg_json(['success' => true, 'data' => $fallback]);
    }
}
