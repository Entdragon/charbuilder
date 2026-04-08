<?php
$pageTitle = 'Search';
$activeNav = 'search';

$q       = trim($_GET['q'] ?? '');
$results = [];

if ($q !== '') {
    $p    = cg_prefix();
    $like = '%' . $q . '%';

    // cols = SELECT columns, where = WHERE condition using real col names
    $tables = [
        'species' => ['label'=>'Species', 'cols'=>'id,name,slug,description',           'where'=>'name LIKE ? OR description LIKE ?'],
        'types'   => ['label'=>'Types',   'cols'=>'id,name,slug,description',           'where'=>'name LIKE ? OR description LIKE ?'],
        'careers' => ['label'=>'Careers', 'cols'=>'id,name,slug,description',           'where'=>'name LIKE ? OR description LIKE ?'],
        'skills'  => ['label'=>'Skills',  'cols'=>'id,name,slug,description',           'where'=>'name LIKE ? OR description LIKE ?'],
        'gifts'   => ['label'=>'Gifts',   'cols'=>'id,name,slug,description',           'where'=>'name LIKE ? OR description LIKE ?'],
        'soaks'   => ['label'=>'Soaks',   'cols'=>'id,name,slug,description',           'where'=>'name LIKE ? OR description LIKE ?'],
        'attacks' => ['label'=>'Attacks', 'cols'=>'id,name,slug,effect AS description', 'where'=>'name LIKE ? OR effect LIKE ? OR notes LIKE ?'],
        'items'   => ['label'=>'Items',   'cols'=>'id,name,slug,cost_class AS description','where'=>'name LIKE ?'],
    ];

    foreach ($tables as $tbl => $meta) {
        try {
            $argCount = substr_count($meta['where'], '?');
            $args     = array_fill(0, $argCount, $like);
            $rows = cg_query(
                "SELECT {$meta['cols']}
                   FROM `{$p}uj_{$tbl}`
                  WHERE published = 1
                    AND ({$meta['where']})
                  ORDER BY name
                  LIMIT 20",
                $args
            );
            if ($rows) {
                $results[$meta['label']] = ['slug_prefix' => $tbl, 'rows' => $rows];
            }
        } catch (Throwable) { }
    }
}

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <h1>Search</h1>
  <p>Search across all species, types, careers, skills, gifts, soaks, attacks, and items.</p>
</div>

<form class="search-form" method="get" action="/uj/search">
  <input class="search-input" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Enter a name or keyword…" autofocus>
  <button class="search-btn" type="submit">Search</button>
</form>

<?php if ($q !== '' && empty($results)): ?>
  <p style="color:var(--uj-text-dim); font-size:1rem;">No results found for <strong style="color:var(--uj-text-muted);"><?= htmlspecialchars($q) ?></strong>.</p>

<?php elseif (!empty($results)): ?>
  <p style="color:var(--uj-text-dim); font-size:0.9rem; margin-bottom:1rem;">
    Results for <strong style="color:var(--uj-amber);"><?= htmlspecialchars($q) ?></strong>
    — <?= array_sum(array_map(fn($g) => count($g['rows']), $results)) ?> match<?= array_sum(array_map(fn($g) => count($g['rows']), $results)) === 1 ? '' : 'es' ?>
    across <?= count($results) ?> section<?= count($results) === 1 ? '' : 's' ?>
  </p>

  <?php foreach ($results as $label => $group): ?>
    <div class="search-group-label"><?= htmlspecialchars($label) ?></div>
    <?php foreach ($group['rows'] as $r): ?>
      <a class="search-result" href="/uj/<?= $group['slug_prefix'] ?>/<?= htmlspecialchars($r['slug']) ?>">
        <div style="flex:1;">
          <p class="search-result-name"><?= htmlspecialchars($r['name']) ?></p>
          <?php if ($r['description']): ?>
          <p class="search-result-desc"><?= htmlspecialchars(mb_substr($r['description'], 0, 180)) ?><?= mb_strlen($r['description']) > 180 ? '…' : '' ?></p>
          <?php endif; ?>
        </div>
        <span class="tag tag-skill" style="flex-shrink:0; align-self:center;"><?= htmlspecialchars($label) ?></span>
      </a>
    <?php endforeach; ?>
  <?php endforeach; ?>

<?php elseif ($q === ''): ?>
  <p style="color:var(--uj-text-dim);">Enter a term above to search across all Urban Jungle rules.</p>
<?php endif; ?>

<?php require __DIR__ . '/../layout-foot.php'; ?>
