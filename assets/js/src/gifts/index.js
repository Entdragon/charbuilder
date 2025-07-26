// assets/js/src/gifts/index.js

import State          from './state.js';
import API            from './api.js';
import localKnowledge from './local-knowledge.js';
import language       from './language.js';
import freeChoices    from './free-choices.js';

console.log('🔥 [Gifts] Module loaded');

export default {
  init() {
    console.group('[Gifts] 🔁 init() called');

    console.log('[Gifts] ▶ Initializing Local Knowledge');
    localKnowledge.init();
    console.log('[Gifts] ✅ Local Knowledge initialized');

    console.log('[Gifts] ▶ Initializing Language');
    language.init();
    console.log('[Gifts] ✅ Language initialized');

    console.log('[Gifts] ▶ Initializing Free Choices');
    freeChoices.init();
    console.log('[Gifts] ✅ Free Choices initialized');

    console.groupEnd();
  }
};
