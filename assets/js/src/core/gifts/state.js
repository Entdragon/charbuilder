// assets/js/src/core/gifts/state.js
//
// Shared Gifts state used by TraitsService to look up gifts by ID.
// Fix: ensure NEW character starts clean (no bleed from previous session),
// including clearing cached Species/Career profiles used by Traits calculations.

import FormBuilderAPI from '../formBuilder';
import SpeciesAPI from '../species/api.js';
import CareerAPI  from '../career/api.js';

const $ = window.jQuery;

const State = {
  // currently selected free-gift IDs (strings; '' means none)
  selected: ['', '', ''],

  // master list of gift objects (must include id; may include ct_gifts_manifold, etc.)
  gifts: [],

  /**
   * Pull any previously saved free_gifts/freeGifts from the builder’s data.
   */
  init() {
    const src =
      (FormBuilderAPI && typeof FormBuilderAPI === 'object' && FormBuilderAPI._data)
        ? FormBuilderAPI._data
        : (typeof FormBuilderAPI?.getData === 'function' ? FormBuilderAPI.getData() : {});

    const arr =
      Array.isArray(src.free_gifts) ? src.free_gifts :
      Array.isArray(src.freeGifts)  ? src.freeGifts  :
      null;

    const normalized = (Array.isArray(arr) ? arr : [])
      .slice(0, 3)
      .map(v => (v ? String(v) : ''));
    while (normalized.length < 3) normalized.push('');

    this.selected = normalized;
  },

  /**
   * Update one slot and persist back into FormBuilder’s live _data (when available).
   */
  set(index, id) {
    this.selected[index] = id ? String(id) : '';

    if (FormBuilderAPI && FormBuilderAPI._data) {
      FormBuilderAPI._data.free_gifts = this.selected.slice();
      FormBuilderAPI._data.freeGifts  = this.selected.slice();
    }
  },

  /**
   * Replace selected list (normalized), and persist into builder _data if present.
   */
  setSelected(list = []) {
    const normalized = (Array.isArray(list) ? list : [])
      .slice(0, 3)
      .map(v => (v ? String(v) : ''));
    while (normalized.length < 3) normalized.push('');

    this.selected = normalized;

    if (FormBuilderAPI && FormBuilderAPI._data) {
      FormBuilderAPI._data.free_gifts = this.selected.slice();
      FormBuilderAPI._data.freeGifts  = this.selected.slice();
    }
  },

  /**
   * Merge incoming gift objects into our master list.
   */
  setList(giftList = []) {
    giftList.forEach(g => {
      if (!g) return;

      const idStr = String(g.id ?? g.ct_id ?? g.gift_id ?? g.ct_gift_id ?? '');
      if (!idStr) return;

      const idx = this.gifts.findIndex(x => String(x.id) === idStr);
      if (idx > -1) {
        this.gifts[idx] = { ...this.gifts[idx], ...g, id: idStr };
      } else {
        this.gifts.push({ ...g, id: idStr });
      }
    });
  },

  /**
   * Find one gift object by its ID.
   */
  getGiftById(id) {
    return this.gifts.find(g => String(g.id) === String(id));
  }
};

// --- HARDENING: re-sync/reset between character sessions ---
//
// Problem: State.selected is a singleton and can bleed between sessions.
// ALSO: SpeciesAPI/CareerAPI currentProfile can remain populated even when UI selects are blank.
// Solution: on builder open/load, re-sync from FormBuilder data.
// On NEW character, force-clear free gifts + wipe boost-target keys + clear cached profiles,
// then fire refresh events.
(function bindGiftStateResyncOnce(){
  if (window.__CG_GIFT_STATE_BOUND__) return;
  window.__CG_GIFT_STATE_BOUND__ = true;

  function isNewCharacter(evt) {
    try {
      if (evt && evt.detail && typeof evt.detail.isNew !== 'undefined') return !!evt.detail.isNew;
    } catch (_) {}
    try {
      const d = (FormBuilderAPI && FormBuilderAPI._data) ? FormBuilderAPI._data : {};
      return !!d.isNew;
    } catch (_) {}
    return false;
  }

  function wipeBoostTargetKeys() {
    try {
      const d = (FormBuilderAPI && FormBuilderAPI._data) ? FormBuilderAPI._data : null;
      if (!d) return;

      delete d.increased_trait_career_target;
      delete d.increased_trait_career_target_0;
      delete d.increased_trait_career_target_1;
      delete d.increased_trait_career_target_2;
    } catch (_) {}
  }

  function clearCachedProfiles() {
    // These cached profiles are used by TraitsService boost calculation.
    try {
      if (SpeciesAPI) {
        SpeciesAPI.currentProfile = null;
        SpeciesAPI.currentProfileId = '';
        SpeciesAPI.currentId = '';
      }
    } catch (_) {}
    try {
      if (CareerAPI) {
        CareerAPI.currentProfile = null;
        CareerAPI.currentProfileId = '';
        CareerAPI.currentId = '';
      }
    } catch (_) {}
  }

  function emitRefresh(reason) {
    try {
      const detail = { reason: String(reason || ''), free_gifts: State.selected.slice() };

      document.dispatchEvent(new CustomEvent('cg:free-gift:changed', { detail }));
      document.dispatchEvent(new CustomEvent('cg:traits:changed', { detail }));

      // Also notify dependents that species/career are effectively blank now
      document.dispatchEvent(new CustomEvent('cg:species:changed', { detail: { id: '' } }));
      document.dispatchEvent(new CustomEvent('cg:career:changed',  { detail: { id: '' } }));

      if ($) {
        $(document).trigger('cg:free-gift:changed', [detail]);
        $(document).trigger('cg:traits:changed', [detail]);
        $(document).trigger('cg:species:changed', [{ id: '' }]);
        $(document).trigger('cg:career:changed',  [{ id: '' }]);
      }
    } catch (_) {}
  }

  function clearSelectIfPresent(selector) {
    try {
      const el = document.querySelector(selector);
      if (!el) return false;
      if (String(el.value || '') !== '') {
        el.value = '';
      }
      el.dispatchEvent(new Event('change', { bubbles: true }));
      return true;
    } catch (_) {}
    return false;
  }

  function resync(evt) {
    try { State.init(); } catch (_) {}

    if (isNewCharacter(evt)) {
      try { State.setSelected(['', '', '']); } catch (_) {}
      wipeBoostTargetKeys();
      clearCachedProfiles();

      // Ensure the UI clears AND its change handlers run (some modules only clear on change)
      // Delay slightly in case builder just re-rendered the form.
      setTimeout(() => {
        clearSelectIfPresent('#cg-species');
        clearSelectIfPresent('#cg-career');
        emitRefresh('new-character-reset');
      }, 0);

      return;
    }

    // Non-new: still ensure Traits reacts to hydrated state
    emitRefresh('resync');
  }

  document.addEventListener('cg:builder:opened', resync);
  document.addEventListener('cg:character:loaded', resync);
})();

window.CG_FreeChoicesState = State;
export default State;
