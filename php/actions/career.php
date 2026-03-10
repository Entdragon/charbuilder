<?php
require_once __DIR__ . '/../includes/db.php';

function cg_get_career_list(): void {
    $p    = cg_prefix();
    $rows = cg_query("
        SELECT
          ct_id                 AS id,
          ct_career_name        AS name,
          ct_career_gift_one    AS gift_id_1,
          ct_career_gift_two    AS gift_id_2,
          ct_career_gift_three  AS gift_id_3,
          ct_career_skill_one   AS skill_one,
          ct_career_skill_two   AS skill_two,
          ct_career_skill_three AS skill_three
        FROM {$p}customtables_table_careers
        ORDER BY ct_career_name ASC
    ");
    cg_json(['success' => true, 'data' => $rows]);
}

function cg_get_career_gifts(): void {
    $careerId = (int) ($_POST['id'] ?? 0);
    if ($careerId <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid career ID.']);
        return;
    }

    $p = cg_prefix();
    $c = "{$p}customtables_table_careers";
    $g = "{$p}customtables_table_gifts";

    $row = cg_query_one("
        SELECT
          c.ct_career_name        AS careerName,
          c.ct_career_gift_one    AS gift_id_1,
          g1.ct_gifts_name        AS gift_1,
          g1.ct_gifts_manifold    AS manifold_1,
          c.ct_career_gift_two    AS gift_id_2,
          g2.ct_gifts_name        AS gift_2,
          g2.ct_gifts_manifold    AS manifold_2,
          c.ct_career_gift_three  AS gift_id_3,
          g3.ct_gifts_name        AS gift_3,
          g3.ct_gifts_manifold    AS manifold_3,
          c.ct_career_skill_one   AS skill_one,
          c.ct_career_skill_two   AS skill_two,
          c.ct_career_skill_three AS skill_three
        FROM {$c} c
        LEFT JOIN {$g} g1 ON c.ct_career_gift_one   = g1.ct_id
        LEFT JOIN {$g} g2 ON c.ct_career_gift_two   = g2.ct_id
        LEFT JOIN {$g} g3 ON c.ct_career_gift_three = g3.ct_id
        WHERE c.ct_id = ?
    ", [$careerId]);

    if (!$row) {
        cg_json(['success' => false, 'data' => 'Career not found.']);
        return;
    }
    cg_json(['success' => true, 'data' => $row]);
}
