/**
 * gift-filter.js
 * ==============
 * Pure gift-eligibility logic shared between the main gifts tab (free-choices.js)
 * and the ally improved-gift panel (ally/index.js).
 *
 * HOW THE PIPELINE WORKS
 * ----------------------
 * All context-sensitive checks (species requirements, trait minimums) accept an
 * explicit `ctx` object so neither caller hard-codes where the data comes from:
 *
 *   ctx = {
 *     speciesName : string   — lowercase display name, e.g. 'badger'
 *     getTraitDie : (key) => number|null
 *                             — returns the numeric face of the die (6/8/10/12)
 *                               for keys like 'body', 'speed', 'mind', 'will', 'species'
 *   }
 *
 * ADDING A NEW RULE
 * -----------------
 * 1. Add the check inside `giftIneligibleReason` (or a helper it calls).
 * 2. If the check is context-sensitive, read from `ctx.speciesName` /
 *    `ctx.getTraitDie(key)` — never from a global directly.
 * 3. Done. Both the main gifts tab and the ally panel inherit the rule with no
 *    further changes needed, because both call `giftIneligibleReason` with their
 *    own ctx object.
 */

// ── Dice utilities ────────────────────────────────────────────────────────────

export function diceToNum(s) {
  const t = String(s || '').trim().toLowerCase();
  const m = t.match(/d\s*(4|6|8|10|12)/);
  return m ? Number(m[1]) : null;
}

// ── Gift data accessors (pure — read only from the gift object) ──────────────

export function giftId(g) {
  return String(g?.id ?? g?.ct_id ?? g?.gift_id ?? '');
}

export function giftName(g) {
  return String(g?.name ?? g?.title ?? g?.gift_name ?? g?.ct_gift_name ?? '');
}

export function requiresSpecialText(g) {
  return String(g?.requires_special ?? g?.ct_gifts_requires_special ?? g?.requiresSpecial ?? '').trim();
}

export function giftEffectDescription(g) {
  if (!g) return '';
  const short = String(g.effect ?? '').trim();
  if (short) return short;
  return String(g.effect_description ?? g.ct_gifts_effect_description ?? '').trim();
}

export function isNaturalGift(g) {
  const cls = String(g?.giftclass ?? g?.ct_gifts_class ?? g?.gift_class ?? '').trim().toLowerCase();
  return cls === 'natural';
}

export function isMajorGift(g) {
  const cls = String(g?.giftclass ?? g?.ct_gifts_class ?? g?.gift_class ?? '').trim().toLowerCase();
  return cls === 'major';
}

/** Returns true if this gift can be taken more than once. */
export function allowsMultiple(g) {
  // Gift 223 (Increased Trait: Career) always allows multiple — each copy targets a different extra career
  if (g && (String(g.id || '') === '223' || String(g.ct_id || '') === '223')) return true;
  const v = g?.allows_multiple ?? g?.ct_gifts_manifold ?? g?.manifold ?? null;
  const n = Number(v);
  return Number.isFinite(n) && n > 0;
}

/**
 * Returns all gift IDs that must be owned before this gift can be taken.
 * Reads both the flat ct_gifts_requires* columns and the structured requirements array.
 */
export function extractRequiredGiftIds(g) {
  if (!g || typeof g !== 'object') return [];
  const out = [];
  Object.keys(g).forEach(k => {
    if (!/^ct_gifts_requires(_[a-z]+)?$/i.test(k) && !/^ct_gifts_requires_/i.test(k)) return;
    if (k.toLowerCase() === 'ct_gifts_requires_special') return;
    const v = g[k];
    if (v == null) return;
    String(v).trim().split(',').map(x => x.trim()).filter(Boolean).forEach(x => out.push(x));
  });
  (Array.isArray(g.requirements) ? g.requirements : []).forEach(req => {
    if (String(req.kind || '') === 'gift_ref') {
      const refId = String(req.ref_id || '');
      if (refId) out.push(refId);
    }
  });
  return [...new Set(out)];
}

/** Returns true if this gift requires GM/Game Host approval. */
export function gmApprovalRequired(g) {
  const prereqs = Array.isArray(g?.prereqs) ? g.prereqs : [];
  if (prereqs.some(p => p.kind === 'gm_permission')) return true;
  const rs = requiresSpecialText(g);
  return /permission\s+from\s+the\s+game\s+host/i.test(rs) ||
         /gm\s+approval/i.test(rs) ||
         /game\s+host\s+approval/i.test(rs);
}

export function getGmApprovalReason(g) {
  const prereqs = Array.isArray(g?.prereqs) ? g.prereqs : [];
  const gm = prereqs.find(p => p.kind === 'gm_permission');
  if (gm) return String(gm.raw_text || gm.req_value || 'Permission from the Game Host').trim();
  const rs = requiresSpecialText(g);
  for (const line of String(rs || '').split(/[\r\n]+/).map(s => s.trim()).filter(Boolean)) {
    if (/permission\s+from\s+the\s+game\s+host/i.test(line) ||
        /gm\s+approval/i.test(line)) return line;
  }
  return 'Requires GM/Game Host approval';
}

// ── Trait minimum helpers ────────────────────────────────────────────────────

const TRAIT_NAMES = ['mind', 'body', 'speed', 'will', 'species'];

/**
 * Parses requires_special text for lines like "Mind d8+" and returns an array of
 * { traitKey, need, raw } objects describing each minimum.
 */
export function extractTraitMinimaFromRequiresSpecial(rs) {
  const text = String(rs || '');
  if (!text) return [];

  const traitRes = [
    { key: 'mind',    re: /\bmind\b/i },
    { key: 'body',    re: /\bbody\b/i },
    { key: 'speed',   re: /\bspeed\b/i },
    { key: 'will',    re: /\bwill\b/i },
    { key: 'species', re: /\bspecies\b/i },
  ];

  const mins = [];
  text.split(/[\n\r.;]+/).map(s => s.trim()).filter(Boolean).forEach(p => {
    traitRes.forEach(t => {
      if (!t.re.test(p)) return;
      const m = p.match(/d\s*(4|6|8|10|12)/i);
      if (!m) return;
      const need = Number(m[1]);
      if (Number.isFinite(need)) mins.push({ traitKey: t.key, need, raw: p });
    });
  });

  const best = new Map();
  mins.forEach(x => {
    const cur = best.get(x.traitKey);
    if (!cur || x.need > cur.need) best.set(x.traitKey, x);
  });
  return Array.from(best.values());
}

// ── Context-aware prerequisite checkers ─────────────────────────────────────

/**
 * Returns the numeric die face for a trait key using ctx.getTraitDie.
 * Falls back gracefully to null if ctx is missing.
 */
function traitDie(key, ctx) {
  try { return ctx?.getTraitDie?.(key) ?? null; } catch (_) { return null; }
}

function speciesAnyofMet(prereq, ctx) {
  const species = String(ctx?.speciesName || '').trim().toLowerCase();
  if (!species) return null;
  const allowed = String(prereq.req_value || '').split(/[|,]/)
    .map(s => String(s || '').replace(/\bor\s+/i, '').trim().toLowerCase())
    .filter(Boolean);
  if (!allowed.length) return null;
  return allowed.some(a => species.includes(a) || a.includes(species)) ? true : false;
}

function speciesForbidMet(prereq, ctx) {
  const species = String(ctx?.speciesName || '').trim().toLowerCase();
  if (!species) return null;
  const forbidden = String(prereq.req_value || '').split(/[|,]/)
    .map(s => String(s || '').replace(/\bor\s+/i, '').trim().toLowerCase())
    .filter(Boolean);
  if (!forbidden.length) return null;
  return forbidden.some(f => species.includes(f) || f.includes(species)) ? false : true;
}

function traitMinStructuredMet(prereq, ctx) {
  const key    = String(prereq.trait_key || '').trim().toLowerCase().replace(/_trait$/, '');
  const needed = Number(prereq.die_min);
  if (!key || !Number.isFinite(needed)) return null;
  const have = traitDie(key, ctx);
  if (have == null) return null;
  const cmp = String(prereq.comparator || '>=');
  if (cmp === '>=') return have >= needed;
  if (cmp === '>')  return have >  needed;
  if (cmp === '=')  return have === needed;
  if (cmp === '<')  return have <  needed;
  if (cmp === '<=') return have <= needed;
  return have >= needed;
}

/**
 * Evaluates the structured prereqs array on a gift.
 * Returns null if all pass, or a human-readable failure reason string.
 */
export function evaluateStructuredPrereqs(g, ownedSet, ctx = {}) {
  const prereqs     = Array.isArray(g?.prereqs)      ? g.prereqs      : [];
  const requirements = Array.isArray(g?.requirements) ? g.requirements : [];

  for (const prereq of prereqs) {
    const kind = String(prereq.kind || '');
    if (kind === 'gm_permission') continue;

    if (kind === 'species_anyof') {
      const result = speciesAnyofMet(prereq, ctx);
      if (result === false)
        return String(prereq.raw_text || prereq.req_value || 'Species requirement not met');
      continue;
    }

    if (kind === 'species_forbid') {
      const result = speciesForbidMet(prereq, ctx);
      if (result === false)
        return String(prereq.raw_text || prereq.req_value || 'Species forbidden');
      continue;
    }

    if (kind === 'trait_min') {
      const result = traitMinStructuredMet(prereq, ctx);
      if (result === false) {
        const key    = String(prereq.trait_key || '');
        const needed = Number(prereq.die_min);
        return `Requires: ${key} d${needed}+`;
      }
      continue;
    }

    if (kind === 'trait_compare') {
      if (!comparativeTraitsSatisfied(g, ctx))
        return String(prereq.raw_text || 'Trait comparison requirement not met');
      continue;
    }

    if (kind === 'gift_trait') {
      const reqKey = String(prereq.req_key || prereq.req_value || '').trim();
      if (reqKey && !ownedSet.has(reqKey))
        return `Requires gift trait: ${reqKey}`;
      continue;
    }
  }

  for (const req of requirements) {
    if (String(req.kind || '') === 'gift_ref') {
      const refId = String(req.ref_id || '');
      if (refId && !ownedSet.has(refId))
        return `Requires: ${String(req.text || `gift #${refId}`).trim()}`;
    }
  }

  return null;
}

/** Returns true if comparative trait lines in requires_special are satisfied. */
export function comparativeTraitsSatisfied(g, ctx = {}) {
  const rs = requiresSpecialText(g);
  if (!rs) return true;
  for (const line of rs.split(/[\n\r.;]+/).map(s => s.trim()).filter(Boolean)) {
    const m = line.match(/\b(mind|body|speed|will|species)[^\w]*(trait)?\s+must\s+be\s+(greater|higher)\s+than\s+(your\s+)?(mind|body|speed|will|species)/i);
    if (!m) continue;
    const lv = traitDie(m[1].toLowerCase(), ctx);
    const rv = traitDie(m[5].toLowerCase(), ctx);
    if (lv == null || rv == null) continue;
    if (lv <= rv) return false;
  }
  return true;
}

/** Returns true if all trait minima from requires_special text are satisfied. */
export function traitMinimaSatisfied(g, ctx = {}) {
  const rs   = requiresSpecialText(g);
  const mins = extractTraitMinimaFromRequiresSpecial(rs);
  if (!mins.length) return true;
  for (const m of mins) {
    const have = traitDie(m.traitKey, ctx);
    if (have == null || have < m.need) return false;
  }
  return true;
}

/**
 * If trait minimums are the ONLY thing blocking this gift (all other checks pass),
 * returns a human-readable reason so the UI can show the option as disabled/dimmed
 * rather than hiding it entirely.  Returns null otherwise.
 */
export function traitMinimumBlockOnly(g, ownedSet, otherSelectedIds, ctx = {}) {
  if (!g) return null;
  const id   = giftId(g);
  const name = giftName(g);
  if (!id || !name) return null;

  if (isNaturalGift(g)) return null;
  if (evaluateStructuredPrereqs(g, ownedSet, ctx)) return null;
  for (const rid of extractRequiredGiftIds(g)) {
    if (!ownedSet.has(String(rid))) return null;
  }
  if (!comparativeTraitsSatisfied(g, ctx)) return null;
  if (otherSelectedIds.has(id) && !allowsMultiple(g)) return null;

  if (!traitMinimaSatisfied(g, ctx)) {
    const rs   = requiresSpecialText(g);
    const mins = extractTraitMinimaFromRequiresSpecial(rs);
    const parts = mins.map(m => {
      const have = traitDie(m.traitKey, ctx);
      if (have == null || have < m.need) {
        return `${m.traitKey.charAt(0).toUpperCase() + m.traitKey.slice(1)} d${m.need}+`;
      }
      return null;
    }).filter(Boolean);
    return 'Requires: ' + (parts.join(', ') || 'higher trait');
  }

  return null;
}

/**
 * THE MAIN FILTER FUNCTION
 * ========================
 * Returns null if the gift is eligible, or a human-readable reason string if not.
 *
 * @param {object}  g                — gift object from cg_get_free_gifts
 * @param {Set}     ownedSet         — all gift IDs the character already has
 * @param {Set}     otherSelectedIds — gift IDs selected in other free-choice slots (same round)
 * @param {object}  ctx              — { speciesName, getTraitDie }  (see module header)
 * @param {object}  opts             — extra flags: { skipQualCheck: boolean }
 *
 * TO ADD A NEW RULE: add a check here (or in a helper it calls).
 * Both the main gifts tab and the ally panel will inherit the rule automatically.
 */
export function giftIneligibleReason(g, ownedSet, otherSelectedIds = new Set(), ctx = {}, opts = {}) {
  if (!g) return 'Unknown gift';
  const id   = giftId(g);
  const name = giftName(g);
  if (!id || !name) return 'Invalid gift data';

  if (isNaturalGift(g)) return 'Natural gift (granted by species)';

  const structuredReason = evaluateStructuredPrereqs(g, ownedSet, ctx);
  if (structuredReason) return structuredReason;

  for (const rid of extractRequiredGiftIds(g)) {
    if (!ownedSet.has(String(rid))) return `Requires gift #${rid}`;
  }

  if (!traitMinimaSatisfied(g, ctx)) {
    const rs    = requiresSpecialText(g);
    const mins  = extractTraitMinimaFromRequiresSpecial(rs);
    const parts = mins.map(m => {
      const have = traitDie(m.traitKey, ctx);
      if (have == null || have < m.need) return `${m.traitKey} d${m.need}+`;
      return null;
    }).filter(Boolean);
    return 'Requires: ' + (parts.join(', ') || 'higher trait');
  }

  if (!comparativeTraitsSatisfied(g, ctx)) return 'Trait comparison requirement not met';

  // qualPrereqsSatisfied is main-character-only (Quals module); callers can opt out via opts
  if (!opts.skipQualCheck && typeof opts.qualPrereqsSatisfied === 'function') {
    if (!opts.qualPrereqsSatisfied(g)) {
      const rs   = requiresSpecialText(g);
      const reqs = extractQualReqLines(rs);
      if (reqs.length) return 'Requires: ' + reqs.map(r => `${r.type}: ${r.raw}`).join('; ');
      return 'Qualification prerequisite not met';
    }
  }

  if (otherSelectedIds.has(id) && !allowsMultiple(g)) return 'Already selected in another slot';

  return null;
}

// ── Internal helpers ─────────────────────────────────────────────────────────

function extractQualReqLines(rs) {
  return String(rs || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean).reduce((acc, line) => {
    const m = line.match(/^(language|literacy|insider|mystic|piety|ordainment)\s*:\s*(.+)$/i);
    if (m && m[1] && m[2]) acc.push({ type: m[1].toLowerCase(), raw: m[2].trim() });
    return acc;
  }, []);
}
