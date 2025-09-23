// assets/js/src/core/career/api.js
// Career list from localized globals with AJAX fallback.

const log  = (...a) => console.log('[CareerAPI]', ...a);
const warn = (...a) => console.warn('[CareerAPI]', ...a);
const $ = window.jQuery;

const CareerAPI = {
  _cache: { list: null },

  getList() {
    if (Array.isArray(window.CG_CAREERS_LIST) && window.CG_CAREERS_LIST.length) {
      this._cache.list = window.CG_CAREERS_LIST.slice();
      return $.Deferred().resolve(this._cache.list).promise();
    }
    if (!window.CG_Ajax || !window.CG_Ajax.ajax_url) {
      warn('No CG_Ajax available; returning empty career list.');
      return $.Deferred().resolve([]).promise();
    }
    if (this._cache.list) {
      return $.Deferred().resolve(this._cache.list).promise();
    }

    log('Fetching careers via AJAX…');
    return $.post(window.CG_Ajax.ajax_url, {
      action: 'cg_get_careers_list',
      _ajax_nonce: window.CG_Ajax.nonce
    })
    .then(res => {
      try { res = (typeof res === 'string') ? JSON.parse(res) : res; } catch(e){}
      const list = Array.isArray(res) ? res : (res?.data || []);
      this._cache.list = list;
      log('Career list fetched:', list.length);
      return list;
    })
    .fail((xhr, status, err) => {
      warn('AJAX careers failed:', status, err);
      return [];
    });
  },

  populateSelect(sel) {
    const el = (sel instanceof Element) ? sel : document.querySelector(sel);
    if (!el) return;

    el.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = ''; ph.textContent = '— Select Career —';
    el.appendChild(ph);

    return this.getList().then(list => {
      list.forEach(({id, name}) => {
        const o = document.createElement('option');
        o.value = id; o.textContent = name;
        el.appendChild(o);
      });
      return el;
    });
  }
};

export default CareerAPI;
