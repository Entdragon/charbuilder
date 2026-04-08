<?php
$pageTitle = 'Character Builder';
$activeNav = 'builder';
require_once __DIR__ . '/../../includes/auth.php';
cg_session_start();
cg_try_wp_sso();
$loggedIn = cg_is_logged_in();
$username = $loggedIn ? htmlspecialchars($_SESSION['cg_username'] ?? '') : '';
require __DIR__ . '/../layout-head.php';
?>
<style>
  /* ── Builder overrides — fill the content area ─────────────── */
  #uj-content { padding: 0; display: flex; flex-direction: column; }

  /* ── Auth screen ─────────────────────────────────────────────── */
  #uj-auth-screen {
    display:         flex;
    align-items:     center;
    justify-content: center;
    flex:            1;
    padding:         3rem 2rem;
  }

  .uj-auth-card {
    background:    var(--uj-surface);
    border:        1px solid var(--uj-border);
    border-radius: var(--uj-radius-lg);
    padding:       0 2rem 2.5rem;
    width:         100%;
    max-width:     400px;
    box-shadow:    0 24px 64px rgba(0,0,0,0.5);
    overflow:      hidden;
  }

  .uj-auth-accent {
    height:     3px;
    background: linear-gradient(90deg, transparent, var(--uj-teal), var(--uj-amber), var(--uj-teal), transparent);
    margin:     0 -2rem 2.5rem;
  }

  .uj-auth-card h2 {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      1.4rem;
    color:          var(--uj-amber-light);
    text-align:     center;
    margin:         0 0 0.25rem;
    letter-spacing: 0.05em;
  }

  .uj-auth-card .subtitle {
    text-align:     center;
    color:          var(--uj-text-dim);
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.72rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-bottom:  2rem;
  }

  .uj-auth-tabs {
    display:       flex;
    border-bottom: 1px solid rgba(244,166,34,0.15);
    margin-bottom: 1.5rem;
  }

  .uj-auth-tab {
    flex:           1;
    background:     none;
    border:         none;
    border-bottom:  2px solid transparent;
    margin-bottom:  -1px;
    color:          var(--uj-text-dim);
    padding:        0.5rem;
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.78rem;
    font-weight:    600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor:         pointer;
    transition:     all 0.2s;
  }

  .uj-auth-tab.active   { color: var(--uj-amber); border-bottom-color: var(--uj-amber); }
  .uj-auth-tab:hover:not(.active) { color: var(--uj-text-muted); }

  .uj-auth-form        { display: none; flex-direction: column; gap: 1rem; }
  .uj-auth-form.active { display: flex; }

  .uj-auth-field label {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.7rem;
    font-weight:    600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color:          var(--uj-text-muted);
    display:        block;
    margin-bottom:  0.35rem;
  }

  .uj-auth-field input {
    width:         100%;
    padding:       0.6rem 0.85rem;
    background:    rgba(10,14,24,0.6);
    border:        1px solid rgba(244,166,34,0.18);
    border-radius: var(--uj-radius);
    color:         var(--uj-text);
    font-family:   'Crimson Pro', Georgia, serif;
    font-size:     1rem;
    outline:       none;
    transition:    border-color 0.2s;
  }

  .uj-auth-field input:focus { border-color: var(--uj-amber); }
  .uj-auth-field input::placeholder { color: var(--uj-text-dim); }

  .uj-auth-submit {
    width:          100%;
    padding:        0.75rem;
    background:     linear-gradient(135deg, var(--uj-amber-dark), var(--uj-amber), var(--uj-amber-dark));
    border:         none;
    border-radius:  var(--uj-radius);
    color:          var(--uj-bg);
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.82rem;
    font-weight:    700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor:         pointer;
    transition:     opacity 0.2s;
    margin-top:     0.25rem;
  }

  .uj-auth-submit:hover { opacity: 0.85; }

  .uj-auth-error {
    color:      var(--uj-error);
    font-size:  0.9rem;
    text-align: center;
    min-height: 1.2em;
    font-style: italic;
  }

  /* ── Builder app screen ───────────────────────────────────────── */
  #uj-builder-screen {
    display:        none;
    flex:           1;
    flex-direction: column;
  }

  .builder-topbar {
    display:         flex;
    align-items:     center;
    justify-content: space-between;
    padding:         0.5rem 1.5rem;
    background:      var(--uj-surface);
    border-bottom:   1px solid var(--uj-border-cool);
    gap:             1rem;
    flex-shrink:     0;
  }

  .builder-topbar-user {
    font-size:  0.88rem;
    color:      var(--uj-text-muted);
  }

  .builder-topbar-user strong { color: var(--uj-text); }

  .builder-topbar-actions { display: flex; gap: 0.5rem; }

  .uj-btn {
    display:         inline-flex;
    align-items:     center;
    gap:             0.4rem;
    padding:         0.45rem 1rem;
    font-family:     'Cinzel', Georgia, serif;
    font-size:       0.75rem;
    font-weight:     600;
    letter-spacing:  0.06em;
    text-transform:  uppercase;
    border-radius:   var(--uj-radius);
    cursor:          pointer;
    transition:      all 0.18s;
    border:          none;
  }

  .uj-btn-amber {
    background: linear-gradient(135deg, var(--uj-amber-dark), var(--uj-amber));
    color:      var(--uj-bg);
  }

  .uj-btn-amber:hover { opacity: 0.85; }

  .uj-btn-ghost {
    background:   transparent;
    color:        var(--uj-text-muted);
    border:       1px solid var(--uj-border-cool);
  }

  .uj-btn-ghost:hover { color: var(--uj-amber-light); border-color: var(--uj-amber-border); }

  .uj-btn-teal {
    background: linear-gradient(135deg, var(--uj-teal-dark), var(--uj-teal));
    color:      var(--uj-bg);
  }

  .uj-btn-teal:hover { opacity: 0.85; }

  .uj-btn-danger {
    background: transparent;
    color:      var(--uj-error);
    border:     1px solid rgba(248,113,113,0.3);
    font-size:  0.7rem;
    padding:    0.3rem 0.7rem;
  }

  .uj-btn-danger:hover { background: rgba(248,113,113,0.1); }

  /* ── Builder main area ────────────────────────────────────────── */
  #uj-builder-main {
    flex:       1;
    overflow-y: auto;
    padding:    1.5rem 2rem;
  }

  /* ── Character list screen ────────────────────────────────────── */
  #uj-char-list-screen {}

  .char-list-header {
    display:         flex;
    align-items:     center;
    justify-content: space-between;
    margin-bottom:   1.25rem;
  }

  .char-list-header h2 {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      1.2rem;
    color:          var(--uj-amber-light);
    margin:         0;
    letter-spacing: 0.05em;
  }

  .char-cards {
    display:               grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap:                   0.75rem;
    margin-bottom:         1.5rem;
  }

  .char-card {
    background:    var(--uj-surface);
    border:        1px solid var(--uj-border-cool);
    border-radius: var(--uj-radius-lg);
    padding:       1.1rem 1.25rem;
    cursor:        pointer;
    transition:    border-color 0.2s, box-shadow 0.2s;
  }

  .char-card:hover {
    border-color: var(--uj-amber-border);
    box-shadow:   0 4px 16px rgba(0,0,0,0.3);
  }

  .char-card-name {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      1.05rem;
    font-weight:    700;
    color:          var(--uj-amber-light);
    margin:         0 0 0.4rem;
  }

  .char-card-detail {
    font-size:   0.85rem;
    color:       var(--uj-text-muted);
    line-height: 1.5;
  }

  .char-card-footer {
    display:         flex;
    justify-content: space-between;
    align-items:     center;
    margin-top:      0.75rem;
    padding-top:     0.6rem;
    border-top:      1px solid var(--uj-border-light);
  }

  .char-card-date { font-size: 0.78rem; color: var(--uj-text-dim); }

  .char-empty {
    text-align:   center;
    color:        var(--uj-text-dim);
    padding:      3rem 2rem;
    font-size:    1rem;
    font-style:   italic;
  }

  /* ── Wizard screen ────────────────────────────────────────────── */
  #uj-wizard-screen { display: none; }

  .wizard-header {
    display:         flex;
    align-items:     center;
    justify-content: space-between;
    margin-bottom:   1.5rem;
    gap:             1rem;
  }

  .wizard-title {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      1.1rem;
    color:          var(--uj-amber-light);
    margin:         0;
    letter-spacing: 0.04em;
  }

  /* progress bar */
  .wizard-progress {
    display:  flex;
    gap:      0.35rem;
    align-items: center;
  }

  .wp-step {
    width:         28px;
    height:        28px;
    border-radius: 50%;
    border:        2px solid var(--uj-border-cool);
    background:    var(--uj-surface-2);
    color:         var(--uj-text-dim);
    font-family:   'Cinzel', Georgia, serif;
    font-size:     0.7rem;
    font-weight:   700;
    display:       flex;
    align-items:   center;
    justify-content: center;
    transition:    all 0.2s;
    flex-shrink:   0;
  }

  .wp-step.done   { background: var(--uj-teal-dark); border-color: var(--uj-teal); color: #fff; }
  .wp-step.active { background: var(--uj-amber);     border-color: var(--uj-amber); color: var(--uj-bg); }

  .wp-line {
    flex:        1;
    height:      2px;
    background:  var(--uj-border-cool);
    min-width:   16px;
    max-width:   32px;
    transition:  background 0.2s;
  }

  .wp-line.done { background: var(--uj-teal-dark); }

  /* step panels */
  .wizard-step { display: none; }
  .wizard-step.active { display: block; }

  .step-heading {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.85rem;
    font-weight:    700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color:          var(--uj-teal);
    margin:         0 0 1rem;
  }

  /* selection grids */
  .select-grid {
    display:               grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap:                   0.6rem;
    margin-bottom:         1.25rem;
  }

  .select-card {
    background:    var(--uj-surface);
    border:        2px solid var(--uj-border-cool);
    border-radius: var(--uj-radius-lg);
    padding:       0.9rem 1rem;
    cursor:        pointer;
    transition:    border-color 0.15s, box-shadow 0.15s;
  }

  .select-card:hover { border-color: var(--uj-amber-border); }

  .select-card.selected {
    border-color: var(--uj-amber);
    background:   rgba(244,166,34,0.06);
    box-shadow:   0 0 0 1px var(--uj-amber);
  }

  .select-card-name {
    font-family:   'Cinzel', Georgia, serif;
    font-size:     0.9rem;
    font-weight:   700;
    color:         var(--uj-amber-light);
    margin:        0 0 0.25rem;
  }

  .select-card-tags {
    display:   flex;
    gap:       0.3rem;
    flex-wrap: wrap;
    margin:    0;
  }

  /* detail panel */
  .detail-panel {
    background:    var(--uj-surface);
    border:        1px solid var(--uj-border);
    border-radius: var(--uj-radius-lg);
    padding:       1.25rem;
    margin-bottom: 1.25rem;
    min-height:    80px;
  }

  .detail-panel-empty {
    color:      var(--uj-text-dim);
    font-style: italic;
    font-size:  0.9rem;
  }

  .detail-panel-name {
    font-family:   'Cinzel', Georgia, serif;
    font-size:     1.1rem;
    font-weight:   700;
    color:         var(--uj-amber-light);
    margin:        0 0 0.5rem;
  }

  .detail-panel-desc {
    font-size:   0.92rem;
    color:       var(--uj-text-muted);
    line-height: 1.5;
    margin:      0 0 1rem;
  }

  .detail-grants {
    display:   flex;
    gap:       1.5rem;
    flex-wrap: wrap;
  }

  .detail-grant-group {}

  .detail-grant-label {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.65rem;
    font-weight:    700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color:          var(--uj-text-dim);
    margin:         0 0 0.3rem;
  }

  .detail-grant-list {
    list-style:   none;
    padding:      0;
    margin:       0;
    display:      flex;
    flex-direction: column;
    gap:          0.2rem;
  }

  .detail-grant-list li {
    font-size:     0.88rem;
    color:         var(--uj-text);
    padding:       0.2rem 0.5rem;
    border-radius: 3px;
    background:    var(--uj-surface-2);
    border-left:   2px solid var(--uj-teal);
  }

  .detail-grant-list li.gift-item { border-left-color: var(--uj-amber); }
  .detail-grant-list li.soak-item { border-left-color: var(--uj-text-dim); }
  .detail-grant-list li.gear-item { border-left-color: #fbbf24; font-size: 0.82rem; color: var(--uj-text-muted); }

  /* dice step */
  .dice-grid {
    display:               grid;
    grid-template-columns: repeat(4, 1fr);
    gap:                   1rem;
    margin-bottom:         1.25rem;
    max-width:             640px;
  }

  .dice-trait {
    background:    var(--uj-surface);
    border:        1px solid var(--uj-border-cool);
    border-radius: var(--uj-radius-lg);
    padding:       1rem;
    text-align:    center;
  }

  .dice-trait-name {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.8rem;
    font-weight:    700;
    color:          var(--uj-text-muted);
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin:         0 0 0.75rem;
  }

  .dice-select {
    width:         100%;
    background:    var(--uj-surface-2);
    border:        1px solid var(--uj-border);
    border-radius: var(--uj-radius);
    color:         var(--uj-amber);
    font-family:   'Cinzel', Georgia, serif;
    font-size:     1rem;
    font-weight:   700;
    padding:       0.5rem;
    text-align:    center;
    cursor:        pointer;
    outline:       none;
    transition:    border-color 0.2s;
  }

  .dice-select:focus { border-color: var(--uj-amber); }

  .dice-hint {
    font-size:  0.8rem;
    color:      var(--uj-text-dim);
    margin:     0.75rem 0 0;
    font-style: italic;
  }

  .dice-error {
    color:      var(--uj-error);
    font-size:  0.88rem;
    margin:     0.5rem 0 0;
    display:    none;
  }

  /* personality step */
  .personality-row {
    display:  flex;
    gap:      1.5rem;
    flex-wrap:wrap;
    margin-bottom: 1.25rem;
  }

  .personality-field {
    flex: 1;
    min-width: 200px;
  }

  .field-label {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.72rem;
    font-weight:    600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color:          var(--uj-text-muted);
    display:        block;
    margin-bottom:  0.4rem;
  }

  .field-input, .field-select, .field-textarea {
    width:         100%;
    padding:       0.6rem 0.85rem;
    background:    var(--uj-surface-2);
    border:        1px solid var(--uj-border-cool);
    border-radius: var(--uj-radius);
    color:         var(--uj-text);
    font-family:   'Crimson Pro', Georgia, serif;
    font-size:     1rem;
    outline:       none;
    transition:    border-color 0.2s;
  }

  .field-input:focus, .field-select:focus, .field-textarea:focus { border-color: var(--uj-amber); }
  .field-select option { background: var(--uj-surface-2); }
  .field-textarea { resize: vertical; min-height: 80px; }

  /* Summary */
  .summary-char-name {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      1.6rem;
    font-weight:    700;
    color:          var(--uj-amber-light);
    margin:         0 0 0.25rem;
    letter-spacing: 0.04em;
  }

  .summary-subtitle {
    font-size:   0.9rem;
    color:       var(--uj-text-muted);
    margin:      0 0 1.5rem;
    font-style:  italic;
  }

  .summary-grid {
    display:               grid;
    grid-template-columns: 1fr 1fr;
    gap:                   1.25rem;
    margin-bottom:         1.5rem;
  }

  @media (max-width: 600px) { .summary-grid { grid-template-columns: 1fr; } }

  .summary-section {
    background:    var(--uj-surface);
    border:        1px solid var(--uj-border-cool);
    border-radius: var(--uj-radius-lg);
    padding:       1rem 1.25rem;
  }

  .summary-section-title {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.72rem;
    font-weight:    700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color:          var(--uj-teal);
    margin:         0 0 0.6rem;
    padding-bottom: 0.4rem;
    border-bottom:  1px solid var(--uj-border-cool);
  }

  .summary-list {
    list-style:    none;
    padding:       0;
    margin:        0;
    display:       flex;
    flex-direction:column;
    gap:           0.25rem;
  }

  .summary-list li {
    font-size:     0.9rem;
    color:         var(--uj-text);
    padding:       0.25rem 0.5rem;
    border-left:   2px solid var(--uj-teal);
    background:    var(--uj-surface-2);
    border-radius: 0 3px 3px 0;
  }

  .summary-list li.gift-item { border-left-color: var(--uj-amber); }
  .summary-list li.soak-item { border-left-color: var(--uj-text-dim); }
  .summary-list li small     { color: var(--uj-text-dim); font-size: 0.78rem; display: block; }

  .summary-traits {
    display:               grid;
    grid-template-columns: repeat(4, 1fr);
    gap:                   0.5rem;
    margin-bottom:         1.25rem;
  }

  .summary-trait {
    background:    var(--uj-surface);
    border:        1px solid var(--uj-border-cool);
    border-radius: var(--uj-radius-lg);
    padding:       0.75rem;
    text-align:    center;
  }

  .summary-trait-name {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.7rem;
    color:          var(--uj-text-dim);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin:         0 0 0.3rem;
  }

  .summary-trait-die {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      1.4rem;
    font-weight:    700;
    color:          var(--uj-amber);
  }

  .summary-personality {
    background:    var(--uj-surface);
    border:        1px solid var(--uj-amber-border);
    border-radius: var(--uj-radius-lg);
    padding:       0.9rem 1.25rem;
    margin-bottom: 1.25rem;
    display:       flex;
    align-items:   center;
    gap:           1rem;
  }

  .summary-personality-label {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.7rem;
    color:          var(--uj-text-dim);
    letter-spacing: 0.1em;
    text-transform: uppercase;
  }

  .summary-personality-word {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      1.2rem;
    font-weight:    700;
    color:          var(--uj-amber-light);
  }

  /* wizard nav buttons */
  .wizard-nav {
    display:         flex;
    justify-content: space-between;
    align-items:     center;
    margin-top:      1.5rem;
    padding-top:     1rem;
    border-top:      1px solid var(--uj-border-light);
    gap:             1rem;
  }

  .wizard-nav-right { display: flex; gap: 0.5rem; }

  /* save status */
  .save-status {
    font-size:  0.82rem;
    color:      var(--uj-text-dim);
    font-style: italic;
  }

  .save-status.saving  { color: var(--uj-amber); }
  .save-status.saved   { color: var(--uj-success); }
  .save-status.error   { color: var(--uj-error); }

  /* print view */
  @media print {
    #uj-nav, .builder-topbar, .wizard-header, .wizard-progress,
    .wizard-nav, .builder-topbar-actions { display: none !important; }
    #uj-content { padding: 0; }
    #uj-builder-main { padding: 1rem; }
    body { background: #fff !important; color: #000 !important; }
  }
</style>

<?php if (!$loggedIn): ?>
<div id="uj-auth-screen">
  <div class="uj-auth-card">
    <div class="uj-auth-accent"></div>
    <h2>Character Builder</h2>
    <p class="subtitle">Urban Jungle</p>

    <div class="uj-auth-tabs">
      <button class="uj-auth-tab active" data-tab="login">Sign In</button>
      <button class="uj-auth-tab" data-tab="register">Register</button>
    </div>

    <form class="uj-auth-form active" id="uj-login-form">
      <div class="uj-auth-field">
        <label>Username</label>
        <input type="text" name="username" autocomplete="username" required>
      </div>
      <div class="uj-auth-field">
        <label>Password</label>
        <input type="password" name="password" autocomplete="current-password" required>
      </div>
      <div class="uj-auth-error" id="uj-login-error"></div>
      <button type="submit" class="uj-auth-submit">Sign In</button>
    </form>

    <form class="uj-auth-form" id="uj-register-form">
      <div class="uj-auth-field">
        <label>Username</label>
        <input type="text" name="username" autocomplete="username" required>
      </div>
      <div class="uj-auth-field">
        <label>Password</label>
        <input type="password" name="password" autocomplete="new-password" required>
      </div>
      <div class="uj-auth-error" id="uj-register-error"></div>
      <button type="submit" class="uj-auth-submit">Create Account</button>
    </form>
  </div>
</div>
<?php else: ?>
<div id="uj-builder-screen" style="display:flex; flex-direction:column; flex:1;">
  <div class="builder-topbar">
    <div class="builder-topbar-user">Signed in as <strong><?= $username ?></strong></div>
    <div class="builder-topbar-actions">
      <button class="uj-btn uj-btn-ghost" id="uj-logout-btn">Sign Out</button>
    </div>
  </div>
  <div id="uj-builder-main">
    <div id="uj-builder-loading" style="text-align:center; padding:3rem; color:var(--uj-text-dim); font-style:italic;">Loading game data…</div>
    <div id="uj-char-list-screen" style="display:none;"></div>
    <div id="uj-wizard-screen" style="display:none;"></div>
  </div>
</div>
<?php endif; ?>

<script>
(function() {
  var LOGGED_IN = <?= $loggedIn ? 'true' : 'false' ?>;

  /* ── Auth tabs (for not-logged-in view) ───────────────────────── */
  document.querySelectorAll('.uj-auth-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
      document.querySelectorAll('.uj-auth-tab').forEach(function(t) { t.classList.remove('active'); });
      document.querySelectorAll('.uj-auth-form').forEach(function(f) { f.classList.remove('active'); });
      tab.classList.add('active');
      var target = document.getElementById('uj-' + tab.dataset.tab + '-form');
      if (target) target.classList.add('active');
    });
  });

  function ajaxPost(action, data) {
    return new Promise(function(resolve, reject) {
      var fd = new FormData();
      fd.append('action', action);
      for (var k in data) {
        if (typeof data[k] === 'object') {
          fd.append(k, JSON.stringify(data[k]));
        } else {
          fd.append(k, data[k]);
        }
      }
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '/ajax.php');
      xhr.onload = function() {
        try { resolve(JSON.parse(xhr.responseText)); }
        catch(e) { reject(e); }
      };
      xhr.onerror = function() { reject(new Error('Network error')); };
      xhr.send(fd);
    });
  }

  /* ── Login form ─────────────────────────────────────────────── */
  var loginForm = document.getElementById('uj-login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var err = document.getElementById('uj-login-error');
      err.textContent = '';
      var fd = new FormData(loginForm);
      ajaxPost('cg_login_user', {
        username: fd.get('username'),
        password: fd.get('password')
      }).then(function(res) {
        if (res.success) { window.location.reload(); }
        else { err.textContent = res.data || 'Login failed.'; }
      }).catch(function() { err.textContent = 'Network error.'; });
    });
  }

  var registerForm = document.getElementById('uj-register-form');
  if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var err = document.getElementById('uj-register-error');
      err.textContent = '';
      var fd = new FormData(registerForm);
      ajaxPost('cg_register_user', {
        username: fd.get('username'),
        password: fd.get('password')
      }).then(function(res) {
        if (res.success) { window.location.reload(); }
        else { err.textContent = res.data || 'Registration failed.'; }
      }).catch(function() { err.textContent = 'Network error.'; });
    });
  }

  if (!LOGGED_IN) return;

  /* ── Logout ─────────────────────────────────────────────────── */
  var logoutBtn = document.getElementById('uj-logout-btn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
      ajaxPost('cg_logout_user', {}).then(function() { window.location.reload(); });
    });
  }

  /* ═══════════════════════════════════════════════════════════════
     UJ CHARACTER BUILDER
  ═══════════════════════════════════════════════════════════════ */

  var state = {
    allData:       null,
    personalities: [],
    characters:    [],
    currentChar:   null,
    // wizard selections
    speciesId:     null,
    typeId:        null,
    careerId:      null,
    bodyDie:       'd6',
    speedDie:      'd6',
    mindDie:       'd6',
    willDie:       'd6',
    personalityWord: '',
    charName:      '',
    notes:         '',
    currentStep:   0,
    saving:        false,
    saveTimeout:   null,
  };

  var DICE_POOL = ['d8', 'd8', 'd6', 'd4'];
  var STEP_LABELS = ['Species', 'Type', 'Career', 'Traits', 'Personality', 'Summary'];

  /* ── DOM refs ──────────────────────────────────────────────── */
  var loadingEl   = document.getElementById('uj-builder-loading');
  var listScreen  = document.getElementById('uj-char-list-screen');
  var wizardScreen= document.getElementById('uj-wizard-screen');

  /* ── Boot ─────────────────────────────────────────────────── */
  Promise.all([
    ajaxPost('uj_get_all_full', {}),
    ajaxPost('cg_get_personality_list', {}),
    ajaxPost('uj_load_characters', {}),
  ]).then(function(results) {
    if (results[0].success) state.allData       = results[0].data;
    if (results[1].success) state.personalities = results[1].data || [];
    if (results[2].success) state.characters    = results[2].data || [];
    loadingEl.style.display = 'none';
    showCharList();
  }).catch(function(err) {
    loadingEl.textContent = 'Error loading data. Please refresh.';
    console.error(err);
  });

  /* ─────────────────────────────────────────────────────────────
     CHARACTER LIST
  ───────────────────────────────────────────────────────────── */
  function showCharList() {
    wizardScreen.style.display = 'none';
    listScreen.style.display = 'block';
    renderCharList();
  }

  function renderCharList() {
    var html = '<div class="char-list-header">' +
      '<h2>My Characters</h2>' +
      '<button class="uj-btn uj-btn-amber" id="uj-new-char-btn">+ New Character</button>' +
      '</div>';

    if (state.characters.length === 0) {
      html += '<div class="char-empty">No characters yet. Create your first one!</div>';
    } else {
      html += '<div class="char-cards">';
      state.characters.forEach(function(c) {
        var sp = state.allData ? (state.allData.species || []).find(function(x) { return x.id == c.species_id; }) : null;
        var ty = state.allData ? (state.allData.types   || []).find(function(x) { return x.id == c.type_id;    }) : null;
        var ca = state.allData ? (state.allData.careers || []).find(function(x) { return x.id == c.career_id;  }) : null;
        var detail = [sp ? sp.name : null, ty ? ty.name : null, ca ? ca.name : null].filter(Boolean).join(' / ');
        var dice = [c.body_die, c.speed_die, c.mind_die, c.will_die].filter(Boolean).join(' ');
        var date = c.updated_at ? c.updated_at.substring(0,10) : '';
        html += '<div class="char-card" data-id="' + esc(c.id) + '">' +
          '<div class="char-card-name">' + esc(c.name || '(Unnamed)') + '</div>' +
          '<div class="char-card-detail">' + esc(detail || 'Incomplete') + '</div>' +
          (dice ? '<div class="char-card-detail" style="color:var(--uj-amber); font-family:\'Cinzel\',serif; font-size:0.8rem; margin-top:0.25rem;">' + esc(dice) + '</div>' : '') +
          '<div class="char-card-footer">' +
            '<span class="char-card-date">' + esc(date) + '</span>' +
            '<div style="display:flex;gap:0.4rem;">' +
              '<button class="uj-btn uj-btn-ghost" style="font-size:0.7rem;padding:0.3rem 0.7rem;" data-load="' + esc(c.id) + '">Edit</button>' +
              '<button class="uj-btn-danger uj-btn" data-delete="' + esc(c.id) + '">Delete</button>' +
            '</div>' +
          '</div>' +
        '</div>';
      });
      html += '</div>';
    }
    listScreen.innerHTML = html;

    document.getElementById('uj-new-char-btn').addEventListener('click', function() {
      startNewChar();
    });

    listScreen.querySelectorAll('[data-load]').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        loadChar(btn.dataset.load);
      });
    });

    listScreen.querySelectorAll('[data-delete]').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (!confirm('Delete this character?')) return;
        deleteChar(btn.dataset.delete);
      });
    });

    listScreen.querySelectorAll('.char-card').forEach(function(card) {
      card.addEventListener('click', function() {
        loadChar(card.dataset.id);
      });
    });
  }

  function startNewChar() {
    state.currentChar    = null;
    state.speciesId      = null;
    state.typeId         = null;
    state.careerId       = null;
    state.bodyDie        = 'd6';
    state.speedDie       = 'd6';
    state.mindDie        = 'd6';
    state.willDie        = 'd4';
    state.personalityWord= '';
    state.charName       = '';
    state.notes          = '';
    state.currentStep    = 0;
    showWizard();
  }

  function loadChar(id) {
    ajaxPost('uj_get_character', { id: id }).then(function(res) {
      if (!res.success) { alert(res.data || 'Error loading character.'); return; }
      var c = res.data;
      state.currentChar    = String(c.id);
      state.speciesId      = c.species_id ? Number(c.species_id) : null;
      state.typeId         = c.type_id    ? Number(c.type_id)    : null;
      state.careerId       = c.career_id  ? Number(c.career_id)  : null;
      state.bodyDie        = c.body_die   || 'd6';
      state.speedDie       = c.speed_die  || 'd6';
      state.mindDie        = c.mind_die   || 'd6';
      state.willDie        = c.will_die   || 'd4';
      state.personalityWord= c.personality_word || '';
      state.charName       = c.name       || '';
      state.notes          = c.notes      || '';
      state.currentStep    = 0;
      showWizard();
    });
  }

  function deleteChar(id) {
    ajaxPost('uj_delete_character', { id: id }).then(function() {
      state.characters = state.characters.filter(function(c) { return c.id != id; });
      renderCharList();
    });
  }

  /* ─────────────────────────────────────────────────────────────
     WIZARD
  ───────────────────────────────────────────────────────────── */
  function showWizard() {
    listScreen.style.display  = 'none';
    wizardScreen.style.display = 'block';
    renderWizard();
  }

  function renderWizard() {
    wizardScreen.innerHTML = buildWizardShell();
    renderStep(state.currentStep);
    bindWizardNav();
  }

  function buildWizardShell() {
    var prog = '<div class="wizard-progress">';
    STEP_LABELS.forEach(function(label, i) {
      if (i > 0) prog += '<div class="wp-line' + (i <= state.currentStep ? ' done' : '') + '"></div>';
      var cls = i < state.currentStep ? 'done' : (i === state.currentStep ? 'active' : '');
      prog += '<div class="wp-step ' + cls + '" title="' + label + '">' + (i + 1) + '</div>';
    });
    prog += '</div>';

    return '<div class="wizard-header">' +
      '<h2 class="wizard-title">' + esc(state.charName || 'New Character') + '</h2>' +
      prog +
      '<button class="uj-btn uj-btn-ghost" id="wiz-back-list-btn">← Characters</button>' +
      '</div>' +
      '<div id="wiz-step-container"></div>' +
      '<div class="wizard-nav">' +
        '<button class="uj-btn uj-btn-ghost" id="wiz-prev-btn"' + (state.currentStep === 0 ? ' disabled' : '') + '>← Back</button>' +
        '<div class="wizard-nav-right">' +
          '<span class="save-status" id="wiz-save-status"></span>' +
          '<button class="uj-btn uj-btn-amber" id="wiz-save-btn">Save</button>' +
          (state.currentStep === STEP_LABELS.length - 1
            ? '<button class="uj-btn uj-btn-teal" id="wiz-print-btn">Print</button>'
            : '<button class="uj-btn uj-btn-teal" id="wiz-next-btn">Next →</button>') +
        '</div>' +
      '</div>';
  }

  function renderStep(step) {
    var container = document.getElementById('wiz-step-container');
    if (!container) return;
    switch(step) {
      case 0: container.innerHTML = buildSpeciesStep(); bindSelectionCards('species', 'speciesId'); break;
      case 1: container.innerHTML = buildTypeStep();    bindSelectionCards('type', 'typeId');       break;
      case 2: container.innerHTML = buildCareerStep();  bindSelectionCards('career', 'careerId');   break;
      case 3: container.innerHTML = buildDiceStep();    bindDiceStep();                             break;
      case 4: container.innerHTML = buildPersonalityStep(); bindPersonalityStep();                  break;
      case 5: container.innerHTML = buildSummaryStep();                                             break;
    }
  }

  function bindWizardNav() {
    var prevBtn = document.getElementById('wiz-prev-btn');
    var nextBtn = document.getElementById('wiz-next-btn');
    var saveBtn = document.getElementById('wiz-save-btn');
    var printBtn= document.getElementById('wiz-print-btn');
    var backBtn = document.getElementById('wiz-back-list-btn');

    if (backBtn) backBtn.addEventListener('click', function() {
      ajaxPost('uj_load_characters', {}).then(function(res) {
        if (res.success) state.characters = res.data || [];
        showCharList();
      });
    });

    if (prevBtn) prevBtn.addEventListener('click', function() {
      if (state.currentStep > 0) { state.currentStep--; renderWizard(); }
    });

    if (nextBtn) nextBtn.addEventListener('click', function() {
      if (validateStep(state.currentStep)) {
        state.currentStep++;
        renderWizard();
      }
    });

    if (saveBtn) saveBtn.addEventListener('click', function() { saveCharacter(); });

    if (printBtn) printBtn.addEventListener('click', function() { window.print(); });
  }

  function validateStep(step) {
    if (step === 0 && !state.speciesId) { alert('Please select a Species.'); return false; }
    if (step === 1 && !state.typeId)    { alert('Please select a Type.');    return false; }
    if (step === 2 && !state.careerId)  { alert('Please select a Career.');  return false; }
    if (step === 3 && !validateDice())  { alert('Please assign all four trait dice. Use one d8, one d8, one d6, and one d4.'); return false; }
    return true;
  }

  function validateDice() {
    var pool = [state.bodyDie, state.speedDie, state.mindDie, state.willDie];
    var sorted  = pool.slice().sort().join(',');
    var expected = DICE_POOL.slice().sort().join(',');
    return sorted === expected;
  }

  /* ─────────────────────────────────────────────────────────────
     STEP BUILDERS
  ───────────────────────────────────────────────────────────── */

  /* ── Step 0: Species ─────────────────────────────────────── */
  function buildSpeciesStep() {
    var species = (state.allData && state.allData.species) || [];
    var html = '<div class="step-heading">Step 1 — Choose Your Species</div>';
    html += buildDetailPanel('species', state.speciesId);
    html += '<div class="select-grid">';
    species.forEach(function(sp) {
      var sel = state.speciesId == sp.id ? ' selected' : '';
      var tags = (sp.skills || []).slice(0,2).map(function(s) {
        return '<span class="tag tag-skill">' + esc(s.name) + '</span>';
      }).join('');
      html += '<div class="select-card' + sel + '" data-id="' + sp.id + '">' +
        '<div class="select-card-name">' + esc(sp.name) + '</div>' +
        '<div class="select-card-tags">' + tags + '</div>' +
      '</div>';
    });
    html += '</div>';
    return html;
  }

  /* ── Step 1: Type ────────────────────────────────────────── */
  function buildTypeStep() {
    var types = (state.allData && state.allData.types) || [];
    var html = '<div class="step-heading">Step 2 — Choose Your Type</div>';
    html += buildDetailPanel('type', state.typeId);
    html += '<div class="select-grid">';
    types.forEach(function(ty) {
      var sel = state.typeId == ty.id ? ' selected' : '';
      var tags = (ty.skills || []).slice(0,2).map(function(s) {
        return '<span class="tag tag-skill">' + esc(s.name) + '</span>';
      }).join('');
      html += '<div class="select-card' + sel + '" data-id="' + ty.id + '">' +
        '<div class="select-card-name">' + esc(ty.name) + '</div>' +
        '<div class="select-card-tags">' + tags + '</div>' +
      '</div>';
    });
    html += '</div>';
    return html;
  }

  /* ── Step 2: Career ──────────────────────────────────────── */
  function buildCareerStep() {
    var careers = (state.allData && state.allData.careers) || [];
    var html = '<div class="step-heading">Step 3 — Choose Your Career</div>';
    html += buildDetailPanel('career', state.careerId);
    html += '<div class="select-grid">';
    careers.forEach(function(ca) {
      var sel = state.careerId == ca.id ? ' selected' : '';
      var tags = (ca.skills || []).slice(0,2).map(function(s) {
        return '<span class="tag tag-skill">' + esc(s.name) + '</span>';
      }).join('');
      html += '<div class="select-card' + sel + '" data-id="' + ca.id + '">' +
        '<div class="select-card-name">' + esc(ca.name) + '</div>' +
        '<div class="select-card-tags">' + tags + '</div>' +
      '</div>';
    });
    html += '</div>';
    return html;
  }

  /* ── Detail panel ────────────────────────────────────────── */
  function buildDetailPanel(type, selectedId) {
    var item = null;
    if (selectedId && state.allData) {
      var list = state.allData[type + 's'] || [];
      item = list.find(function(x) { return x.id == selectedId; });
    }
    if (!item) {
      return '<div class="detail-panel"><span class="detail-panel-empty">Select a ' + type + ' to see its details.</span></div>';
    }
    var html = '<div class="detail-panel">' +
      '<div class="detail-panel-name">' + esc(item.name) + '</div>' +
      '<div class="detail-panel-desc">' + esc(item.description || '') + '</div>' +
      '<div class="detail-grants">';

    if (item.skills && item.skills.length) {
      html += '<div class="detail-grant-group"><div class="detail-grant-label">Skills</div><ul class="detail-grant-list">';
      item.skills.forEach(function(s) {
        html += '<li>' + esc(s.name) + '</li>';
      });
      html += '</ul></div>';
    }

    if (item.gifts && item.gifts.length) {
      html += '<div class="detail-grant-group"><div class="detail-grant-label">Gifts</div><ul class="detail-grant-list">';
      item.gifts.forEach(function(g) {
        html += '<li class="gift-item">' + esc(g.name) + '</li>';
      });
      html += '</ul></div>';
    }

    if (item.soaks && item.soaks.length) {
      html += '<div class="detail-grant-group"><div class="detail-grant-label">Soaks</div><ul class="detail-grant-list">';
      item.soaks.forEach(function(s) {
        html += '<li class="soak-item">' + esc(s.name) + '</li>';
      });
      html += '</ul></div>';
    }

    if (item.gear) {
      html += '<div class="detail-grant-group"><div class="detail-grant-label">Starting Gear</div><ul class="detail-grant-list">';
      item.gear.split(/\n/).forEach(function(line) {
        line = line.trim();
        if (line) html += '<li class="gear-item">' + esc(line) + '</li>';
      });
      html += '</ul></div>';
    }

    html += '</div></div>';
    return html;
  }

  /* ── Bind selection card clicks ──────────────────────────── */
  function bindSelectionCards(entityType, stateKey) {
    var container = document.getElementById('wiz-step-container');
    if (!container) return;
    container.querySelectorAll('.select-card').forEach(function(card) {
      card.addEventListener('click', function() {
        state[stateKey] = Number(card.dataset.id);
        container.querySelectorAll('.select-card').forEach(function(c) { c.classList.remove('selected'); });
        card.classList.add('selected');
        var panelEl = container.querySelector('.detail-panel');
        if (panelEl) {
          panelEl.outerHTML = buildDetailPanel(entityType, state[stateKey]);
        }
      });
    });
  }

  /* ── Step 3: Dice ────────────────────────────────────────── */
  function buildDiceStep() {
    var traits = [
      { key: 'bodyDie',  label: 'Body'  },
      { key: 'speedDie', label: 'Speed' },
      { key: 'mindDie',  label: 'Mind'  },
      { key: 'willDie',  label: 'Will'  },
    ];
    var html = '<div class="step-heading">Step 4 — Assign Trait Dice</div>';
    html += '<p style="color:var(--uj-text-muted); font-size:0.9rem; margin:0 0 1rem;">Assign your dice pool: <strong style="color:var(--uj-amber);">d8, d8, d6, d4</strong> (two d8s for best traits, one d4 for worst).</p>';
    html += '<div class="dice-grid">';
    traits.forEach(function(t) {
      html += '<div class="dice-trait">' +
        '<div class="dice-trait-name">' + t.label + '</div>' +
        '<select class="dice-select" data-trait="' + t.key + '">' +
        ['d4','d6','d8'].map(function(d) {
          return '<option value="' + d + '"' + (state[t.key] === d ? ' selected' : '') + '>' + d + '</option>';
        }).join('') +
        '</select>' +
      '</div>';
    });
    html += '</div>';
    html += '<p class="dice-hint">Pool used: <span id="dice-pool-display"></span></p>';
    html += '<p class="dice-error" id="dice-pool-error">Pool must be exactly d8 + d8 + d6 + d4.</p>';
    return html;
  }

  function bindDiceStep() {
    updateDiceDisplay();
    document.querySelectorAll('.dice-select').forEach(function(sel) {
      sel.addEventListener('change', function() {
        state[sel.dataset.trait] = sel.value;
        updateDiceDisplay();
      });
    });
  }

  function updateDiceDisplay() {
    var pool = [state.bodyDie, state.speedDie, state.mindDie, state.willDie];
    var disp = document.getElementById('dice-pool-display');
    var err  = document.getElementById('dice-pool-error');
    if (disp) disp.textContent = pool.join(', ');
    var valid = validateDice();
    if (err) err.style.display = valid ? 'none' : 'block';
  }

  /* ── Step 4: Personality ─────────────────────────────────── */
  function buildPersonalityStep() {
    var opts = state.personalities.map(function(p) {
      return '<option value="' + esc(p) + '"' + (state.personalityWord === p ? ' selected' : '') + '>' + esc(p) + '</option>';
    }).join('');

    return '<div class="step-heading">Step 5 — Personality &amp; Name</div>' +
      '<div class="personality-row">' +
        '<div class="personality-field">' +
          '<label class="field-label">Character Name</label>' +
          '<input type="text" class="field-input" id="char-name-input" value="' + esc(state.charName) + '" placeholder="What are they called?">' +
        '</div>' +
        '<div class="personality-field">' +
          '<label class="field-label">Personality Trait</label>' +
          '<select class="field-select" id="personality-select">' +
          '<option value="">— Choose a trait —</option>' +
          opts +
          '</select>' +
        '</div>' +
      '</div>' +
      '<div style="margin-bottom:1rem;">' +
        '<label class="field-label">Notes (optional)</label>' +
        '<textarea class="field-textarea" id="char-notes-input" placeholder="Backstory, goals, appearance…">' + esc(state.notes) + '</textarea>' +
      '</div>' +
      '<p style="font-size:0.88rem; color:var(--uj-text-dim);">' +
        'The <strong style="color:var(--uj-text);">Personality</strong> gift is free for all characters. ' +
        'It grants a bonus d12 when you act according to your personality trait.' +
      '</p>';
  }

  function bindPersonalityStep() {
    var nameInput = document.getElementById('char-name-input');
    var selEl     = document.getElementById('personality-select');
    var notesEl   = document.getElementById('char-notes-input');
    if (nameInput) nameInput.addEventListener('input', function() { state.charName = nameInput.value; });
    if (selEl)     selEl.addEventListener('change', function() { state.personalityWord = selEl.value; });
    if (notesEl)   notesEl.addEventListener('input', function() { state.notes = notesEl.value; });
  }

  /* ── Step 5: Summary ─────────────────────────────────────── */
  function buildSummaryStep() {
    var d   = state.allData || {};
    var sp  = (d.species  || []).find(function(x) { return x.id == state.speciesId;  }) || null;
    var ty  = (d.types    || []).find(function(x) { return x.id == state.typeId;     }) || null;
    var ca  = (d.careers  || []).find(function(x) { return x.id == state.careerId;   }) || null;

    // Merge and deduplicate skills
    var skillMap = {};
    function addSkills(arr, source) {
      (arr || []).forEach(function(s) {
        if (!skillMap[s.id]) skillMap[s.id] = { name: s.name, sources: [] };
        skillMap[s.id].sources.push(source);
      });
    }
    if (sp) addSkills(sp.skills, sp.name);
    if (ty) addSkills(ty.skills, ty.name);
    if (ca) addSkills(ca.skills, ca.name);

    // Merge and deduplicate gifts (include Personality)
    var giftMap = {};
    function addGifts(arr, source) {
      (arr || []).forEach(function(g) {
        if (!giftMap[g.id]) giftMap[g.id] = { name: g.name, sources: [] };
        giftMap[g.id].sources.push(source);
      });
    }
    if (sp) addGifts(sp.gifts, sp.name);
    if (ty) addGifts(ty.gifts, ty.name);
    if (ca) addGifts(ca.gifts, ca.name);
    // Always add Personality gift
    giftMap['personality'] = { name: 'Personality [' + (state.personalityWord || 'of choice') + ']', sources: ['All characters'] };

    // Merge soaks
    var soakMap = {};
    if (ty && ty.soaks) {
      ty.soaks.forEach(function(s) {
        if (!soakMap[s.id]) soakMap[s.id] = { name: s.name, detail: s.damage_negated || '' };
      });
    }

    // Gear
    var gearLines = [];
    if (ty && ty.gear) ty.gear.split(/\n/).filter(Boolean).forEach(function(l) { gearLines.push({ from: ty.name, text: l.trim() }); });
    if (ca && ca.gear) ca.gear.split(/\n/).filter(Boolean).forEach(function(l) { gearLines.push({ from: ca.name, text: l.trim() }); });

    var traits = [
      { label: 'Body',  die: state.bodyDie  },
      { label: 'Speed', die: state.speedDie },
      { label: 'Mind',  die: state.mindDie  },
      { label: 'Will',  die: state.willDie  },
    ];

    var html = '';

    html += '<div class="summary-char-name">' + esc(state.charName || '(Unnamed)') + '</div>';
    var subtitle = [sp ? sp.name : '', ty ? ty.name : '', ca ? ca.name : ''].filter(Boolean).join(' · ');
    if (subtitle) html += '<div class="summary-subtitle">' + esc(subtitle) + '</div>';

    // Personality
    if (state.personalityWord) {
      html += '<div class="summary-personality">' +
        '<span class="summary-personality-label">Personality</span>' +
        '<span class="summary-personality-word">' + esc(state.personalityWord) + '</span>' +
        '</div>';
    }

    // Trait dice
    html += '<div class="summary-traits">';
    traits.forEach(function(t) {
      html += '<div class="summary-trait">' +
        '<div class="summary-trait-name">' + t.label + '</div>' +
        '<div class="summary-trait-die">' + (t.die || '—') + '</div>' +
      '</div>';
    });
    html += '</div>';

    // Main grid: skills / gifts / soaks / gear
    html += '<div class="summary-grid">';

    // Skills
    html += '<div class="summary-section"><div class="summary-section-title">Skills</div><ul class="summary-list">';
    var skills = Object.values(skillMap);
    if (skills.length) {
      skills.forEach(function(s) {
        html += '<li>' + esc(s.name) + '<small>' + esc(s.sources.join(', ')) + '</small></li>';
      });
    } else {
      html += '<li style="color:var(--uj-text-dim);border-left-color:var(--uj-text-dim);">None selected</li>';
    }
    html += '</ul></div>';

    // Gifts
    html += '<div class="summary-section"><div class="summary-section-title">Gifts</div><ul class="summary-list">';
    var gifts = Object.values(giftMap);
    gifts.forEach(function(g) {
      html += '<li class="gift-item">' + esc(g.name) + '<small>' + esc(g.sources.join(', ')) + '</small></li>';
    });
    html += '</ul></div>';

    // Soaks
    html += '<div class="summary-section"><div class="summary-section-title">Soaks</div><ul class="summary-list">';
    var soaks = Object.values(soakMap);
    if (soaks.length) {
      soaks.forEach(function(s) {
        html += '<li class="soak-item">' + esc(s.name) + (s.detail ? '<small>' + esc(s.detail) + '</small>' : '') + '</li>';
      });
    } else {
      html += '<li style="color:var(--uj-text-dim);border-left-color:var(--uj-text-dim);">None granted</li>';
    }
    html += '</ul></div>';

    // Gear
    html += '<div class="summary-section"><div class="summary-section-title">Starting Gear</div><ul class="summary-list">';
    if (gearLines.length) {
      gearLines.forEach(function(g) {
        html += '<li style="border-left-color:#fbbf24;">' + esc(g.text) + '<small>' + esc(g.from) + '</small></li>';
      });
    } else {
      html += '<li style="color:var(--uj-text-dim);border-left-color:var(--uj-text-dim);">No gear granted</li>';
    }
    html += '</ul></div>';

    html += '</div>';

    if (state.notes) {
      html += '<div class="summary-section" style="margin-top:1.25rem;">' +
        '<div class="summary-section-title">Notes</div>' +
        '<p style="font-size:0.92rem; color:var(--uj-text-muted); white-space:pre-wrap; margin:0;">' + esc(state.notes) + '</p>' +
      '</div>';
    }

    return '<div class="step-heading">Step 6 — Summary</div>' + html;
  }

  /* ─────────────────────────────────────────────────────────────
     SAVE / LOAD
  ───────────────────────────────────────────────────────────── */
  function saveCharacter() {
    var statusEl = document.getElementById('wiz-save-status');
    if (statusEl) { statusEl.textContent = 'Saving…'; statusEl.className = 'save-status saving'; }
    var payload = {
      id:               state.currentChar || '',
      name:             state.charName,
      species_id:       state.speciesId   || '',
      type_id:          state.typeId      || '',
      career_id:        state.careerId    || '',
      body_die:         state.bodyDie,
      speed_die:        state.speedDie,
      mind_die:         state.mindDie,
      will_die:         state.willDie,
      personality_word: state.personalityWord,
      notes:            state.notes,
    };
    ajaxPost('uj_save_character', { character: payload }).then(function(res) {
      if (res.success) {
        state.currentChar = String(res.data.id);
        if (statusEl) { statusEl.textContent = 'Saved'; statusEl.className = 'save-status saved'; }
        setTimeout(function() { if (statusEl) statusEl.textContent = ''; }, 3000);
        var titleEl = wizardScreen.querySelector('.wizard-title');
        if (titleEl) titleEl.textContent = state.charName || 'New Character';
      } else {
        if (statusEl) { statusEl.textContent = 'Save failed'; statusEl.className = 'save-status error'; }
      }
    }).catch(function() {
      if (statusEl) { statusEl.textContent = 'Network error'; statusEl.className = 'save-status error'; }
    });
  }

  /* ─────────────────────────────────────────────────────────────
     UTILITY
  ───────────────────────────────────────────────────────────── */
  function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;');
  }

})();
</script>

<?php require __DIR__ . '/../layout-foot.php'; ?>
