<?php
$pageTitle = 'Search';
$activeNav = 'search';
require __DIR__ . '/../layout-head.php';

$q       = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (strlen($q) >= 2) {
    $p    = cg_prefix();
    $like = '%' . $q . '%';

    // Species
    $species = cg_query("SELECT ct_species_name AS name, ct_slug AS slug, ct_species_description AS excerpt FROM `{$p}customtables_table_species` WHERE published=1 AND ct_species_name LIKE ? LIMIT 30", [$like]);
    foreach ($species as $r) { $results['Species'][] = ['name' => $r['name'], 'slug' => '/ic/species/' . $r['slug'], 'excerpt' => mb_substr(strip_tags($r['excerpt']), 0, 160)]; }

    // Careers
    $careers = cg_query("SELECT ct_career_name AS name, ct_slug AS slug, ct_career_description AS excerpt FROM `{$p}customtables_table_careers` WHERE published=1 AND ct_career_name LIKE ? LIMIT 30", [$like]);
    foreach ($careers as $r) { $results['Careers'][] = ['name' => $r['name'], 'slug' => '/ic/careers/' . $r['slug'], 'excerpt' => mb_substr(strip_tags($r['excerpt']), 0, 160)]; }

    // Gifts
    $gifts = cg_query("SELECT ct_gifts_name AS name, ct_slug AS slug, ct_gifts_effect_description AS excerpt FROM `{$p}customtables_table_gifts` WHERE published=1 AND (ct_gifts_name LIKE ? OR ct_gifts_effect_description LIKE ?) LIMIT 50", [$like, $like]);
    foreach ($gifts as $r) { $results['Gifts'][] = ['name' => $r['name'], 'slug' => '/ic/gifts/' . $r['slug'], 'excerpt' => mb_substr(strip_tags($r['excerpt']), 0, 160)]; }

    // Skills
    $skills = cg_query("SELECT ct_skill_name AS name, ct_slug AS slug, ct_skill_description AS excerpt FROM `{$p}customtables_table_skills` WHERE published=1 AND (ct_skill_name LIKE ? OR ct_skill_description LIKE ?) LIMIT 20", [$like, $like]);
    foreach ($skills as $r) { $results['Skills'][] = ['name' => $r['name'], 'slug' => '/ic/skills/' . $r['slug'], 'excerpt' => mb_substr(strip_tags($r['excerpt']), 0, 160)]; }

    // Equipment
    $equip = cg_query("SELECT ct_name AS name, ct_slug AS slug, ct_notes AS excerpt FROM `{$p}customtables_table_equipment` WHERE published=1 AND ct_name LIKE ? LIMIT 30", [$like]);
    foreach ($equip as $r) { $results['Equipment'][] = ['name' => $r['name'], 'slug' => '/ic/equipment/' . $r['slug'], 'excerpt' => mb_substr(strip_tags($r['excerpt']), 0, 160)]; }

    // Weapons
    $weaps = cg_query("SELECT ct_weapons_name AS name, ct_slug AS slug, ct_effect AS excerpt FROM `{$p}customtables_table_weapons` WHERE published=1 AND (ct_weapons_name LIKE ? OR ct_effect LIKE ?) LIMIT 30", [$like, $like]);
    foreach ($weaps as $r) { $results['Weapons'][] = ['name' => $r['name'], 'slug' => '/ic/weapons/' . $r['slug'], 'excerpt' => mb_substr(strip_tags($r['excerpt']), 0, 160)]; }
}

$total = array_sum(array_map('count', $results));
?>

<div class="page-header">
  <h1>Search</h1>
  <p>Search across species, careers, gifts, skills and equipment.</p>
</div>

<form class="search-form" method="get">
  <input class="search-input" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search species, gifts, careers, equipment…" autofocus>
  <button class="search-btn" type="submit">Search</button>
</form>

<?php if ($q !== '' && strlen($q) < 2): ?>
  <p style="color:var(--ic-text-muted);">Enter at least 2 characters to search.</p>
<?php elseif ($q !== '' && $total === 0): ?>
  <p style="color:var(--ic-text-muted);">No results found for "<strong><?= htmlspecialchars($q) ?></strong>".</p>
<?php elseif ($total > 0): ?>
  <p style="color:var(--ic-text-muted); margin-bottom:1rem;"><?= $total ?> result<?= $total !== 1 ? 's' : '' ?> for "<strong><?= htmlspecialchars($q) ?></strong>"</p>

  <?php foreach ($results as $section => $items): ?>
    <p class="search-group-label"><?= htmlspecialchars($section) ?> <span style="color:var(--ic-text-dim); font-size:0.75rem;">(<?= count($items) ?>)</span></p>
    <?php foreach ($items as $item): ?>
      <a href="<?= htmlspecialchars($item['slug']) ?>" class="search-result">
        <div style="flex:1; min-width:0;">
          <p class="search-result-name"><?= htmlspecialchars($item['name']) ?></p>
          <?php if ($item['excerpt']): ?>
            <p class="search-result-desc"><?= htmlspecialchars($item['excerpt']) ?></p>
          <?php endif; ?>
        </div>
        <div style="color:var(--ic-text-dim); font-size:0.8rem; font-family:'Cinzel',serif; white-space:nowrap;"><?= htmlspecialchars($section) ?></div>
      </a>
    <?php endforeach; ?>
  <?php endforeach; ?>
<?php elseif ($q === ''): ?>
  <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:0.75rem; margin-top:1rem;">
    <a href="/ic/species"   class="stat-card"><div class="stat-n"><?= $icCounts['species']   ?></div><div class="stat-l">Species</div></a>
    <a href="/ic/careers"   class="stat-card"><div class="stat-n"><?= $icCounts['careers']   ?></div><div class="stat-l">Careers</div></a>
    <a href="/ic/gifts"     class="stat-card"><div class="stat-n"><?= $icCounts['gifts']     ?></div><div class="stat-l">Gifts</div></a>
    <a href="/ic/skills"    class="stat-card"><div class="stat-n"><?= $icCounts['skills']    ?></div><div class="stat-l">Skills</div></a>
    <a href="/ic/equipment" class="stat-card"><div class="stat-n"><?= $icCounts['equipment'] ?></div><div class="stat-l">Equipment</div></a>
    <a href="/ic/weapons"   class="stat-card"><div class="stat-n"><?= $icCounts['weapons']   ?></div><div class="stat-l">Weapons</div></a>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout-foot.php'; ?>
