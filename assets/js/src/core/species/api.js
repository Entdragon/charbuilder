// assets/js/src/core/species/api.js
// Reliable Species data access with localized-globals first and AJAX fallback.

const log  = (...a) => console.log('[SpeciesAPI]', ...a);
const warn = (...a) => console.warn('[SpeciesAPI]', ...a);
const $ = window.jQuery;

const SpeciesAPI = {
  _cache: {
    list: null,
  },

  getList() {
    // Prefer the localized global first (instant, no network)
    if (Array.isArray(window.CG_SPECIES_LIST) && window.CG_SPECIES_LIST.length) {
      this._cache.list = window.CG_SPECIES_LIST.slice();
      return $.Deferred().resolve(this._cache.list).promise();
    }

    // Fallback to AJAX
    if (!window.CG_Ajax || !window.CG_Ajax.ajax_url) {
      warn('No CG_Ajax available; returning empty list.');
      return $.Deferred().resolve([]).promise();
    }

    if (this._cache.list) {
      return $.Deferred().resolve(this._cache.list).promise();
    }

    log('Fetching species via AJAX…');
    return $.post(window.CG_Ajax.ajax_url, {
      action: 'cg_get_species_list',
      _ajax_nonce: window.CG_Ajax.nonce
    })
    .then(res => {
      try { res = (typeof res === 'string') ? JSON.parse(res) : res; } catch(e){}
      const list = Array.isArray(res) ? res : (res?.data || []);
      this._cache.list = list;
      log('Species list fetched:', list.length);
      return list;
    })
    .fail((xhr, status, err) => {
      warn('AJAX species list failed:', status, err);
      return [];
    });
  },

  populateSelect(sel) {
    const el = (sel instanceof Element) ? sel : document.querySelector(sel);
    if (!el) return;

    el.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = ''; ph.textContent = '— Select Species —';
    el.appendChild(ph);

    return this.getList().then(list => {
      list.forEach(({id, name}) => {
        const o = document.createElement('option');
        o.value = id; o.textContent = name;
        el.appendChild(o);
      });
      return el;
    });
  },

  fetchProfile(speciesId) {
    // Optional: used by downstream UI (traits, gifts, bio blocks).
    if (!speciesId) return $.Deferred().resolve(null).promise();
    if (!window.CG_Ajax || !window.CG_Ajax.ajax_url) {
      warn('No CG_Ajax available for fetchProfile.');
      return $.Deferred().resolve(null).promise();
    }
    log('Fetching species profile', speciesId);
    return $.post(window.CG_Ajax.ajax_url, {
      action: 'cg_get_species_profile',
      species_id: speciesId,
      _ajax_nonce: window.CG_Ajax.nonce
    }).then(res => {
      try { return (typeof res === 'string') ? JSON.parse(res) : res; }
      catch(e){ return res; }
    });
  }
};

export default SpeciesAPI;
