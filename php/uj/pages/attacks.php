<?php
$pageTitle = 'Attacks';
$activeNav = 'attacks';

$p    = cg_prefix();
$rows = cg_query(
    "SELECT id, name, slug, category, attack_range, counter_range, attack_dice, effect, notes
       FROM `{$p}uj_attacks`
      WHERE published = 1
      ORDER BY category, name",
    []
);

// Group by category
$byCategory = [];
foreach ($rows as $r) {
    $byCategory[$r['category']][] = $r;
}

// Active category filter
$catFilter = $_GET['cat'] ?? '';
if ($catFilter && !isset($byCategory[$catFilter])) $catFilter = '';

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <h1>Attacks</h1>
  <p><?= count($rows) ?> attack methods across <?= count($byCategory) ?> categories.</p>
</div>

<div class="filter-bar">
  <div class="filter-pills">
    <a href="/uj/attacks" class="filter-pill<?= $catFilter === '' ? ' active' : '' ?>">All</a>
    <?php foreach (array_keys($byCategory) as $cat): ?>
    <a href="/uj/attacks?cat=<?= urlencode($cat) ?>"
       class="filter-pill<?= $catFilter === $cat ? ' active' : '' ?>">
      <?= htmlspecialchars(ucwords($cat)) ?>
      <span style="opacity:0.7;"> (<?= count($byCategory[$cat]) ?>)</span>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php
$renderCats = $catFilter ? [$catFilter => $byCategory[$catFilter]] : $byCategory;
foreach ($renderCats as $cat => $attacks): ?>

<h2 class="attack-category"><?= htmlspecialchars(ucwords($cat)) ?></h2>
<table class="uj-table" style="margin-bottom:1rem;">
  <thead>
    <tr>
      <th>Method</th>
      <th>Attack Range</th>
      <th>Counter Range</th>
      <th>Dice</th>
      <th>Effect / Notes</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($attacks as $a): ?>
    <tr>
      <td class="td-name">
        <a href="/uj/attacks/<?= htmlspecialchars($a['slug']) ?>"><?= htmlspecialchars($a['name']) ?></a>
      </td>
      <td class="td-muted"><?= htmlspecialchars($a['attack_range'] ?? '—') ?></td>
      <td class="td-muted"><?= htmlspecialchars($a['counter_range'] ?? '—') ?></td>
      <td class="td-muted" style="white-space:nowrap;"><?= htmlspecialchars($a['attack_dice'] ?? '—') ?></td>
      <td class="td-dim" style="max-width:280px;">
        <?= htmlspecialchars($a['effect'] ?? '') ?>
        <?php if ($a['notes']): ?>
          <br><em style="color:var(--uj-text-dim);"><?= htmlspecialchars($a['notes']) ?></em>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php endforeach; ?>

<?php require __DIR__ . '/../layout-foot.php'; ?>
