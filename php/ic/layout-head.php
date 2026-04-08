<?php
/**
 * Ironclaw info pages — shared layout header.
 * Expects: $pageTitle (string), $activeNav (string)
 */

// Fetch all nav counts in ONE query (UNION ALL) to minimise proxy round-trips
if (!isset($icCounts)) {
    $icCounts = [];
    try {
        $p = cg_prefix();
        $rows = cg_query("
            SELECT 'species'   AS t, COUNT(*) AS n FROM `{$p}customtables_table_species`   WHERE published=1
            UNION ALL
            SELECT 'careers',         COUNT(*)    FROM `{$p}customtables_table_careers`   WHERE published=1
            UNION ALL
            SELECT 'gifts',           COUNT(*)    FROM `{$p}customtables_table_gifts`     WHERE published=1
            UNION ALL
            SELECT 'skills',          COUNT(*)    FROM `{$p}customtables_table_skills`    WHERE published=1
            UNION ALL
            SELECT 'equipment',       COUNT(*)    FROM `{$p}customtables_table_equipment` WHERE published=1
            UNION ALL
            SELECT 'weapons',         COUNT(*)    FROM `{$p}customtables_table_weapons`   WHERE published=1
        ");
        foreach ($rows as $r) { $icCounts[$r['t']] = (int)$r['n']; }
    } catch (Throwable) { }
}

$nt = fn(string $k): string => ($icCounts[$k] ?? 0) > 0
    ? ' <span class="nav-count">' . $icCounts[$k] . '</span>'
    : '';
$na = fn(string $k): string => ($activeNav ?? '') === $k ? ' active' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Ironclaw') ?> — Library of Calabria</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap">
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    /* ── Ironclaw colour palette — dark parchment + gold ─────────────── */
    :root {
      --ic-bg:           #1a1714;
      --ic-surface:      #242019;
      --ic-surface-2:    #2d2820;
      --ic-surface-3:    #38302a;
      --ic-border:       rgba(201,168,76,0.22);
      --ic-border-light: rgba(201,168,76,0.1);
      --ic-border-warm:  rgba(154,138,106,0.2);

      --ic-gold:         #c9a84c;
      --ic-gold-light:   #e5c97a;
      --ic-gold-dark:    #a8822a;
      --ic-gold-glow:    rgba(201,168,76,0.08);
      --ic-gold-border:  rgba(201,168,76,0.25);

      --ic-emerald:      #5dbf90;
      --ic-emerald-dark: #3d9e6e;
      --ic-emerald-glow: rgba(93,191,144,0.08);

      --ic-text:         #e8dcc4;
      --ic-text-muted:   #9a8a6a;
      --ic-text-dim:     #5a4f3a;

      --ic-error:        #d9534f;
      --ic-success:      #5dbf90;

      --ic-nav-bg:       #161310;
      --ic-nav-border:   rgba(201,168,76,0.12);
      --ic-nav-width:    210px;
      --ic-radius:       6px;
      --ic-radius-lg:    10px;
    }

    html, body {
      margin:      0;
      height:      100%;
      font-family: 'Crimson Pro', Georgia, serif;
      background:  var(--ic-bg);
      color:       var(--ic-text);
      font-size:   16px;
    }

    body {
      background-image: url("data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 48 48' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M24 2L46 24L24 46L2 24Z' fill='none' stroke='%23c9a84c' stroke-width='0.4' stroke-opacity='0.1'/%3E%3C/svg%3E");
    }

    a { color: var(--ic-gold); text-decoration: none; }
    a:hover { color: var(--ic-gold-light); }

    /* ── Site shell ───────────────────────────────────────────────────── */
    #ic-wrapper {
      display:        flex;
      flex-direction: column;
      max-width:      1300px;
      margin:         1.5rem auto;
      border:         2px solid var(--ic-border);
      border-radius:  var(--ic-radius-lg);
      background:     var(--ic-bg);
      box-shadow:     0 8px 40px rgba(0,0,0,0.7), 0 0 0 1px rgba(201,168,76,0.06);
      min-height:     calc(100vh - 3rem);
      overflow:       hidden;
    }

    /* ── Header ───────────────────────────────────────────────────────── */
    #ic-header {
      background:    var(--ic-surface);
      border-bottom: 1px solid var(--ic-border);
      box-shadow:    0 2px 16px rgba(0,0,0,0.5);
      display:       flex;
      align-items:   center;
      gap:           1rem;
      padding:       0 2em;
      height:        56px;
      flex-shrink:   0;
      position:      relative;
      z-index:       100;
    }

    #ic-header::before {
      content:  '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height:   3px;
      background: linear-gradient(90deg, transparent, var(--ic-gold), transparent);
    }

    #ic-header .site-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.2rem;
      font-weight:    700;
      color:          var(--ic-gold-light);
      letter-spacing: 0.05em;
      line-height:    1.1;
    }

    #ic-header .site-title-sub {
      display:        block;
      font-size:      0.6rem;
      color:          var(--ic-text-dim);
      letter-spacing: 0.16em;
      text-transform: uppercase;
      font-family:    'Cinzel', Georgia, serif;
      font-weight:    400;
    }

    #ic-header .header-sep { flex: 1; }

    #ic-header .header-search {
      display:       flex;
      align-items:   center;
      background:    var(--ic-surface-2);
      border:        1px solid var(--ic-border-warm);
      border-radius: var(--ic-radius);
      padding:       0.25rem 0.75rem;
      gap:           0.5rem;
    }

    #ic-header .header-search input {
      background:  transparent;
      border:      none;
      outline:     none;
      color:       var(--ic-text);
      font-family: 'Crimson Pro', Georgia, serif;
      font-size:   0.9rem;
      width:       190px;
    }

    #ic-header .header-search input::placeholder { color: var(--ic-text-dim); }
    #ic-header .header-search svg { color: var(--ic-text-dim); flex-shrink: 0; }

    /* ── Body ─────────────────────────────────────────────────────────── */
    #ic-body { display: flex; flex: 1; min-height: 0; }

    /* ── Sidebar nav ──────────────────────────────────────────────────── */
    #ic-nav {
      width:        var(--ic-nav-width);
      min-width:    var(--ic-nav-width);
      background:   var(--ic-nav-bg);
      border-right: 1px solid var(--ic-nav-border);
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
      color:          var(--ic-text-dim);
      padding:        0.9rem 0.6rem 0.3rem;
    }

    .nav-divider {
      height:     1px;
      background: var(--ic-nav-border);
      margin:     0.5rem 0.25rem;
    }

    .nav-item {
      display:         flex;
      align-items:     center;
      justify-content: space-between;
      background:      var(--ic-surface-2);
      border:          1px solid rgba(154,138,106,0.12);
      border-left:     3px solid transparent;
      border-radius:   var(--ic-radius);
      margin-bottom:   4px;
      padding:         8px 12px;
      font-family:     'Cinzel', Georgia, serif;
      font-size:       0.78rem;
      font-weight:     600;
      letter-spacing:  0.03em;
      color:           var(--ic-text-muted);
      text-decoration: none;
      transition:      background 0.2s, color 0.2s, border-left-color 0.2s;
    }

    .nav-item:hover {
      background:        rgba(201,168,76,0.08);
      color:             var(--ic-gold-light);
      border-left-color: var(--ic-gold);
    }

    .nav-item.active {
      color:             var(--ic-gold);
      border-left-color: var(--ic-gold);
      background:        rgba(201,168,76,0.08);
    }

    .nav-count {
      font-family:  'Crimson Pro', Georgia, serif;
      font-size:    0.75rem;
      font-weight:  400;
      color:        var(--ic-text-dim);
      background:   rgba(154,138,106,0.12);
      border-radius:10px;
      padding:      1px 6px;
    }

    .nav-item.active .nav-count { color: var(--ic-gold-dark); }

    .nav-item-alt {
      color:        var(--ic-emerald);
      border-color: rgba(93,191,144,0.12);
      background:   rgba(93,191,144,0.04);
      border-left-color: transparent;
    }

    .nav-item-alt:hover {
      background:        rgba(93,191,144,0.1);
      color:             var(--ic-emerald);
      border-left-color: var(--ic-emerald);
    }

    /* ── Main content ─────────────────────────────────────────────────── */
    #ic-content {
      flex:       1;
      min-width:  0;
      padding:    2rem 2.5rem;
      overflow-y: auto;
    }

    /* ── Page header ──────────────────────────────────────────────────── */
    .page-header {
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--ic-border-light);
    }

    .page-header h1 {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.7rem;
      font-weight:    700;
      color:          var(--ic-gold-light);
      letter-spacing: 0.04em;
      margin:         0 0 0.35rem;
    }

    .page-header p {
      color:     var(--ic-text-muted);
      font-size: 1rem;
      margin:    0;
    }

    .page-header .header-row {
      display:     flex;
      align-items: flex-end;
      gap:         1rem;
      flex-wrap:   wrap;
    }

    /* ── Filter bar ───────────────────────────────────────────────────── */
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
      background:    var(--ic-surface-2);
      border:        1px solid var(--ic-border-warm);
      border-radius: var(--ic-radius);
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
      color:       var(--ic-text);
      font-family: 'Crimson Pro', Georgia, serif;
      font-size:   1rem;
      width:       100%;
    }

    .filter-search input::placeholder { color: var(--ic-text-dim); }

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
      border:          1px solid var(--ic-border-warm);
      color:           var(--ic-text-muted);
      background:      var(--ic-surface-2);
      text-decoration: none;
      transition:      all 0.2s;
      cursor:          pointer;
    }

    .filter-pill:hover { border-color: var(--ic-gold-border); color: var(--ic-gold); background: var(--ic-gold-glow); }
    .filter-pill.active { border-color: var(--ic-gold); color: var(--ic-bg); background: var(--ic-gold); }

    .filter-select {
      background:    var(--ic-surface-2);
      border:        1px solid var(--ic-border-warm);
      border-radius: var(--ic-radius);
      color:         var(--ic-text);
      font-family:   'Cinzel', Georgia, serif;
      font-size:     0.75rem;
      padding:       0.4rem 0.75rem;
      outline:       none;
      cursor:        pointer;
    }

    /* ── Cards grid ───────────────────────────────────────────────────── */
    .cards-grid {
      display:               grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap:                   1rem;
    }

    .card {
      background:    var(--ic-surface);
      border:        1px solid var(--ic-border-warm);
      border-radius: var(--ic-radius-lg);
      padding:       1.25rem;
      transition:    border-color 0.2s, box-shadow 0.2s, transform 0.2s;
      display:       flex;
      flex-direction:column;
      gap:           0.5rem;
    }

    .card:hover {
      border-color: var(--ic-gold-border);
      box-shadow:   0 4px 20px rgba(0,0,0,0.4), 0 0 0 1px rgba(201,168,76,0.1);
      transform:    translateY(-2px);
    }

    .card-name {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1rem;
      font-weight:    700;
      color:          var(--ic-gold-light);
      letter-spacing: 0.03em;
      margin:         0;
      line-height:    1.25;
    }

    .card-subtitle {
      font-size:  0.85rem;
      color:      var(--ic-emerald);
      margin:     0;
      font-style: italic;
    }

    .card-desc {
      font-size:   0.9rem;
      color:       var(--ic-text-muted);
      margin:      0;
      line-height: 1.4;
      display:     -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .card-divider {
      height:     1px;
      background: var(--ic-border-light);
      margin:     0.25rem 0;
    }

    .card-tags {
      display:  flex;
      gap:      0.3rem;
      flex-wrap:wrap;
      margin:   0;
    }

    .tag {
      font-family:   'Cinzel', Georgia, serif;
      font-size:     0.64rem;
      font-weight:   600;
      letter-spacing:0.05em;
      text-transform:uppercase;
      border-radius: 3px;
      padding:       2px 7px;
    }

    .tag-skill   { background:rgba(93,191,144,0.12); color:var(--ic-emerald); border:1px solid rgba(93,191,144,0.2); }
    .tag-gift    { background:rgba(201,168,76,0.1);  color:var(--ic-gold);    border:1px solid rgba(201,168,76,0.2); }
    .tag-major   { background:rgba(201,168,76,0.12); color:var(--ic-gold);    border:1px solid rgba(201,168,76,0.25); }
    .tag-minor   { background:rgba(154,138,106,0.1); color:var(--ic-text-muted); border:1px solid rgba(154,138,106,0.2); }
    .tag-book    { background:rgba(90,79,58,0.4);    color:var(--ic-text-dim); border:1px solid rgba(90,79,58,0.5); }

    /* ── Tables ───────────────────────────────────────────────────────── */
    .ic-table {
      width:           100%;
      border-collapse: collapse;
      font-size:       0.92rem;
    }

    .ic-table th {
      background:     var(--ic-surface-2);
      color:          var(--ic-gold);
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.7rem;
      font-weight:    700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      padding:        0.65rem 0.9rem;
      text-align:     left;
      border-bottom:  2px solid var(--ic-border);
      white-space:    nowrap;
    }

    .ic-table td {
      padding:       0.55rem 0.9rem;
      border-bottom: 1px solid var(--ic-border-light);
      vertical-align:top;
      line-height:   1.4;
    }

    .ic-table tr:hover td { background: rgba(201,168,76,0.03); }
    .ic-table .td-name { font-family:'Cinzel',serif; font-weight:600; font-size:0.85rem; color:var(--ic-gold-light); white-space:nowrap; }
    .ic-table .td-name a { color:var(--ic-gold-light); }
    .ic-table .td-name a:hover { color:var(--ic-gold); }
    .ic-table .td-muted { color: var(--ic-text-muted); font-size: 0.88rem; }
    .ic-table .td-dim   { color: var(--ic-text-dim);   font-size: 0.85rem; }

    .table-category-header td {
      background:     var(--ic-surface-2);
      color:          var(--ic-emerald);
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.75rem;
      font-weight:    700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding:        0.5rem 0.9rem;
      border-top:     1px solid rgba(93,191,144,0.2);
      border-bottom:  1px solid rgba(93,191,144,0.2);
    }

    /* ── Detail page ──────────────────────────────────────────────────── */
    .detail-layout {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 2rem;
      align-items: start;
    }

    @media (max-width: 900px) { .detail-layout { grid-template-columns: 1fr; } }

    .detail-name {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      2rem;
      font-weight:    700;
      color:          var(--ic-gold-light);
      letter-spacing: 0.04em;
      margin:         0 0 0.25rem;
    }

    .detail-subtitle { font-size:1.05rem; color:var(--ic-emerald); font-style:italic; margin:0 0 1rem; }
    .detail-desc { font-size:1.05rem; color:var(--ic-text); line-height:1.65; margin:0 0 1.5rem; }

    .detail-section { margin-bottom: 1.5rem; }

    .detail-section-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.72rem;
      font-weight:    700;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color:          var(--ic-text-dim);
      margin:         0 0 0.6rem;
      padding-bottom: 0.35rem;
      border-bottom:  1px solid var(--ic-border-light);
    }

    .detail-sidebar { position: sticky; top: 1rem; }

    .sidebar-card {
      background:    var(--ic-surface);
      border:        1px solid var(--ic-border-warm);
      border-radius: var(--ic-radius-lg);
      padding:       1.25rem;
      margin-bottom: 1rem;
    }

    .sidebar-card-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.72rem;
      font-weight:    700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color:          var(--ic-gold);
      margin:         0 0 0.75rem;
      padding-bottom: 0.5rem;
      border-bottom:  1px solid var(--ic-border);
    }

    .trait-list { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.4rem; }

    .trait-list li {
      font-size:   0.95rem;
      color:       var(--ic-text);
      padding:     0.35rem 0.6rem;
      background:  var(--ic-surface-2);
      border-radius: var(--ic-radius);
      border-left: 3px solid var(--ic-emerald);
      line-height: 1.3;
    }

    .trait-list li.gift-item { border-left-color: var(--ic-gold); }
    .trait-list li small { display:block; color:var(--ic-text-dim); font-size:0.78rem; margin-top:0.15rem; }

    /* ── Breadcrumb ───────────────────────────────────────────────────── */
    .breadcrumb {
      font-size:     0.82rem;
      color:         var(--ic-text-dim);
      margin-bottom: 1.25rem;
      display:       flex;
      align-items:   center;
      gap:           0.4rem;
    }

    .breadcrumb a { color: var(--ic-gold-dark); }
    .breadcrumb a:hover { color: var(--ic-gold); }

    /* ── Home stats ───────────────────────────────────────────────────── */
    .home-stats {
      display:               grid;
      grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
      gap:                   0.75rem;
      margin-bottom:         2rem;
    }

    .stat-card {
      background:    var(--ic-surface);
      border:        1px solid var(--ic-border-warm);
      border-radius: var(--ic-radius-lg);
      padding:       1.25rem 1rem;
      text-align:    center;
      text-decoration:none;
      display:       block;
      transition:    border-color 0.2s, transform 0.2s;
    }

    .stat-card:hover { border-color: var(--ic-gold-border); transform: translateY(-2px); }

    .stat-n {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      2rem;
      font-weight:    700;
      color:          var(--ic-emerald);
      line-height:    1;
      margin-bottom:  0.25rem;
    }

    .stat-l {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.68rem;
      font-weight:    600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color:          var(--ic-text-dim);
    }

    /* ── Search ───────────────────────────────────────────────────────── */
    .search-form { display:flex; gap:0.75rem; margin-bottom:2rem; }

    .search-input {
      flex:          1;
      background:    var(--ic-surface-2);
      border:        1px solid var(--ic-border);
      border-radius: var(--ic-radius);
      padding:       0.7rem 1rem;
      color:         var(--ic-text);
      font-family:   'Crimson Pro', Georgia, serif;
      font-size:     1.05rem;
      outline:       none;
      transition:    border-color 0.2s;
    }

    .search-input:focus { border-color: var(--ic-gold); }

    .search-btn {
      background:    var(--ic-gold);
      border:        none;
      border-radius: var(--ic-radius);
      color:         var(--ic-bg);
      font-family:   'Cinzel', Georgia, serif;
      font-size:     0.8rem;
      font-weight:   700;
      letter-spacing:0.06em;
      padding:       0 1.5rem;
      cursor:        pointer;
      transition:    background 0.2s;
    }

    .search-btn:hover { background: var(--ic-gold-light); }

    .search-group-label {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.7rem;
      font-weight:    700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color:          var(--ic-gold);
      margin:         1.5rem 0 0.5rem;
      padding-bottom: 0.3rem;
      border-bottom:  1px solid var(--ic-border);
    }

    .search-result {
      display:        flex;
      gap:            1rem;
      align-items:    flex-start;
      padding:        0.75rem 0;
      border-bottom:  1px solid var(--ic-border-light);
      text-decoration:none;
      color:          inherit;
    }

    .search-result:hover { background: rgba(201,168,76,0.03); }
    .search-result-name { font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; color:var(--ic-gold-light); margin:0 0 0.2rem; }
    .search-result-desc { font-size:0.88rem; color:var(--ic-text-muted); margin:0; line-height:1.4; }

    /* ── Skills row ───────────────────────────────────────────────────── */
    .skill-row {
      background:    var(--ic-surface);
      border:        1px solid var(--ic-border-warm);
      border-radius: var(--ic-radius-lg);
      padding:       1.1rem 1.25rem;
      margin-bottom: 0.75rem;
      display:       grid;
      grid-template-columns: 200px 1fr;
      gap:           1.25rem;
      align-items:   start;
    }

    @media (max-width:700px) { .skill-row { grid-template-columns: 1fr; } }

    .skill-row-name { font-family:'Cinzel',serif; font-size:0.95rem; font-weight:700; color:var(--ic-gold-light); margin:0 0 0.25rem; }
    .skill-row-desc { font-size:0.9rem; color:var(--ic-text-muted); line-height:1.45; margin:0; }
    .skill-row-favs { font-size:0.82rem; color:var(--ic-text-dim); margin-top:0.5rem; line-height:1.5; }

    /* ── Utility ──────────────────────────────────────────────────────── */
    .text-gold   { color: var(--ic-gold); }
    .text-emerald{ color: var(--ic-emerald); }
    .text-muted  { color: var(--ic-text-muted); }
    .text-dim    { color: var(--ic-text-dim); }
    .hidden      { display: none !important; }

    @media (max-width:640px) {
      #ic-content { padding: 1.25rem; }
      .cards-grid { grid-template-columns: 1fr; }
      #ic-nav { display: none; }
    }
  </style>
</head>
<body>
<div id="ic-wrapper">

  <header id="ic-header">
    <a href="/ic" style="text-decoration:none;">
      <span class="site-title">
        The Library of Calabria
        <span class="site-title-sub">Ironclaw Reference</span>
      </span>
    </a>
    <div class="header-sep"></div>
    <form class="header-search" action="/ic/search" method="get">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" placeholder="Search species, gifts, careers…" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    </form>
  </header>

  <div id="ic-body">

    <nav id="ic-nav">
      <a href="/" class="nav-item nav-item-alt">← Library of Calabria</a>

      <div class="nav-divider"></div>
      <div class="nav-group-label">Characters</div>
      <a href="/ic/species"   class="nav-item<?= $na('species') ?>">Species <?= $nt('species') ?></a>
      <a href="/ic/careers"   class="nav-item<?= $na('careers') ?>">Careers <?= $nt('careers') ?></a>

      <div class="nav-divider"></div>
      <div class="nav-group-label">Rules</div>
      <a href="/ic/gifts"     class="nav-item<?= $na('gifts') ?>">Gifts <?= $nt('gifts') ?></a>
      <a href="/ic/skills"    class="nav-item<?= $na('skills') ?>">Skills <?= $nt('skills') ?></a>

      <div class="nav-divider"></div>
      <div class="nav-group-label">Equipment</div>
      <a href="/ic/equipment" class="nav-item<?= $na('equipment') ?>">Equipment <?= $nt('equipment') ?></a>
      <a href="/ic/weapons"   class="nav-item<?= $na('weapons') ?>">Weapons <?= $nt('weapons') ?></a>

      <div class="nav-divider"></div>
      <div class="nav-group-label">Library</div>
      <a href="/ic/books"     class="nav-item<?= $na('books') ?>">Books</a>
      <a href="/ic/search"    class="nav-item<?= $na('search') ?>">🔍 Search</a>

      <div class="nav-divider"></div>
      <a href="/builder" class="nav-item nav-item-alt">⚙ Character Builder</a>
      <a href="/uj"      class="nav-item nav-item-alt">Urban Jungle →</a>
    </nav>

    <main id="ic-content">
