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

/* ── Pane selector ── */
.cga-pane-row { padding: 0.45rem 0.5rem; border-bottom: 1px solid var(--border); flex-shrink: 0; }
.cga-pane-row select { width: 100%; padding: 0.35rem 0.5rem; background: var(--surface);
  border: 1px solid var(--border); border-radius: var(--radius); color: var(--text);
  font-size: 0.82rem; outline: none; cursor: pointer; }
.cga-pane-row select:focus { border-color: var(--gold); }

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

/* ── @@ block preview panel ── */
.cga-at-preview { border: 1px solid var(--border); border-radius: var(--radius);
                  background: var(--surface); margin-top: 0.5rem; overflow: hidden; }
.cga-at-preview-head { display: flex; align-items: center; gap: 0.5rem;
                        padding: 0.3rem 0.65rem; background: rgba(0,0,0,0.25);
                        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
                        letter-spacing: .07em; color: var(--muted); border-bottom: 1px solid var(--border); }
.cga-at-preview-head button { margin-left: auto; font-size: 0.68rem; background: none;
                               border: 1px solid var(--border); border-radius: 3px; color: var(--muted);
                               cursor: pointer; padding: 0.1rem 0.4rem; font-family: inherit; }
.cga-at-preview-head button:hover { color: var(--text); border-color: var(--gold-dim); }
.cga-at-preview-body { max-height: 280px; overflow-y: auto; }
.cga-at-block { padding: 0.4rem 0.65rem; border-bottom: 1px solid rgba(255,255,255,0.04); }
.cga-at-block:last-child { border-bottom: none; }
.cga-at-tag { display: inline-block; font-size: 0.68rem; font-weight: 700; font-family: monospace;
              padding: 0.1rem 0.4rem; border-radius: 3px; border: 1px solid; margin-bottom: 0.25rem;
              letter-spacing: .03em; }
.cga-at-body { font-size: 0.74rem; font-family: monospace; white-space: pre-wrap; word-break: break-word;
               color: var(--muted); margin: 0; max-height: 90px; overflow-y: auto; }
.cga-at-issues { padding: 0.4rem 0.65rem; background: rgba(192,57,43,0.1);
                 border-bottom: 1px solid rgba(192,57,43,0.3); }
.cga-at-issue  { font-size: 0.74rem; color: #e07060; padding: 0.1rem 0; }
.cga-at-issue::before { content: '⚠ '; }
.cga-at-empty  { padding: 0.5rem 0.65rem; font-size: 0.78rem; color: var(--muted); }
.cga-at-preview.cga-at-collapsed .cga-at-preview-body { display: none; }

/* Batch fix modal */
#cga-batchfix-modal { position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:1000;display:flex;align-items:flex-start;justify-content:center;padding-top:3rem; }
#cga-batchfix-modal.hidden { display:none; }
.cga-bfm-box { background:var(--panel);border:1px solid var(--border);border-radius:6px;width:min(860px,94vw);max-height:80vh;display:flex;flex-direction:column;overflow:hidden; }
.cga-bfm-head { display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;border-bottom:1px solid var(--border);font-weight:600; }
.cga-bfm-head input { flex:1;background:var(--bg);border:1px solid var(--border);border-radius:4px;padding:0.3rem 0.55rem;color:var(--fg);font-size:0.82rem; }
.cga-bfm-body { flex:1;overflow-y:auto;padding:0.75rem 1rem; }
.cga-bfm-foot { display:flex;align-items:center;gap:0.75rem;padding:0.65rem 1rem;border-top:1px solid var(--border); }
.cga-bfm-item { border:1px solid var(--border);border-radius:4px;margin-bottom:0.75rem;overflow:hidden; }
.cga-bfm-item-head { display:flex;align-items:center;gap:0.65rem;padding:0.4rem 0.65rem;background:rgba(255,255,255,0.04);cursor:pointer;font-size:0.82rem; }
.cga-bfm-item-head input[type=checkbox] { accent-color:var(--gold);width:1rem;height:1rem; }
.cga-bfm-item-name { font-weight:600;flex:1; }
.cga-bfm-item-note { color:var(--muted);font-size:0.75rem; }
.cga-bfm-diff { display:grid;grid-template-columns:1fr 1fr;gap:0;border-top:1px solid var(--border); }
.cga-bfm-diff-col { padding:0.5rem 0.65rem; }
.cga-bfm-diff-col:first-child { border-right:1px solid var(--border); }
.cga-bfm-diff-label { font-size:0.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.3rem; }
.cga-bfm-diff pre { margin:0;font-size:0.72rem;white-space:pre-wrap;word-break:break-word;color:var(--fg); }
.cga-bfm-empty { text-align:center;padding:2rem;color:var(--muted);font-size:0.88rem; }
.cga-at-nobadge { color: var(--muted); font-size: 0.72rem; }
.cga-at-warn-badge { background: rgba(192,57,43,0.2); color: #e07060; font-size: 0.68rem; font-weight: 700;
                     padding: 0.1rem 0.4rem; border-radius: 10px; }

/* ── Sidebar jump-to ── */
.cga-jump-row { display: flex; gap: 0.3rem; padding: 0.35rem 0.5rem; border-bottom: 1px solid var(--border); }
.cga-jump-row input { flex: 1; padding: 0.3rem 0.5rem; background: var(--surface); border: 1px solid var(--border);
                      border-radius: var(--radius); color: var(--text); font-size: 0.78rem; outline: none; }
.cga-jump-row input::placeholder { color: var(--muted); }
.cga-jump-row input:focus { border-color: var(--gold); }
.cga-jump-btn { padding: 0.3rem 0.55rem; background: transparent; border: 1px solid var(--border);
                border-radius: var(--radius); color: var(--gold); cursor: pointer; font-size: 0.78rem; white-space: nowrap; }
.cga-jump-btn:hover { background: rgba(201,168,76,0.12); border-color: var(--gold); }

/* ── Data Quality modal ── */
.cga-modal { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; }
.cga-modal[hidden] { display: none; }
.cga-modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.65); }
.cga-modal-panel { position: relative; width: min(820px, 94vw); max-height: 86vh;
                   display: flex; flex-direction: column; background: var(--panel);
                   border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.cga-modal-header { display: flex; align-items: center; padding: 0.7rem 1rem;
                    border-bottom: 1px solid var(--border); flex-shrink: 0; }
.cga-modal-header h2 { font-size: 0.95rem; font-weight: 700; color: var(--gold); letter-spacing: .04em; flex: 1; }
.cga-modal-close { background: none; border: 1px solid var(--border); color: var(--muted); cursor: pointer;
                   padding: 0.2rem 0.55rem; border-radius: var(--radius); font-size: 0.88rem; }
.cga-modal-close:hover { color: var(--text); border-color: var(--gold); }
.cga-modal-body { flex: 1; overflow-y: auto; }

/* Quality report groups */
.cga-qr-group { border-bottom: 1px solid var(--border); }
.cga-qr-group-head { display: flex; align-items: center; gap: 0.6rem; padding: 0.6rem 1rem;
                     cursor: pointer; user-select: none; }
.cga-qr-group-head:hover { background: rgba(201,168,76,0.06); }
.cga-qr-badge { background: rgba(201,168,76,0.18); color: var(--gold); font-size: 0.7rem; font-weight: 700;
                padding: 0.1rem 0.45rem; border-radius: 10px; flex-shrink: 0; }
.cga-qr-toggle { margin-left: auto; color: var(--muted); font-size: 0.8rem; transition: transform .15s; }
.cga-qr-group.open .cga-qr-toggle { transform: rotate(180deg); }
.cga-qr-group-body { display: none; }
.cga-qr-group.open .cga-qr-group-body { display: block; }
.cga-qr-row { display: flex; align-items: baseline; gap: 0.6rem; padding: 0.38rem 1rem 0.38rem 2.2rem;
              cursor: pointer; font-size: 0.85rem; border-top: 1px solid rgba(255,255,255,0.04); }
.cga-qr-row:hover { background: rgba(201,168,76,0.08); }
.cga-qr-name { font-weight: 600; color: var(--text); flex-shrink: 0; }
.cga-qr-unpub { font-size: 0.7rem; color: var(--muted); flex-shrink: 0; }
.cga-qr-detail { font-size: 0.78rem; color: var(--muted); margin-left: auto; text-align: right; }
</style>
</head>
<body>
<div class="cga-shell">

  <div class="cga-topbar">
    <span class="cga-logo">Library of Calabria — Content Editor</span>
    <span class="cga-user">Logged in as <?= $username ?></span>
    <button class="cga-tool-btn" onclick="openQualityReport()">Data Quality</button>
    <button class="cga-tool-btn" id="cga-batchfix-btn" onclick="openBatchFix()">Batch @@ Fix</button>
    <button class="cga-tool-btn" onclick="runInstallSpells(this)">Install Spells</button>
    <button class="cga-tool-btn" onclick="runUjInstall(this)" title="Create UJ tables + load all Species/Type/Career trait data">UJ: Install Data</button>
    <a class="cga-logout" href="/ajax.php?action=cg_logout_user" onclick="return doLogout(event)">Log out</a>
  </div>

  <div class="cga-body">
    <div class="cga-sidebar">
      <div class="cga-pane-row">
        <select id="cga-pane-sel">
          <optgroup label="── Ironclaw ──">
            <option value="ic-gifts">IC: Gifts</option>
            <option value="ic-weapons">IC: Weapons</option>
            <option value="ic-species">IC: Species</option>
            <option value="ic-careers">IC: Careers</option>
            <option value="ic-skills">IC: Skills</option>
            <option value="ic-equipment">IC: Equipment</option>
            <option value="ic-books">IC: Books</option>
          </optgroup>
          <optgroup label="── Urban Jungle ──">
            <option value="uj-gifts">UJ: Gifts</option>
            <option value="uj-species">UJ: Species</option>
            <option value="uj-types">UJ: Types</option>
            <option value="uj-careers">UJ: Careers</option>
            <option value="uj-skills">UJ: Skills</option>
            <option value="uj-soaks">UJ: Soaks</option>
            <option value="uj-items">UJ: Items</option>
            <option value="uj-books">UJ: Books</option>
          </optgroup>
        </select>
      </div>
      <div class="cga-search">
        <input id="cga-search" type="search" placeholder="Search…" autocomplete="off">
      </div>
      <div class="cga-jump-row">
        <input id="cga-jump-input" type="text" placeholder="Jump after… (e.g. Death Watch)">
        <button class="cga-jump-btn" id="cga-jump-btn" title="Navigate to the gift alphabetically after this name">After →</button>
      </div>
      <div class="cga-list" id="cga-list"></div>
    </div>

    <div class="cga-main">
      <div class="cga-editor-bar">
        <button class="cga-nav-btn" id="cga-prev" title="Previous" disabled>&#8249;</button>
        <span class="cga-record-id" id="cga-rec-label">Select a record to begin editing</span>
        <button class="cga-nav-btn" id="cga-next" title="Next" disabled>&#8250;</button>
        <span class="cga-status" id="cga-status"></span>
        <button class="cga-tool-btn" id="cga-new-btn" onclick="showNewForm()" style="margin-left:auto;">+ New</button>
        <button class="cga-save-btn" id="cga-save" disabled>Save</button>
      </div>
      <div class="cga-editor" id="cga-editor">
        <div class="cga-empty">← Select a record from the sidebar</div>
      </div>
    </div>
  </div>

</div>

<div id="cga-quality-modal" class="cga-modal" hidden>
  <div class="cga-modal-backdrop" onclick="closeQualityReport()"></div>
  <div class="cga-modal-panel">
    <div class="cga-modal-header">
      <h2>Gift Data Quality Report</h2>
      <button class="cga-modal-close" onclick="closeQualityReport()">✕</button>
    </div>
    <div id="cga-quality-body" class="cga-modal-body">
      <p style="color:var(--muted);padding:1rem;">Loading…</p>
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';

  const AJAX = '/ajax.php';
  let pane      = 'ic-gifts';
  let isNew     = false;
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

  // ── Simple-pane config (list/get/save/create via generic helpers) ─────────
  const SIMPLE_PANES = {
    'ic-species': {
      listAction: 'cg_admin_list_ic_species', getAction: 'cg_admin_get_ic_species',
      saveAction: 'cg_admin_save_ic_species', createAction: 'cg_admin_create_ic_species',
      fields: [
        { col: 'ct_species_name',        label: 'Name' },
        { col: 'ct_slug',                label: 'Slug' },
        { col: 'ct_species_description', label: 'Description', textarea: true, tall: true },
        { col: 'published',              label: 'Published', checkbox: true },
      ],
    },
    'ic-careers': {
      listAction: 'cg_admin_list_ic_careers', getAction: 'cg_admin_get_ic_careers',
      saveAction: 'cg_admin_save_ic_careers', createAction: 'cg_admin_create_ic_careers',
      fields: [
        { col: 'ct_career_name',        label: 'Name' },
        { col: 'ct_slug',               label: 'Slug' },
        { col: 'ct_career_type',        label: 'Type (1=Major, 2=Minor)', select: true,
          options: [{ v: '1', l: 'Major' }, { v: '2', l: 'Minor' }] },
        { col: 'ct_career_description', label: 'Description', textarea: true, tall: true },
        { col: 'published',             label: 'Published', checkbox: true },
      ],
    },
    'ic-skills': {
      listAction: 'cg_admin_list_ic_skills', getAction: 'cg_admin_get_ic_skills',
      saveAction: 'cg_admin_save_ic_skills', createAction: 'cg_admin_create_ic_skills',
      fields: [
        { col: 'ct_skill_name', label: 'Name' },
        { col: 'ct_slug',       label: 'Slug' },
        { col: 'published',     label: 'Published', checkbox: true },
      ],
    },
    'ic-equipment': {
      listAction: 'cg_admin_list_ic_equipment', getAction: 'cg_admin_get_ic_equipment',
      saveAction: 'cg_admin_save_ic_equipment', createAction: 'cg_admin_create_ic_equipment',
      fields: [
        { col: 'ct_name',        label: 'Name' },
        { col: 'ct_slug',        label: 'Slug' },
        { col: 'ct_category',    label: 'Category' },
        { col: 'ct_subcategory', label: 'Subcategory' },
        { col: 'ct_cost_d',      label: 'Cost (D)' },
        { col: 'ct_effect',      label: 'Effect', textarea: true, tall: true },
        { col: 'published',      label: 'Published', checkbox: true },
      ],
    },
    'ic-books': {
      listAction: 'cg_admin_list_ic_books', getAction: 'cg_admin_get_ic_books',
      saveAction: 'cg_admin_save_ic_books', createAction: 'cg_admin_create_ic_books',
      fields: [
        { col: 'ct_book_name',     label: 'Name' },
        { col: 'ct_ct_slug',       label: 'Slug' },
        { col: 'ct_book_abstract', label: 'Abstract', textarea: true, tall: true },
        { col: 'published',        label: 'Published', checkbox: true },
      ],
    },
    'uj-gifts': {
      listAction: 'cg_admin_list_uj_gifts', getAction: 'cg_admin_get_uj_gift',
      saveAction: 'cg_admin_save_uj_gift', createAction: 'cg_admin_create_uj_gift',
      fields: [
        { col: 'name',        label: 'Name' },
        { col: 'slug',        label: 'Slug' },
        { col: 'subtitle',    label: 'Subtitle' },
        { col: 'gift_type',   label: 'Gift Type' },
        { col: 'recharge',    label: 'Recharge' },
        { col: 'description', label: 'Description', textarea: true, tall: true },
        { col: 'published',   label: 'Published', checkbox: true },
      ],
    },
    'uj-species': {
      listAction: 'cg_admin_list_uj_species', getAction: 'cg_admin_get_uj_species',
      saveAction: 'cg_admin_save_uj_species', createAction: 'cg_admin_create_uj_species',
      fields: [
        { col: 'name',        label: 'Name' },
        { col: 'slug',        label: 'Slug' },
        { col: 'description', label: 'Description', textarea: true, tall: true },
        { col: 'published',   label: 'Published', checkbox: true },
      ],
    },
    'uj-types': {
      listAction: 'cg_admin_list_uj_types', getAction: 'cg_admin_get_uj_type',
      saveAction: 'cg_admin_save_uj_type', createAction: 'cg_admin_create_uj_type',
      fields: [
        { col: 'name',        label: 'Name' },
        { col: 'slug',        label: 'Slug' },
        { col: 'description', label: 'Description', textarea: true, tall: true },
        { col: 'published',   label: 'Published', checkbox: true },
      ],
    },
    'uj-careers': {
      listAction: 'cg_admin_list_uj_careers', getAction: 'cg_admin_get_uj_career',
      saveAction: 'cg_admin_save_uj_career', createAction: 'cg_admin_create_uj_career',
      fields: [
        { col: 'name',        label: 'Name' },
        { col: 'slug',        label: 'Slug' },
        { col: 'gear',        label: 'Starting Gear' },
        { col: 'description', label: 'Description', textarea: true, tall: true },
        { col: 'published',   label: 'Published', checkbox: true },
      ],
    },
    'uj-skills': {
      listAction: 'cg_admin_list_uj_skills', getAction: 'cg_admin_get_uj_skill',
      saveAction: 'cg_admin_save_uj_skill', createAction: 'cg_admin_create_uj_skill',
      fields: [
        { col: 'name',             label: 'Name' },
        { col: 'slug',             label: 'Slug' },
        { col: 'paired_trait',     label: 'Paired Trait' },
        { col: 'sample_favorites', label: 'Sample Favorites' },
        { col: 'gift_notes',       label: 'Gift Notes' },
        { col: 'description',      label: 'Description', textarea: true, tall: true },
        { col: 'published',        label: 'Published', checkbox: true },
      ],
    },
    'uj-soaks': {
      listAction: 'cg_admin_list_uj_soaks', getAction: 'cg_admin_get_uj_soak',
      saveAction: 'cg_admin_save_uj_soak', createAction: 'cg_admin_create_uj_soak',
      fields: [
        { col: 'name',           label: 'Name' },
        { col: 'slug',           label: 'Slug' },
        { col: 'soak_type',      label: 'Soak Type' },
        { col: 'damage_negated', label: 'Damage Negated' },
        { col: 'recharge',       label: 'Recharge' },
        { col: 'side_effect',    label: 'Side Effect' },
        { col: 'description',    label: 'Description', textarea: true, tall: true },
        { col: 'published',      label: 'Published', checkbox: true },
      ],
    },
    'uj-items': {
      listAction: 'cg_admin_list_uj_items', getAction: 'cg_admin_get_uj_item',
      saveAction: 'cg_admin_save_uj_item', createAction: 'cg_admin_create_uj_item',
      fields: [
        { col: 'name',        label: 'Name' },
        { col: 'slug',        label: 'Slug' },
        { col: 'cost_class',  label: 'Cost Class' },
        { col: 'price_early', label: 'Price (Early)' },
        { col: 'price_late',  label: 'Price (Late)' },
        { col: 'description', label: 'Description', textarea: true, tall: true },
        { col: 'published',   label: 'Published', checkbox: true },
      ],
    },
    'uj-books': {
      listAction: 'cg_admin_list_uj_books', getAction: 'cg_admin_get_uj_book',
      saveAction: 'cg_admin_save_uj_book', createAction: 'cg_admin_create_uj_book',
      fields: [
        { col: 'name',       label: 'Name' },
        { col: 'slug',       label: 'Slug' },
        { col: 'sort_order', label: 'Sort Order' },
        { col: 'blurb',      label: 'Blurb', textarea: true },
        { col: 'cover_url',  label: 'Cover Image URL' },
        { col: 'buy_url',    label: 'Buy URL' },
        { col: 'published',  label: 'Published', checkbox: true },
      ],
    },
  };

  // ── Global status ─────────────────────────────────────────────────────────
  function status(msg, type = 'ok') {
    const el = document.getElementById('cga-status');
    el.textContent = msg;
    el.className   = 'cga-status ' + type;
    if (msg) setTimeout(() => { if (el.textContent === msg) el.textContent = ''; }, 3000);
  }

  // ── Pane helpers ──────────────────────────────────────────────────────────
  function getItemId(item)   { return item.id !== undefined ? item.id : item.ct_id; }
  function getItemName(item) {
    if (item.name !== undefined)          return item.name;
    if (item.ct_gifts_name !== undefined) return item.ct_gifts_name;
    if (item.ct_weapons_name !== undefined) return item.ct_weapons_name;
    return '#' + getItemId(item);
  }
  function getListAction() {
    const cfg = SIMPLE_PANES[pane];
    if (cfg) return cfg.listAction;
    return pane === 'ic-gifts' ? 'cg_admin_list_gifts' : 'cg_admin_list_weapons';
  }

  // ── Pane select ───────────────────────────────────────────────────────────
  document.getElementById('cga-pane-sel').addEventListener('change', e => {
    if (e.target.value === pane) return;
    pane  = e.target.value;
    isNew = false;
    curId = null;
    document.getElementById('cga-editor').innerHTML = '<div class="cga-empty">← Select a record from the sidebar</div>';
    document.getElementById('cga-rec-label').textContent = 'Select a record to begin editing';
    document.getElementById('cga-prev').disabled = true;
    document.getElementById('cga-next').disabled = true;
    document.getElementById('cga-save').disabled = true;
    document.getElementById('cga-search').value = '';
    loadList();
  });

  // ── Sidebar ───────────────────────────────────────────────────────────────
  async function loadList(search = '') {
    const res = await post(getListAction(), search ? { search } : {});
    if (!res.success) return;
    allItems  = res.data;
    filtItems = allItems;
    renderList();
  }

  function renderList() {
    const ul = document.getElementById('cga-list');
    ul.innerHTML = filtItems.map(item => {
      const id     = getItemId(item);
      const active = id == curId ? ' active' : '';
      const unpub  = !parseInt(item.published) ? ' unpub' : '';
      const label  = getItemName(item);
      return `<div class="cga-list-item${active}${unpub}" data-id="${id}">${esc(label)}</div>`;
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
      filtItems = q ? allItems.filter(i => getItemName(i).toLowerCase().includes(q)) : allItems;
      renderList();
    }, 180);
  });

  // ── Load a record ─────────────────────────────────────────────────────────
  async function loadRecord(id) {
    isNew = false;
    const cfg = SIMPLE_PANES[pane];
    let action, record;

    if (cfg) {
      const res = await post(cfg.getAction, { id });
      if (!res.success) { status('Load failed: ' + res.data, 'err'); return; }
      record    = res.data.record;
      curId     = id;
      curPrevId = res.data.prev_id;
      curNextId = res.data.next_id;
    } else {
      const action2 = pane === 'ic-gifts' ? 'cg_admin_get_gift' : 'cg_admin_get_weapon';
      const res2    = await post(action2, { id });
      if (!res2.success) { status('Load failed: ' + res2.data, 'err'); return; }
      record    = pane === 'ic-gifts' ? res2.data.gift : res2.data.weapon;
      curId     = id;
      curPrevId = res2.data.prev_id;
      curNextId = res2.data.next_id;
    }

    document.getElementById('cga-prev').disabled = !curPrevId;
    document.getElementById('cga-next').disabled = !curNextId;
    document.getElementById('cga-save').disabled = false;

    const recId   = record.ct_id ?? record.id ?? id;
    const recName = getItemName(record);
    document.getElementById('cga-rec-label').textContent = `ID ${recId} — ${recName}`;

    renderEditor(record);
    renderList();
    status('');

    if (pane === 'ic-gifts') loadGiftChildren(id);
  }

  // ── Show new-record form ───────────────────────────────────────────────────
  window.showNewForm = function () {
    const cfg = SIMPLE_PANES[pane];
    if (!cfg) {
      const names = { 'ic-gifts': 'gifts', 'ic-weapons': 'weapons' };
      const label = names[pane] || pane;
      document.getElementById('cga-editor').innerHTML =
        `<div class="cga-empty" style="max-width:460px;text-align:left;line-height:1.7;">
          <strong style="color:var(--gold);">Add New — ${esc(label)}</strong><br><br>
          New ${esc(label)} are created directly in WordPress (CustomTables), which handles
          the full data structure including rules, sections, and sync.<br><br>
          Once created in WordPress, return here to edit the content.
        </div>`;
      document.getElementById('cga-rec-label').textContent = '+ New (WordPress)';
      document.getElementById('cga-save').disabled = true;
      return;
    }
    isNew = true;
    curId = null;
    curPrevId = null;
    curNextId = null;
    document.getElementById('cga-prev').disabled = true;
    document.getElementById('cga-next').disabled = true;
    document.getElementById('cga-save').disabled = false;
    document.getElementById('cga-rec-label').textContent = '+ New Record';
    renderEditor({});
    renderList();
  };

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
  function renderSimpleForm(r, cfg) {
    const rows = cfg.fields.map(f => {
      const opts = {};
      if (f.textarea)  { opts.textarea = true; if (f.tall) opts.tall = true; if (f.xtall) opts.xtall = true; }
      if (f.checkbox)  { opts.checkbox = true; }
      if (f.select)    { opts.select = true; opts.options = f.options; }
      return field(f.col, f.label, r[f.col], opts);
    }).join('');
    return `<div id="cga-main-form">${rows}</div>`;
  }

  function renderEditor(r) {
    const cfg = SIMPLE_PANES[pane];
    let html = '';
    if (cfg) {
      html = renderSimpleForm(r, cfg);
    } else if (pane === 'ic-gifts') {
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
          <div class="cga-at-preview" id="cga-at-preview">
            <div class="cga-at-preview-head">
              @@ Block Preview
              <span id="cga-at-badge" class="cga-at-nobadge"></span>
              <button onclick="document.getElementById('cga-at-preview-body').parentElement.classList.toggle('cga-at-collapsed')" title="Collapse/expand preview">▾</button>
            </div>
            <div class="cga-at-preview-body" id="cga-at-preview-body"><div class="cga-at-empty">Parsing…</div></div>
          </div>
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
    if (pane === 'ic-gifts') initDescPreview();
  }

  // ── @@ block parser + preview ─────────────────────────────────────────────

  const AT_COLORS = {
    PASSIVE:  { bg: '#1a3328', border: '#2a6a50', text: '#5dbf90' },
    ACTION:   { bg: '#2d2010', border: '#7a5010', text: '#d4902a' },
    REACTION: { bg: '#2a1010', border: '#7a2a2a', text: '#d47070' },
    TRIGGER:  { bg: '#102030', border: '#205060', text: '#5090c0' },
    REQUIRES: { bg: '#200a30', border: '#5a2080', text: '#a060e0' },
    SECTION:  { bg: '#102428', border: '#205a60', text: '#50b0c0' },
    FLAVOUR:  { bg: '#28201a', border: '#6a5030', text: '#c0906a' },
    FLAVOR:   { bg: '#28201a', border: '#6a5030', text: '#c0906a' },
    TRAPPINGS:{ bg: '#1e2010', border: '#4a5020', text: '#90a040' },
    REFRESH:  { bg: '#181e2a', border: '#2a3a6a', text: '#6080c8' },
    NOTE:     { bg: '#1a1a1a', border: '#404040', text: '#909090' },
  };

  function parseAtBlocks(text) {
    const norm  = text.replace(/\r\n|\r/g, '\n');
    const re    = /(?:^|\n)(@@[A-Za-z][A-Za-z_]*\s*:[ \t]*)/g;
    const found = [];
    let m;
    while ((m = re.exec(norm)) !== null) {
      const raw       = m[1].trim();
      const directive = raw.replace(/^@@([A-Za-z_]+)\s*:.*$/, '$1').toUpperCase();
      found.push({ offset: m.index, matchLen: m[0].length, directive });
    }
    if (!found.length) return null;
    return found.map((f, i) => {
      const contentStart = f.offset + f.matchLen;
      const contentEnd   = i + 1 < found.length ? found[i + 1].offset : norm.length;
      return { directive: f.directive, content: norm.slice(contentStart, contentEnd).trimEnd() };
    });
  }

  function detectAtIssues(blocks) {
    const issues = [];
    if (!blocks) return issues;
    const nonReqBlock = b => b.directive !== 'REQUIRES';
    const traitPat   = /\b(d4|d6|d8|d10|d12)\s+or\s+better/i;
    const langPat    = /^(Language|Literacy)\s*:/im;
    const requiresPat= /\bRequires?\s*\n/i;

    for (const b of blocks) {
      if (nonReqBlock(b)) {
        if (requiresPat.test(b.content))
          issues.push(`@@${b.directive}: contains a "Requires" list — split into @@REQUIRES: lines`);
        else if (traitPat.test(b.content))
          issues.push(`@@${b.directive}: contains a trait requirement (e.g. "d8 or better") — move to @@REQUIRES:`);
        if (langPat.test(b.content))
          issues.push(`@@${b.directive}: contains a Language/Literacy line — move to @@REQUIRES:`);
      }
      if (b.directive === 'PASSIVE') {
        const lines = b.content.split('\n').filter(l => l.trim());
        if (lines.length > 6 && b.content.length > 250)
          issues.push(`@@PASSIVE: block is very long (${b.content.length} chars) — may contain mixed content`);
      }
    }
    return issues;
  }

  function updateAtPreview(text) {
    const body  = document.getElementById('cga-at-preview-body');
    const badge = document.getElementById('cga-at-badge');
    if (!body) return;

    if (!text.trim()) {
      body.innerHTML  = '<div class="cga-at-empty">No description entered.</div>';
      if (badge) { badge.textContent = ''; badge.className = 'cga-at-nobadge'; }
      return;
    }

    const blocks = parseAtBlocks(text);
    if (!blocks) {
      body.innerHTML  = '<div class="cga-at-empty">No @@ tags found — plain text only.</div>';
      if (badge) { badge.textContent = 'no @@ tags'; badge.className = 'cga-at-nobadge'; }
      return;
    }

    const issues = detectAtIssues(blocks);

    let html = '';
    if (issues.length) {
      html += '<div class="cga-at-issues">' + issues.map(i => `<div class="cga-at-issue">${esc(i)}</div>`).join('') + '</div>';
    }

    blocks.forEach(b => {
      const c = AT_COLORS[b.directive] || { bg: '#1a1a1a', border: '#444', text: '#999' };
      html += `<div class="cga-at-block">
        <span class="cga-at-tag" style="background:${c.bg};border-color:${c.border};color:${c.text}">@@${esc(b.directive)}:</span>
        <pre class="cga-at-body">${esc(b.content || '(empty)')}</pre>
      </div>`;
    });

    body.innerHTML = html;

    if (badge) {
      if (issues.length) {
        badge.textContent = issues.length + ' issue' + (issues.length > 1 ? 's' : '');
        badge.className   = 'cga-at-warn-badge';
      } else {
        badge.textContent = blocks.length + ' block' + (blocks.length > 1 ? 's' : '');
        badge.className   = 'cga-at-nobadge';
      }
    }
  }

  function initDescPreview() {
    const ta = document.querySelector('[name="ct_gifts_effect_description"]');
    if (!ta) return;
    let timer;
    ta.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => updateAtPreview(ta.value), 120);
    });
    updateAtPreview(ta.value);
  }

  // ── Sidebar "Jump after" ──────────────────────────────────────────────────

  function jumpAfter(name) {
    if (!name.trim() || !allItems.length) return;
    const q     = name.trim().toLowerCase();
    const after = allItems.filter(i => getItemName(i).toLowerCase() > q);
    if (!after.length) { status('No record found after "' + name + '"', 'err'); return; }
    const target  = after[0];
    const targetId = getItemId(target);
    document.getElementById('cga-search').value = '';
    filtItems = allItems;
    renderList();
    loadRecord(targetId);
    setTimeout(() => {
      const el = document.querySelector(`.cga-list-item[data-id="${targetId}"]`);
      if (el) el.scrollIntoView({ block: 'center' });
    }, 150);
  }

  document.getElementById('cga-jump-btn').addEventListener('click', () => {
    jumpAfter(document.getElementById('cga-jump-input').value);
  });
  document.getElementById('cga-jump-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') jumpAfter(e.target.value);
  });

  // ── Main Save (scoped to #cga-main-form) ─────────────────────────────────
  document.getElementById('cga-save').addEventListener('click', async () => {
    if (!curId && !isNew) return;
    const form = document.getElementById('cga-main-form');
    if (!form) return;

    const params = {};
    if (!isNew) params.id = curId;
    form.querySelectorAll('input[type="text"], textarea, select').forEach(el => {
      if (el.name) params[el.name] = el.value;
    });
    form.querySelectorAll('input[type="checkbox"]').forEach(el => {
      if (el.name) params[el.name] = el.checked ? '1' : '0';
    });

    const saveBtn = document.getElementById('cga-save');
    saveBtn.disabled = true;
    try {
      const cfg = SIMPLE_PANES[pane];

      if (isNew && cfg) {
        const res = await post(cfg.createAction, params);
        if (res.success) {
          status('Created ✓', 'ok');
          isNew = false;
          curId = res.data.id;
          await loadList(document.getElementById('cga-search').value.trim());
          loadRecord(curId);
        } else {
          status('Error: ' + (res.data || 'create failed'), 'err');
        }
      } else if (cfg) {
        const res = await post(cfg.saveAction, params);
        if (res.success) {
          status('Saved ✓', 'ok');
          loadList(document.getElementById('cga-search').value.trim());
        } else {
          status('Error: ' + (res.data || 'save failed'), 'err');
        }
      } else if (pane === 'ic-gifts') {
        const res = await post('cg_admin_save_gift', params);
        if (res.success) {
          status('Syncing…', 'ok');
          const syncRes = await post('cg_admin_sync_single_gift', { gift_id: curId });
          if (syncRes.success) {
            status('Saved + Synced ✓', 'ok');
          } else {
            status('Saved ✓  (sync: ' + (syncRes.data || 'error') + ')', 'err');
          }
          await loadGiftChildren(curId);
          loadList(document.getElementById('cga-search').value.trim());
        } else {
          status('Error: ' + (res.data || 'save failed'), 'err');
        }
      } else {
        const res = await post('cg_admin_save_weapon', params);
        if (res.success) {
          status('Saved ✓', 'ok');
          loadList(document.getElementById('cga-search').value.trim());
        } else {
          status('Error: ' + (res.data || 'save failed'), 'err');
        }
      }
    } finally {
      saveBtn.disabled = false;
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
    if (e.key === 'Escape') { closeQualityReport(); return; }
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

  // ── Data Quality Report ───────────────────────────────────────────────────
  const ISSUE_META = {
    missing_effect:      { label: 'Missing Card Effect',               order: 1 },
    missing_description: { label: 'Missing Detail Description',        order: 2 },
    overlong_effect:     { label: 'Overlong Card Effect',              order: 3 },
    flavour_fallback:    { label: 'Fallback Section Is Non-Rules Only',order: 4 },
    trigger_unused:      { label: 'Has Trigger Text (Not Shown)',      order: 5 },
    rules_unused:        { label: 'Has Gift Rules (Not Shown)',        order: 6 },
  };

  window.openQualityReport = async function () {
    const modal = document.getElementById('cga-quality-modal');
    const body  = document.getElementById('cga-quality-body');
    modal.hidden = false;
    body.innerHTML = '<p style="color:var(--muted);padding:1rem;">Loading…</p>';

    const res = await post('cg_admin_gift_quality_report');
    if (!res.success) {
      body.innerHTML = `<p style="color:var(--danger);padding:1rem;">Failed: ${esc(String(res.data || 'Unknown error'))}</p>`;
      return;
    }
    if (!res.data || !res.data.items) {
      body.innerHTML = '<p style="color:var(--muted);padding:1rem;">No data returned.</p>';
      return;
    }

    const { total_gifts, total_issues, items } = res.data;

    if (!items.length) {
      body.innerHTML = `<p style="color:var(--success);padding:1rem;">No issues found across ${total_gifts} gifts. All good!</p>`;
      return;
    }

    // Group by issue type
    const groups = {};
    for (const item of items) {
      for (const issue of item.issues) {
        if (!groups[issue.type]) {
          const meta = ISSUE_META[issue.type] || { label: issue.type, order: 99 };
          groups[issue.type] = { meta, gifts: [] };
        }
        groups[issue.type].gifts.push({
          gift_id: item.gift_id, gift_name: item.gift_name,
          published: item.published, detail: issue.label,
        });
      }
    }

    const sortedGroups = Object.values(groups).sort((a, b) => a.meta.order - b.meta.order);

    let html = `<div style="padding:0.65rem 1rem;background:rgba(0,0,0,0.25);border-bottom:1px solid var(--border);
                font-size:0.82rem;color:var(--muted);">${total_issues} of ${total_gifts} gifts have issues</div>`;

    for (const group of sortedGroups) {
      html += `<div class="cga-qr-group">
        <div class="cga-qr-group-head" onclick="this.parentElement.classList.toggle('open')">
          <span class="cga-qr-badge">${group.gifts.length}</span>
          <span>${esc(group.meta.label)}</span>
          <span class="cga-qr-toggle">▾</span>
        </div>
        <div class="cga-qr-group-body">
          ${group.gifts.map(g => `
            <div class="cga-qr-row" data-id="${g.gift_id}">
              <span class="cga-qr-name">${esc(g.gift_name)}</span>
              ${!g.published ? '<span class="cga-qr-unpub">unpublished</span>' : ''}
              <span class="cga-qr-detail">${esc(g.detail)}</span>
            </div>`).join('')}
        </div>
      </div>`;
    }

    body.innerHTML = html;

    body.querySelectorAll('.cga-qr-row').forEach(row => {
      row.addEventListener('click', () => {
        closeQualityReport();
        if (pane !== 'ic-gifts') {
          pane = 'ic-gifts';
          document.getElementById('cga-pane-sel').value = 'ic-gifts';
          document.getElementById('cga-search').value = '';
          loadList().then(() => loadRecord(parseInt(row.dataset.id)));
          return;
        }
        loadRecord(parseInt(row.dataset.id));
      });
    });
  };

  window.closeQualityReport = function () {
    document.getElementById('cga-quality-modal').hidden = true;
  };

  // ── Escape helper ─────────────────────────────────────────────────────────
  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ── Batch @@ Fix ──────────────────────────────────────────────────────────

  let bfmProposals = [];

  window.openBatchFix = function () {
    bfmProposals = [];
    document.getElementById('cga-bfm-body').innerHTML =
      '<div class="cga-bfm-empty">Click "Scan Gifts" to find descriptions with misformatted @@PASSIVE: blocks.</div>';
    document.getElementById('cga-bfm-apply-btn').disabled = true;
    document.getElementById('cga-bfm-apply-btn').textContent = 'Apply Selected Fixes';
    document.getElementById('cga-batchfix-modal').classList.remove('hidden');
  };

  window.closeBatchFix = function () {
    document.getElementById('cga-batchfix-modal').classList.add('hidden');
  };

  window.scanBatchFix = async function () {
    const btn   = document.getElementById('cga-bfm-scan-btn');
    const after = document.getElementById('cga-bfm-after').value.trim();
    btn.disabled    = true;
    btn.textContent = 'Scanning…';
    document.getElementById('cga-bfm-body').innerHTML =
      '<div class="cga-bfm-empty">Scanning — this may take a moment…</div>';
    document.getElementById('cga-bfm-apply-btn').disabled = true;

    const r = await post('cg_admin_preview_batch_fix', after ? { after } : {});
    btn.disabled    = false;
    btn.textContent = 'Scan Gifts';

    if (!r.success) { status(r.data, 'err'); return; }

    bfmProposals = r.data;
    renderBfmProposals();
  };

  function renderBfmProposals() {
    const body = document.getElementById('cga-bfm-body');
    const applyBtn = document.getElementById('cga-bfm-apply-btn');

    if (!bfmProposals.length) {
      body.innerHTML = '<div class="cga-bfm-empty">No auto-fixable patterns found.</div>';
      applyBtn.disabled = true;
      return;
    }

    let html = '';
    bfmProposals.forEach((p, idx) => {
      const noteTxt = p.notes.join(' | ');
      html += `<div class="cga-bfm-item">
        <div class="cga-bfm-item-head">
          <input type="checkbox" id="bfm-chk-${idx}" checked data-idx="${idx}">
          <label class="cga-bfm-item-name" for="bfm-chk-${idx}">${esc(p.name)}</label>
          <span class="cga-bfm-item-note">${esc(noteTxt)}</span>
        </div>
        <div class="cga-bfm-diff">
          <div class="cga-bfm-diff-col">
            <div class="cga-bfm-diff-label">Before</div>
            <pre>${esc(p.orig)}</pre>
          </div>
          <div class="cga-bfm-diff-col">
            <div class="cga-bfm-diff-label">After</div>
            <pre>${esc(p.fixed)}</pre>
          </div>
        </div>
      </div>`;
    });

    body.innerHTML = html;
    applyBtn.disabled    = false;
    applyBtn.textContent = `Apply Selected Fixes (${bfmProposals.length})`;

    // Keep apply count in sync with checkbox state
    body.querySelectorAll('input[type=checkbox]').forEach(chk => {
      chk.addEventListener('change', updateApplyCount);
    });
  }

  function updateApplyCount() {
    const checked = document.querySelectorAll('#cga-bfm-body input[type=checkbox]:checked').length;
    const applyBtn = document.getElementById('cga-bfm-apply-btn');
    applyBtn.disabled    = checked === 0;
    applyBtn.textContent = `Apply Selected Fixes (${checked})`;
  }

  window.applyBatchFix = async function () {
    const checkedIdx = [...document.querySelectorAll('#cga-bfm-body input[type=checkbox]:checked')]
      .map(el => parseInt(el.dataset.idx));
    if (!checkedIdx.length) return;

    const patches = checkedIdx.map(i => ({ id: bfmProposals[i].id, fixed: bfmProposals[i].fixed }));
    const btn     = document.getElementById('cga-bfm-apply-btn');
    btn.disabled    = true;
    btn.textContent = `Applying ${patches.length} fix(es)…`;

    const r = await post('cg_admin_apply_batch_fix', { patches: JSON.stringify(patches) });
    if (!r.success) { status(r.data, 'err'); btn.disabled = false; return; }

    status(r.data, 'ok');
    closeBatchFix();

    // If the currently-loaded record was one of the fixed gifts, reload it
    if (currentId && patches.some(p => p.id === currentId)) loadRecord(currentId);
  };

  // ── Urban Jungle install ──────────────────────────────────────────────────

  window.runUjInstall = async function (btn) {
    if (!confirm('This will CREATE the UJ trait tables (if not exists) and UPSERT all Species, Type, and Career trait data from the book. Continue?')) return;
    btn.disabled    = true;
    btn.textContent = 'Installing…';
    const r1 = await post('uj_install_tables');
    if (!r1.success) { status(r1.data, 'err'); btn.disabled = false; btn.textContent = 'UJ: Install Data'; return; }
    const r2 = await post('uj_install_data');
    btn.disabled    = false;
    btn.textContent = 'UJ: Install Data';
    if (!r2.success) { status(r2.data, 'err'); return; }
    status(r2.data, 'ok');
  };

  // ── Boot ──────────────────────────────────────────────────────────────────
  (async () => {
    const sp        = new URLSearchParams(location.search);
    const urlPane   = sp.get('pane');
    const urlSlug   = sp.get('slug');
    const urlAction = sp.get('action');

    if (urlPane && (SIMPLE_PANES[urlPane] || urlPane === 'ic-gifts' || urlPane === 'ic-weapons')) {
      pane = urlPane;
      document.getElementById('cga-pane-sel').value = pane;
    }

    await loadList();

    if (urlAction === 'new') {
      showNewForm();
    } else if (urlSlug && allItems.length) {
      const target = allItems.find(i => (i.slug || i.ct_slug) === urlSlug);
      if (target) loadRecord(getItemId(target));
    }
  })();
})();
</script>

<div id="cga-batchfix-modal" class="hidden">
  <div class="cga-bfm-box">
    <div class="cga-bfm-head">
      <span>Batch @@ Fix</span>
      <input id="cga-bfm-after" type="text" placeholder="Start after… (e.g. Death Watch — leave blank for all)">
      <button class="cga-tool-btn" id="cga-bfm-scan-btn" onclick="scanBatchFix()">Scan Gifts</button>
      <button class="cga-tool-btn" onclick="closeBatchFix()">✕</button>
    </div>
    <div class="cga-bfm-body" id="cga-bfm-body">
      <div class="cga-bfm-empty">Click "Scan Gifts" to find descriptions with misformatted @@PASSIVE: blocks.</div>
    </div>
    <div class="cga-bfm-foot">
      <span style="color:var(--muted);font-size:0.78rem;flex:1;">
        Only @@PASSIVE: blocks containing an explicit "Requires" heading are auto-fixed.
        Other issues are shown in the per-gift @@ preview and must be corrected manually.
      </span>
      <button class="cga-tool-btn" id="cga-bfm-apply-btn" disabled onclick="applyBatchFix()">Apply Selected Fixes</button>
    </div>
  </div>
</div>
</body>
</html>
