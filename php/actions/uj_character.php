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
          personality_word VARCHAR(100) NOT NULL DEFAULT '',
          notes        TEXT         DEFAULT NULL,
          created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function uj_load_characters(): void {
    cg_session_start();
    if (!cg_is_logged_in()) {
        cg_json(['success' => false, 'data' => 'Not logged in.']);
        return;
    }
    $uid = cg_current_user_id();
    $p   = cg_prefix();
    uj_ensure_characters_table();

    $rows = cg_query(
        "SELECT id, name, species_id, type_id, career_id,
                body_die, speed_die, mind_die, will_die,
                personality_word, created_at, updated_at
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

    $fields = [
        'name'             => substr((string) ($data['name']             ?? ''), 0, 100),
        'species_id'       => ($data['species_id'] !== null && $data['species_id'] !== '') ? (int) $data['species_id'] : null,
        'type_id'          => ($data['type_id']    !== null && $data['type_id']    !== '') ? (int) $data['type_id']    : null,
        'career_id'        => ($data['career_id']  !== null && $data['career_id']  !== '') ? (int) $data['career_id']  : null,
        'body_die'         => substr((string) ($data['body_die']         ?? ''), 0, 4),
        'speed_die'        => substr((string) ($data['speed_die']        ?? ''), 0, 4),
        'mind_die'         => substr((string) ($data['mind_die']         ?? ''), 0, 4),
        'will_die'         => substr((string) ($data['will_die']         ?? ''), 0, 4),
        'personality_word' => substr((string) ($data['personality_word'] ?? ''), 0, 100),
        'notes'            => (string) ($data['notes'] ?? ''),
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
