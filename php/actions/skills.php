<?php
require_once __DIR__ . '/../includes/db.php';

function cg_get_skills_list(): void {
    $p    = cg_prefix();
    $rows = cg_query(
        "SELECT id, ct_skill_name AS name
         FROM {$p}customtables_table_skills ORDER BY ct_skill_name ASC"
    );
    cg_json(['success' => true, 'data' => $rows]);
}

function cg_get_skill_detail(): void {
    $skillId = (int) ($_POST['id'] ?? 0);
    if ($skillId <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid skill ID.']);
        return;
    }

    $p   = cg_prefix();
    $row = cg_query_one(
        "SELECT id, ct_skill_name AS name, ct_skill_description AS description
         FROM {$p}customtables_table_skills WHERE id = ?",
        [$skillId]
    );

    if (!$row) {
        cg_json(['success' => false, 'data' => 'Skill not found.']);
        return;
    }
    cg_json(['success' => true, 'data' => $row]);
}
