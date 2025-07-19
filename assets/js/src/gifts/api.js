// assets/js/src/gifts/api.js

import State from './state.js';
const $ = window.jQuery;

export default {
  fetchLocalKnowledge(cb) {
    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_local_knowledge',
      security: CG_Ajax.nonce
    }).done(res => {
      if (res.success && typeof cb === 'function') cb(res.data);
    });
  },

  fetchLanguageGift(cb) {
    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_language_gift',
      security: CG_Ajax.nonce
    }).done(res => {
      if (res.success && typeof cb === 'function') cb(res.data);
    });
  },

  fetchFreeChoices(cb) {
    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_free_gifts',
      security: CG_Ajax.nonce
    }).done(res => {
      if (!res.success || typeof cb !== 'function') return;

      // Map each gift to ensure we have a numeric manifold count
      const gifts = res.data.map(g => ({
        // carry through all original fields
        ...g,

        // ensure id is string, name is present, and manifold is a Number
        id: String(g.id),
        name: g.name,
        ct_gifts_manifold: parseInt(g.ct_gifts_manifold, 10) || 1
      }));

      cb(gifts);
    });
  }
};
