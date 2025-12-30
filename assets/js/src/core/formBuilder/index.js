// assets/js/src/core/formBuilder/index.js

const $ = window.jQuery;
import BuilderUI     from '../main/builder-ui.js';
import TraitsService from '../traits/service.js';
import FormBuilder   from './form-builder.js';

/**
 * Resolve AJAX URL + nonce from any of the bridges we’ve set up.
 */
function ajaxEnv() {
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  return {
    url:   env.ajax_url || window.ajaxurl || document.body?.dataset?.ajaxUrl || '/wp-admin/admin-ajax.php',
    nonce: env.nonce    || env.security   || window.CG_NONCE || null
  };
}

/**
 * Normalize raw builder data into a flat “core” object with the key names
 * the PHP handler expects. Also JSON-encode any structured blobs.
 */
function normalizeCore(raw = {}) {
  // IMPORTANT: accept either species/career OR species_id/career_id OR trait_* fallbacks
  const species = raw.species ?? raw.species_id ?? raw.trait_species ?? '';
  const career  = raw.career  ?? raw.career_id  ?? raw.trait_career  ?? '';

  return {
    id:          raw.id || '',
    name:        raw.name || '',
    player_name: raw.player_name || '',
    age:         raw.age || '',
    gender:      raw.gender || '',
    motto:       raw.motto || '',
    will:        raw.will ?? '',
    body:        raw.body ?? '',
    mind:        raw.mind ?? '',
    speed:       raw.speed ?? '',
    species,
    career,

    // optional structured blobs (keep them available to PHP if needed)
    skill_marks: raw.skillMarks ? JSON.stringify(raw.skillMarks) : '',
    traits_list: raw.traitsList ? JSON.stringify(raw.traitsList) : '',
    skills_list: raw.skillsList ? JSON.stringify(raw.skillsList) : '',
    gifts:       raw.gifts      ? JSON.stringify(raw.gifts)      : '',

    // keep the free_gifts array as-is if present
    free_gifts:  Array.isArray(raw.free_gifts) ? raw.free_gifts : undefined
  };
}

/**
 * Build a “belt-and-suspenders” payload:
 *  - flat fields (name, species, career, …)
 *  - nested character[...] fields (same content)
 *  - character_json full JSON fallback
 * and include all common nonce keys.
 */
function buildPayload(raw) {
  const core = normalizeCore(raw);
  const { nonce } = ajaxEnv();

  const base = { action: 'cg_save_character' };
  if (nonce) {
    base.security    = nonce;       // typical check_ajax_referer key
    base.nonce       = nonce;       // some handlers look for this
    base._ajax_nonce = nonce;       // legacy WP key
  }

  // Flat fields — only include when present/non-empty
  const flat = {
    ...base,
    ...(core.id            ? { id: core.id } : {}),
    ...(core.name          ? { name: core.name } : {}),
    ...(core.player_name   ? { player_name: core.player_name } : {}),
    ...(core.age           ? { age: core.age } : {}),
    ...(core.gender        ? { gender: core.gender } : {}),
    ...(core.motto         ? { motto: core.motto } : {}),
    ...(core.will  !== ''  ? { will: core.will }   : {}),
    ...(core.body  !== ''  ? { body: core.body }   : {}),
    ...(core.mind  !== ''  ? { mind: core.mind }   : {}),
    ...(core.speed !== ''  ? { speed: core.speed } : {}),
    ...(core.species       ? { species: core.species } : {}),
    ...(core.career        ? { career:  core.career  } : {}),
    ...(core.skill_marks   ? { skill_marks: core.skill_marks } : {}),
    ...(core.traits_list   ? { traits_list: core.traits_list } : {}),
    ...(core.skills_list   ? { skills_list: core.skills_list } : {}),
    ...(core.gifts         ? { gifts: core.gifts } : {}),
  };

  // Nested character[...] mirrors the same fields
  const character = {};
  if (core.id)           character.id           = core.id;
  if (core.name)         character.name         = core.name;
  if (core.player_name)  character.player_name  = core.player_name;
  if (core.age)          character.age          = core.age;
  if (core.gender)       character.gender       = core.gender;
  if (core.motto)        character.motto        = core.motto;
  if (core.will !== '')  character.will         = core.will;
  if (core.body !== '')  character.body         = core.body;
  if (core.mind !== '')  character.mind         = core.mind;
  if (core.speed !== '') character.speed        = core.speed;
  if (core.species)      character.species      = core.species;
  if (core.career)       character.career       = core.career;

  if (core.skill_marks)  character.skill_marks  = core.skill_marks;
  if (core.traits_list)  character.traits_list  = core.traits_list;
  if (core.skills_list)  character.skills_list  = core.skills_list;
  if (core.gifts)        character.gifts        = core.gifts;
  if (Array.isArray(core.free_gifts)) character.free_gifts = core.free_gifts;

  flat.character = character;

  // Full JSON fallback
  flat.character_json = JSON.stringify({ ...core });

  return flat;
}

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

    // Species & Career — collect under BOTH names for compatibility
    const speciesVal = $('#cg-species').val() || '';
    const careerVal  = $('#cg-career').val()  || '';
    d.species_id = speciesVal;
    d.career_id  = careerVal;
    d.species    = speciesVal; // <-- added so PHP sees expected keys
    d.career     = careerVal;  // <-- added so PHP sees expected keys

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

    // Bubble through any preloaded lists if present (helps PHP optionally persist)
    if (Array.isArray(window.CG_SKILLS_LIST)) d.skillsList = window.CG_SKILLS_LIST;

    return d;
  },

  /**
   * Save the character via WP-AJAX and optionally close builder.
   * Posts flat + nested character[...] + JSON as a compatibility belt & suspenders.
   *
   * @param {boolean} shouldClose
   * @returns {Promise}
   */
  save(shouldClose = false) {
    const raw = this.collectFormData();
    console.log('[FormBuilderAPI] ▶ save()', raw);

    const { url } = ajaxEnv();
    if (!url) {
      console.error('[FormBuilderAPI] save(): No AJAX URL available');
      alert('Save error: missing AJAX URL');
      return $.Deferred().reject('no-url').promise();
    }

    const data = buildPayload(raw);

    return $.post(url, data)
      .done(res => {
        try { res = (typeof res === 'string') ? JSON.parse(res) : res; } catch (_) {}
        if (!res || res.success !== true) {
          console.error('[FormBuilderAPI] save.error()', res);
          alert('Save failed: ' + (res?.data || 'Invalid payload.'));
          return;
        }

        // Update ID in-memory
        if (res.data?.id) {
          this._data = { ...this._data, id: res.data.id };
        }
        this.isNew   = false;
        this.hasData = true;

        BuilderUI.markClean();
        if (shouldClose) {
          BuilderUI.closeBuilder();
        } else {
          alert('Character saved');
        }

        // notify others (e.g., reload list)
        document.dispatchEvent(new CustomEvent('cg:character:saved',    { detail: { id: res.data?.id || raw.id || null, record: raw } }));
        document.dispatchEvent(new CustomEvent('cg:characters:refresh', { detail: {} }));
      })
      .fail((xhr, status, err) => {
        console.error('[FormBuilderAPI] save.fail()', status, err, xhr?.responseText);
        alert('Save failed—check console for details.');
      });
  },

  /**
   * Fetch a list of saved characters (for the Load splash).
   */
  listCharacters() {
    const { url, nonce } = ajaxEnv();
    return $.ajax({
      url,
      method: 'POST',
      data: {
        action:   'cg_load_characters',
        security: nonce
      }
    });
  },

  /**
   * Fetch one character’s full data by ID.
   *
   * @param {number|string} id
   */
  fetchCharacter(id) {
    const { url, nonce } = ajaxEnv();
    console.log('[FormBuilderAPI] fetchCharacter() called with ID:', id);
    return $.ajax({
      url,
      method: 'POST',
      data: {
        action:   'cg_get_character',
        id,
        security: nonce
      }
    });
  }
};

export default FormBuilderAPI;
