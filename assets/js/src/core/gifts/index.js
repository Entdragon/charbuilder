// assets/js/src/core/gifts/index.js
//
// Core-owned Gifts module (no self-boot).
// BuilderEvents/Core should call GiftsAPI.init().

import FreeChoices from './free-choices.js';
import State from './state.js';

export function init() {
  try { State.init(); } catch (_) {}
  try { FreeChoices.init(); } catch (_) {}
}

export function refresh(opts = {}) {
  try { FreeChoices.refresh(opts); } catch (_) {}
}

export function getSelected() {
  return (Array.isArray(State.selected) ? State.selected : []);
}

const GiftsAPI = { init, refresh, getSelected };
export default GiftsAPI;
