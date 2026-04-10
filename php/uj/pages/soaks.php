<?php
$pageTitle = 'Soaks';
$activeNav = 'soaks';

$p    = cg_prefix();
$rows = cg_query(
    "SELECT id, name, slug, description, damage_negated, soak_type, recharge, side_effect
       FROM `{$p}uj_soaks`
      WHERE published = 1
      ORDER BY soak_type, name",
    []
);

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <div class="header-row">
    <h1>Soaks</h1>
    <?php if ($isAdmin): ?><a href="/admin?pane=uj-soaks&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
  <p>Soaks reduce damage taken. Basic soaks are available to all characters; advanced soaks must be granted by a type or career.</p>
</div>

<div class="filter-bar">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="uj-live-search" data-target=".card" type="search" placeholder="Filter…">
  </div>
</div>

<div class="cards-grid">
<?php foreach ($rows as $r): ?>
  <div class="card" data-name="<?= htmlspecialchars($r['name']) ?>">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.5rem;">
      <p class="card-name" style="margin:0;">
        <a href="/uj/soaks/<?= htmlspecialchars($r['slug']) ?>"><?= htmlspecialchars($r['name']) ?></a>
      </p>
      <span class="tag tag-<?= $r['soak_type'] ?? 'basic' ?>" style="flex-shrink:0; margin-top:2px;"><?= ucfirst($r['soak_type'] ?? 'basic') ?></span>
    </div>
    <?php if ($r['damage_negated']): ?>
    <p style="font-family:'Cinzel',serif; font-size:0.85rem; color:var(--uj-teal); margin:0;">
      Negates: <?= htmlspecialchars($r['damage_negated']) ?>
    </p>
    <?php endif; ?>
    <?php if ($r['description']): ?>
    <p class="card-desc"><?= htmlspecialchars($r['description']) ?></p>
    <?php endif; ?>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
      <?php if ($r['recharge']): ?>
      <p style="font-size:0.8rem; color:var(--uj-text-dim); margin:0;">Recharge: <?= htmlspecialchars($r['recharge']) ?></p>
      <?php endif; ?>
      <?php if ($r['side_effect']): ?>
      <p style="font-size:0.8rem; color:var(--uj-error); margin:0;"><?= htmlspecialchars($r['side_effect']) ?></p>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
