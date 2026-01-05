// assets/js/src/core/main/builder-ui.js
// Ensures Species/Career lists populate on open and on load,
// binds robust change flows so gifts/other tabs refresh.

import FormBuilderAPI from '../formBuilder';
import SpeciesIndex   from '../species';
import CareerIndex    from '../career';

const $ = window.jQuery;

let isDirty = false;

const SELECTORS = {
  // Include #cg-* as canonical IDs (current formBuilder renders these).
  species: ['#cg-species', 'select[name="species"]', 'select[data-cg="species"]', 'select.cg-species'],
  career:  ['#cg-career',  'select[name="career"]',  'select[data-cg="career"]',  'select.cg-career'],
};

function first(selectorList, root = document) {
  for (let i = 0; i < selectorList.length; i++) {
    const el = root.querySelector(selectorList[i]);
    if (el) return el;
  }
  return null;
}

function waitForSelects(timeoutMs = 2000) {
  return new Promise(resolve => {
    const start = Date.now();
    (function tick() {
      const speciesEl = first(SELECTORS.species);
      const careerEl  = first(SELECTORS.career);
      if (speciesEl && careerEl) return resolve({ speciesEl, careerEl });
      if (Date.now() - start > timeoutMs) return resolve({ speciesEl, careerEl });
      setTimeout(tick, 50);
    })();
  });
}

async function ensureListsThenApply(record = {}) {
  // Ask feature modules to build/bind (idempotent).
  SpeciesIndex.init();
  CareerIndex.init();

  // Wait for selects to exist (form may render async).
  const { speciesEl, careerEl } = await waitForSelects();

  // Populate options if empty (feature modules handle globals/AJAX and dedupe).
  if (speciesEl && speciesEl.options.length <= 1) SpeciesIndex.refresh();
  if (careerEl  && careerEl.options.length  <= 1) CareerIndex.refresh();

  // Apply record selections (id or name), then fire change so downstream recalcs run.
  if (speciesEl) {
    const want = record.species ?? record.species_id ?? '';
    if (want) {
      $(speciesEl).val(String(want));
      if ($(speciesEl).val() !== String(want)) {
        // try by visible text as fallback
        const byName = [...speciesEl.options].find(o => o.textContent === String(want));
        if (byName) $(speciesEl).val(byName.value);
      }
      $(speciesEl).trigger('change');
    }
  }

  if (careerEl) {
    const want = record.career ?? record.career_id ?? '';
    if (want) {
      $(careerEl).val(String(want));
      if ($(careerEl).val() !== String(want)) {
        const byName = [...careerEl.options].find(o => o.textContent === String(want));
        if (byName) $(careerEl).val(byName.value);
      }
      $(careerEl).trigger('change');
    }
  }
}

function activateDefaultTab() {
  try {
    const $details = $('#cg-modal .cg-tabs li[data-tab="tab-details"]');
    if ($details.length) {
      $details.trigger('click');
      return;
    }
    // Fallback: first tab
    const $first = $('#cg-modal .cg-tabs li').first();
    if ($first.length) $first.trigger('click');
  } catch (_) {}
}

function openBuilder({ isNew = false, payload = {} } = {}) {
  console.log('[BuilderUI] openBuilder()', { isNew, payload });

  isDirty = false;
  $('#cg-unsaved-confirm, #cg-unsaved-backdrop').hide().css('display', 'none');

  try { FormBuilderAPI?.init?.({ ...payload, isNew }); } catch (e) {
    console.error('[BuilderUI] FormBuilderAPI.init error', e);
  }

  $('#cg-modal-overlay, #cg-modal')
    .removeClass('cg-hidden')
    .addClass('cg-visible')
    .css('display', 'block');

  // Always open on Details tab (user expectation).
  activateDefaultTab();

  // Critically: (re)build species/career options and push changes downstream.
  ensureListsThenApply(payload).then(() => {
    document.dispatchEvent(new CustomEvent('cg:builder:opened', { detail: { isNew, payload } }));
  });
}

function closeBuilder() {
  $('#cg-unsaved-backdrop').hide().css('display', 'none');
  $('#cg-unsaved-confirm').removeClass('cg-visible').addClass('cg-hidden').hide().css('display', 'none');
  $('#cg-modal, #cg-modal-overlay')
    .removeClass('cg-visible')
    .addClass('cg-hidden')
    .fadeOut(200, () => {
      $('#cg-form-container').empty();
      document.dispatchEvent(new CustomEvent('cg:builder:closed'));
    });
  isDirty = false;
}

function markDirty()  { isDirty = true;  }
function markClean()  { isDirty = false; }
function getIsDirty() { return isDirty;  }

function showUnsaved() {
  if (!$('#cg-unsaved-backdrop').length) $('<div id="cg-unsaved-backdrop"></div>').appendTo('body');
  const $b = $('#cg-unsaved-backdrop'); const $p = $('#cg-unsaved-confirm');
  if (!$p.parent().is('body')) $p.appendTo('body');
  $b.show().css('display', 'block');
  $p.removeClass('cg-hidden').addClass('cg-visible').show().css('display', 'block');
}

function hideUnsaved() {
  $('#cg-unsaved-backdrop').hide().css('display', 'none');
  $('#cg-unsaved-confirm').removeClass('cg-visible').addClass('cg-hidden').hide().css('display', 'none');
}

// Idempotent native listener: cg:character:loaded
// IMPORTANT: do NOT also bind any legacy fallback handler (that’s what caused the duplicate in DIST).
try {
  window.__CG_EVT__ = window.__CG_EVT__ || {};
  const EVT = window.__CG_EVT__;

  if (EVT.builderUICharacterLoaded) {
    document.removeEventListener('cg:character:loaded', EVT.builderUICharacterLoaded);
  }

  EVT.builderUICharacterLoaded = (ev) => {
    const record = ev?.detail || {};
    console.log('[BuilderUI] cg:character:loaded → rehydrate for record', record);
    ensureListsThenApply(record);
  };

  document.addEventListener('cg:character:loaded', EVT.builderUICharacterLoaded);
} catch (e) {
  console.error('[BuilderUI] failed to bind idempotent cg:character:loaded listener', e);
}

export default {
  open: openBuilder,
  openBuilder,
  close: closeBuilder,
  closeBuilder,
  showUnsaved,
  hideUnsaved,
  markDirty,
  markClean,
  getIsDirty,
};
