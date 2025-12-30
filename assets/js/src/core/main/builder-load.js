// assets/js/src/core/main/builder-load.js
// Robust splash-load dropdown population (jQuery-safe):
// - Splash UI may be injected after bundle runs (select absent at init).
// - Observe DOM until it appears, then populate once.
// - Supports refresh after saves via cg:characters:refresh.
// - IMPORTANT: jQuery Deferred doesn't support Promise.finally() -> use .always().

const $    = window.jQuery;
const LOG  = (...a) => console.log('[BuilderLoad]', ...a);
const WARN = (...a) => console.warn('[BuilderLoad]', ...a);

let _inited   = false;
let _observer = null;
let _inFlight = false;

/** Prefer per-action nonce if available */
function nonceFor(action) {
  const per = (window.CG_NONCES && window.CG_NONCES[action]) ? window.CG_NONCES[action] : null;
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  return per || env.nonce || env.security || env._ajax_nonce || window.CG_NONCE || null;
}

function ajaxUrl() {
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  return env.ajax_url || window.ajaxurl || document.body?.dataset?.ajaxUrl || '/wp-admin/admin-ajax.php';
}

function parseJsonMaybe(res) {
  try { return (typeof res === 'string') ? JSON.parse(res) : res; }
  catch (_) { return res; }
}

function normalizeItems(arr) {
  if (!Array.isArray(arr)) return [];
  return arr.map(it => {
    if (it && typeof it === 'object') {
      return { id: String(it.id ?? it.value ?? ''), name: String(it.name ?? it.title ?? '') };
    }
    return { id: String(it), name: String(it) };
  }).filter(it => it.id && it.name);
}

function getSelect() {
  if (!$) return null;
  const $sel = $('#cg-splash-load-select').first();
  return ($sel && $sel.length) ? $sel : null;
}

function populateSelect($sel, items, { preserveSelection = true } = {}) {
  if (!$sel || !$sel.length) return;

  const prior = preserveSelection ? ($sel.val() || '') : '';
  $sel.empty();
  $sel.append(new Option('— Select a character —', ''));

  const normalized = normalizeItems(items);
  normalized.forEach(it => $sel.append(new Option(it.name, it.id)));

  if (preserveSelection && prior && normalized.some(x => x.id === String(prior))) {
    $sel.val(String(prior));
  }
}

/**
 * Fetch characters list (returns jqXHR / jQuery promise)
 * Expects WP-style response: { success:true, data:[...] }
 */
function fetchCharacters() {
  const url   = ajaxUrl();
  const nonce = nonceFor('cg_load_characters');

  const payload = { action: 'cg_load_characters' };
  if (nonce) {
    payload.security    = nonce;
    payload.nonce       = nonce;
    payload._ajax_nonce = nonce;
  }

  // Return a jQuery promise that resolves to the array
  return $.post(url, payload).then(res => {
    const json = parseJsonMaybe(res);
    if (!json || json.success !== true) {
      // Reject in a jQuery-friendly way
      return $.Deferred().reject(json).promise();
    }
    return Array.isArray(json.data) ? json.data : [];
  });
}

/**
 * Populate when the select exists.
 * - If already populated (options > 1) and not forced, do nothing.
 */
function ensurePopulated({ force = false, preserveSelection = false } = {}) {
  if (!$) return;

  const $sel = getSelect();
  if (!$sel) return; // observer will handle late insertion

  const optCount = $sel.find('option').length;
  if (!force && optCount > 1) return;

  // Use preloaded list if present; otherwise AJAX.
  const preload = window.CG_DATA?.characters || window.cgCharacters || null;
  if (preload && Array.isArray(preload)) {
    LOG('using preloaded characters:', preload.length);
    populateSelect($sel, preload, { preserveSelection });
    return;
  }

  if (_inFlight) return;
  _inFlight = true;

  LOG('fetching characters via AJAX…');

  fetchCharacters()
    .done(items => {
      LOG('fetched', (items || []).length, 'records');
      populateSelect($sel, items, { preserveSelection });
    })
    .fail((a, b, c) => {
      // a may be jqXHR, or may be the rejected json object from above
      const msg =
        (a && a.data) ? String(a.data) :
        (a && a.responseText) ? String(a.responseText) :
        (typeof a === 'string') ? a :
        (b || c || 'unknown error');
      WARN('character list fetch failed:', msg);
      populateSelect($sel, [], { preserveSelection: false });
    })
    .always(() => {
      _inFlight = false;
    });
}

/**
 * Watch DOM for the splash select to appear (because splash is often injected by JS).
 */
function startObserver() {
  if (_observer || !window.MutationObserver) return;

  const tryNow = () => {
    const $sel = getSelect();
    if ($sel) {
      LOG('found #cg-splash-load-select (late) — populating');
      ensurePopulated({ force: false, preserveSelection: false });
      return true;
    }
    return false;
  };

  if (tryNow()) return;

  LOG('waiting for #cg-splash-load-select to appear…');
  _observer = new MutationObserver(() => {
    if (tryNow()) {
      _observer.disconnect();
      _observer = null;
    }
  });

  _observer.observe(document.body, { childList: true, subtree: true });

  // Safety stop (don’t watch forever)
  setTimeout(() => {
    if (_observer) {
      _observer.disconnect();
      _observer = null;
      LOG('stop waiting (timeout) — splash select never appeared on this page');
    }
  }, 15000);
}

export default function bindLoadEvents() {
  if (_inited) return;
  _inited = true;

  LOG('init');

  if (!$) {
    WARN('jQuery not present; load dropdown will not populate.');
    return;
  }

  // If select exists now, populate; otherwise observe for late insertion.
  ensurePopulated({ force: false, preserveSelection: false });
  startObserver();

  // Manual refresh button (if present in splash markup)
  $(document)
    .off('click.cgload', '#cg-load-refresh')
    .on('click.cgload', '#cg-load-refresh', e => {
      e.preventDefault();
      LOG('manual refresh');
      ensurePopulated({ force: true, preserveSelection: true });
    });

  // Auto-refresh after a successful save
  document.addEventListener('cg:characters:refresh', () => {
    LOG('received cg:characters:refresh → repopulating load list');
    ensurePopulated({ force: true, preserveSelection: true });
  });
}
