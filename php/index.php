<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
cg_session_start();
cg_try_wp_sso();
$loggedIn = cg_is_logged_in();
$username = $loggedIn ? htmlspecialchars($_SESSION['cg_username'] ?? '') : '';

// Fetch a random book cover from WordPress for the header thumbnail
$bookThumbUrl = '';
try {
    $p   = cg_prefix();
    $row = cg_query_one(
        "SELECT pm.meta_value AS thumb_id
           FROM {$p}posts p
           JOIN {$p}postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id'
          WHERE p.post_status = 'publish'
            AND p.post_type NOT IN ('attachment','revision','nav_menu_item','custom_css','customize_changeset','wp_block')
          ORDER BY RAND()
          LIMIT 1",
        []
    );
    if ($row) {
        $img = cg_query_one(
            "SELECT guid FROM {$p}posts WHERE ID = ? LIMIT 1",
            [(int) $row['thumb_id']]
        );
        $bookThumbUrl = $img['guid'] ?? '';
    }
} catch (Throwable $e) { /* non-fatal */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Character Generator – Library of Calabria</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap">
  <link rel="stylesheet" href="/assets/css/dist/core.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    :root {
      --bg:          #1a1714;
      --surface:     #242019;
      --surface-2:   #2d2820;
      --gold:        #c9a84c;
      --gold-light:  #e5c97a;
      --gold-dark:   #a8822a;
      --gold-border: rgba(201,168,76,0.28);
      --gold-glow:   rgba(201,168,76,0.12);
      --text:        #e8dcc4;
      --text-muted:  #9a8a6a;
      --text-dim:    #5a4f3a;
      --error:       #d9534f;
      --nav-bg:      #1e1a16;
      --nav-border:  rgba(201,168,76,0.15);
      --nav-width:   220px;
      --header-h:    52px;
    }

    html, body {
      margin:      0;
      height:      100%;
      font-family: 'Crimson Pro', Georgia, serif;
      background:  var(--bg);
      color:       var(--text);
    }

    body {
      background-image: url("data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 48 48' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M24 2L46 24L24 46L2 24Z' fill='none' stroke='%23c9a84c' stroke-width='0.4' stroke-opacity='0.12'/%3E%3C/svg%3E");
    }

    #site-wrapper {
      display:        flex;
      flex-direction: column;
      max-width:      1280px;
      margin:         2rem auto;
      border:         3px solid var(--gold-border);
      border-radius:  20px;
      background:     var(--bg);
      box-shadow:     0 8px 40px rgba(0,0,0,0.6), 0 0 0 1px rgba(201,168,76,0.08);
      min-height:     calc(100vh - 4rem);
      overflow:       hidden;
    }

    #site-header {
      background:    var(--surface);
      border-bottom: 1px solid var(--gold-border);
      box-shadow:    0 2px 12px rgba(0,0,0,0.4);
      display:       flex;
      align-items:   center;
      padding:       1.2em 2em;
      position:      relative;
      z-index:       200;
      flex-shrink:   0;
    }

    #site-header::before {
      content:    '';
      display:    block;
      position:   absolute;
      top: 0; left: 0; right: 0;
      height:     3px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
    }

    #site-header a.site-title {
      font-family:     'Cinzel', Georgia, serif;
      font-size:       1.3rem;
      font-weight:     700;
      color:           var(--gold-light);
      letter-spacing:  0.06em;
      text-decoration: none;
      transition:      color 0.2s;
    }

    #site-header a.site-title:hover { color: var(--gold); }

    #site-header .site-title-sub {
      display:        block;
      font-size:      0.65rem;
      color:          var(--text-dim);
      letter-spacing: 0.14em;
      text-transform: uppercase;
      font-weight:    400;
    }

    .header-book-thumb {
      position:   absolute;
      top:        10px;
      right:      1.5em;
      z-index:    10;
    }
    .header-book-thumb img {
      border:        2px solid var(--gold-border);
      border-radius: 6px;
      max-height:    80px;
      width:         auto;
      display:       block;
      transition:    transform 0.3s ease, box-shadow 0.3s ease;
    }
    .header-book-thumb img:hover {
      transform:  scale(1.06);
      box-shadow: 0 4px 16px rgba(201,168,76,0.35);
    }

    #site-body {
      display: flex;
      flex:    1;
    }

    #site-nav {
      width:        var(--nav-width);
      min-width:    var(--nav-width);
      background:   var(--nav-bg);
      border-right: 1px solid var(--nav-border);
      padding:      1rem 0.6rem;
      overflow-y:   auto;
      flex-shrink:  0;
    }

    .nav-item {
      display:         block;
      background:      var(--surface-2);
      border:          1px solid var(--gold-border);
      border-left:     3px solid transparent;
      border-radius:   4px;
      margin-bottom:   5px;
      padding:         9px 14px;
      text-align:      center;
      font-family:     'Cinzel', Georgia, serif;
      font-size:       0.8rem;
      font-weight:     600;
      letter-spacing:  0.04em;
      color:           var(--text-muted);
      text-decoration: none;
      transition:      background 0.2s, color 0.2s, border-left-color 0.2s;
      white-space:     nowrap;
    }

    .nav-item:hover {
      background:        rgba(201,168,76,0.08);
      color:             var(--gold-light);
      border-left-color: var(--gold);
      text-decoration:   none;
    }

    .nav-item.active {
      color:             var(--gold);
      border-left-color: var(--gold);
      background:        rgba(201,168,76,0.10);
    }

    .nav-item.parent {
      font-size:   0.72rem;
      color:       var(--text-dim);
      background:  transparent;
      border-color:transparent;
      padding-top: 1rem;
      cursor:      default;
      text-align:  left;
    }

    .nav-item.child {
      padding-left: 1.6rem;
      text-align:   left;
      font-weight:  400;
      font-size:    0.78rem;
    }

    .nav-divider {
      height:     1px;
      background: var(--nav-border);
      margin:     0.6rem 0.25rem;
    }

    #site-content {
      flex:           1;
      display:        flex;
      flex-direction: column;
      min-width:      0;
    }

    .btn {
      display:         inline-flex;
      align-items:     center;
      justify-content: center;
      gap:             0.5rem;
      padding:         0.6rem 1.4rem;
      font-family:     'Cinzel', Georgia, serif;
      font-size:       0.82rem;
      font-weight:     600;
      letter-spacing:  0.06em;
      border-radius:   5px;
      cursor:          pointer;
      transition:      all 0.2s;
      text-transform:  uppercase;
    }

    .btn-gold {
      background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 50%, var(--gold-dark) 100%);
      color:      #1a1410;
      border:     1px solid var(--gold-dark);
      box-shadow: 0 2px 8px rgba(201,168,76,0.25);
    }

    .btn-gold:hover:not(:disabled) {
      background:  linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%);
      box-shadow:  0 4px 16px rgba(201,168,76,0.35);
      transform:   translateY(-1px);
    }

    .btn-gold:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    .btn-ghost {
      background:     transparent;
      color:          var(--text-muted);
      border:         1px solid rgba(201,168,76,0.2);
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.75rem;
      letter-spacing: 0.05em;
      padding:        0.4rem 0.9rem;
      border-radius:  4px;
      cursor:         pointer;
      text-transform: uppercase;
      transition:     all 0.2s;
    }

    .btn-ghost:hover { color: var(--gold-light); border-color: var(--gold-border); }

    .btn-outline {
      background:     transparent;
      color:          var(--gold);
      border:         1px solid var(--gold-border);
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.82rem;
      letter-spacing: 0.06em;
      padding:        0.6rem 1.4rem;
      border-radius:  5px;
      cursor:         pointer;
      text-transform: uppercase;
      transition:     all 0.2s;
    }

    .btn-outline:hover { background: var(--gold-glow); border-color: var(--gold); color: var(--gold-light); }

    #cg-auth-screen {
      display:         flex;
      align-items:     center;
      justify-content: center;
      flex:            1;
      padding:         3rem 2rem;
    }

    .auth-card {
      background:    var(--surface);
      border:        1px solid var(--gold-border);
      border-radius: 12px;
      padding:       0 2rem 2.5rem;
      width:         100%;
      max-width:     400px;
      box-shadow:    0 24px 64px rgba(0,0,0,0.5), 0 0 0 1px rgba(201,168,76,0.1);
      overflow:      hidden;
    }

    .auth-card-accent {
      height:     3px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
      margin:     0 -2rem 2.5rem;
    }

    .auth-emblem {
      text-align:     center;
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.5rem;
      color:          var(--gold);
      letter-spacing: 0.12em;
      margin-bottom:  0.35rem;
      line-height:    1;
    }

    .auth-card h1 {
      margin:         0 0 0.2rem;
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.5rem;
      font-weight:    700;
      color:          var(--gold-light);
      text-align:     center;
      letter-spacing: 0.06em;
    }

    .auth-card .subtitle {
      text-align:     center;
      color:          var(--text-muted);
      font-size:      0.9rem;
      font-family:    'Cinzel', Georgia, serif;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      margin-bottom:  2rem;
    }

    .auth-divider {
      height:     1px;
      background: linear-gradient(90deg, transparent, var(--gold-border), transparent);
      margin:     0 -2rem 1.75rem;
    }

    .auth-tabs {
      display:       flex;
      border-bottom: 1px solid rgba(201,168,76,0.15);
      margin-bottom: 1.5rem;
    }

    .auth-tab {
      flex:           1;
      background:     none;
      border:         none;
      border-bottom:  2px solid transparent;
      margin-bottom:  -1px;
      color:          var(--text-dim);
      padding:        0.5rem;
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.8rem;
      font-weight:    600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      cursor:         pointer;
      transition:     all 0.2s;
    }

    .auth-tab.active { color: var(--gold); border-bottom-color: var(--gold); }
    .auth-tab:hover:not(.active) { color: var(--text-muted); }

    .auth-form { display: none; flex-direction: column; gap: 1rem; }
    .auth-form.active { display: flex; }

    .auth-field label {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.72rem;
      font-weight:    600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color:          var(--text-muted);
      display:        block;
      margin-bottom:  0.4rem;
    }

    .auth-field input {
      width:         100%;
      padding:       0.65rem 0.9rem;
      background:    rgba(10,8,5,0.5);
      border:        1px solid rgba(201,168,76,0.18);
      border-radius: 5px;
      color:         var(--text);
      font-family:   'Crimson Pro', Georgia, serif;
      font-size:     1rem;
      outline:       none;
      transition:    border-color 0.2s, box-shadow 0.2s;
    }

    .auth-field input:focus { border-color: var(--gold); box-shadow: 0 0 0 2px rgba(201,168,76,0.12); }
    .auth-field input::placeholder { color: var(--text-dim); }
    .auth-submit { width: 100%; padding: 0.8rem; margin-top: 0.25rem; }

    .auth-error {
      color:      var(--error);
      font-size:  0.9rem;
      text-align: center;
      min-height: 1.2em;
      font-style: italic;
    }

    #cg-app-screen {
      display:        none;
      flex:           1;
      flex-direction: column;
    }

    .app-topbar {
      display:         flex;
      align-items:     center;
      justify-content: flex-end;
      padding:         0.5rem 1.5rem;
      background:      var(--surface);
      border-bottom:   1px solid var(--gold-border);
      gap:             1rem;
    }

    .app-topbar__username { font-size: 0.9rem; color: var(--text-muted); }
    .app-topbar__username strong { color: var(--text); font-weight: 600; }

    #cg-open-builder-trigger { display: none; }

    /* ── Character list view ───────────────────────────────────────────────── */

    #cg-char-list-view {
      flex:    1;
      display: flex;
      flex-direction: column;
      padding: 2rem 2.5rem;
    }

    .char-list-header {
      display:         flex;
      align-items:     center;
      justify-content: space-between;
      margin-bottom:   2rem;
      flex-wrap:       wrap;
      gap:             1rem;
    }

    .char-list-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.5rem;
      font-weight:    700;
      color:          var(--gold-light);
      letter-spacing: 0.05em;
      margin:         0;
    }

    .char-list-title span {
      display:    block;
      font-size:  0.7rem;
      color:      var(--text-dim);
      letter-spacing: 0.14em;
      text-transform: uppercase;
      font-weight: 400;
      margin-top: 0.2rem;
    }

    #cg-char-grid {
      display:               grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap:                   1.25rem;
    }

    .char-card {
      background:    var(--surface);
      border:        1px solid var(--gold-border);
      border-radius: 10px;
      padding:       1.4rem 1.5rem 1.2rem;
      display:       flex;
      flex-direction: column;
      gap:           0.6rem;
      transition:    border-color 0.2s, box-shadow 0.2s;
      position:      relative;
      overflow:      hidden;
    }

    .char-card::before {
      content:    '';
      position:   absolute;
      top:        0; left: 0; right: 0;
      height:     2px;
      background: linear-gradient(90deg, transparent, var(--gold-border), transparent);
    }

    .char-card:hover { border-color: var(--gold); box-shadow: 0 4px 18px rgba(201,168,76,0.15); }

    .char-card__name {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.05rem;
      font-weight:    700;
      color:          var(--gold-light);
      letter-spacing: 0.03em;
      margin:         0;
      line-height:    1.25;
    }

    .char-card__meta {
      font-size:  0.88rem;
      color:      var(--text-muted);
      line-height: 1.5;
      margin:     0;
    }

    .char-card__meta strong { color: var(--text); font-weight: 600; }

    .char-card__updated {
      font-size:     0.75rem;
      color:         var(--text-dim);
      font-style:    italic;
      margin-top:    0.25rem;
    }

    .char-card__actions {
      display:    flex;
      gap:        0.5rem;
      margin-top: 0.5rem;
    }

    .char-card__actions .btn-sm {
      flex:        1;
      padding:     0.4rem 0.7rem;
      font-size:   0.72rem;
      font-family: 'Cinzel', Georgia, serif;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      border-radius: 4px;
      cursor:      pointer;
      transition:  all 0.18s;
      border:      1px solid var(--gold-border);
    }

    .char-card__actions .btn-edit {
      background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 50%, var(--gold-dark) 100%);
      color:      #1a1410;
    }

    .char-card__actions .btn-edit:hover {
      background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%);
      box-shadow: 0 2px 10px rgba(201,168,76,0.3);
    }

    .char-card__actions .btn-delete {
      background:   transparent;
      color:        var(--text-muted);
      border-color: rgba(201,168,76,0.15);
    }

    .char-card__actions .btn-delete:hover { color: #e07070; border-color: rgba(224,112,112,0.4); background: rgba(224,112,112,0.07); }

    .char-list-empty {
      text-align:  center;
      padding:     4rem 2rem;
      color:       var(--text-dim);
      font-style:  italic;
      font-size:   1rem;
    }

    .char-list-empty strong {
      display:        block;
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1.1rem;
      color:          var(--text-muted);
      font-style:     normal;
      margin-bottom:  0.5rem;
    }

    .char-list-loading {
      text-align:  center;
      padding:     4rem 2rem;
      color:       var(--text-dim);
      font-style:  italic;
    }

    /* ── Wizard view ───────────────────────────────────────────────────────── */

    #cg-wizard-view {
      flex:           1;
      display:        none;
      flex-direction: column;
    }

    .wizard-header {
      background:    var(--surface);
      border-bottom: 1px solid var(--gold-border);
      padding:       1.1rem 2rem 0;
    }

    .wizard-header-top {
      display:         flex;
      align-items:     center;
      justify-content: space-between;
      margin-bottom:   1rem;
      gap:             1rem;
    }

    .wizard-title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      1rem;
      font-weight:    700;
      color:          var(--gold-light);
      letter-spacing: 0.06em;
      margin:         0;
    }

    .wizard-close-btn {
      background:  transparent;
      border:      1px solid rgba(201,168,76,0.2);
      color:       var(--text-muted);
      font-size:   0.78rem;
      font-family: 'Cinzel', Georgia, serif;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      padding:     0.35rem 0.9rem;
      border-radius: 4px;
      cursor:      pointer;
      transition:  all 0.18s;
    }

    .wizard-close-btn:hover { color: var(--gold-light); border-color: var(--gold-border); }

    .wizard-progress {
      display:         flex;
      gap:             0;
      overflow-x:      auto;
      padding-bottom:  0;
      scrollbar-width: none;
    }

    .wizard-progress::-webkit-scrollbar { display: none; }

    .wizard-step-tab {
      flex:            0 0 auto;
      display:         flex;
      align-items:     center;
      gap:             0.45rem;
      padding:         0.55rem 1rem 0.7rem;
      font-family:     'Cinzel', Georgia, serif;
      font-size:       0.72rem;
      font-weight:     600;
      letter-spacing:  0.05em;
      text-transform:  uppercase;
      color:           var(--text-dim);
      border-bottom:   2px solid transparent;
      cursor:          default;
      white-space:     nowrap;
      transition:      color 0.2s, border-color 0.2s;
    }

    .wizard-step-tab .step-num {
      width:         18px;
      height:        18px;
      border-radius: 50%;
      background:    var(--surface-2);
      border:        1px solid var(--text-dim);
      display:       flex;
      align-items:   center;
      justify-content: center;
      font-size:     0.65rem;
      flex-shrink:   0;
    }

    .wizard-step-tab.active {
      color:        var(--gold);
      border-color: var(--gold);
    }

    .wizard-step-tab.active .step-num {
      background: var(--gold);
      border-color: var(--gold);
      color: #1a1410;
    }

    .wizard-step-tab.done { color: var(--text-muted); }

    .wizard-step-tab.done .step-num {
      background:   var(--gold-dark);
      border-color: var(--gold-dark);
      color:        #1a1410;
    }

    .wizard-body {
      flex:      1;
      overflow:  auto;
      display:   flex;
      flex-direction: column;
    }

    .wizard-nav {
      display:         flex;
      align-items:     center;
      justify-content: space-between;
      padding:         0.9rem 2rem;
      background:      var(--surface);
      border-top:      1px solid var(--gold-border);
      gap:             1rem;
      flex-shrink:     0;
    }

    .wizard-nav .btn-nav-back {
      background:   transparent;
      color:        var(--gold);
      border:       1px solid var(--gold-border);
      font-family:  'Cinzel', Georgia, serif;
      font-size:    0.78rem;
      font-weight:  600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      padding:      0.55rem 1.3rem;
      border-radius: 5px;
      cursor:       pointer;
      transition:   all 0.18s;
    }

    .wizard-nav .btn-nav-back:hover { background: var(--gold-glow); }
    .wizard-nav .btn-nav-back:disabled { opacity: 0.35; cursor: default; }

    .wizard-nav .btn-nav-next {
      background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 50%, var(--gold-dark) 100%);
      color:      #1a1410;
      border:     1px solid var(--gold-dark);
      font-family:  'Cinzel', Georgia, serif;
      font-size:    0.78rem;
      font-weight:  600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      padding:      0.55rem 1.3rem;
      border-radius: 5px;
      cursor:       pointer;
      transition:   all 0.18s;
    }

    .wizard-nav .btn-nav-next:hover { background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%); box-shadow: 0 3px 12px rgba(201,168,76,0.3); }

    .wizard-nav .wizard-save-btns { display: flex; gap: 0.5rem; }

    /* Restyle the modal as a page section (no overlay) */
    #cg-modal-overlay {
      position:   static !important;
      display:    block !important;
      background: transparent !important;
      flex:       1;
      overflow:   hidden;
    }

    #cg-modal {
      position:     static !important;
      width:        100% !important;
      max-width:    none !important;
      height:       100% !important;
      max-height:   none !important;
      border-radius: 0 !important;
      box-shadow:   none !important;
      border:       none !important;
      background:   transparent !important;
      overflow:     auto;
      padding:      0 !important;
    }

    #cg-modal-close { display: none !important; }

    /* Hide the bundle's native tab bar — wizard controls it */
    .cg-tabs-bar { display: none !important; }

    /* Only show active tab panel */
    .cg-tab-wrap .tab-panel { display: none !important; }
    .cg-tab-wrap .tab-panel.active { display: block !important; }

    .button,
    .button.primary,
    .button.secondary {
      display:         inline-flex;
      align-items:     center;
      justify-content: center;
      font-family:     'Cinzel', Georgia, serif;
      font-size:       0.78rem;
      font-weight:     600;
      letter-spacing:  0.06em;
      text-transform:  uppercase;
      border-radius:   5px;
      padding:         0.55rem 1.2rem;
      cursor:          pointer;
      transition:      all 0.18s;
      border:          1px solid var(--gold-border);
      background:      linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 50%, var(--gold-dark) 100%);
      color:           #1a1410;
      box-shadow:      0 2px 6px rgba(201,168,76,0.2);
    }

    .button:hover,
    .button.primary:hover {
      background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%);
      box-shadow: 0 4px 12px rgba(201,168,76,0.3);
      transform:  translateY(-1px);
    }

    .button.secondary {
      background:   transparent;
      color:        var(--gold);
      border-color: var(--gold-border);
      box-shadow:   none;
    }

    .button.secondary:hover { background: rgba(201,168,76,0.08); color: var(--gold-light); border-color: var(--gold); }

    .unsaved-dialog { font-family: 'Crimson Pro', Georgia, serif; }

    .unsaved-buttons { display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap; }

    @media (max-width: 640px) { #site-nav { display: none; } }
  </style>
</head>
<body>

<div id="site-wrapper">

  <header id="site-header">
    <a href="/" class="site-title">
      The Library of Calabria
      <span class="site-title-sub">Character Generator</span>
    </a>
    <?php if ($bookThumbUrl): ?>
    <div class="header-book-thumb">
      <img src="<?= htmlspecialchars($bookThumbUrl) ?>" alt="Ironclaw book cover">
    </div>
    <?php endif; ?>
  </header>

  <div id="site-body">

    <nav id="site-nav" aria-label="Site navigation">
      <a href="/" class="nav-item">← Library of Calabria</a>
      <div class="nav-divider"></div>
      <a href="/ic"           class="nav-item parent">Ironclaw</a>
      <a href="/ic/books"     class="nav-item child">Books</a>
      <a href="/ic/species"   class="nav-item child">Species</a>
      <a href="/ic/careers"   class="nav-item child">Careers</a>
      <a href="/ic/gifts"     class="nav-item child">Gifts</a>
      <a href="/ic/skills"    class="nav-item child">Skills</a>
      <a href="/ic/equipment" class="nav-item child">Equipment</a>
      <div class="nav-divider"></div>
      <a href="/builder"      class="nav-item active">Character Generator</a>
    </nav>

    <div id="site-content">

      <div id="cg-auth-screen" style="display:<?= $loggedIn ? 'none' : 'flex' ?>">
        <div class="auth-card">
          <div class="auth-card-accent"></div>

          <div class="auth-emblem">◆</div>
          <h1>Library of Calabria</h1>
          <p class="subtitle">Character Generator</p>

          <div class="auth-divider"></div>

          <div class="auth-tabs">
            <button class="auth-tab active" data-tab="login">Sign In</button>
            <button class="auth-tab" data-tab="register">Register</button>
          </div>

          <form class="auth-form active" id="login-form" onsubmit="return false;">
            <div class="auth-field">
              <label for="login-username">Username or Email</label>
              <input type="text" id="login-username" name="username" autocomplete="username" placeholder="Enter your username">
            </div>
            <div class="auth-field">
              <label for="login-password">Password</label>
              <input type="password" id="login-password" name="password" autocomplete="current-password" placeholder="Enter your password">
            </div>
            <div class="auth-error" id="login-error"></div>
            <button type="button" id="login-btn" class="btn btn-gold auth-submit">Sign In</button>
          </form>

          <form class="auth-form" id="register-form" onsubmit="return false;">
            <div class="auth-field">
              <label for="reg-username">Username</label>
              <input type="text" id="reg-username" name="username" autocomplete="username" placeholder="Choose a username">
            </div>
            <div class="auth-field">
              <label for="reg-email">Email</label>
              <input type="email" id="reg-email" name="email" autocomplete="email" placeholder="your@email.com">
            </div>
            <div class="auth-field">
              <label for="reg-password">Password</label>
              <input type="password" id="reg-password" name="password" autocomplete="new-password" placeholder="Choose a password">
            </div>
            <div class="auth-error" id="register-error"></div>
            <button type="button" id="register-btn" class="btn btn-gold auth-submit">Create Account</button>
          </form>
        </div>
      </div>

      <div id="cg-app-screen" style="display:<?= $loggedIn ? 'flex' : 'none' ?>; flex-direction:column; flex:1">

        <div class="app-topbar">
          <span class="app-topbar__username">Signed in as <strong id="cg-username-display"><?= $username ?></strong></span>
          <button class="btn-ghost" id="cg-logout-btn">Sign Out</button>
        </div>

        <!-- ── Character list view ──────────────────────────────────────── -->
        <div id="cg-char-list-view">
          <div class="char-list-header">
            <h2 class="char-list-title">
              My Characters
              <span>Library of Calabria &mdash; Ironclaw Records</span>
            </h2>
            <button id="cg-new-char-btn" class="btn btn-gold">✦ &nbsp; New Character</button>
          </div>
          <div id="cg-char-grid">
            <div class="char-list-loading">Loading characters…</div>
          </div>
        </div>

        <!-- ── Step-by-step wizard view ─────────────────────────────────── -->
        <div id="cg-wizard-view">

          <!-- Progress bar / step tabs -->
          <div class="wizard-header">
            <div class="wizard-header-top">
              <h2 class="wizard-title" id="cg-wizard-title">New Character</h2>
              <button class="wizard-close-btn" id="cg-wizard-close-btn">✕ Close</button>
            </div>
            <div class="wizard-progress" id="cg-wizard-progress">
              <!-- steps rendered by JS -->
            </div>
          </div>

          <!-- Form container (bundle renders here) -->
          <div class="wizard-body">
            <div id="cg-modal-overlay" class="cg-hidden">
              <div id="cg-modal">
                <button id="cg-modal-close" class="cg-modal-close">✕</button>
                <div id="cg-form-container"></div>
                <div id="cg-unsaved-confirm" class="cg-hidden">
                  <div class="unsaved-dialog">
                    <p>You have unsaved changes. What would you like to do?</p>
                    <div class="unsaved-buttons">
                      <button id="unsaved-save"   class="button primary">Save &amp; Exit</button>
                      <button id="unsaved-exit"   class="button secondary">Exit Without Saving</button>
                      <button id="unsaved-cancel" class="button">Cancel</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Back / Next navigation -->
          <div class="wizard-nav">
            <button class="btn-nav-back" id="cg-wizard-back" disabled>&#8592; Back</button>
            <div class="wizard-save-btns">
              <button class="btn btn-gold cg-save-button" style="font-size:0.78rem;padding:0.55rem 1.1rem;">&#128190; Save</button>
              <button class="btn btn-gold cg-save-button cg-close-after-save" style="font-size:0.78rem;padding:0.55rem 1.1rem;">&#128190; Save &amp; Close</button>
              <button id="cg-wizard-ally-btn" style="display:none;font-size:0.78rem;padding:0.55rem 1.1rem;" class="btn-ghost">&#9733; Ally</button>
            </div>
            <button class="btn-nav-next" id="cg-wizard-next">Next &#8594;</button>
          </div>
        </div>

        <!-- Hidden splash elements required by builder-events.js -->
        <div id="cg-modal-splash" class="cg-modal-splash cg-hidden" style="display:none!important">
          <div class="cg-modal-splash__content">
            <button id="cg-new-splash" class="button primary"></button>
            <div class="cg-splash-load">
              <select id="cg-splash-load-select"><option value="">—</option></select>
              <button id="cg-load-splash" class="button"></button>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
  window.CG_AJAX = {
    ajax_url:    '/ajax.php',
    ajaxurl:     '/ajax.php',
    nonce:       '1',
    security:    '1',
    _ajax_nonce: '1',
  };
  window.CG_NONCES = new Proxy({}, { get: () => '1' });
</script>

<script>
(function () {
  var loggedIn = <?= $loggedIn ? 'true' : 'false' ?>;
  const authScreen = document.getElementById('cg-auth-screen');
  const appScreen  = document.getElementById('cg-app-screen');

  function ajax(action, data) {
    const body = new URLSearchParams({ action, nonce: '1', security: '1', ...data });
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 10000);
    return fetch('/ajax.php', { method: 'POST', body, signal: controller.signal })
      .then(r => { clearTimeout(timer); return r.json(); })
      .catch(e => { clearTimeout(timer); return { success: false, data: e.name === 'AbortError' ? 'Request timed out.' : 'Network error.' }; });
  }

  function showAuth() {
    authScreen.style.display = 'flex';
    appScreen.style.display  = 'none';
  }

  function showApp(username) {
    authScreen.style.display = 'none';
    appScreen.style.display  = 'flex';
    appScreen.style.flexDirection = 'column';
    document.getElementById('cg-username-display').textContent = username;
    loadBundle();
    showListView();
    loadCharacterList();
  }

  document.querySelectorAll('.auth-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(tab.dataset.tab + '-form').classList.add('active');
    });
  });

  function doLogin() {
    const btn      = document.getElementById('login-btn');
    const err      = document.getElementById('login-error');
    const username = document.getElementById('login-username').value.trim();
    const password = document.getElementById('login-password').value;
    if (!username || !password) { err.textContent = 'Please enter your username and password.'; return; }
    btn.disabled = true;
    err.textContent = 'Signing in\u2026';
    ajax('cg_login_user', { username, password }).then(res => {
      btn.disabled = false;
      if (res.success) { err.textContent = ''; showApp(res.data.username || username); }
      else { err.textContent = res.data || 'Login failed.'; }
    });
  }

  function doRegister() {
    const btn      = document.getElementById('register-btn');
    const err      = document.getElementById('register-error');
    const username = document.getElementById('reg-username').value.trim();
    const email    = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value;
    if (!username || !email || !password) { err.textContent = 'All fields are required.'; return; }
    btn.disabled = true;
    err.textContent = 'Creating account\u2026';
    ajax('cg_register_user', { username, email, password }).then(res => {
      btn.disabled = false;
      if (res.success) { err.textContent = ''; showApp(res.data.username || username); }
      else { err.textContent = res.data || 'Registration failed.'; }
    });
  }

  document.getElementById('login-btn').addEventListener('click', doLogin);
  document.getElementById('login-username').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
  document.getElementById('login-password').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
  document.getElementById('register-btn').addEventListener('click', doRegister);

  document.getElementById('cg-logout-btn').addEventListener('click', () => {
    ajax('cg_logout_user', {}).then(() => showAuth());
  });

  let bundleLoaded = false;
  function loadBundle() {
    if (bundleLoaded) return;
    bundleLoaded = true;
    const s = document.createElement('script');
    s.src = '/assets/js/dist/core.bundle.js';
    document.body.appendChild(s);
  }

  /* ── View switching ─────────────────────────────────────────────────────── */

  const listView   = document.getElementById('cg-char-list-view');
  const wizardView = document.getElementById('cg-wizard-view');

  function showListView() {
    listView.style.display   = 'flex';
    wizardView.style.display = 'none';
  }

  function showWizardView(title) {
    listView.style.display   = 'none';
    wizardView.style.display = 'flex';
    document.getElementById('cg-wizard-title').textContent = title || 'Character Builder';
  }

  if (loggedIn) {
    loadBundle();
    showListView();
    loadCharacterList();
  }

  /* ── Character card list ────────────────────────────────────────────────── */

  function formatDate(str) {
    if (!str) return '';
    try {
      const d = new Date(str.replace(' ', 'T'));
      return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    } catch (_) { return str; }
  }

  function loadCharacterList() {
    const grid = document.getElementById('cg-char-grid');
    grid.innerHTML = '<div class="char-list-loading">Loading characters\u2026</div>';
    ajax('cg_load_characters', {}).then(res => {
      if (!res.success) { grid.innerHTML = '<div class="char-list-empty"><strong>Could not load characters.</strong>' + (res.data || '') + '</div>'; return; }
      const chars = res.data || [];
      if (!chars.length) {
        grid.innerHTML = '<div class="char-list-empty"><strong>No characters yet.</strong>Click \u201cNew Character\u201d to create your first.</div>';
        return;
      }
      grid.innerHTML = chars.map(c => {
        const name    = c.name    || '(Unnamed)';
        const species = c.species_name || 'Unknown';
        const career  = c.career_name  || 'Unknown';
        const updated = c.updated ? formatDate(c.updated) : '';
        return `<div class="char-card" data-char-id="${c.id}">
          <h3 class="char-card__name">${escHtml(name)}</h3>
          <p class="char-card__meta">
            <strong>Species:</strong> ${escHtml(species)}<br>
            <strong>Career:</strong>  ${escHtml(career)}
          </p>
          ${updated ? `<div class="char-card__updated">Last updated: ${escHtml(updated)}</div>` : ''}
          <div class="char-card__actions">
            <button class="btn-sm btn-edit" data-action="edit" data-id="${c.id}" data-name="${escHtml(name)}">Edit</button>
            <button class="btn-sm btn-delete" data-action="delete" data-id="${c.id}" data-name="${escHtml(name)}">Delete</button>
          </div>
        </div>`;
      }).join('');
    });
  }

  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  document.getElementById('cg-new-char-btn').addEventListener('click', () => {
    showWizardView('New Character');
    wizardGoToStep(0);
    document.getElementById('cg-new-splash').click();
  });

  document.getElementById('cg-char-grid').addEventListener('click', e => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const id   = btn.dataset.id;
    const name = btn.dataset.name;
    if (btn.dataset.action === 'edit') {
      showWizardView('Edit: ' + name);
      wizardGoToStep(0);
      const sel = document.getElementById('cg-splash-load-select');
      sel.value = id;
      if (!sel.value || sel.value !== String(id)) {
        document.dispatchEvent(new CustomEvent('cg:characters:refresh', { detail: { source: 'card-edit' } }));
        setTimeout(() => {
          sel.value = id;
          document.getElementById('cg-load-splash').click();
        }, 300);
      } else {
        document.getElementById('cg-load-splash').click();
      }
    } else if (btn.dataset.action === 'delete') {
      if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
      ajax('cg_delete_character', { id }).then(res => {
        if (res.success) { loadCharacterList(); }
        else { alert('Delete failed: ' + (res.data || 'Unknown error.')); }
      });
    }
  });

  /* ── Wizard step navigation ─────────────────────────────────────────────── */

  const WIZARD_STEPS = [
    { tab: 'tab-details',     label: 'Details' },
    { tab: 'tab-traits',      label: 'Traits & Species & Career' },
    { tab: 'tab-gifts',       label: 'Gifts' },
    { tab: 'tab-skills',      label: 'Skills' },
    { tab: 'tab-trappings',   label: 'Battle & Equipment' },
    { tab: 'tab-description', label: 'Description' },
    { tab: 'tab-summary',     label: 'Character Sheet' },
  ];

  let currentStep = 0;

  (function buildProgressBar() {
    const bar = document.getElementById('cg-wizard-progress');
    bar.innerHTML = WIZARD_STEPS.map((s, i) => `
      <div class="wizard-step-tab" id="wstep-${i}">
        <span class="step-num">${i + 1}</span>
        ${escHtml(s.label)}
      </div>`).join('');
  })();

  function wizardGoToStep(idx) {
    if (idx < 0 || idx >= WIZARD_STEPS.length) return;
    currentStep = idx;

    WIZARD_STEPS.forEach((s, i) => {
      const el = document.getElementById('wstep-' + i);
      if (!el) return;
      el.classList.toggle('active', i === idx);
      el.classList.toggle('done',   i <  idx);
    });

    document.getElementById('cg-wizard-back').disabled = (idx === 0);
    const nextBtn = document.getElementById('cg-wizard-next');
    nextBtn.textContent = (idx === WIZARD_STEPS.length - 1) ? 'Finish \u2192' : 'Next \u2192';

    const tabId  = WIZARD_STEPS[idx].tab;
    const hidden = document.querySelector('.cg-tabs li[data-tab="' + tabId + '"]');
    if (hidden) hidden.click();
  }

  document.getElementById('cg-wizard-back').addEventListener('click', () => {
    wizardGoToStep(currentStep - 1);
  });

  document.getElementById('cg-wizard-next').addEventListener('click', () => {
    if (currentStep === 1) {
      const speciesSel = document.getElementById('cg-species');
      if (speciesSel && !speciesSel.value) {
        alert('Please select a Species before continuing.');
        return;
      }
    }
    if (currentStep < WIZARD_STEPS.length - 1) {
      wizardGoToStep(currentStep + 1);
    } else {
      document.getElementById('cg-wizard-close-btn').click();
    }
  });

  document.getElementById('cg-wizard-close-btn').addEventListener('click', () => {
    const closeBtn = document.getElementById('cg-modal-close');
    if (closeBtn) { closeBtn.click(); }
    else {
      showListView();
      loadCharacterList();
    }
  });

  /* ── Ally button — mirrors the hidden tab-ally LI visibility ───────────── */

  (function setupAllyButton() {
    const allyBtn = document.getElementById('cg-wizard-ally-btn');
    if (!allyBtn) return;

    function syncAllyBtn() {
      const allyTab = document.querySelector('.cg-tabs li[data-tab="tab-ally"]');
      const visible = allyTab && allyTab.style.display !== 'none' && getComputedStyle(allyTab).display !== 'none';
      allyBtn.style.display = visible ? '' : 'none';
    }

    allyBtn.addEventListener('click', () => {
      const allyTab = document.querySelector('.cg-tabs li[data-tab="tab-ally"]');
      if (allyTab) allyTab.click();
    });

    const obs = new MutationObserver(syncAllyBtn);
    obs.observe(document.body, { attributes: true, attributeFilter: ['style'], subtree: true });
  })();

  /* ── Listen for builder opened/closed events ────────────────────────────── */

  document.addEventListener('cg:builder:opened', () => {
    wizardGoToStep(0);
  });

  document.addEventListener('cg:builder:closed', () => {
    showListView();
    loadCharacterList();
  });

})();
</script>
</body>
</html>
