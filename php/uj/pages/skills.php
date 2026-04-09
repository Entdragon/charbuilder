<?php
$pageTitle = 'Skills';
$activeNav = 'skills';

$p    = cg_prefix();
$rows = cg_query(
    "SELECT id, name, slug, description, paired_trait, sample_favorites, gift_notes
       FROM `{$p}uj_skills`
      WHERE published = 1
      ORDER BY name",
    []
);

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <div class="header-row">
    <h1>Skills</h1>
    <?php if ($isAdmin): ?><a href="/admin?pane=uj-skills&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
  <p>There are <?= count($rows) ?> skills in Urban Jungle, each paired with a core trait. Skills are gained from your Species, Type, and Career choices.</p>
</div>

<div class="filter-bar">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="uj-live-search" data-target=".skill-row" type="text" placeholder="Filter skills…">
  </div>
</div>

<?php foreach ($rows as $r): ?>
<div class="skill-row" data-name="<?= htmlspecialchars($r['name']) ?>">
  <div>
    <p class="skill-row-name">
      <a href="/uj/skills/<?= htmlspecialchars($r['slug']) ?>" style="color:var(--uj-amber-light);"><?= htmlspecialchars($r['name']) ?></a>
    </p>
    <?php if ($r['paired_trait']): ?>
    <p class="skill-row-trait">paired with <?= htmlspecialchars($r['paired_trait']) ?></p>
    <?php endif; ?>
  </div>
  <p class="skill-row-desc"><?= htmlspecialchars($r['description'] ?? '') ?></p>
  <?php if ($r['sample_favorites']): ?>
  <div class="skill-row-favs">
    <strong>Sample Favorites</strong>
    <?= htmlspecialchars($r['sample_favorites']) ?>
  </div>
  <?php else: ?>
  <div></div>
  <?php endif; ?>
</div>
<?php endforeach; ?>

<?php require __DIR__ . '/../layout-foot.php'; ?>
