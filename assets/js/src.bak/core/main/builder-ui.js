// assets/js/src/core/main/builder-ui.js

import FormBuilderAPI from '../formBuilder';
import TraitsAPI      from '../traits';

const $ = window.jQuery;
let isDirty = false;

/**
 * Opens the builder modal.
 *
 * @param {Object} opts
 *   - isNew   (boolean): true → brand new form; false → loading payload
 *   - payload (object) : the loaded character record
 */
function openBuilder({ isNew = false, payload = {} } = {}) {
  console.log('[BuilderUI] openBuilder() called with opts:', { isNew, payload });

  // Reset dirty flag and hide any existing unsaved‐prompt UI
  isDirty = false;
  $('#cg-unsaved-confirm, #cg-unsaved-backdrop')
    .hide()
    .css('display', 'none');

  // 1) Assemble the data object for FormBuilder
  const data = {
    ...payload,   // spread saved fields if loading
    isNew         // boolean flag
  };

  // 2) Initialize FormBuilder with this data
  FormBuilderAPI.init(data);

  // 3) Show the modal & overlay
  $('#cg-modal-overlay, #cg-modal')
    .removeClass('cg-hidden')
    .addClass('cg-visible')
    .css('display', 'block');

  // 4) If brand‐new, jump into the first tab (Traits)
  if (isNew) {
    $('#cg-modal .cg-tabs li[data-tab="tab-traits"]').click();
  }
}

/**
 * Closes the builder modal (hides UI and clears form container).
 */
function closeBuilder() {
  // Hide unsaved‐changes prompt
  $('#cg-unsaved-backdrop')
    .hide()
    .css('display', 'none');
  $('#cg-unsaved-confirm')
    .removeClass('cg-visible')
    .addClass('cg-hidden')
    .hide()
    .css('display', 'none');

  // Hide the modal & overlay then clear the form container
  $('#cg-modal, #cg-modal-overlay')
    .removeClass('cg-visible')
    .addClass('cg-hidden')
    .fadeOut(200, () => {
      $('#cg-form-container').empty();
    });

  isDirty = false;
}

/**
 * Marks the form as “dirty” (unsaved changes present).
 */
function markDirty() {
  isDirty = true;
}

/**
 * Clears the dirty flag (called after a successful save).
 */
function markClean() {
  isDirty = false;
}

/**
 * Returns whether the form is currently dirty.
 */
function getIsDirty() {
  return isDirty;
}

/**
 * Shows the “unsaved‐changes” prompt and backdrop.
 */
function showUnsaved() {
  // Ensure backdrop exists
  if (!$('#cg-unsaved-backdrop').length) {
    $('<div id="cg-unsaved-backdrop"></div>').appendTo('body');
  }
  const $backdrop = $('#cg-unsaved-backdrop');
  const $prompt   = $('#cg-unsaved-confirm');

  // Move prompt into <body> if not already there
  if (!$prompt.parent().is('body')) {
    $prompt.appendTo('body');
  }

  // Show backdrop
  $backdrop
    .show()
    .css('display', 'block');

  // Show prompt
  $prompt
    .removeClass('cg-hidden')
    .addClass('cg-visible')
    .show()
    .css('display', 'block');
}

/**
 * Hides the “unsaved‐changes” prompt and backdrop.
 */
function hideUnsaved() {
  $('#cg-unsaved-backdrop')
    .hide()
    .css('display', 'none');

  $('#cg-unsaved-confirm')
    .removeClass('cg-visible')
    .addClass('cg-hidden')
    .hide()
    .css('display', 'none');
}

export default {
  openBuilder,
  closeBuilder,
  showUnsaved,
  hideUnsaved,
  markDirty,
  markClean,
  getIsDirty
};
