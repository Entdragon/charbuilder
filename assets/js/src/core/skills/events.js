// assets/js/src/core/skills/events.js
// Skills tab event wiring.
// IMPORTANT: tab activation is now driven by the canonical cg:tab:changed event
// emitted from builder-events.js, not by clicking the panel DOM node.

import FormBuilderAPI from '../formBuilder';
import BuilderUI      from '../main/builder-ui.js';
import SkillsRender   from './render.js';

const $ = window.jQuery;

let _pendingRender = false;
let _onSpeciesProfile = null;
let _onCareerProfile  = null;

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

    // 3b) Re-render when species or career profiles finish loading
    //     (profile arrives asynchronously; the first render may have had null profiles)
    if (_onSpeciesProfile) document.removeEventListener('cg:species:profile', _onSpeciesProfile, true);
    _onSpeciesProfile = () => requestRender('species profile loaded');
    document.addEventListener('cg:species:profile', _onSpeciesProfile, true);

    if (_onCareerProfile) document.removeEventListener('cg:career:profile', _onCareerProfile, true);
    _onCareerProfile = () => requestRender('career profile loaded');
    document.addEventListener('cg:career:profile', _onCareerProfile, true);

    // 4) Extra careers changed
    $(document)
      .off('cg:extra-careers:changed.cgskills')
      .on('cg:extra-careers:changed.cgskills', () => {
        requestRender('extra careers changed');
      });

    // 5) Gift skill marks changed (from Gifts tab Knack For selection)
    $(document)
      .off('cg:gift-skill-marks:changed.cgskills')
      .on('cg:gift-skill-marks:changed.cgskills', () => {
        requestRender('gift skill marks changed');
      });

    // 6) Creation mark-button clicks (max 3/skill, 13 total budget)
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

    // 7) XP mark +/− buttons (no per-skill cap, uses xpMarksBudget)
    $(document)
      .off('click.cgskills', '.xp-skill-btn')
      .on('click.cgskills', '.xp-skill-btn', function() {
        if ($(this).prop('disabled') || $(this).hasClass('disabled')) return;

        const skillId = String($(this).data('skill-id') ?? '');
        const action  = String($(this).data('xp-action') ?? '');
        if (!skillId || !action) return;

        FormBuilderAPI._data = FormBuilderAPI._data || {};
        if (!FormBuilderAPI._data.xpSkillMarks
            || typeof FormBuilderAPI._data.xpSkillMarks !== 'object'
            || Array.isArray(FormBuilderAPI._data.xpSkillMarks)) {
          // Convert stale array (PHP decoded [] as an array) to a plain object
          const stale = FormBuilderAPI._data.xpSkillMarks;
          const recovered = {};
          if (Array.isArray(stale)) {
            Object.keys(stale).forEach(k => { const n = parseInt(stale[k], 10) || 0; if (n > 0) recovered[k] = n; });
          }
          FormBuilderAPI._data.xpSkillMarks = recovered;
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

    // 8) Favourite use text input (debounced to avoid thrashing on every keystroke)
    let _favDebounce = null;
    $(document)
      .off('input.cgskills', '.skill-fav-input')
      .on('input.cgskills', '.skill-fav-input', function() {
        const skillId = String($(this).data('skill-id') ?? '');
        const val     = String($(this).val() ?? '');
        if (!skillId) return;

        FormBuilderAPI._data = FormBuilderAPI._data || {};
        if (!FormBuilderAPI._data.skill_notes
            || typeof FormBuilderAPI._data.skill_notes !== 'object'
            || Array.isArray(FormBuilderAPI._data.skill_notes)) {
          const staleNotes = FormBuilderAPI._data.skill_notes;
          const recovered = {};
          if (Array.isArray(staleNotes)) {
            Object.keys(staleNotes).forEach(k => { if (staleNotes[k]) recovered[k] = String(staleNotes[k]); });
          }
          FormBuilderAPI._data.skill_notes = recovered;
        }

        if (val) {
          FormBuilderAPI._data.skill_notes[skillId] = val;
        } else {
          delete FormBuilderAPI._data.skill_notes[skillId];
        }

        // Charge 1 XP when changing a previously-committed fav use (not first-time set).
        // favUseOriginal is snapshotted from loaded skill_notes on init().
        // favUsePaid tracks which skills have already been charged this session.
        try {
          const original = (FormBuilderAPI._data.favUseOriginal || {})[skillId];
          const paid     = FormBuilderAPI._data.favUsePaid || {};
          // Only charge if: there was a committed value AND the new text differs AND not yet charged
          if (original !== undefined && original !== '' && val !== original && !paid[skillId]) {
            paid[skillId] = true;
            FormBuilderAPI._data.favUsePaid = paid;

            // Resolve skill name from the global skills list
            const skillList  = window.CG_SKILLS_LIST || [];
            const skillEntry = Array.isArray(skillList) ? skillList.find(s => String(s.id) === skillId) : null;
            const skillName  = skillEntry ? String(skillEntry.name || skillId) : skillId;

            // Add 1-XP retrain-log entry (same mechanism as the retrain system)
            const log = Array.isArray(FormBuilderAPI._data.retrainLog) ? [...FormBuilderAPI._data.retrainLog] : [];
            log.push({
              type:      'fav_use',
              skill_id:  skillId,
              note:      `Changed favourite use: "${skillName}"`,
              cost:      1,
              timestamp: Date.now(),
            });
            FormBuilderAPI._data.retrainLog     = log;
            FormBuilderAPI._data.retrainPenalty = (parseInt(FormBuilderAPI._data.retrainPenalty, 10) || 0) + 1;

            // Refresh the XP widget so the cost appears immediately
            try {
              const XPAPI = window.CG_ExperienceAPI;
              if (XPAPI && typeof XPAPI.initWidget === 'function') {
                XPAPI.initWidget();
              }
            } catch (_) {}
          }
        } catch (_) {}

        // Debounce dirty marking to avoid lag
        clearTimeout(_favDebounce);
        _favDebounce = setTimeout(() => {
          try { BuilderUI.markDirty(); } catch (_) {}
        }, 500);
      });

    // Render immediately if skills tab is already visible
    if (isSkillsTabActive() || _pendingRender) {
      _pendingRender = false;
      SkillsRender.render();
    }
  }
};
