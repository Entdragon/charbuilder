<?php
$pageTitle = 'Types';
$activeNav = 'types';

$p    = cg_prefix();
$rows = cg_query(
    "SELECT id, name, slug, description, skill_1, skill_2, skill_3, gift_1, soak_1, soak_2, gear
       FROM `{$p}uj_types`
      WHERE published = 1
      ORDER BY name",
    []
);

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <div class="header-row">
    <h1>Types</h1>
  </div>
  <p>A character's type refines their species, granting three skills, a gift, soaks, and starting gear.</p>
</div>

<div class="filter-bar">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="uj-live-search" data-target=".card" type="text" placeholder="Filter by name…">
  </div>
  <span class="text-dim" style="font-size:0.85rem;"><?= count($rows) ?> types</span>
</div>

<div class="cards-grid">
<?php foreach ($rows as $r): ?>
  <div class="card" data-name="<?= htmlspecialchars($r['name']) ?>">
    <p class="card-name">
      <a href="/uj/types/<?= htmlspecialchars($r['slug']) ?>"><?= htmlspecialchars($r['name']) ?></a>
    </p>
    <?php if ($r['description']): ?>
    <p class="card-desc"><?= htmlspecialchars($r['description']) ?></p>
    <?php endif; ?>
    <div class="card-divider"></div>
    <div class="card-tags">
      <?php foreach ([$r['skill_1'], $r['skill_2'], $r['skill_3']] as $s): ?>
        <?php if ($s): ?><span class="tag tag-skill"><?= htmlspecialchars($s) ?></span><?php endif; ?>
      <?php endforeach; ?>
      <?php if ($r['gift_1']): ?><span class="tag tag-gift"><?= htmlspecialchars($r['gift_1']) ?></span><?php endif; ?>
      <?php if ($r['soak_1']): ?><span class="tag tag-soak"><?= htmlspecialchars($r['soak_1']) ?></span><?php endif; ?>
      <?php if ($r['soak_2']): ?><span class="tag tag-soak"><?= htmlspecialchars($r['soak_2']) ?></span><?php endif; ?>
    </div>
    <?php if ($r['gear']): ?>
    <p style="font-size:0.8rem; color:var(--uj-text-dim); margin:0;">
      <span class="tag tag-gear">Gear</span> <?= htmlspecialchars($r['gear']) ?>
    </p>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
