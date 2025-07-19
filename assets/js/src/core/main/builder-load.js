// assets/js/src/core/main/builder-load.js
import BuilderUI      from './builder-ui.js';
import FormBuilderAPI from '../formBuilder';

const $ = window.jQuery;

export default function bindLoadEvents() {
  //
  // 1) Confirm load — this is your “Load” button inside the splash
  //
  $(document)
    .off('click', '#cg-load-confirm')
    .on('click', '#cg-load-confirm', e => {
      e.preventDefault();
      const charId = $('#cg-splash-load-select').val();
      if (!charId) {
        alert('Please select a character.');
        return;
      }

      console.log('[BuilderLoad] #cg-load-confirm clicked → fetch ID', charId);
      FormBuilderAPI.fetchCharacter(charId)
        .done(res => {
          const parsed  = typeof res === 'string' ? JSON.parse(res) : res;
          const record  = parsed.data || parsed;
          if (!record || !record.id) {
            alert('Could not load character.');
            return;
          }

          $('#cg-modal-splash')
            .removeClass('visible')
            .addClass('cg-hidden');

          BuilderUI.openBuilder({
            isNew:   false,
            payload: record
          });
        })
        .fail((xhr, status, err) => {
          console.error('[BuilderLoad] fetchCharacter failed:', xhr.responseText);
          alert('Load error. See console.');
        });
    });

  //
  // 2) Back button
  //
  $(document)
    .off('click', '#cg-splash-back')
    .on('click', '#cg-splash-back', e => {
      e.preventDefault();
      console.log('[BuilderLoad] #cg-splash-back clicked → show NEW form');
      BuilderUI.openBuilder({ isNew: true });
    });
}
