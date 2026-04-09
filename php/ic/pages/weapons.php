<?php
$pageTitle = 'Weapons';
$activeNav = 'weapons';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();

$filterClass = isset($_GET['class']) ? trim($_GET['class']) : '';

// One query: class names + counts
$classCountRows = cg_query("SELECT ct_weapon_class, COUNT(*) n FROM `{$p}customtables_table_weapons` WHERE published=1 GROUP BY ct_weapon_class ORDER BY ct_weapon_class");
$classes     = [];
$classCounts = [];
foreach ($classCountRows as $r) {
    if (!$r['ct_weapon_class']) continue;
    $classes[]                              = $r['ct_weapon_class'];
    $classCounts[$r['ct_weapon_class']]     = (int)$r['n'];
}

$where   = "WHERE published=1" . ($filterClass ? " AND ct_weapon_class = " . "'" . addslashes($filterClass) . "'" : '');
$weapons = cg_query("SELECT * FROM `{$p}customtables_table_weapons` $where ORDER BY ct_weapon_class, ct_weapons_name");
?>

<div class="page-header">
  <div class="header-row">
    <div>
      <h1>Weapons</h1>
      <p>Melee, ranged, natural and thrown weapons of Calabria.</p>
    </div>
    <div style="margin-left:auto; color:var(--ic-text-dim); font-size:0.85rem;"><?= count($weapons) ?> weapons</div>
    <?php if ($isAdmin): ?><a href="/admin?pane=ic-weapons&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
</div>

<div class="filter-bar" style="flex-wrap:wrap;">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="w-search" type="text" placeholder="Filter by name or effect…" autocomplete="off">
  </div>
  <div class="filter-pills">
    <a href="/ic/weapons" class="filter-pill<?= $filterClass==='' ? ' active' : '' ?>">All</a>
    <?php foreach ($classes as $cls): if (!$cls) continue; ?>
      <a href="/ic/weapons?class=<?= urlencode($cls) ?>" class="filter-pill<?= $filterClass===$cls ? ' active' : '' ?>">
        <?= htmlspecialchars(ucfirst($cls)) ?> <?= $classCounts[$cls] ?? '' ?>
      </a>
    <?php endforeach; ?>
  </div>
  <div style="color:var(--ic-text-dim); font-size:0.82rem; margin-left:auto;">
    <span id="visible-count"><?= count($weapons) ?></span> shown
  </div>
</div>

<table class="ic-table" id="weapons-table">
  <thead>
    <tr>
      <th style="width:180px;">Name</th>
      <th style="width:90px;">Class</th>
      <th style="width:80px;">Range</th>
      <th>Attack Dice</th>
      <th>Effect</th>
      <th style="width:100px;">Descriptors</th>
    </tr>
  </thead>
  <tbody>
  <?php
  $currentClass = null;
  foreach ($weapons as $w):
    $cls = $w['ct_weapon_class'];
    if ($cls !== $currentClass && !$filterClass):
      $currentClass = $cls;
  ?>
    <tr class="table-category-header w-cat" data-cls="<?= htmlspecialchars($cls) ?>">
      <td colspan="6"><?= htmlspecialchars(ucfirst($cls)) ?></td>
    </tr>
  <?php endif; ?>
    <tr class="w-row" data-name="<?= htmlspecialchars(strtolower($w['ct_weapons_name'])) ?>">
      <td class="td-name">
        <a href="/ic/weapons/<?= htmlspecialchars($w['ct_slug']) ?>"><?= htmlspecialchars($w['ct_weapons_name']) ?></a>
        <?php if (!empty($w['ct_is_species_weapon']) && $w['ct_is_species_weapon']): ?>
          <small class="td-dim" style="display:block; font-size:0.72rem; font-style:italic;">(species only)</small>
        <?php endif; ?>
      </td>
      <td class="td-dim"><?= htmlspecialchars(ucfirst($w['ct_weapon_class'])) ?></td>
      <td class="td-muted"><?= htmlspecialchars($w['ct_range_band'] ?? '—') ?></td>
      <td class="td-muted" style="font-size:0.85rem;"><?= htmlspecialchars($w['ct_attack_dice'] ?? '—') ?></td>
      <td class="td-muted" style="font-size:0.85rem; max-width:260px;"><?= htmlspecialchars(mb_substr($w['ct_effect'] ?? '', 0, 140)) ?></td>
      <td class="td-dim" style="font-size:0.82rem;"><?= htmlspecialchars($w['ct_descriptors'] ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<script>
(function () {
  const s = document.getElementById('w-search');
  const c = document.getElementById('visible-count');
  if (!s) return;
  s.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('.w-cat').forEach(h => h.style.display = '');
    let n = 0;
    document.querySelectorAll('.w-row').forEach(row => {
      const show = !q || row.textContent.toLowerCase().includes(q);
      row.style.display = show ? '' : 'none';
      if (show) n++;
    });
    if (c) c.textContent = n;
  });
}());
</script>

<?php require __DIR__ . '/../layout-foot.php'; ?>
