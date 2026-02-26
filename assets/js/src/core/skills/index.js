// assets/js/src/core/skills/index.js
//
// TAB-RESTRUCTURE HARDENING (Dec 2025):
// - Skills should NOT render or touch DOM unless the Skills tab is active.
// - Events can bind anytime (idempotent), but rendering is gated.
//
// STANDALONE APP (Feb 2026):
// - window.CG_SKILLS_LIST is not injected server-side (no wp_localize_script).
// - On first init, fetch skills list via cg_get_skills_list AJAX if not already loaded.

import FormBuilderAPI from '../formBuilder';
import SkillsEvents   from './events.js';
import SkillsRender   from './render.js';

const $ = window.jQuery;

let _inited    = false;
let _fetching  = false;
let _fetchDone = false;

function isSkillsTabActive() {
  try {
    const li  = document.querySelector('#cg-modal .cg-tabs li.active');
    const tab = li ? (li.getAttribute('data-tab') || '') : '';
    if (tab === 'tab-skills') return true;

    const panel = document.getElementById('tab-skills');
    if (panel && panel.classList.contains('active')) return true;
  } catch (_) {}
  return false;
}

function ajaxEnv() {
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  const ajax_url =
    env.ajax_url ||
    window.ajaxurl ||
    document.body?.dataset?.ajaxUrl ||
    '/wp-admin/admin-ajax.php';
  const nonce = env.nonce || env.security || window.CG_NONCE || null;
  return { ajax_url, nonce };
}

function fetchSkillsList() {
  if (_fetchDone || _fetching) return;
  _fetching = true;

  const { ajax_url, nonce } = ajaxEnv();
  const payload = {
    action:      'cg_get_skills_list',
    security:    nonce,
    _ajax_nonce: nonce,
    nonce:       nonce
  };

  $.post(ajax_url, payload)
    .then(res => {
      _fetching = false;
      _fetchDone = true;

      let list = [];
      if (res && res.success && Array.isArray(res.data)) {
        list = res.data;
      } else if (Array.isArray(res)) {
        list = res;
      }

      // Normalise to [{id, name}]
      list = list.map(s => ({
        id:   String(s.id ?? s.skill_id ?? ''),
        name: String(s.name ?? s.ct_skill_name ?? s.skill_name ?? '')
      })).filter(s => s.id && s.name);

      window.CG_SKILLS_LIST = list;
      FormBuilderAPI._data = FormBuilderAPI._data || {};
      FormBuilderAPI._data.skillsList = list;

      if (isSkillsTabActive()) {
        SkillsRender.render();
      }
    })
    .catch(err => {
      _fetching = false;
      console.warn('[SkillsIndex] cg_get_skills_list fetch failed:', err);
    });
}

export default {
  init() {
    if (!_inited) {
      _inited = true;

      FormBuilderAPI._data = FormBuilderAPI._data || {};

      // Seed from localized global if available (WordPress classic mode)
      if (!Array.isArray(FormBuilderAPI._data.skillsList) || !FormBuilderAPI._data.skillsList.length) {
        if (Array.isArray(window.CG_SKILLS_LIST) && window.CG_SKILLS_LIST.length) {
          FormBuilderAPI._data.skillsList = window.CG_SKILLS_LIST;
          _fetchDone = true;
        }
      } else {
        _fetchDone = true;
      }

      if (typeof FormBuilderAPI._data.skillMarks !== 'object' || !FormBuilderAPI._data.skillMarks) {
        FormBuilderAPI._data.skillMarks = {};
      }

      SkillsEvents.bind();
    }

    // If list is missing, fetch it; render will fire automatically after fetch.
    if (!_fetchDone) {
      fetchSkillsList();
      return;
    }

    // List is ready — render now if tab is active.
    if (isSkillsTabActive()) {
      SkillsRender.render();
    }
  },

  // Allow external callers (e.g. character load) to reset the fetch gate
  // so a fresh skills list is re-fetched if the DB changes.
  reset() {
    _fetchDone = false;
    _fetching  = false;
  }
};
