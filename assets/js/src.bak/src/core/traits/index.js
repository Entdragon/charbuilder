// public API for Traits

import TraitsService from './service.js';
import TraitsEvents  from './events.js';

export default {
  init() {
    // Enforce and display initial trait limits
    TraitsService.refreshAll();

    // Wire up change handlers
    TraitsEvents.bind();
  },

  // Expose for other modules
  getBoostedDie: TraitsService.getBoostedDie.bind(TraitsService)
};
