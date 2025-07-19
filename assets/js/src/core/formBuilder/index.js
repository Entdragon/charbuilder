// assets/js/src/core/formBuilder/index.js

const $ = window.jQuery;
import BuilderUI     from '../main/builder-ui.js';
import TraitsService from '../traits/service.js';
import FormBuilder   from './form-builder.js';

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
    this._data   = { ...payload };
    this.isNew   = Boolean(payload.isNew);
    this.hasData = !this.isNew;

    $('#cg-form-container').html(
      FormBuilder.buildForm(this._data)
    );
  },

  /**
   * Return a shallow copy of the in-memory data.
   */
  getData() {
    return { ...this._data };
  },

  /**
   * Read every form field from the DOM into a single payload object,
   * merging in-memory skillMarks to avoid losing them when inputs
   * aren't present on the current tab.
   */
  collectFormData() {
    const d = {};

    // Preserve existing ID (for updates)
    if (this._data.id) {
      d.id = this._data.id;
    }

    // Basic fields
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

    // Species & Career
    d.species_id = $('#cg-species').val();
    d.career_id  = $('#cg-career').val();

    // Traits (will, speed, body, mind, trait_species, trait_career)
    TraitsService.TRAITS.forEach(key => {
      d[key] = $(`#cg-${key}`).val();
    });

    // Skill marks: start with whatever is in-memory,
    // then overwrite from any <input class="skill-marks">
    const mergedMarks = { ...(this._data.skillMarks || {}) };
    $('input.skill-marks').each((i, el) => {
      const skillId = $(el).data('skill-id');
      const val     = parseInt($(el).val(), 10) || 0;
      mergedMarks[skillId] = val;
    });
    d.skillMarks = mergedMarks;

    // Free‐choice gifts
    d.free_gifts = [
      $('#cg-free-choice-0').val() || '',
      $('#cg-free-choice-1').val() || '',
      $('#cg-free-choice-2').val() || ''
    ];

    return d;
  },

  /**
   * Save the character via WP-AJAX and optionally close builder.
   *
   * @param {boolean} shouldClose
   * @returns {Promise}
   */
  save(shouldClose = false) {
    const payload = this.collectFormData();
    console.log('[FormBuilderAPI] ▶ save()', payload);

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
      if (!res.success) {
        console.error('[FormBuilderAPI] save.error()', res.data);
        alert('Save failed: ' + res.data);
        return;
      }

      // Update ID in-memory
      this._data = { ...this._data, id: res.data.id };
      this.isNew   = false;
      this.hasData = true;

      BuilderUI.markClean();
      if (shouldClose) {
        BuilderUI.closeBuilder();
      }
    })
    .fail((xhr, status, err) => {
      console.error('[FormBuilderAPI] save.fail()', status, err, xhr.responseText);
      alert('Save failed—check console for details.');
    });
  },

  /**
   * Fetch a list of saved characters (for the Load splash).
   */
  listCharacters() {
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
    console.log('[FormBuilderAPI] fetchCharacter() called with ID:', id);
    return $.ajax({
      url:    CG_Ajax.ajax_url,
      method: 'POST',
      data: {
        action:   'cg_get_character',
        id,
        security: CG_Ajax.nonce
      }
    });
  }
};

export default FormBuilderAPI;
