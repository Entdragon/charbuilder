// assets/js/src/core/gifts/free-choices.js
//
// Renders the "Chosen" Free Gifts (3) selects inside the Gifts tab placeholder #cg-free-choices.
//
// Requirements:
// - Filtering MUST work even before choosing career/species (only depends on currently-owned gifts + traits).
// - Filtering MUST respect prerequisite gifts in ct_gifts_requires* columns.
// - Filtering MUST respect minimum trait requirements parsed from requires_special text (e.g. "Mind of d8 or better").
// - Slot-scoped qualification selectors (Language/Literacy/Insider/Mystic/Piety) are rendered INLINE under each slot,
//   driven by requires_special lines like "Piety: ..." / "Mystic: ..." etc.
// - Base "everyone gets one Language" selector is handled by quals/ui.js; we do NOT render that here.
//
// Exposes: window.CG_FreeChoices

import FormBuilderAPI from '../formBuilder';
import State from './state.js';
import Quals from '../quals/index.js';
import QualState from '../quals/state.js';

function cgWin() {
  if (typeof globalThis !== 'undefined') return globalThis;
  if (typeof window !== 'undefined') return window;
  return {};
}

const W = cgWin();
const $ = (W && W.jQuery) ? W.jQuery : null;

const log = (...a) => { try { console.log('[FreeChoices]', ...a); } catch (_) {} };
const warn = (...a) => { try { console.warn('[FreeChoices]', ...a); } catch (_) {} };

const ALWAYS_ACQUIRED_GIFT_IDS = ['242', '236']; // Local Knowledge, Language (always acquired in your model)

const KNACK_FOR_GIFT_ID = '232'; // "Knack For: [Choice]" — grants +1 mark in a chosen skill

function setGiftSkillMarks(map) {
  const clean = (map && typeof map === 'object' && !Array.isArray(map)) ? map : {};
  const d = getBuilderData();
  if (FormBuilderAPI && FormBuilderAPI._data) {
    FormBuilderAPI._data.gift_skill_marks = clean;
  } else {
    d.gift_skill_marks = clean;
  }
}

function recomputeGiftSkillMarks(slotQualMap) {
  // Count how many Knack For gifts chose each skill across all slots
  const marks = {};
  const slots = getFreeGiftSlotsFromData();
  [0, 1, 2].forEach(i => {
    const gId = String(slots[i] || '').trim();
    if (gId !== KNACK_FOR_GIFT_ID) return;
    const sKey = String(i);
    const skillId = String((slotQualMap[sKey] || {}).knack_skill || '').trim();
    if (!skillId) return;
    marks[skillId] = (marks[skillId] || 0) + 1;
  });
  setGiftSkillMarks(marks);
  try {
    const detail = { gift_skill_marks: marks };
    document.dispatchEvent(new CustomEvent('cg:gift-skill-marks:changed', { detail }));
    if ($) $(document).trigger('cg:gift-skill-marks:changed', [detail]);
  } catch (_) {}
}

function normalize3(arr) {
  const out = (Array.isArray(arr) ? arr : []).slice(0, 3).map(v => (v ? String(v) : ''));
  while (out.length < 3) out.push('');
  return out;
}

function getBuilderData() {
  if (FormBuilderAPI && FormBuilderAPI._data) return FormBuilderAPI._data;
  if (typeof FormBuilderAPI?.getData === 'function') return FormBuilderAPI.getData() || {};
  return {};
}

function setBuilderKey(key, value) {
  const d = getBuilderData();
  try {
    if (FormBuilderAPI && FormBuilderAPI._data) FormBuilderAPI._data[key] = value;
    else d[key] = value;
  } catch (_) {}
}

function getAjax() {
  const env = W.CG_AJAX || W.CG_Ajax || {};
  const base = (typeof W.CG_API_BASE === 'string' && W.CG_API_BASE)
    ? W.CG_API_BASE.replace(/\/+$/, '') : '';
  if (base) return Object.assign({}, env, { ajax_url: base + '/api/ajax' });
  return env;
}

function getNonceFor(action) {
  const nonces = W.CG_NONCES || {};
  if (nonces && nonces[action]) return nonces[action];
  const ajax = getAjax();
  if (ajax && ajax.nonce) return ajax.nonce;
  if (ajax && ajax.security) return ajax.security;
  return '';
}

function ajaxPost(payload) {
  const ajax = getAjax();
  const url = ajax.ajax_url || ajax.url || W.ajaxurl || '';
  const data = { ...(payload || {}) };

  const nonce = getNonceFor(String(data.action || ''));
  if (nonce && !data.security && !data.nonce && !data._ajax_nonce) {
    data.security = nonce;
    data.nonce = nonce;
    data._ajax_nonce = nonce;
  }

  if ($ && url) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url,
        method: 'POST',
        data,
        dataType: 'json',
        success: (res) => resolve(res),
        error: (xhr, status, err) => reject(err || status || xhr)
      });
    });
  }

  if (!url) return Promise.reject(new Error('Missing AJAX url'));
  const body = new URLSearchParams();
  Object.entries(data).forEach(([k, v]) => body.append(k, String(v ?? '')));

  return fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body
  }).then(r => r.json());
}

function modalRoot() {
  if (typeof document === 'undefined') return null;
  return document.querySelector('#cg-modal') || null;
}

function getFreeGiftSlotsFromData() {
  try {
    if (State && typeof State.getFreeGifts === 'function') return normalize3(State.getFreeGifts());
  } catch (_) {}

  const d = getBuilderData();
  const v =
    d.free_gifts ||
    d.freeGifts ||
    d.cg_free_gifts ||
    d.free_choices ||
    d.freeChoices ||
    [];

  return normalize3(Array.isArray(v) ? v : [v]);
}

function emitFreeGiftChanged(selected, source = 'free-choices') {
  try {
    const detail = { free_gifts: selected.slice(), source: String(source || '') };
    document.dispatchEvent(new CustomEvent('cg:free-gift:changed', { detail }));
    if ($) $(document).trigger('cg:free-gift:changed', [detail]);
  } catch (_) {}
}

function setFreeGiftSlotsToData(slots, source = 'free-choices') {
  const normalized = normalize3(slots);

  setBuilderKey('free_gifts', normalized);
  setBuilderKey('freeGifts', normalized);
  setBuilderKey('cg_free_gifts', normalized);

  let stateHandled = false;
  try {
    if (State && typeof State.setSelected === 'function') {
      State.setSelected(normalized);
      stateHandled = true;
    } else if (State && typeof State.setFreeGifts === 'function') {
      State.setFreeGifts(normalized, { source });
      stateHandled = true;
    }
  } catch (_) {}

  if (!stateHandled) emitFreeGiftChanged(normalized, source);

  return normalized;
}

function giftId(g) {
  return String(g?.id ?? g?.ct_id ?? g?.gift_id ?? g?.ct_gift_id ?? '');
}

function giftName(g) {
  return String(g?.name ?? g?.title ?? g?.gift_name ?? g?.ct_gift_name ?? '');
}

function requiresSpecialText(g) {
  const rs = (g?.requires_special ?? g?.ct_gifts_requires_special ?? g?.requiresSpecial ?? '');
  return String(rs ?? '').trim();
}

function allowsMultiple(g) {
  // Gift 223 (Increased Trait: Career) is always allowed multiple times so each
  // free-choice copy can independently target a different extra career.
  if (g && (String(g.id || '') === '223' || String(g.ct_id || '') === '223')) return true;

  // Any gift that requires a qualification sub-choice (Language, Literacy, Mystic,
  // Piety, Insider) can always be taken multiple times — uniqueness is enforced at
  // the qualification-value level, not the gift level.
  if (detectQualTypesNeeded(g).length > 0) return true;

  // Fix: use > 0 not > 1 — a DB value of 1 means "allows multiple".
  const v = g?.allows_multiple ?? g?.ct_gifts_manifold ?? g?.manifold ?? null;
  const n = Number(v);
  return Number.isFinite(n) && n > 0;
}

function extractRequiredGiftIds(g) {
  if (!g || typeof g !== 'object') return [];
  const out = [];
  Object.keys(g).forEach(k => {
    if (!/^ct_gifts_requires(_[a-z]+)?$/i.test(k) && !/^ct_gifts_requires_/i.test(k)) return;
    if (k.toLowerCase() === 'ct_gifts_requires_special') return;
    const v = g[k];
    if (v == null) return;
    const s = String(v).trim();
    if (!s) return;
    s.split(',').map(x => x.trim()).filter(Boolean).forEach(x => out.push(String(x)));
  });
  return [...new Set(out)];
}

function diceToNum(s) {
  const t = String(s || '').trim().toLowerCase();
  const m = t.match(/d\s*(4|6|8|10|12)/);
  return m ? Number(m[1]) : null;
}

function getTraitDieValue(traitKey) {
  const d = getBuilderData();

  const pools = [];
  if (d && typeof d === 'object') {
    if (d.traits && typeof d.traits === 'object') pools.push(d.traits);
    if (d.cg_traits && typeof d.cg_traits === 'object') pools.push(d.cg_traits);
    pools.push(d);
  }

  const keys = [traitKey, `trait_${traitKey}`, `${traitKey}_die`, `die_${traitKey}`];

  for (const obj of pools) {
    for (const k of keys) {
      if (!obj || !(k in obj)) continue;
      const v = obj[k];
      const n = diceToNum(v);
      if (n != null) return n;
    }
  }

  return null;
}

function extractTraitMinimaFromRequiresSpecial(rs) {
  const text = String(rs || '');
  if (!text) return [];

  const traits = [
    { key: 'mind',    re: /\bmind\b/i },
    { key: 'body',    re: /\bbody\b/i },
    { key: 'speed',   re: /\bspeed\b/i },
    { key: 'will',    re: /\bwill\b/i },
    { key: 'species', re: /\bspecies\b/i },
  ];

  const mins = [];
  const parts = text.split(/[\n\r.;]+/).map(s => s.trim()).filter(Boolean);

  parts.forEach(p => {
    traits.forEach(t => {
      if (!t.re.test(p)) return;
      const m = p.match(/d\s*(4|6|8|10|12)/i);
      if (!m) return;
      const need = Number(m[1]);
      if (!Number.isFinite(need)) return;
      mins.push({ traitKey: t.key, need, raw: p });
    });
  });

  const best = new Map();
  mins.forEach(x => {
    const cur = best.get(x.traitKey);
    if (!cur || x.need > cur.need) best.set(x.traitKey, x);
  });

  return Array.from(best.values());
}

function traitMinimaSatisfied(g) {
  const rs = requiresSpecialText(g);
  const mins = extractTraitMinimaFromRequiresSpecial(rs);
  if (!mins.length) return true;

  for (const m of mins) {
    const have = getTraitDieValue(m.traitKey);
    if (have == null) return false;
    if (have < m.need) return false;
  }
  return true;
}

/**
 * Handles comparative trait requirements, e.g.:
 *   "Speed must be greater than Body"
 *   "Species Trait must be higher than your Mind Trait"
 */
function comparativeTraitsSatisfied(g) {
  const rs = requiresSpecialText(g);
  if (!rs) return true;

  const TRAIT_NAMES = ['mind', 'body', 'speed', 'will', 'species'];

  const lines = rs.split(/[\n\r.;]+/).map(s => s.trim()).filter(Boolean);
  for (const line of lines) {
    // Match "X must be greater than Y" / "X must be higher than Y"
    const m = line.match(/\b(mind|body|speed|will|species)[^\w]*(trait)?\s+must\s+be\s+(greater|higher)\s+than\s+(your\s+)?(mind|body|speed|will|species)/i);
    if (!m) continue;
    const leftKey  = m[1].toLowerCase();
    const rightKey = m[5].toLowerCase();
    const leftVal  = getTraitDieValue(leftKey);
    const rightVal = getTraitDieValue(rightKey);
    // If either trait is unknown, don't block (give benefit of the doubt)
    if (leftVal == null || rightVal == null) continue;
    if (leftVal <= rightVal) return false;
  }
  return true;
}

function extractQualReqLinesFromRequiresSpecial(rs) {
  const lines = String(rs || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
  const out = [];
  for (const line of lines) {
    const m = line.match(/^(language|literacy|insider|mystic|piety|ordainment)\s*:\s*(.+)$/i);
    if (!m || !m[1] || !m[2]) continue;
    out.push({ type: String(m[1]).toLowerCase(), raw: String(m[2]).trim() });
  }
  return out;
}

/**
 * Enforce simple qualification prerequisites expressed in requires_special, e.g.:
 *   "Language: Magniloquentia"
 *   "Mystic: Elementalism"
 *
 * We ONLY enforce explicit "Type: Value" lines for known types.
 * Anything else in requires_special is ignored here (future work).
 */
function qualPrereqsSatisfied(g) {
  const rs = requiresSpecialText(g);
  if (!rs) return true;

  // Non-enforceable GM permission text
  if (/permission\s+from\s+the\s+game\s+host/i.test(rs)) return true;

  const reqs = extractQualReqLinesFromRequiresSpecial(rs);
  if (!reqs.length) return true;

  try { ensureQualStateInit(); } catch (_) {}

  for (const r of reqs) {
    const type = r.type;
    const raw = String(r.raw || '').trim();
    if (!type || !raw) continue;

    // Ambiguous placeholders: do not block eligibility on these
    if (/\[\s*choice\s*\]/i.test(raw) || /\bchoice\b/i.test(raw)) continue;

    const parts = raw
      .split(/,|\/|;|\bor\b/ig)
      .map(s => String(s || '').trim())
      .filter(Boolean);

    if (!parts.length) continue;

    let ok = false;
    for (const p of parts) {
      if (!p) continue;
      if (/^any\b/i.test(p)) { ok = true; break; }
      if (qualStateHas(type, p)) { ok = true; break; }
    }
    if (!ok) return false;
  }

  return true;
}


function computeOwnedGiftIdSet(selectedFreeGiftIds = []) {
  const owned = new Set();
  ALWAYS_ACQUIRED_GIFT_IDS.forEach(id => owned.add(String(id)));
  (selectedFreeGiftIds || []).filter(Boolean).forEach(id => owned.add(String(id)));

  const d = getBuilderData();
  const candidateKeys = [
    'gift_id_1','gift_id_2','gift_id_3',
    'species_gift_one','species_gift_two','species_gift_three',
    'career_gift_one','career_gift_two','career_gift_three',
    'species_gifts','career_gifts',
    'gift_ids','giftIds','gifts',
      'career_gift_replacements',
      'cg_career_gift_replacements',
  ];

  candidateKeys.forEach(k => {
    if (!d || !(k in d)) return;
    const v = d[k];
    if (Array.isArray(v)) v.forEach(x => x && owned.add(String(x)));
    else if (v && typeof v === 'object') Object.values(v).forEach(x => x && owned.add(String(x)));
    else if (v) owned.add(String(v));
  });

  const apis = [W.SpeciesAPI, W.CareerAPI].filter(Boolean);
  apis.forEach(api => {
    const prof = api?.currentProfile || api?.profile || api?.current || api?.selected || null;
    if (!prof) return;
    candidateKeys.forEach(k => {
      if (!(k in prof)) return;
      const v = prof[k];
      if (Array.isArray(v)) v.forEach(x => x && owned.add(String(x)));
      else if (v && typeof v === 'object') Object.values(v).forEach(x => x && owned.add(String(x)));
      else if (v) owned.add(String(v));
    });
  });

  return owned;
}

function giftEffectDescription(g) {
  if (!g) return '';
  const v = g.effect_description ?? g.ct_gifts_effect_description ?? '';
  return String(v || '').trim();
}

function isNaturalGift(g) {
  if (!g) return false;
  const cls = String(g.giftclass ?? g.ct_gifts_class ?? g.gift_class ?? '').trim().toLowerCase();
  return cls === 'natural';
}

function getCharacterSpecies() {
  const d = getBuilderData();
  const raw = d.species || d.cg_species || d.species_name || d.speciesName || '';
  return String(raw || '').trim().toLowerCase();
}

function speciesAnyofMet(prereq) {
  const species = getCharacterSpecies();
  if (!species) return null;
  const allowed = String(prereq.req_value || '').split(/[|,]/).map(s =>
    String(s || '').replace(/\bor\s+/i, '').trim().toLowerCase()
  ).filter(Boolean);
  if (!allowed.length) return null;
  if (allowed.some(a => species.includes(a) || a.includes(species))) return true;
  return false;
}

function speciesForbidMet(prereq) {
  const species = getCharacterSpecies();
  if (!species) return null;
  const forbidden = String(prereq.req_value || '').split(/[|,]/).map(s =>
    String(s || '').replace(/\bor\s+/i, '').trim().toLowerCase()
  ).filter(Boolean);
  if (!forbidden.length) return null;
  if (forbidden.some(f => species.includes(f) || f.includes(species))) return false;
  return true;
}

function traitMinStructuredMet(prereq) {
  const traitKey = String(prereq.trait_key || '').trim().toLowerCase().replace(/_trait$/, '');
  const needed = Number(prereq.die_min);
  if (!traitKey || !Number.isFinite(needed)) return null;
  const have = getTraitDieValue(traitKey);
  if (have == null) return null;
  const cmp = String(prereq.comparator || '>=');
  if (cmp === '>=') return have >= needed;
  if (cmp === '>') return have > needed;
  if (cmp === '=') return have === needed;
  if (cmp === '<') return have < needed;
  if (cmp === '<=') return have <= needed;
  return have >= needed;
}

function gmApprovalRequired(g) {
  const prereqs = Array.isArray(g && g.prereqs) ? g.prereqs : [];
  if (prereqs.some(p => p.kind === 'gm_permission')) return true;

  const rs = requiresSpecialText(g);
  return /permission\s+from\s+the\s+game\s+host/i.test(rs) ||
         /gm\s+approval/i.test(rs) ||
         /game\s+host\s+approval/i.test(rs);
}

function getGmApprovalReason(g) {
  const prereqs = Array.isArray(g && g.prereqs) ? g.prereqs : [];
  const gmPrereq = prereqs.find(p => p.kind === 'gm_permission');
  if (gmPrereq) {
    return String(gmPrereq.raw_text || gmPrereq.req_value || 'Permission from the Game Host').trim();
  }

  const rs = requiresSpecialText(g);
  const lines = String(rs || '').split(/[\r\n]+/).map(s => s.trim()).filter(Boolean);
  for (const line of lines) {
    if (/permission\s+from\s+the\s+game\s+host/i.test(line) ||
        /gm\s+approval/i.test(line) ||
        /game\s+host\s+approval/i.test(line)) {
      return line;
    }
  }
  return 'Requires GM/Game Host approval';
}

const KNOWN_PREREQ_KINDS = new Set([
  'gm_permission', 'species_anyof', 'species_forbid', 'trait_min',
  'trait_compare', 'gift_trait', 'gift_ref', 'special_text', 'note',
]);

function evaluateStructuredPrereqs(g, ownedSet) {
  const prereqs = Array.isArray(g && g.prereqs) ? g.prereqs : [];
  const requirements = Array.isArray(g && g.requirements) ? g.requirements : [];

  for (const prereq of prereqs) {
    const kind = String(prereq.kind || '');

    if (kind === 'gm_permission') continue;

    if (kind === 'species_anyof') {
      const result = speciesAnyofMet(prereq);
      if (result === false) {
        return String(prereq.raw_text || prereq.req_value || 'Species requirement not met');
      }
      continue;
    }

    if (kind === 'species_forbid') {
      const result = speciesForbidMet(prereq);
      if (result === false) {
        return String(prereq.raw_text || prereq.req_value || 'Species forbidden');
      }
      continue;
    }

    if (kind === 'trait_min') {
      const result = traitMinStructuredMet(prereq);
      if (result === false) {
        const traitKey = String(prereq.trait_key || '');
        const needed = Number(prereq.die_min);
        return `Requires: ${traitKey} d${needed}+`;
      }
      continue;
    }

    if (kind === 'trait_compare') {
      if (!comparativeTraitsSatisfied(g)) {
        return String(prereq.raw_text || 'Trait comparison requirement not met');
      }
      continue;
    }

    if (kind === 'gift_trait') {
      const reqKey = String(prereq.req_key || prereq.req_value || '').trim();
      if (reqKey && !ownedSet.has(reqKey)) {
        return `Requires gift trait: ${reqKey}`;
      }
      continue;
    }
  }

  for (const req of requirements) {
    const kind = String(req.kind || '');

    if (kind === 'gift_ref') {
      const refId = String(req.ref_id || '');
      if (refId && !ownedSet.has(refId)) {
        const reqName = String(req.text || `gift #${refId}`).trim();
        return `Requires: ${reqName}`;
      }
      continue;
    }
  }

  return null;
}

function getUnknownPrereqNotes(g) {
  const prereqs = Array.isArray(g && g.prereqs) ? g.prereqs : [];
  const requirements = Array.isArray(g && g.requirements) ? g.requirements : [];
  const notes = [];

  for (const prereq of prereqs) {
    const kind = String(prereq.kind || '');
    if (KNOWN_PREREQ_KINDS.has(kind)) continue;
    const text = String(prereq.raw_text || prereq.req_value || '').trim();
    if (text) notes.push(text);
  }

  for (const req of requirements) {
    const kind = String(req.kind || '');
    if (KNOWN_PREREQ_KINDS.has(kind)) continue;
    const text = String(req.text || '').trim();
    if (text) notes.push(text);
  }

  return notes;
}

function giftIneligibleReason(g, ownedSet, otherSelectedIds = new Set()) {
  if (!g) return 'Unknown gift';
  const id = giftId(g);
  const name = giftName(g);
  if (!id || !name) return 'Invalid gift data';

  if (isNaturalGift(g)) return 'Natural gift (granted by species)';

  const structuredReason = evaluateStructuredPrereqs(g, ownedSet);
  if (structuredReason) return structuredReason;

  const reqIds = extractRequiredGiftIds(g);
  for (const rid of reqIds) {
    if (!ownedSet.has(String(rid))) {
      return `Requires gift #${rid}`;
    }
  }

  if (!traitMinimaSatisfied(g)) {
    const rs = requiresSpecialText(g);
    const mins = extractTraitMinimaFromRequiresSpecial(rs);
    const parts = mins.map(m => {
      const have = getTraitDieValue(m.traitKey);
      if (have == null || have < m.need) return `${m.traitKey} d${m.need}+`;
      return null;
    }).filter(Boolean);
    return 'Requires: ' + (parts.join(', ') || 'higher trait');
  }

  if (!comparativeTraitsSatisfied(g)) {
    return 'Trait comparison requirement not met';
  }

  if (!qualPrereqsSatisfied(g)) {
    const rs = requiresSpecialText(g);
    const reqs = extractQualReqLinesFromRequiresSpecial(rs);
    if (reqs.length) {
      const parts = reqs.map(r => `${r.type}: ${r.raw}`);
      return 'Requires: ' + parts.join('; ');
    }
    return 'Qualification prerequisite not met';
  }

  if (otherSelectedIds.has(id) && !allowsMultiple(g)) return 'Already selected in another slot';

  return null;
}

function giftEligible(g, ownedSet, otherSelectedIds = new Set()) {
  if (!g) return false;
  const id = giftId(g);
  const name = giftName(g);
  if (!id || !name) return false;

  if (isNaturalGift(g)) return false;

  const structuredReason = evaluateStructuredPrereqs(g, ownedSet);
  if (structuredReason) return false;

  const reqIds = extractRequiredGiftIds(g);
  for (const rid of reqIds) {
    if (!ownedSet.has(String(rid))) return false;
  }

  if (!traitMinimaSatisfied(g)) return false;

  if (!comparativeTraitsSatisfied(g)) return false;

  if (!qualPrereqsSatisfied(g)) return false;

  if (otherSelectedIds.has(id) && !allowsMultiple(g)) return false;

  return true;
}

function ensureSectionInHost(host) {
  if (typeof document === 'undefined') return null;

  let section = document.getElementById('cg-free-gifts');
  if (!section) {
    section = document.createElement('div');
    section.id = 'cg-free-gifts';
    section.className = 'cg-free-gifts';
    section.innerHTML = `
      <div class="cg-free-row" style="display:flex; flex-direction:column; gap:6px;"></div>
    `;
  }

  try {
    if (host && section.parentNode !== host) host.appendChild(section);
  } catch (_) {}

  return section;
}

function getSlotQualMap() {
  const d = getBuilderData();
  const m = d.free_gift_quals || d.cg_free_gift_quals || d.freeGiftQuals || {};
  if (!m || typeof m !== 'object') return {};
  return JSON.parse(JSON.stringify(m));
}

function setSlotQualMap(map) {
  const clean = map && typeof map === 'object' ? map : {};
  setBuilderKey('free_gift_quals', clean);
  setBuilderKey('cg_free_gift_quals', clean);
  setBuilderKey('freeGiftQuals', clean);
}

function stripDiacritics(s) {
  return String(s || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

function canon(s) {
  return stripDiacritics(String(s || ''))
    .trim()
    .replace(/\s+/g, ' ')
    .toLowerCase();
}

function detectQualTypesNeeded(g) {
  const rs = requiresSpecialText(g);
  const name = giftName(g).toLowerCase();

  const found = new Set();
  const lines = String(rs || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
  lines.forEach(line => {
    const m = line.match(/^(language|literacy|insider|mystic|piety)\s*:/i);
    if (m && m[1]) found.add(String(m[1]).toLowerCase());
  });

  if (found.size === 0) {
    if (name.includes('piety')) found.add('piety');
    if (name.includes('mystic')) found.add('mystic');
    if (name.includes('insider')) found.add('insider');
    if (name.includes('literacy')) found.add('literacy');
    if (name.includes('language')) found.add('language');
  }

  const TYPES = ['language', 'literacy', 'insider', 'mystic', 'piety'];
  return TYPES.filter(t => found.has(t));
}

/**
 * IMPORTANT:
 * Primary source is Quals.get(type), BUT that catalog can be empty early in the session.
 * Fallback: derive possible values directly from the gifts list by scanning requires_special lines.
 */
function getQualItemsForType(type, allGifts) {
  let items = [];
  try {
    items = (Quals && typeof Quals.get === 'function') ? (Quals.get(type) || []) : [];
  } catch (_) {
    items = [];
  }

  if (Array.isArray(items) && items.length > 0) return items;

  // Fallback: parse "Type: Value" lines across ALL gifts
  const want = String(type || '').toLowerCase();
  const rx = new RegExp(`^\\s*${want}\\s*:\\s*(.+)$`, 'i');

  const keep = new Map(); // canon -> bestLabel

  const list = Array.isArray(allGifts) ? allGifts : [];
  for (const g of list) {
    const rs = requiresSpecialText(g);
    if (!rs) continue;

    const lines = rs.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
    for (const line of lines) {
      const m = line.match(rx);
      if (!m || !m[1]) continue;

      // Value part may contain multiple entries separated by commas or " or "
      const raw = String(m[1]).trim();
      const parts = raw
        .split(/,|\/|;|\bor\b/ig)
        .map(s => String(s || '').trim())
        .filter(Boolean);

      for (const p of parts) {
        // Avoid obvious non-values
        if (/^any\b/i.test(p)) continue;
        const k = canon(p);
        if (!k) continue;
        if (!keep.has(k)) keep.set(k, p);
      }
    }
  }

  const labels = Array.from(keep.values())
    .sort((a, b) => String(a).localeCompare(String(b), undefined, { sensitivity: 'base' }));

  // Normalize to the same shape Quals.get typically returns
  return labels.map(label => ({ label, key: label }));
}

function ensureQualStateInit() {
  try { QualState?.init?.(); } catch (_) {}
}

function qualStateHas(type, value) {
  type = String(type || '').toLowerCase();

  if (!value) return false;
  try {
    if (typeof QualState?.has === 'function') return !!QualState.has(type, value);
    const arr = QualState?.get?.(type) || [];
    return arr.some(v => canon(v) === canon(value));
  } catch (_) {
    return false;
  }
}

function qualStateAdd(type, value) {
  if (!value) return;
  ensureQualStateInit();

  if (type === 'language') {
    try {
      const cur = (QualState?.get?.('language') || []).slice();
      const base = cur[0] || '';
      if (canon(base) === canon(value)) return;
      if (qualStateHas('language', value)) return;

      if (QualState?.data && Array.isArray(QualState.data.language) && typeof QualState.persist === 'function') {
        QualState.data.language = [base, ...cur.slice(1).filter(v => canon(v) !== canon(value)), value].filter(Boolean);
        QualState.persist();
      } else if (typeof QualState?.add === 'function') {
        QualState.add('language', value);
      }
    } catch (_) {}
    return;
  }

  try {
    if (qualStateHas(type, value)) return;
    if (typeof QualState?.add === 'function') QualState.add(type, value);
    else if (QualState?.data && Array.isArray(QualState.data[type]) && typeof QualState.persist === 'function') {
      QualState.data[type] = (QualState.data[type] || []).concat([value]);
      QualState.persist();
    }
  } catch (_) {}
}

function qualStateRemoveIfSafe(type, value, slotMap) {
  if (!value) return;
  ensureQualStateInit();

  const stillUsed = Object.values(slotMap || {}).some(slotObj => {
    if (!slotObj || typeof slotObj !== 'object') return false;
    const v = slotObj[type];
    return v && canon(v) === canon(value);
  });
  if (stillUsed) return;

  if (type === 'language') {
    try {
      const cur = (QualState?.get?.('language') || []).slice();
      const base = cur[0] || '';
      if (canon(base) === canon(value)) return;
    } catch (_) {}
  }

  try {
    if (typeof QualState?.remove === 'function') QualState.remove(type, value);
    else if (QualState?.data && Array.isArray(QualState.data[type]) && typeof QualState.persist === 'function') {
      QualState.data[type] = (QualState.data[type] || []).filter(v => canon(v) !== canon(value));
      QualState.persist();
    }
  } catch (_) {}
}

function renderQualSelectHtml({ slot, type, value, allGifts, excludeValues = [] }) {
  const items = getQualItemsForType(type, allGifts);
  const excluded = new Set((excludeValues || []).map(v => canon(String(v || ''))).filter(Boolean));
  const opts = (Array.isArray(items) ? items : []).map(it => {
    const label = String(it?.label ?? it?.key ?? '').trim();
    if (!label) return '';
    // Always include the current slot's saved value even if another slot has the same
    const isCurrent = canon(value) === canon(label);
    if (!isCurrent && excluded.has(canon(label))) return '';
    const sel = isCurrent ? ' selected' : '';
    return `<option value="${label}"${sel}>${label}</option>`;
  }).filter(Boolean).join('\n');

  const nice = type.charAt(0).toUpperCase() + type.slice(1);

  return `
    <label style="display:flex; flex-direction:column; gap:4px; margin-top:8px; min-width:220px;">
      <span style="font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:var(--cg-text-muted);">${nice}</span>
      <select class="cg-free-qual-select" data-slot="${slot}" data-qtype="${type}">
        <option value="">— Select ${nice} —</option>
        ${opts}
      </select>
    </label>
  `;
}

// Reuse singleton if module is evaluated twice
const Existing = W.CG_FreeChoices;

const FreeChoices = (Existing && Existing.__cg_singleton) ? Existing : {
  __cg_singleton: true,

  _inited: false,
  _bound: false,

  _allGifts: [],
  _byId: null,
  _loading: false,


    _renderQueued: false,
    _renderRunning: false,
    _renderPending: false,

    _scheduleRender(reason = 'auto') {
      // Coalesce bursts: at most one render per tick, plus one pending pass.
      if (this._renderRunning) { this._renderPending = true; return; }
      if (this._renderQueued) return;
      this._renderQueued = true;

      const run = () => {
        this._renderQueued = false;
        this._renderRunning = true;

        try {
          this.render();
        } catch (e) {
          try { console.error('[FreeChoices] render failed', e); } catch (_) {}
        } finally {
          this._renderRunning = false;

          if (this._renderPending) {
            this._renderPending = false;
            this._scheduleRender('pending');
          }
        }
      };

      if (typeof requestAnimationFrame === 'function') requestAnimationFrame(run);
      else setTimeout(run, 0);
    },
  init() {
    if (this.__inited) return;
    this.__inited = true;
    if (this._inited) return;
    this._inited = true;

    try { State?.init?.(); } catch (_) {}
    try { QualState?.init?.(); } catch (_) {}
    try { Quals?.init?.(); } catch (_) {} // best-effort if Quals has an init

    this._bindGlobalEventsOnce();
    this.refresh({ reason: 'init', fetch: true });
  },

  _bindGlobalEventsOnce() {
    if (this._bound) return;
    this._bound = true;

    const rerenderSoon = this._rerenderSoon || ((e) => { this._scheduleRender((e && e.type) ? e.type : 'event'); });
    this._rerenderSoon = rerenderSoon;


    // Prefer jQuery binding when available to avoid double-firing (jQuery also sees CustomEvent)
    if ($ && $.fn) {
      $(document)
        .off('cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices')
        .on('cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices', rerenderSoon);
      return;
    }

    document.removeEventListener('cg:builder:opened', rerenderSoon);
    document.addEventListener('cg:builder:opened', rerenderSoon);

    try {
      const W = window;
      W.__CG_EVT__ = W.__CG_EVT__ || {};
      const EVT = W.__CG_EVT__;
      if (EVT.freeChoicesRerenderSoon) {
        try { document.removeEventListener('cg:tab:changed', EVT.freeChoicesRerenderSoon); } catch (_) {}
      }
      EVT.freeChoicesRerenderSoon = rerenderSoon;
      document.addEventListener('cg:tab:changed', EVT.freeChoicesRerenderSoon);
    } catch (_) {
      try { document.addEventListener('cg:tab:changed', rerenderSoon); } catch (_) {}
    }
    document.removeEventListener('cg:character:loaded', rerenderSoon);
    document.addEventListener('cg:character:loaded', rerenderSoon);

    document.removeEventListener('cg:quals:catalog-updated', rerenderSoon);
    document.addEventListener('cg:quals:catalog-updated', rerenderSoon);

    if ($ && $.fn) {
      $(document)
        .off('cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices')
        .on('cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices', rerenderSoon);
    }
      // Removed legacy tab-click sniffing: cg:tab:changed is the canonical trigger.
  },

  async refresh({ fetch = false } = {}) {
    if (fetch) await this._fetchAllGifts();
      this._scheduleRender('refresh');
},

  async _fetchAllGifts() {
    if (this._loading) return;
    this._loading = true;

    try {
      const res = await ajaxPost({ action: 'cg_get_free_gifts' });

      let list = null;
      if (Array.isArray(res)) list = res;
      else if (res && Array.isArray(res.data)) list = res.data;
      else if (res && res.data && Array.isArray(res.data.gifts)) list = res.data.gifts;
      else if (res && Array.isArray(res.gifts)) list = res.gifts;

      if (!Array.isArray(list)) {
        warn('cg_get_free_gifts returned unexpected payload', res);
        list = [];
      }

      this._allGifts = list.slice();
      this._byId = new Map();
      this._allGifts.forEach(g => {
        const id = giftId(g);
        if (id) this._byId.set(id, g);
      });

      // Hydrate shared GiftsState so TraitsService/Quals can see gift rows
      try { State.setList(this._allGifts); } catch (_) {}

      log('Fetched gifts', { count: this._allGifts.length });
    } catch (err) {
      warn('Failed to fetch gifts', err);
      this._allGifts = [];
      this._byId = new Map();
    } finally {
      this._loading = false;
    }
  },

  _getGift(id) {
    const k = String(id || '');
    if (!k) return null;
    if (this._byId && this._byId.get(k)) return this._byId.get(k);
    const list = Array.isArray(this._allGifts) ? this._allGifts : [];
    return list.find(g => giftId(g) === k) || null;
  },

  render() {
    const modal = modalRoot();
    if (!modal) return;

    const host = modal.querySelector('#cg-free-choices') || document.querySelector('#cg-free-choices');
    if (!host) return;

    const section = ensureSectionInHost(host);
    if (!section) return;

    this._bindSectionDelegates(section);

    const slots = getFreeGiftSlotsFromData();
    const row = section.querySelector('.cg-free-row');
    if (!row) return;

    const owned = computeOwnedGiftIdSet(slots.filter(Boolean));

    const allGifts = Array.isArray(this._allGifts) ? this._allGifts : [];

    const htmlSlots = [0, 1, 2].map(i => {
      const selectedId = String(slots[i] || '').trim();
      const others = new Set(slots.map((v, idx) => (idx === i ? '' : String(v || '').trim())).filter(Boolean));

      const curGift = selectedId ? this._getGift(selectedId) : null;

      const seen = new Set();
      const optionItems = [];

      if (curGift) {
        const forcedId = giftId(curGift);
        seen.add(forcedId);
        optionItems.push({ id: forcedId, name: giftName(curGift), _forced: true, gift: curGift });
      }

      allGifts.forEach(g => {
        if (isNaturalGift(g)) return;
        const id = giftId(g);
        const name = giftName(g);
        if (!id || !name) return;
        if (seen.has(id)) return;
        seen.add(id);

        const reason = giftIneligibleReason(g, owned, others);
        if (reason) return;
        const isGmOnly = gmApprovalRequired(g);
        const unknownNotes = !isGmOnly ? getUnknownPrereqNotes(g) : [];
        optionItems.push({ id, name, gift: g, isGmOnly, unknownNotes });
      });

      const options = optionItems.map(o => {
        const sel = (String(selectedId) === String(o.id)) ? ' selected' : '';
        let label = String(o.name);

        if (o._forced) {
          label += ' (saved)';
        } else if (o.isGmOnly) {
          const gmReason = getGmApprovalReason(o.gift);
          label += ` — [GM approval: ${gmReason}]`;
        } else if (o.unknownNotes && o.unknownNotes.length) {
          label += ` — [${o.unknownNotes[0]}]`;
        }

        const safeLabel = String(label).replace(/"/g, '&quot;');
        return `<option value="${String(o.id)}"${sel}>${safeLabel}</option>`;
      }).join('\n');

      const selectedDesc = curGift ? giftEffectDescription(curGift) : '';
      const descHtml = selectedDesc
        ? `<div class="cg-gift-effect-desc" style="margin-top:4px; font-size:0.82rem; color:var(--cg-text-muted); font-style:italic; padding-left:2px;">${String(selectedDesc).replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>`
        : '';

      const extraCareerHint = selectedId === '184'
        ? `<span class="cg-gift-extra-career-hint">
             Extra Career unlocked — go to the <strong>Traits</strong> tab and look under
             <em>Species &amp; Career</em> to choose which career to add.
           </span>`
        : '';

      return `
        <div class="cg-free-slot" data-slot="${i}">
          <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-weight:600; white-space:nowrap; font-size:0.88rem; color:var(--cg-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Gift</span>
            <select id="cg-free-choice-${i}" class="cg-free-gift-select" data-slot="${i}">
              <option value="">— Select a gift —</option>
              ${options}
            </select>
          </div>
          ${descHtml}
          ${extraCareerHint}
          <div class="cg-free-slot-quals" data-slot="${i}"></div>
        </div>
      `;
    });

    row.innerHTML = htmlSlots.join('\n');
    this._renderSlotQuals(slots);

    // Sync gift_skill_marks from slot qual map after rendering
    try {
      const map = getSlotQualMap();
      recomputeGiftSkillMarks(map);
    } catch (_) {}

    // Let ExtraCareers re-inject per-slot UI (e.g. "Applies to:" for gift 223)
    // after this render has settled, since our innerHTML wipes any injected elements.
    try {
      document.dispatchEvent(new CustomEvent('cg:free-choices:rendered'));
    } catch (_) {}
  },

  _bindSectionDelegates(section) {
    if (section.__cgBound) return;
    section.__cgBound = true;

    section.addEventListener('change', (e) => {
      const t = e.target;
      if (!t || !t.classList) return;

      if (t.classList.contains('cg-free-gift-select')) {
        const slot = Number(t.getAttribute('data-slot') || '0');
        const nextId = String(t.value || '').trim();

        const prevSlots = getFreeGiftSlotsFromData();
        const nextSlots = prevSlots.slice();
        nextSlots[slot] = nextId;

        this._cleanupSlotQualsOnGiftChange({ slot, prevGiftId: prevSlots[slot], nextGiftId: nextId });

        setFreeGiftSlotsToData(nextSlots, 'free-gift-select');
        this._scheduleRender('free-gift-select');
        return;
      }

      if (t.classList.contains('cg-free-qual-select')) {
        const slot = String(t.getAttribute('data-slot') || '0');
        const type = String(t.getAttribute('data-qtype') || '').toLowerCase();
        const val = String(t.value || '').trim();

        const map = getSlotQualMap();
        if (!map[slot] || typeof map[slot] !== 'object') map[slot] = {};

        const prev = String(map[slot][type] || '').trim();

        if (val) map[slot][type] = val;
        else delete map[slot][type];

        if (Object.keys(map[slot] || {}).length === 0) delete map[slot];

        setSlotQualMap(map);

        if (prev && canon(prev) !== canon(val)) qualStateRemoveIfSafe(type, prev, map);
        if (val) qualStateAdd(type, val);

        this._scheduleRender('free-qual-select');
        return;
      }

      if (t.classList.contains('cg-knack-skill-select')) {
        const slot = String(t.getAttribute('data-slot') || '0');
        const skillId = String(t.value || '').trim();

        const map = getSlotQualMap();
        if (!map[slot] || typeof map[slot] !== 'object') map[slot] = {};

        if (skillId) {
          map[slot].knack_skill = skillId;
        } else {
          delete map[slot].knack_skill;
        }

        if (Object.keys(map[slot] || {}).length === 0) delete map[slot];

        setSlotQualMap(map);
        recomputeGiftSkillMarks(map);

        try {
          const BuilderUI = W.CG_BuilderUI;
          if (BuilderUI && typeof BuilderUI.markDirty === 'function') BuilderUI.markDirty();
        } catch (_) {}

        this._scheduleRender('knack-skill-select');
        return;
      }
    });
  },

  _cleanupSlotQualsOnGiftChange({ slot, prevGiftId, nextGiftId }) {
    const prevG = prevGiftId ? this._getGift(prevGiftId) : null;
    const nextG = nextGiftId ? this._getGift(nextGiftId) : null;

    const prevNeeds = prevG ? detectQualTypesNeeded(prevG) : [];
    const nextNeeds = nextG ? detectQualTypesNeeded(nextG) : [];

    const map = getSlotQualMap();
    const sKey = String(slot);

    const slotObj = (map[sKey] && typeof map[sKey] === 'object') ? map[sKey] : null;

    // Clean up knack_skill if the previous gift was Knack For and the next is not
    const prevWasKnack = prevGiftId === KNACK_FOR_GIFT_ID;
    const nextIsKnack  = nextGiftId === KNACK_FOR_GIFT_ID;
    if (prevWasKnack && !nextIsKnack && slotObj) {
      delete slotObj.knack_skill;
    }

    if (slotObj) {
      prevNeeds.forEach(type => {
        if (nextNeeds.includes(type)) return;

        const prevVal = String(slotObj[type] || '').trim();
        if (!prevVal) return;

        delete slotObj[type];
        qualStateRemoveIfSafe(type, prevVal, map);
      });
    }

    if (slotObj && Object.keys(slotObj).length === 0) delete map[sKey];
    setSlotQualMap(map);
    recomputeGiftSkillMarks(map);
  },

  _renderSlotQuals(slots) {
    const section = document.getElementById('cg-free-gifts');
    if (!section) return;

    const map = getSlotQualMap();

    [0, 1, 2].forEach(i => {
      const slotId = String(i);
      const giftIdSel = String(slots[i] || '').trim();
      const wrap = section.querySelector(`.cg-free-slot-quals[data-slot="${i}"]`);
      if (!wrap) return;

      if (!giftIdSel) {
        wrap.innerHTML = '';
        return;
      }

      // Special handling for Knack For gift
      if (giftIdSel === KNACK_FOR_GIFT_ID) {
        if (!map[slotId] || typeof map[slotId] !== 'object') map[slotId] = {};
        const curSkillId = String(map[slotId].knack_skill || '').trim();
        wrap.innerHTML = this._renderKnackSkillSelectHtml(i, curSkillId, map);
        setSlotQualMap(map);
        return;
      }

      const g = this._getGift(giftIdSel);
      if (!g) {
        wrap.innerHTML = '';
        return;
      }

      const needs = detectQualTypesNeeded(g);
      if (!needs.length) {
        wrap.innerHTML = '';
        return;
      }

      if (!map[slotId] || typeof map[slotId] !== 'object') map[slotId] = {};

      const html = needs.map(type => {
        const cur = String(map[slotId][type] || '').trim();
        // Collect values chosen in OTHER free-gift slots for this type
        const excludeValues = [0, 1, 2]
          .filter(j => j !== i)
          .map(j => String((map[String(j)] || {})[type] || '').trim())
          .filter(Boolean);
        // Also exclude the base-language selection (QualState index 0) for language type
        if (type === 'language') {
          try {
            const baseLang = (QualState.get('language') || [])[0] || '';
            if (baseLang) excludeValues.push(baseLang);
          } catch (_) {}
        }
        return renderQualSelectHtml({ slot: i, type, value: cur, allGifts: this._allGifts, excludeValues });
      }).join('\n');

      wrap.innerHTML = html;
      setSlotQualMap(map);
    });
  },

  _renderKnackSkillSelectHtml(slot, curSkillId, slotQualMap) {
    // Build list of skills from CG_SKILLS_LIST or builder data
    let skills = [];
    try {
      const d = getBuilderData();
      if (Array.isArray(d.skillsList)) {
        skills = d.skillsList;
      } else if (Array.isArray(W.CG_SKILLS_LIST)) {
        skills = W.CG_SKILLS_LIST;
      }
    } catch (_) {}

    // Find skills already chosen by OTHER Knack For slots
    const usedSkillIds = new Set();
    const currentSlots = getFreeGiftSlotsFromData();
    [0, 1, 2].forEach(j => {
      if (j === slot) return;
      const sKey = String(j);
      if (String(currentSlots[j] || '').trim() !== KNACK_FOR_GIFT_ID) return;
      const sk = String((slotQualMap[sKey] || {}).knack_skill || '').trim();
      if (sk) usedSkillIds.add(sk);
    });

    const opts = skills
      .map(s => {
        const sid = String(s.id || '').trim();
        const sname = String(s.name || '').trim();
        if (!sid || !sname) return '';
        // Allow current slot's skill even if also used in another slot (edge case: allow duplicate for display)
        const isCurrent = sid === curSkillId;
        if (!isCurrent && usedSkillIds.has(sid)) return '';
        const sel = isCurrent ? ' selected' : '';
        return `<option value="${sid}"${sel}>${sname}</option>`;
      })
      .filter(Boolean)
      .join('\n');

    return `
      <label style="display:flex; flex-direction:column; gap:4px; margin-top:8px; min-width:220px;">
        <span style="font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:var(--cg-text-muted);">Skill for Knack</span>
        <select class="cg-knack-skill-select" data-slot="${slot}">
          <option value="">— Choose a skill —</option>
          ${opts}
        </select>
        <span style="font-size:0.75rem; color:var(--cg-text-muted);">This gift grants +1 mark in the chosen skill.</span>
      </label>
    `;
  },

  getEligibleGiftsForSlot(allSelectedIds = [], slotIndex = 0) {
    if (!Array.isArray(this._allGifts) || !this._allGifts.length) return [];
    const owned  = computeOwnedGiftIdSet(allSelectedIds.filter(Boolean));
    const others = new Set(
      allSelectedIds
        .map((v, idx) => (idx === slotIndex ? '' : String(v || '').trim()))
        .filter(Boolean)
    );
    return this._allGifts.filter(g => !isNaturalGift(g) && giftEligible(g, owned, others));
  },

  debug() {
    const sizes = {};
    ['language','literacy','insider','mystic','piety'].forEach(t => {
      try { sizes[t] = getQualItemsForType(t, this._allGifts).length; } catch (_) { sizes[t] = null; }
    });

    return {
      giftsCount: Array.isArray(this._allGifts) ? this._allGifts.length : 0,
      slots: getFreeGiftSlotsFromData(),
      slotQualMap: getSlotQualMap(),
      qualCatalogSizes: sizes,
      qualState: QualState?.getAll?.() || null
    };
  }
};

W.CG_FreeChoices = FreeChoices;
export default FreeChoices;
