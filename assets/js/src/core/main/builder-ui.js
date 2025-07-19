// assets/js/src/core/main/builder-ui.js

import FormBuilderAPI from '../formBuilder';

const $ = window.jQuery;
let isDirty = false;

function openBuilder({ isNew = false, payload = {} } = {}) {
  console.log('[BuilderUI] openBuilder() called with opts:', { isNew, payload });

  isDirty = false;
  $('#cg-unsaved-confirm, #cg-unsaved-backdrop')
    .hide()
    .css('display', 'none');

  const data = {
    ...payload,
    isNew
  };

  FormBuilderAPI.init(data);

  $('#cg-modal-overlay, #cg-modal')
    .removeClass('cg-hidden')
    .addClass('cg-visible')
    .css('display', 'block');

  if (isNew) {
    $('#cg-modal .cg-tabs li[data-tab="tab-traits"]').click();
  }
}

function closeBuilder() {
  $('#cg-unsaved-backdrop').hide().css('display', 'none');
  $('#cg-unsaved-confirm')
    .removeClass('cg-visible')
    .addClass('cg-hidden')
    .hide()
    .css('display', 'none');

  $('#cg-modal, #cg-modal-overlay')
    .removeClass('cg-visible')
    .addClass('cg-hidden')
    .fadeOut(200, () => {
      $('#cg-form-container').empty();
    });

  isDirty = false;
}

function markDirty()  { isDirty = true;  }
function markClean()  { isDirty = false; }
function getIsDirty() { return isDirty;  }

function showUnsaved() {
  if (!$('#cg-unsaved-backdrop').length) {
    $('<div id="cg-unsaved-backdrop"></div>').appendTo('body');
  }

  const $backdrop = $('#cg-unsaved-backdrop');
  const $prompt   = $('#cg-unsaved-confirm');

  if (!$prompt.parent().is('body')) {
    $prompt.appendTo('body');
  }

  $backdrop.show().css('display', 'block');
  $prompt.removeClass('cg-hidden').addClass('cg-visible').show().css('display', 'block');
}

function hideUnsaved() {
  $('#cg-unsaved-backdrop').hide().css('display', 'none');
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
