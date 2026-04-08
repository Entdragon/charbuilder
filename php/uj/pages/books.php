<?php
$pageTitle = 'Books';
$activeNav = 'books';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();
$speciesCount = (int)(cg_query_one("SELECT COUNT(*) n FROM `{$p}uj_species` WHERE published=1")['n'] ?? 0);
$careerCount  = (int)(cg_query_one("SELECT COUNT(*) n FROM `{$p}uj_careers` WHERE published=1")['n'] ?? 0);
$giftCount    = (int)(cg_query_one("SELECT COUNT(*) n FROM `{$p}uj_gifts` WHERE published=1")['n'] ?? 0);
$skillCount   = (int)(cg_query_one("SELECT COUNT(*) n FROM `{$p}uj_skills` WHERE published=1")['n'] ?? 0);
?>

<div class="page-header">
  <h1>Urban Jungle Books</h1>
  <p>Sourcebooks, supplements, and adventures for Urban Jungle.</p>
</div>

<div style="background:var(--uj-surface); border:1px solid var(--uj-border-cool); border-radius:var(--uj-radius-lg);
            display:flex; gap:1.5rem; padding:1.5rem; align-items:flex-start;">

  <div style="flex:1; min-width:0;">
    <p style="font-family:'Cinzel',serif; font-size:1.15rem; font-weight:700; color:var(--uj-teal-light); letter-spacing:0.04em; margin:0 0 0.5rem;">
      Urban Jungle: Anthropomorphic Noir Role-Play
    </p>

    <p style="font-size:0.95rem; color:var(--uj-text-muted); line-height:1.65; margin:0 0 0.5rem;">
      The early 20th century of the United States was rife with fantastic change: from the rise of industry giants, to the great experiment of Prohibition, to the tragedy of the Great Depression, onto the dawn of the Atomic Age.
    </p>
    <p style="font-size:0.95rem; color:var(--uj-text-muted); line-height:1.65; margin:0 0 1rem;">
      A complete game in one volume, <em>Urban Jungle</em> makes you a player in an anthropomorphic world of pulp-adventure, hard-boiled crime, and film noir. You'll tangle with hardened gangsters, jaded debutantes, world-weary veterans, and all kinds of shady characters.
    </p>

    <div style="display:flex; gap:1.5rem; flex-wrap:wrap; margin:0 0 1.25rem;">
      <?php foreach (['Species' => $speciesCount, 'Careers' => $careerCount, 'Gifts' => $giftCount, 'Skills' => $skillCount] as $lbl => $n): ?>
      <div style="text-align:center;">
        <div style="font-family:'Cinzel',serif; font-size:1.3rem; font-weight:700; color:var(--uj-teal);"><?= $n ?></div>
        <div style="font-size:0.72rem; letter-spacing:0.08em; text-transform:uppercase; color:var(--uj-text-dim);"><?= $lbl ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
      <a href="/uj/books/urban-jungle"
         style="display:inline-flex; align-items:center; gap:0.4rem;
                background:rgba(75,191,216,0.06); border:1px solid rgba(75,191,216,0.2);
                color:var(--uj-text-muted); border-radius:var(--uj-radius);
                font-family:'Cinzel',serif; font-size:0.72rem; font-weight:700;
                letter-spacing:0.06em; text-transform:uppercase; padding:0.45rem 1rem;
                text-decoration:none;">
        View Contents →
      </a>
      <a href="https://www.drivethrurpg.com/en/product/194021/urban-jungle-anthropomorphic-noir-role-play"
         target="_blank" rel="noopener"
         style="display:inline-flex; align-items:center; gap:0.4rem;
                background:rgba(75,191,216,0.12); border:1px solid rgba(75,191,216,0.3);
                color:var(--uj-teal); border-radius:var(--uj-radius);
                font-family:'Cinzel',serif; font-size:0.72rem; font-weight:700;
                letter-spacing:0.06em; text-transform:uppercase; padding:0.45rem 1rem;
                text-decoration:none;">
        Buy on DriveThruRPG →
      </a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
