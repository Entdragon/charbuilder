// assets/js/src/core/career/index.js
// Coordinates Career API + events + select population/refetch

import CareerAPI from './api.js';
import bindCareerEvents from './events.js';

const CareerIndex = {
  _init: false,

  /**
   * One-time init: bind change events and populate the select if present.
   */
  init() {
    if (this._init) return;
    this._init = true;

    // Bind the #cg-career change handler (idempotent inside the module)
    bindCareerEvents();

    // Initial population if the select exists
    const sel = document.querySelector('#cg-career');
    if (sel) {
      CareerAPI.populateSelect(sel).catch?.(() => {});
    }
  },

  /**
   * Refresh the career <select>. Will repopulate if empty or when force=true.
   * @param {{force?: boolean}} opts
   * @returns {Promise<void>|void}
   */
  refresh(opts = {}) {
    const sel = document.querySelector('#cg-career');
    if (!sel) return;

    const force = !!opts.force || (sel.options.length <= 1);
    return CareerAPI.populateSelect(sel, { force }).catch?.(() => {});
  }
};

export default CareerIndex;
