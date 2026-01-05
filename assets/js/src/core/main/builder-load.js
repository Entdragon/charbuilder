// assets/js/src/core/main/builder-load.js
// BuilderLoad v2026-01-04: guard against MutationObserver self-trigger loops + better AJAX parsing
import FormBuilderAPI from '../formBuilder';

const $ = window.jQuery;
const LOG = (...a) => console.log('[BuilderLoad]', ...a);
const ERR = (...a) => console.error('[BuilderLoad]', ...a);

let _inited = false;

// Cache to avoid repeated AJAX calls and reduce flicker
let _cacheRows = null;
let _cacheAt = 0;
const CACHE_MS = 30_000;

// Guards to prevent runaway loops
let _inFlight = null;           // promise of current list fetch
let _populating = false;        // we are actively mutating the select options
let _suppressObserver = false;  // ignore observer callbacks during our own DOM writes
let _lastEnsureAt = 0;
const ENSURE_THROTTLE_MS = 250;

let _lastForceFetchAt = 0;
const FORCE_THROTTLE_MS = 2000;

function _getListRequest() {
  // Prefer the current API name, but support the older one if present.
  if (FormBuilderAPI && typeof FormBuilderAPI.listCharacters === 'function') {
    return FormBuilderAPI.listCharacters();
  }
  if (FormBuilderAPI && typeof FormBuilderAPI.fetchCharacters === 'function') {
    return FormBuilderAPI.fetchCharacters();
  }
  return null;
}

function _parseRows(resp) {
  let parsed = resp;

  if (typeof resp === 'string') {
    try { parsed = JSON.parse(resp); } catch (_) { /* keep string */ }
  }

  // WordPress-style: { success: true|false, data: ... }
  if (parsed && typeof parsed === 'object' && Object.prototype.hasOwnProperty.call(parsed, 'success')) {
    if (parsed.success !== true) {
      ERR('AJAX returned success:false', parsed.data || parsed);
      return [];
    }
    if (Array.isArray(parsed.data)) return parsed.data;
    if (Array.isArray(parsed.data?.data)) return parsed.data.data;
    return [];
  }

  // Sometimes APIs return { data: [...] } or raw [...]
  if (Array.isArray(parsed)) return parsed;
  if (parsed && typeof parsed === 'object' && Array.isArray(parsed.data)) return parsed.data;

  return [];
}

function fetchCharacterList(force = false) {
  // Prevent duplicate concurrent calls
  if (_inFlight) return _inFlight;

  const now = Date.now();

  // Throttle force-refresh storms (e.g. accidental repeated refresh events)
  if (force && (now - _lastForceFetchAt) < FORCE_THROTTLE_MS && _cacheRows) {
    return $.Deferred().resolve(_cacheRows).promise();
  }

  // Use cache unless force
  if (!force && _cacheRows && (now - _cacheAt) < CACHE_MS) {
    return $.Deferred().resolve(_cacheRows).promise();
  }

  LOG('fetching characters via AJAX…', force ? '(force)' : '');

  const req = _getListRequest();
  if (!req) {
    ERR('No listCharacters()/fetchCharacters() available on FormBuilderAPI');
    return $.Deferred().resolve([]).promise();
  }

  const d = $.Deferred();
  _inFlight = d.promise();
  if (force) _lastForceFetchAt = now;

  const finalize = (rows) => {
    _inFlight = null;
    d.resolve(rows);
  };

  const onOk = (resp) => {
    const rows = _parseRows(resp);
    _cacheRows = Array.isArray(rows) ? rows : [];
    _cacheAt = Date.now();
    LOG(`fetched ${_cacheRows.length} records`);
    finalize(_cacheRows);
  };

  const onFail = (xhr, status, err) => {
    // jqXHR-style failure
    ERR('character list request failed', status, err, xhr?.status, xhr?.responseText);
    finalize([]);
  };

  // Support both jqXHR (jQuery Deferred) and native Promises.
  if (typeof req.then === 'function') {
    Promise.resolve(req).then(onOk).catch((err) => {
      ERR('character list request failed', err);
      finalize([]);
    });
  } else if (typeof req.done === 'function' && typeof req.fail === 'function') {
    req.done(onOk).fail(onFail);
  } else {
    ERR('character list request returned an unsupported value:', req);
    finalize([]);
  }

  return _inFlight;
}

function populateLoadSelect(rows) {
  const $sel = $('#cg-splash-load-select');
  if (!$sel.length) return;

  _populating = true;
  _suppressObserver = true;

  try {
    const current = String($sel.val() || '');
    $sel.empty();
    $sel.append($('<option>', { value: '', text: '-- Select a character --' }));

    (rows || []).forEach((r) => {
      const id = String(r?.id ?? '');
      const name = String(r?.name ?? '');
      if (!id) return;
      $sel.append($('<option>', { value: id, text: name || `#${id}` }));
    });

    if (current) $sel.val(current);
  } finally {
    // Let DOM mutations settle, then re-enable observer-driven checks.
    setTimeout(() => {
      _suppressObserver = false;
      _populating = false;
    }, 0);
  }
}

function ensurePopulated({ force = false } = {}) {
  const now = Date.now();
  if ((now - _lastEnsureAt) < ENSURE_THROTTLE_MS) return;
  _lastEnsureAt = now;

  if (_populating || _suppressObserver) return;

  const $sel = $('#cg-splash-load-select');
  if (!$sel.length) return;

  const count = $sel.find('option').length;
  if (count > 1) return;

  // If we already have a cache and we're not forcing, just paint it (no AJAX).
  if (!force && _cacheRows && (now - _cacheAt) < CACHE_MS) {
    populateLoadSelect(_cacheRows);
    return;
  }

  LOG('populating #cg-splash-load-select');
  fetchCharacterList(force).always(populateLoadSelect);
}

function _makeDebouncedEnsure() {
  let t = null;
  return () => {
    if (t) return;
    t = setTimeout(() => {
      t = null;
      ensurePopulated({ force: false });
    }, 50);
  };
}

export default function bindLoadEvents() {
  if (_inited) return;
  _inited = true;

  LOG('init');

  // Populate once on init if present
  ensurePopulated({ force: false });

  // Cross-bundle idempotency: store all native listeners/observers on window.__CG_EVT__.
  try {
    window.__CG_EVT__ = window.__CG_EVT__ || {};

    // ---- MutationObserver (idempotent) ----
    if (window.__CG_EVT__.builderLoadObserver) {
      try { window.__CG_EVT__.builderLoadObserver.disconnect(); } catch (_) {}
    }

    const debouncedEnsure = _makeDebouncedEnsure();

    window.__CG_EVT__.builderLoadObserver = new MutationObserver(() => {
      if (_suppressObserver) return;
      debouncedEnsure();
    });

    try {
      window.__CG_EVT__.builderLoadObserver.observe(document.body, { childList: true, subtree: true });
    } catch (_) {}

    // ---- Custom event cg:characters:refresh (idempotent, NEVER anonymous) ----
    if (window.__CG_EVT__.charactersRefreshLoad) {
      document.removeEventListener('cg:characters:refresh', window.__CG_EVT__.charactersRefreshLoad);
    }

    window.__CG_EVT__.charactersRefreshLoad = () => {
      LOG('received cg:characters:refresh → repopulating load list');
      fetchCharacterList(true).always(populateLoadSelect);
    };

    document.addEventListener('cg:characters:refresh', window.__CG_EVT__.charactersRefreshLoad);
  } catch (e) {
    ERR('failed to bind load listeners', e);
  }

  // Public debug handle (optional)
  try {
    window.CG_BuilderLoad = window.CG_BuilderLoad || {};
    window.CG_BuilderLoad.refresh = () => fetchCharacterList(true).always(populateLoadSelect);
    window.CG_BuilderLoad.ensure  = () => ensurePopulated({ force: false });
    window.CG_BuilderLoad._debug  = () => ({
      cacheAgeMs: _cacheAt ? (Date.now() - _cacheAt) : null,
      cacheCount: Array.isArray(_cacheRows) ? _cacheRows.length : null,
      inFlight: !!_inFlight,
      populating: _populating,
      suppressObserver: _suppressObserver
    });
  } catch (_) {}
}
