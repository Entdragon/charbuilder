<?php
/**
 * The Library of Calabria — main landing page.
 * Served at / (root)
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

cg_session_start();

$p = cg_prefix();

// Fetch recent published blog posts + both game counts in one round-trip (UNION ALL)
$blogPosts = [];
$icCount   = 0;
$ujCount   = 0;
try {
    $blogPosts = cg_query("
        SELECT id, title, slug, excerpt, published_at
        FROM `{$p}loc_blog_posts`
        WHERE is_published = 1
        ORDER BY published_at DESC
        LIMIT 5
    ");

    $counts = cg_query("
        SELECT 'ic_species'   AS k, COUNT(*) AS n FROM `{$p}customtables_table_species`  WHERE published=1
        UNION ALL SELECT 'ic_careers',  COUNT(*) FROM `{$p}customtables_table_careers`  WHERE published=1
        UNION ALL SELECT 'ic_gifts',    COUNT(*) FROM `{$p}customtables_table_gifts`    WHERE published=1
        UNION ALL SELECT 'uj_species',  COUNT(*) FROM `{$p}uj_species`
        UNION ALL SELECT 'uj_careers',  COUNT(*) FROM `{$p}uj_careers`
        UNION ALL SELECT 'uj_gifts',    COUNT(*) FROM `{$p}uj_gifts`
    ");
    $cm = [];
    foreach ($counts as $r) { $cm[$r['k']] = (int)$r['n']; }
    $icCount = ($cm['ic_species'] ?? 0) + ($cm['ic_careers'] ?? 0) + ($cm['ic_gifts'] ?? 0);
    $ujCount = ($cm['uj_species'] ?? 0) + ($cm['uj_careers'] ?? 0) + ($cm['uj_gifts'] ?? 0);
} catch (Throwable) { }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>The Library of Calabria</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap">
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    :root {
      --gold:       #c9a84c;
      --gold-light: #e5c97a;
      --gold-dark:  #a8822a;
      --teal:       #4bbfd8;
      --teal-light: #7dd6e8;
      --teal-dark:  #2d9bb5;
      --bg:         #12100e;
      --surface:    #1a1714;
      --surface-2:  #242019;
      --surface-3:  #2d2820;
      --border:     rgba(201,168,76,0.18);
      --text:       #e8dcc4;
      --text-muted: #9a8a6a;
      --text-dim:   #5a4f3a;
      --radius:     8px;
      --radius-lg:  14px;
    }

    html, body {
      margin:      0;
      min-height:  100%;
      font-family: 'Crimson Pro', Georgia, serif;
      background:  var(--bg);
      color:       var(--text);
      font-size:   16px;
    }

    body {
      background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 2L58 30L30 58L2 30Z' fill='none' stroke='%23c9a84c' stroke-width='0.5' stroke-opacity='0.07'/%3E%3C/svg%3E");
    }

    a { color: var(--gold); text-decoration: none; }
    a:hover { color: var(--gold-light); }

    /* ── Page wrapper ────────────────────────────────────────────────── */
    .page {
      max-width: 1100px;
      margin:    0 auto;
      padding:   3rem 2rem 4rem;
    }

    /* ── Site header ─────────────────────────────────────────────────── */
    .site-header {
      text-align:   center;
      margin-bottom:3rem;
      padding-bottom:2rem;
      border-bottom: 1px solid rgba(201,168,76,0.15);
      position:     relative;
    }

    .site-header::before {
      content:  '';
      display:  block;
      width:    60px;
      height:   2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
      margin:   0 auto 1.5rem;
    }

    .site-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      2.5rem;
      font-weight:    900;
      color:          var(--gold-light);
      letter-spacing: 0.06em;
      margin:         0 0 0.4rem;
      line-height:    1.1;
    }

    .site-subtitle {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.75rem;
      font-weight:    400;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color:          var(--text-dim);
      margin:         0;
    }

    /* ── Game tiles ──────────────────────────────────────────────────── */
    .game-tiles {
      display:   grid;
      gap:       1.25rem;
      grid-template-columns: 1fr 1fr;
      margin-bottom: 2.5rem;
    }

    @media (max-width: 680px) { .game-tiles { grid-template-columns: 1fr; } }

    .game-tile {
      display:        block;
      background:     var(--surface);
      border-radius:  var(--radius-lg);
      padding:        2.5rem 2rem;
      text-decoration:none;
      color:          inherit;
      transition:     transform 0.25s, box-shadow 0.25s, border-color 0.25s;
      position:       relative;
      overflow:       hidden;
    }

    .game-tile::before {
      content:  '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height:   4px;
    }

    .game-tile:hover {
      transform:  translateY(-4px);
      box-shadow: 0 12px 40px rgba(0,0,0,0.6);
    }

    .tile-ic {
      border: 1px solid rgba(201,168,76,0.22);
    }

    .tile-ic::before { background: linear-gradient(90deg, var(--gold-dark), var(--gold), var(--gold-dark)); }
    .tile-ic:hover   { border-color: rgba(201,168,76,0.45); }

    .tile-uj {
      border: 1px solid rgba(75,191,216,0.2);
    }

    .tile-uj::before { background: linear-gradient(90deg, var(--teal-dark), var(--teal), var(--teal-dark)); }
    .tile-uj:hover   { border-color: rgba(75,191,216,0.4); box-shadow: 0 12px 40px rgba(0,0,0,0.6), 0 0 40px rgba(75,191,216,0.05); }

    .tile-icon {
      font-size:     2rem;
      margin-bottom: 0.75rem;
      line-height:   1;
    }

    .tile-name {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.6rem;
      font-weight:    700;
      letter-spacing: 0.04em;
      margin:         0 0 0.35rem;
      line-height:    1.1;
    }

    .tile-ic .tile-name { color: var(--gold-light); }
    .tile-uj .tile-name { color: var(--teal-light); }

    .tile-tagline {
      font-size:   1rem;
      color:       var(--text-muted);
      font-style:  italic;
      margin:      0 0 1.25rem;
      line-height: 1.4;
    }

    .tile-stats {
      display:  flex;
      gap:      0.75rem;
      flex-wrap:wrap;
    }

    .tile-stat {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.68rem;
      font-weight:    600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      padding:        0.3rem 0.75rem;
      border-radius:  20px;
    }

    .tile-ic .tile-stat { background:rgba(201,168,76,0.1); color:var(--gold); border:1px solid rgba(201,168,76,0.2); }
    .tile-uj .tile-stat { background:rgba(75,191,216,0.1); color:var(--teal); border:1px solid rgba(75,191,216,0.2); }

    .tile-cta {
      display:      inline-flex;
      align-items:  center;
      gap:          0.4rem;
      margin-top:   1.5rem;
      font-family:  'Cinzel', Georgia, serif;
      font-size:    0.75rem;
      font-weight:  700;
      letter-spacing:0.08em;
      text-transform:uppercase;
      padding:      0.55rem 1.25rem;
      border-radius:6px;
      transition:   background 0.2s, color 0.2s;
    }

    .tile-ic .tile-cta  { background:rgba(201,168,76,0.15); color:var(--gold);  border:1px solid rgba(201,168,76,0.3); }
    .tile-ic:hover .tile-cta { background:var(--gold); color:var(--bg); }
    .tile-uj .tile-cta  { background:rgba(75,191,216,0.1);  color:var(--teal); border:1px solid rgba(75,191,216,0.25); }
    .tile-uj:hover .tile-cta { background:var(--teal); color:var(--bg); }

    /* ── Builder tile ────────────────────────────────────────────────── */
    .builder-strip {
      background:    var(--surface);
      border:        1px solid rgba(201,168,76,0.14);
      border-radius: var(--radius-lg);
      padding:       1.5rem 2rem;
      display:       flex;
      align-items:   center;
      gap:           1.5rem;
      margin-bottom: 2.5rem;
    }

    @media (max-width:600px) { .builder-strip { flex-direction:column; text-align:center; } }

    .builder-strip-text h3 {
      font-family:    'Cinzel', serif;
      font-size:      1rem;
      font-weight:    700;
      color:          var(--gold-light);
      letter-spacing: 0.06em;
      margin:         0 0 0.25rem;
      text-transform: uppercase;
    }

    .builder-strip-text p {
      font-size:   0.95rem;
      color:       var(--text-muted);
      margin:      0;
      line-height: 1.5;
    }

    .builder-cta {
      margin-left:   auto;
      display:       inline-block;
      background:    linear-gradient(135deg, var(--gold-dark), var(--gold) 50%, var(--gold-dark));
      color:         var(--bg);
      font-family:   'Cinzel', serif;
      font-size:     0.78rem;
      font-weight:   700;
      letter-spacing:0.08em;
      text-transform:uppercase;
      padding:       0.7rem 1.75rem;
      border-radius: 6px;
      text-decoration:none;
      white-space:   nowrap;
      transition:    opacity 0.2s;
    }

    .builder-cta:hover { opacity: 0.85; color: var(--bg); }

    /* ── Blog section ────────────────────────────────────────────────── */
    .blog-section { margin-top: 1rem; }

    .section-heading {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.7rem;
      font-weight:    700;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color:          var(--text-dim);
      margin:         0 0 1rem;
      padding-bottom: 0.5rem;
      border-bottom:  1px solid rgba(201,168,76,0.1);
      display:        flex;
      align-items:    center;
      justify-content:space-between;
    }

    .post-card {
      background:    var(--surface);
      border:        1px solid rgba(154,138,106,0.15);
      border-radius: var(--radius);
      padding:       1.1rem 1.4rem;
      margin-bottom: 0.75rem;
      transition:    border-color 0.2s;
    }

    .post-card:hover { border-color: rgba(201,168,76,0.25); }

    .post-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1rem;
      font-weight:    700;
      color:          var(--gold-light);
      letter-spacing: 0.03em;
      margin:         0 0 0.3rem;
    }

    .post-meta {
      font-size:  0.8rem;
      color:      var(--text-dim);
      margin:     0 0 0.5rem;
    }

    .post-excerpt {
      font-size:   0.95rem;
      color:       var(--text-muted);
      line-height: 1.55;
      margin:      0;
    }

    .no-posts {
      font-size:   0.95rem;
      color:       var(--text-dim);
      font-style:  italic;
      padding:     1rem 0;
    }

    /* ── Footer ──────────────────────────────────────────────────────── */
    .site-footer {
      text-align:   center;
      margin-top:   3rem;
      padding-top:  1.5rem;
      border-top:   1px solid rgba(201,168,76,0.1);
      font-size:    0.8rem;
      color:        var(--text-dim);
    }
  </style>
</head>
<body>
<div class="page">

  <header class="site-header">
    <h1 class="site-title">The Library of Calabria</h1>
    <p class="site-subtitle">Ironclaw &amp; Urban Jungle Reference</p>
  </header>

  <div class="game-tiles">

    <a href="/ic" class="game-tile tile-ic">
      <div class="tile-icon">✦</div>
      <p class="tile-name">Ironclaw</p>
      <p class="tile-tagline">Anthropomorphic fantasy in a rich, detailed world — nations, magic, and adventure</p>
      <div class="tile-stats">
        <span class="tile-stat">Species &amp; Careers</span>
        <span class="tile-stat">703 Gifts</span>
        <span class="tile-stat">Equipment</span>
      </div>
      <div class="tile-cta">Browse Reference →</div>
    </a>

    <a href="/uj" class="game-tile tile-uj">
      <div class="tile-icon">◈</div>
      <p class="tile-name">Urban Jungle</p>
      <p class="tile-tagline">1920s–1940s anthropomorphic noir — crime, mystery, and moral ambiguity</p>
      <div class="tile-stats">
        <span class="tile-stat">Species &amp; Types</span>
        <span class="tile-stat">Careers</span>
        <span class="tile-stat">Skills &amp; Gifts</span>
      </div>
      <div class="tile-cta">Browse Reference →</div>
    </a>

  </div>

  <div class="builder-strip">
    <div class="builder-strip-text">
      <h3>Character Generator</h3>
      <p>Build and save Ironclaw characters with the full interactive character creator.</p>
    </div>
    <a href="/builder" class="builder-cta">✦ &nbsp;Open Builder</a>
  </div>

  <div class="builder-strip" style="border-color:rgba(75,191,216,0.2); margin-top:-1.5rem;">
    <div class="builder-strip-text">
      <h3 style="color:var(--teal);">Urban Jungle Builder</h3>
      <p>Build and save Urban Jungle characters — species, type, career, skills, and battle stats.</p>
    </div>
    <a href="/uj/builder" class="builder-cta" style="background:linear-gradient(135deg,#1a6a7a,#2bb8d4 50%,#1a6a7a);">⚙ &nbsp;Open Builder</a>
  </div>

  <section class="blog-section">
    <p class="section-heading">
      Updates
      <?php if (cg_is_logged_in()): ?>
        <a href="/blog-admin" style="font-size:0.75rem; color:var(--gold-dark); font-weight:600;">+ New Post</a>
      <?php endif; ?>
    </p>

    <?php if (empty($blogPosts)): ?>
      <p class="no-posts">No updates yet.</p>
    <?php else: ?>
      <?php foreach ($blogPosts as $post): ?>
        <div class="post-card">
          <p class="post-title"><?= htmlspecialchars($post['title']) ?></p>
          <p class="post-meta"><?= $post['published_at'] ? date('j F Y', strtotime($post['published_at'])) : '' ?></p>
          <?php if ($post['excerpt']): ?>
            <p class="post-excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <footer class="site-footer">
    The Library of Calabria — Ironclaw &amp; Urban Jungle are trademarks of Sanguine Productions
  </footer>

</div>
</body>
</html>
