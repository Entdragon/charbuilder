// assets/js/src/core/summary/index.js
//
// Summary module entry.
// - Expose SummaryAPI on window at module-load for console debugging.
// - Do NOT auto-init here; init() is still called by builder-refresh when Summary tab is shown.

import SummaryAPI from './api.js';

try {
  if (typeof window !== 'undefined') {
    window.SummaryAPI = window.SummaryAPI || SummaryAPI;
  }
} catch (_) {}

export default {
  init() {
    SummaryAPI.init();
  }
};
