<?php
$pageTitle = 'Careers';
$activeNav = 'careers';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();

$typeMap = [1 => 'Major', 2 => 'Minor'];

$filterType = isset($_GET['type']) && in_array((int)$_GET['type'], [1,2]) ? (int)$_GET['type'] : 0;

// One query: career → skill names via JOIN (replaces skills map + junction table queries)
$csJoin = cg_query("
    SELECT cs.career_id, sk.ct_skill_name
    FROM `{$p}customtables_table_career_skills` cs
    JOIN `{$p}customtables_table_skills` sk ON sk.id = cs.skill_id
    ORDER BY cs.career_id, cs.sort
");
$careerSkills = [];
foreach ($csJoin as $r) {
    $careerSkills[(int)$r['career_id']][] = $r['ct_skill_name'];
}

// One query: type counts via UNION ALL
$countRows = cg_query("
    SELECT 1 AS t, COUNT(*) AS n FROM `{$p}customtables_table_careers` WHERE published=1 AND ct_career_type=1
    UNION ALL
    SELECT 2,     COUNT(*)    FROM `{$p}customtables_table_careers` WHERE published=1 AND ct_career_type=2
");
$counts = [];
foreach ($countRows as $r) { $counts[(int)$r['t']] = (int)$r['n']; }

// Careers main query
$where = "WHERE published=1" . ($filterType ? " AND ct_career_type = $filterType" : '');
$careers = cg_query("SELECT * FROM `{$p}customtables_table_careers` $where ORDER BY ct_career_name");
?>

<div class="page-header">
  <div class="header-row">
    <div>
      <h1>Careers</h1>
      <p>Major careers suit player characters; minor careers are for supporting NPCs.</p>
    </div>
    <?php if ($isAdmin): ?><a href="/admin?pane=ic-careers&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
</div>

<div class="filter-bar">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="ic-live-search" type="text" placeholder="Filter by name…" data-target=".card" autocomplete="off">
  </div>
  <div class="filter-pills">
    <a href="/ic/careers" class="filter-pill<?= $filterType===0 ? ' active' : '' ?>">All <?= array_sum($counts) ?></a>
    <a href="/ic/careers?type=1" class="filter-pill<?= $filterType===1 ? ' active' : '' ?>">Major <?= $counts[1] ?></a>
    <a href="/ic/careers?type=2" class="filter-pill<?= $filterType===2 ? ' active' : '' ?>">Minor <?= $counts[2] ?></a>
  </div>
  <div style="color:var(--ic-text-dim); font-size:0.82rem; margin-left:auto;">
    <span id="visible-count"><?= count($careers) ?></span> shown
  </div>
</div>

<div class="cards-grid">
<?php foreach ($careers as $c):
    $id      = (int)$c['id'];
    $name    = $c['ct_career_name'];
    $slug    = $c['ct_slug'];
    $type    = (int)$c['ct_career_type'];
    $desc    = $c['ct_career_description'];
    $skills  = $careerSkills[$id] ?? []; // already skill name strings
?>
  <a href="/ic/careers/<?= htmlspecialchars($slug) ?>" class="card" data-name="<?= htmlspecialchars($name) ?>" style="text-decoration:none;">
    <p class="card-name"><?= htmlspecialchars($name) ?></p>
    <?php if (isset($typeMap[$type])): ?>
      <p class="card-subtitle"><?= $typeMap[$type] ?> Career</p>
    <?php endif; ?>
    <?php if ($desc): ?>
      <p class="card-desc"><?= htmlspecialchars(mb_substr(strip_tags($desc), 0, 180)) ?></p>
    <?php endif; ?>
    <?php if ($skills || isset($typeMap[$type])): ?>
      <div class="card-divider"></div>
      <div class="card-tags">
        <?php foreach ($skills as $skillName): ?>
          <span class="tag tag-skill"><?= htmlspecialchars($skillName) ?></span>
        <?php endforeach; ?>
        <?php if (isset($typeMap[$type])): ?>
          <span class="tag <?= $type===1 ? 'tag-major' : 'tag-minor' ?>"><?= $typeMap[$type] ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </a>
<?php endforeach; ?>
</div>

<script>
(function () {
  const input = document.getElementById('ic-live-search');
  const counter = document.getElementById('visible-count');
  if (!input) return;
  function doFilter() {
    const q = input.value.trim().toLowerCase();
    let visible = 0;
    document.querySelectorAll('.card').forEach(el => {
      const match = !q || (el.dataset.name || el.textContent).toLowerCase().includes(q);
      el.classList.toggle('hidden', !match);
      if (match) visible++;
    });
    if (counter) counter.textContent = visible;
  }
  input.addEventListener('input', doFilter);
  window.addEventListener('pagehide', function() { input.value = ''; });
  window.addEventListener('pageshow', doFilter);
}());
</script>

<?php require __DIR__ . '/../layout-foot.php'; ?>
