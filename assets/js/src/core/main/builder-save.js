// assets/js/src/core/main/builder-save.js

import BuilderUI      from './builder-ui.js';
import FormBuilderAPI from '../formBuilder';
const $ = window.jQuery;

export default function bindSaveEvents() {
  // Both “Save” and “Save & Close” buttons use the .cg-save-button class
  $(document)
    .off('click', '.cg-save-button')
    .on('click', '.cg-save-button', function(e) {
      e.preventDefault();

      // Detect if this click should close after saving
      const closeAfter = $(this).hasClass('cg-close-after-save');

      // Collect the full form payload in one go
      const payload = FormBuilderAPI.collectFormData();
      console.log('[BuilderSave] ▶ saving payload:', payload);

      // Send to your AJAX handler
      $.post(CG_Ajax.ajax_url, {
        action:    'cg_save_character',
        character: payload,
        security:  CG_Ajax.nonce
      })
      .done(res => {
        if (!res.success) {
          console.error('[BuilderSave] server error:', res.data);
          return alert('Save error: ' + res.data);
        }

        // Merge the returned ID into our in-memory state
        payload.id = res.data.id;
        FormBuilderAPI._data = { ...FormBuilderAPI._data, ...payload };

        // Mark clean and either close or stay open
        BuilderUI.markClean();
        if (closeAfter) {
          BuilderUI.closeBuilder();
        } else {
          alert('Character saved');
        }
      })
      .fail((xhr, err) => {
        console.error('[BuilderSave] AJAX error:', err);
        alert('AJAX save error — see console');
      });
    });
}
