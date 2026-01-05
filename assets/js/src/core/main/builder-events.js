/**
 * assets/js/src/core/main/builder-events.js
 * Maintains original builder wiring and adds safe hydration for Species/Career
 * across New, Load, tab switches, and post-save refreshes.
 *
 * IMPORTANT:
 * - Do NOT include trait_* selects in Species/Career selectors.
 * - Tabs may detach panels; when #cg-species/#cg-career are not present we must NOT
 *   accidentally target #cg-trait_species/#cg-trait_career.
 *
 * Jan 2026 fix:
 * - Prevent programmatic "change" events firing just because options were injected.
 *   Only fire change when we are applying a real record selection.
 * - Remove redundant NEW-character hydration (tab-click hydration already runs).
 */

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
// NOTE: intentionally excludes trait_species / trait_career.
const SEL = {
  species: '#cg-species, select[name="species"], select[data-cg="species"], .cg-species',
  career:  '#cg-career,  select[name="career"],  select[data-cg="career"],  .cg-career',
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

  const valRaw = String(want);
  const val = valRaw.trim();

  // Extra safety: never set a select to a dice value
  const dice = new Set(['d4','d6','d8','d10','d12','–','-']);
  if (dice.has(val.toLowerCase())) return false;

  $sel.val(val);
  if (String($sel.val() || '') === val) return true;

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

/**
 * Emit a single canonical tab-change event.
 * This is the anchor for the upcoming tab restructure so modules stop binding
 * "panel click" hacks and instead react to cg:tab:changed.
 */
function emitTabChanged(fromTab, toTab) {
  try {
    if (!toTab) return;
    if (fromTab && String(fromTab) === String(toTab)) return; // no-op: same tab
    document.dispatchEvent(new CustomEvent('cg:tab:changed', {
      detail: {
        from: fromTab ? String(fromTab) : '',
        to:   String(toTab),
      }
    }));
  } catch (_) { /* non-fatal */ }
}

/**
 * hydrateSelect(kind: 'species'|'career', { force, record })
 * - Calls your Index module first (render/refresh); if empty, falls back to API.populateSelect
 * - Applies record’s saved selection (id or name)
 * - IMPORTANT: Only triggers "change" when we actually applied a non-empty record selection.
 *   Do NOT trigger change merely because options were injected; that causes duplicate empty logs.
 */
function hydrateSelect(kind, { force = false, record = null } = {}) {
  const key      = kind === 'species' ? 'species' : 'career';
  const selector = kind === 'species' ? SEL.species : SEL.career;
  const $sel     = firstSelect(selector);

  if (!$sel) { LOG(`no ${kind} select found`); return $.Deferred().resolve().promise(); }

  const el = $sel.get(0);
  const beforeVal = String($sel.val() || '').trim();

  // Prefer project modules
  try {
    const Index = (kind === 'species') ? SpeciesIndex : CareerIndex;
    if (Index?.refresh) Index.refresh();
    else if (Index?.render) Index.render();
  } catch (_) { /* non-fatal */ }

  // Fallback populate via API if still empty
  const ensureOptions = () => {
    if (el.options.length > 1 && !force) return $.Deferred().resolve().promise();
    const API = (kind === 'species') ? SpeciesAPI : CareerAPI;
    if (typeof API?.populateSelect !== 'function') return $.Deferred().resolve().promise();
    return API.populateSelect(el, { force: !!force });
  };

  const doApply = () => {
    if (!record) return;

    const wantRaw = record[key] ?? record[`${key}_id`] ?? '';
    const want = String(wantRaw || '').trim();
    if (!want) return;

    const applied = setSelectValue($sel, want);
    const afterVal = String($sel.val() || '').trim();

    // Only trigger change if we have a real, non-empty selection after applying.
    // This avoids "selected species → ''" logs during mere option injection.
    if (afterVal) {
      // If we successfully applied OR the value now differs from before, notify downstream
      if (applied || afterVal !== beforeVal) {
        $sel.trigger('change');
      }
    }
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

// Stable handler reference so we can remove/re-add safely.
function onCharactersRefresh() {
  setTimeout(() => {
    hydrateSpeciesAndCareer({ force: true });
  }, 0);
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
      }

      // NOTE: Do NOT hydrate here; openBuilder() activates a tab which triggers hydration once.
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

          // Rehydrate lists and apply record selections AFTER options exist (avoids “empty change”)
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

      const fromTab = $('#cg-modal .cg-tabs li.active').data('tab');
      const tabName = $(this).data('tab');

      $('#cg-modal .cg-tabs li').removeClass('active');
      $(this).addClass('active');

      $('.tab-panel').removeClass('active');
      $(`#${tabName}`).addClass('active');

      // Canonical tab-change hook for the upcoming tab restructure
      emitTabChanged(fromTab, tabName);

      refreshTab();

      // Some tabs rebuild DOM; ensure selects remain populated (NO change trigger here)
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
  // IMPORTANT: avoid stacking native listeners on repeated bindUIEvents() calls.
  try {
    window.__CG_EVT__ = window.__CG_EVT__ || {};
    if (window.__CG_EVT__.charactersRefreshEvents) {
      document.removeEventListener('cg:characters:refresh', window.__CG_EVT__.charactersRefreshEvents);
    }
    window.__CG_EVT__.charactersRefreshEvents = onCharactersRefresh;
    document.addEventListener('cg:characters:refresh', window.__CG_EVT__.charactersRefreshEvents);
  } catch (_) {}
}
