// assets/js/src/core/gifts/free-choices.js
//
// Free-choice gifts dropdowns (3).
// - Mounts into Profile tab placeholder #cg-free-choices when present.
// - Ensures expected IDs exist (#cg-free-choice-0..2) so collectFormData() can read them.
// - Safe remount when builder DOM is re-rendered (detached nodes).
// - Syncs gift rows into State.gifts so TraitsService can read ct_gifts_manifold.
// - Filters options by prereqs: ct_gifts_requires .. ct_gifts_requires_nineteen.
// - A gift with NO prereqs is always eligible.
// - If prereqs exist, ALL non-empty prereq IDs must already be acquired (free/species/career).
//
// Exposes: window.CG_FreeChoices (+ window.CG_FormBuilderAPI for debugging)

import State from './state.js';
import FormBuilderAPI from '../formBuilder';
import SpeciesAPI from '../species/api.js';
import CareerAPI from '../career/api.js';

const $ = window.jQuery;

const log  = (...a) => (window.CG_DEBUG_GIFTS ? console.log('[FreeChoices]', ...a) : null);
const warn = (...a) => console.warn('[FreeChoices]', ...a);
const err  = (...a) => console.error('[FreeChoices]', ...a);

const REQ_SUFFIXES = [
  '', 'two','three','four','five','six',
  'seven','eight','nine','ten','eleven',
  'twelve','thirteen','fourteen','fifteen',
  'sixteen','seventeen','eighteen','nineteen'
];

// These appear to be “always present” gifts in your UI/endpoint set.
const ALWAYS_ACQUIRED_GIFT_IDS = ['242', '236']; // Local Knowledge, Language

function ajaxEnv() {
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  const url =
    env.ajax_url ||
    window.ajaxurl ||
    document.body?.dataset?.ajaxUrl ||
    '/wp-admin/admin-ajax.php';

  // Prefer per-action nonce if available
  const perAction = (window.CG_NONCES && window.CG_NONCES.cg_get_free_gifts)
    ? window.CG_NONCES.cg_get_free_gifts
    : null;

  const generic = env.nonce || env.security || env._ajax_nonce || window.CG_NONCE || null;
  return { url, nonce: (perAction || generic) };
}

function parseJsonMaybe(res) {
  try { return (typeof res === 'string') ? JSON.parse(res) : res; }
  catch (_) { return res; }
}

function isTruthyBool(v) {
  if (v === true) return true;
  if (v === false) return false;
  if (v == null) return false;
  const s = String(v).trim().toLowerCase();
  return (s === '1' || s === 'true' || s === 'yes' || s === 'y' || s === 'on');
}

function splitIds(val) {
  if (val == null) return [];
  if (Array.isArray(val)) return val.flatMap(splitIds);
  const s = String(val).trim();
  if (!s) return [];
  // split on commas/whitespace
  return s.split(/[,\s]+/g)
    .map(t => t.trim())
    .filter(Boolean)
    // only keep “number-ish” tokens
    .map(t => (String(parseInt(t, 10)) === 'NaN' ? '' : String(parseInt(t, 10))))
    .filter(Boolean);
}

function extractRequires(rawGift) {
  const req = [];
  REQ_SUFFIXES.forEach(suf => {
    const key = suf ? `ct_gifts_requires_${suf}` : 'ct_gifts_requires';
    if (!Object.prototype.hasOwnProperty.call(rawGift, key)) return;
    req.push(...splitIds(rawGift[key]));
  });
  // de-dupe
  return Array.from(new Set(req));
}

function normalizeGiftForState(g) {
  if (!g || typeof g !== 'object') return null;

  const idRaw =
    g.id ?? g.ct_id ?? g.gift_id ?? g.ct_gift_id ?? g.ct_gifts_id ?? null;

  if (idRaw == null || String(idRaw) === '') return null;

  const manifoldRaw =
    g.ct_gifts_manifold ?? g.manifold ?? g.manifold_count ?? g.ct_manifold ?? 1;

  const id = String(idRaw);

  return {
    ...g,
    id,
    ct_gifts_manifold: parseInt(manifoldRaw, 10) || 1,
    name: (g.name || g.ct_gifts_name || g.title || '').toString() || `Gift #${id}`,
    allows_multiple: isTruthyBool(g.allows_multiple ?? g.ct_gifts_allows_multiple ?? 0),
    requires: extractRequires(g),
  };
}

function inDom(node) {
  return !!(node && document.contains(node));
}

const FreeChoices = {
  _inited: false,
  _mounted: false,

  _root: null,     // placeholder (#cg-free-choices or fallback section)
  _host: null,     // row wrapper
  _selects: [],

  _allGifts: [],   // normalized full gifts from endpoint (includes requires/allows_multiple)
  _refreshInFlight: false,
  _lastRefreshAt: 0,
  _retryCount: 0,
  _maxRetries: 2,

  _mountTimer: null,
  _mountTries: 0,
  _maxMountTries: 80, // 80 * 250ms = 20s

  _suppressEmit: false,

  init() {
    if (this._inited) return;
    this._inited = true;

    window.CG_FormBuilderAPI = FormBuilderAPI;

    try { State.init(); } catch (_) {}

    this._ensureMounted();

    // Re-mount + refresh when builder opens
    document.addEventListener('cg:builder:opened', () => {
      this._ensureMounted();
      this.refresh({ force: false });
    });

    // React to changes that affect prereqs
    if ($) {
      $(document)
        .off('cg:species:changed.cgfree cg:career:changed.cgfree cg:free-gift:changed.cgfree')
        .on('cg:species:changed.cgfree cg:career:changed.cgfree cg:free-gift:changed.cgfree', () => {
          // No refetch needed; just re-filter options
          this._fillSelectsFiltered();
        });
    }
    document.addEventListener('cg:free-gift:changed', () => this._fillSelectsFiltered());

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this._ensureMounted());
    }
  },

  _resetMount() {
    this._mounted = false;
    this._root = null;
    this._host = null;
    this._selects = [];
  },

  _ensureMounted() {
    if (this._mounted && (!inDom(this._root) || !inDom(this._host))) {
      warn('mounted root was detached; remounting');
      this._resetMount();
    }
    if (this._mounted) return true;

    const ok = this._tryMount();
    if (ok) return true;

    if (this._mountTimer) return false;

    this._mountTries = 0;
    this._mountTimer = setInterval(() => {
      this._mountTries++;
      if (this._tryMount()) {
        clearInterval(this._mountTimer);
        this._mountTimer = null;
        this.refresh({ force: false });
        return;
      }
      if (this._mountTries >= this._maxMountTries) {
        clearInterval(this._mountTimer);
        this._mountTimer = null;
      }
    }, 250);

    return false;
  },

  _tryMount() {
    let root = document.querySelector('#cg-free-choices');

    if (!root) {
      const container = document.querySelector('#cg-form-container');
      if (!container) return false;

      let section = document.getElementById('cg-free-gifts');
      if (!section) {
        section = document.createElement('section');
        section.id = 'cg-free-gifts';
        section.innerHTML = `<h3>Free Gifts (3)</h3><div class="cg-free-row"></div>`;
        container.appendChild(section);
      }
      root = section;
    }

    if (!root) return false;

    let row = root.querySelector('.cg-free-row');
    if (!row) {
      row = document.createElement('div');
      row.className = 'cg-free-row';
      root.appendChild(row);
    }

    row.style.display = 'flex';
    row.style.flexWrap = 'wrap';
    row.style.gap = '12px';
    row.style.marginTop = '8px';
    row.style.width = '100%';
    row.style.maxWidth = '100%';
    row.style.boxSizing = 'border-box';
    row.style.alignItems = 'flex-start';

    const ensureSelect = (slot) => {
      const id = `cg-free-choice-${slot}`;
      let sel = row.querySelector(`#${id}`);
      if (!sel) {
        sel = document.createElement('select');
        sel.id = id;
        sel.className = 'cg-free-select';
        sel.setAttribute('data-slot', String(slot));
        row.appendChild(sel);
      }

      sel.style.flex = '1 1 240px';
      sel.style.minWidth = '200px';
      sel.style.maxWidth = '100%';
      sel.style.width = '100%';
      sel.style.boxSizing = 'border-box';

      return sel;
    };

    const s0 = ensureSelect(0);
    const s1 = ensureSelect(1);
    const s2 = ensureSelect(2);

    this._root = root;
    this._host = row;
    this._selects = [s0, s1, s2];

    // Bind once
    this._selects.forEach(sel => {
      if ($) {
        $(sel).off('change.cgfree').on('change.cgfree', () => this._onSelectChange(sel));
      } else {
        sel.onchange = () => this._onSelectChange(sel);
      }
    });

    this._mounted = true;
    this._drawPlaceholders();
    return true;
  },

  _drawPlaceholders() {
    if (!this._mounted) return;
    const placeholders = [
      '— Select gift #1 —',
      '— Select gift #2 —',
      '— Select gift #3 —'
    ];
    this._selects.forEach((sel, idx) => {
      sel.innerHTML = '';
      sel.appendChild(new Option(placeholders[idx], ''));
    });
  },

  _readSelections() {
    let src = null;

    try {
      if (FormBuilderAPI && FormBuilderAPI._data) {
        src = FormBuilderAPI._data.free_gifts || FormBuilderAPI._data.freeGifts || null;
      }
    } catch (_) {}

    if (!Array.isArray(src)) {
      try { if (Array.isArray(State.selected)) src = State.selected; } catch (_) {}
    }
    if (!Array.isArray(src)) src = this._selects.map(s => s.value || '');

    const out = (Array.isArray(src) ? src : [])
      .slice(0, 3)
      .map(v => (v ? String(v) : ''));
    while (out.length < 3) out.push('');
    return out;
  },

  _writeSelections(arrStr) {
    const normalized = (Array.isArray(arrStr) ? arrStr : [])
      .slice(0, 3)
      .map(v => (v ? String(v) : ''));
    while (normalized.length < 3) normalized.push('');

    try {
      if (FormBuilderAPI && FormBuilderAPI._data) {
        FormBuilderAPI._data.free_gifts = normalized.slice();
        FormBuilderAPI._data.freeGifts  = normalized.slice();
      }
    } catch (_) {}

    try { State.setSelected(normalized); } catch (_) {}

    if (this._suppressEmit) return;

    document.dispatchEvent(new CustomEvent('cg:free-gift:changed', {
      detail: { free_gifts: normalized }
    }));
    if ($) $(document).trigger('cg:free-gift:changed', [{ free_gifts: normalized }]);
  },

  _onSelectChange(sel) {
    const slot = parseInt(sel.getAttribute('data-slot') || '0', 10);
    const cur = this._readSelections();
    cur[slot] = sel.value || '';

    // Persist, then re-filter so newly unlocked gifts appear immediately
    this._writeSelections(cur);
    this._fillSelectsFiltered();
  },

  _acquiredGiftIdSet(selections) {
    const set = new Set();

    // Always-acquired gifts (if your rules want these)
    ALWAYS_ACQUIRED_GIFT_IDS.forEach(id => set.add(String(id)));

    // Free choices
    (selections || []).forEach(id => { if (id) set.add(String(id)); });

    // Species gifts
    const sp = SpeciesAPI?.currentProfile || null;
    if (sp) {
      ['gift_id_1', 'gift_id_2', 'gift_id_3'].forEach(k => {
        if (sp[k] != null && String(sp[k])) set.add(String(sp[k]));
      });
    }

    // Career gifts
    const cp = CareerAPI?.currentProfile || null;
    if (cp) {
      ['gift_id_1', 'gift_id_2', 'gift_id_3'].forEach(k => {
        if (cp[k] != null && String(cp[k])) set.add(String(cp[k]));
      });
    }

    return set;
  },

  _isEligibleGift(g, acquiredSet) {
    const req = Array.isArray(g.requires) ? g.requires : [];
    if (!req.length) return true;
    // AND semantics: all prereqs must be acquired
    return req.every(id => acquiredSet.has(String(id)));
  },

  _optionsForSlot(slot, selections, acquiredSet) {
    const cur = selections[slot] || '';
    const otherSelected = new Set(
      selections.map((id, i) => (i === slot ? '' : (id || ''))).filter(Boolean)
    );

    return (this._allGifts || [])
      .filter(g => {
        if (!g || !g.id) return false;
        if (!this._isEligibleGift(g, acquiredSet)) return false;

        // Prevent duplicates unless allows_multiple OR it is the current selection
        if (!g.allows_multiple && otherSelected.has(String(g.id)) && String(g.id) !== String(cur)) {
          return false;
        }
        return true;
      })
      .map(g => ({ id: String(g.id), name: String(g.name || `Gift #${g.id}`) }));
  },

  _fillSelectsFiltered() {
    if (!this._mounted) return;
    if (!Array.isArray(this._allGifts) || !this._allGifts.length) {
      this._drawPlaceholders();
      return;
    }

    const placeholders = [
      '— Select gift #1 —',
      '— Select gift #2 —',
      '— Select gift #3 —'
    ];

    // Iterate a few times to clear any now-invalid selections (after species/career changes)
    let selections = this._readSelections().slice();
    let changed = false;

    for (let pass = 0; pass < 3; pass++) {
      changed = false;
      const acquired = this._acquiredGiftIdSet(selections);

      for (let slot = 0; slot < this._selects.length; slot++) {
        const opts = this._optionsForSlot(slot, selections, acquired);
        const cur  = selections[slot] || '';
        const isCurValid = !cur || opts.some(o => o.id === String(cur));

        if (cur && !isCurValid) {
          log('Clearing invalid selection', { slot, cur });
          selections[slot] = '';
          changed = true;
        }
      }

      if (!changed) break;
    }

    // If we had to clear anything, persist silently first
    const before = this._readSelections().slice();
    if (selections.join('|') !== before.join('|')) {
      this._suppressEmit = true;
      this._writeSelections(selections);
      this._suppressEmit = false;
    }

    const acquiredFinal = this._acquiredGiftIdSet(selections);

    const fillSelect = (sel, options, placeholder, prior) => {
      sel.innerHTML = '';
      sel.appendChild(new Option(placeholder, ''));

      options.forEach(opt => sel.appendChild(new Option(opt.name, opt.id)));

      if (prior && options.some(o => o.id === String(prior))) {
        sel.value = String(prior);
      } else {
        sel.value = '';
      }
    };

    this._selects.forEach((sel, idx) => {
      const options = this._optionsForSlot(idx, selections, acquiredFinal);
      fillSelect(sel, options, placeholders[idx], selections[idx]);
    });

    // Sync state (silently; DOM is authoritative here)
    const nowSel = this._selects.map(s => s.value || '');
    if (nowSel.join('|') !== selections.join('|')) {
      this._suppressEmit = true;
      this._writeSelections(nowSel);
      this._suppressEmit = false;
    }
  },

  refresh({ force = false } = {}) {
    this._ensureMounted();
    if (!this._mounted) return;

    const now = Date.now();
    if (!force && (now - this._lastRefreshAt) < 3000) return;
    if (this._refreshInFlight) return;
    this._refreshInFlight = true;

    const { url, nonce } = ajaxEnv();
    const payload = { action: 'cg_get_free_gifts' };
    if (nonce) {
      payload.security = nonce;
      payload.nonce = nonce;
      payload._ajax_nonce = nonce;
    }

    const onDone = (res) => {
      this._refreshInFlight = false;
      this._lastRefreshAt = Date.now();

      const json = parseJsonMaybe(res);

      if (!json || json.success !== true || !Array.isArray(json.data)) {
        warn('Unexpected response:', json);
        this._drawPlaceholders();
        this._maybeRetry();
        return;
      }

      const giftRows = json.data
        .map(normalizeGiftForState)
        .filter(Boolean);

      // Keep full list for filtering + state lookups
      this._allGifts = giftRows;

      // TraitsService needs the master list
      try { State.setList(giftRows); } catch (_) {}

      this._retryCount = 0;
      this._fillSelectsFiltered();
    };

    const onFail = (status, errorText, responseText) => {
      this._refreshInFlight = false;
      this._lastRefreshAt = Date.now();
      err('AJAX failed', status, errorText, responseText || '');
      this._drawPlaceholders();
      this._maybeRetry();
    };

    if ($ && $.post) {
      $.post(url, payload)
        .done(onDone)
        .fail((xhr, status, e) => onFail(status, e, xhr?.responseText));
    } else {
      const body = new URLSearchParams(payload).toString();
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body,
        credentials: 'same-origin'
      })
        .then(r => r.text().then(t => ({ status: r.status, text: t })))
        .then(({ status, text }) => {
          let j = null;
          try { j = JSON.parse(text); } catch (_) {}
          if (status >= 200 && status < 300) onDone(j);
          else onFail(status, 'http_error', text);
        })
        .catch(e => onFail('fetch_error', e?.message || String(e), ''));
    }
  },

  _maybeRetry() {
    if (!this._mounted) return;
    if (this._retryCount >= this._maxRetries) return;

    this._retryCount++;
    const delay = 600 * this._retryCount;
    setTimeout(() => {
      if (!this._allGifts || !this._allGifts.length) {
        this.refresh({ force: true });
      }
    }, delay);
  }
};

// Expose globally
window.CG_FreeChoices = FreeChoices;

export default FreeChoices;
