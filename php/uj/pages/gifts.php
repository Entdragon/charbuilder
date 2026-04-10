<?php
$pageTitle = 'Gifts';
$activeNav = 'gifts';

$p = cg_prefix();

// Filter by type
$typeFilter = $_GET['type'] ?? '';
$validTypes = ['basic', 'advanced'];
if (!in_array($typeFilter, $validTypes, true)) $typeFilter = '';

$where = $typeFilter ? "AND gift_type = ?" : '';
$args  = $typeFilter ? [$typeFilter] : [];

$rows = cg_query(
    "SELECT id, name, slug, subtitle, description, gift_type, recharge
       FROM `{$p}uj_gifts`
      WHERE published = 1 $where
      ORDER BY gift_type, name",
    $args
);

$basicCount    = (int)(cg_query_one("SELECT COUNT(*) n FROM `{$p}uj_gifts` WHERE published=1 AND gift_type='basic'")['n'] ?? 0);
$advancedCount = (int)(cg_query_one("SELECT COUNT(*) n FROM `{$p}uj_gifts` WHERE published=1 AND gift_type='advanced'")['n'] ?? 0);

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <div class="header-row">
    <h1>Gifts</h1>
    <?php if ($isAdmin): ?><a href="/admin?pane=uj-gifts&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
  <p>Special abilities granted by your species, type, career, and personality choices.</p>
</div>

<div class="filter-bar">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="uj-live-search" data-target=".card" type="search" placeholder="Filter by name…">
  </div>
  <div class="filter-pills">
    <a href="/uj/gifts" class="filter-pill<?= $typeFilter === '' ? ' active' : '' ?>">All (<?= $basicCount + $advancedCount ?>)</a>
    <a href="/uj/gifts?type=basic"    class="filter-pill<?= $typeFilter === 'basic'    ? ' active' : '' ?>">Basic (<?= $basicCount ?>)</a>
    <a href="/uj/gifts?type=advanced" class="filter-pill<?= $typeFilter === 'advanced' ? ' active' : '' ?>">Advanced (<?= $advancedCount ?>)</a>
  </div>
</div>

<div class="cards-grid">
<?php foreach ($rows as $r): ?>
  <div class="card" data-name="<?= htmlspecialchars($r['name']) ?>">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.5rem;">
      <p class="card-name" style="margin:0;">
        <a href="/uj/gifts/<?= htmlspecialchars($r['slug']) ?>"><?= htmlspecialchars($r['name']) ?></a>
      </p>
      <span class="tag tag-<?= $r['gift_type'] ?>" style="flex-shrink:0; margin-top:2px;"><?= ucfirst($r['gift_type']) ?></span>
    </div>
    <?php if ($r['subtitle']): ?>
    <p class="card-subtitle" style="margin:0;"><?= htmlspecialchars($r['subtitle']) ?></p>
    <?php endif; ?>
    <?php if ($r['description']): ?>
    <p class="card-desc"><?= htmlspecialchars($r['description']) ?></p>
    <?php endif; ?>
    <?php if ($r['recharge']): ?>
    <p style="font-size:0.8rem; color:var(--uj-text-dim); margin:0;">
      Recharge: <?= htmlspecialchars($r['recharge']) ?>
    </p>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
