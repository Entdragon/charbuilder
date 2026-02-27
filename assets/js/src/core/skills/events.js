// assets/js/src/core/skills/events.js
// Skills tab event wiring.
// IMPORTANT: tab activation is now driven by the canonical cg:tab:changed event
// emitted from builder-events.js, not by clicking the panel DOM node.

import FormBuilderAPI from '../formBuilder';
import BuilderUI      from '../main/builder-ui.js';
import SkillsRender   from './render.js';

const $ = window.jQuery;

let _pendingRender = false;

function isSkillsTabActive() {
  try {
    return String($('#cg-modal .cg-tabs li.active').data('tab') || '') === 'tab-skills';
  } catch (_) {
    return false;
  }
}

function requestRender(_reason = '') {
  if (isSkillsTabActive()) {
    _pendingRender = false;
    SkillsRender.render();
  } else {
    _pendingRender = true;
  }
}

function onTabChanged(e) {
  const detail = (e && e.detail) || (e && e.originalEvent && e.originalEvent.detail) || {};
  const to = String(detail.to || '');

  if (to === 'tab-skills') {
    _pendingRender = false;
    SkillsRender.render();
  }
}

export default {
  bind() {
    // 1) Canonical tab change event
    $(document)
      .off('cg:tab:changed.cgskills')
      .on('cg:tab:changed.cgskills', onTabChanged);

    // 2) XP marks changed (from Details tab XP widget)
    $(document)
      .off('cg:xp:marks:changed.cgskills')
      .on('cg:xp:marks:changed.cgskills', () => {
        requestRender('xp marks changed');
      });

    // 3) Species/Career changes
    $(document)
      .off('change.cgskills', '#cg-species, #cg-career')
      .on('change.cgskills', '#cg-species, #cg-career', () => {
        requestRender('species/career change');
      });

    // 4) Extra careers changed
    $(document)
      .off('cg:extra-careers:changed.cgskills')
      .on('cg:extra-careers:changed.cgskills', () => {
        requestRender('extra careers changed');
      });

    // 5) Creation mark-button clicks (max 3/skill, 13 total budget)
    $(document)
      .off('click.cgskills', '.skill-mark-btn')
      .on('click.cgskills', '.skill-mark-btn', function() {
        const skillId = String($(this).data('skill-id') ?? '');
        const markRaw = parseInt($(this).data('mark'), 10);

        if (!skillId) return;
        const mark = Number.isFinite(markRaw) ? markRaw : 0;

        FormBuilderAPI._data = FormBuilderAPI._data || {};
        FormBuilderAPI._data.skillMarks = (FormBuilderAPI._data.skillMarks && typeof FormBuilderAPI._data.skillMarks === 'object')
          ? FormBuilderAPI._data.skillMarks
          : {};

        const current = parseInt(FormBuilderAPI._data.skillMarks[skillId], 10) || 0;

        // Clicking the active level toggles it off
        let next = (current === mark) ? Math.max(0, mark - 1) : mark;
        if (next < 0) next = 0;
        if (next > 3) next = 3;

        FormBuilderAPI._data.skillMarks[skillId] = next;

        try { BuilderUI.markDirty(); } catch (_) {}
        SkillsRender.render();
      });

    // 6) XP mark +/− buttons (no per-skill cap, uses xpMarksBudget)
    $(document)
      .off('click.cgskills', '.xp-skill-btn')
      .on('click.cgskills', '.xp-skill-btn', function() {
        if ($(this).prop('disabled') || $(this).hasClass('disabled')) return;

        const skillId = String($(this).data('skill-id') ?? '');
        const action  = String($(this).data('xp-action') ?? '');
        if (!skillId || !action) return;

        FormBuilderAPI._data = FormBuilderAPI._data || {};
        if (!FormBuilderAPI._data.xpSkillMarks || typeof FormBuilderAPI._data.xpSkillMarks !== 'object') {
          FormBuilderAPI._data.xpSkillMarks = {};
        }

        const xpBudget = parseInt(FormBuilderAPI._data.xpMarksBudget, 10) || 0;
        const xpPlaced = Object.values(FormBuilderAPI._data.xpSkillMarks)
          .reduce((s, v) => s + (parseInt(v, 10) || 0), 0);
        const xpRemain = xpBudget - xpPlaced;

        const cur = parseInt(FormBuilderAPI._data.xpSkillMarks[skillId], 10) || 0;

        if (action === 'plus' && xpRemain > 0) {
          FormBuilderAPI._data.xpSkillMarks[skillId] = cur + 1;
        } else if (action === 'minus' && cur > 0) {
          const newVal = cur - 1;
          if (newVal === 0) {
            delete FormBuilderAPI._data.xpSkillMarks[skillId];
          } else {
            FormBuilderAPI._data.xpSkillMarks[skillId] = newVal;
          }
        }

        try { BuilderUI.markDirty(); } catch (_) {}
        SkillsRender.render();
      });

    // Render immediately if skills tab is already visible
    if (isSkillsTabActive() || _pendingRender) {
      _pendingRender = false;
      SkillsRender.render();
    }
  }
};
