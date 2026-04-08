<?php
require_once __DIR__ . '/../includes/db.php';

function cg_get_career_list(): void {
    $p  = cg_prefix();
    $c  = "{$p}customtables_table_careers";
    $cg = "{$p}customtables_table_career_gifts";
    $cs = "{$p}customtables_table_career_skills";

    $rows = cg_query("
        SELECT
          c.ct_id          AS id,
          c.ct_career_name AS name,
          MAX(CASE WHEN cg.sort = 1 THEN cg.gift_id END) AS gift_id_1,
          MAX(CASE WHEN cg.sort = 2 THEN cg.gift_id END) AS gift_id_2,
          MAX(CASE WHEN cg.sort = 3 THEN cg.gift_id END) AS gift_id_3,
          MAX(CASE WHEN cs.sort = 1 THEN cs.skill_id END) AS skill_one,
          MAX(CASE WHEN cs.sort = 2 THEN cs.skill_id END) AS skill_two,
          MAX(CASE WHEN cs.sort = 3 THEN cs.skill_id END) AS skill_three
        FROM {$c} c
        LEFT JOIN {$cg} cg ON c.ct_id = cg.career_id
        LEFT JOIN {$cs} cs ON c.ct_id = cs.career_id
        GROUP BY c.ct_id, c.ct_career_name
        ORDER BY c.ct_career_name ASC
    ");
    cg_json(['success' => true, 'data' => $rows]);
}

function cg_get_career_gifts(): void {
    $careerId = (int) ($_POST['id'] ?? 0);
    if ($careerId <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid career ID.']);
        return;
    }

    $p  = cg_prefix();
    $c  = "{$p}customtables_table_careers";
    $cg = "{$p}customtables_table_career_gifts";
    $cs = "{$p}customtables_table_career_skills";
    $g  = "{$p}customtables_table_gifts";
    $sk = "{$p}customtables_table_skills";

    $row = cg_query_one("
        SELECT
          c.ct_career_name AS careerName,
          MAX(CASE WHEN cg.sort = 1 THEN cg.gift_id           END) AS gift_id_1,
          MAX(CASE WHEN cg.sort = 1 THEN gf.ct_gifts_name     END) AS gift_1,
          MAX(CASE WHEN cg.sort = 1 THEN gf.ct_gifts_manifold END) AS manifold_1,
          MAX(CASE WHEN cg.sort = 2 THEN cg.gift_id           END) AS gift_id_2,
          MAX(CASE WHEN cg.sort = 2 THEN gf.ct_gifts_name     END) AS gift_2,
          MAX(CASE WHEN cg.sort = 2 THEN gf.ct_gifts_manifold END) AS manifold_2,
          MAX(CASE WHEN cg.sort = 3 THEN cg.gift_id           END) AS gift_id_3,
          MAX(CASE WHEN cg.sort = 3 THEN gf.ct_gifts_name     END) AS gift_3,
          MAX(CASE WHEN cg.sort = 3 THEN gf.ct_gifts_manifold END) AS manifold_3,
          MAX(CASE WHEN cs.sort = 1 THEN cs.skill_id          END) AS skill_one,
          MAX(CASE WHEN cs.sort = 2 THEN cs.skill_id          END) AS skill_two,
          MAX(CASE WHEN cs.sort = 3 THEN cs.skill_id          END) AS skill_three,
          MAX(CASE WHEN cs.sort = 1 THEN sk.ct_skill_name     END) AS skill_name_one,
          MAX(CASE WHEN cs.sort = 2 THEN sk.ct_skill_name     END) AS skill_name_two,
          MAX(CASE WHEN cs.sort = 3 THEN sk.ct_skill_name     END) AS skill_name_three
        FROM {$c} c
        LEFT JOIN {$cg} cg ON c.ct_id = cg.career_id
        LEFT JOIN {$g}  gf ON cg.gift_id = gf.ct_id
        LEFT JOIN {$cs} cs ON c.ct_id = cs.career_id
        LEFT JOIN {$sk} sk ON cs.skill_id = sk.id
        WHERE c.ct_id = ?
        GROUP BY c.ct_id, c.ct_career_name
    ", [$careerId]);

    if (!$row) {
        cg_json(['success' => false, 'data' => 'Career not found.']);
        return;
    }
    cg_json(['success' => true, 'data' => $row]);
}
