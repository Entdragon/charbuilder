// assets/js/src/gifts/state.js

import FormBuilderAPI from '../core/formBuilder';

const State = {
  // currently selected free‐gift IDs
  selected: [],

  // the last fetched gift objects (with name, manifold, requires…)
  gifts: [],

  /**
   * Pull any previously saved freeGifts from the builder’s data.
   */
  init() {
    const data = FormBuilderAPI.getData();
    this.selected = Array.isArray(data.freeGifts)
      ? data.freeGifts
      : ['', '', ''];
  },

  /**
   * Update one slot and persist back into FormBuilder’s data.
   */
  set(index, id) {
    this.selected[index] = id;
    const data = FormBuilderAPI.getData();
    data.freeGifts = this.selected;
  },

  /**
   * Merge incoming gift objects into our master list,
   * keeping manifold, requires, etc.
   */
  setList(giftList = []) {
    giftList.forEach(g => {
      const idStr = String(g.id);
      const idx   = this.gifts.findIndex(x => String(x.id) === idStr);
      if (idx > -1) {
        // update existing object
        this.gifts[idx] = { ...this.gifts[idx], ...g };
      } else {
        // add new gift
        this.gifts.push(g);
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
