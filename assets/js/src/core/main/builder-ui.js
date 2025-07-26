import BuilderUI       from './builder-ui.js';
import FormBuilderAPI  from '../formBuilder';
import refreshTab      from './builder-refresh.js';
import bindLoadEvents  from './builder-load.js';
import bindSaveEvents  from './builder-save.js';
import TraitsService   from '../traits/service.js';
import SpeciesIndex    from '../species';
import CareerIndex     from '../career';
import GiftsAPI        from '../../gifts';
import SkillsAPI       from '../skills';
import SummaryAPI      from '../summary';

window.TraitsService = TraitsService;

const $ = window.jQuery;

export default function bindUIEvents() {
  console.log('[BuilderEvents] 🔧 bindUIEvents() called');

  // 1) Sync form changes to memory
  $(document)
    .off('input change', '#cg-modal input, #cg-modal select, #cg-modal textarea')
    .on('input change', '#cg-modal input, #cg-modal select, #cg-modal textarea', function () {
      BuilderUI.markDirty();
      const $el = $(this);
      const id = this.id || '(no ID)';
      console.log(`[BuilderEvents] ✏️ Input/Change → #${id} =`, $el.val());

      if ($el.hasClass('skill-marks')) {
        const skillId = $el.data('skill-id');
        const val = parseInt($el.val(), 10) || 0;
        FormBuilderAPI._data.skillMarks = FormBuilderAPI._data.skillMarks || {};
        FormBuilderAPI._data.skillMarks[skillId] = val;
        console.log(`[BuilderEvents] 🔢 Updated skillMarks →`, FormBuilderAPI._data.skillMarks);
        return;
      }

      if (!this.id) {
        console.warn('[BuilderEvents] ⚠️ Element changed but no ID present');
        return;
      }

      const key = this.id.replace(/^cg-/, '');
      FormBuilderAPI._data[key] = $el.val();
      console.log(`[BuilderEvents] 💾 Updated FormBuilderAPI._data[${key}] →`, $el.val());
    });

  // 2) Open splash selector
  $(document)
    .off('click', '#cg-open-builder')
    .on('click', '#cg-open-builder', e => {
      e.preventDefault();
      console.log('[BuilderEvents] 📂 Open builder splash');
      $('#cg-modal-splash').removeClass('cg-hidden').addClass('visible');

      FormBuilderAPI.listCharacters().done(resp => {
        console.log('[BuilderEvents] 🧾 Character list fetched:', resp.data);
        const $sel = $('#cg-splash-load-select').empty();
        resp.data.forEach(c => {
          console.log(`→ Character: ${c.name} (ID ${c.id})`);
          $sel.append(`<option value="${c.id}">${c.name}</option>`);
        });
      });
    });

  // 3) Start new blank builder
  $(document)
    .off('click', '#cg-new-splash')
    .on('click', '#cg-new-splash', e => {
      e.preventDefault();
      console.log('[BuilderEvents] 🆕 Starting new character');
      $('#cg-modal-splash').removeClass('visible').addClass('cg-hidden');

      const payload = {
        will: '', speed: '', body: '', mind: '',
        trait_species: '', trait_career: '',
        name: '', player_name: '', gender: '', age: '',
        motto: '', goal1: '', goal2: '', goal3: '',
        description: '', backstory: ''
      };

      console.log('[BuilderEvents] 🔄 Initializing blank payload:', payload);
      FormBuilderAPI._data = { ...payload };
      FormBuilderAPI._data.skillMarks = {};
      FormBuilderAPI._data.species = '';
      FormBuilderAPI._data.career  = '';

      if (window.CG_FreeChoicesState) {
        console.log('[BuilderEvents] 🧹 Resetting FreeChoicesState');
        window.CG_FreeChoicesState.selected = ['', '', ''];
        window.CG_FreeChoicesState.gifts = [];
      }

      if (window.SpeciesService) {
        console.log('[BuilderEvents] ❌ Clearing SpeciesService');
        window.SpeciesService.currentProfile = null;
      }

      if (window.CareerService) {
        console.log('[BuilderEvents] ❌ Clearing CareerService');
        window.CareerService.currentProfile = null;
      }

      if (window.GiftsState) {
        console.log('[BuilderEvents] ❌ Clearing GiftsState');
        window.GiftsState.selected = [];
      }

      BuilderUI.openBuilder({ isNew: true, payload });

      requestAnimationFrame(() => {
        console.log('[BuilderEvents] 🔁 TraitsService.refreshAll() (new character)');
        TraitsService.refreshAll();
      });
    });

  // 4) Load character and launch
  $(document)
    .off('click', '#cg-load-splash')
    .on('click', '#cg-load-splash', e => {
      e.preventDefault();
      const charId = $('#cg-splash-load-select').val();
      if (!charId) {
        alert('Please select a character to load.');
        return;
      }

      console.log(`[BuilderEvents] 📥 Loading character ID: ${charId}`);

      FormBuilderAPI.fetchCharacter(charId)
        .done(resp => {
          const parsed = typeof resp === 'string' ? JSON.parse(resp) : resp;
          const record = parsed.data || parsed;

          if (!record || !record.id) {
            console.warn('[BuilderEvents] ❌ Invalid character record:', record);
            alert('Character could not be loaded.');
            return;
          }

          console.log('[BuilderEvents] ✅ Character loaded:', record);
          $('#cg-modal-splash').removeClass('visible').addClass('cg-hidden');

          BuilderUI.openBuilder({ isNew: false, payload: record });

          requestAnimationFrame(() => {
            console.log('[BuilderEvents] 🔁 TraitsService.refreshAll() (loaded character)');
            TraitsService.refreshAll();
          });
        })
        .fail((xhr, status, err) => {
          console.error('[BuilderEvents] ❌ Load failed:', status, err, xhr.responseText);
          alert('Could not load character. Check console for details.');
        });
    });

  // 5) Save/load
  console.log('[BuilderEvents] 🔗 Binding load/save events');
  bindLoadEvents();
  bindSaveEvents();

  // 6) Tab switch
  $(document)
    .off('click', '#cg-modal .cg-tabs li')
    .on('click', '#cg-modal .cg-tabs li', function (e) {
      e.preventDefault();
      const tabName = $(this).data('tab');
      console.log(`[BuilderEvents] 🧭 Switching to tab: ${tabName}`);

      $('#cg-modal .cg-tabs li').removeClass('active');
      $(this).addClass('active');

      $('.tab-panel').removeClass('active');
      $(`#${tabName}`).addClass('active');

      refreshTab();
    });

  // 7) Unsaved modal events
  $(document).off('click', '#cg-modal-close').on('click', '#cg-modal-close', e => {
    e.preventDefault();
    console.log('[BuilderEvents] 🧨 Modal close clicked — checking unsaved changes');
    BuilderUI.showUnsaved();
  });

  $(document).off('click', '#cg-modal-overlay').on('click', '#cg-modal-overlay', function (e) {
    if (e.target !== this) return;
    console.log('[BuilderEvents] 🧨 Modal overlay clicked — checking unsaved changes');
    BuilderUI.showUnsaved();
  });

  $(document).off('click', '#unsaved-save').on('click', '#unsaved-save', e => {
    e.preventDefault();
    console.log('[BuilderEvents] 💾 Save & Close clicked');
    FormBuilderAPI.save(true);
  });

  $(document).off('click', '#unsaved-exit').on('click', '#unsaved-exit', e => {
    e.preventDefault();
    console.log('[BuilderEvents] ❌ Exit without saving clicked');
    BuilderUI.closeBuilder();
  });

  $(document).off('click', '#unsaved-cancel').on('click', '#unsaved-cancel', e => {
    e.preventDefault();
    console.log('[BuilderEvents] 🔙 Cancel exit clicked');
    BuilderUI.hideUnsaved();
  });
}
