// assets/js/src/core/main/builder-events.js

import BuilderUI       from './builder-ui.js';
import FormBuilderAPI  from '../formBuilder';
import refreshTab      from './builder-refresh.js';
import bindLoadEvents  from './builder-load.js';
import bindSaveEvents  from './builder-save.js';
import TraitsAPI       from '../traits';
import SpeciesIndex    from '../species';
import CareerIndex     from '../career';
import GiftsAPI        from '../../gifts';
import SkillsAPI       from '../skills';
import SummaryAPI      from '../summary';

const $ = window.jQuery;

export default function bindUIEvents() {
  console.log('[BuilderEvents] bindUIEvents() called');

  // 1) Mark form dirty on any input/change
  $(document)
    .off('input change', '#cg-modal input, #cg-modal select, #cg-modal textarea')
    .on('input change', '#cg-modal input, #cg-modal select, #cg-modal textarea', () => {
      console.log('[BuilderEvents] form marked dirty');
      BuilderUI.markDirty();
    });

  // 2) “Open Builder” button → show splash
  $(document)
    .off('click', '#cg-open-builder')
    .on('click', '#cg-open-builder', e => {
      e.preventDefault();
      console.log('[BuilderEvents] #cg-open-builder clicked');
      BuilderUI.openBuilder({ isNew: false });
    });

  // 3) “New Character” splash button → dive straight into form
  $(document)
    .off('click', '#cg-new-splash')
    .on('click', '#cg-new-splash', e => {
      e.preventDefault();
      console.log('[BuilderEvents] #cg-new-splash clicked');
      BuilderUI.openBuilder({ isNew: true });
    });

  // 4) Bind AJAX load & save handlers
  bindLoadEvents();
  bindSaveEvents();

  // 5) Tab navigation & per-tab init
  $(document)
    .off('click', '#cg-modal .cg-tabs li')
    .on('click', '#cg-modal .cg-tabs li', function(e) {
      e.preventDefault();
      const tabName = $(this).data('tab');
      console.log('[BuilderEvents] tab clicked:', tabName);

      $('#cg-modal .cg-tabs li').removeClass('active');
      $(this).addClass('active');

      $('.tab-panel').removeClass('active');
      $(`#${tabName}`).addClass('active');

      refreshTab();

      switch (tabName) {
        case 'tab-traits':
          TraitsAPI.init();
          break;
        case 'tab-profile':
          SpeciesIndex.init();
          CareerIndex.init();
          GiftsAPI.init();
          break;
        case 'tab-skills':
          SkillsAPI.init();
          break;
        case 'tab-summary':
          SummaryAPI.init();
          break;
      }
    });

  // 6a) Close via “X” → show unsaved‐changes prompt
  $(document)
    .off('click', '#cg-modal-close')
    .on('click', '#cg-modal-close', e => {
      e.preventDefault();
      console.log('[BuilderEvents] close requested (X) → showing prompt');
      BuilderUI.showUnsaved();
    });

  // 6b) Close via overlay click → show unsaved‐changes prompt
  $(document)
    .off('click', '#cg-modal-overlay')
    .on('click', '#cg-modal-overlay', function(e) {
      if (e.target !== this) return;
      console.log('[BuilderEvents] close requested (overlay) → showing prompt');
      BuilderUI.showUnsaved();
    });

  // 7a) Unsaved‐prompt: Save & Exit
  $(document)
    .off('click', '#unsaved-save')
    .on('click', '#unsaved-save', e => {
      e.preventDefault();
      console.log('[BuilderEvents] Prompt: SAVE & EXIT clicked');
      // trigger the shared save‐and‐close logic
      $('#cg-modal').trigger('cg.save.exit');
    });

  // 7b) Unsaved‐prompt: Exit Without Saving
  $(document)
    .off('click', '#unsaved-exit')
    .on('click', '#unsaved-exit', e => {
      e.preventDefault();
      console.log('[BuilderEvents] Prompt: EXIT WITHOUT SAVE clicked');
      BuilderUI.closeBuilder();
    });

  // 7c) Unsaved‐prompt: Cancel
  $(document)
    .off('click', '#unsaved-cancel')
    .on('click', '#unsaved-cancel', e => {
      e.preventDefault();
      console.log('[BuilderEvents] Prompt: CANCEL clicked');
      BuilderUI.hideUnsaved();
    });
}
