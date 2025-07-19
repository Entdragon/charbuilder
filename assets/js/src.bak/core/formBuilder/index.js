// assets/js/src/core/formBuilder/index.js

import $ from 'jquery';
import BuilderUI from '../main/builder-ui.js';
import TraitsService from '../traits/service.js';

const FormBuilderAPI = {
  _data:   {},    // holds the last‐loaded or last‐saved record
  isNew:   true,
  hasData: false,

  init(payload = {}) {
    // payload = { isNew: boolean, id?: number, …other fields… }
    this._data   = { ...payload };
    this.isNew   = payload.isNew;
    this.hasData = !payload.isNew;
    // …render logic…
  },

  getData() {
    return { ...this._data };
  },

  /**
   * Pull every form value off the page so nothing ever slips through.
   */
  collectFormData() {
    const d = {};
    if (this._data.id) {
      d.id = this._data.id;
    }

    // Basic details
    d.name        = $('#cg-name').val();
    d.player_name = $('#cg-player_name').val();
    d.age         = $('#cg-age').val();
    d.gender      = $('#cg-gender').val();
    d.motto       = $('#cg-motto').val();

    // Profile selections
    d.species_id = $('#cg-species').val();
    d.career_id  = $('#cg-career').val();

    // Traits
    TraitsService.TRAITS.forEach(key => {
      d[key] = $(`#cg-${key}`).val();
    });

    // Skill marks (example selector, adjust to your markup)
    d.skillMarks = {};
    $('.cg-skill-mark select').each((i, el) => {
      const skillId = $(el).data('skill-id');
      d.skillMarks[skillId] = $(el).val();
    });

    // Free gifts
    d.free_gift_1 = $('#cg-free_gift_1').val();
    d.free_gift_2 = $('#cg-free_gift_2').val();
    d.free_gift_3 = $('#cg-free_gift_3').val();

    // (and any other fields you need…)

    return d;
  },

  /**
   * Saves or updates a character.  PUT if we have an id, POST otherwise.
   * @param {boolean} shouldClose – when true, close the builder on success
   */
  save(shouldClose = false) {
    // Rebuild the payload from the live form
    const payload = this.collectFormData();

    // Decide POST vs PUT by presence of id
    const hasId = Boolean(payload.id);
    const method = hasId ? 'PUT' : 'POST';
    const url    = hasId
      ? `/api/characters/${payload.id}`
      : `/api/characters`;

    console.log('[FormBuilderAPI] ▶ save()', { method, url, payload });

    return $.ajax({
      url,
      method,
      contentType: 'application/json',
      data: JSON.stringify(payload),
    })
    .done(resp => {
      console.log('[FormBuilderAPI] save.done()', resp);

      // Grab the record back from the server (resp.data or resp)
      const record = resp.data || resp;

      // Update our in–memory store
      this._data   = { ...record };
      this.hasData = true;
      this.isNew   = false;    // now it’s definitely an edit

      BuilderUI.markClean();

      if (shouldClose) {
        BuilderUI.closeBuilder();
      }
    })
    .fail((xhr, status, err) => {
      console.error('[FormBuilderAPI] save.fail()', status, err, xhr.responseText);
      alert('Save failed – check the console for details.');
    });
  }
};

export default FormBuilderAPI;
