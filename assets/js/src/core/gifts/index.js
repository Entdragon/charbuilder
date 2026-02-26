// assets/js/src/core/gifts/index.js
//
// Core-owned Gifts module (no self-boot).
// BuilderEvents/Core should call GiftsAPI.init().
//
// IMPORTANT (Jan 2026):
// Gifts UI (free-choice dropdowns) is tab-gated and may be initialized only
// when the user first enters the Gifts tab. In that case the earlier
// cg:builder:opened / cg:tab:changed events may have already fired.
// So we force a refresh on init to ensure the dropdowns populate immediately.

import FreeChoices from './free-choices.js';
import State from './state.js';
import QualUI from '../quals/ui.js';

export function init() {
  try { State.init(); } catch (_) {}
  try { FreeChoices.init(); } catch (_) {}
  try { QualUI.init(); } catch (_) {}

  // Ensure data is fetched + UI fills when Gifts tab is opened the first time.
  // refresh() is safe to call anytime; it will only touch DOM when Gifts tab is active.
  try { FreeChoices.refresh({ force: false }); } catch (_) {}
}

export function refresh(opts = {}) {
  try { FreeChoices.refresh(opts); } catch (_) {}
}

export function getSelected() {
  return (Array.isArray(State.selected) ? State.selected : []);
}

const GiftsAPI = { init, refresh, getSelected };
export default GiftsAPI;
