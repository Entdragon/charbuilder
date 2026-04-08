<?php
$pageTitle = 'Items';
$activeNav = 'items';

$p = cg_prefix();

// Cost class filter
$classFilter = $_GET['class'] ?? '';
$validClasses = ['Affordable','Expensive','Extravagant','Proscribed'];
if (!in_array($classFilter, $validClasses, true)) $classFilter = '';

$where = $classFilter ? 'AND cost_class = ?' : '';
$args  = $classFilter ? [$classFilter] : [];

$rows = cg_query(
    "SELECT id, name, slug, cost_class, price_early, price_late
       FROM `{$p}uj_items`
      WHERE published = 1 $where
      ORDER BY cost_class, name",
    $args
);

// Counts per class for filter pills
$classCounts = [];
foreach ($validClasses as $cls) {
    $n = cg_query_one("SELECT COUNT(*) n FROM `{$p}uj_items` WHERE published=1 AND cost_class=?", [$cls]);
    $classCounts[$cls] = (int)($n['n'] ?? 0);
}

// Group by class
$byClass = [];
foreach ($rows as $r) {
    $byClass[$r['cost_class']][] = $r;
}

$classColors = [
    'Affordable'  => '#34d399',
    'Expensive'   => '#f4a622',
    'Extravagant' => '#c084fc',
    'Proscribed'  => '#f87171',
];

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <h1>Items &amp; Price List</h1>
  <p>Equipment organised by cost class. Prices shown for 1910s–1920s and 1930s–1940s eras.</p>
</div>

<div class="filter-bar">
  <div class="filter-pills">
    <a href="/uj/items" class="filter-pill<?= $classFilter === '' ? ' active' : '' ?>">All (<?= count($rows) ?: array_sum($classCounts) ?>)</a>
    <?php foreach ($validClasses as $cls): ?>
    <a href="/uj/items?class=<?= urlencode($cls) ?>"
       class="filter-pill<?= $classFilter === $cls ? ' active' : '' ?>">
      <?= $cls ?> (<?= $classCounts[$cls] ?>)
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php foreach ($byClass as $cls => $items): ?>
<div style="margin-bottom: 2rem;">
  <h2 style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:<?= $classColors[$cls] ?? 'var(--uj-text-muted)' ?>; margin-bottom:0.75rem; padding-bottom:0.4rem; border-bottom:2px solid <?= $classColors[$cls] ?? 'var(--uj-border)' ?>33;">
    <?= htmlspecialchars($cls) ?>
  </h2>
  <table class="uj-table">
    <thead>
      <tr>
        <th>Item</th>
        <th>1910s / 1920s</th>
        <th>1930s / 1940s</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
      <tr>
        <td class="td-name">
          <a href="/uj/items/<?= htmlspecialchars($item['slug']) ?>"><?= htmlspecialchars($item['name']) ?></a>
        </td>
        <td class="td-muted" style="white-space:nowrap;"><?= htmlspecialchars($item['price_early'] ?? '—') ?></td>
        <td class="td-muted" style="white-space:nowrap;"><?= htmlspecialchars($item['price_late']  ?? '—') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endforeach; ?>

<?php require __DIR__ . '/../layout-foot.php'; ?>
