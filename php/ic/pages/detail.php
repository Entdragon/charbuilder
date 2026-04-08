<?php
/**
 * Ironclaw detail page — handles species, careers, gifts, skills, weapons, equipment.
 * $entity and $slug are set by ic.php.
 */
declare(strict_types=1);

$p = cg_prefix();

// Helper function: get sorted column names we actually have
function ic_get_cols(string $table): array {
    static $cache = [];
    global $p;
    if (!isset($cache[$table])) {
        $cols = cg_query("SHOW COLUMNS FROM `{$p}customtables_table_{$table}`");
        $cache[$table] = array_column($cols, 'Field');
    }
    return $cache[$table];
}

function ic_col_exists(string $table, string $col): bool {
    return in_array($col, ic_get_cols($table));
}

$notFound = function () use ($entity) {
    global $p, $icCounts;
    $pageTitle = 'Not Found';
    $activeNav = $entity;
    require __DIR__ . '/../layout-head.php';
    echo '<div class="page-header"><h1 style="color:var(--ic-error);">Not Found</h1><p>That entry does not exist or has been removed.</p></div>';
    echo '<p><a href="/ic/' . htmlspecialchars($entity) . '">← Back to ' . htmlspecialchars(ucfirst($entity)) . '</a></p>';
    require __DIR__ . '/../layout-foot.php';
    exit;
};

// ── Load entity ─────────────────────────────────────────────────────────────
$row = null;

switch ($entity) {

    case 'species':
        $row = cg_query_one("
            SELECT sp.*, b.ct_book_name, b.ct_ct_slug AS book_slug
            FROM `{$p}customtables_table_species` sp
            LEFT JOIN `{$p}customtables_table_books` b ON b.id = sp.ct_species_source_book
            WHERE sp.ct_slug=? AND sp.published=1", [$slug]);
        if (!$row) $notFound();

        // Traits with gift + skill names joined
        $traits = cg_query("
            SELECT st.*, g.ct_gifts_name, g.ct_slug AS gift_slug,
                   sk.ct_skill_name
            FROM `{$p}customtables_table_species_traits` st
            LEFT JOIN `{$p}customtables_table_gifts`  g  ON g.id  = st.ref_id
            LEFT JOIN `{$p}customtables_table_skills` sk ON (st.trait_key LIKE 'skill%'
                                                             AND CAST(st.text_value AS UNSIGNED) = sk.id)
            WHERE st.species_id=?
            ORDER BY st.sort
        ", [(int)$row['id']]);

        // Gift map
        $giftIds = array_filter(array_unique(array_column($traits, 'ref_id')));
        $giftsMap = [];
        if ($giftIds) {
            $in = implode(',', array_map('intval', $giftIds));
            $gs = cg_query("SELECT id, ct_gifts_name, ct_slug FROM `{$p}customtables_table_gifts` WHERE id IN ($in)");
            foreach ($gs as $g) { $giftsMap[(int)$g['id']] = $g; }
        }

        $pageTitle = $row['ct_species_name'];
        $activeNav = 'species';
        require __DIR__ . '/../layout-head.php';
        ?>
        <div class="breadcrumb">
          <a href="/ic">Home</a> <span>›</span>
          <a href="/ic/species">Species</a> <span>›</span>
          <?= htmlspecialchars($row['ct_species_name']) ?>
        </div>

        <div class="detail-layout">
          <div>
            <p class="detail-name"><?= htmlspecialchars($row['ct_species_name']) ?></p>
            <?php if ($row['ct_species_description']): ?>
              <div class="detail-desc"><?= nl2br(htmlspecialchars(strip_tags($row['ct_species_description']))) ?></div>
            <?php endif; ?>
          </div>

          <aside class="detail-sidebar">
            <?php
            // Separate skills and gifts from traits
            $skillTraits = []; $giftTraits = [];
            foreach ($traits as $t) {
                if (str_starts_with($t['trait_key'], 'skill')) $skillTraits[] = $t;
                elseif (str_starts_with($t['trait_key'], 'gift')) $giftTraits[] = $t;
            }
            if ($skillTraits): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Skills</p>
                <ul class="trait-list">
                  <?php foreach ($skillTraits as $t):
                    $sName = $t['ct_skill_name'] ?? $t['text_value'] ?? '—'; ?>
                    <li><?= htmlspecialchars($sName) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if ($giftTraits): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Gifts</p>
                <ul class="trait-list">
                  <?php foreach ($giftTraits as $t):
                    $gId    = (int)($t['ref_id'] ?? 0);
                    $gName  = $t['ct_gifts_name'] ?? ($giftsMap[$gId]['ct_gifts_name'] ?? null);
                    $gSlug  = $t['gift_slug']     ?? ($giftsMap[$gId]['ct_slug']        ?? null);
                  ?>
                    <li class="gift-item">
                      <?php if ($gName && $gSlug): ?>
                        <a href="/ic/gifts/<?= htmlspecialchars($gSlug) ?>"><?= htmlspecialchars($gName) ?></a>
                      <?php elseif ($gName): ?>
                        <?= htmlspecialchars($gName) ?>
                      <?php else: ?>
                        <?= htmlspecialchars($t['text_value'] ?: '—') ?>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php
            // Gift choice columns
            $choices = array_filter([$row['ct_species_gift_one_choice'] ?? '', $row['ct_species_gift_two_choice'] ?? '', $row['ct_species_gift_three_choice'] ?? '']);
            if ($choices): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Gift Choices</p>
                <ul class="trait-list">
                  <?php foreach ($choices as $c): ?>
                    <li class="gift-item" style="font-size:0.85rem;"><?= htmlspecialchars($c) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if ($row['ct_book_name'] || $row['ct_pg_no']): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Source</p>
                <?php if ($row['ct_book_name']): ?>
                  <p style="font-size:0.9rem; margin:0 0 0.2rem;">
                    <a href="/ic/books/<?= htmlspecialchars($row['book_slug']) ?>"><?= htmlspecialchars($row['ct_book_name']) ?></a>
                  </p>
                <?php endif; ?>
                <?php if ($row['ct_pg_no']): ?>
                  <p style="font-size:0.85rem; color:var(--ic-text-dim); margin:0;">Page <?= htmlspecialchars((string)$row['ct_pg_no']) ?></p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </aside>
        </div>
        <?php
        break;

    case 'careers':
        $row = cg_query_one("
            SELECT c.*, b.ct_book_name, b.ct_ct_slug AS book_slug
            FROM `{$p}customtables_table_careers` c
            LEFT JOIN `{$p}customtables_table_books` b ON b.id = c.ct_career_source_book
            WHERE c.ct_slug=? AND c.published=1", [$slug]);
        if (!$row) $notFound();

        $typeMap = [1 => 'Major', 2 => 'Minor'];

        // Career skills
        $csRows = cg_query("SELECT skill_id FROM `{$p}customtables_table_career_skills` WHERE career_id=? ORDER BY sort", [(int)$row['id']]);
        $skillIds = array_column($csRows, 'skill_id');
        $cSkills  = [];
        if ($skillIds) {
            $in = implode(',', array_map('intval', $skillIds));
            $cSkills = cg_query("SELECT id, ct_skill_name, ct_slug FROM `{$p}customtables_table_skills` WHERE id IN ($in)");
        }

        // Career gifts
        $cgRows = cg_query("SELECT gift_id FROM `{$p}customtables_table_career_gifts` WHERE career_id=? ORDER BY sort LIMIT 20", [(int)$row['id']]);
        $giftIds = array_column($cgRows, 'gift_id');
        $cGifts  = [];
        if ($giftIds) {
            $in = implode(',', array_map('intval', $giftIds));
            $cGifts = cg_query("SELECT id, ct_gifts_name, ct_slug FROM `{$p}customtables_table_gifts` WHERE id IN ($in) AND published=1");
        }

        $pageTitle = $row['ct_career_name'];
        $activeNav = 'careers';
        require __DIR__ . '/../layout-head.php';
        ?>
        <div class="breadcrumb">
          <a href="/ic">Home</a> <span>›</span>
          <a href="/ic/careers">Careers</a> <span>›</span>
          <?= htmlspecialchars($row['ct_career_name']) ?>
        </div>

        <div class="detail-layout">
          <div>
            <p class="detail-name"><?= htmlspecialchars($row['ct_career_name']) ?></p>
            <?php if (isset($typeMap[(int)$row['ct_career_type']])): ?>
              <p class="detail-subtitle"><?= $typeMap[(int)$row['ct_career_type']] ?> Career</p>
            <?php endif; ?>

            <?php if ($row['ct_career_description']): ?>
              <div class="detail-desc"><?= nl2br(htmlspecialchars(strip_tags($row['ct_career_description']))) ?></div>
            <?php endif; ?>

            <?php if ($row['ct_career_trappings']): ?>
              <div class="detail-section">
                <p class="detail-section-title">Trappings</p>
                <p style="color:var(--ic-text-muted); font-size:1rem; line-height:1.6;"><?= nl2br(htmlspecialchars(strip_tags($row['ct_career_trappings']))) ?></p>
              </div>
            <?php endif; ?>

            <?php if ($row['ct_requires']): ?>
              <div class="detail-section">
                <p class="detail-section-title">Requirements</p>
                <p style="color:var(--ic-text-muted); font-size:0.95rem;"><?= htmlspecialchars(strip_tags($row['ct_requires'])) ?></p>
              </div>
            <?php endif; ?>
          </div>

          <aside class="detail-sidebar">
            <?php if ($cSkills): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Skills</p>
                <ul class="trait-list">
                  <?php foreach ($cSkills as $sk): ?>
                    <li><a href="/ic/skills/<?= htmlspecialchars($sk['ct_slug']) ?>"><?= htmlspecialchars($sk['ct_skill_name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if ($cGifts): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Career Gifts</p>
                <ul class="trait-list">
                  <?php foreach ($cGifts as $g): ?>
                    <li class="gift-item"><a href="/ic/gifts/<?= htmlspecialchars($g['ct_slug']) ?>"><?= htmlspecialchars($g['ct_gifts_name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php
            $choices = array_filter([$row['ct_career_gift_one_choice'] ?? '', $row['ct_career_gift_two_choice'] ?? '', $row['ct_career_gift_three_choice'] ?? '']);
            if ($choices): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Gift Choices</p>
                <ul class="trait-list">
                  <?php foreach ($choices as $c): ?>
                    <li class="gift-item" style="font-size:0.85rem;"><?= htmlspecialchars($c) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if ($row['ct_book_name'] || $row['ct_pg_no']): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Source</p>
                <?php if ($row['ct_book_name']): ?>
                  <p style="font-size:0.9rem; margin:0 0 0.2rem;">
                    <a href="/ic/books/<?= htmlspecialchars($row['book_slug']) ?>"><?= htmlspecialchars($row['ct_book_name']) ?></a>
                  </p>
                <?php endif; ?>
                <?php if ($row['ct_pg_no']): ?>
                  <p style="font-size:0.85rem; color:var(--ic-text-dim); margin:0;">Page <?= htmlspecialchars((string)$row['ct_pg_no']) ?></p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </aside>
        </div>
        <?php
        break;

    case 'gifts':
        $row = cg_query_one("
          SELECT g.*, gc.ct_class_name, b.ct_book_name, b.ct_ct_slug AS book_slug
          FROM `{$p}customtables_table_gifts` g
          LEFT JOIN `{$p}customtables_table_giftclass` gc ON gc.id = g.ct_gift_class
          LEFT JOIN `{$p}customtables_table_books` b ON b.id = g.ct_book_id
          WHERE g.ct_slug=? AND g.published=1", [$slug]);
        if (!$row) $notFound();

        $pageTitle = $row['ct_gifts_name'];
        $activeNav = 'gifts';
        require __DIR__ . '/../layout-head.php';
        ?>
        <div class="breadcrumb">
          <a href="/ic">Home</a> <span>›</span>
          <a href="/ic/gifts">Gifts</a> <span>›</span>
          <?= htmlspecialchars($row['ct_gifts_name']) ?>
        </div>

        <div class="detail-layout">
          <div>
            <p class="detail-name"><?= htmlspecialchars($row['ct_gifts_name']) ?></p>
            <?php if ($row['ct_class_name']): ?>
              <p class="detail-subtitle"><?= htmlspecialchars($row['ct_class_name']) ?> Gift</p>
            <?php endif; ?>

            <?php $effect = strip_tags($row['ct_gifts_effect_description'] ?: $row['ct_gifts_effect'] ?? ''); ?>
            <?php if ($effect): ?>
              <div class="detail-desc"><?= nl2br(htmlspecialchars($effect)) ?></div>
            <?php endif; ?>
          </div>

          <aside class="detail-sidebar">
            <div class="sidebar-card">
              <p class="sidebar-card-title">Details</p>
              <table style="width:100%; font-size:0.88rem; border-collapse:collapse;">
                <?php if ($row['ct_class_name']): ?>
                  <tr>
                    <td style="color:var(--ic-text-dim); padding:0.25rem 0; width:40%;">Class</td>
                    <td style="color:var(--ic-text-muted);"><?= htmlspecialchars($row['ct_class_name']) ?></td>
                  </tr>
                <?php endif; ?>
                <?php if (!empty($row['ct_gifts_refresh'])): ?>
                  <tr>
                    <td style="color:var(--ic-text-dim); padding:0.25rem 0;">Refresh</td>
                    <td style="color:var(--ic-text-muted);"><?= htmlspecialchars($row['ct_gifts_refresh']) ?></td>
                  </tr>
                <?php endif; ?>
                <?php if (!empty($row['ct_gifts_allows_multiple'])): ?>
                  <tr>
                    <td style="color:var(--ic-text-dim); padding:0.25rem 0;">Multiple</td>
                    <td style="color:var(--ic-text-muted);">Allowed</td>
                  </tr>
                <?php endif; ?>
                <?php if ($row['ct_book_name']): ?>
                  <tr>
                    <td style="color:var(--ic-text-dim); padding:0.25rem 0;">Book</td>
                    <td><a href="/ic/books/<?= htmlspecialchars($row['book_slug']) ?>"><?= htmlspecialchars($row['ct_book_name']) ?></a></td>
                  </tr>
                <?php endif; ?>
                <?php if ($row['ct_pg_no']): ?>
                  <tr>
                    <td style="color:var(--ic-text-dim); padding:0.25rem 0;">Page</td>
                    <td style="color:var(--ic-text-muted);"><?= htmlspecialchars((string)$row['ct_pg_no']) ?></td>
                  </tr>
                <?php endif; ?>
              </table>
            </div>
          </aside>
        </div>
        <?php
        break;

    case 'skills':
        $row = cg_query_one("
            SELECT sk.*, b.ct_book_name, b.ct_ct_slug AS book_slug
            FROM `{$p}customtables_table_skills` sk
            LEFT JOIN `{$p}customtables_table_books` b ON b.id = sk.ct_skill_source_book
            WHERE sk.ct_slug=? AND sk.published=1", [$slug]);
        if (!$row) $notFound();

        $descs = [];
        for ($i = 1; $i <= 3; $i++) {
            $v = $row["ct_skill_descriptor_$i"] ?? '';
            if ($v) $descs[] = $v;
        }
        $favs = [];
        for ($i = 1; $i <= 10; $i++) {
            $key = $i === 1 ? 'ct_skill_suggested_fav' : "ct_skill_suggested_fav$i";
            if (!empty($row[$key])) $favs[] = $row[$key];
        }
        // Gifts that improve this skill
        $improveGifts = [];
        for ($i = 1; $i <= 8; $i++) {
            $key = $i === 1 ? 'ct_skill_gifts_improve' : "ct_skill_gifts_improve$i";
            if (!empty($row[$key])) $improveGifts[] = (int)$row[$key];
        }
        $igRows = [];
        if ($improveGifts) {
            $in = implode(',', $improveGifts);
            $igRows = cg_query("SELECT id, ct_gifts_name, ct_slug FROM `{$p}customtables_table_gifts` WHERE id IN ($in) AND published=1");
        }

        // Careers that use this skill
        $skillCareers = cg_query("
            SELECT c.ct_career_name, c.ct_slug
            FROM `{$p}customtables_table_careers` c
            JOIN `{$p}customtables_table_career_skills` cs ON cs.career_id = c.id
            WHERE cs.skill_id = ? AND c.published=1
            ORDER BY c.ct_career_name
        ", [(int)$row['id']]);

        // Species that have this skill
        $skillSpecies = cg_query("
            SELECT sp.ct_species_name, sp.ct_slug
            FROM `{$p}customtables_table_species` sp
            JOIN `{$p}customtables_table_species_traits` st ON st.species_id = sp.id
            WHERE st.trait_key LIKE 'skill%'
              AND CAST(st.text_value AS UNSIGNED) = ?
              AND sp.published=1
            ORDER BY sp.ct_species_name
        ", [(int)$row['id']]);

        $pageTitle = $row['ct_skill_name'];
        $activeNav = 'skills';
        require __DIR__ . '/../layout-head.php';
        ?>
        <div class="breadcrumb">
          <a href="/ic">Home</a> <span>›</span>
          <a href="/ic/skills">Skills</a> <span>›</span>
          <?= htmlspecialchars($row['ct_skill_name']) ?>
        </div>

        <div class="detail-layout">
          <div>
            <p class="detail-name"><?= htmlspecialchars($row['ct_skill_name']) ?></p>

            <?php if ($descs): ?>
              <div class="card-tags" style="margin-bottom:1rem;">
                <?php foreach ($descs as $d): ?><span class="tag tag-skill"><?= htmlspecialchars($d) ?></span><?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if ($row['ct_skill_description']): ?>
              <div class="detail-desc"><?= nl2br(htmlspecialchars(strip_tags($row['ct_skill_description']))) ?></div>
            <?php endif; ?>

            <?php if ($favs): ?>
              <div class="detail-section">
                <p class="detail-section-title">Suggested Favourites</p>
                <div class="card-tags">
                  <?php foreach ($favs as $f): ?><span class="tag tag-skill"><?= htmlspecialchars($f) ?></span><?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <aside class="detail-sidebar">
            <?php if ($skillCareers): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Careers Using This Skill</p>
                <ul class="trait-list">
                  <?php foreach ($skillCareers as $c): ?>
                    <li><a href="/ic/careers/<?= htmlspecialchars($c['ct_slug']) ?>"><?= htmlspecialchars($c['ct_career_name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if ($skillSpecies): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Species With This Skill</p>
                <ul class="trait-list">
                  <?php foreach ($skillSpecies as $sp): ?>
                    <li><a href="/ic/species/<?= htmlspecialchars($sp['ct_slug']) ?>"><?= htmlspecialchars($sp['ct_species_name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if ($igRows): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Gifts That Improve This Skill</p>
                <ul class="trait-list">
                  <?php foreach ($igRows as $g): ?>
                    <li class="gift-item"><a href="/ic/gifts/<?= htmlspecialchars($g['ct_slug']) ?>"><?= htmlspecialchars($g['ct_gifts_name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if ($row['ct_book_name'] || $row['ct_pg_no']): ?>
              <div class="sidebar-card">
                <p class="sidebar-card-title">Source</p>
                <?php if ($row['ct_book_name']): ?>
                  <p style="font-size:0.9rem; margin:0 0 0.2rem;">
                    <a href="/ic/books/<?= htmlspecialchars($row['book_slug']) ?>"><?= htmlspecialchars($row['ct_book_name']) ?></a>
                  </p>
                <?php endif; ?>
                <?php if ($row['ct_pg_no']): ?>
                  <p style="font-size:0.85rem; color:var(--ic-text-dim); margin:0;">Page <?= htmlspecialchars((string)$row['ct_pg_no']) ?></p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </aside>
        </div>
        <?php
        break;

    case 'weapons':
        $row = cg_query_one("SELECT * FROM `{$p}customtables_table_weapons` WHERE ct_slug=? AND published=1", [$slug]);
        if (!$row) $notFound();

        $pageTitle = $row['ct_weapons_name'];
        $activeNav = 'weapons';
        require __DIR__ . '/../layout-head.php';
        ?>
        <div class="breadcrumb">
          <a href="/ic">Home</a> <span>›</span>
          <a href="/ic/weapons">Weapons</a> <span>›</span>
          <?= htmlspecialchars($row['ct_weapons_name']) ?>
        </div>

        <div class="detail-layout">
          <div>
            <p class="detail-name"><?= htmlspecialchars($row['ct_weapons_name']) ?></p>
            <?php if ($row['ct_weapon_class']): ?>
              <p class="detail-subtitle"><?= htmlspecialchars(ucfirst($row['ct_weapon_class'])) ?> Weapon</p>
            <?php endif; ?>

            <?php if ($row['ct_description']): ?>
              <div class="detail-desc"><?= nl2br(htmlspecialchars(strip_tags($row['ct_description']))) ?></div>
            <?php endif; ?>

            <?php if ($row['ct_effect']): ?>
              <div class="detail-section">
                <p class="detail-section-title">Effect</p>
                <p style="color:var(--ic-text-muted); font-size:1rem; line-height:1.6;"><?= nl2br(htmlspecialchars($row['ct_effect'])) ?></p>
              </div>
            <?php endif; ?>
          </div>

          <aside class="detail-sidebar">
            <div class="sidebar-card">
              <p class="sidebar-card-title">Statistics</p>
              <table style="width:100%; font-size:0.88rem; border-collapse:collapse;">
                <?php $stats = [
                    'Class'       => ucfirst($row['ct_weapon_class'] ?? ''),
                    'Equip'       => $row['ct_equip'] ?? '',
                    'Range'       => $row['ct_range_band'] ?? '',
                    'Attack Dice' => $row['ct_attack_dice'] ?? '',
                    'Descriptors' => $row['ct_descriptors'] ?? '',
                    'Reload'      => $row['ct_reload'] ?? '',
                    'Page'        => $row['ct_pg_no'] ?? '',
                ]; ?>
                <?php foreach ($stats as $k => $v): if (!$v) continue; ?>
                  <tr>
                    <td style="color:var(--ic-text-dim); padding:0.25rem 0; width:45%;"><?= htmlspecialchars($k) ?></td>
                    <td style="color:var(--ic-text-muted);"><?= htmlspecialchars((string)$v) ?></td>
                  </tr>
                <?php endforeach; ?>
              </table>
            </div>
          </aside>
        </div>
        <?php
        break;

    case 'equipment':
        $row = cg_query_one("SELECT * FROM `{$p}customtables_table_equipment` WHERE ct_slug=? AND published=1", [$slug]);
        if (!$row) $notFound();

        $pageTitle = $row['ct_name'];
        $activeNav = 'equipment';
        require __DIR__ . '/../layout-head.php';

        $catLabels = [
            'ammunition' => 'Ammunition', 'armor' => 'Armour', 'care' => 'Care & Hygiene',
            'consumables' => 'Consumables', 'containers' => 'Containers',
            'food_and_drink' => 'Food & Drink', 'garments' => 'Garments',
            'illumination' => 'Illumination', 'labor' => 'Labour', 'lodging' => 'Lodging',
            'personal_items' => 'Personal Items', 'shields' => 'Shields',
            'trade_gear' => 'Trade Gear', 'transportation' => 'Transportation',
            'trappings_misc' => 'Trappings & Misc', 'valuables' => 'Valuables',
        ];
        $catName = $catLabels[$row['ct_category']] ?? ucwords(str_replace('_', ' ', $row['ct_category']));
        ?>
        <div class="breadcrumb">
          <a href="/ic">Home</a> <span>›</span>
          <a href="/ic/equipment">Equipment</a> <span>›</span>
          <?= htmlspecialchars($catName) ?> <span>›</span>
          <?= htmlspecialchars($row['ct_name']) ?>
        </div>

        <div class="detail-layout">
          <div>
            <p class="detail-name"><?= htmlspecialchars($row['ct_name']) ?></p>
            <?php if ($row['ct_subcategory']): ?>
              <p class="detail-subtitle"><?= htmlspecialchars($row['ct_subcategory']) ?> · <?= htmlspecialchars($catName) ?></p>
            <?php endif; ?>

            <?php if ($row['ct_effect']): ?>
              <div class="detail-desc"><?= nl2br(htmlspecialchars(strip_tags($row['ct_effect']))) ?></div>
            <?php endif; ?>

            <?php if ($row['ct_notes']): ?>
              <div class="detail-section">
                <p class="detail-section-title">Notes</p>
                <p style="color:var(--ic-text-muted); font-size:1rem; line-height:1.6;"><?= nl2br(htmlspecialchars(strip_tags($row['ct_notes']))) ?></p>
              </div>
            <?php endif; ?>
          </div>

          <aside class="detail-sidebar">
            <div class="sidebar-card">
              <p class="sidebar-card-title">Details</p>
              <table style="width:100%; font-size:0.88rem; border-collapse:collapse;">
                <?php
                $cost = $row['ct_cost_text'] ?: (($row['ct_cost_qty'] ?: '') . ' ' . ($row['ct_cost_unit'] ?: ''));
                $stats = [
                    'Category'   => $catName,
                    'Cost'       => trim($cost),
                    'Weight'     => $row['ct_weight_text'] ?: '',
                    'Rare'       => !empty($row['ct_is_rare']) ? 'Yes' : '',
                    'Proscribed' => !empty($row['ct_is_proscribed']) ? 'Yes' : '',
                    'Armour'     => $row['ct_armor_dice'] ?: '',
                    'Cover'      => $row['ct_cover_dice'] ?: '',
                    'Capacity'   => $row['ct_capacity_text'] ?: '',
                    'Page'       => $row['ct_pg_no'] ?: '',
                ];
                ?>
                <?php foreach ($stats as $k => $v): if (!$v) continue; ?>
                  <tr>
                    <td style="color:var(--ic-text-dim); padding:0.25rem 0; width:45%;"><?= htmlspecialchars($k) ?></td>
                    <td style="color:var(--ic-text-muted);"><?= htmlspecialchars((string)$v) ?></td>
                  </tr>
                <?php endforeach; ?>
              </table>
            </div>
          </aside>
        </div>
        <?php
        break;

    case 'books':
        $row = cg_query_one("SELECT * FROM `{$p}customtables_table_books` WHERE ct_ct_slug=? AND published=1", [$slug]);
        if (!$row) $notFound();

        // Dedup abstract (raw DB value repeats the same paragraph 3×)
        $bookDesc = $row['ct_book_abstract'] ?? '';
        if ($bookDesc && strlen($bookDesc) > 120) {
            $marker = substr($bookDesc, 0, 80);
            $repeat = strpos($bookDesc, $marker, 80);
            if ($repeat !== false) $bookDesc = trim(substr($bookDesc, 0, $repeat));
            $bookDesc = rtrim($bookDesc, ". \t\n\r");
        }

        $bookId = (int)$row['id'];

        // Species in this book
        $bookSpecies = cg_query("
            SELECT ct_species_name, ct_slug, ct_pg_no
            FROM `{$p}customtables_table_species`
            WHERE ct_species_source_book = ? AND published=1
            ORDER BY ct_species_name
        ", [$bookId]);

        // Careers in this book
        $bookCareers = cg_query("
            SELECT ct_career_name, ct_slug, ct_pg_no
            FROM `{$p}customtables_table_careers`
            WHERE ct_career_source_book = ? AND published=1
            ORDER BY ct_career_name
        ", [$bookId]);

        // Gifts in this book
        $bookGifts = cg_query("
            SELECT ct_gifts_name, ct_slug, ct_pg_no
            FROM `{$p}customtables_table_gifts`
            WHERE ct_book_id = ? AND published=1
            ORDER BY ct_gifts_name
        ", [$bookId]);

        $pageTitle = $row['ct_book_name'];
        $activeNav = 'books';
        require __DIR__ . '/../layout-head.php';
        ?>
        <div class="breadcrumb">
          <a href="/ic">Home</a> <span>›</span>
          <a href="/ic/books">Books</a> <span>›</span>
          <?= htmlspecialchars($row['ct_book_name']) ?>
        </div>

        <div class="detail-layout">
          <div>
            <p class="detail-name"><?= htmlspecialchars($row['ct_book_name']) ?></p>

            <?php if ($bookDesc): ?>
              <div class="detail-desc"><?= nl2br(htmlspecialchars($bookDesc)) ?></div>
            <?php endif; ?>

            <?php if ($bookSpecies): ?>
              <div class="detail-section">
                <p class="detail-section-title">Species (<?= count($bookSpecies) ?>)</p>
                <div class="card-tags">
                  <?php foreach ($bookSpecies as $sp): ?>
                    <a href="/ic/species/<?= htmlspecialchars($sp['ct_slug']) ?>" class="tag tag-skill"><?= htmlspecialchars($sp['ct_species_name']) ?></a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if ($bookCareers): ?>
              <div class="detail-section">
                <p class="detail-section-title">Careers (<?= count($bookCareers) ?>)</p>
                <div class="card-tags">
                  <?php foreach ($bookCareers as $c): ?>
                    <a href="/ic/careers/<?= htmlspecialchars($c['ct_slug']) ?>" class="tag tag-skill"><?= htmlspecialchars($c['ct_career_name']) ?></a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if ($bookGifts): ?>
              <div class="detail-section">
                <p class="detail-section-title">Gifts (<?= count($bookGifts) ?>)</p>
                <div class="card-tags">
                  <?php foreach ($bookGifts as $g): ?>
                    <a href="/ic/gifts/<?= htmlspecialchars($g['ct_slug']) ?>" class="tag tag-skill"><?= htmlspecialchars($g['ct_gifts_name']) ?></a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <aside class="detail-sidebar">
            <?php if ($row['ct_cover_image']): ?>
              <div class="sidebar-card" style="padding:0; overflow:hidden; border-radius:var(--ic-radius-lg);">
                <?php if ($row['ct_url_to_buy']): ?>
                  <a href="<?= htmlspecialchars($row['ct_url_to_buy']) ?>" target="_blank" rel="noopener">
                <?php endif; ?>
                <img src="<?= htmlspecialchars($row['ct_cover_image']) ?>"
                     alt="<?= htmlspecialchars($row['ct_book_name']) ?> cover"
                     style="width:100%; display:block; border-radius:var(--ic-radius-lg);">
                <?php if ($row['ct_url_to_buy']): ?>
                  </a>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="sidebar-card">
              <p class="sidebar-card-title">Contents</p>
              <table style="width:100%; font-size:0.88rem; border-collapse:collapse;">
                <?php if ($bookSpecies): ?>
                  <tr><td style="color:var(--ic-text-dim); padding:0.25rem 0;">Species</td><td style="color:var(--ic-text-muted);"><?= count($bookSpecies) ?></td></tr>
                <?php endif; ?>
                <?php if ($bookCareers): ?>
                  <tr><td style="color:var(--ic-text-dim); padding:0.25rem 0;">Careers</td><td style="color:var(--ic-text-muted);"><?= count($bookCareers) ?></td></tr>
                <?php endif; ?>
                <?php if ($bookGifts): ?>
                  <tr><td style="color:var(--ic-text-dim); padding:0.25rem 0;">Gifts</td><td style="color:var(--ic-text-muted);"><?= count($bookGifts) ?></td></tr>
                <?php endif; ?>
              </table>
            </div>

            <?php if ($row['ct_url_to_buy']): ?>
              <div class="sidebar-card">
                <a href="<?= htmlspecialchars($row['ct_url_to_buy']) ?>" target="_blank" rel="noopener"
                   style="display:block; text-align:center; background:rgba(201,168,76,0.12); border:1px solid rgba(201,168,76,0.3);
                          color:var(--ic-gold); border-radius:var(--ic-radius); font-family:'Cinzel',serif;
                          font-size:0.72rem; font-weight:700; letter-spacing:0.06em; text-transform:uppercase;
                          padding:0.6rem 1rem; text-decoration:none;">
                  Buy on DriveThruRPG →
                </a>
              </div>
            <?php endif; ?>
          </aside>
        </div>
        <?php
        break;

    default:
        $notFound();
}
?>

<?php require __DIR__ . '/../layout-foot.php'; ?>
