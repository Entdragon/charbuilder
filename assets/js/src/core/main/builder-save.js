// assets/js/src/core/main/builder-save.js
// Sends flat fields, nested `character[...]`, and JSON `character_json` for max compatibility.

import BuilderUI      from './builder-ui.js';
import FormBuilderAPI from '../formBuilder';

const $ = window.jQuery;

function ajaxEnv() {
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  return {
    url:   env.ajax_url || window.ajaxurl || document.body?.dataset?.ajaxUrl || '/wp-admin/admin-ajax.php',
    nonce: env.nonce || env.security || window.CG_NONCE || null
  };
}

/**
 * Normalize raw builder data into a flat "core" object the server expects.
 * - Prefer *_id for species/career but fall back to other keys if needed.
 * - JSON-stringify complex blobs so PHP can decode safely.
 */
function normalizeCore(raw = {}) {
  const species = (raw.species_id ?? raw.species ?? raw.trait_species ?? '');
  const career  = (raw.career_id  ?? raw.career  ?? raw.trait_career  ?? '');

  return {
    id:           raw.id || '',
    name:         raw.name || '',
    player_name:  raw.player_name || '',
    age:          raw.age || '',
    gender:       raw.gender || '',
    motto:        raw.motto || '',
    will:         raw.will ?? '',
    speed:        raw.speed ?? '',
    body:         raw.body ?? '',
    mind:         raw.mind ?? '',
    trait_species: raw.trait_species ?? '',
    trait_career:  raw.trait_career ?? '',
    species,
    career,

    // structured blobs (stringify to be safe for PHP)
    skill_marks: raw.skillMarks ? JSON.stringify(raw.skillMarks) : '',
    free_gifts:  raw.free_gifts  ? JSON.stringify(raw.free_gifts)  : '',
    traits_list: raw.traitsList  ? JSON.stringify(raw.traitsList)  : '',
    skills_list: raw.skillsList  ? JSON.stringify(raw.skillsList)  : '',
    gifts:       raw.gifts       ? JSON.stringify(raw.gifts)       : ''
  };
}

/**
 * Build a payload that works with varying server handlers:
 *  - Flat fields
 *  - Nested `character[...]`
 *  - `character_json` string
 *  - Always include security/nonce variations if available
 */
function buildPayload(raw) {
  const core       = normalizeCore(raw);
  const { nonce }  = ajaxEnv();

  // Base + nonce variations
  const base = { action: 'cg_save_character' };
  if (nonce) {
    base.security    = nonce;       // common check_ajax_referer(..., 'security')
    base.nonce       = nonce;       // some handlers expect 'nonce'
    base._ajax_nonce = nonce;       // legacy compatibility
  }

  // Flat fields (only include when non-empty to avoid clobbering)
  const flat = {
    ...base,
    ...(core.id            ? { id: core.id } : {}),
    ...(core.name          ? { name: core.name } : {}),
    ...(core.player_name   ? { player_name: core.player_name } : {}),
    ...(core.age           ? { age: core.age } : {}),
    ...(core.gender        ? { gender: core.gender } : {}),
    ...(core.motto         ? { motto: core.motto } : {}),
    ...(core.will  !== ''  ? { will: core.will } : {}),
    ...(core.speed !== ''  ? { speed: core.speed } : {}),
    ...(core.body  !== ''  ? { body: core.body } : {}),
    ...(core.mind  !== ''  ? { mind: core.mind } : {}),
    ...(core.trait_species !== '' ? { trait_species: core.trait_species } : {}),
    ...(core.trait_career  !== '' ? { trait_career:  core.trait_career }  : {}),
    ...(core.species       ? { species: core.species } : {}),
    ...(core.career        ? { career:  core.career }  : {}),

    ...(core.skill_marks   ? { skill_marks: core.skill_marks } : {}),
    ...(core.free_gifts    ? { free_gifts:  core.free_gifts }  : {}),
    ...(core.traits_list   ? { traits_list: core.traits_list } : {}),
    ...(core.skills_list   ? { skills_list: core.skills_list } : {}),
    ...(core.gifts         ? { gifts:       core.gifts }       : {})
  };

  // Nested array under character[...]
  const character = {};
  if (core.id)            character.id            = core.id;
  if (core.name)          character.name          = core.name;
  if (core.player_name)   character.player_name   = core.player_name;
  if (core.age)           character.age           = core.age;
  if (core.gender)        character.gender        = core.gender;
  if (core.motto)         character.motto         = core.motto;
  if (core.will  !== '')  character.will          = core.will;
  if (core.speed !== '')  character.speed         = core.speed;
  if (core.body  !== '')  character.body          = core.body;
  if (core.mind  !== '')  character.mind          = core.mind;
  if (core.trait_species !== '') character.trait_species = core.trait_species;
  if (core.trait_career  !== '') character.trait_career  = core.trait_career;
  if (core.species)       character.species       = core.species;
  if (core.career)        character.career        = core.career;

  if (core.skill_marks)   character.skill_marks   = core.skill_marks;
  if (core.free_gifts)    character.free_gifts    = core.free_gifts;
  if (core.traits_list)   character.traits_list   = core.traits_list;
  if (core.skills_list)   character.skills_list   = core.skills_list;
  if (core.gifts)         character.gifts         = core.gifts;

  flat.character = character;

  // JSON blob as a final fallback (server can json_decode)
  flat.character_json = JSON.stringify({ ...core });

  return flat;
}

export default function bindSaveEvents() {
  $(document)
    .off('click.cg', '.cg-save-button')
    .on('click.cg', '.cg-save-button', function(e) {
      e.preventDefault();

      const closeAfter = $(this).hasClass('cg-close-after-save');
      const raw        = FormBuilderAPI.collectFormData();

      console.log('[BuilderSave] ▶ saving payload:', raw);

      const { url } = ajaxEnv();
      if (!url) {
        console.error('[BuilderSave] No AJAX URL available');
        alert('Save error: missing AJAX URL');
        return;
      }

      const data = buildPayload(raw);

      $.post(url, data)
        .done(res => {
          try { res = (typeof res === 'string') ? JSON.parse(res) : res; } catch (_) {}

          if (!res || res.success !== true) {
            console.error('[BuilderSave] save failed:', res);
            alert('Save error: ' + (res?.data || 'Invalid payload.'));
            return;
          }

          // persist new ID if returned
          if (res.data?.id) {
            raw.id = res.data.id;
            FormBuilderAPI._data = { ...FormBuilderAPI._data, id: res.data.id };
          }

          BuilderUI.markClean();
          if (closeAfter) {
            BuilderUI.closeBuilder();
          } else {
            alert('Character saved');
          }

          // Broadcast for any listeners (e.g., refresh load list)
          document.dispatchEvent(new CustomEvent('cg:character:saved',    { detail: { id: res.data?.id || raw.id || null, record: raw } }));
          document.dispatchEvent(new CustomEvent('cg:characters:refresh', { detail: {} }));
        })
        .fail((xhr, status, err) => {
          console.error('[BuilderSave] AJAX error:', status, err, xhr?.responseText);
          alert('AJAX save error — see console');
        });
    });
}
