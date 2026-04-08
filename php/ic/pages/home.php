<?php
$pageTitle = 'Ironclaw';
$activeNav = '';
require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <h1>Ironclaw</h1>
  <p>Anthropomorphic fantasy RPG — species, careers, gifts, skills &amp; equipment reference</p>
</div>

<div class="home-stats">
  <a href="/ic/species"   class="stat-card"><div class="stat-n"><?= $icCounts['species']   ?? '—' ?></div><div class="stat-l">Species</div></a>
  <a href="/ic/careers"   class="stat-card"><div class="stat-n"><?= $icCounts['careers']   ?? '—' ?></div><div class="stat-l">Careers</div></a>
  <a href="/ic/gifts"     class="stat-card"><div class="stat-n"><?= $icCounts['gifts']     ?? '—' ?></div><div class="stat-l">Gifts</div></a>
  <a href="/ic/skills"    class="stat-card"><div class="stat-n"><?= $icCounts['skills']    ?? '—' ?></div><div class="stat-l">Skills</div></a>
  <a href="/ic/equipment" class="stat-card"><div class="stat-n"><?= $icCounts['equipment'] ?? '—' ?></div><div class="stat-l">Equipment</div></a>
  <a href="/ic/weapons"   class="stat-card"><div class="stat-n"><?= $icCounts['weapons']   ?? '—' ?></div><div class="stat-l">Weapons</div></a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.5rem;">

  <div style="background:var(--ic-surface); border:1px solid var(--ic-border-warm); border-radius:var(--ic-radius-lg); padding:1.5rem;">
    <h2 style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--ic-gold); margin:0 0 0.75rem;">Building a Character</h2>
    <ol style="margin:0; padding-left:1.3rem; line-height:1.85; color:var(--ic-text-muted); font-size:0.92rem;">
      <li>Choose your <strong style="color:var(--ic-text);">Traits</strong> — two d8s, three d6s, one d4</li>
      <li>Choose a <a href="/ic/species">Species</a> — fill in your Species <a href="/ic/gifts">Gifts</a></li>
      <li>Choose a <a href="/ic/careers">Career</a> — fill in your Career <a href="/ic/gifts">Gifts</a></li>
      <li>Choose your <strong style="color:var(--ic-text);">Personality</strong> — grants a bonus d12</li>
      <li>Choose your <strong style="color:var(--ic-text);">Local Knowledge</strong> — ask your Game Host</li>
      <li>Add <strong style="color:var(--ic-text);">thirteen Marks</strong> to <a href="/ic/skills">Skills</a> — up to 3 per skill</li>
      <li>Choose your <a href="/ic/gifts">Gifts</a> — 3 Species, 3 Career, 3 of your choice</li>
      <li>Choose a <strong style="color:var(--ic-text);">Name</strong></li>
      <li>Choose a <strong style="color:var(--ic-text);">Motto</strong></li>
      <li>Choose a <strong style="color:var(--ic-text);">Goal</strong></li>
      <li>Choose your <a href="/ic/equipment">Equipment</a> — Career starting gear, plus additions</li>
      <li>Fill out the <strong style="color:var(--ic-text);">Battle Array</strong> — Initiative, Stride, Attacks, Defenses &amp; Soaks</li>
    </ol>
  </div>

  <div style="background:var(--ic-surface); border:1px solid var(--ic-border-warm); border-radius:var(--ic-radius-lg); padding:1.5rem;">
    <h2 style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--ic-emerald); margin:0 0 0.75rem;">Core Traits</h2>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
      <?php foreach ([
        'Body'  => 'Strength, endurance &amp; physical power',
        'Speed' => 'Agility, reflexes &amp; coordination',
        'Mind'  => 'Intellect, perception &amp; knowledge',
        'Will'  => 'Courage, conviction &amp; social grace',
      ] as $trait => $desc): ?>
      <div style="background:var(--ic-surface-2); border:1px solid var(--ic-border-light); border-radius:var(--ic-radius); padding:0.6rem 0.75rem;">
        <div style="font-family:'Cinzel',serif; font-size:0.8rem; font-weight:700; color:var(--ic-gold-light); margin-bottom:0.15rem;"><?= $trait ?></div>
        <div style="font-size:0.8rem; color:var(--ic-text-dim); line-height:1.3;"><?= $desc ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<div style="background:var(--ic-surface); border:1px solid var(--ic-border-warm); border-radius:var(--ic-radius-lg); padding:1.5rem;">
  <h2 style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--ic-gold); margin:0 0 0.75rem;">Gift Classes</h2>
  <p style="font-size:0.92rem; color:var(--ic-text-muted); line-height:1.6; margin:0 0 0.75rem;">
    Gifts represent special abilities beyond the reach of ordinary people. Each career grants a set of gifts, and additional gifts may be purchased with experience points.
  </p>
  <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
    <?php
    try {
      $p2 = cg_prefix();
      $classes = cg_query("SELECT id, ct_class_name FROM `{$p2}customtables_table_giftclass` ORDER BY id");
      foreach ($classes as $gc):
    ?>
      <span style="background:rgba(201,168,76,0.1); border:1px solid rgba(201,168,76,0.2); color:var(--ic-gold);
                   font-family:'Cinzel',serif; font-size:0.72rem; font-weight:600; letter-spacing:0.05em;
                   text-transform:uppercase; padding:0.3rem 0.8rem; border-radius:20px;">
        <?= htmlspecialchars($gc['ct_class_name']) ?>
      </span>
    <?php endforeach; } catch (Throwable) {} ?>
  </div>
</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
