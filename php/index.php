<?php
require_once __DIR__ . '/includes/auth.php';
cg_session_start();
$loggedIn = cg_is_logged_in();
$username = $loggedIn ? htmlspecialchars($_SESSION['cg_username'] ?? '') : '';
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

    .app-main {
      flex:            1;
      display:         flex;
      align-items:     center;
      justify-content: center;
      padding:         3rem 2rem;
    }

    .app-hero { text-align: center; max-width: 480px; }

    .app-hero__ornament {
      font-size:     2rem;
      color:         var(--gold);
      display:       block;
      margin-bottom: 1rem;
      opacity:       0.7;
    }

    .app-hero__title {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      2rem;
      font-weight:    700;
      color:          var(--gold-light);
      letter-spacing: 0.05em;
      margin:         0 0 0.4rem;
      line-height:    1.2;
    }

    .app-hero__subtitle {
      font-family:    'Cinzel', Georgia, serif;
      font-size:      0.8rem;
      color:          var(--text-muted);
      letter-spacing: 0.15em;
      text-transform: uppercase;
      margin:         0 0 2rem;
    }

    .app-hero__rule {
      height:     1px;
      background: linear-gradient(90deg, transparent, var(--gold-border), transparent);
      margin:     0 auto 2.5rem;
      width:      80%;
    }

    .app-hero__cta { padding: 0.9rem 2.5rem; font-size: 0.9rem; }

    #cg-open-builder-trigger { display: none; }

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
    <a href="https://libraryofcalbria.com" class="site-title">
      The Library of Calabria
      <span class="site-title-sub">Character Generator</span>
    </a>
  </header>

  <div id="site-body">

    <nav id="site-nav" aria-label="Site navigation">
      <a href="https://libraryofcalbria.com/" class="nav-item parent">Ironclaw</a>
      <a href="https://libraryofcalbria.com/ironclaw-books/" class="nav-item child">Ironclaw Books</a>
      <a href="https://libraryofcalbria.com/species/" class="nav-item child">Species</a>
      <a href="https://libraryofcalbria.com/careers/" class="nav-item child">Careers</a>
      <a href="https://libraryofcalbria.com/gifts/" class="nav-item child">Gifts</a>
      <a href="https://libraryofcalbria.com/skills/" class="nav-item child">Skills</a>
      <a href="https://libraryofcalbria.com/index/" class="nav-item child">Index</a>
      <div class="nav-divider"></div>
      <a href="https://characters.libraryofcalbria.com/" class="nav-item active">Character Generator</a>
      <a href="https://libraryofcalbria.com/corrections-log/" class="nav-item">Corrections Log</a>
      <a href="https://libraryofcalbria.com/updates/" class="nav-item">Updates</a>
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

        <main class="app-main">
          <div class="app-hero">
            <span class="app-hero__ornament">✦ ◆ ✦</span>
            <h2 class="app-hero__title">Your Characters</h2>
            <p class="app-hero__subtitle">Library of Calabria &mdash; Character Records</p>
            <div class="app-hero__rule"></div>
            <button id="cg-open-builder" class="btn btn-gold app-hero__cta">
              ✦ &nbsp; Open Character Builder
            </button>
          </div>
        </main>

        <div id="cg-modal-splash" class="cg-modal-splash cg-hidden">
          <div class="cg-modal-splash__content">
            <h2>Character Builder</h2>
            <p style="color:var(--text-muted);font-family:'Crimson Pro',Georgia,serif;font-size:0.95rem;margin:0 0 1.5rem">Begin a new tale or continue an existing one.</p>
            <button id="cg-new-splash" class="button primary">✦ &nbsp; New Character</button>
            <div class="cg-splash-load">
              <label for="cg-splash-load-select">Load an existing character</label>
              <select id="cg-splash-load-select">
                <option value="">— Loading… —</option>
              </select>
              <button id="cg-load-splash" class="button">Load Character</button>
            </div>
          </div>
        </div>

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
    document.getElementById('cg-username-display').textContent = username;
    loadBundle();
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

  if (loggedIn) {
    loadBundle();
  }
})();
</script>
</body>
</html>
