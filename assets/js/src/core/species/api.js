// assets/js/src/core/species/api.js
// Reliable Species data access with localized-globals first and AJAX fallback.
// IMPORTANT: adds in-flight de-dupe so multiple callers don't spam AJAX.
// Preserves behavior, supports both CG_AJAX & CG_Ajax, and sends all common nonce keys.
// Exposes window.SpeciesAPI for console debugging.

const log  = (...a) => console.log('[SpeciesAPI]', ...a);
const warn = (...a) => console.warn('[SpeciesAPI]', ...a);
const $    = window.jQuery;

/**
 * Collect AJAX env (mu-plugin, legacy, or WP ajaxurl)
 */
function ajaxEnv() {
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  const ajax_url =
    env.ajax_url ||
    window.ajaxurl ||
    document.body?.dataset?.ajaxUrl ||
    '/wp-admin/admin-ajax.php';
  const nonce = env.nonce || env.security || window.CG_NONCE || null;
  return { ajax_url, nonce };
}

/**
 * Normalize various list shapes into [{id,name}]
 */
function normalizeList(raw) {
  if (!Array.isArray(raw)) return [];
  return raw.map(it => {
    if (it && typeof it === 'object') {
      return {
        id:   String(it.id ?? it.value ?? ''),
        name: String(it.name ?? it.title ?? '')
      };
    }
    return { id: String(it), name: String(it) };
  }).filter(x => x.id && x.name);
}

/**
 * Try preloaded globals (fast path)
 */
function preloadedList() {
  if (Array.isArray(window.CG_SPECIES_LIST) && window.CG_SPECIES_LIST.length) {
    return normalizeList(window.CG_SPECIES_LIST);
  }
  if (window.CG_DATA && Array.isArray(window.CG_DATA.species) && window.CG_DATA.species.length) {
    return normalizeList(window.CG_DATA.species);
  }
  if (Array.isArray(window.cgSpecies) && window.cgSpecies.length) {
    return normalizeList(window.cgSpecies);
  }
  return null;
}

/** Post + lenient JSON parse (stays in jQuery Deferred world) */
function postJSON(url, data) {
  return $.post(url, data).then(res => {
    try { return (typeof res === 'string') ? JSON.parse(res) : res; }
    catch (_) { return res; }
  });
}

const SpeciesAPI = {
  _cache: { list: null, listPromise: null },
  currentProfile: null,

  clearCache() {
    this._cache.list = null;
    this._cache.listPromise = null;
  },

  /**
   * Get the species list.
   * @param {boolean} force If true, bypasses globals/cache and hits AJAX.
   * @returns {jqXHR|Promise<array>}
   */
  getList(force = false) {
    // 1) Globals (no network)
    if (!force) {
      const pre = preloadedList();
      if (pre && pre.length) {
        this._cache.list = pre.slice();
        return $.Deferred().resolve(this._cache.list).promise();
      }
    }

    // 2) Cached
    if (!force && this._cache.list) {
      return $.Deferred().resolve(this._cache.list).promise();
    }

    // 2.5) In-flight de-dupe (CRITICAL)
    // If a request is already running, reuse it (even if force=true) to avoid parallel spam.
    if (this._cache.listPromise) {
      return this._cache.listPromise;
    }

    // 3) AJAX
    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) {
      warn('No AJAX URL available; returning empty list.');
      return $.Deferred().resolve([]).promise();
    }

    log('Fetching species via AJAX…');
    const payload = {
      action:      'cg_get_species_list',
      security:    nonce,  // check_ajax_referer(...,'security')
      nonce:       nonce,  // some handlers read `nonce`
      _ajax_nonce: nonce   // and some rely on WP’s default key
    };

    // Store the in-flight promise so other callers reuse it
    const p = postJSON(ajax_url, payload)
      .then(res => {
        const listRaw = Array.isArray(res) ? res : (res?.data || []);
        const list    = normalizeList(listRaw);
        this._cache.list = list;
        log('Species list fetched:', list.length);
        return list;
      })
      .fail((xhr, status, err) => {
        warn('AJAX species list failed:', status, err, xhr?.responseText);
        return [];
      })
      .always(() => {
        // Clear in-flight marker once resolved/rejected
        this._cache.listPromise = null;
      });

    this._cache.listPromise = p;
    return p;
  },

  /**
   * Populate a <select> with the species list.
   * Keeps prior selection if still present.
   * @param {HTMLElement|string} sel element or query selector
   * @param {{force?: boolean}} opts
   * @returns {Promise<HTMLElement|null>}
   */
  populateSelect(sel, { force = false } = {}) {
    const el = (sel instanceof Element) ? sel : document.querySelector(sel);
    if (!el) return $.Deferred().resolve(null).promise();

    const prior = el.value || '';
    el.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = '';
    ph.textContent = '— Select Species —';
    el.appendChild(ph);

    return this.getList(force).then(list => {
      list.forEach(({ id, name }) => {
        const o = document.createElement('option');
        o.value = id;
        o.textContent = name;
        el.appendChild(o);
      });
      if (prior && list.some(x => String(x.id) === String(prior))) {
        el.value = String(prior);
      }
      return el;
    });
  },

  /**
   * Fetch the full profile for one species (gifts, skills, etc).
   * Caches to `currentProfile` and emits a DOM event.
   * @param {string|number} speciesId
   * @returns {jqXHR|Promise<object|null>}
   */
  fetchProfile(speciesId) {
    if (!speciesId) {
      this.currentProfile = null;
      return $.Deferred().resolve(null).promise();
    }

    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) {
      warn('No AJAX URL available for fetchProfile.');
      return $.Deferred().resolve(null).promise();
    }

    log('Fetching species profile', speciesId);
    const payload = {
      action:      'cg_get_species_profile',
      species_id:  speciesId,  // preferred key
      id:          speciesId,  // fallback
      security:    nonce,
      nonce:       nonce,
      _ajax_nonce: nonce
    };

    return postJSON(ajax_url, payload)
      .then(res => {
        const profile = res?.data || res || null;
        this.currentProfile = profile || null;
        document.dispatchEvent(new CustomEvent('cg:species:profile', {
          detail: { id: String(speciesId), profile: this.currentProfile }
        }));
        return this.currentProfile;
      })
      .fail((xhr, status, err) => {
        warn('AJAX species profile failed:', status, err, xhr?.responseText);
        this.currentProfile = null;
        return null;
      });
  }
};

// Expose for console debugging
if (typeof window !== 'undefined') {
  window.SpeciesAPI = SpeciesAPI;
}

export default SpeciesAPI;
