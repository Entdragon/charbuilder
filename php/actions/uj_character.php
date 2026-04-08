<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

function uj_ensure_characters_table(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    $p = cg_prefix();
    cg_exec("
        CREATE TABLE IF NOT EXISTS `{$p}uj_character_records` (
          id           INT AUTO_INCREMENT PRIMARY KEY,
          user_id      INT          NOT NULL,
          name         VARCHAR(100) NOT NULL DEFAULT '',
          species_id   INT          DEFAULT NULL,
          type_id      INT          DEFAULT NULL,
          career_id    INT          DEFAULT NULL,
          body_die     VARCHAR(4)   NOT NULL DEFAULT '',
          speed_die    VARCHAR(4)   NOT NULL DEFAULT '',
          mind_die     VARCHAR(4)   NOT NULL DEFAULT '',
          will_die     VARCHAR(4)   NOT NULL DEFAULT '',
          species_die  VARCHAR(4)   NOT NULL DEFAULT '',
          type_die     VARCHAR(4)   NOT NULL DEFAULT '',
          career_die   VARCHAR(4)   NOT NULL DEFAULT '',
          personality_word VARCHAR(100) NOT NULL DEFAULT '',
          notes        TEXT         DEFAULT NULL,
          created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    foreach (['species_die','type_die','career_die'] as $col) {
        try {
            cg_exec("ALTER TABLE `{$p}uj_character_records`
                     ADD COLUMN `{$col}` VARCHAR(4) NOT NULL DEFAULT ''");
        } catch (Throwable $e) {}
    }
    foreach ([
        'ally_species_id INT DEFAULT NULL',
        'ally_career_id INT DEFAULT NULL',
        'ally_name VARCHAR(191) DEFAULT NULL',
        'ally_gender VARCHAR(64) DEFAULT NULL',
        'ally_body_die VARCHAR(4) DEFAULT NULL',
        'ally_speed_die VARCHAR(4) DEFAULT NULL',
        'ally_mind_die VARCHAR(4) DEFAULT NULL',
        'ally_will_die VARCHAR(4) DEFAULT NULL',
        'gift_choices TEXT DEFAULT NULL',
        'experience INT NOT NULL DEFAULT 0',
        'purchased_gifts TEXT DEFAULT NULL',
        'extra_career_id INT DEFAULT NULL',
        'extra_type_id INT DEFAULT NULL',
        'extra_career_die VARCHAR(4) DEFAULT NULL',
        'extra_type_die VARCHAR(4) DEFAULT NULL',
    ] as $colDef) {
        try {
            cg_exec("ALTER TABLE `{$p}uj_character_records` ADD COLUMN {$colDef}");
        } catch (Throwable $e) {}
    }
}

function uj_load_characters(): void {
    cg_session_start();
    if (!cg_is_logged_in()) {
        cg_json(['success' => false, 'data' => 'Not logged in.']);
        return;
    }
    $uid = cg_current_user_id();
    $p   = cg_prefix();
    try { uj_ensure_characters_table(); } catch (Throwable $e) {
        error_log('[UJ] uj_ensure_characters_table failed: ' . $e->getMessage());
        cg_json(['success' => true, 'data' => []]);
        return;
    }

    $rows = cg_query(
        "SELECT id, name, species_id, type_id, career_id,
                body_die, speed_die, mind_die, will_die,
                species_die, type_die, career_die,
                personality_word, notes,
                ally_species_id, ally_career_id, ally_name, ally_gender,
                ally_body_die, ally_speed_die, ally_mind_die, ally_will_die, gift_choices,
                experience, purchased_gifts,
                extra_career_id, extra_type_id, extra_career_die, extra_type_die,
                created_at, updated_at
           FROM `{$p}uj_character_records`
          WHERE user_id = ?
          ORDER BY updated_at DESC",
        [$uid]
    );
    foreach ($rows as &$r) { $r['id'] = (string) $r['id']; }
    cg_json(['success' => true, 'data' => $rows]);
}

function uj_get_character(): void {
    cg_session_start();
    if (!cg_is_logged_in()) {
        cg_json(['success' => false, 'data' => 'Not logged in.']);
        return;
    }
    $uid    = cg_current_user_id();
    $charId = (int) ($_POST['id'] ?? 0);
    if ($charId <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid ID.']);
        return;
    }
    $p = cg_prefix();
    uj_ensure_characters_table();

    $row = cg_query_one(
        "SELECT * FROM `{$p}uj_character_records` WHERE id = ? AND user_id = ? LIMIT 1",
        [$charId, $uid]
    );
    if (!$row) {
        cg_json(['success' => false, 'data' => 'Character not found.']);
        return;
    }
    $row['id'] = (string) $row['id'];
    cg_json(['success' => true, 'data' => $row]);
}

function uj_save_character(): void {
    cg_session_start();
    if (!cg_is_logged_in()) {
        cg_json(['success' => false, 'data' => 'Not logged in.']);
        return;
    }
    $uid  = cg_current_user_id();
    $data = $_POST['character'] ?? [];
    if (is_string($data)) {
        $data = json_decode($data, true) ?? [];
    }
    $p  = cg_prefix();
    $id = (int) ($data['id'] ?? 0);
    uj_ensure_characters_table();

    static $ALLOWED_DICE = ['d4', 'd6', 'd8', 'd10', 'd12'];

    $rawSpecies  = $data['species_id'] ?? null;
    $rawType     = $data['type_id']    ?? null;
    $rawCareer   = $data['career_id']  ?? null;

    $bodyDie    = in_array($data['body_die']    ?? '', $ALLOWED_DICE, true) ? $data['body_die']    : '';
    $speedDie   = in_array($data['speed_die']   ?? '', $ALLOWED_DICE, true) ? $data['speed_die']   : '';
    $mindDie    = in_array($data['mind_die']    ?? '', $ALLOWED_DICE, true) ? $data['mind_die']    : '';
    $willDie    = in_array($data['will_die']    ?? '', $ALLOWED_DICE, true) ? $data['will_die']    : '';
    $speciesDie = in_array($data['species_die'] ?? '', $ALLOWED_DICE, true) ? $data['species_die'] : '';
    $typeDie    = in_array($data['type_die']    ?? '', $ALLOWED_DICE, true) ? $data['type_die']    : '';
    $careerDie  = in_array($data['career_die']  ?? '', $ALLOWED_DICE, true) ? $data['career_die']  : '';

    $rawAllySpecies    = $data['ally_species_id'] ?? null;
    $rawAllyCareer     = $data['ally_career_id']  ?? null;
    $rawGiftChoices    = $data['gift_choices']     ?? null;
    $rawExperience     = $data['experience']       ?? null;
    $rawPurchasedGifts = $data['purchased_gifts']  ?? null;

    if (is_array($rawGiftChoices)) {
        $rawGiftChoices = json_encode($rawGiftChoices);
    } elseif (!is_string($rawGiftChoices)) {
        $rawGiftChoices = null;
    }
    if (is_array($rawPurchasedGifts)) {
        $rawPurchasedGifts = json_encode($rawPurchasedGifts);
    } elseif (!is_string($rawPurchasedGifts)) {
        $rawPurchasedGifts = null;
    }

    $fields = [
        'name'             => substr((string) ($data['name']             ?? ''), 0, 100),
        'species_id'       => ($rawSpecies !== null && $rawSpecies !== '') ? (int) $rawSpecies : null,
        'type_id'          => ($rawType    !== null && $rawType    !== '') ? (int) $rawType    : null,
        'career_id'        => ($rawCareer  !== null && $rawCareer  !== '') ? (int) $rawCareer  : null,
        'body_die'         => $bodyDie,
        'speed_die'        => $speedDie,
        'mind_die'         => $mindDie,
        'will_die'         => $willDie,
        'species_die'      => $speciesDie,
        'type_die'         => $typeDie,
        'career_die'       => $careerDie,
        'personality_word' => substr((string) ($data['personality_word'] ?? ''), 0, 100),
        'notes'            => (string) ($data['notes'] ?? ''),
        'ally_species_id'  => ($rawAllySpecies !== null && $rawAllySpecies !== '') ? (int) $rawAllySpecies : null,
        'ally_career_id'   => ($rawAllyCareer  !== null && $rawAllyCareer  !== '') ? (int) $rawAllyCareer  : null,
        'gift_choices'     => $rawGiftChoices,
        'experience'       => ($rawExperience !== null) ? max(0, (int) $rawExperience) : 0,
        'purchased_gifts'  => $rawPurchasedGifts,
        'updated_at'       => date('Y-m-d H:i:s'),
    ];

    if ($id > 0) {
        $exists = cg_query_one(
            "SELECT id FROM `{$p}uj_character_records` WHERE id = ? AND user_id = ? LIMIT 1",
            [$id, $uid]
        );
        if (!$exists) {
            cg_json(['success' => false, 'data' => 'Character not found.']);
            return;
        }
        $setClauses = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($fields)));
        cg_exec(
            "UPDATE `{$p}uj_character_records` SET {$setClauses} WHERE id = ? AND user_id = ?",
            [...array_values($fields), $id, $uid]
        );
        cg_json(['success' => true, 'data' => ['id' => (string) $id]]);
    } else {
        $fields['user_id']    = $uid;
        $fields['created_at'] = date('Y-m-d H:i:s');
        $cols    = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($fields)));
        $holders = implode(', ', array_fill(0, count($fields), '?'));
        $result  = cg_exec(
            "INSERT INTO `{$p}uj_character_records` ({$cols}) VALUES ({$holders})",
            array_values($fields)
        );
        cg_json(['success' => true, 'data' => ['id' => (string) $result['lastInsertId']]]);
    }
}

function uj_update_development(): void {
    cg_session_start();
    if (!cg_is_logged_in()) {
        cg_json(['success' => false, 'data' => 'Not logged in.']);
        return;
    }
    $uid    = cg_current_user_id();
    $id     = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid ID.']);
        return;
    }
    $p = cg_prefix();
    uj_ensure_characters_table();
    $exists = cg_query_one(
        "SELECT id FROM `{$p}uj_character_records` WHERE id = ? AND user_id = ? LIMIT 1",
        [$id, $uid]
    );
    if (!$exists) {
        cg_json(['success' => false, 'data' => 'Character not found.']);
        return;
    }
    $experience     = max(0, (int) ($_POST['experience'] ?? 0));
    $purchasedGifts = $_POST['purchased_gifts'] ?? null;
    if (!is_string($purchasedGifts)) {
        $purchasedGifts = '[]';
    }
    $rawAllySpecies = $_POST['ally_species_id'] ?? null;
    $rawAllyCareer  = $_POST['ally_career_id']  ?? null;
    $allySpeciesId  = ($rawAllySpecies !== null && $rawAllySpecies !== '') ? (int) $rawAllySpecies : null;
    $allyCareerId   = ($rawAllyCareer  !== null && $rawAllyCareer  !== '') ? (int) $rawAllyCareer  : null;
    $allyName       = isset($_POST['ally_name'])   ? trim(substr($_POST['ally_name'], 0, 191)) : null;
    $allyGender     = isset($_POST['ally_gender']) ? trim(substr($_POST['ally_gender'], 0, 64)) : null;
    $validDice      = ['d4', 'd6', 'd8', 'd10', 'd12'];
    $allyBodyDie    = in_array($_POST['ally_body_die']  ?? '', $validDice) ? $_POST['ally_body_die']  : 'd6';
    $allySpeedDie   = in_array($_POST['ally_speed_die'] ?? '', $validDice) ? $_POST['ally_speed_die'] : 'd6';
    $allyMindDie    = in_array($_POST['ally_mind_die']  ?? '', $validDice) ? $_POST['ally_mind_die']  : 'd6';
    $allyWillDie    = in_array($_POST['ally_will_die']  ?? '', $validDice) ? $_POST['ally_will_die']  : 'd6';
    $rawExtraCareer = $_POST['extra_career_id'] ?? null;
    $rawExtraType   = $_POST['extra_type_id']   ?? null;
    $extraCareerId  = ($rawExtraCareer !== null && $rawExtraCareer !== '') ? (int) $rawExtraCareer : null;
    $extraTypeId    = ($rawExtraType   !== null && $rawExtraType   !== '') ? (int) $rawExtraType   : null;
    $extraCareerDie = in_array($_POST['extra_career_die'] ?? '', $validDice) ? $_POST['extra_career_die'] : null;
    $extraTypeDie   = in_array($_POST['extra_type_die']   ?? '', $validDice) ? $_POST['extra_type_die']   : null;
    cg_exec(
        "UPDATE `{$p}uj_character_records` SET experience = ?, purchased_gifts = ?,
         ally_species_id = ?, ally_career_id = ?, ally_name = ?, ally_gender = ?,
         ally_body_die = ?, ally_speed_die = ?, ally_mind_die = ?, ally_will_die = ?,
         extra_career_id = ?, extra_type_id = ?, extra_career_die = ?, extra_type_die = ?,
         updated_at = ? WHERE id = ? AND user_id = ?",
        [$experience, $purchasedGifts, $allySpeciesId, $allyCareerId,
         $allyName ?: null, $allyGender ?: null,
         $allyBodyDie, $allySpeedDie, $allyMindDie, $allyWillDie,
         $extraCareerId, $extraTypeId, $extraCareerDie ?: null, $extraTypeDie ?: null,
         date('Y-m-d H:i:s'), $id, $uid]
    );
    cg_json(['success' => true, 'data' => ['id' => (string) $id]]);
}

function uj_delete_character(): void {
    cg_session_start();
    if (!cg_is_logged_in()) {
        cg_json(['success' => false, 'data' => 'Not logged in.']);
        return;
    }
    $uid    = cg_current_user_id();
    $charId = (int) ($_POST['id'] ?? 0);
    if ($charId <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid ID.']);
        return;
    }
    $p = cg_prefix();
    uj_ensure_characters_table();
    cg_exec(
        "DELETE FROM `{$p}uj_character_records` WHERE id = ? AND user_id = ?",
        [$charId, $uid]
    );
    cg_json(['success' => true, 'data' => null]);
}
