// public API for Traits

import TraitsService from './service.js';
import TraitsEvents  from './events.js';

console.log('🔥 [Traits] Module loaded');

export default {
  init() {
    console.group('[Traits] 🔁 init()');

    console.log('[Traits] ▶ Calling TraitsService.refreshAll()');
    TraitsService.refreshAll();
    console.log('[Traits] ✅ TraitsService.refreshAll() complete');

    console.log('[Traits] ▶ Binding TraitsEvents');
    TraitsEvents.bind();
    console.log('[Traits] ✅ TraitsEvents.bind() complete');

    console.groupEnd();
  },

  // Expose for other modules
  getBoostedDie: TraitsService.getBoostedDie.bind(TraitsService)
};
