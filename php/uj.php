<?php
/**
 * Urban Jungle Character Generator — placeholder page.
 *
 * Will grow into the full character builder as development progresses.
 * Accessible at /uj (dev) and /character-generator/uj/ (production).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Attempt to load trait counts to confirm tables exist.
$counts  = [];
$hasData = false;
try {
    $p  = cg_prefix();
    foreach (['uj_species' => 'species', 'uj_types' => 'types', 'uj_careers' => 'careers', 'uj_skills' => 'skills', 'uj_gifts' => 'gifts', 'uj_soaks' => 'soaks'] as $tbl => $label) {
        $row = cg_query_one("SELECT COUNT(*) AS n FROM `{$p}{$tbl}`");
        $counts[$label] = (int)($row['n'] ?? 0);
    }
    $hasData = array_sum($counts) > 0;
} catch (Throwable) {
    $hasData = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Urban Jungle — Character Generator</title>
<link rel="stylesheet" href="/assets/css/dist/core.css">
<style>
  body { background:#111; color:#ddd; font-family:'Segoe UI',sans-serif; margin:0; padding:2rem; }
  .uj-hero { max-width:760px; margin:0 auto; }
  h1 { font-size:2.2rem; color:#c9a84c; margin-bottom:0.25rem; }
  h1 span { color:#888; font-size:1rem; font-weight:400; vertical-align:middle; margin-left:0.5rem; }
  .uj-tagline { color:#999; font-size:1rem; margin-bottom:2rem; }
  .uj-status { border:1px solid #333; border-radius:6px; padding:1.25rem 1.5rem; background:#1a1a1a; margin-bottom:2rem; }
  .uj-status h2 { margin:0 0 0.75rem; font-size:1rem; color:#c9a84c; }
  .uj-counts { display:flex; gap:2rem; flex-wrap:wrap; }
  .uj-count  { text-align:center; }
  .uj-count-n { font-size:2rem; font-weight:700; color:#5dbf90; }
  .uj-count-l { font-size:0.78rem; color:#999; text-transform:uppercase; letter-spacing:0.07em; }
  .uj-missing { color:#e07060; font-size:0.88rem; }
  .uj-roadmap { border:1px solid #333; border-radius:6px; padding:1.25rem 1.5rem; background:#1a1a1a; }
  .uj-roadmap h2 { margin:0 0 0.75rem; font-size:1rem; color:#c9a84c; }
  .uj-roadmap ol { margin:0; padding-left:1.3rem; line-height:1.9; color:#ccc; font-size:0.9rem; }
  .uj-roadmap li.done { color:#5dbf90; }
  .uj-roadmap li.done::marker { content:"✓  "; }
  .back { display:inline-block; margin-top:2rem; color:#888; text-decoration:none; font-size:0.85rem; }
  .back:hover { color:#c9a84c; }
</style>
</head>
<body>
<div class="uj-hero">
  <h1>Urban Jungle <span>Character Generator</span></h1>
  <p class="uj-tagline">1920s anthropomorphic noir — powered by the Library of Calabria engine</p>

  <div class="uj-status">
    <h2>Database Status</h2>
    <?php if ($hasData): ?>
      <div class="uj-counts">
        <div class="uj-count">
          <div class="uj-count-n"><?= $counts['species'] ?></div>
          <div class="uj-count-l">Species</div>
        </div>
        <div class="uj-count">
          <div class="uj-count-n"><?= $counts['types'] ?></div>
          <div class="uj-count-l">Types</div>
        </div>
        <div class="uj-count">
          <div class="uj-count-n"><?= $counts['careers'] ?></div>
          <div class="uj-count-l">Careers</div>
        </div>
        <div class="uj-count">
          <div class="uj-count-n"><?= $counts['skills'] ?></div>
          <div class="uj-count-l">Skills</div>
        </div>
        <div class="uj-count">
          <div class="uj-count-n"><?= $counts['gifts'] ?></div>
          <div class="uj-count-l">Gifts</div>
        </div>
        <div class="uj-count">
          <div class="uj-count-n"><?= $counts['soaks'] ?></div>
          <div class="uj-count-l">Soaks</div>
        </div>
      </div>
    <?php else: ?>
      <p class="uj-missing">
        No data found. Visit the Admin panel and click <strong>UJ: Install Data</strong>
        to create the tables and load all Species, Types, Careers, and Skills.
      </p>
    <?php endif; ?>
  </div>

  <div class="uj-roadmap">
    <h2>Build Roadmap</h2>
    <ol>
      <li class="done">Schema design — uj_species, uj_types, uj_careers, uj_skills tables</li>
      <li class="done">Data extraction — all traits &amp; skills from book screenshots</li>
      <li class="done">AJAX endpoints — get/install for all four tables</li>
      <li class="done">Admin install button — one-click table creation + data load</li>
      <li>Character builder UI — trait picker (Species → Type → Career)</li>
      <li>Trait die assignment — pick 2 best (d8), 1 worst (d4), rest d6</li>
      <li>Skills panel — 14 skills, carry-over from trait selections</li>
      <li>Gifts panel — from Species (×2), Type (×1), Career (×2), Personality (×1)</li>
      <li>Soak &amp; Gear — auto-filled from Type + Career selections</li>
      <li>Print / export — character sheet layout matching the UJ sheet</li>
      <li>WordPress embed — iframe at /character-generator/uj/</li>
    </ol>
  </div>

  <a class="back" href="/">← Back to Ironclaw Generator</a>
</div>
</body>
</html>
