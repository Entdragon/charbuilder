// assets/js/src/core/formBuilder/index.js

const $ = window.jQuery;
import FormBuilder from './form-builder.js';

const FormBuilderAPI = {
  _data:   {},
  isNew:   true,
  hasData: false,

  /**
   * Initialize the builder state and render the form.
   *
   * @param {Object} payload
   */
  init(payload = {}) {
    console.log('[FormBuilderAPI] 🚀 init() called with payload:', payload);
    this._data   = { ...payload };
    this.isNew   = Boolean(payload.isNew);
    this.hasData = !this.isNew;

    console.log('[FormBuilderAPI] 📦 Initial state:', {
      _data: this._data,
      isNew: this.isNew,
      hasData: this.hasData
    });

    const html = FormBuilder.buildForm(this._data);
    $('#cg-form-container').html(html);
    console.log('[FormBuilderAPI] 🧱 Form rendered');
  },

  /**
   * Return a shallow copy of the in-memory data.
   */
  getData() {
    console.log('[FormBuilderAPI] 📤 getData() called');
    return { ...this._data };
  },

  /**
   * Read every form field from the DOM into a single payload object,
   * merging in-memory skillMarks to avoid losing them when inputs
   * aren't present on the current tab.
   */
  collectFormData() {
    console.log('[FormBuilderAPI] 🗃️ collectFormData() called');

    const d = {};

    if (this._data.id) {
      d.id = this._data.id;
      console.log('[FormBuilderAPI] ⏳ Merging existing ID:', d.id);
    }

    d.name        = $('#cg-name').val();
    d.player_name = $('#cg-player-name').val();
    d.age         = $('#cg-age').val();
    d.gender      = $('#cg-gender').val();
    d.motto       = $('#cg-motto').val();
    d.goal1       = $('#cg-goal1').val();
    d.goal2       = $('#cg-goal2').val();
    d.goal3       = $('#cg-goal3').val();
    d.description = $('#cg-description').val();
    d.backstory   = $('#cg-backstory').val();

    d.species_id = $('#cg-species').val();
    d.career_id  = $('#cg-career').val();

    d.skillMarks = {};
    $('input.skill-marks').each((i, el) => {
      const skillId = $(el).data('skill-id');
      d.skillMarks[skillId] = parseInt($(el).val(), 10) || 0;
    });

    d.free_gifts = [
      $('#cg-free-choice-0').val() || '',
      $('#cg-free-choice-1').val() || '',
      $('#cg-free-choice-2').val() || ''
    ];

    d.traits = {};
    $('.cg-trait-select').each((i, sel) => {
      const key = $(sel).attr('id').replace('cg-', '');
      const val = $(sel).val();
      d.traits[key] = val;
    });

    console.log('[FormBuilderAPI] 📦 Form data collected:', d);
    return d;
  },

  /**
   * Save the character via WP-AJAX and optionally close builder.
   *
   * @param {boolean} shouldClose
   * @returns {Promise}
   */
  save(shouldClose = false) {
    console.log('[FormBuilderAPI] 💾 save() called. shouldClose:', shouldClose);
    const payload = this.collectFormData();

    return $.ajax({
      url:    CG_Ajax.ajax_url,
      method: 'POST',
      data: {
        action:    'cg_save_character',
        character: payload,
        security:  CG_Ajax.nonce
      }
    })
    .done(res => {
      console.log('[FormBuilderAPI] ✅ save done. Response:', res);
      if (!res.success) {
        console.error('[FormBuilderAPI] ❌ save error:', res.data);
        alert('Save failed: ' + res.data);
        return;
      }

      this._data   = { ...this._data, id: res.data.id };
      this.isNew   = false;
      this.hasData = true;

      console.log('[FormBuilderAPI] 💾 save complete. Updated state:', {
        _data: this._data,
        isNew: this.isNew,
        hasData: this.hasData
      });

      FormBuilderAPI.onSaveClean();
      if (shouldClose) {
        FormBuilderAPI.onSaveClose();
      }
    })
    .fail((xhr, status, err) => {
      console.error('[FormBuilderAPI] ❌ save failed', status, err, xhr.responseText);
      alert('Save failed—check console for details.');
    });
  },

  /**
   * Fetch a list of saved characters (for the Load splash).
   */
  listCharacters() {
    console.log('[FormBuilderAPI] 📄 listCharacters() called');
    return $.ajax({
      url:    CG_Ajax.ajax_url,
      method: 'POST',
      data: {
        action:   'cg_load_characters',
        security: CG_Ajax.nonce
      }
    });
  },

  /**
   * Fetch one character’s full data by ID.
   *
   * @param {number|string} id
   */
  fetchCharacter(id) {
    console.log('[FormBuilderAPI] 📥 fetchCharacter() called with ID:', id);
    return $.ajax({
      url:    CG_Ajax.ajax_url,
      method: 'POST',
      data: {
        action:   'cg_get_character',
        id,
        security: CG_Ajax.nonce
      }
    });
  },

  // Hooks assigned by builder-ui.js:
  onSaveClean: () => {
    console.log('[FormBuilderAPI] 🧹 onSaveClean() called');
  },
  onSaveClose: () => {
    console.log('[FormBuilderAPI] 🛑 onSaveClose() called');
  }
};

export default FormBuilderAPI;
