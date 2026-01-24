// assets/js/src/core/summary/index.js
//
// Summary module entry.
// - Expose SummaryAPI on window at module-load for console debugging.
// - Bind live auto-render at module-load so Summary updates even before visiting the Summary tab.
// - Do NOT auto-call SummaryAPI.init() here; init() is still called by builder-refresh when Summary tab is shown.

import SummaryAPI from './api.js';

try {
  if (typeof window !== 'undefined') {
    window.SummaryAPI = window.SummaryAPI || SummaryAPI;

    // CG HARDEN: bind auto-render at module load (no tab switch required)
    if (window.SummaryAPI && typeof window.SummaryAPI.bindAutoRender === 'function') {
      window.SummaryAPI.bindAutoRender();
    }
  }
} catch (_) {}

export default {
  init() {
    SummaryAPI.init();
  }
};
