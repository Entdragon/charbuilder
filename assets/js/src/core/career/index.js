// assets/js/src/core/career/index.js
// Coordinates career API + events + population

import CareerAPI from './api.js';
import bindCareerEvents from './events.js';

const CareerIndex = {
  _init: false,

  init() {
    if (this._init) return;
    this._init = true;

    bindCareerEvents();
    CareerAPI.populateSelect('#cg-career');
  }
};

export default CareerIndex;
