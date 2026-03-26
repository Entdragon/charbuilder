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
<title>Content Editor — Library of Calabria</title>
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
  --danger-dim:#8b2a20;
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
.cga-tool-btn { font-size: 0.8rem; color: var(--muted); background: none; cursor: pointer;
                padding: 0.25rem 0.6rem; border: 1px solid var(--border); border-radius: var(--radius);
                font-family: inherit; }
.cga-tool-btn:hover { color: var(--gold); border-color: var(--gold); }

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
.cga-field textarea.tall  { min-height: 180px; }
.cga-field textarea.xtall { min-height: 280px; }
.cga-row     { display: flex; gap: 1rem; }
.cga-row .cga-field { flex: 1; }
.cga-check   { display: flex; align-items: center; gap: 0.4rem; font-size: 0.88rem; cursor: pointer; }
.cga-check input { accent-color: var(--gold); width: 1rem; height: 1rem; cursor: pointer; }
.cga-divider { border: none; border-top: 1px solid var(--border); margin: 1rem 0; }

/* ── Child table section heading ── */
.cga-child-header { display: flex; align-items: center; justify-content: space-between;
                    margin: 1.5rem 0 0.6rem; }
.cga-child-header h3 { font-size: 0.82rem; font-weight: 700; text-transform: uppercase;
                       letter-spacing: .07em; color: var(--gold); }
.cga-child-header small { font-size: 0.72rem; color: var(--muted); }
.cga-add-btn { padding: 0.25rem 0.75rem; background: transparent; border: 1px solid var(--gold-dim);
               color: var(--gold); border-radius: var(--radius); cursor: pointer; font-size: 0.78rem;
               transition: background .15s; }
.cga-add-btn:hover { background: rgba(201,168,76,0.12); }

/* ── Child row card ── */
.cga-child-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
                  margin-bottom: 0.75rem; overflow: hidden; }
.cga-child-card-head { display: flex; align-items: center; gap: 0.5rem; padding: 0.45rem 0.75rem;
                       border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.2); cursor: pointer; }
.cga-child-card-head .badge { font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
                               letter-spacing: .05em; padding: 0.1rem 0.4rem; border-radius: 3px;
                               background: rgba(201,168,76,0.15); color: var(--gold); }
.cga-child-card-head .title { flex: 1; font-size: 0.85rem; color: var(--text); overflow: hidden;
                               text-overflow: ellipsis; white-space: nowrap; }
.cga-child-card-head .toggle { font-size: 0.75rem; color: var(--muted); flex-shrink: 0; }
.cga-child-card-body { padding: 0.75rem; display: none; }
.cga-child-card.open .cga-child-card-body { display: block; }
.cga-child-card-foot { display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 0.75rem; }
.cga-row-save { padding: 0.28rem 0.8rem; background: var(--gold); color: #1a1510; border: none;
                border-radius: var(--radius); font-weight: 700; cursor: pointer; font-size: 0.82rem; }
.cga-row-save:hover { opacity: 0.85; }
.cga-row-del  { padding: 0.28rem 0.75rem; background: transparent; border: 1px solid var(--danger-dim);
                color: var(--danger); border-radius: var(--radius); cursor: pointer; font-size: 0.82rem; }
.cga-row-del:hover { background: rgba(192,57,43,0.12); }
.cga-row-status { font-size: 0.75rem; margin-right: auto; padding: 0.2rem 0; }
.cga-row-status.ok  { color: var(--success); }
.cga-row-status.err { color: var(--danger); }

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
    <button class="cga-tool-btn" onclick="runInstallSpells(this)">Install Spells</button>
    <a class="cga-logout" href="/ajax.php?action=cg_logout_user" onclick="return doLogout(event)">Log out</a>
  </div>

  <div class="cga-tabs" style="padding: 0 1rem; background: var(--panel); border-bottom: 1px solid var(--border);">
    <div class="cga-tab active" data-pane="gifts">Gifts</div>
    <div class="cga-tab"        data-pane="weapons">Weapons</div>
  </div>

  <div class="cga-body">
    <div class="cga-sidebar">
      <div class="cga-search">
        <input id="cga-search" type="search" placeholder="Search…" autocomplete="off">
      </div>
      <div class="cga-list" id="cga-list"></div>
    </div>

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
  let pane      = 'gifts';
  let allItems  = [];
  let filtItems = [];
  let curId     = null;
  let curPrevId = null;
  let curNextId = null;

  // ── AJAX helper ───────────────────────────────────────────────────────────
  async function post(action, params = {}) {
    const body = new URLSearchParams({ action, ...params });
    const res  = await fetch(AJAX, { method: 'POST', credentials: 'include', body });
    return res.json();
  }

  // ── Global status ─────────────────────────────────────────────────────────
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

  // ── Sidebar ───────────────────────────────────────────────────────────────
  async function loadList(search = '') {
    const action = pane === 'gifts' ? 'cg_admin_list_gifts' : 'cg_admin_list_weapons';
    const res    = await post(action, search ? { search } : {});
    if (!res.success) return;
    allItems  = res.data;
    filtItems = allItems;
    renderList();
  }

  function renderList() {
    const ul      = document.getElementById('cga-list');
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

  let searchTimer;
  document.getElementById('cga-search').addEventListener('input', e => {
    clearTimeout(searchTimer);
    const q = e.target.value.trim().toLowerCase();
    searchTimer = setTimeout(() => {
      const nameKey = pane === 'gifts' ? 'ct_gifts_name' : 'ct_weapons_name';
      filtItems = q ? allItems.filter(i => (i[nameKey] || '').toLowerCase().includes(q)) : allItems;
      renderList();
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

    const record  = pane === 'gifts' ? res.data.gift : res.data.weapon;
    const nameKey = pane === 'gifts' ? 'ct_gifts_name' : 'ct_weapons_name';
    document.getElementById('cga-rec-label').textContent = `ID ${record.ct_id} — ${record[nameKey] || ''}`;

    renderEditor(record);
    renderList();
    status('');

    if (pane === 'gifts') loadGiftChildren(id);
  }

  document.getElementById('cga-prev').addEventListener('click', () => { if (curPrevId) loadRecord(curPrevId); });
  document.getElementById('cga-next').addEventListener('click', () => { if (curNextId) loadRecord(curNextId); });

  // ── Field builder ─────────────────────────────────────────────────────────
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
    if (opts.select) {
      const opts_html = opts.options.map(o =>
        `<option value="${esc(o.v)}"${o.v === String(val ?? '') ? ' selected' : ''}>${esc(o.l)}</option>`
      ).join('');
      return `<div class="cga-field"><label>${label}</label><select name="${col}">${opts_html}</select></div>`;
    }
    return `<div class="cga-field"><label>${label}</label>
      <input type="text" name="${col}" value="${v}"></div>`;
  }

  // ── Render main editor form ───────────────────────────────────────────────
  function renderEditor(r) {
    let html = '';
    if (pane === 'gifts') {
      html = `
        <div id="cga-main-form">
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
          <p style="font-size:0.75rem;color:var(--muted);margin-bottom:0.75rem;">
            ↓ Character Generator fields (gift card + detail view)
          </p>
          ${field('ct_gifts_effect', 'Effect — short (character generator card)', r.ct_gifts_effect, { textarea: true, rows: 3 })}
          ${field('ct_gifts_effect_description', 'Full Description — with @@markers (character generator detail)', r.ct_gifts_effect_description, { textarea: true, xtall: true })}
          <hr class="cga-divider">
          ${field('ct_gift_trigger', 'Trigger (character generator)', r.ct_gift_trigger, { textarea: true, rows: 2 })}
        </div>
        <div id="cga-children"><p style="color:var(--muted);font-size:0.82rem;margin-top:1rem;">Loading WordPress data…</p></div>
      `;
    } else {
      html = `
        <div id="cga-main-form">
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
        </div>
      `;
    }
    document.getElementById('cga-editor').innerHTML = html;
  }

  // ── Main Save (scoped to #cga-main-form) ─────────────────────────────────
  document.getElementById('cga-save').addEventListener('click', async () => {
    if (!curId) return;
    const form   = document.getElementById('cga-main-form');
    if (!form) return;
    const action = pane === 'gifts' ? 'cg_admin_save_gift' : 'cg_admin_save_weapon';
    const params = { id: curId };

    form.querySelectorAll('input[type="text"], textarea').forEach(el => {
      if (el.name) params[el.name] = el.value;
    });
    form.querySelectorAll('input[type="checkbox"]').forEach(el => {
      if (el.name) params[el.name] = el.checked ? '1' : '0';
    });

    document.getElementById('cga-save').disabled = true;
    const res = await post(action, params);
    document.getElementById('cga-save').disabled = false;

    if (res.success) {
      status('Saved ✓', 'ok');
      loadList(document.getElementById('cga-search').value.trim());
    } else {
      status('Error: ' + (res.data || 'save failed'), 'err');
    }
  });

  // ── Gift children: Rules + Sections ──────────────────────────────────────
  const RULE_TYPES = [
    { v: 'passive',          l: 'Passive' },
    { v: 'action',           l: 'Action' },
    { v: 'reaction',         l: 'Reaction' },
    { v: 'improved_action',  l: 'Improved Action' },
    { v: 'long_action',      l: 'Long Action' },
    { v: 'start',            l: 'Start of Turn' },
    { v: 'stunt',            l: 'Stunt' },
    { v: 'note',             l: 'Note' },
  ];
  const SECTION_TYPES = [
    { v: 'rules',   l: 'Rules' },
    { v: 'flavour', l: 'Flavour' },
    { v: 'trigger', l: 'Trigger' },
    { v: 'table',   l: 'Table' },
    { v: 'note',    l: 'Note' },
  ];

  async function loadGiftChildren(giftId) {
    const res = await post('cg_admin_get_gift_children', { gift_id: giftId });
    const container = document.getElementById('cga-children');
    if (!container) return;
    if (!res.success) {
      container.innerHTML = `<p style="color:var(--danger);font-size:0.82rem;">Failed to load WordPress data.</p>`;
      return;
    }
    renderChildren(container, giftId, res.data.rules, res.data.sections);
  }

  function renderChildren(container, giftId, rules, sections) {
    container.innerHTML = `
      <hr class="cga-divider" style="margin-top:1.5rem;">
      <div class="cga-child-header">
        <div>
          <h3>Rules <span style="font-size:0.68rem;font-weight:400;color:var(--muted);">→ WordPress "Effect" section</span></h3>
        </div>
        <button class="cga-add-btn" id="cga-add-rule">+ Add Rule</button>
      </div>
      <div id="cga-rules-list"></div>

      <div class="cga-child-header" style="margin-top:1.25rem;">
        <div>
          <h3>Sections <span style="font-size:0.68rem;font-weight:400;color:var(--muted);">→ WordPress "Effect Description" section</span></h3>
        </div>
        <button class="cga-add-btn" id="cga-add-section">+ Add Section</button>
      </div>
      <div id="cga-sections-list"></div>
    `;

    const rulesList    = document.getElementById('cga-rules-list');
    const sectionsList = document.getElementById('cga-sections-list');

    rules.forEach(r => rulesList.appendChild(makeRuleCard(r, giftId)));
    sections.forEach(s => sectionsList.appendChild(makeSectionCard(s, giftId)));

    if (!rules.length)    rulesList.innerHTML    = '<p style="color:var(--muted);font-size:0.82rem;margin:0.4rem 0 0.8rem;">No rules yet.</p>';
    if (!sections.length) sectionsList.innerHTML = '<p style="color:var(--muted);font-size:0.82rem;margin:0.4rem 0 0.8rem;">No sections yet.</p>';

    document.getElementById('cga-add-rule').addEventListener('click', () => {
      const blank = { ct_id: 0, ct_sort: 10, ct_rule_type: 'passive', ct_rule_title: '',
                      ct_cost_text: '', ct_limit_text: '', ct_summary: '', ct_details: '' };
      const card = makeRuleCard(blank, giftId, true);
      const placeholder = rulesList.querySelector('p');
      if (placeholder) placeholder.remove();
      rulesList.appendChild(card);
      card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    document.getElementById('cga-add-section').addEventListener('click', () => {
      const blank = { ct_id: 0, ct_sort: 10, ct_section_type: 'rules', ct_heading: '', ct_body: '' };
      const card = makeSectionCard(blank, giftId, true);
      const placeholder = sectionsList.querySelector('p');
      if (placeholder) placeholder.remove();
      sectionsList.appendChild(card);
      card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  }

  function makeRuleCard(r, giftId, startOpen = false) {
    const card = document.createElement('div');
    card.className = 'cga-child-card' + (startOpen ? ' open' : '');
    card.dataset.ctId = r.ct_id;

    const typeLabel = RULE_TYPES.find(t => t.v === r.ct_rule_type)?.l ?? r.ct_rule_type ?? 'Passive';
    const headTitle = r.ct_rule_title ? `${typeLabel}: ${r.ct_rule_title}` : (r.ct_summary || typeLabel);
    const shortHead = headTitle.length > 60 ? headTitle.slice(0, 57) + '…' : headTitle;

    const ruleTypeField = field('ct_rule_type', 'Type', r.ct_rule_type, { select: true, options: RULE_TYPES });

    card.innerHTML = `
      <div class="cga-child-card-head">
        <span class="badge">${esc(typeLabel)}</span>
        <span class="title">${esc(shortHead)}</span>
        <span class="toggle">▾</span>
      </div>
      <div class="cga-child-card-body">
        <div class="cga-row">
          <div class="cga-field" style="flex:0 0 80px"><label>Sort</label>
            <input type="text" name="ct_sort" value="${esc(r.ct_sort)}"></div>
          ${ruleTypeField}
          ${field('ct_rule_title', 'Title', r.ct_rule_title)}
        </div>
        <div class="cga-row">
          ${field('ct_cost_text',  'Cost',  r.ct_cost_text)}
          ${field('ct_limit_text', 'Limit', r.ct_limit_text)}
        </div>
        ${field('ct_summary', 'Summary (shown on WP page)', r.ct_summary, { textarea: true, rows: 3 })}
        ${field('ct_details', 'Details (expanded)', r.ct_details, { textarea: true, tall: true })}
        <div class="cga-child-card-foot">
          <span class="cga-row-status"></span>
          <button class="cga-row-del">Delete</button>
          <button class="cga-row-save">Save Rule</button>
        </div>
      </div>
    `;

    card.querySelector('.cga-child-card-head').addEventListener('click', () => {
      card.classList.toggle('open');
    });

    card.querySelector('.cga-row-save').addEventListener('click', async () => {
      const btn    = card.querySelector('.cga-row-save');
      const st     = card.querySelector('.cga-row-status');
      const params = { gift_id: giftId, ct_id: card.dataset.ctId };
      card.querySelectorAll('input[type="text"], textarea, select').forEach(el => {
        if (el.name) params[el.name] = el.value;
      });
      btn.disabled = true;
      const res = await post('cg_admin_save_gift_rule', params);
      btn.disabled = false;
      if (res.success) {
        if (card.dataset.ctId == 0) card.dataset.ctId = res.data.ct_id;
        st.textContent = 'Saved ✓'; st.className = 'cga-row-status ok';
        setTimeout(() => { st.textContent = ''; }, 2500);
        // Update card heading
        const tv = card.querySelector('[name="ct_rule_type"]')?.value ?? 'passive';
        const tl = RULE_TYPES.find(t => t.v === tv)?.l ?? tv;
        const ti = card.querySelector('[name="ct_rule_title"]')?.value ?? '';
        const su = card.querySelector('[name="ct_summary"]')?.value ?? '';
        const hd = (ti ? `${tl}: ${ti}` : su || tl).slice(0, 60);
        card.querySelector('.badge').textContent = tl;
        card.querySelector('.title').textContent = hd;
      } else {
        st.textContent = 'Error: ' + (res.data || '?'); st.className = 'cga-row-status err';
      }
    });

    card.querySelector('.cga-row-del').addEventListener('click', async () => {
      const ctId = parseInt(card.dataset.ctId);
      if (ctId && !confirm('Delete this rule?')) return;
      if (ctId) await post('cg_admin_delete_gift_rule', { ct_id: ctId });
      card.remove();
    });

    return card;
  }

  function makeSectionCard(s, giftId, startOpen = false) {
    const card = document.createElement('div');
    card.className = 'cga-child-card' + (startOpen ? ' open' : '');
    card.dataset.ctId = s.ct_id;

    const typeLabel = SECTION_TYPES.find(t => t.v === s.ct_section_type)?.l ?? s.ct_section_type ?? 'Rules';
    const headTitle = s.ct_heading || s.ct_body?.slice(0, 60) || typeLabel;
    const shortHead = headTitle.length > 60 ? headTitle.slice(0, 57) + '…' : headTitle;

    const secTypeField = field('ct_section_type', 'Type', s.ct_section_type, { select: true, options: SECTION_TYPES });

    card.innerHTML = `
      <div class="cga-child-card-head">
        <span class="badge">${esc(typeLabel)}</span>
        <span class="title">${esc(shortHead)}</span>
        <span class="toggle">▾</span>
      </div>
      <div class="cga-child-card-body">
        <div class="cga-row">
          <div class="cga-field" style="flex:0 0 80px"><label>Sort</label>
            <input type="text" name="ct_sort" value="${esc(s.ct_sort)}"></div>
          ${secTypeField}
        </div>
        ${field('ct_heading', 'Heading', s.ct_heading)}
        ${field('ct_body',    'Body (shown on WP page)', s.ct_body, { textarea: true, xtall: true })}
        <div class="cga-child-card-foot">
          <span class="cga-row-status"></span>
          <button class="cga-row-del">Delete</button>
          <button class="cga-row-save">Save Section</button>
        </div>
      </div>
    `;

    card.querySelector('.cga-child-card-head').addEventListener('click', () => {
      card.classList.toggle('open');
    });

    card.querySelector('.cga-row-save').addEventListener('click', async () => {
      const btn    = card.querySelector('.cga-row-save');
      const st     = card.querySelector('.cga-row-status');
      const params = { gift_id: giftId, ct_id: card.dataset.ctId };
      card.querySelectorAll('input[type="text"], textarea, select').forEach(el => {
        if (el.name) params[el.name] = el.value;
      });
      btn.disabled = true;
      const res = await post('cg_admin_save_gift_section', params);
      btn.disabled = false;
      if (res.success) {
        if (card.dataset.ctId == 0) card.dataset.ctId = res.data.ct_id;
        st.textContent = 'Saved ✓'; st.className = 'cga-row-status ok';
        setTimeout(() => { st.textContent = ''; }, 2500);
        const tv = card.querySelector('[name="ct_section_type"]')?.value ?? 'rules';
        const tl = SECTION_TYPES.find(t => t.v === tv)?.l ?? tv;
        const hd = (card.querySelector('[name="ct_heading"]')?.value || card.querySelector('[name="ct_body"]')?.value || tl).slice(0, 60);
        card.querySelector('.badge').textContent = tl;
        card.querySelector('.title').textContent = hd;
      } else {
        st.textContent = 'Error: ' + (res.data || '?'); st.className = 'cga-row-status err';
      }
    });

    card.querySelector('.cga-row-del').addEventListener('click', async () => {
      const ctId = parseInt(card.dataset.ctId);
      if (ctId && !confirm('Delete this section?')) return;
      if (ctId) await post('cg_admin_delete_gift_section', { ct_id: ctId });
      card.remove();
    });

    return card;
  }

  // ── Keyboard shortcuts ────────────────────────────────────────────────────
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      document.getElementById('cga-save').click();
    }
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

  // ── Install Spells ────────────────────────────────────────────────────────
  window.runInstallSpells = async function (btn) {
    btn.disabled = true;
    btn.textContent = 'Installing…';
    try {
      const r = await post('cg_install_spells');
      status(r.data || (r.success ? 'Spells installed.' : 'Failed.'), r.success ? 'ok' : 'err');
    } catch (err) {
      status('Request failed: ' + err.message, 'err');
    }
    btn.disabled = false;
    btn.textContent = 'Install Spells';
  };

  // ── Escape helper ─────────────────────────────────────────────────────────
  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ── Boot ──────────────────────────────────────────────────────────────────
  loadList();
})();
</script>
</body>
</html>
