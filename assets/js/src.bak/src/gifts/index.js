// assets/js/src/gifts/index.js

import State         from './state.js';
import API           from './api.js';
import localKnowledge from './local-knowledge.js';
import language       from './language.js';
import freeChoices    from './free-choices.js';

export default {
  init() {
    // Load the pre-set gifts
    localKnowledge.init();
    language.init();

    // Then build the three free-choice dropdowns
    freeChoices.init();
  }
};
