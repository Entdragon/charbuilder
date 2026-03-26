<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

cg_session_start();
cg_try_wp_sso();

if (!cg_is_logged_in() || !cg_is_admin()) {
    header('Location: /');
    exit;
}
$username = htmlspecialchars($_SESSION['cg_username'] ?? 'Admin', ENT_QUOTES);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gift Editor — Library of Calabria</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:       #1a1510;
  --surface:  #2a221a;
  --panel:    #231c14;
  --border:   #4a3e2c;
  --gold:     #c9a84c;
  --gold-dim: #8a6e2f;
  --text:     #e8dcc8;
  --muted:    #8a7a60;
  --danger:   #c0392b;
  --success:  #27ae60;
  --radius:   6px;
  --font-ui:  'Segoe UI', system-ui, sans-serif;
  --font-body:'Georgia', serif;
}

html, body { height: 100%; font-family: var(--font-ui); background: var(--bg); color: var(--text); font-size: 14px; }

/* ── Layout ── */
.cga-shell   { display: flex; flex-direction: column; height: 100vh; }
.cga-topbar  { display: flex; align-items: center; gap: 1rem; padding: 0.6rem 1rem;
               background: var(--panel); border-bottom: 1px solid var(--border); flex-shrink: 0; }
.cga-body    { display: flex; flex: 1; overflow: hidden; }
.cga-sidebar { width: 240px; flex-shrink: 0; display: flex; flex-direction: column;
               border-right: 1px solid var(--border); background: var(--panel); }
.cga-main    { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* ── Top bar ── */
.cga-logo    { font-size: 1rem; font-weight: 700; color: var(--gold); letter-spacing: .04em; }
.cga-user    { margin-left: auto; font-size: 0.8rem; color: var(--muted); }
.cga-logout  { font-size: 0.8rem; color: var(--muted); text-decoration: none; padding: 0.25rem 0.6rem;
               border: 1px solid var(--border); border-radius: var(--radius); }
.cga-logout:hover { color: var(--text); border-color: var(--gold); }

/* ── Tabs ── */
.cga-tabs    { display: flex; border-bottom: 1px solid var(--border); }
.cga-tab     { padding: 0.55rem 1.2rem; font-size: 0.82rem; font-weight: 600; letter-spacing: .05em;
               text-transform: uppercase; cursor: pointer; color: var(--muted);
               border-bottom: 2px solid transparent; transition: color .15s; }
.cga-tab.active, .cga-tab:hover { color: var(--gold); border-bottom-color: var(--gold); }

/* ── Sidebar ── */
.cga-search  { padding: 0.5rem; border-bottom: 1px solid var(--border); }
.cga-search input { width: 100%; padding: 0.4rem 0.6rem; background: var(--surface);
                    border: 1px solid var(--border); border-radius: var(--radius); color: var(--text);
                    font-size: 0.85rem; outline: none; }
.cga-search input:focus { border-color: var(--gold); }
.cga-list    { flex: 1; overflow-y: auto; }
.cga-list-item { padding: 0.45rem 0.75rem; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.04);
                 font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
                 transition: background .1s; }
.cga-list-item:hover  { background: rgba(201,168,76,0.08); }
.cga-list-item.active { background: rgba(201,168,76,0.16); color: var(--gold); }
.cga-list-item.unpub  { color: var(--muted); }

/* ── Editor toolbar ── */
.cga-editor-bar { display: flex; align-items: center; gap: 0.6rem; padding: 0.55rem 1rem;
                  border-bottom: 1px solid var(--border); background: var(--surface); flex-shrink: 0; }
.cga-nav-btn   { background: var(--surface); border: 1px solid var(--border); color: var(--text);
                 border-radius: var(--radius); padding: 0.3rem 0.65rem; cursor: pointer;
                 font-size: 1rem; transition: border-color .15s; }
.cga-nav-btn:hover:not(:disabled) { border-color: var(--gold); color: var(--gold); }
.cga-nav-btn:disabled { opacity: 0.3; cursor: default; }
.cga-record-id { font-size: 0.78rem; color: var(--muted); flex: 1; text-align: center; }
.cga-save-btn  { margin-left: auto; padding: 0.35rem 1rem; background: var(--gold); color: #1a1510;
                 border: none; border-radius: var(--radius); font-weight: 700; cursor: pointer;
                 font-size: 0.88rem; transition: opacity .15s; }
.cga-save-btn:hover  { opacity: 0.85; }
.cga-save-btn:disabled { opacity: 0.4; cursor: default; }
.cga-status    { font-size: 0.78rem; padding: 0.2rem 0.5rem; border-radius: var(--radius); white-space: nowrap; }
.cga-status.ok   { color: var(--success); }
.cga-status.err  { color: var(--danger); }

/* ── Editor form ── */
.cga-editor  { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }
.cga-empty   { display: flex; align-items: center; justify-content: center; height: 100%;
               color: var(--muted); font-size: 0.9rem; }
.cga-field   { margin-bottom: 1rem; }
.cga-field label { display: block; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
                   letter-spacing: .07em; color: var(--muted); margin-bottom: 0.3rem; }
.cga-field input[type="text"],
.cga-field textarea,
.cga-field select { width: 100%; background: var(--surface); border: 1px solid var(--border);
                    border-radius: var(--radius); color: var(--text); padding: 0.45rem 0.6rem;
                    font-size: 0.88rem; font-family: var(--font-body); outline: none;
                    transition: border-color .15s; resize: vertical; }
.cga-field input:focus, .cga-field textarea:focus, .cga-field select:focus { border-color: var(--gold); }
.cga-field textarea.tall { min-height: 180px; }
.cga-field textarea.xtall { min-height: 280px; }
.cga-row     { display: flex; gap: 1rem; }
.cga-row .cga-field { flex: 1; }
.cga-check   { display: flex; align-items: center; gap: 0.4rem; font-size: 0.88rem; cursor: pointer; }
.cga-check input { accent-color: var(--gold); width: 1rem; height: 1rem; cursor: pointer; }
.cga-divider { border: none; border-top: 1px solid var(--border); margin: 1rem 0; }

/* scrollbars */
::-webkit-scrollbar { width: 6px; } ::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
</style>
</head>
<body>
<div class="cga-shell">

  <div class="cga-topbar">
    <span class="cga-logo">Library of Calabria — Content Editor</span>
    <span class="cga-user">Logged in as <?= $username ?></span>
    <a class="cga-logout" href="/ajax.php?action=cg_logout_user" onclick="return doLogout(event)">Log out</a>
  </div>

  <div class="cga-tabs" style="padding: 0 1rem; background: var(--panel); border-bottom: 1px solid var(--border);">
    <div class="cga-tab active" data-pane="gifts">Gifts</div>
    <div class="cga-tab"        data-pane="weapons">Weapons</div>
  </div>

  <div class="cga-body">
    <!-- Sidebar -->
    <div class="cga-sidebar">
      <div class="cga-search">
        <input id="cga-search" type="search" placeholder="Search…" autocomplete="off">
      </div>
      <div class="cga-list" id="cga-list"></div>
    </div>

    <!-- Main editor -->
    <div class="cga-main">
      <div class="cga-editor-bar">
        <button class="cga-nav-btn" id="cga-prev" title="Previous" disabled>&#8249;</button>
        <span class="cga-record-id" id="cga-rec-label">Select a record to begin editing</span>
        <button class="cga-nav-btn" id="cga-next" title="Next" disabled>&#8250;</button>
        <span class="cga-status" id="cga-status"></span>
        <button class="cga-save-btn" id="cga-save" disabled>Save</button>
      </div>
      <div class="cga-editor" id="cga-editor">
        <div class="cga-empty">← Select a record from the sidebar</div>
      </div>
    </div>
  </div>

</div>
<script>
(function () {
  'use strict';

  const AJAX = '/ajax.php';
  let pane      = 'gifts';   // 'gifts' | 'weapons'
  let allItems  = [];        // [{id, name, ...}]
  let filtItems = [];        // filtered
  let curId     = null;
  let curPrevId = null;
  let curNextId = null;
  let dirty     = false;

  // ── AJAX helper ───────────────────────────────────────────────────────────
  async function post(action, params = {}) {
    const body = new URLSearchParams({ action, ...params });
    const res  = await fetch(AJAX, { method: 'POST', credentials: 'include', body });
    return res.json();
  }

  // ── Status ────────────────────────────────────────────────────────────────
  function status(msg, type = 'ok') {
    const el = document.getElementById('cga-status');
    el.textContent = msg;
    el.className   = 'cga-status ' + type;
    if (msg) setTimeout(() => { if (el.textContent === msg) el.textContent = ''; }, 3000);
  }

  // ── Tabs ──────────────────────────────────────────────────────────────────
  document.querySelectorAll('.cga-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      if (tab.dataset.pane === pane) return;
      document.querySelectorAll('.cga-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      pane  = tab.dataset.pane;
      curId = null;
      document.getElementById('cga-editor').innerHTML = '<div class="cga-empty">← Select a record from the sidebar</div>';
      document.getElementById('cga-rec-label').textContent = 'Select a record to begin editing';
      document.getElementById('cga-prev').disabled = true;
      document.getElementById('cga-next').disabled = true;
      document.getElementById('cga-save').disabled = true;
      loadList();
    });
  });

  // ── Sidebar list ──────────────────────────────────────────────────────────
  async function loadList(search = '') {
    const action = pane === 'gifts' ? 'cg_admin_list_gifts' : 'cg_admin_list_weapons';
    const res    = await post(action, search ? { search } : {});
    if (!res.success) return;
    allItems  = res.data;
    filtItems = allItems;
    renderList();
  }

  function renderList() {
    const ul   = document.getElementById('cga-list');
    const nameKey = pane === 'gifts' ? 'ct_gifts_name' : 'ct_weapons_name';
    ul.innerHTML  = filtItems.map(item => {
      const active = item.ct_id == curId ? ' active' : '';
      const unpub  = !parseInt(item.published) ? ' unpub' : '';
      const label  = item[nameKey] || `#${item.ct_id}`;
      return `<div class="cga-list-item${active}${unpub}" data-id="${item.ct_id}">${esc(label)}</div>`;
    }).join('');

    ul.querySelectorAll('.cga-list-item').forEach(el => {
      el.addEventListener('click', () => loadRecord(parseInt(el.dataset.id)));
    });
  }

  // Search
  let searchTimer;
  document.getElementById('cga-search').addEventListener('input', e => {
    clearTimeout(searchTimer);
    const q = e.target.value.trim().toLowerCase();
    searchTimer = setTimeout(() => {
      if (q) {
        const nameKey = pane === 'gifts' ? 'ct_gifts_name' : 'ct_weapons_name';
        filtItems = allItems.filter(i => (i[nameKey] || '').toLowerCase().includes(q));
        renderList();
      } else {
        filtItems = allItems;
        renderList();
      }
    }, 180);
  });

  // ── Load a record ─────────────────────────────────────────────────────────
  async function loadRecord(id) {
    const action = pane === 'gifts' ? 'cg_admin_get_gift' : 'cg_admin_get_weapon';
    const res    = await post(action, { id });
    if (!res.success) { status('Load failed: ' + res.data, 'err'); return; }

    curId     = id;
    curPrevId = res.data.prev_id;
    curNextId = res.data.next_id;

    document.getElementById('cga-prev').disabled = !curPrevId;
    document.getElementById('cga-next').disabled = !curNextId;
    document.getElementById('cga-save').disabled = false;

    const record   = pane === 'gifts' ? res.data.gift : res.data.weapon;
    const nameKey  = pane === 'gifts' ? 'ct_gifts_name' : 'ct_weapons_name';
    document.getElementById('cga-rec-label').textContent =
      `ID ${record.ct_id} — ${record[nameKey] || ''}`;

    renderEditor(record);
    renderList(); // update active state
    dirty = false;
    status('');
  }

  // ── Nav buttons ───────────────────────────────────────────────────────────
  document.getElementById('cga-prev').addEventListener('click', () => { if (curPrevId) loadRecord(curPrevId); });
  document.getElementById('cga-next').addEventListener('click', () => { if (curNextId) loadRecord(curNextId); });

  // ── Render editor ─────────────────────────────────────────────────────────
  function field(col, label, val, opts = {}) {
    const v = esc(val ?? '');
    if (opts.textarea) {
      const cls = opts.xtall ? 'xtall' : opts.tall ? 'tall' : '';
      return `<div class="cga-field"><label>${label}</label>
        <textarea name="${col}" class="${cls}" rows="${opts.rows || 5}">${v}</textarea></div>`;
    }
    if (opts.checkbox) {
      const chk = parseInt(val) ? ' checked' : '';
      return `<div class="cga-field"><label class="cga-check">
        <input type="checkbox" name="${col}" value="1"${chk}> ${label}</label></div>`;
    }
    return `<div class="cga-field"><label>${label}</label>
      <input type="text" name="${col}" value="${v}"></div>`;
  }

  function renderEditor(r) {
    let html = '';
    if (pane === 'gifts') {
      html = `
        <div class="cga-row">
          ${field('ct_gifts_name', 'Name', r.ct_gifts_name)}
          ${field('ct_pg_no', 'Page #', r.ct_pg_no)}
        </div>
        <div class="cga-row">
          ${field('ct_slug', 'Slug', r.ct_slug)}
          ${field('published', 'Published', r.published, { checkbox: true })}
          ${field('ct_gifts_manifold', 'Allows Multiple', r.ct_gifts_manifold, { checkbox: true })}
        </div>
        <hr class="cga-divider">
        ${field('ct_gifts_effect', 'Effect (short summary)', r.ct_gifts_effect, { textarea: true, rows: 3 })}
        ${field('ct_gifts_effect_description', 'Full Description', r.ct_gifts_effect_description, { textarea: true, xtall: true })}
        <hr class="cga-divider">
        ${field('ct_gift_trigger', 'Trigger', r.ct_gift_trigger, { textarea: true, rows: 2 })}
      `;
    } else {
      html = `
        <div class="cga-row">
          ${field('ct_weapons_name', 'Name', r.ct_weapons_name)}
          ${field('ct_weapon_class', 'Class', r.ct_weapon_class)}
          ${field('ct_pg_no', 'Page #', r.ct_pg_no)}
        </div>
        <div class="cga-row">
          ${field('ct_slug', 'Slug', r.ct_slug)}
          ${field('ct_cost_d', 'Cost (D)', r.ct_cost_d)}
          ${field('published', 'Published', r.published, { checkbox: true })}
        </div>
        <hr class="cga-divider">
        <div class="cga-row">
          ${field('ct_attack_dice', 'Attack Dice', r.ct_attack_dice)}
          ${field('ct_damage_mod', 'Damage Mod', r.ct_damage_mod)}
          ${field('ct_range_band', 'Range Band', r.ct_range_band)}
          ${field('ct_equip', 'Equip Time', r.ct_equip)}
        </div>
        ${field('ct_effect', 'Effect', r.ct_effect, { textarea: true, rows: 3 })}
        ${field('ct_descriptors', 'Descriptors', r.ct_descriptors, { textarea: true, rows: 2 })}
        <hr class="cga-divider">
        ${field('ct_description', 'Full Description', r.ct_description, { textarea: true, xtall: true })}
      `;
    }
    document.getElementById('cga-editor').innerHTML = html;
    dirty = false;
    document.getElementById('cga-editor').addEventListener('input', () => { dirty = true; }, { once: true });
  }

  // ── Save ──────────────────────────────────────────────────────────────────
  document.getElementById('cga-save').addEventListener('click', async () => {
    if (!curId) return;
    const form   = document.getElementById('cga-editor');
    const action = pane === 'gifts' ? 'cg_admin_save_gift' : 'cg_admin_save_weapon';
    const params = { id: curId };

    form.querySelectorAll('input[type="text"], textarea').forEach(el => {
      params[el.name] = el.value;
    });
    form.querySelectorAll('input[type="checkbox"]').forEach(el => {
      params[el.name] = el.checked ? '1' : '0';
    });

    document.getElementById('cga-save').disabled = true;
    const res = await post(action, params);
    document.getElementById('cga-save').disabled = false;

    if (res.success) {
      status('Saved ✓', 'ok');
      dirty = false;
      // Refresh the sidebar name in case name changed
      loadList(document.getElementById('cga-search').value.trim());
    } else {
      status('Error: ' + (res.data || 'save failed'), 'err');
    }
  });

  // ── Keyboard shortcuts ────────────────────────────────────────────────────
  document.addEventListener('keydown', e => {
    // Ctrl/Cmd + S → save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      document.getElementById('cga-save').click();
    }
    // Alt + Left/Right → prev/next (only when not in a text field)
    if (e.altKey && !e.target.matches('input, textarea, select')) {
      if (e.key === 'ArrowLeft'  && curPrevId) { e.preventDefault(); loadRecord(curPrevId); }
      if (e.key === 'ArrowRight' && curNextId) { e.preventDefault(); loadRecord(curNextId); }
    }
  });

  // ── Logout ────────────────────────────────────────────────────────────────
  window.doLogout = async function (e) {
    e.preventDefault();
    await post('cg_logout_user');
    location.href = '/';
  };

  // ── Escape helper ─────────────────────────────────────────────────────────
  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ── Boot ─────────────────────────────────────────────────────────────────
  loadList();
})();
</script>
</body>
</html>
