<?php
$pageTitle = 'Careers';
$activeNav = 'careers';

$p    = cg_prefix();
$rows = cg_query(
    "SELECT id, name, slug, description, skill_1, skill_2, skill_3, gift_1, gift_2, gear
       FROM `{$p}uj_careers`
      WHERE published = 1
      ORDER BY name",
    []
);

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <div class="header-row">
    <h1>Careers</h1>
    <?php if ($isAdmin): ?><a href="/admin?pane=uj-careers&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
  <p>A character's career defines their profession, granting three skills, two gifts, and starting gear.</p>
</div>

<div class="filter-bar">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="uj-live-search" data-target=".card" type="text" placeholder="Filter by name…">
  </div>
  <span class="text-dim" style="font-size:0.85rem;"><?= count($rows) ?> careers</span>
</div>

<div class="cards-grid">
<?php foreach ($rows as $r): ?>
  <div class="card" data-name="<?= htmlspecialchars($r['name']) ?>">
    <p class="card-name">
      <a href="/uj/careers/<?= htmlspecialchars($r['slug']) ?>"><?= htmlspecialchars($r['name']) ?></a>
    </p>
    <?php if ($r['description']): ?>
    <p class="card-desc"><?= htmlspecialchars($r['description']) ?></p>
    <?php endif; ?>
    <div class="card-divider"></div>
    <div class="card-tags">
      <?php foreach ([$r['skill_1'], $r['skill_2'], $r['skill_3']] as $s): ?>
        <?php if ($s): ?><span class="tag tag-skill"><?= htmlspecialchars($s) ?></span><?php endif; ?>
      <?php endforeach; ?>
      <?php foreach ([$r['gift_1'], $r['gift_2']] as $g): ?>
        <?php if ($g): ?><span class="tag tag-gift"><?= htmlspecialchars($g) ?></span><?php endif; ?>
      <?php endforeach; ?>
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
