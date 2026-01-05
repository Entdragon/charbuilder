// assets/js/src/core/career/api.js
// Reliable Career data access with localized-globals first and AJAX fallback.
// IMPORTANT: adds in-flight de-dupe so multiple callers don't spam AJAX.
// Matches your PHP endpoints:
//   - cg_get_career_list   → [{ id, name }]
//   - cg_get_career_gifts  → profile object (careerName, gift_*, gift_id_*, manifold_*, skill_one/two/three)

const log  = (...a) => console.log('[CareerAPI]', ...a);
const warn = (...a) => console.warn('[CareerAPI]', ...a);
const $    = window.jQuery;

/** Collect AJAX env (mu-plugin, legacy, or WP ajaxurl) */
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

/** Normalize various list shapes into [{id,name}] */
function normalizeList(raw) {
  if (!Array.isArray(raw)) return [];
  return raw.map(it => {
    if (it && typeof it === 'object') {
      const out = {
        id:   String(it.id ?? it.value ?? ''),
        name: String(it.name ?? it.title ?? '')
      };

      // CG_CAREERLIST_EXTFIELDS: preserve extra fields returned by PHP list endpoint
      // so Extra Careers can compute eligibility without N AJAX calls.
      ['gift_id_1','gift_id_2','gift_id_3','skill_one','skill_two','skill_three'].forEach(k => {
        if (it[k] != null && String(it[k]) !== '') out[k] = String(it[k]);
      });

      return out;
    }
    return { id: String(it), name: String(it) };
  }).filter(x => x.id && x.name);
}


/**
 * Try preloaded globals (fast path)
 * NOTE: hardener localizes CG_CAREERS_LIST (plural). Older code sometimes used CG_CAREER_LIST (singular).
 * We accept both to avoid unnecessary AJAX calls.
 */
function preloadedList() {
  // Preferred (hardener)
  if (Array.isArray(window.CG_CAREERS_LIST) && window.CG_CAREERS_LIST.length) {
    return normalizeList(window.CG_CAREERS_LIST);
  }

  // Legacy/singular
  if (Array.isArray(window.CG_CAREER_LIST) && window.CG_CAREER_LIST.length) {
    return normalizeList(window.CG_CAREER_LIST);
  }

  // Other legacy shapes
  if (window.CG_DATA && Array.isArray(window.CG_DATA.careers) && window.CG_DATA.careers.length) {
    return normalizeList(window.CG_DATA.careers);
  }
  if (Array.isArray(window.cgCareers) && window.cgCareers.length) {
    return normalizeList(window.cgCareers);
  }
  return null;
}

/** Post + lenient JSON parse (stay in jQuery Deferred world) */
function postJSON(url, data) {
  return $.post(url, data).then(res => {
    try { return (typeof res === 'string') ? JSON.parse(res) : res; }
    catch (_) { return res; }
  });
}

/** Coerce profile so downstream code always has predictable keys */
function normalizeCareerProfileShape(profile = {}) {
  const p = (profile && typeof profile === 'object') ? { ...profile } : {};

  // Name aliases
  if (!p.careerName && p.career_name) p.careerName = p.career_name;
  if (!p.career_name && p.careerName) p.career_name = p.careerName;
  if (!p.careerName && !p.career_name && p.name) p.careerName = p.name;

  // Ensure skill ids are strings
  ['skill_one', 'skill_two', 'skill_three'].forEach(k => {
    if (p[k] != null) p[k] = String(p[k]);
  });

  // Gift IDs as strings
  ['gift_id_1', 'gift_id_2', 'gift_id_3'].forEach(k => {
    if (p[k] != null) p[k] = String(p[k]);
  });

  // Manifolds as Numbers (default 1)
  ['manifold_1', 'manifold_2', 'manifold_3'].forEach(k => {
    if (p[k] != null) p[k] = parseInt(p[k], 10) || 1;
  });

  return p;
}

/** Convert normalized profile → array of gift names (with × manifold if >1) */
function giftNamesFromProfile(profile = {}) {
  const names = [profile.gift_1, profile.gift_2, profile.gift_3]
    .map(v => (v == null ? '' : String(v)))
    .filter(Boolean);

  return names.map((name, i) => {
    const m = profile[`manifold_${i + 1}`];
    const mult = parseInt(m, 10) || 1;
    return mult > 1 ? `${name} × ${mult}` : name;
  });
}

const CareerAPI = {
  _cache: { list: null, listPromise: null },
  currentProfile: null,
    currentProfileId: null, // CG_CAREERAPI_PROFILE_RACE_GUARD

  clearCache() {
    this._cache.list = null;
    this._cache.listPromise = null;
  },

  /** getList(force=false) → Promise resolving to [{id,name}] */
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

    log('Fetching careers via AJAX…');
    const payload = {
      action:      'cg_get_career_list',
      security:    nonce,
      nonce:       nonce,
      _ajax_nonce: nonce
    };

    const p = postJSON(ajax_url, payload)
      .then(res => {
        const listRaw = Array.isArray(res) ? res : (res?.data || []);
        const list    = normalizeList(listRaw);
        this._cache.list = list;
        log('Career list fetched:', list.length);
        return list;
      })
      .fail((xhr, status, err) => {
        warn('AJAX career list failed:', status, err, xhr?.responseText);
        return [];
      })
      .always(() => {
        this._cache.listPromise = null;
      });

    this._cache.listPromise = p;
    return p;
  },

  /** populateSelect(sel) → Promise resolving to the populated select */
  populateSelect(sel, { force = false } = {}) {
    const el = (sel instanceof Element) ? sel : document.querySelector(sel);
    if (!el) return $.Deferred().resolve(null).promise();

    const prior = el.value || '';
    el.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = '';
    ph.textContent = '— Select Career —';
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
   * fetchProfile(careerId) → Promise resolving to normalized profile (or null)
   * IMPORTANT: Your PHP returns the full profile from `cg_get_career_gifts`,
   * so we call that here and treat the response as the profile.
   */
  fetchProfile(careerId) {
    // CG_CAREERAPI_PROFILE_RACE_GUARD: guard against out-of-order AJAX profile responses
    const wantedId = String(careerId || '');
    if (!wantedId) {
      this.currentProfile = null;
      this.currentProfileId = null;
      return $.Deferred().resolve(null).promise();
    }
    this.currentProfileId = wantedId;

    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) {
      warn('No AJAX URL available for fetchProfile.');
      return $.Deferred().resolve(null).promise();
    }

    log('Fetching career profile', careerId);
    const payload = {
      action:      'cg_get_career_gifts',
      id:          careerId,
      security:    nonce,
      nonce:       nonce,
      _ajax_nonce: nonce
    };

    return postJSON(ajax_url, payload)
      .then(res => {
        const profRaw =
          (res && res.success === true) ? res.data :
          (res && res.success === undefined) ? res :
          null;

        if (!profRaw) {
          warn('Career profile response missing/invalid:', res);
          if (String(this.currentProfileId || '') === String(wantedId)) {
            this.currentProfile = null;
          }
          return null;
        }

        const normalized = normalizeCareerProfileShape(profRaw);
        this.currentProfile = normalized;
        return normalized;
      })
      .fail((xhr, status, err) => {
        warn('AJAX career profile failed:', status, err, xhr?.responseText);
        this.currentProfile = null;
        return null;
      });
  },

  /**
   * fetchGifts(careerId) → Promise<string[]>
   * For compatibility with callers that “just want gift names”
   */
  fetchGifts(careerId) {
    return this.fetchProfile(careerId).then(profile => {
      if (!profile) return [];
      return giftNamesFromProfile(profile);
    });
  }
};

// Expose for console debugging
if (typeof window !== 'undefined') {
  window.CareerAPI = CareerAPI;
}

export default CareerAPI;
