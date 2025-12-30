// assets/js/src/core/species/index.js
// Coordinates Species API + events + select population/refetch

import SpeciesAPI from './api.js';
import bindSpeciesEvents from './events.js';

const SpeciesIndex = {
  _init: false,

  /**
   * One-time init: bind change events and populate the select if present.
   */
  init() {
    if (this._init) return;
    this._init = true;

    // Bind the #cg-species change handler (idempotent inside the module)
    bindSpeciesEvents();

    // Initial population if the select exists
    const sel = document.querySelector('#cg-species');
    if (sel) {
      SpeciesAPI.populateSelect(sel);
    }
  },

  /**
   * Refresh the species <select>. Will repopulate if empty or when force=true.
   * @param {{force?: boolean}} opts
   * @returns {Promise<void>|void}
   */
  refresh(opts = {}) {
    const sel = document.querySelector('#cg-species');
    if (!sel) return;

    const force = !!opts.force || (sel.options.length <= 1);
    return SpeciesAPI.populateSelect(sel, { force });
  }
};

export default SpeciesIndex;
