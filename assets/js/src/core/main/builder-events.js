// assets/js/src/core/main/builder-events.js
// Maintains original builder wiring and adds safe hydration for Species/Career
// across New, Load, tab switches, and post-save refreshes.

import bindOnce, { makeInitGuard } from '../utils/bind-once.js';
import BuilderUI       from './builder-ui.js';
import FormBuilderAPI  from '../formBuilder';
import refreshTab      from './builder-refresh.js';
import bindLoadEvents  from './builder-load.js';
import bindSaveEvents  from './builder-save.js';
import TraitsAPI       from '../traits';
import SpeciesIndex    from '../species';
import CareerIndex     from '../career';
import GiftsAPI        from '../gifts';
import SkillsAPI       from '../skills';
import SummaryAPI      from '../summary';

// Fallback APIs (used only if Index modules don't populate)
import SpeciesAPI      from '../species/api.js';
import CareerAPI       from '../career/api.js';

const $ = window.jQuery;

const LOG = (...a) => console.log('[BuilderEvents]', ...a);
const ERR = (...a) => console.error('[BuilderEvents]', ...a);

// Broad selectors to match your actual controls no matter how they’re rendered
const SEL = {
  species: '#cg-species, select[name="species"], select[name="trait_species"], select[data-cg="species"], .cg-species',
  career:  '#cg-career,  select[name="career"],  select[name="trait_career"],  select[data-cg="career"],  .cg-career',
};

function firstSelect(selector) {
  const $sel = $(selector);
  // prefer in-modal elements if multiple exist
  const $modalSel = $('#cg-modal').find(selector);
  if ($modalSel.length) return $modalSel.first();
  return $sel.length ? $sel.first() : null;
}

function setSelectValue($sel, want) {
  if (!$sel || !$sel.length || !want) return false;
  const val = String(want);
  $sel.val(val);
  if ($sel.val() === val) return true;

  // fallback: try by visible text
  const $byText = $sel.find('option').filter(function () {
    return $(this).text() === val;
  }).first();
  if ($byText.length) {
    $sel.val($byText.val());
    return true;
  }
  return false;
}

function triggerDownstream() {
  try { TraitsAPI?.Service?.updateAdjustedDisplays?.(); } catch (_) {}
  try { GiftsAPI?.refresh?.(); }   catch (_) {}
  try { SkillsAPI?.refresh?.(); }  catch (_) {}
  try { SummaryAPI?.refresh?.(); } catch (_) {}
}

/**
 * hydrateSelect(kind: 'species'|'career', { force, record })
 * - Calls your Index module first (render/refresh); if empty, falls back to API.populateSelect
 * - Applies record’s saved selection (id or name) and triggers change → downstream recompute
 */
function hydrateSelect(kind, { force = false, record = null } = {}) {
  const key      = kind === 'species' ? 'species' : 'career';
  const selector = kind === 'species' ? SEL.species : SEL.career;
  const $sel     = firstSelect(selector);

  if (!$sel) { LOG(`no ${kind} select found`); return $.Deferred().resolve().promise(); }

  const el       = $sel.get(0);
  const hadCount = el.options.length;

  // Prefer project modules
  try {
    const Index = (kind === 'species') ? SpeciesIndex : CareerIndex;
    if (Index?.refresh) Index.refresh();
    else if (Index?.render) Index.render();
  } catch (e) { /* non-fatal */ }

  const doApply = () => {
    // Apply record selection if provided
    if (record) {
      const want = record[key] ?? record[`${key}_id`] ?? '';
      if (want) setSelectValue($sel, want);
    }
    // If options were newly injected or we applied a value, trigger change
    if (el.options.length > hadCount || (record && (record[key] || record[`${key}_id`]))) {
      $sel.trigger('change');
      triggerDownstream();
    }
  };

  // Fallback populate via API if still empty
  const ensureOptions = () => {
    if (el.options.length > 1 && !force) return $.Deferred().resolve().promise();
    const API = (kind === 'species') ? SpeciesAPI : CareerAPI;
    if (typeof API?.populateSelect !== 'function') return $.Deferred().resolve().promise();
    return API.populateSelect(el, { force: !!force });
  };

  // Give the DOM a tick in case Index.render() appended later in the event loop
  return $.Deferred(function (dfr) {
    setTimeout(() => {
      ensureOptions()
        .then(() => { doApply(); dfr.resolve(); })
        .catch(() => dfr.resolve());
    }, 0);
  }).promise();
}

function hydrateSpeciesAndCareer(opts = {}) {
  return $.when(
    hydrateSelect('species', opts),
    hydrateSelect('career',  opts)
  );
}

export default function bindUIEvents() {
  LOG('bindUIEvents() called');

  // Core-owned Gifts module init (idempotent)
  try { GiftsAPI?.init?.(); } catch (_) {}

  // 1) Mark form dirty on change & sync into FormBuilderAPI._data
  $(document)
    .off('input.cg change.cg', '#cg-modal input, #cg-modal select, #cg-modal textarea')
    .on('input.cg change.cg', '#cg-modal input, #cg-modal select, #cg-modal textarea', function() {
      BuilderUI.markDirty();

      const $el = $(this);

      // Skill-marks inputs don’t have an ID, so handle them explicitly
      if ($el.hasClass('skill-marks')) {
        const skillId = $el.data('skill-id');
        const val     = parseInt($el.val(), 10) || 0;
        FormBuilderAPI._data.skillMarks = FormBuilderAPI._data.skillMarks || {};
        FormBuilderAPI._data.skillMarks[skillId] = val;
        return;
      }

      // All other fields map by stripping “cg-” off the ID
      const id  = this.id;     // e.g. “cg-name”, “cg-body”, “cg-species”
      if (!id) return;
      const key = id.replace(/^cg-/, '');
      FormBuilderAPI._data[key] = $el.val();
    });

  // 2) Show splash (dropdown population is owned by builder-load.js)
  $(document)
    .off('click.cg', '#cg-open-builder')
    .on('click.cg', '#cg-open-builder', e => {
      e.preventDefault();
      $('#cg-modal-splash').removeClass('cg-hidden').addClass('visible');

      // If the dropdown exists but is still empty, ask BuilderLoad to populate ONCE.
      try {
        const $sel = $('#cg-splash-load-select');
        const optCount = $sel.length ? $sel.find('option').length : 0;
        if ($sel.length && optCount <= 1) {
          document.dispatchEvent(new CustomEvent('cg:characters:refresh', { detail: { source: 'splash-open' } }));
        }
      } catch (_) {}
    });

  // 3) Open blank builder (NEW CHARACTER) and purge previous state
  $(document)
    .off('click.cg', '#cg-new-splash')
    .on('click.cg', '#cg-new-splash', e => {
      e.preventDefault();

      // Hide the splash
      $('#cg-modal-splash').removeClass('visible').addClass('cg-hidden');

      // Clear all builder state
      BuilderUI.openBuilder({ isNew: true, payload: {} });

      // Reset skill marks
      FormBuilderAPI._data.skillMarks = {};

      // Reset species/career
      FormBuilderAPI._data.species = '';
      FormBuilderAPI._data.career  = '';

      // Reset gifts state
      if (window.CG_FreeChoicesState) {
        window.CG_FreeChoicesState.selected = ['', '', ''];
        // keep gifts list unless you explicitly want it purged
        // window.CG_FreeChoicesState.gifts    = [];
      }

      // Ensure species/career lists exist for the new form
      setTimeout(() => {
        hydrateSpeciesAndCareer({ force: false, record: {} });
      }, 0);
    });

  // 4) Load character by ID and launch builder
  $(document)
    .off('click.cg', '#cg-load-splash')
    .on('click.cg', '#cg-load-splash', e => {
      e.preventDefault();
      const charId = $('#cg-splash-load-select').val();
      if (!charId) {
        alert('Please select a character to load.');
        return;
      }

      FormBuilderAPI.fetchCharacter(charId)
        .done(resp => {
          console.log('🔎 [AJAX] raw cg_get_character response:', resp);
          const parsed = typeof resp === 'string' ? JSON.parse(resp) : resp;
          console.log('🔍 [AJAX] parsed.data:', parsed?.data);

          const record = parsed?.data || parsed;
          if (!record || !record.id) {
            alert('Character could not be loaded.');
            return;
          }

          // Hide the splash, open builder, and seed state from record
          $('#cg-modal-splash').removeClass('visible').addClass('cg-hidden');

          BuilderUI.openBuilder({ isNew: false, payload: record });

          // Rehydrate lists and apply record selections, fire downstream recompute
          setTimeout(() => {
            hydrateSpeciesAndCareer({ force: true, record });
          }, 0);
        })
        .fail((xhr) => {
          console.error('Load failed:', xhr?.responseText || xhr);
          alert('Could not load character. Check console for details.');
        });
    });

  // 5) Wire up Save & Load inside builder
  bindLoadEvents();
  bindSaveEvents();

  // 6) Tab navigation → highlight panel + run refreshTab() and gently rehydrate lists
  $(document)
    .off('click.cg', '#cg-modal .cg-tabs li')
    .on('click.cg', '#cg-modal .cg-tabs li', function(e) {
      e.preventDefault();
      const tabName = $(this).data('tab');

      $('#cg-modal .cg-tabs li').removeClass('active');
      $(this).addClass('active');

      $('.tab-panel').removeClass('active');
      $(`#${tabName}`).addClass('active');

      refreshTab();

      // Some tabs rebuild DOM; ensure selects remain populated
      setTimeout(() => {
        hydrateSpeciesAndCareer({ force: false });
      }, 0);
    });

  // 7a) Modal close (X)
  $(document)
    .off('click.cg', '#cg-modal-close')
    .on('click.cg', '#cg-modal-close', e => {
      e.preventDefault();
      BuilderUI.showUnsaved();
    });

  // 7b) Modal overlay click
  $(document)
    .off('click.cg', '#cg-modal-overlay')
    .on('click.cg', '#cg-modal-overlay', function(e) {
      if (e.target !== this) return;
      BuilderUI.showUnsaved();
    });

  // 7c) Prompt: Save & Exit
  $(document)
    .off('click.cg', '#unsaved-save')
    .on('click.cg', '#unsaved-save', e => {
      e.preventDefault();
      console.log('[BuilderEvents] Prompt: SAVE & EXIT clicked');
      FormBuilderAPI.save(true);
    });

  // 7d) Prompt: Exit Without Save
  $(document)
    .off('click.cg', '#unsaved-exit')
    .on('click.cg', '#unsaved-exit', e => {
      e.preventDefault();
      BuilderUI.closeBuilder();
    });

  // 7e) Prompt: Cancel
  $(document)
    .off('click.cg', '#unsaved-cancel')
    .on('click.cg', '#unsaved-cancel', e => {
      e.preventDefault();
      BuilderUI.hideUnsaved();
    });

  // 8) After saves trigger list refresh elsewhere, gently rehydrate here too
  document.addEventListener('cg:characters:refresh', () => {
    setTimeout(() => {
      hydrateSpeciesAndCareer({ force: true });
    }, 0);
  });
}
