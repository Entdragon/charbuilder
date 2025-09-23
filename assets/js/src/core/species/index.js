// assets/js/src/core/species/index.js
// Coordinates species API + events + population

import SpeciesAPI from './api.js';
import bindSpeciesEvents from './events.js';

const SpeciesIndex = {
  _init: false,

  init() {
    if (this._init) return;
    this._init = true;

    bindSpeciesEvents();
    SpeciesAPI.populateSelect('#cg-species');
  }
};

export default SpeciesIndex;
