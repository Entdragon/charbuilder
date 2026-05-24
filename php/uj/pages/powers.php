<?php
$pageTitle = 'Powers';
$activeNav = 'powers';

require_once __DIR__ . '/../../actions/uj.php';

$p = cg_prefix();
$meta = uj_powers_meta();

// Count effects per power for the cards
$counts = array_fill_keys(array_keys($meta), 0);
$tableMissing = false;
try {
    foreach ($meta as $key => $_) {
        $row = cg_query_one(
            "SELECT COUNT(*) n FROM `{$p}uj_powers` WHERE published=1 AND power=?",
            [$key]
        );
        $counts[$key] = (int)($row['n'] ?? 0);
    }
} catch (Throwable) {
    $tableMissing = true;
}

require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <div class="header-row">
    <h1>Powers of Supernatural Power</h1>
  </div>
  <p>The seven Powers from <em>Occult Horror</em>. Each Power grants access to a list of effects you can cast, provided you have a Power die from <a href="/uj/gifts/personal-power" style="color:var(--uj-teal);">Personal Power</a> or <a href="/uj/gifts/petitioned-power" style="color:var(--uj-teal);">Petitioned Power</a>.</p>
</div>

<?php if ($tableMissing): ?>
  <p style="color:var(--uj-text-muted);">The <code>uj_powers</code> table has not been created yet. Run the UJ data installer from the admin panel to create it and seed the Occult Horror powers.</p>
<?php else: ?>
  <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:1rem; margin-top:1rem;">
    <?php foreach ($meta as $key => $m): ?>
      <a href="/uj/powers/<?= htmlspecialchars($key) ?>"
         style="display:block; background:var(--uj-surface-2); border:1px solid rgba(244,166,34,0.18); border-left:3px solid var(--uj-amber); border-radius:var(--uj-radius); padding:1rem 1.1rem; text-decoration:none; transition:border-color 0.15s, background 0.15s;"
         onmouseover="this.style.borderLeftColor='var(--uj-teal)';this.style.background='var(--uj-surface-3,var(--uj-surface-2))';"
         onmouseout="this.style.borderLeftColor='var(--uj-amber)';this.style.background='var(--uj-surface-2)';">
        <div style="display:flex; align-items:baseline; justify-content:space-between; gap:0.75rem; margin-bottom:0.4rem;">
          <h2 style="font-family:'Cinzel',Georgia,serif; font-size:1.05rem; color:var(--uj-amber); letter-spacing:0.06em; text-transform:uppercase; margin:0;"><?= htmlspecialchars($m['full_name']) ?></h2>
          <span style="font-size:0.78rem; color:var(--uj-text-dim);"><?= $counts[$key] ?> effect<?= $counts[$key] === 1 ? '' : 's' ?></span>
        </div>
        <p style="font-family:'Crimson Pro',Georgia,serif; font-size:0.9rem; color:var(--uj-text-muted); line-height:1.45; margin:0;">
          <?= htmlspecialchars(mb_substr($m['description'], 0, 180)) ?><?= mb_strlen($m['description']) > 180 ? '…' : '' ?>
        </p>
        <p style="font-size:0.75rem; color:var(--uj-text-dim); margin:0.6rem 0 0; text-transform:uppercase; letter-spacing:0.05em;">
          Occult Horror &middot; Page <?= (int)$m['page'] ?>
        </p>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout-foot.php'; ?>
