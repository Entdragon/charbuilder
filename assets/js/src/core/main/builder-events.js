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

  // 1) Mark form dirty on change & sync into FormBuilderAPI._data
  $(document)
    .off('input change', '#cg-modal input, #cg-modal select, #cg-modal textarea')
    .on('input change', '#cg-modal input, #cg-modal select, #cg-modal textarea', function() {
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

  // 2) Show splash with character dropdown
  $(document)
    .off('click', '#cg-open-builder')
    .on('click', '#cg-open-builder', e => {
      e.preventDefault();
      $('#cg-modal-splash').removeClass('cg-hidden').addClass('visible');
      FormBuilderAPI.listCharacters()
        .done(resp => {
          const $sel = $('#cg-splash-load-select').empty();
          resp.data.forEach(c => {
            $sel.append(`<option value="${c.id}">${c.name}</option>`);
          });
        });
    });

  // 3) Open blank builder (NEW CHARACTER) and purge previous state
  $(document)
    .off('click', '#cg-new-splash')
    .on('click', '#cg-new-splash', e => {
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
        window.CG_FreeChoicesState.gifts    = [];
      }
    });

  // 4) Load character by ID and launch builder
  $(document)
    .off('click', '#cg-load-splash')
    .on('click', '#cg-load-splash', e => {
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
          console.log('🔍 [AJAX] parsed.data:', parsed.data);

          const record = parsed.data || parsed;
          if (!record || !record.id) {
            return alert('Character could not be loaded.');
          }

          // Hide the splash, open builder, and seed state from record
          $('#cg-modal-splash')
            .removeClass('visible')
            .addClass('cg-hidden');

          BuilderUI.openBuilder({
            isNew:   false,
            payload: record
          });
        })
        .fail((xhr, status, err) => {
          console.error('Load failed:', xhr.responseText);
          alert('Could not load character. Check console for details.');
        });
    });

  // 5) Wire up Save & Load inside builder
  bindLoadEvents();
  bindSaveEvents();

  // 6) Tab navigation → highlight panel + run refreshTab()
  $(document)
    .off('click', '#cg-modal .cg-tabs li')
    .on('click', '#cg-modal .cg-tabs li', function(e) {
      e.preventDefault();
      const tabName = $(this).data('tab');

      $('#cg-modal .cg-tabs li').removeClass('active');
      $(this).addClass('active');

      $('.tab-panel').removeClass('active');
      $(`#${tabName}`).addClass('active');

      refreshTab();
    });

  // 7a) Modal close (X)
  $(document)
    .off('click', '#cg-modal-close')
    .on('click', '#cg-modal-close', e => {
      e.preventDefault();
      BuilderUI.showUnsaved();
    });

  // 7b) Modal overlay click
  $(document)
    .off('click', '#cg-modal-overlay')
    .on('click', '#cg-modal-overlay', function(e) {
      if (e.target !== this) return;
      BuilderUI.showUnsaved();
    });

  // 7c) Prompt: Save & Exit
  $(document)
    .off('click', '#unsaved-save')
    .on('click', '#unsaved-save', e => {
      e.preventDefault();
      console.log('[BuilderEvents] Prompt: SAVE & EXIT clicked');
      FormBuilderAPI.save(true);
    });

  // 7d) Prompt: Exit Without Save
  $(document)
    .off('click', '#unsaved-exit')
    .on('click', '#unsaved-exit', e => {
      e.preventDefault();
      BuilderUI.closeBuilder();
    });

  // 7e) Prompt: Cancel
  $(document)
    .off('click', '#unsaved-cancel')
    .on('click', '#unsaved-cancel', e => {
      e.preventDefault();
      BuilderUI.hideUnsaved();
    });
}
