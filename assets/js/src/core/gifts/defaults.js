// assets/js/src/core/gifts/defaults.js
//
// Renders the always-acquired default gifts:
//   - Local Knowledge  (#cg-local-knowledge) — gift name + region text input + effect
//   - Combat Save      (#cg-combat-save)      — gift name + effect
//   - Personality      (#cg-personality)      — dropdown from cg_personality table

import FormBuilderAPI from '../formBuilder';

function cgWin() {
  if (typeof globalThis !== 'undefined') return globalThis;
  if (typeof window !== 'undefined') return window;
  return {};
}
const W = cgWin();

function ajaxUrl() {
  const base = (typeof W.CG_API_BASE === 'string' && W.CG_API_BASE)
    ? W.CG_API_BASE.replace(/\/+$/, '') : '';
  if (base) return base + '/api/ajax';
  const env = W.CG_AJAX || W.CG_Ajax || {};
  return env.ajax_url || W.ajaxurl || '/api/ajax';
}

function postJSON(url, data) {
  return fetch(url, {
    method:      'POST',
    credentials: 'include',
    headers:     { 'Content-Type': 'application/json' },
    body:        JSON.stringify(data),
  }).then(r => r.json()).catch(() => null);
}

function safeHtml(s) {
  return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function effectDescHtml(desc) {
  if (!desc) return '';
  const clean = String(desc).replace(/@@\w+[:\s]*/g, ' ').replace(/\s+/g, ' ').trim();
  const short  = clean.length > 220 ? clean.slice(0, 217) + '…' : clean;
  return `<div class="cg-default-gift-effect">${safeHtml(short)}</div>`;
}

function getData() {
  if (FormBuilderAPI && FormBuilderAPI._data) return FormBuilderAPI._data;
  if (typeof FormBuilderAPI?.getData === 'function') return FormBuilderAPI.getData() || {};
  return {};
}

function setKey(key, value) {
  const d = getData();
  if (FormBuilderAPI && FormBuilderAPI._data) {
    FormBuilderAPI._data[key] = value;
  } else {
    d[key] = value;
  }
  try {
    document.dispatchEvent(new CustomEvent('cg:defaults:changed', { detail: { key, value } }));
  } catch (_) {}
}

// ── Local Knowledge ────────────────────────────────────────────────────────────

let _lkGift = null;

async function fetchLKGift() {
  if (_lkGift) return _lkGift;
  const res = await postJSON(ajaxUrl(), { action: 'cg_get_local_knowledge' });
  if (res && res.success && res.data) _lkGift = res.data;
  return _lkGift;
}

function renderLK(host) {
  if (!host) return;
  const data = getData();
  const regionVal = safeHtml(String(data.local_knowledge_region || ''));
  const gift = _lkGift;
  const name = gift ? safeHtml(String(gift.name || 'Local Knowledge')) : 'Local Knowledge';
  const desc = gift ? effectDescHtml(gift.effect_description || '') : '';

  host.innerHTML = `
    <div class="cg-default-gift-row">
      <span class="cg-default-gift-name">${name}</span>
      <input
        type="text"
        id="cg-local-knowledge-region"
        class="cg-default-gift-text"
        placeholder="Region / area of knowledge…"
        value="${regionVal}"
        maxlength="120"
        title="Specify your character's area of local knowledge"
      />
    </div>
    ${desc}
  `;

  const inp = host.querySelector('#cg-local-knowledge-region');
  if (inp) {
    inp.addEventListener('input', (e) => {
      setKey('local_knowledge_region', String(e.target.value || '').trim());
    });
  }
}

// ── Combat Save ────────────────────────────────────────────────────────────────

let _csGift = null;

async function fetchCombatSave() {
  if (_csGift) return _csGift;
  const res = await postJSON(ajaxUrl(), { action: 'cg_get_combat_save' });
  if (res && res.success && res.data) _csGift = res.data;
  return _csGift;
}

function renderCombatSave(host) {
  if (!host) return;
  const gift = _csGift;
  const name = gift ? safeHtml(String(gift.name || 'Combat Save')) : 'Combat Save';
  const desc = gift ? effectDescHtml(gift.effect_description || '') : '';

  host.innerHTML = `
    <div class="cg-default-gift-row">
      <span class="cg-default-gift-name">${name}</span>
    </div>
    ${desc}
  `;
}

// ── Personality ────────────────────────────────────────────────────────────────

let _personalityList = null;

async function fetchPersonalityList() {
  if (_personalityList) return _personalityList;
  const res = await postJSON(ajaxUrl(), { action: 'cg_get_personality_list' });
  if (res && res.success && Array.isArray(res.data)) _personalityList = res.data;
  return _personalityList || [];
}

function renderPersonality(host) {
  if (!host) return;
  const data = getData();
  const cur  = String(data.personality_trait || '');
  const list = _personalityList || [];

  const opts = list.map(name => {
    const safe = safeHtml(name);
    const sel  = (safe === safeHtml(cur)) ? ' selected' : '';
    return `<option value="${safe}"${sel}>${safe}</option>`;
  }).join('');

  host.innerHTML = `
    <select id="cg-personality-select" class="cg-free-select" style="max-width:300px;">
      <option value="">— Select personality trait —</option>
      ${opts}
    </select>
  `;

  const sel = host.querySelector('#cg-personality-select');
  if (sel) {
    sel.addEventListener('change', (e) => {
      setKey('personality_trait', String(e.target.value || '').trim());
    });
  }
}

// ── Main API ──────────────────────────────────────────────────────────────────

let _inited = false;

async function init() {
  if (_inited) { return render(); }
  _inited = true;

  await Promise.all([fetchLKGift(), fetchCombatSave(), fetchPersonalityList()]);
  render();
}

function render() {
  const modal = document.getElementById('cg-modal');
  if (!modal) return;

  const lkHost = modal.querySelector('#cg-local-knowledge');
  const csHost = modal.querySelector('#cg-combat-save');
  const pHost  = modal.querySelector('#cg-personality');

  if (lkHost) renderLK(lkHost);
  if (csHost) renderCombatSave(csHost);
  if (pHost)  renderPersonality(pHost);
}

const GiftsDefaults = { init, render };

if (typeof W !== 'undefined') W.CG_GiftsDefaults = GiftsDefaults;
export default GiftsDefaults;
