<?php
$pageTitle = 'Gifts';
$activeNav = 'gifts';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();

// One query: gift class names + counts via LEFT JOIN
$classRows = cg_query("
    SELECT gc.id, gc.ct_class_name, gc.ct_slug,
           COUNT(g.id) AS n
    FROM `{$p}customtables_table_giftclass` gc
    LEFT JOIN `{$p}customtables_table_gifts` g ON g.ct_gift_class = gc.id AND g.published=1
    GROUP BY gc.id, gc.ct_class_name, gc.ct_slug
    ORDER BY gc.ct_class_name
");
$giftClasses = $classRows;
$classMap    = [];
$classCounts = [];
$classSlugMap = [];
foreach ($classRows as $r) {
    $classMap[(int)$r['id']]    = $r['ct_class_name'];
    $classCounts[(int)$r['id']] = (int)$r['n'];
    if ($r['ct_slug']) $classSlugMap[$r['ct_slug']] = (int)$r['id'];
}

// Filter by class — accept either integer ID or slug string
$filterClass = 0;
if (isset($_GET['class'])) {
    $raw = $_GET['class'];
    if (ctype_digit($raw) && (int)$raw > 0) {
        $filterClass = (int)$raw;
    } elseif (isset($classSlugMap[$raw])) {
        $filterClass = $classSlugMap[$raw];
    }
}

// Filter by type (slug)
$filterType     = trim($_GET['type'] ?? '');
$filterTypeName = '';
if ($filterType) {
    $typeRow = cg_query_one("SELECT id, ct_type_name FROM `{$p}customtables_table_gifttype` WHERE ct_slug=?", [$filterType]);
    if ($typeRow) {
        $filterTypeName = $typeRow['ct_type_name'];
    } else {
        $filterType = ''; // invalid slug — ignore
    }
}

$classWhere = $filterClass ? " AND g.ct_gift_class=$filterClass" : '';
$typeJoin   = '';
if ($filterType && $typeRow) {
    $tid = (int)$typeRow['id'];
    $typeJoin  = " JOIN `{$p}customtables_table_gift_type_map` tm ON tm.gift_id=g.id AND tm.type_id=$tid";
}
$gifts = cg_query("
  SELECT g.*, gc.ct_class_name
  FROM `{$p}customtables_table_gifts` g
  LEFT JOIN `{$p}customtables_table_giftclass` gc ON gc.id = g.ct_gift_class
  {$typeJoin}
  WHERE g.published=1 $classWhere
  ORDER BY gc.ct_class_name, g.ct_gifts_name
");

$total = count($gifts);
?>

<div class="page-header">
  <div class="header-row">
    <div>
      <h1>Gifts</h1>
      <p>Gifts are special abilities, powers and perks that define your character.</p>
    </div>
    <div style="margin-left:auto; color:var(--ic-text-dim); font-size:0.85rem;"><?= $total ?> gifts<?= ($filterClass || $filterType) ? ' (filtered)' : ' total' ?></div>
  </div>
</div>

<div class="filter-bar" style="flex-wrap:wrap;">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="gift-search" type="text" placeholder="Filter by name or effect…" autocomplete="off">
  </div>
  <select id="class-select" class="filter-select">
    <option value="">All Classes (<?= $icCounts['gifts'] ?>)</option>
    <?php foreach ($giftClasses as $gc):
      $cid = (int)$gc['id'];
      $cnt = $classCounts[$cid] ?? 0;
      if ($cnt === 0) continue;
    ?>
      <option value="<?= $cid ?>" <?= $filterClass===$cid ? 'selected' : '' ?>>
        <?= htmlspecialchars($gc['ct_class_name']) ?> (<?= $cnt ?>)
      </option>
    <?php endforeach; ?>
  </select>
  <div style="color:var(--ic-text-dim); font-size:0.82rem; margin-left:auto;">
    <span id="visible-count"><?= $total ?></span> shown
  </div>
</div>

<?php if ($filterTypeName): ?>
<div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem; align-items:center;">
  <span style="color:var(--ic-text-dim); font-size:0.85rem;">Filtered by type:</span>
  <a href="/ic/gifts" style="display:inline-flex; align-items:center; gap:0.3rem; background:var(--ic-accent-dim,rgba(179,138,90,0.15)); color:var(--ic-accent); border-radius:4px; padding:0.2rem 0.5rem; font-size:0.82rem; text-decoration:none;">
    <?= htmlspecialchars($filterTypeName) ?> <span style="opacity:0.6;">✕</span>
  </a>
</div>
<?php endif; ?>

<table class="ic-table" id="gifts-table">
  <thead>
    <tr>
      <th style="width:220px;">Gift</th>
      <th style="width:120px;">Class</th>
      <th>Effect</th>
      <th style="width:90px;">Refresh</th>
    </tr>
  </thead>
  <tbody>
  <?php
  $currentClass = null;
  foreach ($gifts as $g):
    $class = $g['ct_class_name'] ?? '—';
    if ($class !== $currentClass && !$filterClass):
      $currentClass = $class;
  ?>
    <tr class="table-category-header">
      <td colspan="4"><?= htmlspecialchars($class) ?></td>
    </tr>
  <?php endif; ?>
    <tr class="gift-row" data-name="<?= htmlspecialchars(strtolower($g['ct_gifts_name'])) ?>" data-class="<?= (int)$g['ct_gift_class'] ?>">
      <td class="td-name"><a href="/ic/gifts/<?= htmlspecialchars($g['ct_slug']) ?>"><?= htmlspecialchars($g['ct_gifts_name']) ?></a></td>
      <td class="td-muted"><?= htmlspecialchars($class) ?></td>
      <td class="td-muted" style="max-width:500px; overflow:hidden;">
        <?php
          $effect = $g['ct_gifts_effect'] ?: $g['ct_gifts_effect_description'];
          echo htmlspecialchars(mb_substr(strip_tags($effect ?? ''), 0, 200));
        ?>
      </td>
      <td class="td-dim"><?= htmlspecialchars($g['ct_gifts_refresh'] ?? '—') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<script>
(function () {
  const search  = document.getElementById('gift-search');
  const select  = document.getElementById('class-select');
  const counter = document.getElementById('visible-count');
  const rows    = document.querySelectorAll('.gift-row');
  const catRows = document.querySelectorAll('.table-category-header');

  function filter() {
    const q   = search.value.trim().toLowerCase();
    const cls = select.value ? parseInt(select.value) : 0;
    let visible = 0;
    let lastCat = null;
    catRows.forEach(r => r.style.display = '');
    rows.forEach(row => {
      const nameMatch  = !q  || row.dataset.name.includes(q) || row.textContent.toLowerCase().includes(q);
      const classMatch = !cls || parseInt(row.dataset.class) === cls;
      const show = nameMatch && classMatch;
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (counter) counter.textContent = visible;
    // Hide category headers that have no visible rows
    catRows.forEach(cat => {
      let next = cat.nextElementSibling;
      let hasVisible = false;
      while (next && !next.classList.contains('table-category-header')) {
        if (next.style.display !== 'none') hasVisible = true;
        next = next.nextElementSibling;
      }
      cat.style.display = hasVisible ? '' : 'none';
    });
  }

  search.addEventListener('input', filter);
  select.addEventListener('change', function () {
    const cls = this.value ? '?class=' + this.value : '';
    // Update URL without reload for class filter
    const rows2 = document.querySelectorAll('.gift-row');
    const cls2 = this.value ? parseInt(this.value) : 0;
    let visible = 0;
    catRows.forEach(r => r.style.display = '');
    rows2.forEach(row => {
      const q2 = search.value.trim().toLowerCase();
      const nameMatch  = !q2 || row.dataset.name.includes(q2) || row.textContent.toLowerCase().includes(q2);
      const classMatch = !cls2 || parseInt(row.dataset.class) === cls2;
      const show = nameMatch && classMatch;
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (counter) counter.textContent = visible;
    catRows.forEach(cat => {
      let next = cat.nextElementSibling;
      let hasVisible = false;
      while (next && !next.classList.contains('table-category-header')) {
        if (next.style.display !== 'none') hasVisible = true;
        next = next.nextElementSibling;
      }
      cat.style.display = hasVisible ? '' : 'none';
    });
  });
}());
</script>

<?php require __DIR__ . '/../layout-foot.php'; ?>
