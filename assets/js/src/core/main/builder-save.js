// assets/js/src/core/main/builder-save.js
//
// HARDENING (Jan 2026):
// - Prevent duplicate save handlers (jQuery delegated + legacy selectors).
// - Explicitly unbind any old handlers attached to ".cg-close-after-save".
// - Stop propagation so only ONE save path runs.
// - Route through FormBuilderAPI.save() so there is ONE save implementation + ONE in-flight guard.

import FormBuilderAPI from '../formBuilder';

const $ = window.jQuery;

const LOG  = (...a) => console.log('[BuilderSave]', ...a);
const WARN = (...a) => console.warn('[BuilderSave]', ...a);

function setSaveButtonsDisabled(disabled) {
  try {
    $('#cg-modal .cg-save-button')
      .prop('disabled', !!disabled)
      .toggleClass('cg-disabled', !!disabled);
  } catch (_) {}
}

export default function bindSaveEvents() {
  // HARD DEDUPE:
  // Remove any previously bound click handlers for these selectors (namespaced or not).
  // This catches legacy code that bound ".cg-close-after-save" separately.
  $(document).off('click', '.cg-save-button');
  $(document).off('click', '.cg-close-after-save');

  // Rebind with our namespace.
  $(document).on('click.cg', '.cg-save-button', function (e) {
    e.preventDefault();

    // Critical: prevents other click handlers for the same event from firing AFTER this one.
    // If some other handler was bound earlier on a different selector, our .off above removes it.
    e.stopImmediatePropagation();

    const $btn = $(this);

    // Save & Close buttons carry this class in the markup.
    const shouldClose = $btn.hasClass('cg-close-after-save');

    if (window.CG_SAVE_IN_FLIGHT) {
      WARN('Save click ignored: CG_SAVE_IN_FLIGHT already true', { shouldClose });
      return;
    }

    setSaveButtonsDisabled(true);
    LOG('▶ Saving character...', { shouldClose });

    const req = FormBuilderAPI.save(!!shouldClose);

    // jqXHR has .always()
    if (req && typeof req.always === 'function') {
      req.always(() => setSaveButtonsDisabled(false));
    } else {
      // Best-effort unlock
      setTimeout(() => setSaveButtonsDisabled(false), 1500);
    }
  });
}
