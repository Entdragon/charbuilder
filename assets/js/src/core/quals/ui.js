// assets/js/src/core/quals/ui.js
//
// PURPOSE (after refactor):
// - ONLY manage the BASE free Language selector (the "everyone gets one Language").
// - REMOVE/STOP rendering the old global "Qualifications" UI (chips/add buttons).
//
// Slot-scoped qualification selectors (Language/Literacy/Mystic/Piety/Insider from selected gifts)
// are rendered INLINE by gifts/free-choices.js under the free gift slots.
//
// Exposes: window.CG_QualUI

import Quals from './index.js';
import QualState from './state.js';

function cgWin() {
  if (typeof globalThis !== 'undefined') return globalThis;
  if (typeof window !== 'undefined') return window;
  return {};
}

const W = cgWin();
const $ = (W && W.jQuery) ? W.jQuery : null;

function modalRoot() {
  if (typeof document === 'undefined') return null;
  return document.querySelector('#cg-modal') || null;
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

function uniqSorted(list) {
  const seen = new Set();
  const out = [];
  (Array.isArray(list) ? list : []).forEach(v => {
    const raw = String(v || '').trim().replace(/\s+/g, ' ');
    if (!raw) return;
    const k = canon(raw);
    if (!k || seen.has(k)) return;
    seen.add(k);
    out.push(raw);
  });
  out.sort((a, b) => canon(a).localeCompare(canon(b)));
  return out;
}

function removeLegacyQualUIs(modal) {
  if (!modal) return;
  try {
    const oldBox = modal.querySelector('#cg-quals-box');
    if (oldBox) oldBox.remove();
  } catch (_) {}

  // If any older inline container exists from prior builds, remove it.
  try {
    const oldInline = modal.querySelector('#cg-quals-inline');
    if (oldInline) oldInline.remove();
  } catch (_) {}
}

function getAllGiftsList() {
  // Primary: FreeChoices keeps the full gifts list after cg_get_free_gifts
  const FC = W.CG_FreeChoices;
  if (FC && Array.isArray(FC._allGifts) && FC._allGifts.length) return FC._allGifts;

  // Secondary (best-effort): other globals if present
  const GS = W.CG_GiftsState || W.CG_Gifts || null;
  if (GS) {
    if (Array.isArray(GS._allGifts) && GS._allGifts.length) return GS._allGifts;
    if (Array.isArray(GS.allGifts) && GS.allGifts.length) return GS.allGifts;
    if (Array.isArray(GS.gifts) && GS.gifts.length) return GS.gifts;
  }

  return [];
}

function getRequiresSpecial(g) {
  return String(
    g?.requires_special ??
    g?.ct_gifts_requires_special ??
    g?.ct_requires_special ??
    ''
  );
}

function extractLanguagesFromGifts(gifts) {
  const out = [];

  (Array.isArray(gifts) ? gifts : []).forEach(g => {
    const rs = getRequiresSpecial(g);
    if (!rs) return;

    // Split into candidate lines
    const lines = String(rs)
      .split(/\r?\n|•|·/g)
      .map(s => String(s || '').trim())
      .filter(Boolean);

    lines.forEach(line => {
      // Match: "Language: X"
      const m = line.match(/^language\s*:\s*(.+)$/i);
      if (!m) return;

      const rest = String(m[1] || '').trim();
      if (!rest) return;

      // If it's clearly generic, ignore (keeps the base list “real”)
      const restCanon = canon(rest);
      if (restCanon === 'any' || restCanon === 'varies' || restCanon === 'see text') return;

      // Some rows might contain multiple languages separated by commas/semicolons
      rest
        .split(/\s*[;,]\s*/g)
        .map(x => String(x || '').trim())
        .filter(Boolean)
        .forEach(x => out.push(x));
    });
  });

  return uniqSorted(out);
}

/**
 * Best-effort: find where the "Language" gift placeholder lives in the Gifts list.
 * If we can't find it reliably, we mount directly before #cg-free-choices placeholder.
 */
function findBaseLanguageHost(modal) {
  if (!modal) return null;

  // Best: the actual placeholder from render-profile.js
  const cgLanguage = modal.querySelector('#cg-language');
  if (cgLanguage) return cgLanguage;

  // Prefer explicit IDs if they exist (future-proofing)
  const direct =
    modal.querySelector('#cg-free-language') ||
    modal.querySelector('#cg-language-choice') ||
    modal.querySelector('#cg-gift-language');
  if (direct) return direct;

  // Fallback: mount near free choices placeholder
  const freeChoices = modal.querySelector('#cg-free-choices') || document.querySelector('#cg-free-choices');
  if (freeChoices && freeChoices.parentElement) return freeChoices.parentElement;

  return null;
}

function ensureBaseLanguageContainer(modal) {
  const host = findBaseLanguageHost(modal);
  if (!host) return null;

  let wrap = modal.querySelector('#cg-base-language');
  if (!wrap) {
    wrap = document.createElement('div');
    wrap.id = 'cg-base-language';
    wrap.className = 'cg-gift-item cg-base-language';
    wrap.innerHTML = `
      <div class="cg-base-language-inner" style="display:flex; flex-direction:column; gap:6px;">
        <div style="font-weight:600;">Language</div>
        <div class="cg-base-language-control"></div>
      </div>
    `;
  }

  // If host is a gift-item, append inside it; otherwise insert before free choices.
  try {
    if (host.classList && host.classList.contains('cg-gift-item')) {
      if (!host.querySelector('#cg-base-language')) host.appendChild(wrap);
    } else {
      const freeChoices = modal.querySelector('#cg-free-choices') || document.querySelector('#cg-free-choices');
      if (freeChoices && freeChoices.parentElement) {
        if (wrap.parentNode !== freeChoices.parentElement) {
          freeChoices.parentElement.insertBefore(wrap, freeChoices);
        }
      } else if (wrap.parentNode !== host) {
        host.appendChild(wrap);
      }
    }
  } catch (_) {}

  return wrap;
}

let _languageListCache = null;
let _languageListLoading = false;

function fetchLanguageList(onLoaded) {
  if (_languageListCache !== null) {
    if (onLoaded) onLoaded(_languageListCache);
    return;
  }
  if (_languageListLoading) return;
  _languageListLoading = true;

  try {
    const ajaxUrl = (W.CG_AJAX && W.CG_AJAX.ajax_url) ? W.CG_AJAX.ajax_url : '/api/ajax';
    fetch(ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'cg_get_language_list' }),
    })
      .then(r => r.json())
      .then(json => {
        _languageListLoading = false;
        _languageListCache = (json && json.success && Array.isArray(json.data)) ? json.data : [];
        if (onLoaded) onLoaded(_languageListCache);
      })
      .catch(() => {
        _languageListLoading = false;
        _languageListCache = [];
        if (onLoaded) onLoaded(_languageListCache);
      });
  } catch (_) {
    _languageListLoading = false;
    _languageListCache = [];
  }
}

function getLanguageLabelsForBaseSelect() {
  if (_languageListCache !== null && _languageListCache.length) return _languageListCache;

  // Fallback: derive languages from gifts requires_special (legacy path)
  const gifts = getAllGiftsList();
  const langs = extractLanguagesFromGifts(gifts);
  return langs;
}

function renderLanguageSelect(container) {
  if (!container) return;

  const labels = getLanguageLabelsForBaseSelect();
  const cur = (QualState && typeof QualState.get === 'function')
    ? ((QualState.get('language') || [])[0] || '')
    : '';

  // Ensure current selection is always present, even if not in catalog yet
  const finalLabels = uniqSorted([cur, ...labels]);

  const opts = finalLabels.map(label => {
    const sel = (canon(cur) === canon(label)) ? ' selected' : '';
    const safe = String(label).replace(/"/g, '&quot;');
    return `<option value="${safe}"${sel}>${label}</option>`;
  }).join('\n');

  container.innerHTML = `
    <select id="cg-base-language-select" style="min-width:220px;">
      <option value="">— Select Language —</option>
      ${opts}
    </select>
  `;

  const sel = container.querySelector('#cg-base-language-select');
  if (!sel) return;

  sel.addEventListener('change', (e) => {
    const nextVal = String(e.target.value || '').trim();
    try { QualState?.init?.(); } catch (_) {}

    // Keep base language as index 0 by rewriting the language list deterministically.
    try {
      const current = (QualState && typeof QualState.get === 'function')
        ? (QualState.get('language') || []).slice()
        : [];

      const rest = current.filter(v => canon(v) !== canon(nextVal) && canon(v) !== canon(current[0] || ''));
      const next = nextVal ? [nextVal, ...rest] : rest;

      // Mutate state in-place and persist (QualState.persist() will dispatch cg:quals:changed)
      if (QualState && QualState.data && Array.isArray(QualState.data.language) && typeof QualState.persist === 'function') {
        QualState.data.language = next;
        QualState.persist();
      } else {
        // Best-effort fallback: remove old base + add new (order might vary)
        if (current[0]) QualState.remove('language', current[0]);
        if (nextVal) QualState.add('language', nextVal);
      }
    } catch (_) {}
  });
}

// Reuse singleton if module is evaluated twice
const Existing = W.CG_QualUI;

const QualUI = (Existing && Existing.__cg_singleton) ? Existing : {
  __cg_singleton: true,

  _inited: false,
  _observer: null,
  _lastModalPresent: null,

  _renderScheduled: false,
  _rendering: false,

  _onCatalogUpdated: null,
  _onBuilderOpened: null,
  _onTabChanged: null,

  init() {
    if (this._inited) return;
    this._inited = true;

    try { QualState?.init?.(); } catch (_) {}

    this._bindEvents();
    this._installObserver();
    this._scheduleRender('init');
  },

  _scheduleRender(reason = '') {
    if (this._renderScheduled) return;
    this._renderScheduled = true;

    const run = () => {
      this._renderScheduled = false;
      if (this._rendering) return;
      this._rendering = true;
      try {
        this.render();
      } catch (err) {
        console.error('[QualUI] render failed', { reason }, err);
      } finally {
        this._rendering = false;
      }
    };

    if (typeof requestAnimationFrame !== 'undefined') requestAnimationFrame(run);
    else setTimeout(run, 0);
  },

  _bindEvents() {
    if (typeof document === 'undefined') return;

    if (!this._onCatalogUpdated) this._onCatalogUpdated = () => this._scheduleRender('catalog-updated');
    if (!this._onBuilderOpened)  this._onBuilderOpened  = () => { try { QualState?.init?.(); } catch (_) {} this._scheduleRender('builder-opened'); };
    if (!this._onTabChanged)     this._onTabChanged     = () => this._scheduleRender('tab-changed');

    document.removeEventListener('cg:quals:catalog-updated', this._onCatalogUpdated);
    document.addEventListener('cg:quals:catalog-updated', this._onCatalogUpdated);

    document.removeEventListener('cg:builder:opened', this._onBuilderOpened);
    document.addEventListener('cg:builder:opened', this._onBuilderOpened);

    document.removeEventListener('cg:tab:changed', this._onTabChanged);
    document.addEventListener('cg:tab:changed', this._onTabChanged);

    if ($ && $.fn) {
      $(document)
        .off('cg:quals:catalog-updated.cgqualui cg:builder:opened.cgqualui cg:tab:changed.cgqualui')
        .on('cg:quals:catalog-updated.cgqualui', this._onCatalogUpdated)
        .on('cg:builder:opened.cgqualui', this._onBuilderOpened)
        .on('cg:tab:changed.cgqualui', this._onTabChanged);
    }
  },

  _installObserver() {
    if (this._observer) return;
    if (typeof MutationObserver === 'undefined' || typeof document === 'undefined') return;

    this._lastModalPresent = !!modalRoot();

    this._observer = new MutationObserver(() => {
      const present = !!modalRoot();
      if (present !== this._lastModalPresent) {
        this._lastModalPresent = present;
        if (present) this._scheduleRender('modal-opened');
        return;
      }
    });

    this._observer.observe(document.body, { childList: true, subtree: true });
  },

  render() {
    const modal = modalRoot();
    if (!modal) return;

    // Kill old "Qualifications" UI so we don't get global piety/literacy/etc spam.
    removeLegacyQualUIs(modal);

    // Ensure base language selector is mounted near the gifts list
    const wrap = ensureBaseLanguageContainer(modal);
    if (!wrap) return;

    const control = wrap.querySelector('.cg-base-language-control');
    if (!control) return;

    renderLanguageSelect(control);
  }
};

W.CG_QualUI = QualUI;
export default QualUI;
