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
    foreach (['ally_species_id INT DEFAULT NULL', 'ally_career_id INT DEFAULT NULL', 'gift_choices TEXT DEFAULT NULL'] as $colDef) {
        $colName = explode(' ', $colDef)[0];
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
                ally_species_id, ally_career_id, gift_choices,
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

    $rawAllySpecies = $data['ally_species_id'] ?? null;
    $rawAllyCareer  = $data['ally_career_id']  ?? null;
    $rawGiftChoices = $data['gift_choices']    ?? null;
    if (is_array($rawGiftChoices)) {
        $rawGiftChoices = json_encode($rawGiftChoices);
    } elseif (!is_string($rawGiftChoices)) {
        $rawGiftChoices = null;
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
