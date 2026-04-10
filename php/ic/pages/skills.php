<?php
$pageTitle = 'Skills';
$activeNav = 'skills';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();
$skills = cg_query("SELECT * FROM `{$p}customtables_table_skills` WHERE published=1 ORDER BY ct_skill_name");

// Build descriptor list per skill
function getSkillDescriptors(array $row): array {
    $out = [];
    for ($i = 1; $i <= 3; $i++) {
        $val = $row["ct_skill_descriptor_$i"] ?? '';
        if ($val) $out[] = $val;
    }
    return $out;
}

function getSkillFavs(array $row): array {
    $out = [];
    for ($i = 1; $i <= 10; $i++) {
        $key = $i === 1 ? 'ct_skill_suggested_fav' : "ct_skill_suggested_fav$i";
        $val = $row[$key] ?? '';
        if ($val) $out[] = $val;
    }
    return $out;
}
?>

<div class="page-header">
  <div class="header-row">
    <h1>Skills</h1>
    <?php if ($isAdmin): ?><a href="/admin?pane=ic-skills&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
  <p>Core skills used by all characters. Each skill can be specialised with descriptors.</p>
</div>

<div class="filter-bar">
  <div class="filter-search">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input id="ic-live-search" type="search" placeholder="Filter by name…" data-target=".skill-row" autocomplete="off">
  </div>
</div>

<div id="skills-list">
<?php foreach ($skills as $sk):
    $name  = $sk['ct_skill_name'];
    $desc  = $sk['ct_skill_description'];
    $slug  = $sk['ct_slug'];
    $descs = getSkillDescriptors($sk);
    $favs  = getSkillFavs($sk);
?>
  <div class="skill-row" data-name="<?= htmlspecialchars(strtolower($name)) ?>">
    <div>
      <p class="skill-row-name">
        <a href="/ic/skills/<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($name) ?></a>
      </p>
      <?php if ($descs): ?>
        <div class="card-tags" style="margin-top:0.35rem;">
          <?php foreach ($descs as $d): ?>
            <span class="tag tag-skill"><?= htmlspecialchars($d) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div>
      <?php if ($desc): ?>
        <p class="skill-row-desc"><?= htmlspecialchars(mb_substr(strip_tags($desc), 0, 600)) ?></p>
      <?php endif; ?>
      <?php if ($favs): ?>
        <p class="skill-row-favs">
          <strong style="color:var(--ic-gold); font-family:'Cinzel',serif; font-size:0.75rem; letter-spacing:0.06em; text-transform:uppercase;">Suggested favourites:</strong>
          <?= htmlspecialchars(implode(', ', $favs)) ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>

<script>
(function () {
  const input = document.getElementById('ic-live-search');
  if (!input) return;
  function doFilter() {
    const q = input.value.trim().toLowerCase();
    document.querySelectorAll('.skill-row').forEach(el => {
      el.classList.toggle('hidden', !!q && !el.dataset.name.includes(q) && !el.textContent.toLowerCase().includes(q));
    });
  }
  let _typed = false;
  input.addEventListener('input', function() { _typed = true; doFilter(); });
  window.addEventListener('pagehide', function() { input.value = ''; });
  window.addEventListener('pageshow', function() { _typed = false; input.value = ''; doFilter(); });
  [100, 350, 750].forEach(function(ms) {
    setTimeout(function() { if (!_typed && input.value) { input.value = ''; doFilter(); } }, ms);
  });
}());
</script>

<?php require __DIR__ . '/../layout-foot.php'; ?>
