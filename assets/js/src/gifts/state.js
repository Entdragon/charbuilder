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
    console.group('[FreeChoicesState] 🔄 init() called');

    const data = FormBuilderAPI.getData();
    const initial = Array.isArray(data.freeGifts) ? data.freeGifts : ['', '', ''];

    this.selected = initial;

    console.log('[FreeChoicesState] 📥 Fetched from FormBuilderAPI →', data);
    console.log('[FreeChoicesState] ✅ Initial free gift selection set →', this.selected);
    console.groupEnd();
  },

  /**
   * Update one slot and persist back into FormBuilder’s data.
   */
  set(index, id) {
    console.group(`[FreeChoicesState] 📝 set(index=${index}, id=${id})`);

    if (index < 0 || index >= 3) {
      console.warn(`[FreeChoicesState] ⚠️ Invalid index: ${index}`);
      console.groupEnd();
      return;
    }

    this.selected[index] = id;

    const data = FormBuilderAPI.getData();
    data.freeGifts = [...this.selected];
    FormBuilderAPI._data.freeGifts = [...this.selected];  // ensure sync

    console.log('[FreeChoicesState] 📌 Updated internal selected array:', this.selected);
    console.log('[FreeChoicesState] 🔄 Synced with FormBuilderAPI._data.freeGifts:', FormBuilderAPI._data.freeGifts);
    console.groupEnd();
  },

  /**
   * Merge incoming gift objects into our master list,
   * keeping manifold, requires, etc.
   */
  setList(giftList = []) {
    console.group('[FreeChoicesState] 📦 setList() called');
    console.log('[FreeChoicesState] 💾 Received gift list with', giftList.length, 'items');

    giftList.forEach(g => {
      const idStr = String(g.id);
      const idx   = this.gifts.findIndex(x => String(x.id) === idStr);

      if (idx > -1) {
        console.log(`[FreeChoicesState] 🔁 Updating existing gift ID ${idStr}`);
        this.gifts[idx] = { ...this.gifts[idx], ...g };
      } else {
        console.log(`[FreeChoicesState] ➕ Adding new gift ID ${idStr}`);
        this.gifts.push(g);
      }
    });

    console.log('[FreeChoicesState] 📊 Updated gift cache →', this.gifts);
    console.groupEnd();
  },

  /**
   * Find one gift object by its ID.
   */
  getGiftById(id) {
    console.group(`[FreeChoicesState] 🔍 getGiftById(${id})`);
    const result = this.gifts.find(g => String(g.id) === String(id));

    if (result) {
      console.log(`[FreeChoicesState] ✅ Found gift object →`, result);
    } else {
      console.warn(`[FreeChoicesState] ❌ Gift ID ${id} not found in current list`);
    }

    console.groupEnd();
    return result;
  }
};

// expose globally for debugging
window.CG_FreeChoicesState = State;

console.log('🔥 [FreeChoicesState] Module loaded & available as CG_FreeChoicesState');

export default State;
