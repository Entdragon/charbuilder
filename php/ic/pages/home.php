<?php
$pageTitle = 'Ironclaw Reference';
$activeNav = '';
require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <h1>The Library of Calabria</h1>
  <p>Ironclaw rules reference — species, careers, gifts, skills, and equipment</p>
</div>

<div class="home-stats">
  <a href="/ic/species"   class="stat-card"><div class="stat-n"><?= $icCounts['species']   ?></div><div class="stat-l">Species</div></a>
  <a href="/ic/careers"   class="stat-card"><div class="stat-n"><?= $icCounts['careers']   ?></div><div class="stat-l">Careers</div></a>
  <a href="/ic/gifts"     class="stat-card"><div class="stat-n"><?= $icCounts['gifts']     ?></div><div class="stat-l">Gifts</div></a>
  <a href="/ic/skills"    class="stat-card"><div class="stat-n"><?= $icCounts['skills']    ?></div><div class="stat-l">Skills</div></a>
  <a href="/ic/equipment" class="stat-card"><div class="stat-n"><?= $icCounts['equipment'] ?></div><div class="stat-l">Equipment</div></a>
  <a href="/ic/weapons"   class="stat-card"><div class="stat-n"><?= $icCounts['weapons']   ?></div><div class="stat-l">Weapons</div></a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
  <div style="background:var(--ic-surface); border:1px solid var(--ic-border-warm); border-radius:var(--ic-radius-lg); padding:1.5rem;">
    <h2 style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--ic-gold); margin:0 0 0.75rem;">Quick Links</h2>
    <ul style="margin:0; padding-left:1.2rem; line-height:2.1; color:var(--ic-text-muted); font-size:0.95rem;">
      <li><a href="/ic/species">Browse all <?= $icCounts['species'] ?> species</a></li>
      <li><a href="/ic/careers">Browse all <?= $icCounts['careers'] ?> careers</a></li>
      <li><a href="/ic/gifts">Browse all <?= $icCounts['gifts'] ?> gifts</a></li>
      <li><a href="/ic/skills">View all <?= $icCounts['skills'] ?> skills</a></li>
      <li><a href="/ic/equipment">Equipment price list</a></li>
      <li><a href="/ic/weapons">Weapons &amp; armour</a></li>
      <li><a href="/ic/search">Search everything</a></li>
    </ul>
  </div>

  <div style="background:var(--ic-surface); border:1px solid var(--ic-border-warm); border-radius:var(--ic-radius-lg); padding:1.5rem;">
    <h2 style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--ic-emerald); margin:0 0 0.75rem;">Character Builder</h2>
    <p style="color:var(--ic-text-muted); font-size:0.95rem; line-height:1.6; margin:0 0 1rem;">Build and save your Ironclaw characters using the full interactive character generator.</p>
    <a href="/" style="display:inline-block; background:linear-gradient(135deg,var(--ic-gold-dark) 0%,var(--ic-gold) 50%,var(--ic-gold-dark) 100%); color:var(--ic-bg); font-family:'Cinzel',serif; font-size:0.8rem; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; padding:0.7rem 1.5rem; border-radius:var(--ic-radius); text-decoration:none;">
      ✦ &nbsp;Open Character Builder
    </a>
  </div>
</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
