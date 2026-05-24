<?php
$pageTitle = 'Powers';
$activeNav = 'powers';

$p = cg_prefix();

// Filter by power key
$powerFilter = $_GET['power'] ?? '';
$validPowers = ['esp','mesmerism','psychokinesis','rituals','spiritualism','telepathy','vitalism'];
if (!in_array($powerFilter, $validPowers, true)) $powerFilter = '';

$where = $powerFilter ? "AND power = ?" : '';
$args  = $powerFilter ? [$powerFilter] : [];

$rows = [];
$tableMissing = false;
try {
    $rows = cg_query(
        "SELECT name, slug, power, power_label, order_level, descriptors, description, page_number
           FROM `{$p}uj_powers`
          WHERE published = 1 $where
          ORDER BY power_label, order_level, name",
        $args
    );
} catch (Throwable) {
    $tableMissing = true;
}

// Group by power_label
$grouped = [];
foreach ($rows as $r) {
    $grouped[$r['power_label']][] = $r;
}

// Counts per power for filter pills
$counts = array_fill_keys($validPowers, 0);
if (!$tableMissing) {
    foreach ($validPowers as $pk) {
        try {
            $row = cg_query_one("SELECT COUNT(*) n FROM `{$p}uj_powers` WHERE published=1 AND power=?", [$pk]);
            $counts[$pk] = (int)($row['n'] ?? 0);
        } catch (Throwable) { }
    }
}

// Pretty labels for pills
$pillLabels = [
    'esp' => 'ESP', 'mesmerism' => 'Mesmerism', 'psychokinesis' => 'PK',
    'rituals' => 'Rituals', 'spiritualism' => 'Spiritualism',
    'telepathy' => 'Telepathy', 'vitalism' => 'Vitalism',
];

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <div class="header-row">
    <h1>Powers</h1>
  </div>
  <p>The seven Powers of Supernatural Power from Occult Horror — <?= count($rows) ?> effects across <?= count($grouped) ?> power<?= count($grouped) === 1 ? '' : 's' ?>. Effects are ordered by their casting Order (1, 3, 5, 7, or 10), which sets the difficulty of the spellcasting roll.</p>
</div>

<div class="filter-bar">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="uj-live-search" data-target=".power-row" type="search" placeholder="Filter effects…">
  </div>
  <div class="filter-pills">
    <a href="/uj/powers" class="filter-pill<?= $powerFilter === '' ? ' active' : '' ?>">All</a>
    <?php foreach ($validPowers as $pk): if (!$counts[$pk]) continue; ?>
      <a href="/uj/powers?power=<?= $pk ?>" class="filter-pill<?= $powerFilter === $pk ? ' active' : '' ?>"><?= htmlspecialchars($pillLabels[$pk]) ?> <?= $counts[$pk] ?></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if ($tableMissing): ?>
  <p style="color:var(--uj-text-muted);">The <code>uj_powers</code> table has not been created yet. Run the UJ data installer from the admin panel to create it and seed the Occult Horror powers.</p>
<?php elseif (!$rows): ?>
  <p style="color:var(--uj-text-muted);">No effects found. (Admins: run installer to seed Occult Horror powers.)</p>
<?php else: ?>
  <?php foreach ($grouped as $label => $effects): ?>
    <h2 style="font-family:'Cinzel',Georgia,serif; font-size:1.1rem; color:var(--uj-amber); letter-spacing:0.08em; text-transform:uppercase; margin:1.75rem 0 0.5rem; padding-bottom:0.35rem; border-bottom:1px solid var(--uj-border-light);"><?= htmlspecialchars($label) ?> <span style="color:var(--uj-text-dim); font-size:0.8rem; font-weight:400;">(<?= count($effects) ?>)</span></h2>
    <table class="uj-table">
      <thead>
        <tr>
          <th style="width:3.5rem;">Ord.</th>
          <th>Effect</th>
          <th>Descriptors</th>
          <th style="width:3rem;">Pg</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($effects as $r): ?>
        <tr class="power-row" data-name="<?= htmlspecialchars($r['name'] . ' ' . $r['descriptors']) ?>">
          <td class="td-dim"><?= (int)$r['order_level'] ?></td>
          <td class="td-name">
            <a href="/uj/powers/<?= htmlspecialchars($r['slug']) ?>"><?= htmlspecialchars($r['name']) ?></a>
            <br>
            <span style="font-family:'Crimson Pro',Georgia,serif; font-weight:400; font-size:0.88rem; color:var(--uj-text-muted); text-transform:none; letter-spacing:0; white-space:normal;"><?= htmlspecialchars($r['description']) ?></span>
          </td>
          <td class="td-dim" style="white-space:normal;"><?= htmlspecialchars($r['descriptors']) ?></td>
          <td class="td-dim"><?= $r['page_number'] ? (int)$r['page_number'] : '' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/../layout-foot.php'; ?>
