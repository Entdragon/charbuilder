<?php
require_once __DIR__ . '/../includes/db.php';

function cg_normalize_character(array $row): array {
    $row['id']         = (string) ($row['id']         ?? '');
    $row['species_id'] = (string) ($row['species']     ?? $row['species_id'] ?? 0);
    $row['career_id']  = (string) ($row['career']      ?? $row['career_id']  ?? 0);

    if (!empty($row['extra_career_1'])) $row['extra_career_1'] = (string) $row['extra_career_1'];
    if (!empty($row['extra_career_2'])) $row['extra_career_2'] = (string) $row['extra_career_2'];

    // retrain_log decodes to an array
    if (isset($row['retrain_log']) && $row['retrain_log'] !== '') {
        $decoded = json_decode($row['retrain_log'], true);
        $row['retrain_log'] = is_array($decoded) ? $decoded : [];
    } else {
        $row['retrain_log'] = [];
    }
    $row['retrainLog']     = $row['retrain_log'];
    $row['retrainPenalty'] = (int) ($row['retrain_penalty'] ?? 0);

    // JSON fields that decode to objects (key→value maps)
    foreach (['skill_marks', 'career_gift_replacements', 'xp_skill_marks',
              'skill_notes', 'gift_skill_marks', 'free_gift_quals', 'xp_gift_quals', 'money_holdings'] as $field) {
        if (isset($row[$field]) && $row[$field] !== '') {
            $decoded = json_decode($row[$field], true);
            $row[$field] = is_array($decoded) ? $decoded : [];
        } else {
            $row[$field] = [];
        }
    }
    $row['xpSkillMarks']     = $row['xp_skill_marks'];
    $row['skillMarks']       = $row['skill_marks'];
    $row['personality_trait'] = (string) ($row['personality_trait'] ?? '');

    // JSON fields that decode to arrays
    foreach (['xp_gifts', 'weapons', 'armor', 'trappings_list'] as $field) {
        if (isset($row[$field]) && $row[$field] !== '') {
            $decoded = json_decode($row[$field], true);
            $row[$field] = is_array($decoded) ? $decoded : [];
        } else {
            $row[$field] = [];
        }
    }
    $row['xpGifts']     = $row['xp_gifts'];
    $row['xpGiftQuals'] = $row['xp_gift_quals'];

    $row['experience_points'] = (int) ($row['experience_points'] ?? 0);
    $row['xpMarksBudget']     = (int) ($row['xp_marks_budget']   ?? 0);
    $row['xpGiftSlots']       = (int) ($row['xp_gift_slots']     ?? 0);

    // Money flat fields
    $row['money_liras']     = (string) ($row['money_liras']     ?? '');
    $row['money_denarii']   = (string) ($row['money_denarii']   ?? '');
    $row['money_farthings'] = (string) ($row['money_farthings'] ?? '');

    // Synthesize free_gifts array from flat DB columns so JS can read it directly
    $g1 = (int) ($row['free_gift_1'] ?? 0);
    $g2 = (int) ($row['free_gift_2'] ?? 0);
    $g3 = (int) ($row['free_gift_3'] ?? 0);
    $row['free_gifts']  = [$g1 ?: '', $g2 ?: '', $g3 ?: ''];
    $row['freeGifts']   = $row['free_gifts'];

    return $row;
}

function cg_load_characters(): void {
    $p   = cg_prefix();
    $uid = cg_current_user_id();

    $rows = cg_query(
        "SELECT id, name, player_name, age, gender, species AS species_id, career AS career_id
         FROM {$p}character_records WHERE user_id = ? ORDER BY updated DESC",
        [$uid]
    );

    $normalized = array_map(fn($r) => array_merge($r, [
        'id'         => (string) $r['id'],
        'species_id' => (string) ($r['species_id'] ?? 0),
        'career_id'  => (string) ($r['career_id']  ?? 0),
    ]), $rows);

    cg_json(['success' => true, 'data' => $normalized]);
}

function cg_get_character(): void {
    $charId = (int) ($_POST['id'] ?? 0);
    if ($charId <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid character ID.']);
        return;
    }

    $p   = cg_prefix();
    $uid = cg_current_user_id();
    cg_ensure_battle_columns();
    cg_ensure_profile_columns();
    cg_ensure_xp_columns();

    $row = cg_query_one(
        "SELECT * FROM {$p}character_records WHERE id = ? AND user_id = ? LIMIT 1",
        [$charId, $uid]
    );

    if (!$row) {
        cg_json(['success' => false, 'data' => 'Character not found.']);
        return;
    }

    cg_json(['success' => true, 'data' => cg_normalize_character($row)]);
}

function cg_save_character(): void {
    $data = $_POST['character'] ?? [];
    if (is_string($data)) {
        $data = json_decode($data, true) ?? [];
    }

    $p   = cg_prefix();
    $uid = cg_current_user_id();
    $id  = (int) ($data['id'] ?? 0);

    cg_ensure_battle_columns();
    cg_ensure_profile_columns();
    cg_ensure_xp_columns();

    // skill_marks may arrive as a JSON string (character[skill_marks]) or a proper nested
    // PHP array (character[skillMarks]).  Prefer the array form; decode string as fallback.
    $rawMarks   = $data['skillMarks'] ?? $data['skill_marks'] ?? [];
    if (is_string($rawMarks)) { $rawMarks = json_decode($rawMarks, true) ?? []; }
    $skillMarks = is_array($rawMarks) ? $rawMarks : [];
    $giftReplacements = $data['career_gift_replacements'] ?? [];
    $freeGifts        = $data['free_gifts'] ?? [$data['free_gift_1'] ?? 0, $data['free_gift_2'] ?? 0, $data['free_gift_3'] ?? 0];

    $xpGifts     = $data['xp_gifts']   ?? $data['xpGifts']     ?? [];
    $xpSkillMrks = $data['xp_skill_marks'] ?? $data['xpSkillMarks'] ?? [];
    $weapons     = $data['weapons'] ?? [];
    $armor       = $data['armor']   ?? [];

    $fields = [
        'name'                          => substr((string) ($data['name']        ?? ''), 0, 100),
        'player_name'                   => substr((string) ($data['player_name'] ?? ''), 0, 255),
        'age'                           => substr((string) ($data['age']         ?? ''), 0, 10),
        'gender'                        => substr((string) ($data['gender']      ?? ''), 0, 20),
        'will'                          => substr((string) ($data['will']        ?? ''), 0, 4),
        'speed'                         => substr((string) ($data['speed']       ?? ''), 0, 4),
        'body'                          => substr((string) ($data['body']        ?? ''), 0, 4),
        'mind'                          => substr((string) ($data['mind']        ?? ''), 0, 4),
        'species'                       => (int)  ($data['species_id'] ?? $data['species'] ?? 0),
        'career'                        => (int)  ($data['career_id']  ?? $data['career']  ?? 0),
        'free_gift_1'                   => (int)  ($freeGifts[0] ?? 0),
        'free_gift_2'                   => (int)  ($freeGifts[1] ?? 0),
        'free_gift_3'                   => (int)  ($freeGifts[2] ?? 0),
        'career_gift_replacements'      => json_encode(is_array($giftReplacements) ? $giftReplacements : []),
        'local_area'                    => (string) ($data['local_area']  ?? ''),
        'personality_trait'             => (string) ($data['personality_trait'] ?? ''),
        'language'                      => (string) ($data['language']    ?? ''),
        'skill_marks'                   => json_encode(is_array($skillMarks) ? $skillMarks : []),
        'description'                   => (string) ($data['description'] ?? ''),
        'backstory'                     => (string) ($data['backstory']   ?? ''),
        'motto'                         => (string) ($data['motto']       ?? ''),
        'goal1'                         => (string) ($data['goal1']       ?? ''),
        'goal2'                         => (string) ($data['goal2']       ?? ''),
        'goal3'                         => (string) ($data['goal3']       ?? ''),
        'extra_career_1'                => ($data['extra_career_1'] !== null && $data['extra_career_1'] !== '')
                                            ? (int) $data['extra_career_1'] : null,
        'extra_trait_career_1'          => $data['extra_trait_career_1'] ?? null,
        'extra_career_2'                => ($data['extra_career_2'] !== null && $data['extra_career_2'] !== '')
                                            ? (int) $data['extra_career_2'] : null,
        'extra_trait_career_2'          => $data['extra_trait_career_2'] ?? null,
        'trait_species'                 => substr((string) ($data['trait_species'] ?? ''), 0, 10),
        'trait_career'                  => substr((string) ($data['trait_career']  ?? ''), 0, 10),
        'increased_trait_career_target' => $data['increased_trait_career_target'] ?? null,
        'experience_points'             => (int) ($data['experience_points'] ?? 0),
        'xp_marks_budget'               => (int) ($data['xp_marks_budget']   ?? $data['xpMarksBudget'] ?? 0),
        'xp_gift_slots'                 => (int) ($data['xp_gift_slots']     ?? $data['xpGiftSlots']   ?? 0),
        'xp_skill_marks'                => json_encode(is_array($xpSkillMrks) ? $xpSkillMrks : [], JSON_FORCE_OBJECT),
        'xp_gifts'                      => json_encode(is_array($xpGifts)     ? $xpGifts     : []),
        'weapons'                       => json_encode(is_array($weapons)     ? $weapons      : []),
        'armor'                         => json_encode(is_array($armor)       ? $armor        : []),

        // Trappings & money
        'trappings_list'                => json_encode(is_array($data['trappings_list'] ?? null) ? $data['trappings_list'] : []),
        'money_holdings'                => json_encode(is_array($data['money_holdings'] ?? null) ? $data['money_holdings'] : (is_array($data['moneyHoldings'] ?? null) ? $data['moneyHoldings'] : [])),
        'money_liras'                   => substr((string) ($data['money_liras']     ?? ''), 0, 20),
        'money_denarii'                 => substr((string) ($data['money_denarii']   ?? ''), 0, 20),
        'money_farthings'               => substr((string) ($data['money_farthings'] ?? ''), 0, 20),

        // Per-skill notes and gift-granted marks
        'skill_notes'                   => json_encode(is_array($data['skill_notes']       ?? null) ? $data['skill_notes']       : (is_object($data['skill_notes'] ?? null) ? (array) $data['skill_notes'] : []), JSON_FORCE_OBJECT),
        'gift_skill_marks'              => json_encode(is_array($data['gift_skill_marks']   ?? null) ? $data['gift_skill_marks']   : []),

        // Free-gift qualification data (language/mystic/knack per slot)
        'free_gift_quals'               => json_encode(is_array($data['free_gift_quals']    ?? null) ? $data['free_gift_quals']    : []),

        // XP-gift qualification data (language/literacy chosen for each XP gift slot)
        'xp_gift_quals'                 => json_encode(is_array($data['xp_gift_quals']      ?? null) ? $data['xp_gift_quals']      : []),

        // Retraining
        'retrain_penalty'               => (int) ($data['retrain_penalty'] ?? $data['retrainPenalty'] ?? 0),
        'retrain_log'                   => json_encode(is_array($data['retrain_log'] ?? null) ? $data['retrain_log'] : (is_array($data['retrainLog'] ?? null) ? $data['retrainLog'] : [])),

        'updated'                       => date('Y-m-d H:i:s'),
    ];

    if ($id > 0) {
        $exists = cg_query_one(
            "SELECT id FROM {$p}character_records WHERE id = ? AND user_id = ? LIMIT 1",
            [$id, $uid]
        );
        if (!$exists) {
            cg_json(['success' => false, 'data' => 'Character not found.']);
            return;
        }

        $setClauses = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($fields)));
        cg_exec(
            "UPDATE {$p}character_records SET {$setClauses} WHERE id = ? AND user_id = ?",
            [...array_values($fields), $id, $uid]
        );
        cg_json(['success' => true, 'data' => ['id' => (string) $id]]);
    } else {
        $fields['user_id'] = $uid;
        $fields['created'] = date('Y-m-d H:i:s');

        $cols     = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($fields)));
        $holders  = implode(', ', array_fill(0, count($fields), '?'));
        $insertResult = cg_exec(
            "INSERT INTO {$p}character_records ({$cols}) VALUES ({$holders})",
            array_values($fields)
        );
        cg_json(['success' => true, 'data' => ['id' => $insertResult['lastInsertId']]]);
    }
}
