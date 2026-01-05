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
// NOTE (Jan 2026):
// Layout is controlled by SCSS (assets/css/src/components/_profile.scss).
// We intentionally DO NOT apply inline styles here. We also clear any legacy inline styles.

import State from './state.js';
import FormBuilderAPI from '../formBuilder';
import SpeciesAPI from '../species/api.js';
import CareerAPI from '../career/api.js';

const $ = window.jQuery;

const dbg  = () => !!window.CG_DEBUG_GIFTS;
const log  = (...a) => (dbg() ? console.log('[FreeChoices]', ...a) : null);
const warn = (...a) => (dbg() ? console.warn('[FreeChoices]', ...a) : null);
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
  return s.split(/[,\s]+/g)
    .map(t => t.trim())
    .filter(Boolean)
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

  _root: null,
  _host: null,
  _selects: [],

  _allGifts: [],
  _refreshInFlight: false,
  _lastRefreshAt: 0,
  _retryCount: 0,
  _maxRetries: 2,

  _mountTimer: null,
  _mountTries: 0,
  _maxMountTries: 80, // 20s

  _suppressEmit: false,
  _pendingFill: false,

  init() {
    if (this._inited) return;
    this._inited = true;

    window.CG_FormBuilderAPI = FormBuilderAPI;

    try { State.init(); } catch (_) {}

    // Ensure stable 3-slot selection array
    try {
      this._suppressEmit = true;
      const cur = this._readSelections();
      this._writeSelections(cur);
      this._suppressEmit = false;
    } catch (_) {
      this._suppressEmit = false;
    }

    // Builder lifecycle
    document.addEventListener('cg:builder:opened', () => {
      this._ensureMounted();
      this.refresh({ force: false });
    });

    // IMPORTANT: Builder close empties #cg-form-container; drop DOM refs + stop mount retries.
    document.addEventListener('cg:builder:closed', () => {
      this._resetMount(true);
    });

    // If the builder emits "rendered", refresh UI.
    document.addEventListener('cg:builder:rendered', () => {
      this._ensureMounted();
      this._fillSelectsFiltered();
    });

    // Character load often triggers rebuild/rehydrate.
    document.addEventListener('cg:character:loaded', () => {
      this._ensureMounted();
      this._fillSelectsFiltered();
    });

    // When entering profile, mount + fill
    document.addEventListener('cg:tab:changed', (e) => {
      const to = e?.detail?.to || '';
      if (to !== 'tab-profile') return;

      this._ensureMounted();
      if (!this._mounted) return;

      if (this._pendingFill && Array.isArray(this._allGifts) && this._allGifts.length) {
        this._pendingFill = false;
        this._fillSelectsFiltered();
      } else if (!Array.isArray(this._allGifts) || !this._allGifts.length) {
        this.refresh({ force: true });
      }
    });

    // React to changes that affect prereqs
    if ($) {
      $(document)
        .off('cg:species:changed.cgfree cg:career:changed.cgfree cg:free-gift:changed.cgfree')
        .on('cg:species:changed.cgfree cg:career:changed.cgfree cg:free-gift:changed.cgfree', () => {
          this._fillSelectsFiltered();
        });
    }
    document.addEventListener('cg:free-gift:changed', () => this._fillSelectsFiltered());

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this._ensureMounted());
    } else {
      this._ensureMounted();
    }
  },

  _clearMountTimer() {
    if (this._mountTimer) {
      clearInterval(this._mountTimer);
      this._mountTimer = null;
    }
  },

  _resetMount(clearTimer = false) {
    if (clearTimer) this._clearMountTimer();
    this._mounted = false;
    this._root = null;
    this._host = null;
    this._selects = [];
    this._mountTries = 0;
  },

  _ensureMounted() {
    if (this._mounted && (!inDom(this._root) || !inDom(this._host))) {
      // This is normal during builder re-render; only show in debug mode.
      warn('mounted root was detached; remounting');
      this._resetMount(false);
    }
    if (this._mounted) return true;

    const ok = this._tryMount();
    if (ok) return true;

    if (this._mountTimer) return false;

    this._mountTries = 0;
    this._mountTimer = setInterval(() => {
      this._mountTries++;
      if (this._tryMount()) {
        this._clearMountTimer();

        if (Array.isArray(this._allGifts) && this._allGifts.length) {
          this._fillSelectsFiltered();
        } else {
          this.refresh({ force: false });
        }
        return;
      }
      if (this._mountTries >= this._maxMountTries) {
        this._clearMountTimer();
      }
    }, 250);

    return false;
  },

  _tryMount() {
    let root = document.querySelector('#cg-free-choices');

    // Fallback if placeholder missing
    if (!root) {
      const profilePanel = document.getElementById('tab-profile');
      const container = profilePanel || document.querySelector('#cg-form-container');
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
    } else {
      if (!row.classList.contains('cg-free-row')) row.classList.add('cg-free-row');
    }

    // Let SCSS own layout (clear legacy inline styles)
    if (row.getAttribute('style')) row.removeAttribute('style');

    const ensureSelect = (slot) => {
      const id = `cg-free-choice-${slot}`;
      let sel = row.querySelector(`#${id}`);
      if (!sel) {
        sel = document.createElement('select');
        sel.id = id;
        sel.className = 'cg-free-select';
        sel.setAttribute('data-slot', String(slot));
        row.appendChild(sel);
      } else {
        if (!sel.classList.contains('cg-free-select')) sel.classList.add('cg-free-select');
      }

      // Let SCSS own layout (clear legacy inline styles)
      if (sel.getAttribute('style')) sel.removeAttribute('style');

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

    this._writeSelections(cur);
    this._fillSelectsFiltered();
  },

  _acquiredGiftIdSet(selections) {
    const set = new Set();
    ALWAYS_ACQUIRED_GIFT_IDS.forEach(id => set.add(String(id)));
    (selections || []).forEach(id => { if (id) set.add(String(id)); });

    const sp = SpeciesAPI?.currentProfile || null;
    if (sp) {
      ['gift_id_1', 'gift_id_2', 'gift_id_3'].forEach(k => {
        if (sp[k] != null && String(sp[k])) set.add(String(sp[k]));
      });
    }

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

    let selections = this._readSelections().slice();

    for (let pass = 0; pass < 3; pass++) {
      let changed = false;
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

      if (prior && options.some(o => o.id === String(prior))) sel.value = String(prior);
      else sel.value = '';
    };

    this._selects.forEach((sel, idx) => {
      const options = this._optionsForSlot(idx, selections, acquiredFinal);
      fillSelect(sel, options, placeholders[idx], selections[idx]);
    });

    const nowSel = this._selects.map(s => s.value || '');
    if (nowSel.join('|') !== selections.join('|')) {
      this._suppressEmit = true;
      this._writeSelections(nowSel);
      this._suppressEmit = false;
    }
  },

  refresh({ force = false } = {}) {
    this._ensureMounted();

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
        if (this._mounted) this._drawPlaceholders();
        this._maybeRetry();
        return;
      }

      const giftRows = json.data.map(normalizeGiftForState).filter(Boolean);

      this._allGifts = giftRows;

      try { State.setList(giftRows); } catch (_) {}

      this._retryCount = 0;

      if (this._mounted) this._fillSelectsFiltered();
      else this._pendingFill = true;
    };

    const onFail = (status, errorText, responseText) => {
      this._refreshInFlight = false;
      this._lastRefreshAt = Date.now();
      err('AJAX failed', status, errorText, responseText || '');
      if (this._mounted) this._drawPlaceholders();
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

window.CG_FreeChoices = FreeChoices;
export default FreeChoices;
