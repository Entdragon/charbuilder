<?php
$pageTitle = 'Species';
$activeNav = 'species';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();

// Trait filters from query string
$filterDiet    = trim($_GET['diet']    ?? '');
$filterHabitat = trim($_GET['habitat'] ?? '');
$filterCycle   = trim($_GET['cycle']   ?? '');
$filterSense   = trim($_GET['sense']   ?? '');
$filterActive  = $filterDiet || $filterHabitat || $filterCycle || $filterSense;

// Build filtered species query
$joins  = '';
$params = [];

if ($filterDiet) {
    $joins .= " JOIN `{$p}customtables_table_species_traits` st_d  ON st_d.species_id  = sp.id AND st_d.trait_key  = 'diet'";
    $joins .= " JOIN `{$p}customtables_table_diet`           d_flt ON d_flt.id          = st_d.ref_id AND d_flt.ct_slug = ?";
    $params[] = $filterDiet;
}
if ($filterHabitat) {
    $joins .= " JOIN `{$p}customtables_table_species_traits` st_h  ON st_h.species_id  = sp.id AND st_h.trait_key  = 'habitat'";
    $joins .= " JOIN `{$p}customtables_table_habitat`        h_flt ON h_flt.id          = st_h.ref_id AND h_flt.ct_slug = ?";
    $params[] = $filterHabitat;
}
if ($filterCycle) {
    $joins .= " JOIN `{$p}customtables_table_species_traits` st_c  ON st_c.species_id  = sp.id AND st_c.trait_key  = 'cycle'";
    $joins .= " JOIN `{$p}customtables_table_cycle`          c_flt ON c_flt.id          = st_c.ref_id AND c_flt.ct_slug = ?";
    $params[] = $filterCycle;
}
if ($filterSense) {
    $joins .= " JOIN `{$p}customtables_table_species_traits` st_s  ON st_s.species_id  = sp.id AND st_s.trait_key  LIKE 'sense%'";
    $joins .= " JOIN `{$p}customtables_table_senses`         s_flt ON s_flt.id          = st_s.ref_id AND s_flt.ct_slug = ?";
    $params[] = $filterSense;
}

$species = cg_query(
    "SELECT sp.* FROM `{$p}customtables_table_species` sp {$joins} WHERE sp.published=1 ORDER BY sp.ct_species_name",
    $params
);

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

// Active filter labels for display
$filterLabels = [];
if ($filterDiet) {
    $dl = cg_query_one("SELECT ct_diet_name FROM `{$p}customtables_table_diet` WHERE ct_slug=?", [$filterDiet]);
    if ($dl) $filterLabels[] = ['label' => 'Diet: ' . $dl['ct_diet_name'], 'remove' => ic_species_filter_url(diet: null)];
}
if ($filterHabitat) {
    $hl = cg_query_one("SELECT ct_habitat_name FROM `{$p}customtables_table_habitat` WHERE ct_slug=?", [$filterHabitat]);
    if ($hl) $filterLabels[] = ['label' => 'Habitat: ' . $hl['ct_habitat_name'], 'remove' => ic_species_filter_url(habitat: null)];
}
if ($filterCycle) {
    $cl = cg_query_one("SELECT ct_cycle_name FROM `{$p}customtables_table_cycle` WHERE ct_slug=?", [$filterCycle]);
    if ($cl) $filterLabels[] = ['label' => 'Cycle: ' . $cl['ct_cycle_name'], 'remove' => ic_species_filter_url(cycle: null)];
}
if ($filterSense) {
    $sl = cg_query_one("SELECT ct_senses_name FROM `{$p}customtables_table_senses` WHERE ct_slug=?", [$filterSense]);
    if ($sl) $filterLabels[] = ['label' => 'Sense: ' . $sl['ct_senses_name'], 'remove' => ic_species_filter_url(sense: null)];
}

function ic_species_filter_url(?string $diet = '__keep__', ?string $habitat = '__keep__', ?string $cycle = '__keep__', ?string $sense = '__keep__'): string {
    global $filterDiet, $filterHabitat, $filterCycle, $filterSense;
    $q = [];
    $d = ($diet    === '__keep__') ? $filterDiet    : $diet;
    $h = ($habitat === '__keep__') ? $filterHabitat : $habitat;
    $c = ($cycle   === '__keep__') ? $filterCycle   : $cycle;
    $s = ($sense   === '__keep__') ? $filterSense   : $sense;
    if ($d) $q['diet']    = $d;
    if ($h) $q['habitat'] = $h;
    if ($c) $q['cycle']   = $c;
    if ($s) $q['sense']   = $s;
    return '/ic/species' . ($q ? '?' . http_build_query($q) : '');
}
?>

<div class="page-header">
  <div class="header-row">
    <div>
      <h1>Species</h1>
      <p>Every character belongs to a species, which grants skills and gifts.</p>
    </div>
    <div style="margin-left:auto; color:var(--ic-text-dim); font-size:0.85rem;"><?= count($species) ?> species</div>
    <?php if ($isAdmin): ?><a href="/admin?pane=ic-species&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
</div>

<?php if ($filterActive): ?>
<div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem; align-items:center;">
  <span style="color:var(--ic-text-dim); font-size:0.85rem;">Filtered by:</span>
  <?php foreach ($filterLabels as $fl): ?>
    <a href="<?= htmlspecialchars($fl['remove']) ?>" style="display:inline-flex; align-items:center; gap:0.3rem; background:var(--ic-accent-dim,rgba(179,138,90,0.15)); color:var(--ic-accent); border-radius:4px; padding:0.2rem 0.5rem; font-size:0.82rem; text-decoration:none;">
      <?= htmlspecialchars($fl['label']) ?> <span style="opacity:0.6;">✕</span>
    </a>
  <?php endforeach; ?>
  <a href="/ic/species" style="font-size:0.82rem; color:var(--ic-text-dim); text-decoration:underline;">Clear all</a>
</div>
<?php endif; ?>

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
  function doFilter() {
    const q = input.value.trim().toLowerCase();
    let visible = 0;
    document.querySelectorAll('.card').forEach(el => {
      const match = !q || (el.dataset.name || el.textContent).toLowerCase().includes(q);
      el.classList.toggle('hidden', !match);
      if (match) visible++;
    });
    if (counter) counter.textContent = visible;
  }
  input.addEventListener('input', doFilter);
  window.addEventListener('pagehide', function() { input.value = ''; });
  window.addEventListener('pageshow', function() { input.value = ''; doFilter(); });
}());
</script>

<?php require __DIR__ . '/../layout-foot.php'; ?>
