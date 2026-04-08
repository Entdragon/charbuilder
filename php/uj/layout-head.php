<?php
/**
 * Urban Jungle — shared layout header.
 * Expects: $pageTitle (string), $activeNav (string), $ujCounts (array)
 */

// Fetch nav counts once if not already set
if (!isset($ujCounts)) {
    $ujCounts = [];
    try {
        $p = cg_prefix();
        foreach (['species','types','careers','skills','gifts','soaks','attacks','items'] as $t) {
            $row = cg_query_one("SELECT COUNT(*) AS n FROM `{$p}uj_{$t}` WHERE published = 1");
            $ujCounts[$t] = (int)($row['n'] ?? 0);
        }
    } catch (Throwable) { }
}

$nt = fn(string $k): string => $ujCounts[$k] ? ' <span class="nav-count">' . $ujCounts[$k] . '</span>' : '';
$na = fn(string $k): string => ($activeNav ?? '') === $k ? ' active' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Urban Jungle') ?> — Urban Jungle</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap">
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    /* ── UJ colour palette — deep navy + amber/teal neon ────────────── */
    :root {
      --uj-bg:           #0d1117;
      --uj-surface:      #161c2a;
      --uj-surface-2:    #1c2438;
      --uj-surface-3:    #253050;
      --uj-border:       rgba(244,166,34,0.22);
      --uj-border-light: rgba(244,166,34,0.1);
      --uj-border-cool:  rgba(139,172,200,0.15);

      --uj-amber:        #f4a622;
      --uj-amber-light:  #f9c760;
      --uj-amber-dark:   #c8820a;
      --uj-amber-glow:   rgba(244,166,34,0.08);
      --uj-amber-border: rgba(244,166,34,0.25);

      --uj-teal:         #2dd4bf;
      --uj-teal-dark:    #0d9488;
      --uj-teal-glow:    rgba(45,212,191,0.08);
      --uj-teal-border:  rgba(45,212,191,0.25);

      --uj-text:         #dce8f5;
      --uj-text-muted:   #8bacc8;
      --uj-text-dim:     #4a6888;

      --uj-error:        #f87171;
      --uj-success:      #34d399;

      --uj-nav-bg:       #0a0e18;
      --uj-nav-border:   rgba(244,166,34,0.12);
      --uj-nav-width:    210px;
      --uj-header-h:     56px;

      --uj-radius:       6px;
      --uj-radius-lg:    10px;
    }

    html, body {
      margin:      0;
      height:      100%;
      font-family: 'Crimson Pro', Georgia, serif;
      background:  var(--uj-bg);
      color:       var(--uj-text);
      font-size:   16px;
    }

    body {
      /* Subtle hex-grid pattern in cool navy */
      background-image: url("data:image/svg+xml,%3Csvg width='60' height='52' viewBox='0 0 60 52' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 2L58 17v18L30 50 2 35V17z' fill='none' stroke='%232dd4bf' stroke-width='0.4' stroke-opacity='0.06'/%3E%3C/svg%3E");
    }

    a { color: var(--uj-amber); text-decoration: none; }
    a:hover { color: var(--uj-amber-light); }

    /* ── Site shell ───────────────────────────────────────────────────── */
    #uj-wrapper {
      display:        flex;
      flex-direction: column;
      max-width:      1300px;
      margin:         1.5rem auto;
      border:         2px solid var(--uj-border);
      border-radius:  var(--uj-radius-lg);
      background:     var(--uj-bg);
      box-shadow:     0 8px 40px rgba(0,0,0,0.7), 0 0 0 1px rgba(244,166,34,0.06);
      min-height:     calc(100vh - 3rem);
      overflow:       hidden;
    }

    /* ── Header ───────────────────────────────────────────────────────── */
    #uj-header {
      background:    var(--uj-surface);
      border-bottom: 1px solid var(--uj-border);
      box-shadow:    0 2px 16px rgba(0,0,0,0.5);
      display:       flex;
      align-items:   center;
      gap:           1rem;
      padding:       0 2em;
      height:        var(--uj-header-h);
      flex-shrink:   0;
      position:      relative;
      z-index:       100;
    }

    #uj-header::before {
      content:  '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height:   3px;
      background: linear-gradient(90deg, transparent, var(--uj-teal), var(--uj-amber), var(--uj-teal), transparent);
    }

    #uj-header .site-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.25rem;
      font-weight:    700;
      color:          var(--uj-amber-light);
      letter-spacing: 0.05em;
      line-height:    1.1;
    }

    #uj-header .site-title-sub {
      display:        block;
      font-size:      0.6rem;
      color:          var(--uj-text-dim);
      letter-spacing: 0.16em;
      text-transform: uppercase;
      font-family:    'Cinzel', Georgia, serif;
      font-weight:    400;
    }

    #uj-header .header-sep {
      flex: 1;
    }

    #uj-header .header-search {
      display:       flex;
      align-items:   center;
      background:    var(--uj-surface-2);
      border:        1px solid var(--uj-border-cool);
      border-radius: var(--uj-radius);
      padding:       0.25rem 0.75rem;
      gap:           0.5rem;
    }

    #uj-header .header-search input {
      background:  transparent;
      border:      none;
      outline:     none;
      color:       var(--uj-text);
      font-family: 'Crimson Pro', Georgia, serif;
      font-size:   0.9rem;
      width:       180px;
    }

    #uj-header .header-search input::placeholder { color: var(--uj-text-dim); }

    #uj-header .header-search svg { color: var(--uj-text-dim); flex-shrink: 0; }

    /* ── Body split ───────────────────────────────────────────────────── */
    #uj-body {
      display:    flex;
      flex:       1;
      min-height: 0;
    }

    /* ── Sidebar nav ──────────────────────────────────────────────────── */
    #uj-nav {
      width:        var(--uj-nav-width);
      min-width:    var(--uj-nav-width);
      background:   var(--uj-nav-bg);
      border-right: 1px solid var(--uj-nav-border);
      padding:      1rem 0.6rem;
      overflow-y:   auto;
      flex-shrink:  0;
    }

    .nav-group-label {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.62rem;
      font-weight:    600;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color:          var(--uj-text-dim);
      padding:        0.9rem 0.6rem 0.3rem;
    }

    .nav-divider {
      height:     1px;
      background: var(--uj-nav-border);
      margin:     0.5rem 0.25rem;
    }

    .nav-item {
      display:         flex;
      align-items:     center;
      justify-content: space-between;
      background:      var(--uj-surface-2);
      border:          1px solid rgba(139,172,200,0.1);
      border-left:     3px solid transparent;
      border-radius:   var(--uj-radius);
      margin-bottom:   4px;
      padding:         8px 12px;
      font-family:     'Cinzel', Georgia, serif;
      font-size:       0.78rem;
      font-weight:     600;
      letter-spacing:  0.03em;
      color:           var(--uj-text-muted);
      text-decoration: none;
      transition:      background 0.2s, color 0.2s, border-left-color 0.2s;
    }

    .nav-item:hover {
      background:        rgba(244,166,34,0.08);
      color:             var(--uj-amber-light);
      border-left-color: var(--uj-amber);
    }

    .nav-item.active {
      color:             var(--uj-amber);
      border-left-color: var(--uj-amber);
      background:        rgba(244,166,34,0.08);
    }

    .nav-count {
      font-family:    'Crimson Pro', Georgia, serif;
      font-size:      0.75rem;
      font-weight:    400;
      color:          var(--uj-text-dim);
      background:     rgba(139,172,200,0.1);
      border-radius:  10px;
      padding:        1px 6px;
      letter-spacing: 0;
    }

    .nav-item.active .nav-count { color: var(--uj-amber-dark); }

    .nav-item-back {
      color:           var(--uj-teal);
      border-color:    rgba(45,212,191,0.12);
      background:      rgba(45,212,191,0.04);
      border-left-color: transparent;
    }

    .nav-item-back:hover {
      background:        rgba(45,212,191,0.1);
      color:             var(--uj-teal);
      border-left-color: var(--uj-teal);
    }

    /* ── Main content ─────────────────────────────────────────────────── */
    #uj-content {
      flex:      1;
      min-width: 0;
      padding:   2rem 2.5rem;
      overflow-y:auto;
    }

    /* ── Page header ──────────────────────────────────────────────────── */
    .page-header {
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--uj-border-light);
    }

    .page-header h1 {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.7rem;
      font-weight:    700;
      color:          var(--uj-amber-light);
      letter-spacing: 0.04em;
      margin:         0 0 0.35rem;
    }

    .page-header p {
      color:     var(--uj-text-muted);
      font-size: 1rem;
      margin:    0;
    }

    .page-header .header-row {
      display:     flex;
      align-items: flex-end;
      gap:         1rem;
      flex-wrap:   wrap;
    }

    .page-header .header-row h1 { margin-bottom: 0; }

    /* ── Search / filter bar ──────────────────────────────────────────── */
    .filter-bar {
      display:     flex;
      gap:         0.75rem;
      align-items: center;
      flex-wrap:   wrap;
      margin-bottom: 1.5rem;
    }

    .filter-search {
      display:       flex;
      align-items:   center;
      background:    var(--uj-surface-2);
      border:        1px solid var(--uj-border-cool);
      border-radius: var(--uj-radius);
      padding:       0.4rem 0.9rem;
      gap:           0.5rem;
      flex:          1;
      min-width:     180px;
      max-width:     320px;
    }

    .filter-search input {
      background:  transparent;
      border:      none;
      outline:     none;
      color:       var(--uj-text);
      font-family: 'Crimson Pro', Georgia, serif;
      font-size:   1rem;
      width:       100%;
    }

    .filter-search input::placeholder { color: var(--uj-text-dim); }

    .filter-pills {
      display:  flex;
      gap:      0.4rem;
      flex-wrap:wrap;
    }

    .filter-pill {
      padding:         0.3rem 0.9rem;
      border-radius:   20px;
      font-family:     'Cinzel', Georgia, serif;
      font-size:       0.72rem;
      font-weight:     600;
      letter-spacing:  0.04em;
      text-transform:  uppercase;
      border:          1px solid var(--uj-border-cool);
      color:           var(--uj-text-muted);
      background:      var(--uj-surface-2);
      text-decoration: none;
      transition:      all 0.2s;
      cursor:          pointer;
    }

    .filter-pill:hover {
      border-color: var(--uj-amber-border);
      color:        var(--uj-amber);
      background:   var(--uj-amber-glow);
    }

    .filter-pill.active {
      border-color: var(--uj-amber);
      color:        var(--uj-bg);
      background:   var(--uj-amber);
    }

    /* ── Cards grid ───────────────────────────────────────────────────── */
    .cards-grid {
      display:               grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap:                   1rem;
    }

    .card {
      background:    var(--uj-surface);
      border:        1px solid var(--uj-border-cool);
      border-radius: var(--uj-radius-lg);
      padding:       1.25rem;
      transition:    border-color 0.2s, box-shadow 0.2s, transform 0.2s;
      display:       flex;
      flex-direction:column;
      gap:           0.5rem;
    }

    .card:hover {
      border-color: var(--uj-amber-border);
      box-shadow:   0 4px 20px rgba(0,0,0,0.4), 0 0 0 1px rgba(244,166,34,0.1);
      transform:    translateY(-2px);
    }

    .card a { color: inherit; display: block; }
    .card a:hover { color: inherit; }

    .card-name {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1rem;
      font-weight:    700;
      color:          var(--uj-amber-light);
      letter-spacing: 0.03em;
      margin:         0;
      line-height:    1.25;
    }

    .card-subtitle {
      font-size:  0.85rem;
      color:      var(--uj-teal);
      margin:     0;
      font-style: italic;
    }

    .card-desc {
      font-size:   0.9rem;
      color:       var(--uj-text-muted);
      margin:      0;
      line-height: 1.4;
      display:     -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .card-divider {
      height:     1px;
      background: var(--uj-border-light);
      margin:     0.25rem 0;
    }

    .card-tags {
      display:  flex;
      gap:      0.3rem;
      flex-wrap:wrap;
      margin:   0;
    }

    .tag {
      font-family:  'Cinzel', Georgia, serif;
      font-size:    0.65rem;
      font-weight:  600;
      letter-spacing:0.05em;
      text-transform:uppercase;
      border-radius: 3px;
      padding:      2px 7px;
    }

    .tag-skill {
      background: rgba(45,212,191,0.12);
      color:      var(--uj-teal);
      border:     1px solid rgba(45,212,191,0.2);
    }

    .tag-gift {
      background: rgba(244,166,34,0.1);
      color:      var(--uj-amber);
      border:     1px solid rgba(244,166,34,0.2);
    }

    .tag-soak {
      background: rgba(139,172,200,0.1);
      color:      var(--uj-text-muted);
      border:     1px solid rgba(139,172,200,0.15);
    }

    .tag-basic    { background:rgba(52,211,153,0.1); color:#34d399; border:1px solid rgba(52,211,153,0.2); }
    .tag-advanced { background:rgba(168,85,247,0.1); color:#c084fc; border:1px solid rgba(168,85,247,0.2); }
    .tag-gear     { background:rgba(251,191,36,0.08); color:#fbbf24; border:1px solid rgba(251,191,36,0.15); }

    /* ── Tables ───────────────────────────────────────────────────────── */
    .uj-table {
      width:           100%;
      border-collapse: collapse;
      font-size:       0.92rem;
    }

    .uj-table th {
      background:     var(--uj-surface-2);
      color:          var(--uj-amber);
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.7rem;
      font-weight:    700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      padding:        0.65rem 0.9rem;
      text-align:     left;
      border-bottom:  2px solid var(--uj-border);
      white-space:    nowrap;
    }

    .uj-table td {
      padding:       0.55rem 0.9rem;
      border-bottom: 1px solid var(--uj-border-light);
      vertical-align:top;
      line-height:   1.4;
    }

    .uj-table tr:hover td { background: rgba(244,166,34,0.03); }

    .uj-table .td-name {
      font-family:    'Cinzel', Georgia, serif;
      font-weight:    600;
      font-size:      0.85rem;
      color:          var(--uj-amber-light);
      letter-spacing: 0.02em;
      white-space:    nowrap;
    }

    .uj-table .td-name a { color: var(--uj-amber-light); }
    .uj-table .td-name a:hover { color: var(--uj-amber); }

    .uj-table .td-muted { color: var(--uj-text-muted); font-size: 0.88rem; }
    .uj-table .td-dim   { color: var(--uj-text-dim);   font-size: 0.85rem; }

    .table-section-header td {
      background:     var(--uj-surface-2);
      color:          var(--uj-teal);
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.75rem;
      font-weight:    700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding:        0.5rem 0.9rem;
      border-top:     1px solid var(--uj-teal-border);
      border-bottom:  1px solid var(--uj-teal-border);
    }

    /* ── Detail page ──────────────────────────────────────────────────── */
    .detail-layout {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 2rem;
      align-items: start;
    }

    @media (max-width: 900px) {
      .detail-layout { grid-template-columns: 1fr; }
    }

    .detail-body {}

    .detail-name {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      2rem;
      font-weight:    700;
      color:          var(--uj-amber-light);
      letter-spacing: 0.04em;
      margin:         0 0 0.25rem;
    }

    .detail-subtitle {
      font-size:  1.1rem;
      color:      var(--uj-teal);
      font-style: italic;
      margin:     0 0 1rem;
    }

    .detail-desc {
      font-size:   1.05rem;
      color:       var(--uj-text);
      line-height: 1.65;
      margin:      0 0 1.5rem;
    }

    .detail-section {
      margin-bottom: 1.5rem;
    }

    .detail-section-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.72rem;
      font-weight:    700;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color:          var(--uj-text-dim);
      margin:         0 0 0.6rem;
      padding-bottom: 0.35rem;
      border-bottom:  1px solid var(--uj-border-light);
    }

    .detail-sidebar {
      position: sticky;
      top: 1rem;
    }

    .sidebar-card {
      background:    var(--uj-surface);
      border:        1px solid var(--uj-border-cool);
      border-radius: var(--uj-radius-lg);
      padding:       1.25rem;
      margin-bottom: 1rem;
    }

    .sidebar-card-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.72rem;
      font-weight:    700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color:          var(--uj-amber);
      margin:         0 0 0.75rem;
      padding-bottom: 0.5rem;
      border-bottom:  1px solid var(--uj-border);
    }

    .trait-list {
      list-style:   none;
      padding:      0;
      margin:       0;
      display:      flex;
      flex-direction:column;
      gap:          0.4rem;
    }

    .trait-list li {
      font-size:   0.95rem;
      color:       var(--uj-text);
      padding:     0.35rem 0.6rem;
      background:  var(--uj-surface-2);
      border-radius: var(--uj-radius);
      border-left: 3px solid var(--uj-teal);
      line-height: 1.3;
    }

    .trait-list li small {
      display:    block;
      color:      var(--uj-text-dim);
      font-size:  0.78rem;
      margin-top: 0.15rem;
    }

    .trait-list li.gift-item { border-left-color: var(--uj-amber); }
    .trait-list li.soak-item { border-left-color: var(--uj-text-dim); }

    .gear-text {
      font-size:   0.9rem;
      color:       var(--uj-text-muted);
      line-height: 1.5;
      margin:      0;
    }

    /* ── Breadcrumb ───────────────────────────────────────────────────── */
    .breadcrumb {
      font-size:     0.82rem;
      color:         var(--uj-text-dim);
      margin-bottom: 1.25rem;
      display:       flex;
      align-items:   center;
      gap:           0.4rem;
    }

    .breadcrumb a { color: var(--uj-amber-dark); }
    .breadcrumb a:hover { color: var(--uj-amber); }

    /* ── Home stats ───────────────────────────────────────────────────── */
    .home-stats {
      display:               grid;
      grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
      gap:                   0.75rem;
      margin-bottom:         2rem;
    }

    .stat-card {
      background:    var(--uj-surface);
      border:        1px solid var(--uj-border-cool);
      border-radius: var(--uj-radius-lg);
      padding:       1.25rem 1rem;
      text-align:    center;
      text-decoration:none;
      display:       block;
      transition:    border-color 0.2s, transform 0.2s;
    }

    .stat-card:hover {
      border-color: var(--uj-amber-border);
      transform:    translateY(-2px);
    }

    .stat-n {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      2rem;
      font-weight:    700;
      color:          var(--uj-teal);
      line-height:    1;
      margin-bottom:  0.25rem;
    }

    .stat-l {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.68rem;
      font-weight:    600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color:          var(--uj-text-dim);
    }

    /* ── Utility ──────────────────────────────────────────────────────── */
    .text-amber  { color: var(--uj-amber); }
    .text-teal   { color: var(--uj-teal); }
    .text-muted  { color: var(--uj-text-muted); }
    .text-dim    { color: var(--uj-text-dim); }

    .mt-0 { margin-top: 0; }
    .mb-0 { margin-bottom: 0; }

    .hidden { display: none !important; }

    /* ── Skill info list (skills page) ────────────────────────────────── */
    .skill-row {
      background:    var(--uj-surface);
      border:        1px solid var(--uj-border-cool);
      border-radius: var(--uj-radius-lg);
      padding:       1.1rem 1.25rem;
      margin-bottom: 0.75rem;
      display:       grid;
      grid-template-columns: 180px 1fr 200px;
      gap:           1.25rem;
      align-items:   start;
    }

    @media (max-width: 780px) { .skill-row { grid-template-columns: 1fr; } }

    .skill-row-name {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.95rem;
      font-weight:    700;
      color:          var(--uj-amber-light);
      margin:         0 0 0.25rem;
    }

    .skill-row-trait {
      font-size:  0.78rem;
      color:      var(--uj-teal);
      font-style: italic;
    }

    .skill-row-desc {
      font-size:   0.9rem;
      color:       var(--uj-text-muted);
      line-height: 1.45;
      margin:      0;
    }

    .skill-row-favs {
      font-size:   0.82rem;
      color:       var(--uj-text-dim);
      line-height: 1.4;
    }

    .skill-row-favs strong {
      display:        block;
      color:          var(--uj-text-muted);
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.65rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      margin-bottom:  0.2rem;
    }

    /* ── Attacks category header ──────────────────────────────────────── */
    .attack-category {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1rem;
      font-weight:    700;
      color:          var(--uj-teal);
      letter-spacing: 0.06em;
      text-transform: uppercase;
      margin:         2rem 0 0.75rem;
      padding-bottom: 0.4rem;
      border-bottom:  2px solid var(--uj-teal-border);
    }

    .attack-category:first-of-type { margin-top: 0; }

    /* ── Search page ──────────────────────────────────────────────────── */
    .search-form {
      display:  flex;
      gap:      0.75rem;
      margin-bottom: 2rem;
    }

    .search-input {
      flex:          1;
      background:    var(--uj-surface-2);
      border:        1px solid var(--uj-border);
      border-radius: var(--uj-radius);
      padding:       0.7rem 1rem;
      color:         var(--uj-text);
      font-family:   'Crimson Pro', Georgia, serif;
      font-size:     1.05rem;
      outline:       none;
      transition:    border-color 0.2s;
    }

    .search-input:focus { border-color: var(--uj-amber); }

    .search-btn {
      background:    var(--uj-amber);
      border:        none;
      border-radius: var(--uj-radius);
      color:         var(--uj-bg);
      font-family:   'Cinzel', Georgia, serif;
      font-size:     0.8rem;
      font-weight:   700;
      letter-spacing:0.06em;
      padding:       0 1.5rem;
      cursor:        pointer;
      transition:    background 0.2s;
    }

    .search-btn:hover { background: var(--uj-amber-light); }

    .search-group-label {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.7rem;
      font-weight:    700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color:          var(--uj-amber);
      margin:         1.5rem 0 0.5rem;
      padding-bottom: 0.3rem;
      border-bottom:  1px solid var(--uj-border);
    }

    .search-result {
      display:        flex;
      gap:            1rem;
      align-items:    flex-start;
      padding:        0.75rem 0;
      border-bottom:  1px solid var(--uj-border-light);
      text-decoration:none;
      color:          inherit;
      transition:     background 0.15s;
    }

    .search-result:hover { background: rgba(244,166,34,0.03); }

    .search-result-name {
      font-family:   'Cinzel', Georgia, serif;
      font-size:     0.9rem;
      font-weight:   700;
      color:         var(--uj-amber-light);
      margin:        0 0 0.2rem;
    }

    .search-result-desc {
      font-size:  0.88rem;
      color:      var(--uj-text-muted);
      margin:     0;
      line-height:1.4;
    }

    /* ── Responsive tweaks ────────────────────────────────────────────── */
    @media (max-width: 640px) {
      #uj-content { padding: 1.25rem; }
      .cards-grid { grid-template-columns: 1fr; }
      #uj-nav { display: none; }
    }
  </style>
</head>
<body>
<div id="uj-wrapper">

  <header id="uj-header">
    <a href="/uj" style="text-decoration:none;">
      <span class="site-title">
        Urban Jungle
        <span class="site-title-sub">Character Reference</span>
      </span>
    </a>
    <div class="header-sep"></div>
    <form class="header-search" action="/uj/search" method="get">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" placeholder="Search all rules…" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    </form>
  </header>

  <div id="uj-body">

    <nav id="uj-nav">
      <div class="nav-group-label">Characters</div>
      <a href="/uj/species"  class="nav-item<?= $na('species') ?>">Species <?= $nt('species') ?></a>
      <a href="/uj/types"    class="nav-item<?= $na('types') ?>">Types <?= $nt('types') ?></a>
      <a href="/uj/careers"  class="nav-item<?= $na('careers') ?>">Careers <?= $nt('careers') ?></a>

      <div class="nav-divider"></div>
      <div class="nav-group-label">Rules</div>
      <a href="/uj/skills"   class="nav-item<?= $na('skills') ?>">Skills <?= $nt('skills') ?></a>
      <a href="/uj/gifts"    class="nav-item<?= $na('gifts') ?>">Gifts <?= $nt('gifts') ?></a>
      <a href="/uj/soaks"    class="nav-item<?= $na('soaks') ?>">Soaks <?= $nt('soaks') ?></a>

      <div class="nav-divider"></div>
      <div class="nav-group-label">Equipment</div>
      <a href="/uj/attacks"  class="nav-item<?= $na('attacks') ?>">Attacks <?= $nt('attacks') ?></a>
      <a href="/uj/items"    class="nav-item<?= $na('items') ?>">Items <?= $nt('items') ?></a>

      <div class="nav-divider"></div>
      <a href="/uj/search"   class="nav-item<?= $na('search') ?>">🔍 Search</a>

      <div class="nav-divider"></div>
      <a href="/" class="nav-item nav-item-back">← Ironclaw</a>
    </nav>

    <main id="uj-content">
