<?php
$pageTitle = 'Species';
$activeNav = 'species';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();

// One query: species list
$species = cg_query("SELECT * FROM `{$p}customtables_table_species` WHERE published=1 ORDER BY ct_species_name");

// One query: traits + gift names + skill names via JOINs
$traitsRaw = cg_query("
    SELECT st.species_id, st.trait_key, st.ref_id, st.text_value, st.sort,
           g.ct_gifts_name, g.ct_slug AS gift_slug,
           sk.ct_skill_name
    FROM `{$p}customtables_table_species_traits` st
    LEFT JOIN `{$p}customtables_table_gifts`  g  ON g.id  = st.ref_id
    LEFT JOIN `{$p}customtables_table_skills` sk ON (st.trait_key LIKE 'skill%'
                                                     AND CAST(st.text_value AS UNSIGNED) = sk.id)
    ORDER BY st.species_id, st.sort
");
$traits   = [];
$giftsMap = [];
foreach ($traitsRaw as $t) {
    $traits[(int)$t['species_id']][] = $t;
    if ($t['ref_id'] && $t['ct_gifts_name']) {
        $giftsMap[(int)$t['ref_id']] = $t['ct_gifts_name'];
    }
}
?>

<div class="page-header">
  <div class="header-row">
    <div>
      <h1>Species</h1>
      <p>Every character belongs to a species, which grants skills and gifts.</p>
    </div>
    <div style="margin-left:auto; color:var(--ic-text-dim); font-size:0.85rem;"><?= count($species) ?> species</div>
  </div>
</div>

<div class="filter-bar">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="ic-live-search" type="text" placeholder="Filter by name…" data-target=".card" autocomplete="off">
  </div>
  <div style="color:var(--ic-text-dim); font-size:0.82rem; margin-left:auto;">
    <span id="visible-count"><?= count($species) ?></span> shown
  </div>
</div>

<div class="cards-grid" id="species-grid">
<?php foreach ($species as $s):
    $id       = (int)$s['id'];
    $name     = $s['ct_species_name'];
    $desc     = $s['ct_species_description'];
    $slug     = $s['ct_slug'];
    $myTraits = $traits[$id] ?? [];

    // Separate skill tags and gift tags from trait rows
    $skillTags = [];
    $giftTags  = [];
    foreach ($myTraits as $t) {
        if (str_starts_with($t['trait_key'], 'skill')) {
            // Use joined skill name if available, otherwise text_value
            $skillName = $t['ct_skill_name'] ?? $t['text_value'];
            if ($skillName) $skillTags[] = $skillName;
        } elseif (str_starts_with($t['trait_key'], 'gift')) {
            if ($t['ct_gifts_name']) {
                $giftTags[] = $t['ct_gifts_name'];
            } elseif ($t['ref_id'] && isset($giftsMap[(int)$t['ref_id']])) {
                $giftTags[] = $giftsMap[(int)$t['ref_id']];
            } elseif ($t['text_value']) {
                $giftTags[] = $t['text_value'];
            }
        }
    }

    // Fallback for gift choice columns
    if (empty($giftTags)) {
        foreach (['ct_species_gift_one_choice', 'ct_species_gift_two_choice', 'ct_species_gift_three_choice'] as $col) {
            if (!empty($s[$col])) $giftTags[] = $s[$col];
        }
    }
?>
  <a href="/ic/species/<?= htmlspecialchars($slug) ?>" class="card" data-name="<?= htmlspecialchars($name) ?>" style="text-decoration:none;">
    <p class="card-name"><?= htmlspecialchars($name) ?></p>
    <?php if ($desc): ?>
      <p class="card-desc"><?= htmlspecialchars(mb_substr(strip_tags($desc), 0, 200)) ?></p>
    <?php endif; ?>
    <?php if ($skillTags || $giftTags): ?>
      <div class="card-divider"></div>
      <div class="card-tags">
        <?php foreach ($skillTags as $sk): ?>
          <span class="tag tag-skill"><?= htmlspecialchars($sk) ?></span>
        <?php endforeach; ?>
        <?php foreach ($giftTags as $gt): ?>
          <span class="tag tag-gift"><?= htmlspecialchars($gt) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </a>
<?php endforeach; ?>
</div>

<script>
(function () {
  const input = document.getElementById('ic-live-search');
  const counter = document.getElementById('visible-count');
  if (!input) return;
  input.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    let visible = 0;
    document.querySelectorAll('.card').forEach(el => {
      const match = !q || (el.dataset.name || el.textContent).toLowerCase().includes(q);
      el.classList.toggle('hidden', !match);
      if (match) visible++;
    });
    if (counter) counter.textContent = visible;
  });
}());
</script>

<?php require __DIR__ . '/../layout-foot.php'; ?>
