// assets/js/src/core/gifts/state.js
//
// Shared Gifts state used by TraitsService to look up gifts by ID.
// IMPORTANT: FormBuilderAPI.getData() may return a COPY, so we prefer writing to _data.

import FormBuilderAPI from '../formBuilder';

const State = {
  // currently selected free-gift IDs (strings; '' means none)
  selected: ['', '', ''],

  // master list of gift objects (must include id; may include ct_gifts_manifold, etc.)
  gifts: [],

  /**
   * Pull any previously saved free_gifts/freeGifts from the builder’s data.
   */
  init() {
    const src = (FormBuilderAPI && typeof FormBuilderAPI === 'object' && FormBuilderAPI._data)
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
      // optional legacy alias
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
   * Merge incoming gift objects into our master list,
   * keeping manifold, requires, etc.
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

// expose globally for debugging
window.CG_FreeChoicesState = State;

export default State;
