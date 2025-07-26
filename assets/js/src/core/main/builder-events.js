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
const TRAITS = TraitsAPI.TRAITS;

export default function bindUIEvents() {
  console.log('[BuilderEvents] 🚀 bindUIEvents() called');

  // 1) Sync form input/select/textarea changes into FormBuilderAPI._data
  $(document)
    .off('input change', '#cg-modal input, #cg-modal select, #cg-modal textarea')
    .on('input change', '#cg-modal input, #cg-modal select, #cg-modal textarea', function () {
      BuilderUI.markDirty();
      const $el = $(this);
      const val = $el.val();
      const id = this.id;

      console.log(`[BuilderEvents] ✏️ Change detected on: #${id || '[no-id]'}`, val);

      // Handle skill-marks which don’t use standard ID mapping
      if ($el.hasClass('skill-marks')) {
        const skillId = $el.data('skill-id');
        const skillVal = parseInt(val, 10) || 0;

        console.log(`[BuilderEvents] 🎯 Skill mark changed → SkillID: ${skillId}, Value: ${skillVal}`);

        FormBuilderAPI._data.skillMarks = FormBuilderAPI._data.skillMarks || {};
        FormBuilderAPI._data.skillMarks[skillId] = skillVal;

        console.log('[BuilderEvents] 💾 Updated skillMarks:', FormBuilderAPI._data.skillMarks);
        return;
      }

      if (!id) {
        console.warn('[BuilderEvents] ⚠️ Element changed has no ID. Skipping.');
        return;
      }

      const key = id.replace(/^cg-/, '');
      FormBuilderAPI._data[key] = val;

      console.log(`[BuilderEvents] 💾 Updated FormBuilderAPI._data["${key}"] →`, val);

      // Update trait-adjusted display, if applicable
      if (TRAITS.includes(key)) {
        const $out = $(`#cg-${key}-adjusted`);
        const isValid = ['d4', 'd6', 'd8'].includes(val);
        const displayVal = isValid ? val : '–';

        $out.text(displayVal);
        console.log(`[BuilderEvents] 🧬 Trait adjustment → ${key}: raw "${val}", display "${displayVal}"`);
      }
    });

  // 2) Modal tab switching
  $(document)
    .off('click', '#cg-modal .cg-tabs li')
    .on('click', '#cg-modal .cg-tabs li', function (e) {
      e.preventDefault();
      const tabName = $(this).data('tab');

      console.log(`[BuilderEvents] 🔀 Switched to tab: ${tabName}`);

      $('#cg-modal .cg-tabs li').removeClass('active');
      $(this).addClass('active');

      $('.tab-panel').removeClass('active');
      $(`#${tabName}`).addClass('active');

      refreshTab();
    });

  // 3) Unsaved modal logic
  $(document).off('click', '#cg-modal-close').on('click', '#cg-modal-close', e => {
    e.preventDefault();
    console.log('[BuilderEvents] ❌ Modal close clicked');
    BuilderUI.showUnsaved();
  });

  $(document).off('click', '#cg-modal-overlay').on('click', '#cg-modal-overlay', function (e) {
    if (e.target !== this) return;
    console.log('[BuilderEvents] 🧊 Modal overlay clicked');
    BuilderUI.showUnsaved();
  });

  $(document).off('click', '#unsaved-save').on('click', '#unsaved-save', e => {
    e.preventDefault();
    console.log('[BuilderEvents] 💾 Unsaved modal → Save and close clicked');
    FormBuilderAPI.save(true);
  });

  $(document).off('click', '#unsaved-exit').on('click', '#unsaved-exit', e => {
    e.preventDefault();
    console.log('[BuilderEvents] 🚪 Unsaved modal → Exit without saving clicked');
    BuilderUI.closeBuilder();
  });

  $(document).off('click', '#unsaved-cancel').on('click', '#unsaved-cancel', e => {
    e.preventDefault();
    console.log('[BuilderEvents] 🔙 Unsaved modal → Cancel clicked');
    BuilderUI.hideUnsaved();
  });

  console.log('[BuilderEvents] ✅ Event bindings complete');
}
