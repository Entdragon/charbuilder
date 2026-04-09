<?php
$pageTitle = 'Equipment';
$activeNav = 'equipment';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();

$filterCat = isset($_GET['cat']) ? trim($_GET['cat']) : '';

// Pretty category labels
$catLabels = [
    'ammunition'     => 'Ammunition',
    'armor'          => 'Armour',
    'care'           => 'Care & Hygiene',
    'consumables'    => 'Consumables',
    'containers'     => 'Containers',
    'food_and_drink' => 'Food & Drink',
    'garments'       => 'Garments',
    'illumination'   => 'Illumination',
    'labor'          => 'Labour',
    'lodging'        => 'Lodging',
    'personal_items' => 'Personal Items',
    'shields'        => 'Shields',
    'trade_gear'     => 'Trade Gear',
    'transportation' => 'Transportation',
    'trappings_misc' => 'Trappings & Misc',
    'valuables'      => 'Valuables',
];

$where = "WHERE published=1" . ($filterCat ? " AND ct_category = " . "'$filterCat'" : '');
$items = cg_query("SELECT * FROM `{$p}customtables_table_equipment` $where ORDER BY ct_category, ct_subcategory, ct_name");

// Count per category
$catCounts = [];
$ccRows = cg_query("SELECT ct_category, COUNT(*) n FROM `{$p}customtables_table_equipment` WHERE published=1 GROUP BY ct_category");
foreach ($ccRows as $r) { $catCounts[$r['ct_category']] = (int)$r['n']; }
?>

<div class="page-header">
  <div class="header-row">
    <div>
      <h1>Equipment</h1>
      <p>Gear, trade goods, clothing, transportation and more.</p>
    </div>
    <div style="margin-left:auto; color:var(--ic-text-dim); font-size:0.85rem;"><?= count($items) ?> items</div>
    <?php if ($isAdmin): ?><a href="/admin?pane=ic-equipment&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
</div>

<div class="filter-bar" style="flex-wrap:wrap; gap:0.4rem 0.75rem; margin-bottom:1rem;">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="eq-search" type="text" placeholder="Filter by name…" autocomplete="off">
  </div>
  <select id="cat-select" class="filter-select">
    <option value="">All Categories (<?= $icCounts['equipment'] ?>)</option>
    <?php foreach ($catLabels as $key => $label): ?>
      <option value="<?= htmlspecialchars($key) ?>" <?= $filterCat === $key ? 'selected' : '' ?>>
        <?= htmlspecialchars($label) ?> <?= isset($catCounts[$key]) ? '(' . $catCounts[$key] . ')' : '' ?>
      </option>
    <?php endforeach; ?>
  </select>
  <div style="color:var(--ic-text-dim); font-size:0.82rem; margin-left:auto;">
    <span id="visible-count"><?= count($items) ?></span> shown
  </div>
</div>

<table class="ic-table" id="eq-table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Category</th>
      <th>Cost</th>
      <th>Weight</th>
      <th>Notes</th>
    </tr>
  </thead>
  <tbody>
  <?php
  $currentCat = null;
  foreach ($items as $item):
    $cat = $item['ct_category'];
    if ($cat !== $currentCat && !$filterCat):
      $currentCat = $cat;
  ?>
    <tr class="table-category-header cat-header" data-cat="<?= htmlspecialchars($cat) ?>">
      <td colspan="5"><?= htmlspecialchars($catLabels[$cat] ?? ucwords(str_replace('_',' ',$cat))) ?></td>
    </tr>
  <?php endif; ?>
    <tr class="eq-row" data-name="<?= htmlspecialchars(strtolower($item['ct_name'])) ?>" data-cat="<?= htmlspecialchars($cat) ?>">
      <td class="td-name"><?= htmlspecialchars($item['ct_name']) ?></td>
      <td class="td-dim"><?= htmlspecialchars($catLabels[$cat] ?? ucwords(str_replace('_',' ',$cat))) ?></td>
      <td class="td-muted" style="white-space:nowrap;">
        <?php
          $cost = $item['ct_cost_text'] ?: (($item['ct_cost_qty'] ?: '') . ' ' . ($item['ct_cost_unit'] ?: ''));
          echo htmlspecialchars(trim($cost)) ?: '—';
        ?>
      </td>
      <td class="td-dim" style="white-space:nowrap;"><?= htmlspecialchars($item['ct_weight_text'] ?: '—') ?></td>
      <td class="td-muted" style="font-size:0.85rem; max-width:300px;">
        <?= htmlspecialchars(mb_substr(strip_tags($item['ct_notes'] ?: $item['ct_effect'] ?: ''), 0, 120)) ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<script>
(function () {
  const search  = document.getElementById('eq-search');
  const select  = document.getElementById('cat-select');
  const counter = document.getElementById('visible-count');
  const rows    = document.querySelectorAll('.eq-row');
  const catHdrs = document.querySelectorAll('.cat-header');

  function applyFilter() {
    const q   = search.value.trim().toLowerCase();
    const cat = select.value;
    let n = 0;
    catHdrs.forEach(h => h.style.display = '');
    rows.forEach(row => {
      const nameMatch = !q   || row.dataset.name.includes(q);
      const catMatch  = !cat || row.dataset.cat === cat;
      const show = nameMatch && catMatch;
      row.style.display = show ? '' : 'none';
      if (show) n++;
    });
    // Hide empty category headers
    catHdrs.forEach(cat => {
      let next = cat.nextElementSibling;
      let has = false;
      while (next && !next.classList.contains('cat-header')) {
        if (next.style.display !== 'none' && next.classList.contains('eq-row')) has = true;
        next = next.nextElementSibling;
      }
      cat.style.display = has ? '' : 'none';
    });
    if (counter) counter.textContent = n;
  }

  search.addEventListener('input', applyFilter);
  select.addEventListener('change', applyFilter);
}());
</script>

<?php require __DIR__ . '/../layout-foot.php'; ?>
