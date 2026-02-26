// assets/js/src/core/gifts/free-choices.js
//
// Renders the "Chosen" Free Gifts (3) selects inside the Gifts tab placeholder #cg-free-choices.
//
// Requirements:
// - Filtering MUST work even before choosing career/species (only depends on currently-owned gifts + traits).
// - Filtering MUST respect prerequisite gifts in ct_gifts_requires* columns.
// - Filtering MUST respect minimum trait requirements parsed from requires_special text (e.g. "Mind of d8 or better").
// - Slot-scoped qualification selectors (Language/Literacy/Insider/Mystic/Piety) are rendered INLINE under each slot,
//   driven by requires_special lines like "Piety: ..." / "Mystic: ..." etc.
// - Base "everyone gets one Language" selector is handled by quals/ui.js; we do NOT render that here.
//
// Exposes: window.CG_FreeChoices

import FormBuilderAPI from '../formBuilder';
import State from './state.js';
import Quals from '../quals/index.js';
import QualState from '../quals/state.js';

function cgWin() {
  if (typeof globalThis !== 'undefined') return globalThis;
  if (typeof window !== 'undefined') return window;
  return {};
}

const W = cgWin();
const $ = (W && W.jQuery) ? W.jQuery : null;

const log = (...a) => { try { console.log('[FreeChoices]', ...a); } catch (_) {} };
const warn = (...a) => { try { console.warn('[FreeChoices]', ...a); } catch (_) {} };

const ALWAYS_ACQUIRED_GIFT_IDS = ['242', '236']; // Local Knowledge, Language (always acquired in your model)

function normalize3(arr) {
  const out = (Array.isArray(arr) ? arr : []).slice(0, 3).map(v => (v ? String(v) : ''));
  while (out.length < 3) out.push('');
  return out;
}

function getBuilderData() {
  if (FormBuilderAPI && FormBuilderAPI._data) return FormBuilderAPI._data;
  if (typeof FormBuilderAPI?.getData === 'function') return FormBuilderAPI.getData() || {};
  return {};
}

function setBuilderKey(key, value) {
  const d = getBuilderData();
  try {
    if (FormBuilderAPI && FormBuilderAPI._data) FormBuilderAPI._data[key] = value;
    else d[key] = value;
  } catch (_) {}
}

function getAjax() {
  return W.CG_AJAX || W.CG_Ajax || {};
}

function getNonceFor(action) {
  const nonces = W.CG_NONCES || {};
  if (nonces && nonces[action]) return nonces[action];
  const ajax = getAjax();
  if (ajax && ajax.nonce) return ajax.nonce;
  if (ajax && ajax.security) return ajax.security;
  return '';
}

function ajaxPost(payload) {
  const ajax = getAjax();
  const url = ajax.ajax_url || ajax.url || W.ajaxurl || '';
  const data = { ...(payload || {}) };

  const nonce = getNonceFor(String(data.action || ''));
  if (nonce && !data.security && !data.nonce && !data._ajax_nonce) {
    data.security = nonce;
    data.nonce = nonce;
    data._ajax_nonce = nonce;
  }

  if ($ && url) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url,
        method: 'POST',
        data,
        dataType: 'json',
        success: (res) => resolve(res),
        error: (xhr, status, err) => reject(err || status || xhr)
      });
    });
  }

  if (!url) return Promise.reject(new Error('Missing AJAX url'));
  const body = new URLSearchParams();
  Object.entries(data).forEach(([k, v]) => body.append(k, String(v ?? '')));

  return fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body
  }).then(r => r.json());
}

function modalRoot() {
  if (typeof document === 'undefined') return null;
  return document.querySelector('#cg-modal') || null;
}

function getFreeGiftSlotsFromData() {
  try {
    if (State && typeof State.getFreeGifts === 'function') return normalize3(State.getFreeGifts());
  } catch (_) {}

  const d = getBuilderData();
  const v =
    d.free_gifts ||
    d.freeGifts ||
    d.cg_free_gifts ||
    d.free_choices ||
    d.freeChoices ||
    [];

  return normalize3(Array.isArray(v) ? v : [v]);
}

function emitFreeGiftChanged(selected, source = 'free-choices') {
  try {
    const detail = { free_gifts: selected.slice(), source: String(source || '') };
    document.dispatchEvent(new CustomEvent('cg:free-gift:changed', { detail }));
    if ($) $(document).trigger('cg:free-gift:changed', [detail]);
  } catch (_) {}
}

function setFreeGiftSlotsToData(slots, source = 'free-choices') {
  const normalized = normalize3(slots);

  setBuilderKey('free_gifts', normalized);
  setBuilderKey('freeGifts', normalized);
  setBuilderKey('cg_free_gifts', normalized);

  let stateHandled = false;
  try {
    if (State && typeof State.setSelected === 'function') {
      State.setSelected(normalized);
      stateHandled = true;
    } else if (State && typeof State.setFreeGifts === 'function') {
      State.setFreeGifts(normalized, { source });
      stateHandled = true;
    }
  } catch (_) {}

  if (!stateHandled) emitFreeGiftChanged(normalized, source);

  return normalized;
}

function giftId(g) {
  return String(g?.id ?? g?.ct_id ?? g?.gift_id ?? g?.ct_gift_id ?? '');
}

function giftName(g) {
  return String(g?.name ?? g?.title ?? g?.gift_name ?? g?.ct_gift_name ?? '');
}

function requiresSpecialText(g) {
  const rs = (g?.requires_special ?? g?.ct_gifts_requires_special ?? g?.requiresSpecial ?? '');
  return String(rs ?? '').trim();
}

function allowsMultiple(g) {
  // Gift 223 (Increased Trait: Career) is always allowed multiple times so each
  // free-choice copy can independently target a different extra career.
  if (g && (String(g.id || '') === '223' || String(g.ct_id || '') === '223')) return true;

  const v = g?.allows_multiple ?? g?.ct_gifts_manifold ?? g?.manifold ?? null;
  if (v === true) return true;
  const n = Number(v);
  return Number.isFinite(n) && n > 1;
}

function extractRequiredGiftIds(g) {
  if (!g || typeof g !== 'object') return [];
  const out = [];
  Object.keys(g).forEach(k => {
    if (!/^ct_gifts_requires(_[a-z]+)?$/i.test(k) && !/^ct_gifts_requires_/i.test(k)) return;
    if (k.toLowerCase() === 'ct_gifts_requires_special') return;
    const v = g[k];
    if (v == null) return;
    const s = String(v).trim();
    if (!s) return;
    s.split(',').map(x => x.trim()).filter(Boolean).forEach(x => out.push(String(x)));
  });
  return [...new Set(out)];
}

function diceToNum(s) {
  const t = String(s || '').trim().toLowerCase();
  const m = t.match(/d\s*(4|6|8|10|12)/);
  return m ? Number(m[1]) : null;
}

function getTraitDieValue(traitKey) {
  const d = getBuilderData();

  const pools = [];
  if (d && typeof d === 'object') {
    if (d.traits && typeof d.traits === 'object') pools.push(d.traits);
    if (d.cg_traits && typeof d.cg_traits === 'object') pools.push(d.cg_traits);
    pools.push(d);
  }

  const keys = [traitKey, `trait_${traitKey}`, `${traitKey}_die`, `die_${traitKey}`];

  for (const obj of pools) {
    for (const k of keys) {
      if (!obj || !(k in obj)) continue;
      const v = obj[k];
      const n = diceToNum(v);
      if (n != null) return n;
    }
  }

  return null;
}

function extractTraitMinimaFromRequiresSpecial(rs) {
  const text = String(rs || '');
  if (!text) return [];

  const traits = [
    { key: 'mind',    re: /\bmind\b/i },
    { key: 'body',    re: /\bbody\b/i },
    { key: 'speed',   re: /\bspeed\b/i },
    { key: 'will',    re: /\bwill\b/i },
    { key: 'species', re: /\bspecies\b/i },
  ];

  const mins = [];
  const parts = text.split(/[\n\r.;]+/).map(s => s.trim()).filter(Boolean);

  parts.forEach(p => {
    traits.forEach(t => {
      if (!t.re.test(p)) return;
      const m = p.match(/d\s*(4|6|8|10|12)/i);
      if (!m) return;
      const need = Number(m[1]);
      if (!Number.isFinite(need)) return;
      mins.push({ traitKey: t.key, need, raw: p });
    });
  });

  const best = new Map();
  mins.forEach(x => {
    const cur = best.get(x.traitKey);
    if (!cur || x.need > cur.need) best.set(x.traitKey, x);
  });

  return Array.from(best.values());
}

function traitMinimaSatisfied(g) {
  const rs = requiresSpecialText(g);
  const mins = extractTraitMinimaFromRequiresSpecial(rs);
  if (!mins.length) return true;

  for (const m of mins) {
    const have = getTraitDieValue(m.traitKey);
    if (have == null) return false;
    if (have < m.need) return false;
  }
  return true;
}

/**
 * Handles comparative trait requirements, e.g.:
 *   "Speed must be greater than Body"
 *   "Species Trait must be higher than your Mind Trait"
 */
function comparativeTraitsSatisfied(g) {
  const rs = requiresSpecialText(g);
  if (!rs) return true;

  const TRAIT_NAMES = ['mind', 'body', 'speed', 'will', 'species'];

  const lines = rs.split(/[\n\r.;]+/).map(s => s.trim()).filter(Boolean);
  for (const line of lines) {
    // Match "X must be greater than Y" / "X must be higher than Y"
    const m = line.match(/\b(mind|body|speed|will|species)[^\w]*(trait)?\s+must\s+be\s+(greater|higher)\s+than\s+(your\s+)?(mind|body|speed|will|species)/i);
    if (!m) continue;
    const leftKey  = m[1].toLowerCase();
    const rightKey = m[5].toLowerCase();
    const leftVal  = getTraitDieValue(leftKey);
    const rightVal = getTraitDieValue(rightKey);
    // If either trait is unknown, don't block (give benefit of the doubt)
    if (leftVal == null || rightVal == null) continue;
    if (leftVal <= rightVal) return false;
  }
  return true;
}

function extractQualReqLinesFromRequiresSpecial(rs) {
  const lines = String(rs || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
  const out = [];
  for (const line of lines) {
    const m = line.match(/^(language|literacy|insider|mystic|piety|ordainment)\s*:\s*(.+)$/i);
    if (!m || !m[1] || !m[2]) continue;
    out.push({ type: String(m[1]).toLowerCase(), raw: String(m[2]).trim() });
  }
  return out;
}

/**
 * Enforce simple qualification prerequisites expressed in requires_special, e.g.:
 *   "Language: Magniloquentia"
 *   "Mystic: Elementalism"
 *
 * We ONLY enforce explicit "Type: Value" lines for known types.
 * Anything else in requires_special is ignored here (future work).
 */
function qualPrereqsSatisfied(g) {
  const rs = requiresSpecialText(g);
  if (!rs) return true;

  // Non-enforceable GM permission text
  if (/permission\s+from\s+the\s+game\s+host/i.test(rs)) return true;

  const reqs = extractQualReqLinesFromRequiresSpecial(rs);
  if (!reqs.length) return true;

  try { ensureQualStateInit(); } catch (_) {}

  for (const r of reqs) {
    const type = r.type;
    const raw = String(r.raw || '').trim();
    if (!type || !raw) continue;

    // Ambiguous placeholders: do not block eligibility on these
    if (/\[\s*choice\s*\]/i.test(raw) || /\bchoice\b/i.test(raw)) continue;

    const parts = raw
      .split(/,|\/|;|\bor\b/ig)
      .map(s => String(s || '').trim())
      .filter(Boolean);

    if (!parts.length) continue;

    let ok = false;
    for (const p of parts) {
      if (!p) continue;
      if (/^any\b/i.test(p)) { ok = true; break; }
      if (qualStateHas(type, p)) { ok = true; break; }
    }
    if (!ok) return false;
  }

  return true;
}


function computeOwnedGiftIdSet(selectedFreeGiftIds = []) {
  const owned = new Set();
  ALWAYS_ACQUIRED_GIFT_IDS.forEach(id => owned.add(String(id)));
  (selectedFreeGiftIds || []).filter(Boolean).forEach(id => owned.add(String(id)));

  const d = getBuilderData();
  const candidateKeys = [
    'gift_id_1','gift_id_2','gift_id_3',
    'species_gift_one','species_gift_two','species_gift_three',
    'career_gift_one','career_gift_two','career_gift_three',
    'species_gifts','career_gifts',
    'gift_ids','giftIds','gifts',
      'career_gift_replacements',
      'cg_career_gift_replacements',
  ];

  candidateKeys.forEach(k => {
    if (!d || !(k in d)) return;
    const v = d[k];
    if (Array.isArray(v)) v.forEach(x => x && owned.add(String(x)));
    else if (v && typeof v === 'object') Object.values(v).forEach(x => x && owned.add(String(x)));
    else if (v) owned.add(String(v));
  });

  const apis = [W.SpeciesAPI, W.CareerAPI].filter(Boolean);
  apis.forEach(api => {
    const prof = api?.currentProfile || api?.profile || api?.current || api?.selected || null;
    if (!prof) return;
    candidateKeys.forEach(k => {
      if (!(k in prof)) return;
      const v = prof[k];
      if (Array.isArray(v)) v.forEach(x => x && owned.add(String(x)));
      else if (v && typeof v === 'object') Object.values(v).forEach(x => x && owned.add(String(x)));
      else if (v) owned.add(String(v));
    });
  });

  return owned;
}

function giftEligible(g, ownedSet, otherSelectedIds = new Set()) {
  if (!g) return false;
  const id = giftId(g);
  const name = giftName(g);
      const __DBG471 = !!(W && W.__CG_DBG471__) && (String(id) === '471' || String(name).toLowerCase() === 'attendant fireball');
    const __owned = (typeof ownedSet !== 'undefined') ? ownedSet : ((typeof owned !== 'undefined') ? owned : null);
    const __others = (typeof otherSelectedIds !== 'undefined') ? otherSelectedIds : ((typeof others !== 'undefined') ? others : null);
    if (__DBG471) {
      console.log('[FreeChoices][DBG471] start', { id, name });
      try { console.log('[FreeChoices][DBG471] owned', __owned ? Array.from(__owned) : __owned); } catch (_) {}
      try { console.log('[FreeChoices][DBG471] others', __others ? Array.from(__others) : __others); } catch (_) {}
      console.log('[FreeChoices][DBG471] fields', {
        requires: g && g.requires,
        ct_gifts_requires: g && g.ct_gifts_requires,
        manifold: g && g.manifold,
        ct_gifts_manifold: g && g.ct_gifts_manifold,
        allows_multiple: g && g.allows_multiple,
        ct_gifts_allows_multiple: g && g.ct_gifts_allows_multiple,
        requires_special: g && (g.requires_special || g.ct_gifts_requires_special || g.requiresSpecial)
      });
    }
if (!id || !name) { if (__DBG471) console.log('[FreeChoices][DBG471] FAIL: missing id/name', {id, name}); return false; }

  const reqIds = extractRequiredGiftIds(g);
  for (const rid of reqIds) {
    if (!ownedSet.has(String(rid))) return false;
  }

  if (!traitMinimaSatisfied(g)) { if (__DBG471) { try { const rs = requiresSpecialText(g); const mins = extractTraitMinimaFromRequiresSpecial(rs); const tv = {}; mins.forEach(t => tv[t.trait] = getTraitDieValue(t.trait)); console.log('[FreeChoices][DBG471] FAIL: trait minima', { rs, mins, tv }); } catch (_) {} } return false; }

  if (!comparativeTraitsSatisfied(g)) return false;

  if (!qualPrereqsSatisfied(g)) return false;

  if (otherSelectedIds.has(id) && !allowsMultiple(g)) return false;

  return true;
}

function ensureSectionInHost(host) {
  if (typeof document === 'undefined') return null;

  let section = document.getElementById('cg-free-gifts');
  if (!section) {
    section = document.createElement('div');
    section.id = 'cg-free-gifts';
    section.className = 'cg-free-gifts';
    section.innerHTML = `
      <div class="cg-free-row" style="display:flex; flex-direction:column; gap:6px;"></div>
    `;
  }

  try {
    if (host && section.parentNode !== host) host.appendChild(section);
  } catch (_) {}

  return section;
}

function getSlotQualMap() {
  const d = getBuilderData();
  const m = d.free_gift_quals || d.cg_free_gift_quals || d.freeGiftQuals || {};
  if (!m || typeof m !== 'object') return {};
  return JSON.parse(JSON.stringify(m));
}

function setSlotQualMap(map) {
  const clean = map && typeof map === 'object' ? map : {};
  setBuilderKey('free_gift_quals', clean);
  setBuilderKey('cg_free_gift_quals', clean);
  setBuilderKey('freeGiftQuals', clean);
}

function stripDiacritics(s) {
  return String(s || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

function canon(s) {
  return stripDiacritics(String(s || ''))
    .trim()
    .replace(/\s+/g, ' ')
    .toLowerCase();
}

function detectQualTypesNeeded(g) {
  const rs = requiresSpecialText(g);
  const name = giftName(g).toLowerCase();

  const found = new Set();
  const lines = String(rs || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
  lines.forEach(line => {
    const m = line.match(/^(language|literacy|insider|mystic|piety)\s*:/i);
    if (m && m[1]) found.add(String(m[1]).toLowerCase());
  });

  if (found.size === 0) {
    if (name.includes('piety')) found.add('piety');
    if (name.includes('mystic')) found.add('mystic');
    if (name.includes('insider')) found.add('insider');
    if (name.includes('literacy')) found.add('literacy');
    if (name.includes('language')) found.add('language');
  }

  const TYPES = ['language', 'literacy', 'insider', 'mystic', 'piety'];
  return TYPES.filter(t => found.has(t));
}

/**
 * IMPORTANT:
 * Primary source is Quals.get(type), BUT that catalog can be empty early in the session.
 * Fallback: derive possible values directly from the gifts list by scanning requires_special lines.
 */
function getQualItemsForType(type, allGifts) {
  let items = [];
  try {
    items = (Quals && typeof Quals.get === 'function') ? (Quals.get(type) || []) : [];
  } catch (_) {
    items = [];
  }

  if (Array.isArray(items) && items.length > 0) return items;

  // Fallback: parse "Type: Value" lines across ALL gifts
  const want = String(type || '').toLowerCase();
  const rx = new RegExp(`^\\s*${want}\\s*:\\s*(.+)$`, 'i');

  const keep = new Map(); // canon -> bestLabel

  const list = Array.isArray(allGifts) ? allGifts : [];
  for (const g of list) {
    const rs = requiresSpecialText(g);
    if (!rs) continue;

    const lines = rs.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
    for (const line of lines) {
      const m = line.match(rx);
      if (!m || !m[1]) continue;

      // Value part may contain multiple entries separated by commas or " or "
      const raw = String(m[1]).trim();
      const parts = raw
        .split(/,|\/|;|\bor\b/ig)
        .map(s => String(s || '').trim())
        .filter(Boolean);

      for (const p of parts) {
        // Avoid obvious non-values
        if (/^any\b/i.test(p)) continue;
        const k = canon(p);
        if (!k) continue;
        if (!keep.has(k)) keep.set(k, p);
      }
    }
  }

  const labels = Array.from(keep.values())
    .sort((a, b) => String(a).localeCompare(String(b), undefined, { sensitivity: 'base' }));

  // Normalize to the same shape Quals.get typically returns
  return labels.map(label => ({ label, key: label }));
}

function ensureQualStateInit() {
  try { QualState?.init?.(); } catch (_) {}
}

function qualStateHas(type, value) {
  type = String(type || '').toLowerCase();

  if (!value) return false;
  try {
    if (typeof QualState?.has === 'function') return !!QualState.has(type, value);
    const arr = QualState?.get?.(type) || [];
    return arr.some(v => canon(v) === canon(value));
  } catch (_) {
    return false;
  }
}

function qualStateAdd(type, value) {
  if (!value) return;
  ensureQualStateInit();

  if (type === 'language') {
    try {
      const cur = (QualState?.get?.('language') || []).slice();
      const base = cur[0] || '';
      if (canon(base) === canon(value)) return;
      if (qualStateHas('language', value)) return;

      if (QualState?.data && Array.isArray(QualState.data.language) && typeof QualState.persist === 'function') {
        QualState.data.language = [base, ...cur.slice(1).filter(v => canon(v) !== canon(value)), value].filter(Boolean);
        QualState.persist();
      } else if (typeof QualState?.add === 'function') {
        QualState.add('language', value);
      }
    } catch (_) {}
    return;
  }

  try {
    if (qualStateHas(type, value)) return;
    if (typeof QualState?.add === 'function') QualState.add(type, value);
    else if (QualState?.data && Array.isArray(QualState.data[type]) && typeof QualState.persist === 'function') {
      QualState.data[type] = (QualState.data[type] || []).concat([value]);
      QualState.persist();
    }
  } catch (_) {}
}

function qualStateRemoveIfSafe(type, value, slotMap) {
  if (!value) return;
  ensureQualStateInit();

  const stillUsed = Object.values(slotMap || {}).some(slotObj => {
    if (!slotObj || typeof slotObj !== 'object') return false;
    const v = slotObj[type];
    return v && canon(v) === canon(value);
  });
  if (stillUsed) return;

  if (type === 'language') {
    try {
      const cur = (QualState?.get?.('language') || []).slice();
      const base = cur[0] || '';
      if (canon(base) === canon(value)) return;
    } catch (_) {}
  }

  try {
    if (typeof QualState?.remove === 'function') QualState.remove(type, value);
    else if (QualState?.data && Array.isArray(QualState.data[type]) && typeof QualState.persist === 'function') {
      QualState.data[type] = (QualState.data[type] || []).filter(v => canon(v) !== canon(value));
      QualState.persist();
    }
  } catch (_) {}
}

function renderQualSelectHtml({ slot, type, value, allGifts }) {
  const items = getQualItemsForType(type, allGifts);
  const opts = (Array.isArray(items) ? items : []).map(it => {
    const label = String(it?.label ?? it?.key ?? '').trim();
    if (!label) return '';
    const sel = (canon(value) === canon(label)) ? ' selected' : '';
    return `<option value="${label}"${sel}>${label}</option>`;
  }).filter(Boolean).join('\n');

  const nice = type.charAt(0).toUpperCase() + type.slice(1);

  return `
    <label style="display:flex; flex-direction:column; gap:4px; margin-top:8px; min-width: 220px;">
      <span style="font-weight:600;">${nice}</span>
      <select class="cg-free-qual-select" data-slot="${slot}" data-qtype="${type}">
        <option value="">— Select ${nice} —</option>
        ${opts}
      </select>
    </label>
  `;
}

// Reuse singleton if module is evaluated twice
const Existing = W.CG_FreeChoices;

const FreeChoices = (Existing && Existing.__cg_singleton) ? Existing : {
  __cg_singleton: true,

  _inited: false,
  _bound: false,

  _allGifts: [],
  _byId: null,
  _loading: false,


    _renderQueued: false,
    _renderRunning: false,
    _renderPending: false,

    _scheduleRender(reason = 'auto') {
      // Coalesce bursts: at most one render per tick, plus one pending pass.
      if (this._renderRunning) { this._renderPending = true; return; }
      if (this._renderQueued) return;
      this._renderQueued = true;

      const run = () => {
        this._renderQueued = false;
        this._renderRunning = true;

        try {
          this.render();
        } catch (e) {
          try { console.error('[FreeChoices] render failed', e); } catch (_) {}
        } finally {
          this._renderRunning = false;

          if (this._renderPending) {
            this._renderPending = false;
            this._scheduleRender('pending');
          }
        }
      };

      if (typeof requestAnimationFrame === 'function') requestAnimationFrame(run);
      else setTimeout(run, 0);
    },
  init() {
    if (this.__inited) return;
    this.__inited = true;
    if (this._inited) return;
    this._inited = true;

    try { State?.init?.(); } catch (_) {}
    try { QualState?.init?.(); } catch (_) {}
    try { Quals?.init?.(); } catch (_) {} // best-effort if Quals has an init

    this._bindGlobalEventsOnce();
    this.refresh({ reason: 'init', fetch: true });
  },

  _bindGlobalEventsOnce() {
    if (this._bound) return;
    this._bound = true;

    const rerenderSoon = this._rerenderSoon || ((e) => { this._scheduleRender((e && e.type) ? e.type : 'event'); });
    this._rerenderSoon = rerenderSoon;


    // Prefer jQuery binding when available to avoid double-firing (jQuery also sees CustomEvent)
    if ($ && $.fn) {
      $(document)
        .off('cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices')
        .on('cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices', rerenderSoon);
      return;
    }

    document.removeEventListener('cg:builder:opened', rerenderSoon);
    document.addEventListener('cg:builder:opened', rerenderSoon);

    try {
      const W = window;
      W.__CG_EVT__ = W.__CG_EVT__ || {};
      const EVT = W.__CG_EVT__;
      if (EVT.freeChoicesRerenderSoon) {
        try { document.removeEventListener('cg:tab:changed', EVT.freeChoicesRerenderSoon); } catch (_) {}
      }
      EVT.freeChoicesRerenderSoon = rerenderSoon;
      document.addEventListener('cg:tab:changed', EVT.freeChoicesRerenderSoon);
    } catch (_) {
      try { document.addEventListener('cg:tab:changed', rerenderSoon); } catch (_) {}
    }
    document.removeEventListener('cg:character:loaded', rerenderSoon);
    document.addEventListener('cg:character:loaded', rerenderSoon);

    document.removeEventListener('cg:quals:catalog-updated', rerenderSoon);
    document.addEventListener('cg:quals:catalog-updated', rerenderSoon);

    if ($ && $.fn) {
      $(document)
        .off('cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices')
        .on('cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices', rerenderSoon);
    }
      // Removed legacy tab-click sniffing: cg:tab:changed is the canonical trigger.
  },

  async refresh({ fetch = false } = {}) {
    if (fetch) await this._fetchAllGifts();
      this._scheduleRender('refresh');
},

  async _fetchAllGifts() {
    if (this._loading) return;
    this._loading = true;

    try {
      const res = await ajaxPost({ action: 'cg_get_free_gifts' });

      let list = null;
      if (Array.isArray(res)) list = res;
      else if (res && Array.isArray(res.data)) list = res.data;
      else if (res && res.data && Array.isArray(res.data.gifts)) list = res.data.gifts;
      else if (res && Array.isArray(res.gifts)) list = res.gifts;

      if (!Array.isArray(list)) {
        warn('cg_get_free_gifts returned unexpected payload', res);
        list = [];
      }

      this._allGifts = list.slice();
      this._byId = new Map();
      this._allGifts.forEach(g => {
        const id = giftId(g);
        if (id) this._byId.set(id, g);
      });

      // Hydrate shared GiftsState so TraitsService/Quals can see gift rows
      try { State.setList(this._allGifts); } catch (_) {}

      log('Fetched gifts', { count: this._allGifts.length });
    } catch (err) {
      warn('Failed to fetch gifts', err);
      this._allGifts = [];
      this._byId = new Map();
    } finally {
      this._loading = false;
    }
  },

  _getGift(id) {
    const k = String(id || '');
    if (!k) return null;
    if (this._byId && this._byId.get(k)) return this._byId.get(k);
    const list = Array.isArray(this._allGifts) ? this._allGifts : [];
    return list.find(g => giftId(g) === k) || null;
  },

  render() {
    const modal = modalRoot();
    if (!modal) return;

    const host = modal.querySelector('#cg-free-choices') || document.querySelector('#cg-free-choices');
    if (!host) return;

    const section = ensureSectionInHost(host);
    if (!section) return;

    this._bindSectionDelegates(section);

    const slots = getFreeGiftSlotsFromData();
    const row = section.querySelector('.cg-free-row');
    if (!row) return;

    const owned = computeOwnedGiftIdSet(slots.filter(Boolean));

    const htmlSlots = [0, 1, 2].map(i => {
      const selectedId = String(slots[i] || '').trim();
      const others = new Set(slots.map((v, idx) => (idx === i ? '' : String(v || '').trim())).filter(Boolean));

      const eligible = (Array.isArray(this._allGifts) ? this._allGifts : []).filter(g => giftEligible(g, owned, others));

      const curGift = selectedId ? this._getGift(selectedId) : null;
      const curOpt = curGift ? [{ id: giftId(curGift), name: giftName(curGift), _forced: true }] : [];

      const seen = new Set();
      const options = []
        .concat(curOpt.map(o => ({ _forced: true, id: o.id, name: o.name })))
        .concat(eligible.map(g => ({ id: giftId(g), name: giftName(g) })))
        .filter(o => o && o.id && o.name)
        .filter(o => {
          if (seen.has(o.id)) return false;
          seen.add(o.id);
          return true;
        })
        .map(o => {
          const sel = (String(selectedId) === String(o.id)) ? ' selected' : '';
          const suffix = o._forced ? ' (saved)' : '';
          return `<option value="${String(o.id)}"${sel}>${String(o.name)}${suffix}</option>`;
        })
        .join('\n');

      return `
        <div class="cg-free-slot" data-slot="${i}">
          <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-weight:600; white-space:nowrap; font-size:0.88rem; color:var(--cg-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Gift</span>
            <select id="cg-free-choice-${i}" class="cg-free-gift-select" data-slot="${i}">
              <option value="">— Select a gift —</option>
              ${options}
            </select>
          </div>
          <div class="cg-free-slot-quals" data-slot="${i}"></div>
        </div>
      `;
    });

    row.innerHTML = htmlSlots.join('\n');
    this._renderSlotQuals(slots);

    // Let ExtraCareers re-inject per-slot UI (e.g. "Applies to:" for gift 223)
    // after this render has settled, since our innerHTML wipes any injected elements.
    try {
      document.dispatchEvent(new CustomEvent('cg:free-choices:rendered'));
    } catch (_) {}
  },

  _bindSectionDelegates(section) {
    if (section.__cgBound) return;
    section.__cgBound = true;

    section.addEventListener('change', (e) => {
      const t = e.target;
      if (!t || !t.classList) return;

      if (t.classList.contains('cg-free-gift-select')) {
        const slot = Number(t.getAttribute('data-slot') || '0');
        const nextId = String(t.value || '').trim();

        const prevSlots = getFreeGiftSlotsFromData();
        const nextSlots = prevSlots.slice();
        nextSlots[slot] = nextId;

        this._cleanupSlotQualsOnGiftChange({ slot, prevGiftId: prevSlots[slot], nextGiftId: nextId });

        setFreeGiftSlotsToData(nextSlots, 'free-gift-select');
        this._scheduleRender('free-gift-select');
        return;
      }

      if (t.classList.contains('cg-free-qual-select')) {
        const slot = String(t.getAttribute('data-slot') || '0');
        const type = String(t.getAttribute('data-qtype') || '').toLowerCase();
        const val = String(t.value || '').trim();

        const map = getSlotQualMap();
        if (!map[slot] || typeof map[slot] !== 'object') map[slot] = {};

        const prev = String(map[slot][type] || '').trim();

        if (val) map[slot][type] = val;
        else delete map[slot][type];

        if (Object.keys(map[slot] || {}).length === 0) delete map[slot];

        setSlotQualMap(map);

        if (prev && canon(prev) !== canon(val)) qualStateRemoveIfSafe(type, prev, map);
        if (val) qualStateAdd(type, val);

        this._scheduleRender('free-qual-select');
        return;
      }
    });
  },

  _cleanupSlotQualsOnGiftChange({ slot, prevGiftId, nextGiftId }) {
    const prevG = prevGiftId ? this._getGift(prevGiftId) : null;
    const nextG = nextGiftId ? this._getGift(nextGiftId) : null;

    const prevNeeds = prevG ? detectQualTypesNeeded(prevG) : [];
    const nextNeeds = nextG ? detectQualTypesNeeded(nextG) : [];

    const map = getSlotQualMap();
    const sKey = String(slot);

    const slotObj = (map[sKey] && typeof map[sKey] === 'object') ? map[sKey] : null;
    if (!slotObj) return;

    prevNeeds.forEach(type => {
      if (nextNeeds.includes(type)) return;

      const prevVal = String(slotObj[type] || '').trim();
      if (!prevVal) return;

      delete slotObj[type];
      qualStateRemoveIfSafe(type, prevVal, map);
    });

    if (slotObj && Object.keys(slotObj).length === 0) delete map[sKey];
    setSlotQualMap(map);
  },

  _renderSlotQuals(slots) {
    const section = document.getElementById('cg-free-gifts');
    if (!section) return;

    const map = getSlotQualMap();

    [0, 1, 2].forEach(i => {
      const slotId = String(i);
      const giftIdSel = String(slots[i] || '').trim();
      const wrap = section.querySelector(`.cg-free-slot-quals[data-slot="${i}"]`);
      if (!wrap) return;

      if (!giftIdSel) {
        wrap.innerHTML = '';
        return;
      }

      const g = this._getGift(giftIdSel);
      if (!g) {
        wrap.innerHTML = '';
        return;
      }

      const needs = detectQualTypesNeeded(g);
      if (!needs.length) {
        wrap.innerHTML = '';
        return;
      }

      if (!map[slotId] || typeof map[slotId] !== 'object') map[slotId] = {};

      const html = needs.map(type => {
        const cur = String(map[slotId][type] || '').trim();
        return renderQualSelectHtml({ slot: i, type, value: cur, allGifts: this._allGifts });
      }).join('\n');

      wrap.innerHTML = html;
      setSlotQualMap(map);
    });
  },

  debug() {
    const sizes = {};
    ['language','literacy','insider','mystic','piety'].forEach(t => {
      try { sizes[t] = getQualItemsForType(t, this._allGifts).length; } catch (_) { sizes[t] = null; }
    });

    return {
      giftsCount: Array.isArray(this._allGifts) ? this._allGifts.length : 0,
      slots: getFreeGiftSlotsFromData(),
      slotQualMap: getSlotQualMap(),
      qualCatalogSizes: sizes,
      qualState: QualState?.getAll?.() || null
    };
  }
};

W.CG_FreeChoices = FreeChoices;
export default FreeChoices;
