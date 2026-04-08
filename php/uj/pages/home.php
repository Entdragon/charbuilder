<?php
$pageTitle = 'Overview';
$activeNav = '';

// Counts already available (fetched in layout-head via $ujCounts)
require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <h1>Urban Jungle</h1>
  <p>1920s–1940s anthropomorphic noir — reference &amp; character builder</p>
</div>

<div class="home-stats">
  <a href="/uj/species"  class="stat-card"><div class="stat-n"><?= $ujCounts['species'] ?></div><div class="stat-l">Species</div></a>
  <a href="/uj/types"    class="stat-card"><div class="stat-n"><?= $ujCounts['types']   ?></div><div class="stat-l">Types</div></a>
  <a href="/uj/careers"  class="stat-card"><div class="stat-n"><?= $ujCounts['careers'] ?></div><div class="stat-l">Careers</div></a>
  <a href="/uj/skills"   class="stat-card"><div class="stat-n"><?= $ujCounts['skills']  ?></div><div class="stat-l">Skills</div></a>
  <a href="/uj/gifts"    class="stat-card"><div class="stat-n"><?= $ujCounts['gifts']   ?></div><div class="stat-l">Gifts</div></a>
  <a href="/uj/soaks"    class="stat-card"><div class="stat-n"><?= $ujCounts['soaks']   ?></div><div class="stat-l">Soaks</div></a>
  <a href="/uj/attacks"  class="stat-card"><div class="stat-n"><?= $ujCounts['attacks'] ?></div><div class="stat-l">Attacks</div></a>
  <a href="/uj/items"    class="stat-card"><div class="stat-n"><?= $ujCounts['items']   ?></div><div class="stat-l">Items</div></a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.5rem;">

  <div style="background:var(--uj-surface); border:1px solid var(--uj-border-cool); border-radius:var(--uj-radius-lg); padding:1.5rem;">
    <h2 style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--uj-amber); margin:0 0 0.75rem;">Building a Character</h2>
    <ol style="margin:0; padding-left:1.3rem; line-height:2; color:var(--uj-text-muted); font-size:0.95rem;">
      <li>Choose a <a href="/uj/species">Species</a> — grants 3 skills &amp; 2 gifts</li>
      <li>Choose a <a href="/uj/types">Type</a> — grants 3 skills, 1 gift, soaks &amp; gear</li>
      <li>Choose a <a href="/uj/careers">Career</a> — grants 3 skills, 2 gifts &amp; gear</li>
      <li>Assign trait dice: 2 best → d8, 1 worst → d4, rest → d6</li>
      <li>Pick a <a href="/uj/gifts">Personality gift</a> (any basic gift)</li>
      <li>Note your <a href="/uj/soaks">Soaks</a> and starting gear</li>
    </ol>
  </div>

  <div style="background:var(--uj-surface); border:1px solid var(--uj-border-cool); border-radius:var(--uj-radius-lg); padding:1.5rem;">
    <h2 style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--uj-teal); margin:0 0 0.75rem;">Four Core Traits</h2>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
      <?php foreach ([
        'Body'  => 'Physical strength and endurance',
        'Mind'  => 'Intellect and perception',
        'Speed' => 'Agility and reflexes',
        'Will'  => 'Courage and social grace',
      ] as $trait => $desc): ?>
      <div style="background:var(--uj-surface-2); border:1px solid var(--uj-border-light); border-radius:var(--uj-radius); padding:0.6rem 0.75rem;">
        <div style="font-family:'Cinzel',serif; font-size:0.8rem; font-weight:700; color:var(--uj-amber-light); margin-bottom:0.15rem;"><?= $trait ?></div>
        <div style="font-size:0.8rem; color:var(--uj-text-dim); line-height:1.3;"><?= $desc ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
