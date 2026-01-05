// assets/js/src/core/skills/index.js
//
// TAB-RESTRUCTURE HARDENING (Dec 2025):
// - Skills should NOT render or touch DOM unless the Skills tab is active.
// - Events can bind anytime (idempotent), but rendering is gated.
// - Debug logging is opt-in via: window.CG_DEBUG_SKILLS = true

import FormBuilderAPI from '../formBuilder';
import SkillsEvents   from './events.js';
import SkillsRender   from './render.js';

const $ = window.jQuery;

let _inited = false;

function isSkillsTabActive() {
  try {
    // Primary: active tab li in the modal
    const li  = document.querySelector('#cg-modal .cg-tabs li.active');
    const tab = li ? (li.getAttribute('data-tab') || '') : '';
    if (tab === 'tab-skills') return true;

    // Fallback: active panel class
    const panel = document.getElementById('tab-skills');
    if (panel && panel.classList.contains('active')) return true;
  } catch (_) {}
  return false;
}

export default {
  init() {
    if (!_inited) {
      _inited = true;

      if (window.CG_DEBUG_SKILLS) {
        console.log('[SkillsIndex] init — builder state:', FormBuilderAPI._data);
      }

      // Ensure we have the localized skills list
      if (!Array.isArray(FormBuilderAPI._data.skillsList)) {
        FormBuilderAPI._data.skillsList = window.CG_SKILLS_LIST || [];
      }

      // Initialize marks-allocation storage if missing
      if (typeof FormBuilderAPI._data.skillMarks !== 'object' || !FormBuilderAPI._data.skillMarks) {
        FormBuilderAPI._data.skillMarks = {};
      }

      // Wire up handlers (idempotent)
      SkillsEvents.bind();
    }

    // Render ONLY if Skills tab is active (render.js is also gated)
    if (isSkillsTabActive()) {
      SkillsRender.render();
    }
  }
};
