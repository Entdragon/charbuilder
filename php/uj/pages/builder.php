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
  /* ── Builder overrides ───────────────────────────────────────── */
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
    max-width:     420px;
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

  .uj-auth-tab.active { color: var(--uj-amber); border-bottom-color: var(--uj-amber); }
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
    box-sizing:    border-box;
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

  /* ── Builder app screen ───────────────────────────────────── */
  #uj-builder-screen {
    display:        flex;
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

  .builder-topbar-user { font-size: 0.88rem; color: var(--uj-text-muted); }
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

  .uj-btn-amber { background: linear-gradient(135deg, var(--uj-amber-dark), var(--uj-amber)); color: var(--uj-bg); }
  .uj-btn-amber:hover { opacity: 0.85; }

  .uj-btn-ghost { background: transparent; color: var(--uj-text-muted); border: 1px solid var(--uj-border-cool); }
  .uj-btn-ghost:hover { color: var(--uj-amber-light); border-color: var(--uj-amber-border); }
  .uj-btn-ghost:disabled { opacity: 0.4; pointer-events: none; }

  .uj-btn-teal { background: linear-gradient(135deg, var(--uj-teal-dark), var(--uj-teal)); color: var(--uj-bg); }
  .uj-btn-teal:hover { opacity: 0.85; }

  .uj-btn-danger { background: transparent; color: var(--uj-error); border: 1px solid rgba(248,113,113,0.3); font-size: 0.7rem; padding: 0.3rem 0.7rem; }
  .uj-btn-danger:hover { background: rgba(248,113,113,0.1); }

  /* ── Builder main area ────────────────────────────────────── */
  #uj-builder-main { flex: 1; overflow-y: auto; padding: 1.5rem 2rem; }

  /* ── Character list ───────────────────────────────────────── */
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

  .char-card:hover { border-color: var(--uj-amber-border); box-shadow: 0 4px 16px rgba(0,0,0,0.3); }

  .char-card-name { font-family: 'Cinzel', Georgia, serif; font-size: 1.05rem; font-weight: 700; color: var(--uj-amber-light); margin: 0 0 0.4rem; }
  .char-card-detail { font-size: 0.85rem; color: var(--uj-text-muted); line-height: 1.5; }

  .char-card-footer {
    display:         flex;
    justify-content: space-between;
    align-items:     center;
    margin-top:      0.75rem;
    padding-top:     0.6rem;
    border-top:      1px solid var(--uj-border-light);
  }

  .char-card-date { font-size: 0.78rem; color: var(--uj-text-dim); }

  .char-empty { text-align: center; color: var(--uj-text-dim); padding: 3rem 2rem; font-size: 1rem; font-style: italic; }

  /* ── Wizard ───────────────────────────────────────────────── */
  .wizard-header {
    display:         flex;
    align-items:     center;
    justify-content: space-between;
    margin-bottom:   1.5rem;
    gap:             1rem;
    flex-wrap:       wrap;
  }

  .wizard-title { font-family: 'Cinzel', Georgia, serif; font-size: 1.1rem; color: var(--uj-amber-light); margin: 0; letter-spacing: 0.04em; }

  .wizard-progress { display: flex; gap: 0.35rem; align-items: center; }

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
  .wp-step.active { background: var(--uj-amber); border-color: var(--uj-amber); color: var(--uj-bg); }

  .wp-line { flex: 1; height: 2px; background: var(--uj-border-cool); min-width: 16px; max-width: 32px; transition: background 0.2s; }
  .wp-line.done { background: var(--uj-teal-dark); }

  .step-heading {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.85rem;
    font-weight:    700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color:          var(--uj-teal);
    margin:         0 0 1rem;
  }

  /* ── Selection grid ───────────────────────────────────────── */
  .select-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.6rem; margin-bottom: 1.25rem; }

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

  .select-card-name { font-family: 'Cinzel', Georgia, serif; font-size: 0.9rem; font-weight: 700; color: var(--uj-amber-light); margin: 0 0 0.25rem; }

  .select-card-tags { display: flex; gap: 0.3rem; flex-wrap: wrap; margin: 0; }

  /* ── Detail panel ─────────────────────────────────────────── */
  .detail-panel {
    background:    var(--uj-surface);
    border:        1px solid var(--uj-border);
    border-radius: var(--uj-radius-lg);
    padding:       1.25rem;
    margin-bottom: 1.25rem;
    min-height:    80px;
  }

  .detail-panel-empty { color: var(--uj-text-dim); font-style: italic; font-size: 0.9rem; }
  .detail-panel-name  { font-family: 'Cinzel', Georgia, serif; font-size: 1.1rem; font-weight: 700; color: var(--uj-amber-light); margin: 0 0 0.5rem; }

  .detail-panel-desc { font-size: 0.92rem; color: var(--uj-text-muted); line-height: 1.5; margin: 0 0 1rem; }

  .detail-grants { display: flex; gap: 1.5rem; flex-wrap: wrap; }

  .detail-grant-label {
    font-family:    'Cinzel', Georgia, serif;
    font-size:      0.65rem;
    font-weight:    700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color:          var(--uj-text-dim);
    margin:         0 0 0.3rem;
  }

  .detail-grant-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.2rem; }

  .detail-grant-list li { font-size: 0.88rem; color: var(--uj-text); padding: 0.2rem 0.5rem; border-radius: 3px; background: var(--uj-surface-2); border-left: 2px solid var(--uj-teal); }
  .detail-grant-list li.gift-item { border-left-color: var(--uj-amber); }
  .detail-grant-list li.soak-item { border-left-color: var(--uj-text-dim); }
  .detail-grant-list li.gear-item { border-left-color: #fbbf24; font-size: 0.82rem; color: var(--uj-text-muted); }

  /* ── Dice step ────────────────────────────────────────────── */
  .dice-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.25rem; max-width: 600px; }

  .dice-trait { background: var(--uj-surface); border: 1px solid var(--uj-border-cool); border-radius: var(--uj-radius-lg); padding: 1rem; text-align: center; }

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
  .dice-hint  { font-size: 0.8rem; color: var(--uj-text-dim); margin: 0.75rem 0 0; font-style: italic; }
  .dice-error { color: var(--uj-error); font-size: 0.88rem; margin: 0.5rem 0 0; display: none; }

  /* ── Personality step ─────────────────────────────────────── */
  .personality-row { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
  .personality-field { flex: 1; min-width: 200px; }

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
    box-sizing:    border-box;
  }

  .field-input:focus, .field-select:focus, .field-textarea:focus { border-color: var(--uj-amber); }
  .field-select option { background: var(--uj-surface-2); }
  .field-textarea { resize: vertical; min-height: 80px; }

  /* ── Summary ──────────────────────────────────────────────── */
  .summary-char-name { font-family: 'Cinzel', Georgia, serif; font-size: 1.6rem; font-weight: 700; color: var(--uj-amber-light); margin: 0 0 0.25rem; letter-spacing: 0.04em; }
  .summary-subtitle  { font-size: 0.9rem; color: var(--uj-text-muted); margin: 0 0 1.5rem; font-style: italic; }

  .summary-traits { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 1.25rem; }

  .summary-trait { background: var(--uj-surface); border: 1px solid var(--uj-border-cool); border-radius: var(--uj-radius-lg); padding: 0.75rem; text-align: center; }

  .summary-trait-name { font-family: 'Cinzel', Georgia, serif; font-size: 0.7rem; color: var(--uj-text-dim); letter-spacing: 0.1em; text-transform: uppercase; margin: 0 0 0.3rem; }
  .summary-trait-die  { font-family: 'Cinzel', Georgia, serif; font-size: 1.4rem; font-weight: 700; color: var(--uj-amber); }

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

  .summary-personality-label { font-family: 'Cinzel', Georgia, serif; font-size: 0.7rem; color: var(--uj-text-dim); letter-spacing: 0.1em; text-transform: uppercase; }
  .summary-personality-word  { font-family: 'Cinzel', Georgia, serif; font-size: 1.2rem; font-weight: 700; color: var(--uj-amber-light); }

  .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem; }
  @media (max-width: 600px) { .summary-grid { grid-template-columns: 1fr; } }

  .summary-section { background: var(--uj-surface); border: 1px solid var(--uj-border-cool); border-radius: var(--uj-radius-lg); padding: 1rem 1.25rem; }

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

  .summary-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.25rem; }

  .summary-list li { font-size: 0.9rem; color: var(--uj-text); padding: 0.25rem 0.5rem; border-left: 2px solid var(--uj-teal); background: var(--uj-surface-2); border-radius: 0 3px 3px 0; }
  .summary-list li.gift-item { border-left-color: var(--uj-amber); }
  .summary-list li.soak-item { border-left-color: var(--uj-text-dim); }
  .summary-list li small     { color: var(--uj-text-dim); font-size: 0.78rem; display: block; }

  /* ── Wizard nav buttons ───────────────────────────────────── */
  .wizard-nav {
    display:         flex;
    justify-content: space-between;
    align-items:     center;
    margin-top:      1.5rem;
    padding-top:     1rem;
    border-top:      1px solid var(--uj-border-light);
    gap:             1rem;
  }

  .wizard-nav-right { display: flex; gap: 0.5rem; align-items: center; }

  .save-status { font-size: 0.82rem; color: var(--uj-text-dim); font-style: italic; }
  .save-status.saving { color: var(--uj-amber); }
  .save-status.saved  { color: var(--uj-success); }
  .save-status.error  { color: var(--uj-error); }

  /* ── Source die picker (Steps 1-3) ───────────────────────── */
  .source-die-row {
    display: flex; align-items: center; gap: 0.75rem;
    background: rgba(75,191,216,0.06); border: 1px solid rgba(75,191,216,0.15);
    border-radius: 8px; padding: 0.6rem 1rem; margin-bottom: 1rem;
  }
  .source-die-label {
    font-family: 'Cinzel', serif; font-size: 0.7rem; font-weight: 700;
    letter-spacing: 0.08em; text-transform: uppercase; color: var(--uj-teal);
    white-space: nowrap;
  }
  .source-die-options { display: flex; gap: 0.4rem; flex-wrap: wrap; }
  .die-pill {
    cursor: pointer; padding: 0.3rem 0.7rem;
    border: 1px solid rgba(255,255,255,0.15); border-radius: 5px;
    font-family: 'Cinzel', serif; font-size: 0.78rem; font-weight: 700;
    color: var(--uj-text-muted); background: rgba(255,255,255,0.04);
    user-select: none; transition: all 0.15s;
  }
  .die-pill:hover { border-color: var(--uj-teal); color: var(--uj-teal); }
  .die-pill-active {
    background: var(--uj-teal); color: #0a1a1e;
    border-color: var(--uj-teal); font-weight: 700;
  }

  /* ── Summary source dice ──────────────────────────────────── */
  .summary-source-dice {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;
    margin-bottom: 1.25rem;
  }
  .summary-source-die-item {
    display: flex; flex-direction: column; align-items: center; gap: 0.15rem;
    background: rgba(75,191,216,0.06); border: 1px solid rgba(75,191,216,0.15);
    border-radius: 8px; padding: 0.6rem 0.5rem;
  }

  /* ── Skills table ─────────────────────────────────────────── */
  .summary-skills-section { margin-bottom: 1.25rem; }
  .skills-table {
    width: 100%; border-collapse: collapse; font-size: 0.88rem;
  }
  .skills-table thead th {
    font-family: 'Cinzel', serif; font-size: 0.68rem; font-weight: 700;
    letter-spacing: 0.07em; text-transform: uppercase;
    color: var(--uj-text-dim); padding: 0.35rem 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);
    text-align: center;
  }
  .skills-table thead th.skill-name-col { text-align: left; }
  .skills-table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.04); }
  .skills-table tbody tr:hover { background: rgba(255,255,255,0.03); }
  .skill-name-col { padding: 0.3rem 0.5rem; color: var(--uj-text); font-weight: 500; }
  .skill-die-col  { padding: 0.3rem 0.4rem; text-align: center; }
  .skill-total-col { padding: 0.3rem 0.5rem; font-size: 0.82rem; }
  .skill-row-active .skill-name-col { color: var(--uj-amber-light); font-weight: 600; }
  .skill-die-badge {
    display: inline-block; padding: 0.15rem 0.45rem;
    background: rgba(43,184,212,0.15); border: 1px solid rgba(43,184,212,0.3);
    border-radius: 4px; color: var(--uj-teal); font-family: 'Cinzel', serif;
    font-size: 0.75rem; font-weight: 700;
  }
  .skill-die-empty { color: rgba(255,255,255,0.15); font-size: 0.8rem; }

  /* ── Battle array ─────────────────────────────────────────── */
  .summary-battle-array { margin-bottom: 1.25rem; }
  .battle-array-grid {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 0.75rem;
    margin-top: 0.5rem;
  }
  .battle-stat {
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px; padding: 0.75rem 1rem; text-align: center;
  }
  .battle-stat-name {
    font-family: 'Cinzel', serif; font-size: 0.85rem; font-weight: 700;
    color: var(--uj-amber-light); margin-bottom: 0.15rem;
  }
  .battle-stat-sub { font-size: 0.72rem; color: var(--uj-text-dim); margin-bottom: 0.4rem; }
  .battle-stat-dice {
    font-family: 'Cinzel', serif; font-size: 1rem; font-weight: 700;
    color: var(--uj-teal);
  }
  @media (max-width: 600px) {
    .summary-source-dice { grid-template-columns: 1fr; }
    .battle-array-grid   { grid-template-columns: 1fr; }
    .skills-table { font-size: 0.8rem; }
  }

  /* ── Print ────────────────────────────────────────────────── */
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
        <label>Email</label>
        <input type="email" name="email" autocomplete="email" required>
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
<div id="uj-builder-screen">
  <div class="builder-topbar">
    <div class="builder-topbar-user">Signed in as <strong><?= $username ?></strong></div>
    <div class="builder-topbar-actions">
      <button class="uj-btn uj-btn-ghost" id="uj-logout-btn">Sign Out</button>
    </div>
  </div>
  <div id="uj-builder-main">
    <div id="uj-builder-loading" style="text-align:center; padding:3rem; color:var(--uj-text-dim); font-style:italic;">Loading game data…</div>
    <div id="uj-char-list-screen" style="display:none;"></div>
    <div id="uj-wizard-screen"    style="display:none;"></div>
  </div>
</div>
<?php endif; ?>

<script>
  window.UJBuilder = {
    loggedIn: <?= $loggedIn ? 'true' : 'false' ?>,
    username: <?= json_encode($username) ?>,
  };
</script>
<script src="/assets/js/dist/uj-builder.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/dist/uj-builder.js') ?>"></script>

<?php require __DIR__ . '/../layout-foot.php'; ?>
