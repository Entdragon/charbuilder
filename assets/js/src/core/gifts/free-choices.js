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
import TraitsService from '../traits/service.js';
import SkillsIndex from '../skills/index.js';

function cgWin() {
  if (typeof globalThis !== 'undefined') return globalThis;
  if (typeof window !== 'undefined') return window;
  return {};
}

const W = cgWin();
const $ = (W && W.jQuery) ? W.jQuery : null;

const log = (...a) => { try { console.log('[FreeChoices]', ...a); } catch (_) {} };
const warn = (...a) => { try { console.warn('[FreeChoices]', ...a); } catch (_) {} };

function safeHtml(s) {
  return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

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

  let rawNum = null;
  for (const obj of pools) {
    for (const k of keys) {
      if (!obj || !(k in obj)) continue;
      const n = diceToNum(obj[k]);
      if (n != null) { rawNum = n; break; }
    }
    if (rawNum != null) break;
  }

  // Also check the boosted die from TraitsService (accounts for "Improve Trait" gifts).
  // Use whichever is higher so requirements correctly reflect gift improvements.
  try {
    const boosted = TraitsService.getBoostedDie(traitKey);
    if (boosted) {
      const boostedNum = diceToNum(boosted);
      if (boostedNum != null && (rawNum == null || boostedNum > rawNum)) {
        return boostedNum;
      }
    }
  } catch (_) {}

  return rawNum;
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
 * Returns a human-readable reason string if trait minima from requires_special text are the
 * ONLY reason this gift is ineligible (all other checks pass). Returns null if the gift is
 * eligible, or if some other check also blocks it (in which case it should remain hidden).
 *
 * Used so the UI can show trait-gated gifts as greyed-out (disabled) options rather than
 * hiding them completely, consistent with the task spec: "not hidden, only visually dimmed."
 */
function traitMinimumBlockOnly(g, ownedSet, otherSelectedIds) {
  if (!g) return null;
  const id = giftId(g);
  const name = giftName(g);
  if (!id || !name) return null;

  // These blocks always hide the gift completely — never dim
  if (isNaturalGift(g)) return null;
  if (evaluateStructuredPrereqs(g, ownedSet)) return null;
  const reqIds = extractRequiredGiftIds(g);
  for (const rid of reqIds) {
    if (!ownedSet.has(String(rid))) return null;
  }
  if (!comparativeTraitsSatisfied(g)) return null;
  if (!qualPrereqsSatisfied(g)) return null;
  if (otherSelectedIds.has(id) && !allowsMultiple(g)) return null;

  // At this point the only possible remaining block is requires_special trait minimums
  if (!traitMinimaSatisfied(g)) {
    const rs = requiresSpecialText(g);
    const mins = extractTraitMinimaFromRequiresSpecial(rs);
    const parts = mins.map(m => {
      const have = getTraitDieValue(m.traitKey);
      if (have == null || have < m.need) {
        const label = m.traitKey.charAt(0).toUpperCase() + m.traitKey.slice(1);
        return `${label} d${m.need}+`;
      }
      return null;
    }).filter(Boolean);
    return 'Requires: ' + (parts.join(', ') || 'higher trait');
  }

  return null; // gift is eligible — not applicable
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
    'xpGifts','xp_gifts','xp_gift_ids',
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
  const short = String(g.effect ?? '').trim();
  if (short) return short;
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

  // Detect if the saved value is a custom (not-in-catalog) entry
  const knownCanon = new Set(
    (Array.isArray(items) ? items : [])
      .map(it => canon(String(it?.label ?? it?.key ?? ''))).filter(Boolean)
  );
  const isCustomValue = !!(value && !knownCanon.has(canon(value)));

  const esc = s => String(s || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

  const opts = (Array.isArray(items) ? items : []).map(it => {
    const label = String(it?.label ?? it?.key ?? '').trim();
    if (!label) return '';
    const isCurrent = !isCustomValue && canon(value) === canon(label);
    if (!isCurrent && excluded.has(canon(label))) return '';
    const sel = isCurrent ? ' selected' : '';
    return `<option value="${esc(label)}"${sel}>${esc(label)}</option>`;
  }).filter(Boolean).join('\n');

  const nice = type.charAt(0).toUpperCase() + type.slice(1);
  const otherSel  = isCustomValue ? ' selected' : '';
  const inputDisp = isCustomValue ? '' : 'none';

  return `
    <label style="display:flex; flex-direction:column; gap:4px; margin-top:8px; min-width:220px;">
      <span style="font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:var(--cg-text-muted);">${esc(nice)}</span>
      <select class="cg-free-qual-select" data-slot="${slot}" data-qtype="${type}">
        <option value="">— Select ${esc(nice)} —</option>
        ${opts}
        <option value="__other__"${otherSel}>Other (type below)…</option>
      </select>
      <input type="text"
        class="cg-qual-custom-input cg-free-select"
        data-slot="${slot}"
        data-qtype="${type}"
        value="${esc(isCustomValue ? value : '')}"
        placeholder="Enter custom ${esc(nice.toLowerCase())}…"
        autocomplete="off"
        style="display:${inputDisp}; margin-top:4px;" />
    </label>
  `;
}

// ── [Choice] gift helpers ───────────────────────────────────────────────────

/**
 * Returns true if this gift has "[Choice]" in its name but is NOT already
 * handled by a qual sub-selector (language, literacy, insider, mystic, piety)
 * or the Knack For skill picker.
 */
function isUnhandledChoiceGift(g) {
  if (!g) return false;
  if (!giftName(g).includes('[Choice]')) return false;
  if (giftId(g) === KNACK_FOR_GIFT_ID) return false;
  if (detectQualTypesNeeded(g).length > 0) return false;
  return true;
}

/**
 * Parse suggestion items from a gift's effect description.
 * Looks for lines after a "Suggestions" / "Specialties" / "Examples" header,
 * or standalone bullet-prefixed lines that are short enough to be list items.
 */
function extractGiftSuggestions(g) {
  const parts = [
    String(g.ct_gifts_effect_description ?? ''),
    String(g.ct_gifts_effect ?? ''),
    String(g.effect_description ?? ''),
    String(g.description ?? ''),
  ];
  const fullText = parts.filter(Boolean).join('\n');
  const lines = fullText.split(/\r?\n/);
  const suggestions = [];
  let inSuggestions = false;

  for (const line of lines) {
    const trimmed = line.trim();
    if (!trimmed) { inSuggestions = false; continue; }

    // Handle inline "Suggestions: a, b, c" on same line
    const inlineMatch = trimmed.match(/\b(?:suggestion|specialt|example|choice)[s]?\s*[:]\s*(.+)/i);
    if (inlineMatch) {
      const items = inlineMatch[1].split(/[,;]+/).map(s => s.split('(')[0].trim()).filter(s => s && s.length <= 50);
      items.forEach(s => suggestions.push(s));
      inSuggestions = true;
      continue;
    }

    if (/\b(suggestion|specialt|example|choice)/i.test(trimmed) && trimmed.length < 60) {
      inSuggestions = true;
      continue;
    }

    if (inSuggestions) {
      // Also handle comma-separated continuation lines
      if (!trimmed.match(/^[-•*\u2013\u2014]/) && trimmed.includes(',') && trimmed.length < 80) {
        const items = trimmed.split(/[,;]+/).map(s => s.split('(')[0].trim()).filter(s => s && s.length <= 50 && !/\b(you|when|if|the|this|can|may|will)\b/i.test(s));
        items.forEach(s => suggestions.push(s));
        continue;
      }
      const cleaned = trimmed.replace(/^[-•*\u2013\u2014]+\s*/, '').split('(')[0].trim();
      if (!cleaned || cleaned.length > 50) { inSuggestions = false; continue; }
      if (/\b(you|when|if|the|this|can|may|will|your|and\s+|or\s+|a\s+bonus|roll)\b/i.test(cleaned) && cleaned.split(/\s+/).length > 4) {
        inSuggestions = false; continue;
      }
      suggestions.push(cleaned);
    } else {
      const bulletMatch = trimmed.match(/^[-•*\u2013\u2014]\s+(.+)$/);
      if (bulletMatch) {
        const content = bulletMatch[1].split('(')[0].trim();
        if (content.length <= 50 && !/\b(you|when|if|the|this|can|may|will)\b/i.test(content)) {
          suggestions.push(content);
        }
      }
    }
  }

  return [...new Set(suggestions)].slice(0, 24);
}

/**
 * Render a text input (with optional datalist suggestions) for a [Choice] gift.
 * @param {number} slot - slot index
 * @param {string} currentValue - currently saved text value
 * @param {string[]} suggestions - datalist options
 * @param {string} [giftLabel] - gift name used to derive a specific prompt label
 */
function renderChoiceTextInputHtml(slot, currentValue, suggestions, giftLabel) {
  const listId = `cg-gift-choice-list-${slot}`;
  const esc = s => String(s || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

  // Derive a context-specific label from the gift name, e.g. "Artist: [Choice]" → "Artist"
  const baseName = giftLabel
    ? String(giftLabel).replace(/\s*[:\-–]\s*\[Choice\]/i, '').replace(/\[Choice\]/i, '').trim()
    : '';
  const labelText = baseName ? `Choice: ${baseName}` : 'Your Choice';
  const placeholder = baseName ? `Enter your ${baseName.toLowerCase()}…` : 'Enter your choice…';

  const datalist = suggestions.length
    ? `<datalist id="${listId}">${suggestions.map(s => `<option value="${esc(s)}">`).join('')}</datalist>`
    : '';
  return `
    <label style="display:flex; flex-direction:column; gap:4px; margin-top:8px; min-width:180px;">
      <span style="font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:var(--cg-text-muted);">${esc(labelText)}</span>
      <input type="text"
        class="cg-gift-choice-input cg-free-select"
        data-slot="${slot}"
        value="${esc(currentValue)}"
        placeholder="${esc(placeholder)}"
        autocomplete="off"
        ${suggestions.length ? `list="${listId}"` : ''} />
      ${datalist}
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

    // Kick off skills list fetch early so Knack For dropdown is populated
    // even if the Skills tab has never been visited.
    try { SkillsIndex.init(); } catch (_) {}
  },

  _bindGlobalEventsOnce() {
    if (this._bound) return;
    this._bound = true;

    const rerenderSoon = this._rerenderSoon || ((e) => { this._scheduleRender((e && e.type) ? e.type : 'event'); });
    this._rerenderSoon = rerenderSoon;


    // Re-render when skills list arrives (Knack For dropdown needs it)
    try {
      document.addEventListener('cg:skills-list:loaded', rerenderSoon);
      if ($ && $.fn) $(document).on('cg:skills-list:loaded.cgfreechoices', rerenderSoon);
    } catch (_) {}

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
        if (reason) {
          // Show trait-minimum-gated gifts as dimmed (disabled) options instead of hiding them
          const traitReason = traitMinimumBlockOnly(g, owned, others);
          if (traitReason) {
            optionItems.push({ id, name, gift: g, _traitGated: true, _traitReason: traitReason });
          }
          return;
        }
        const isGmOnly = gmApprovalRequired(g);
        const unknownNotes = !isGmOnly ? getUnknownPrereqNotes(g) : [];
        optionItems.push({ id, name, gift: g, isGmOnly, unknownNotes });
      });

      // ── Build select options ───────────────────────────────────────────────
      const options = optionItems.map(o => {
        const sel = (String(selectedId) === String(o.id)) ? ' selected' : '';
        const dis = o._traitGated ? ' disabled' : '';
        let label = String(o.name);
        if (o._forced)                                label += ' (saved)';
        else if (o._traitGated)                       label += ` — ${o._traitReason}`;
        else if (o.isGmOnly)                          label += ` — [GM: ${getGmApprovalReason(o.gift)}]`;
        else if (o.unknownNotes && o.unknownNotes.length) label += ` — [${o.unknownNotes[0]}]`;
        return `<option value="${String(o.id)}"${sel}${dis}>${String(label).replace(/"/g, '&quot;')}</option>`;
      }).join('\n');

      // ── Effect description & GM badge ─────────────────────────────────────
      const selectedDesc = curGift ? giftEffectDescription(curGift) : '';
      const descHtml = selectedDesc
        ? `<div class="cg-gift-effect-inline">${String(selectedDesc).replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>`
        : '';

      const gmHtml = curGift && gmApprovalRequired(curGift)
        ? `<div class="cg-gift-gm-badge">⚠ GM approval required</div>`
        : '';

      // ── Rule type badge + allows-multiple badge ────────────────────────────
      let badgeRowHtml = '';
      if (curGift) {
        const ruleType = String(curGift.rule_type ?? '').trim();
        const ruleTypeBadgeHtml = ruleType
          ? `<span class="cg-gift-rule-badge cg-gift-rule-badge--${safeHtml(ruleType.toLowerCase().replace(/\s+/g, '-'))}">${safeHtml(ruleType.charAt(0).toUpperCase() + ruleType.slice(1))}</span>`
          : '';

        // ×2 badge: show when ct_gifts_manifold or allows_multiple > 0 per spec.
        // Uses raw DB fields directly, not the broader allowsMultiple() JS helper which also
        // returns true for qual-type gifts (their repeatability is implicit, not manifold-flagged).
        // Note: gift 184 shows a separate "Extra Career unlocked" context hint (below); that
        // is independent of this badge and applies to a different gift (Increased Extra Career).
        const rawManifold = Number(curGift.ct_gifts_manifold ?? curGift.allows_multiple ?? 0);
        const isManifold = Number.isFinite(rawManifold) && rawManifold > 0;
        const manifoldHtml = isManifold
          ? `<span class="cg-gift-manifold-badge" title="This gift can be taken more than once">×2</span>`
          : '';

        if (ruleTypeBadgeHtml || manifoldHtml) {
          badgeRowHtml = `<div class="cg-gift-badge-row">${ruleTypeBadgeHtml}${manifoldHtml}</div>`;
        }
      }

      // ── Trigger line ───────────────────────────────────────────────────────
      let triggerHtml = '';
      if (curGift) {
        const triggerText = String(curGift.trigger ?? curGift.ct_gift_trigger ?? '').trim();
        if (triggerText) {
          triggerHtml = `<div class="cg-gift-trigger-line"><span class="cg-gift-trigger-label">Trigger:</span> ${safeHtml(triggerText)}</div>`;
        }
      }

      // ── Descriptor tag chips ───────────────────────────────────────────────
      let tagsHtml = '';
      if (curGift) {
        const tags = Array.isArray(curGift.tags) ? curGift.tags : [];
        if (tags.length) {
          tagsHtml = `<div class="cg-gift-tag-chips">${tags.map(t => `<span class="cg-gift-tag-chip">${safeHtml(String(t))}</span>`).join('')}</div>`;
        }
      }

      const extraCareerHint = selectedId === '184'
        ? `<span class="cg-gift-extra-career-hint">
             Extra Career unlocked — go to the <strong>Traits</strong> tab and look under
             <em>Species &amp; Career</em> to choose which career to add.
           </span>`
        : '';

      return `
        <div class="cg-free-slot" data-slot="${i}">
          <label class="cg-gift-label" for="cg-free-choice-${i}">Gift ${i + 1}</label>
          <select id="cg-free-choice-${i}" class="cg-free-gift-select" data-slot="${i}">
            <option value="">— Select a gift —</option>
            ${options}
          </select>
          ${badgeRowHtml}
          ${descHtml}
          ${triggerHtml}
          ${tagsHtml}
          ${gmHtml}
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

    // [Choice] text input — persist to free_gift_quals[slot].choice_text
    // Also handles custom qual entry text inputs (.cg-qual-custom-input)
    section.addEventListener('input', e => {
      const choiceInp = e.target.closest('.cg-gift-choice-input');
      if (choiceInp) {
        const slot = String(choiceInp.dataset.slot || '0');
        const val  = String(choiceInp.value || '').trim();
        const map  = getSlotQualMap();
        if (!map[slot] || typeof map[slot] !== 'object') map[slot] = {};
        if (val) map[slot].choice_text = val;
        else delete map[slot].choice_text;
        if (Object.keys(map[slot]).length === 0) delete map[slot];
        setSlotQualMap(map);
        return;
      }

      const customQualInp = e.target.closest('.cg-qual-custom-input');
      if (customQualInp) {
        const slot = String(customQualInp.dataset.slot || '0');
        const type = String(customQualInp.dataset.qtype || '').toLowerCase();
        const val  = String(customQualInp.value || '').trim();
        const map  = getSlotQualMap();
        if (!map[slot] || typeof map[slot] !== 'object') map[slot] = {};
        const prev = String(map[slot][type] || '').trim();
        if (val) map[slot][type] = val;
        else delete map[slot][type];
        if (Object.keys(map[slot]).length === 0) delete map[slot];
        setSlotQualMap(map);
        if (prev && canon(prev) !== canon(val)) qualStateRemoveIfSafe(type, prev, map);
        if (val) qualStateAdd(type, val);
      }
    });

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
        const val  = String(t.value || '').trim();

        // "Other (type below)…" — reveal the custom text input, don't save yet
        if (val === '__other__') {
          const customInp = t.closest('label')?.querySelector('.cg-qual-custom-input');
          if (customInp) {
            customInp.style.display = '';
            customInp.focus();
          }
          return;
        }

        // Switching away from a custom value — hide + clear the text input
        const customInp = t.closest('label')?.querySelector('.cg-qual-custom-input');
        if (customInp) {
          const oldCustomVal = String(customInp.value || '').trim();
          if (oldCustomVal) {
            // Remove the old custom value from QualState
            const map2 = getSlotQualMap();
            qualStateRemoveIfSafe(type, oldCustomVal, map2);
          }
          customInp.value = '';
          customInp.style.display = 'none';
        }

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

    // Clean up choice_text if the previous gift needed a text choice and either:
    //   (a) the next gift does not need a text choice, or
    //   (b) the next gift is a different [Choice] gift (stale text would be meaningless)
    const prevWasChoice = prevG ? isUnhandledChoiceGift(prevG) : false;
    const nextIsChoice  = nextG ? isUnhandledChoiceGift(nextG) : false;
    if (prevWasChoice && (!nextIsChoice || prevGiftId !== nextGiftId) && slotObj) {
      delete slotObj.choice_text;
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

      // [Choice] gift that isn't handled by a qual sub-selector — show text input
      if (isUnhandledChoiceGift(g)) {
        if (!map[slotId] || typeof map[slotId] !== 'object') map[slotId] = {};
        const curChoiceText = String(map[slotId].choice_text || '').trim();
        const suggestions   = extractGiftSuggestions(g);
        wrap.innerHTML      = renderChoiceTextInputHtml(i, curChoiceText, suggestions, giftName(g));
        setSlotQualMap(map);
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
