// assets/js/src/core/formBuilder/index.js
//
// SAVE HARDENING (Dec 2025 → Jan 2026):
// - Tabs may be detached/rebuilt; DOM fields may not exist when saving.
// - collectFormData() MUST fall back to in-memory _data to avoid clobbering.
// - ALWAYS send species_id/career_id (PHP expects these), plus species/career as fallback.
// - Send free_gift_1..3 + free-choice-0..2 for PHP compatibility.
// - Global in-flight guard prevents double-save from multiple handlers.
//
// CAREER GIFT REPLACEMENTS (Jan 2026):
// - Collect career_gift_replacements from DOM dropdowns (.cg-career-gift-replace)
// - Persist to DB and restore on character load

import BuilderUI     from '../main/builder-ui.js';
import TraitsService from '../traits/service.js';
import FormBuilder   from './form-builder.js';

const $ = window.jQuery;

function asString(v) {
  return (v == null) ? '' : String(v);
}

function firstNonEmpty(...vals) {
  for (const v of vals) {
    if (v == null) continue;
    const s = String(v);
    if (s !== '') return s; // NOTE: "0" is valid and should be kept
  }
  return '';
}

function normalizeFreeGifts(arr) {
  const out = Array.isArray(arr) ? arr.slice(0, 3).map(asString) : [];
  while (out.length < 3) out.push('');
  return out;
}

/**
 * Normalize skill marks into an object map { "skillId": n, ... }.
 * Accepts:
 *  - object map
 *  - array of [id, val] or objects
 *  - JSON string of either of the above
 */
function normalizeSkillMarks(src) {
  if (!src) return {};

  // JSON string?
  if (typeof src === 'string') {
    try { src = JSON.parse(src); }
    catch (_) { return {}; }
  }

  const out = {};

  if (Array.isArray(src)) {
    for (const x of src) {
      if (x == null) continue;

      if (Array.isArray(x) && x.length >= 2) {
        const k = x[0];
        const v = x[1];
        if (k != null) out[String(k)] = parseInt(v, 10) || 0;
        continue;
      }

      if (typeof x === 'object') {
        const k = x.skillId ?? x.skill_id ?? x.id ?? x.key ?? null;
        const v = x.mark ?? x.value ?? x.val ?? x.marks ?? x.count ?? null;
        if (k != null && v != null) out[String(k)] = parseInt(v, 10) || 0;
      }
    }
    return out;
  }

  if (typeof src === 'object') {
    for (const k of Object.keys(src)) {
      out[String(k)] = parseInt(src[k], 10) || 0;
    }
    return out;
  }

  return {};
}

/**
 * Normalize career gift replacements into an object map { "slot": "giftId", ... }.
 * Accepts object map, JSON string, or empty.
 */
function normalizeGiftReplacements(src) {
  if (!src) return {};

  // JSON string?
  if (typeof src === 'string') {
    try { src = JSON.parse(src); }
    catch (_) { return {}; }
  }

  if (typeof src !== 'object' || Array.isArray(src)) return {};

  const out = {};
  for (const k of Object.keys(src)) {
    const slot = String(k).trim();
    const val = String(src[k] || '').trim();
    // Only slots 1-3 with non-empty values
    if (['1','2','3'].includes(slot) && val && val !== '0') {
      out[slot] = val;
    }
  }
  return out;
}

/**
 * Resolve AJAX URL + nonce from any of the bridges we've set up.
 */
function ajaxEnv() {
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  return {
    url:   env.ajax_url || window.ajaxurl || document.body?.dataset?.ajaxUrl || '/wp-admin/admin-ajax.php',
    nonce: env.nonce    || env.security   || window.CG_NONCE || null
  };
}

/**
 * Normalize raw builder data into a flat "core" object with the key names
 * the PHP handler expects. Also JSON-encode any structured blobs.
 */
function normalizeCore(raw = {}) {
  // IMPORTANT: species/career are IDs, NOT trait_species/trait_career dice.
  const species = firstNonEmpty(
    raw.species_id,
    raw.species,
    raw.profile?.species,
    raw.profileSpecies
  );

  const career = firstNonEmpty(
    raw.career_id,
    raw.career,
    raw.profile?.career,
    raw.profileCareer
  );

  const freeArr = normalizeFreeGifts(
    Array.isArray(raw.free_gifts) ? raw.free_gifts :
    Array.isArray(raw.freeGifts)  ? raw.freeGifts  :
    (raw.free_gift_1 != null || raw.free_gift_2 != null || raw.free_gift_3 != null)
      ? [raw.free_gift_1, raw.free_gift_2, raw.free_gift_3]
      : (raw['free-choice-0'] != null || raw['free-choice-1'] != null || raw['free-choice-2'] != null)
        ? [raw['free-choice-0'], raw['free-choice-1'], raw['free-choice-2']]
        : []
  );

  // IMPORTANT: PHP expects character.skillMarks (object map)
  const marksObj = normalizeSkillMarks(
    raw.skillMarks ??
    raw.skill_marks ??
    raw.skill_marks_json ??
    ''
  );

  // Career gift replacements
  const giftReplacements = normalizeGiftReplacements(raw.career_gift_replacements);

  return {
    id:          raw.id || '',
    name:        raw.name || '',
    player_name: raw.player_name || '',
    age:         raw.age || '',
    gender:      raw.gender || '',
    motto:       raw.motto || '',

    // Persistable narrative fields (PHP saves these)
    goal1:       raw.goal1 ?? '',
    goal2:       raw.goal2 ?? '',
    goal3:       raw.goal3 ?? '',
    description: raw.description ?? '',
    backstory:   raw.backstory ?? '',

    // Optional fields PHP also supports
    local_area:  raw.local_area ?? '',
    language:    raw.language ?? '',

    will:        raw.will ?? '',
    body:        raw.body ?? '',
    mind:        raw.mind ?? '',
    speed:       raw.speed ?? '',
    trait_species: raw.trait_species ?? '',
    trait_career:  raw.trait_career  ?? '',

    // Canonical IDs (what PHP expects)
    species_id: species,
    career_id:  career,

    // Compatibility fallbacks (older handlers / older code paths)
    species,
    career,

    // structured blobs
    // - send BOTH:
    //   - skillMarks (object) for PHP cg_save_character()
    //   - skill_marks (JSON string) for older/legacy paths (if any)
    skillMarks:  marksObj,
    skill_marks: JSON.stringify(marksObj),

    // Career gift replacements (duplicate -> increase trait selections)
    career_gift_replacements: giftReplacements,

    traits_list: raw.traitsList ? JSON.stringify(raw.traitsList) : (raw.traits_list || ''),
    skills_list: raw.skillsList ? JSON.stringify(raw.skillsList) : (raw.skills_list || ''),
    gifts:       raw.gifts      ? JSON.stringify(raw.gifts)      : (raw.gifts || ''),

    // free gifts (array + convenience slots)
    free_gifts: freeArr,
    free_gifts_json: JSON.stringify(freeArr),
    free_gift_1: freeArr[0] || '',
    free_gift_2: freeArr[1] || '',
    free_gift_3: freeArr[2] || '',
    'free-choice-0': freeArr[0] || '',
    'free-choice-1': freeArr[1] || '',
    'free-choice-2': freeArr[2] || ''
  };
}

/**
 * Build a "belt-and-suspenders" payload:
 *  - flat fields
 *  - nested character[...] fields
 *  - character_json full JSON fallback
 * and include all common nonce keys.
 */
function buildPayload(raw) {
  const core = normalizeCore(raw);
  const { nonce } = ajaxEnv();

  const base = { action: 'cg_save_character' };
  if (nonce) {
    base.security    = nonce;
    base.nonce       = nonce;
    base._ajax_nonce = nonce;
  }

  // Flat fields — only include when present/non-empty
  // (Primary save path is nested character[...] — flat is extra compatibility only.)
  const flat = {
    ...base,
    ...(core.id            ? { id: core.id } : {}),
    ...(core.name          ? { name: core.name } : {}),
    ...(core.player_name   ? { player_name: core.player_name } : {}),
    ...(core.age           ? { age: core.age } : {}),
    ...(core.gender        ? { gender: core.gender } : {}),
    ...(core.motto         ? { motto: core.motto } : {}),

    ...(core.goal1 !== '' ? { goal1: core.goal1 } : {}),
    ...(core.goal2 !== '' ? { goal2: core.goal2 } : {}),
    ...(core.goal3 !== '' ? { goal3: core.goal3 } : {}),
    ...(core.description !== '' ? { description: core.description } : {}),
    ...(core.backstory   !== '' ? { backstory: core.backstory } : {}),

    ...(core.will  !== ''  ? { will: core.will }   : {}),
    ...(core.body  !== ''  ? { body: core.body }   : {}),
    ...(core.mind  !== ''  ? { mind: core.mind }   : {}),
    ...(core.speed !== ''  ? { speed: core.speed } : {}),
    ...(core.trait_species !== '' ? { trait_species: core.trait_species } : {}),
    ...(core.trait_career  !== '' ? { trait_career:  core.trait_career  } : {}),

    // IMPORTANT: PHP expects these
    ...(core.species_id    ? { species_id: core.species_id } : {}),
    ...(core.career_id     ? { career_id:  core.career_id  } : {}),

    // Back-compat
    ...(core.species       ? { species: core.species } : {}),
    ...(core.career        ? { career:  core.career  } : {}),

    // Legacy JSON blobs (harmless even if unused)
    ...(core.skill_marks   ? { skill_marks: core.skill_marks } : {}),
    ...(core.traits_list   ? { traits_list: core.traits_list } : {}),
    ...(core.skills_list   ? { skills_list: core.skills_list } : {}),
    ...(core.gifts         ? { gifts: core.gifts } : {}),

    // Free gifts: send JSON + per-slot keys
    ...(core.free_gifts_json ? { free_gifts: core.free_gifts_json } : {}),
    ...(core.free_gift_1     ? { free_gift_1: core.free_gift_1 } : {}),
    ...(core.free_gift_2     ? { free_gift_2: core.free_gift_2 } : {}),
    ...(core.free_gift_3     ? { free_gift_3: core.free_gift_3 } : {}),
    ...(core['free-choice-0'] ? { 'free-choice-0': core['free-choice-0'] } : {}),
    ...(core['free-choice-1'] ? { 'free-choice-1': core['free-choice-1'] } : {}),
    ...(core['free-choice-2'] ? { 'free-choice-2': core['free-choice-2'] } : {})
  };

  // Nested character[...] mirrors the same fields (THIS is what PHP reads)
  const character = {};


  // CG_EC_PERSIST_REGEX_V1: payload — ensure PHP receives extra careers via character[...]
  try {
    const domVals = [];
    try {
      document.querySelectorAll('select.cg-extra-career-select').forEach(sel => {
        const v = String(sel?.value || '').trim();
        const n = parseInt(v, 10) || 0;
        domVals.push(n > 0 ? String(n) : '');
      });
    } catch (_) {}
    while (domVals.length < 2) domVals.push('');

    const pick = (...vals) => {
      for (const v of vals) {
        if (v == null) continue;
        const sv = String(v).trim();
        const nv = parseInt(sv, 10) || 0;
        if (nv > 0) return String(nv);
      }
      return '';
    };

    const arg0 = (arguments && arguments.length) ? (arguments[0] || {}) : {};
    const ec1 = pick(arg0.extra_career_1, domVals[0]);
    const ec2 = pick(arg0.extra_career_2, domVals[1]);

    // authoritative (PHP reads character[...])
    character.extra_career_1 = ec1;
    character.extra_career_2 = ec2;

    // flat compatibility (harmless)
    flat.extra_career_1 = ec1;
    flat.extra_career_2 = ec2;
  } catch (_) {}

  if (core.id)          character.id          = core.id;
  if (core.name)        character.name        = core.name;

  character.player_name = core.player_name || '';
  character.age         = core.age || '';
  character.gender      = core.gender || '';
  character.motto       = core.motto || '';

  character.goal1       = (core.goal1 ?? '');
  character.goal2       = (core.goal2 ?? '');
  character.goal3       = (core.goal3 ?? '');
  character.description = (core.description ?? '');
  character.backstory   = (core.backstory ?? '');

  character.local_area  = (core.local_area ?? '');
  character.language    = (core.language ?? '');

  character.will          = (core.will ?? '');
  character.body          = (core.body ?? '');
  character.mind          = (core.mind ?? '');
  character.speed         = (core.speed ?? '');
  character.trait_species = (core.trait_species ?? '');
  character.trait_career  = (core.trait_career  ?? '');

  if (core.species_id)   character.species_id   = core.species_id;
  if (core.career_id)    character.career_id    = core.career_id;

  if (core.species)      character.species      = core.species;
  if (core.career)       character.career       = core.career;

  // IMPORTANT: what PHP reads for marks
  character.skillMarks = core.skillMarks || {};

  // Legacy JSON blobs (kept for compatibility with any older storage paths)
  if (core.skill_marks)  character.skill_marks  = core.skill_marks;
  if (core.traits_list)  character.traits_list  = core.traits_list;
  if (core.skills_list)  character.skills_list  = core.skills_list;
  if (core.gifts)        character.gifts        = core.gifts;

  character.free_gifts      = core.free_gifts; // array
  character.free_gift_1     = core.free_gift_1;
  character.free_gift_2     = core.free_gift_2;
  character.free_gift_3     = core.free_gift_3;
  character['free-choice-0'] = core['free-choice-0'];
  character['free-choice-1'] = core['free-choice-1'];
  character['free-choice-2'] = core['free-choice-2'];

  // Career gift replacements (duplicate -> increase trait selections)
  character.career_gift_replacements = core.career_gift_replacements || {};

  flat.character = character;

  flat.character_json = JSON.stringify({ ...core });

  return flat;
}

const FormBuilderAPI = {
  _data:   {},
  isNew:   true,
  hasData: false,

  init(payload = {}) {
    this._data   = { ...payload };
    this.isNew   = Boolean(payload.isNew);
    this.hasData = !this.isNew;

    $('#cg-form-container').html(
      FormBuilder.buildForm(this._data)
    );
  },

  getData() {
    return { ...this._data };
  },

  /**
   * Read every form field from the DOM into a single payload object.
   * HARDENED: if a field is not present in DOM (tab detached), fall back to in-memory _data.
   */
  collectFormData() {
    const d = { ...(this._data || {}) };

    const readIfExists = (selector) => {
      const $el = $(selector);
      if (!$el.length) return undefined;
      const v = $el.val();
      return (v == null) ? '' : v;
    };

    const normalize3 = (arr) => {
      const out = Array.isArray(arr) ? arr.slice(0, 3) : [];
      while (out.length < 3) out.push('');
      return out.map(v => (v == null ? '' : String(v)));
    };

    const normalizeMarks = (src) => {
      const out = {};
      if (!src) return out;

      if (Array.isArray(src)) {
        for (const x of src) {
          if (x == null) continue;

          if (Array.isArray(x) && x.length >= 2) {
            const k = x[0];
            const v = x[1];
            if (k != null) out[String(k)] = parseInt(v, 10) || 0;
            continue;
          }

          if (typeof x === 'object') {
            const k = x.skillId ?? x.skill_id ?? x.id ?? x.key ?? null;
            const v = x.mark ?? x.value ?? x.val ?? x.marks ?? x.count ?? null;
            if (k != null && v != null) out[String(k)] = parseInt(v, 10) || 0;
          }
        }
        return out;
      }

      if (typeof src === 'object') {
        for (const k of Object.keys(src)) {
          out[String(k)] = parseInt(src[k], 10) || 0;
        }
        return out;
      }

      return out;
    };

    // Preserve existing ID (for updates)
    if (this._data && this._data.id) d.id = this._data.id;

    // Basic fields — only overwrite if the element exists
    const basic = [
      ['name',        '#cg-name'],
      ['player_name', '#cg-player-name'],
      ['age',         '#cg-age'],
      ['gender',      '#cg-gender'],
      ['motto',       '#cg-motto'],
      ['goal1',       '#cg-goal1'],
      ['goal2',       '#cg-goal2'],
      ['goal3',       '#cg-goal3'],
      ['description', '#cg-description'],
      ['backstory',   '#cg-backstory']
    ];

    basic.forEach(([key, sel]) => {
      const v = readIfExists(sel);
      if (v !== undefined) d[key] = v;
      if (d[key] == null) d[key] = '';
    });

    // Species & Career — prefer DOM if present; else fall back to memory/profile
    const memSpecies = (this._data?.species_id ?? this._data?.species ?? this._data?.profile?.species ?? d.profile?.species ?? '');
    const memCareer  = (this._data?.career_id  ?? this._data?.career  ?? this._data?.profile?.career  ?? d.profile?.career  ?? '');

    const domSpecies = readIfExists('#cg-species');
    const domCareer  = readIfExists('#cg-career');

    const speciesVal = String((domSpecies !== undefined ? domSpecies : memSpecies) ?? '');
    const careerVal  = String((domCareer  !== undefined ? domCareer  : memCareer ) ?? '');

    d.species_id = speciesVal;
    d.career_id  = careerVal;

    d.species    = speciesVal;
    d.career     = careerVal;

    d.profile = (d.profile && typeof d.profile === 'object') ? d.profile : {};
    d.profile.species = speciesVal;
    d.profile.career  = careerVal;

    this._data = this._data || {};
    this._data.species_id = speciesVal;
    this._data.career_id  = careerVal;
    this._data.species    = speciesVal;
    this._data.career     = careerVal;
    this._data.profile    = (this._data.profile && typeof this._data.profile === 'object') ? this._data.profile : {};
    this._data.profile.species = speciesVal;
    this._data.profile.career  = careerVal;

    // Traits — only overwrite if the select exists
    TraitsService.TRAITS.forEach(key => {
      const sel = `#cg-${key}`;
      const v = readIfExists(sel);
      if (v !== undefined) {
        d[key] = v;
        this._data[key] = v;
      } else if (d[key] == null && this._data && this._data[key] != null) {
        d[key] = this._data[key];
      } else if (d[key] == null) {
        d[key] = '';
      }
    });

    // Skill marks:
    // 1) start with in-memory (normalize array/object)
    // 2) merge legacy inputs if present
    // 3) CG_SKILLMARKS_DOMSCAN: merge from active mark buttons (Skills UI uses buttons)
    const mergedMarks = { ...normalizeMarks(this._data.skillMarks) };

    $('input.skill-marks').each((i, el) => {
      const skillId = $(el).data('skill-id');
      const val     = parseInt($(el).val(), 10) || 0;
      if (skillId != null) mergedMarks[String(skillId)] = val;
    });

    // CG_SKILLMARKS_DOMSCAN:
    // Skills UI uses buttons like .skill-mark-btn with data-skill-id + data-mark.
    // Use any "active/selected" cue to infer current marks.
    const btnMarks = {};
    $('.skill-mark-btn').each((i, el) => {
      const $b = $(el);
      const isOn =
        $b.hasClass('active') ||
        $b.hasClass('is-active') ||
        $b.hasClass('selected') ||
        String($b.attr('aria-pressed') || '') === 'true' ||
        String($b.data('active') || '') === '1' ||
        String($b.attr('data-active') || '') === '1';

      if (!isOn) return;

      const sid = $b.data('skill-id');
      const mk  = parseInt($b.data('mark'), 10) || 0;
      const key = (sid == null) ? '' : String(sid);
      if (!key) return;

      if (btnMarks[key] == null || btnMarks[key] < mk) btnMarks[key] = mk;
    });

    if (Object.keys(btnMarks).length) {
      Object.keys(btnMarks).forEach(k => { mergedMarks[String(k)] = btnMarks[k]; });
    }

    d.skillMarks = mergedMarks;
    this._data.skillMarks = mergedMarks;

    // Free-choice gifts:
    const s0 = readIfExists('#cg-free-choice-0');
    const s1 = readIfExists('#cg-free-choice-1');
    const s2 = readIfExists('#cg-free-choice-2');

    let freeArr;
    if (s0 !== undefined || s1 !== undefined || s2 !== undefined) {
      freeArr = normalize3([s0 ?? '', s1 ?? '', s2 ?? '']);
    } else if (Array.isArray(this._data.free_gifts)) {
      freeArr = normalize3(this._data.free_gifts);
    } else if (Array.isArray(this._data.freeGifts)) {
      freeArr = normalize3(this._data.freeGifts);
    } else if (
      this._data['free-choice-0'] != null ||
      this._data['free-choice-1'] != null ||
      this._data['free-choice-2'] != null
    ) {
      freeArr = normalize3([this._data['free-choice-0'], this._data['free-choice-1'], this._data['free-choice-2']]);
    } else {
      freeArr = normalize3([this._data.free_gift_1, this._data.free_gift_2, this._data.free_gift_3]);
    }

    d.free_gifts  = freeArr;
    d.freeGifts   = freeArr.slice();
    d.free_gift_1 = freeArr[0] || '';
    d.free_gift_2 = freeArr[1] || '';
    d.free_gift_3 = freeArr[2] || '';

    d['free-choice-0'] = d.free_gift_1;
    d['free-choice-1'] = d.free_gift_2;
    d['free-choice-2'] = d.free_gift_3;

    this._data.free_gifts   = freeArr.slice();
    this._data.freeGifts    = freeArr.slice();
    this._data.free_gift_1  = d.free_gift_1;
    this._data.free_gift_2  = d.free_gift_2;
    this._data.free_gift_3  = d.free_gift_3;
    this._data['free-choice-0'] = d['free-choice-0'];
    this._data['free-choice-1'] = d['free-choice-1'];
    this._data['free-choice-2'] = d['free-choice-2'];

    if (Array.isArray(window.CG_SKILLS_LIST)) d.skillsList = window.CG_SKILLS_LIST;

    // CG_EC_PERSIST_REGEX_V1: persist extra-career ids (write into returned `d`; DOM is source-of-truth)
    try {
      const domVals = [];
      try {
        document.querySelectorAll('select.cg-extra-career-select').forEach(sel => {
          const v = String(sel?.value || '').trim();
          const n = parseInt(v, 10) || 0;
          domVals.push(n > 0 ? String(n) : '');
        });
      } catch (_) {}
      while (domVals.length < 2) domVals.push('');

      const pick = (...vals) => {
        for (const v of vals) {
          if (v == null) continue;
          const sv = String(v).trim();
          const nv = parseInt(sv, 10) || 0;
          if (nv > 0) return String(nv);
        }
        return '';
      };

      const ecs = Array.isArray(this._data?.extraCareers) ? this._data.extraCareers : null;
      const v1 = pick(d.extra_career_1, this._data?.extra_career_1, ecs?.[0]?.id, domVals[0]);
      const v2 = pick(d.extra_career_2, this._data?.extra_career_2, ecs?.[1]?.id, domVals[1]);

      d.extra_career_1 = v1;
      d.extra_career_2 = v2;
      this._data.extra_career_1 = v1;
      this._data.extra_career_2 = v2;
    } catch (_) {}

    // CG_CAREER_GIFT_REPLACEMENTS_PERSIST: collect replacement gift selections
    try {
      // Priority: DOM dropdowns > memory > empty
      const domReplacements = {};
      document.querySelectorAll('.cg-career-gift-replace').forEach(sel => {
        const slot = sel.getAttribute('data-slot');
        const val = sel.value;
        if (slot && val && val !== '0') {
          domReplacements[String(slot)] = String(val);
        }
      });

      const memReplacements = this._data?.career_gift_replacements || {};

      // Merge: DOM wins if present, but keep memory values for slots not in DOM
      const merged = { ...memReplacements };
      Object.keys(domReplacements).forEach(k => {
        if (domReplacements[k]) {
          merged[k] = domReplacements[k];
        }
      });

      // Clean: only keep slots with non-empty values
      const cleaned = {};
      Object.keys(merged).forEach(k => {
        const v = merged[k];
        if (v && String(v).trim() && String(v) !== '0') {
          cleaned[k] = String(v);
        }
      });

      d.career_gift_replacements = cleaned;
      this._data.career_gift_replacements = cleaned;
    } catch (_) {
      d.career_gift_replacements = this._data?.career_gift_replacements || {};
    }

    return d;
  },

  /**
   * Save the character via WP-AJAX and optionally close builder.
   * Guarded to prevent double-save if multiple click handlers exist.
   */
  save(shouldClose = false) {
    if (window.CG_SAVE_IN_FLIGHT) {
      console.warn('[FormBuilderAPI] save() blocked: CG_SAVE_IN_FLIGHT already true');
      return $.Deferred().reject('in-flight').promise();
    }

    window.CG_SAVE_IN_FLIGHT = true;

    const raw = this.collectFormData();
    console.log("[CG_SAVE_DEBUG_SKILLMARKS]", Object.keys(((raw && raw.skillMarks) ? raw.skillMarks : {})).length, ((raw && raw.skillMarks) ? raw.skillMarks : {}));
    console.log('[FormBuilderAPI] ▶ save()', raw);

    const { url } = ajaxEnv();
    if (!url) {
      window.CG_SAVE_IN_FLIGHT = false;
      console.error('[FormBuilderAPI] save(): No AJAX URL available');
      alert('Save error: missing AJAX URL');
      return $.Deferred().reject('no-url').promise();
    }

    const data = buildPayload(raw);

    const req = $.post(url, data);

    req
      .done(res => {
        try { res = (typeof res === 'string') ? JSON.parse(res) : res; } catch (_) {}
        if (!res || res.success !== true) {
          console.error('[FormBuilderAPI] save.error()', res);
          alert('Save failed: ' + (res?.data || 'Invalid payload.'));
          return;
        }

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

        document.dispatchEvent(new CustomEvent('cg:character:saved',    { detail: { id: res.data?.id || raw.id || null, record: raw } }));
        document.dispatchEvent(new CustomEvent('cg:characters:refresh', { detail: {} }));
      })
      .fail((xhr, status, err) => {
        console.error('[FormBuilderAPI] save.fail()', status, err, xhr?.responseText);
        alert('Save failed—check console for details.');
      })
      .always(() => {
        window.CG_SAVE_IN_FLIGHT = false;
      });

    return req;
  },

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


// CG_EC_PERSIST_REGEX_V1: window alias (debug)
try {
  window.FormBuilderAPI = window.FormBuilderAPI || FormBuilderAPI;
  window.CG_FormBuilderAPI = window.CG_FormBuilderAPI || FormBuilderAPI;
} catch (_) {}
