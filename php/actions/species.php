<?php
require_once __DIR__ . '/../includes/db.php';

function cg_get_species_list(): void {
    $p    = cg_prefix();
    $rows = cg_query(
        "SELECT ct_id AS id, ct_species_name AS name
         FROM {$p}customtables_table_species ORDER BY ct_species_name ASC"
    );
    cg_json(['success' => true, 'data' => $rows]);
}

function cg_get_species_profile(): void {
    $speciesId = (int) ($_POST['id'] ?? 0);
    if ($speciesId <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid species ID.']);
        return;
    }

    $p  = cg_prefix();
    $s  = "{$p}customtables_table_species";
    $g  = "{$p}customtables_table_gifts";
    $h  = "{$p}customtables_table_habitat";
    $d  = "{$p}customtables_table_diet";
    $c  = "{$p}customtables_table_cycle";
    $sn = "{$p}customtables_table_senses";
    $w  = "{$p}customtables_table_weapons";

    $row = cg_query_one("
        SELECT
          sp.ct_species_name        AS speciesName,

          sp.ct_species_gift_one    AS gift_id_1,
          g1.ct_gifts_name          AS gift_1,
          g1.ct_gifts_manifold      AS manifold_1,

          sp.ct_species_gift_two    AS gift_id_2,
          g2.ct_gifts_name          AS gift_2,
          g2.ct_gifts_manifold      AS manifold_2,

          sp.ct_species_gift_three  AS gift_id_3,
          g3.ct_gifts_name          AS gift_3,
          g3.ct_gifts_manifold      AS manifold_3,

          sp.ct_species_skill_one   AS skill_one,
          sp.ct_species_skill_two   AS skill_two,
          sp.ct_species_skill_three AS skill_three,

          hb.ct_habitat_name        AS habitat,
          dt.ct_diet_name           AS diet,
          cy.ct_cycle_name          AS cycle,

          s1.ct_senses_name         AS sense_1,
          s2.ct_senses_name         AS sense_2,
          s3.ct_senses_name         AS sense_3,

          w1.ct_weapons_name        AS weapon_1,
          w2.ct_weapons_name        AS weapon_2,
          w3.ct_weapons_name        AS weapon_3

        FROM {$s} sp
        LEFT JOIN {$g}  g1 ON sp.ct_species_gift_one    = g1.ct_id
        LEFT JOIN {$g}  g2 ON sp.ct_species_gift_two    = g2.ct_id
        LEFT JOIN {$g}  g3 ON sp.ct_species_gift_three  = g3.ct_id
        LEFT JOIN {$h}  hb ON sp.ct_species_habitat     = hb.ct_id
        LEFT JOIN {$d}  dt ON sp.ct_species_diet        = dt.ct_id
        LEFT JOIN {$c}  cy ON sp.ct_species_cycle       = cy.ct_id
        LEFT JOIN {$sn} s1 ON sp.ct_species_senses_one  = s1.ct_id
        LEFT JOIN {$sn} s2 ON sp.ct_species_senses_two  = s2.ct_id
        LEFT JOIN {$sn} s3 ON sp.ct_species_senses_three= s3.ct_id
        LEFT JOIN {$w}  w1 ON sp.ct_species_weapon_one  = w1.ct_id
        LEFT JOIN {$w}  w2 ON sp.ct_species_weapon_two  = w2.ct_id
        LEFT JOIN {$w}  w3 ON sp.ct_species_weapon_three= w3.ct_id
        WHERE sp.ct_id = ?
    ", [$speciesId]);

    if (!$row) {
        cg_json(['success' => false, 'data' => 'Species not found.']);
        return;
    }
    cg_json(['success' => true, 'data' => $row]);
}
