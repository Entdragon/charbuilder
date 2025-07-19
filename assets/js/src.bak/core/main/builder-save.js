// assets/js/src/core/main/builder-save.js

import BuilderUI      from './builder-ui.js';
import FormBuilderAPI from '../formBuilder';
const $ = window.jQuery;

export default function bindSaveEvents() {
  // Both Save buttons share this class in form-builder.js
  $(document)
    .off('click', '.cg-save-button')
    .on('click', '.cg-save-button', function(e) {
      e.preventDefault();

      // Determine if this click is "Save & Close"
      const closeAfter = $(this).hasClass('cg-close-after-save');

      // 1) Sync required fields into state
      FormBuilderAPI._data.name   = $('#cg-name').val().trim();
      FormBuilderAPI._data.age    = $('#cg-age').val().trim();
      FormBuilderAPI._data.gender = $('#cg-gender').val();
      FormBuilderAPI._data.motto  = $('#cg-motto').val();
      // …repeat for any other form inputs you need…

      // 2) Grab the full payload
      const payload = FormBuilderAPI.getData();

      // 3) POST to save
      $.post(CG_Ajax.ajax_url, {
        action:    'cg_save_character',
        character: payload,
        security:  CG_Ajax.nonce
      })
      .done(res => {
        if (!res.success) {
          console.error('Save failed:', res.data);
          alert('Save error: ' + res.data);
          return;
        }

        // Store returned ID for future edits
        payload.id = res.data.id;

        // Mark form clean, then either close or stay open
        BuilderUI.markClean();
        if (closeAfter) {
          BuilderUI.closeBuilder();
        } else {
          alert('Character saved');
        }
      })
      .fail((xhr,err) => {
        console.error('AJAX save error:', err);
        alert('AJAX save error: ' + err);
      });
    });
}
