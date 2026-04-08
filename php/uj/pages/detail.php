<?php
/**
 * Generic detail page.
 * Expects $entity (string) and $slug (string) from the router.
 */
$entity = $entity ?? '';
$slug   = $slug   ?? '';

$p = cg_prefix();

// ── Fetch the main record ─────────────────────────────────────────────────
$record = null;
$pageTitle = 'Not Found';
$activeNav = $entity;

try {
    switch ($entity) {
        case 'species':
            $record = cg_query_one(
                "SELECT * FROM `{$p}uj_species` WHERE slug = ? AND published = 1",
                [$slug]
            );
            break;
        case 'types':
            $record = cg_query_one(
                "SELECT * FROM `{$p}uj_types` WHERE slug = ? AND published = 1",
                [$slug]
            );
            break;
        case 'careers':
            $record = cg_query_one(
                "SELECT * FROM `{$p}uj_careers` WHERE slug = ? AND published = 1",
                [$slug]
            );
            break;
        case 'skills':
            $record = cg_query_one(
                "SELECT * FROM `{$p}uj_skills` WHERE slug = ? AND published = 1",
                [$slug]
            );
            break;
        case 'gifts':
            $record = cg_query_one(
                "SELECT * FROM `{$p}uj_gifts` WHERE slug = ? AND published = 1",
                [$slug]
            );
            break;
        case 'soaks':
            $record = cg_query_one(
                "SELECT * FROM `{$p}uj_soaks` WHERE slug = ? AND published = 1",
                [$slug]
            );
            break;
        case 'attacks':
            $record = cg_query_one(
                "SELECT * FROM `{$p}uj_attacks` WHERE slug = ? AND published = 1",
                [$slug]
            );
            break;
        case 'items':
            $record = cg_query_one(
                "SELECT * FROM `{$p}uj_items` WHERE slug = ? AND published = 1",
                [$slug]
            );
            break;
    }
} catch (Throwable) { }

if (!$record) {
    $pageTitle = 'Not Found';
    require __DIR__ . '/../layout-head.php';
    echo '<div class="page-header"><h1 style="color:var(--uj-error);">Not Found</h1>';
    echo '<p>The page you requested could not be found.</p>';
    echo '<p><a href="/uj/' . htmlspecialchars($entity) . '">← Back to ' . htmlspecialchars(ucfirst($entity)) . '</a></p></div>';
    require __DIR__ . '/../layout-foot.php';
    exit;
}

$pageTitle = $record['name'];

// ── Fetch joined skills/gifts/soaks where applicable ──────────────────────
$joinedSkills = [];
$joinedGifts  = [];
$joinedSoaks  = [];

$entityId = (int)$record['id'];

try {
    if (in_array($entity, ['species', 'types', 'careers'])) {
        $singular = rtrim($entity, 's'); // species→specy (not used), types→type, careers→career
        // Fix: entity slug to singular for table names
        $singMap  = ['species' => 'species', 'types' => 'types', 'careers' => 'careers'];
        $tblBase  = $singMap[$entity];

        $joinedSkills = cg_query(
            "SELECT s.name, s.slug, s.paired_trait, s.description
               FROM `{$p}uj_{$tblBase}_skills` js
               JOIN `{$p}uj_skills` s ON s.id = js.skill_id
              WHERE js.{$tblBase}_id = ?
              ORDER BY js.sort_order, s.name",
            [$entityId]
        ) ?: [];

        $joinedGifts = cg_query(
            "SELECT g.name, g.slug, g.subtitle, g.gift_type, g.description, g.recharge
               FROM `{$p}uj_{$tblBase}_gifts` jg
               JOIN `{$p}uj_gifts` g ON g.id = jg.gift_id
              WHERE jg.{$tblBase}_id = ?
              ORDER BY jg.sort_order, g.name",
            [$entityId]
        ) ?: [];
    }

    if ($entity === 'types') {
        $joinedSoaks = cg_query(
            "SELECT s.name, s.slug, s.damage_negated, s.soak_type
               FROM `{$p}uj_types_soaks` js
               JOIN `{$p}uj_soaks` s ON s.id = js.soak_id
              WHERE js.types_id = ?
              ORDER BY js.sort_order, s.name",
            [$entityId]
        ) ?: [];
    }
} catch (Throwable) { }

require __DIR__ . '/../layout-head.php';

$listLabel = [
    'species' => 'Species', 'types' => 'Types', 'careers' => 'Careers',
    'skills' => 'Skills', 'gifts' => 'Gifts', 'soaks' => 'Soaks',
    'attacks' => 'Attacks', 'items' => 'Items',
][$entity] ?? ucfirst($entity);
?>

<div class="breadcrumb">
  <a href="/uj">Home</a>
  <span>›</span>
  <a href="/uj/<?= htmlspecialchars($entity) ?>"><?= htmlspecialchars($listLabel) ?></a>
  <span>›</span>
  <span><?= htmlspecialchars($record['name']) ?></span>
</div>

<?php if (in_array($entity, ['species', 'types', 'careers'])): ?>
<!-- ── Character entity detail ─────────────────────────────────────────── -->
<div class="detail-layout">
  <div class="detail-body">
    <h1 class="detail-name"><?= htmlspecialchars($record['name']) ?></h1>

    <?php if (!empty($record['description'])): ?>
    <p class="detail-desc"><?= nl2br(htmlspecialchars($record['description'])) ?></p>
    <?php endif; ?>

    <?php if ($joinedSkills): ?>
    <div class="detail-section">
      <h3 class="detail-section-title">Skills Granted</h3>
      <div style="display:flex; flex-direction:column; gap:0.5rem;">
        <?php foreach ($joinedSkills as $sk): ?>
        <div style="background:var(--uj-surface-2); border:1px solid rgba(45,212,191,0.15); border-left:3px solid var(--uj-teal); border-radius:var(--uj-radius); padding:0.6rem 0.9rem;">
          <a href="/uj/skills/<?= htmlspecialchars($sk['slug']) ?>" style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; color:var(--uj-teal);"><?= htmlspecialchars($sk['name']) ?></a>
          <?php if ($sk['paired_trait']): ?><span style="font-size:0.78rem; color:var(--uj-text-dim); margin-left:0.5rem;">— <?= htmlspecialchars($sk['paired_trait']) ?></span><?php endif; ?>
          <?php if ($sk['description']): ?><p style="font-size:0.85rem; color:var(--uj-text-muted); margin:0.3rem 0 0; line-height:1.4;"><?= htmlspecialchars($sk['description']) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($joinedGifts): ?>
    <div class="detail-section">
      <h3 class="detail-section-title">Gifts Granted</h3>
      <div style="display:flex; flex-direction:column; gap:0.5rem;">
        <?php foreach ($joinedGifts as $g): ?>
        <div style="background:var(--uj-surface-2); border:1px solid rgba(244,166,34,0.15); border-left:3px solid var(--uj-amber); border-radius:var(--uj-radius); padding:0.6rem 0.9rem;">
          <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;">
            <a href="/uj/gifts/<?= htmlspecialchars($g['slug']) ?>" style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; color:var(--uj-amber-light);"><?= htmlspecialchars($g['name']) ?></a>
            <span class="tag tag-<?= htmlspecialchars($g['gift_type']) ?>"><?= ucfirst($g['gift_type']) ?></span>
          </div>
          <?php if ($g['subtitle']): ?><p style="font-style:italic; color:var(--uj-teal); font-size:0.85rem; margin:0.2rem 0 0;"><?= htmlspecialchars($g['subtitle']) ?></p><?php endif; ?>
          <?php if ($g['description']): ?><p style="font-size:0.85rem; color:var(--uj-text-muted); margin:0.3rem 0 0; line-height:1.4;"><?= htmlspecialchars($g['description']) ?></p><?php endif; ?>
          <?php if ($g['recharge']): ?><p style="font-size:0.78rem; color:var(--uj-text-dim); margin:0.25rem 0 0;">Recharge: <?= htmlspecialchars($g['recharge']) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- .detail-body -->

  <div class="detail-sidebar">
    <div class="sidebar-card">
      <h3 class="sidebar-card-title">Quick Reference</h3>
      <?php if ($joinedSkills): ?>
      <p style="font-family:'Cinzel',serif; font-size:0.65rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--uj-text-dim); margin:0 0 0.4rem;">Skills</p>
      <ul class="trait-list" style="margin-bottom:0.75rem;">
        <?php foreach ($joinedSkills as $sk): ?>
        <li><a href="/uj/skills/<?= htmlspecialchars($sk['slug']) ?>" style="color:var(--uj-teal);"><?= htmlspecialchars($sk['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <?php if ($joinedGifts): ?>
      <p style="font-family:'Cinzel',serif; font-size:0.65rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--uj-text-dim); margin:0 0 0.4rem;">Gifts</p>
      <ul class="trait-list" style="margin-bottom:0.75rem;">
        <?php foreach ($joinedGifts as $g): ?>
        <li class="gift-item"><a href="/uj/gifts/<?= htmlspecialchars($g['slug']) ?>" style="color:var(--uj-amber);"><?= htmlspecialchars($g['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <?php if ($joinedSoaks): ?>
      <p style="font-family:'Cinzel',serif; font-size:0.65rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--uj-text-dim); margin:0 0 0.4rem;">Soaks</p>
      <ul class="trait-list" style="margin-bottom:0.75rem;">
        <?php foreach ($joinedSoaks as $s): ?>
        <li class="soak-item"><a href="/uj/soaks/<?= htmlspecialchars($s['slug']) ?>" style="color:var(--uj-text-muted);"><?= htmlspecialchars($s['name']) ?></a>
          <?php if ($s['damage_negated']): ?><small><?= htmlspecialchars($s['damage_negated']) ?></small><?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <?php if (!empty($record['gear'])): ?>
      <p style="font-family:'Cinzel',serif; font-size:0.65rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--uj-text-dim); margin:0 0 0.4rem;">Starting Gear</p>
      <p class="gear-text"><?= htmlspecialchars($record['gear']) ?></p>
      <?php endif; ?>
    </div><!-- .sidebar-card -->
  </div><!-- .detail-sidebar -->

</div><!-- .detail-layout -->

<?php elseif ($entity === 'skills'): ?>
<!-- ── Skill detail ──────────────────────────────────────────────────────── -->
<h1 class="detail-name"><?= htmlspecialchars($record['name']) ?></h1>
<?php if (!empty($record['paired_trait'])): ?>
<p class="detail-subtitle">Paired with <?= htmlspecialchars($record['paired_trait']) ?></p>
<?php endif; ?>
<?php if (!empty($record['description'])): ?>
<p class="detail-desc"><?= nl2br(htmlspecialchars($record['description'])) ?></p>
<?php endif; ?>
<?php if (!empty($record['sample_favorites'])): ?>
<div class="sidebar-card" style="max-width:480px;">
  <h3 class="sidebar-card-title">Sample Favorites</h3>
  <p style="font-size:0.9rem; color:var(--uj-text-muted); margin:0;"><?= nl2br(htmlspecialchars($record['sample_favorites'])) ?></p>
</div>
<?php endif; ?>
<?php if (!empty($record['gift_notes'])): ?>
<div class="sidebar-card" style="max-width:480px; margin-top:1rem;">
  <h3 class="sidebar-card-title">Gift Notes</h3>
  <p style="font-size:0.9rem; color:var(--uj-text-muted); margin:0;"><?= nl2br(htmlspecialchars($record['gift_notes'])) ?></p>
</div>
<?php endif; ?>

<?php elseif ($entity === 'gifts'): ?>
<!-- ── Gift detail ───────────────────────────────────────────────────────── -->
<div style="display:flex; align-items:flex-start; gap:0.75rem; margin-bottom:0.5rem; flex-wrap:wrap;">
  <h1 class="detail-name" style="margin:0;"><?= htmlspecialchars($record['name']) ?></h1>
  <span class="tag tag-<?= htmlspecialchars($record['gift_type']) ?>" style="margin-top:6px;"><?= ucfirst($record['gift_type']) ?></span>
</div>
<?php if (!empty($record['subtitle'])): ?>
<p class="detail-subtitle"><?= htmlspecialchars($record['subtitle']) ?></p>
<?php endif; ?>
<?php if (!empty($record['description'])): ?>
<p class="detail-desc"><?= nl2br(htmlspecialchars($record['description'])) ?></p>
<?php endif; ?>
<?php if (!empty($record['recharge'])): ?>
<p style="color:var(--uj-text-dim); font-size:0.9rem;"><strong style="color:var(--uj-text-muted);">Recharge:</strong> <?= htmlspecialchars($record['recharge']) ?></p>
<?php endif; ?>

<?php elseif ($entity === 'soaks'): ?>
<!-- ── Soak detail ───────────────────────────────────────────────────────── -->
<div style="display:flex; align-items:flex-start; gap:0.75rem; margin-bottom:0.5rem; flex-wrap:wrap;">
  <h1 class="detail-name" style="margin:0;"><?= htmlspecialchars($record['name']) ?></h1>
  <span class="tag tag-<?= htmlspecialchars($record['soak_type'] ?? 'basic') ?>" style="margin-top:6px;"><?= ucfirst($record['soak_type'] ?? 'basic') ?></span>
</div>
<?php if (!empty($record['damage_negated'])): ?>
<p class="detail-subtitle">Negates: <?= htmlspecialchars($record['damage_negated']) ?></p>
<?php endif; ?>
<?php if (!empty($record['description'])): ?>
<p class="detail-desc"><?= nl2br(htmlspecialchars($record['description'])) ?></p>
<?php endif; ?>
<?php if (!empty($record['recharge'])): ?>
<p style="color:var(--uj-text-dim); font-size:0.9rem;"><strong style="color:var(--uj-text-muted);">Recharge:</strong> <?= htmlspecialchars($record['recharge']) ?></p>
<?php endif; ?>
<?php if (!empty($record['side_effect'])): ?>
<p style="color:var(--uj-error); font-size:0.9rem;"><strong>Side effect:</strong> <?= htmlspecialchars($record['side_effect']) ?></p>
<?php endif; ?>

<?php elseif ($entity === 'attacks'): ?>
<!-- ── Attack detail ─────────────────────────────────────────────────────── -->
<h1 class="detail-name"><?= htmlspecialchars($record['name']) ?></h1>
<p class="detail-subtitle"><?= htmlspecialchars(ucwords($record['category'] ?? '')) ?></p>
<div style="display:flex; gap:1.5rem; flex-wrap:wrap; margin-bottom:1.25rem;">
  <?php foreach (['attack_range' => 'Attack Range', 'counter_range' => 'Counter Range', 'attack_dice' => 'Dice'] as $col => $lbl): ?>
    <?php if (!empty($record[$col])): ?>
    <div>
      <div style="font-family:'Cinzel',serif; font-size:0.65rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--uj-text-dim); margin-bottom:0.2rem;"><?= $lbl ?></div>
      <div style="font-size:1rem; color:var(--uj-amber-light); font-weight:600;"><?= htmlspecialchars($record[$col]) ?></div>
    </div>
    <?php endif; ?>
  <?php endforeach; ?>
</div>
<?php if (!empty($record['effect'])): ?>
<p class="detail-desc"><?= nl2br(htmlspecialchars($record['effect'])) ?></p>
<?php endif; ?>
<?php if (!empty($record['notes'])): ?>
<p style="color:var(--uj-text-dim); font-size:0.9rem; font-style:italic;"><?= htmlspecialchars($record['notes']) ?></p>
<?php endif; ?>

<?php elseif ($entity === 'items'): ?>
<!-- ── Item detail ───────────────────────────────────────────────────────── -->
<?php
$classColors = ['Affordable'=>'#34d399','Expensive'=>'#f4a622','Extravagant'=>'#c084fc','Proscribed'=>'#f87171'];
$cls = $record['cost_class'] ?? '';
$cc  = $classColors[$cls] ?? 'var(--uj-text-muted)';
?>
<h1 class="detail-name"><?= htmlspecialchars($record['name']) ?></h1>
<p class="detail-subtitle" style="color:<?= $cc ?>;"><?= htmlspecialchars($cls) ?></p>
<div style="display:flex; gap:2rem; flex-wrap:wrap; margin-bottom:1.25rem;">
  <?php if (!empty($record['price_early'])): ?>
  <div>
    <div style="font-family:'Cinzel',serif; font-size:0.65rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--uj-text-dim); margin-bottom:0.2rem;">1910s / 1920s</div>
    <div style="font-size:1.1rem; color:var(--uj-amber-light); font-weight:600;"><?= htmlspecialchars($record['price_early']) ?></div>
  </div>
  <?php endif; ?>
  <?php if (!empty($record['price_late'])): ?>
  <div>
    <div style="font-family:'Cinzel',serif; font-size:0.65rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--uj-text-dim); margin-bottom:0.2rem;">1930s / 1940s</div>
    <div style="font-size:1.1rem; color:var(--uj-amber-light); font-weight:600;"><?= htmlspecialchars($record['price_late']) ?></div>
  </div>
  <?php endif; ?>
</div>
<?php if (!empty($record['description'])): ?>
<p class="detail-desc"><?= nl2br(htmlspecialchars($record['description'])) ?></p>
<?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../layout-foot.php'; ?>
