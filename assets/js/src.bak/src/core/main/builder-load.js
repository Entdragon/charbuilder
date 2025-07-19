// assets/js/src/core/main/builder-load.js

import BuilderUI      from './builder-ui.js';
import FormBuilderAPI from '../formBuilder';
const $ = window.jQuery;

export default function bindLoadEvents() {
  // 1) Show the load‐splash dropdown
  $(document).on('click', '#cg-load-splash', e => {
    e.preventDefault();
    e.stopPropagation();

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_load_characters',
      security: CG_Ajax.nonce
    })
    .done(res => {
      if (!res.success) {
        console.error('cg_load_characters failed:', res.data);
        return;
      }
      let opts = '<option value="">— Select Character —</option>';
      res.data.forEach(c => {
        opts += `<option value="${c.id}">${c.name}</option>`;
      });
      $('#cg-form-container').html(`
        <div class="cg-splash-load">
          <label for="cg-select">Choose character:</label>
          <select id="cg-select">${opts}</select>
          <div class="cg-splash-actions">
            <button id="cg-load-confirm">Load</button>
            <button id="cg-splash-back">Back</button>
          </div>
        </div>
      `);
    })
    .fail((xhr,err) => console.error('AJAX error cg_load_characters:', err));
  });

  // 2) Actually load the selected character
  $(document).on('click', '#cg-load-confirm', e => {
    e.preventDefault();
    e.stopPropagation();
    const id = $('#cg-select').val();
    if (!id) return;

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_character',
      id,
      security: CG_Ajax.nonce
    })
    .done(res => {
      if (!res.success) {
        console.error('cg_get_character failed:', res.data);
        return;
      }
      // Pass the loaded record into openBuilder
      BuilderUI.openBuilder({
        isNew:   false,
        payload: res.data
      });
    })
    .fail((xhr,err) => console.error('AJAX error cg_get_character:', err));
  });

  // 3) Back to the initial splash
  $(document).on('click', '#cg-splash-back', e => {
    e.preventDefault();
    e.stopPropagation();
    BuilderUI.openBuilder({ isNew: true });
  });
}
