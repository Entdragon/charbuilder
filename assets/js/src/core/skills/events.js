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
  // If skills tab is active, render immediately; otherwise mark pending.
  if (isSkillsTabActive()) {
    _pendingRender = false;
    SkillsRender.render();
  } else {
    _pendingRender = true;
  }
}

function onTabChanged(e) {
  // Support both native CustomEvent and jQuery-wrapped events.
  const detail = (e && e.detail) || (e && e.originalEvent && e.originalEvent.detail) || {};
  const to = String(detail.to || '');

  if (to === 'tab-skills') {
    // On entry to Skills tab, always render (also clears pending).
    _pendingRender = false;
    SkillsRender.render();
  }
}

export default {
  bind() {
    // 1) Canonical tab change event (preferred)
    $(document)
      .off('cg:tab:changed.cgskills')
      .on('cg:tab:changed.cgskills', onTabChanged);

    // 2) Species/Career changes: refresh Skills, but only render immediately if Skills tab is active.
    // (Otherwise we mark pending and the render happens when Skills tab is opened.)
    $(document)
      .off('change.cgskills', '#cg-species, #cg-career')
      .on('change.cgskills', '#cg-species, #cg-career', () => {
        requestRender('species/career change');
      });

    // 3) Extra careers: same behavior (pending unless tab active)
    $(document)
      .off('cg:extra-careers:changed.cgskills')
      .on('cg:extra-careers:changed.cgskills', () => {
        requestRender('extra careers changed');
      });

    // 4) Handle mark-button clicks (persist into FormBuilderAPI._data)
    $(document)
      .off('click.cgskills', '.skill-mark-btn')
      .on('click.cgskills', '.skill-mark-btn', function() {
        const skillId = String($(this).data('skill-id') ?? '');
        const markRaw = parseInt($(this).data('mark'), 10);

        if (!skillId) return;
        const mark = Number.isFinite(markRaw) ? markRaw : 0;

        // Ensure state exists
        FormBuilderAPI._data = FormBuilderAPI._data || {};
        FormBuilderAPI._data.skillMarks = (FormBuilderAPI._data.skillMarks && typeof FormBuilderAPI._data.skillMarks === 'object')
          ? FormBuilderAPI._data.skillMarks
          : {};

        const current = parseInt(FormBuilderAPI._data.skillMarks[skillId], 10) || 0;

        // UX: clicking the same active mark decrements by 1 (allows reaching 0)
        let next = (current === mark) ? Math.max(0, mark - 1) : mark;

        // Clamp to expected range
        if (next < 0) next = 0;
        if (next > 3) next = 3;

        FormBuilderAPI._data.skillMarks[skillId] = next;

        // Mark dirty and re-render table (render reads from state)
        try { BuilderUI.markDirty(); } catch (_) {}
        SkillsRender.render();
      });

    // If we bind while Skills is already visible, render once.
    if (isSkillsTabActive() || _pendingRender) {
      _pendingRender = false;
      SkillsRender.render();
    }
  }
};
