// assets/js/src/gifts/api.js

import State from './state.js';
const $ = window.jQuery;

console.log('🔥 [GiftsAPI] Module loaded');

export default {
  fetchLocalKnowledge(cb) {
    console.log('[GiftsAPI] 📡 fetchLocalKnowledge() called');

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_local_knowledge',
      security: CG_Ajax.nonce
    })
    .done(res => {
      console.log('[GiftsAPI] ✅ Response from cg_get_local_knowledge:', res);

      if (res.success && typeof cb === 'function') {
        console.log('[GiftsAPI] 📞 Calling callback with data:', res.data);
        cb(res.data);
      } else {
        console.warn('[GiftsAPI] ❌ Unsuccessful or invalid callback');
      }
    })
    .fail((xhr, status, error) => {
      console.error('[GiftsAPI] ❌ AJAX error in fetchLocalKnowledge:', { status, error, response: xhr.responseText });
    });
  },

  fetchLanguageGift(cb) {
    console.log('[GiftsAPI] 📡 fetchLanguageGift() called');

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_language_gift',
      security: CG_Ajax.nonce
    })
    .done(res => {
      console.log('[GiftsAPI] ✅ Response from cg_get_language_gift:', res);

      if (res.success && typeof cb === 'function') {
        console.log('[GiftsAPI] 📞 Calling callback with data:', res.data);
        cb(res.data);
      } else {
        console.warn('[GiftsAPI] ❌ Unsuccessful or invalid callback');
      }
    })
    .fail((xhr, status, error) => {
      console.error('[GiftsAPI] ❌ AJAX error in fetchLanguageGift:', { status, error, response: xhr.responseText });
    });
  },

  fetchFreeChoices(cb) {
    console.log('[GiftsAPI] 📡 fetchFreeChoices() called');

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_free_gifts',
      security: CG_Ajax.nonce
    })
    .done(res => {
      console.log('[GiftsAPI] ✅ Response from cg_get_free_gifts:', res);

      if (!res.success || typeof cb !== 'function') {
        console.warn('[GiftsAPI] ❌ Unsuccessful or no callback provided');
        return;
      }

      const gifts = res.data.map(g => {
        const parsed = {
          ...g,
          id: String(g.id),
          name: g.name,
          ct_gifts_manifold: parseInt(g.ct_gifts_manifold, 10) || 1
        };
        console.log(`[GiftsAPI] 🧪 Parsed gift → ID: ${parsed.id}, Name: "${parsed.name}", Manifold: ${parsed.ct_gifts_manifold}`);
        return parsed;
      });

      console.log('[GiftsAPI] 📦 Parsed gift list ready. Calling callback...');
      cb(gifts);
    })
    .fail((xhr, status, error) => {
      console.error('[GiftsAPI] ❌ AJAX error in fetchFreeChoices:', { status, error, response: xhr.responseText });
    });
  }
};
