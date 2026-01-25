// assets/js/src/core/career/events.js
// One-time, namespaced bindings for Career select.
// Fetches + normalizes profile, stores it on CareerAPI.currentProfile,
// renders Profile tab gifts, and announces via cg:career:changed.
//
// Jan 2026: If a non-repeatable Career gift duplicates a Species gift,
// replace that Career gift slot with a dropdown that lets the user pick
// an "Increase Trait" gift instead (e.g., Donkey + Soldier both grant Hiking).
//
// CareerEvents v2026-01-04c:
// - Preserve saved career_gift_replacements during transient empty-career states
// - Listen to BOTH jQuery + native species change events (prevents missed rerender)
// - After render, explicitly set dropdown values (defensive against later resets)

import CareerAPI from './api.js';
import SpeciesAPI from '../species/api.js';

const $ = window.jQuery;
let _bound = false;

let _giftWaitTries = 0;
let _giftWaitTimer = null;

function normalizeCareerProfile(raw = {}) {
  const out = { ...raw };

  out.careerName =
    raw.careerName ||
    raw.career_name ||
    raw.ct_career_name ||
    raw.name ||
    raw.title ||
    '';

  if (raw.skill_one   != null) out.skill_one   = String(raw.skill_one);
  if (raw.skill_two   != null) out.skill_two   = String(raw.skill_two);
  if (raw.skill_three != null) out.skill_three = String(raw.skill_three);

  ['gift_id_1', 'gift_id_2', 'gift_id_3'].forEach(k => {
    if (raw[k] != null) out[k] = String(raw[k]);
  });

  ['manifold_1', 'manifold_2', 'manifold_3'].forEach(k => {
    if (raw[k] != null) out[k] = parseInt(raw[k], 10) || 1;
  });

  return out;
}

function escapeHtml(s) {
  return String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function normalizeReplacementMap(src) {
  if (!src) return {};
  if (typeof src === 'string') {
    try { return normalizeReplacementMap(JSON.parse(src)); }
    catch (_) { return {}; }
  }
  if (Array.isArray(src)) {
    const out = {};
    src.forEach(it => {
      const slot = it?.slot ?? it?.key ?? it?.k;
      const id   = it?.id   ?? it?.value ?? it?.v;
      if (slot != null && id != null && String(id)) out[String(slot)] = String(id);
    });
    return out;
  }
  if (typeof src === 'object') {
    const out = {};
    Object.keys(src).forEach(k => {
      const v = src[k];
      if (v != null && String(v)) out[String(k)] = String(v);
    });
    return out;
  }
  return {};
}

function getAllGiftsList() {
  return (window.CG_FreeChoices && Array.isArray(window.CG_FreeChoices._allGifts))
    ? window.CG_FreeChoices._allGifts
    : [];
}

function byIdMap(all = []) {
  const map = Object.create(null);
  all.forEach(g => {
    if (!g || g.id == null) return;
    map[String(g.id)] = g;
  });
  return map;
}

function isRepeatableGift(g) {
  if (!g) return false;
  if (g.allows_multiple) return true;

  const m = parseInt(g.ct_gifts_manifold, 10);
  if (!Number.isNaN(m) && m > 1) return true;

  const m2 = parseInt(g.manifold, 10);
  if (!Number.isNaN(m2) && m2 > 1) return true;

  return false;
}

function increaseTraitGifts(all = []) {
  const inc = (all || [])
    .filter(g => g && g.id != null)
    .filter(g => {
      const nm = String(g.name || g.title || g.ct_gift_name || '');
      return /^Increase\s+/i.test(nm) || /Increased Trait/i.test(nm);
    })
    .map(g => ({ id: String(g.id), name: String(g.name || g.title || g.ct_gift_name || `Gift #${g.id}`) }));

  inc.sort((a, b) => a.name.localeCompare(b.name));
  return inc;
}

function getFormBuilderData() {
  const fb = window.CG_FormBuilderAPI || window.FormBuilderAPI || null;
  return fb && fb._data ? fb._data : null;
}

function setFormBuilderCareerReplacements(map) {
  const d = getFormBuilderData();
  if (!d) return;
  d.career_gift_replacements = map;
}

function _hasKeys(obj) {
  try { return !!obj && typeof obj === 'object' && Object.keys(obj).length > 0; }
  catch (_) { return false; }
}

function _shouldPreserveOnEmptyCareer(e) {
  const isUser = !!(e && e.originalEvent);
  if (isUser) return false;

  const d = getFormBuilderData();
  if (!d) return false;

  const fbCareer = String(d.career_id ?? d.career ?? '').trim();
  if (!fbCareer) return false;

  const repl = normalizeReplacementMap(d.career_gift_replacements);
  return _hasKeys(repl);
}

function renderCareerGiftsWithReplacements(profile) {
  const $ul = $('#career-gifts');
  if (!$ul || !$ul.length) return;

  const sp = SpeciesAPI?.currentProfile || null;
  const spIds = new Set(
    (sp ? [sp.gift_id_1, sp.gift_id_2, sp.gift_id_3] : [])
      .filter(v => v != null && String(v))
      .map(v => String(v))
  );

  const all = getAllGiftsList();
  const idMap = byIdMap(all);
  const inc = increaseTraitGifts(all);

  if (!all.length) {
    const labels = ['Career Gift One', 'Career Gift Two', 'Career Gift Three'];
    const li = [];
    for (let i = 1; i <= 3; i++) {
      const name = profile[`gift_${i}`] || '';
      const gid  = profile[`gift_id_${i}`] || '';
      const mult = parseInt(profile[`manifold_${i}`], 10) || 1;

      const display = name ? String(name) : (gid ? `Gift #${gid}` : '');
      if (!display) continue;

      li.push(`<li><strong>${labels[i-1]}:</strong> ${escapeHtml(mult > 1 ? `${display} × ${mult}` : display)}</li>`);
    }
    $ul.html(li.join(''));

    if (_giftWaitTries < 10) {
      _giftWaitTries++;
      clearTimeout(_giftWaitTimer);
      _giftWaitTimer = setTimeout(() => renderCareerGiftsWithReplacements(profile), 350);
    }
    return;
  }

  _giftWaitTries = 0;
  clearTimeout(_giftWaitTimer);
  _giftWaitTimer = null;

  const labels = ['Career Gift One', 'Career Gift Two', 'Career Gift Three'];

  const savedFromFB = normalizeReplacementMap(getFormBuilderData()?.career_gift_replacements);
  const savedFromProfile = normalizeReplacementMap(profile.career_gift_replacements);
  const saved = { ...savedFromFB, ...savedFromProfile };

  const neededSlots = new Set();
  const li = [];

  for (let i = 1; i <= 3; i++) {
    const gid  = profile[`gift_id_${i}`] || '';
    const mult = parseInt(profile[`manifold_${i}`], 10) || 1;

    const fromProfileName = profile[`gift_${i}`] || '';
    const fromAllName = gid && idMap[String(gid)]
      ? (idMap[String(gid)].name || idMap[String(gid)].title || idMap[String(gid)].ct_gift_name || '')
      : '';
    const baseName = String(fromProfileName || fromAllName || (gid ? `Gift #${gid}` : ''));

    if (!gid && !baseName) continue;

    const dupeWithSpecies = gid && spIds.has(String(gid));
    const repeatable = dupeWithSpecies ? isRepeatableGift(idMap[String(gid)]) : false;
    const needsReplace = !!(dupeWithSpecies && !repeatable);

    if (!needsReplace) {
      const txt = mult > 1 ? `${baseName} × ${mult}` : baseName;
      li.push(`<li><strong>${labels[i-1]}:</strong> ${escapeHtml(txt)}</li>`);
      continue;
    }

    neededSlots.add(String(i));

    const current = saved[String(i)] || '';
    const selectId = `cg-career-gift-replace-${i}`;

    let selectHtml = '';
    if (!inc.length) {
      selectHtml = `<select id="${escapeHtml(selectId)}" class="cg-profile-select cg-career-gift-replace" data-slot="${escapeHtml(i)}" disabled>
        <option value="">(No “Increase Trait” gifts found)</option>
      </select>`;
    } else {
      const opts = inc
        .map(g => `<option value="${escapeHtml(g.id)}"${String(g.id) === String(current) ? ' selected' : ''}>${escapeHtml(g.name)}</option>`)
        .join('');
      selectHtml = `<select id="${escapeHtml(selectId)}" class="cg-profile-select cg-career-gift-replace" data-slot="${escapeHtml(i)}">
        <option value="">— Choose an Increase Trait gift —</option>
        ${opts}
      </select>`;
    }

    li.push(
      `<li class="cg-career-gift-line cg-career-gift-line--replace">
        <strong>${labels[i-1]}:</strong>
        <span class="cg-career-gift-dup-note">Duplicate: ${escapeHtml(baseName)} →</span>
        ${selectHtml}
      </li>`
    );
  }

  const cleaned = {};
  Object.keys(saved).forEach(k => {
    if (neededSlots.has(String(k)) && saved[k]) cleaned[String(k)] = String(saved[k]);
  });

  profile.career_gift_replacements = cleaned;
  CareerAPI.currentProfile = profile;
  setFormBuilderCareerReplacements(cleaned);

  $ul.html(li.join(''));

  // Defensive: explicitly set values after DOM insert (in case another renderer resets selects)
  setTimeout(() => {
    try {
      neededSlots.forEach(slot => {
        const v = cleaned[String(slot)] || '';
        const el = document.getElementById(`cg-career-gift-replace-${slot}`);
        if (el && v) el.value = String(v);
      });
    } catch (_) {}
  }, 0);
}

function _rerenderFromSpeciesChange() {
  if (!CareerAPI.currentProfile) return;
  renderCareerGiftsWithReplacements(CareerAPI.currentProfile);
}

export default function bindCareerEvents() {
  if (_bound) return;
  _bound = true;

  try {
    window.__CG_BUILD_MARKERS__ = window.__CG_BUILD_MARKERS__ || {};
    window.__CG_BUILD_MARKERS__.CareerEvents = 'v2026-01-04c';
  } catch (_) {}

  // Replacement dropdown changes (delegated)
  $(document)
    .off('change.cg', '#career-gifts .cg-career-gift-replace')
    .on('change.cg', '#career-gifts .cg-career-gift-replace', e => {
      const sel = e.currentTarget;
      const slot = (sel && sel.dataset && sel.dataset.slot) ? String(sel.dataset.slot) : '';
      const val  = (sel && sel.value) ? String(sel.value) : '';

      const profile = CareerAPI.currentProfile || null;
      if (!profile || !slot) return;

      const map = normalizeReplacementMap(profile.career_gift_replacements);
      if (!val) delete map[slot];
      else map[slot] = val;

      profile.career_gift_replacements = map;
      CareerAPI.currentProfile = profile;
      setFormBuilderCareerReplacements(map);

      const careerId = String($('#cg-career').val() || '');
      $(document).trigger('cg:career:changed', [{ id: careerId, profile }]);
      $(document).trigger('cg:free-gift:changed', [{ source: 'career-replacement' }]);
    });

  // Career select change
  $(document)
    .off('change.cg', '#cg-career')
    .on('change.cg', '#cg-career', e => {
      const val = (e.currentTarget && e.currentTarget.value) || '';
      console.log('[CareerEvents] selected career →', val);

      if (!val) {
        const preserve = _shouldPreserveOnEmptyCareer(e);

        CareerAPI.currentProfile = null;
        $('#career-gifts').empty();

        if (!preserve) {
          setFormBuilderCareerReplacements({});
        } else {
          try {
            const d = getFormBuilderData();
            console.log('[CareerEvents] empty career during load; preserving career_gift_replacements', {
              career: String(d?.career_id ?? d?.career ?? ''),
              repl: normalizeReplacementMap(d?.career_gift_replacements)
            });
          } catch (_) {}
        }

        $(document).trigger('cg:career:changed', [{ id: '', profile: null }]);
        return;
      }

      CareerAPI.fetchProfile(val).done(profileRaw => {
        const profile = normalizeCareerProfile(profileRaw || {});

        const saved = normalizeReplacementMap(getFormBuilderData()?.career_gift_replacements);
        if (Object.keys(saved).length) profile.career_gift_replacements = saved;

        CareerAPI.currentProfile = profile;

        renderCareerGiftsWithReplacements(profile);

        $(document).trigger('cg:career:changed', [{ id: String(val), profile }]);
      });
    });

  // --- Species change: listen via BOTH jQuery + native CustomEvent ---
  // jQuery path (if Species module uses $(document).trigger(...))
  $(document)
    .off('cg:species:changed.careergifts.cg')
    .on('cg:species:changed.careergifts.cg', _rerenderFromSpeciesChange);

  // Native path (if Species module uses document.dispatchEvent(new CustomEvent(...)))
  try {
    window.__CG_EVT__ = window.__CG_EVT__ || {};
    if (window.__CG_EVT__.speciesChangedCareerGiftsNative) {
      document.removeEventListener('cg:species:changed', window.__CG_EVT__.speciesChangedCareerGiftsNative);
    }
    window.__CG_EVT__.speciesChangedCareerGiftsNative = _rerenderFromSpeciesChange;
    document.addEventListener('cg:species:changed', window.__CG_EVT__.speciesChangedCareerGiftsNative);
  } catch (_) {}
}
