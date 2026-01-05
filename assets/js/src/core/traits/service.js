// assets/js/src/core/traits/service.js

import GiftsState     from '../gifts/state.js';
import SpeciesService from '../species/api.js';
import CareerService  from '../career/api.js';

const DIE_ORDER = ['d4','d6','d8','d10','d12'];

/**
 * Map of gift IDs → traitKey
 * (these IDs correspond to “increased trait” gifts)
 */
const BOOSTS = {
  78:  'will',
  89:  'speed',
  85:  'body',
  100: 'mind',
  224: 'trait_species',
  223: 'trait_career'
};

const TRAITS     = ['will','speed','body','mind','trait_species','trait_career'];
const DICE_TYPES = ['d8','d6','d4'];
const MAX_COUNT  = { d8:2, d6:3, d4:1 };

function boostedDie(baseDie, steps) {
  const base = String(baseDie || '').toLowerCase().trim();
  const idx  = DIE_ORDER.indexOf(base);
  if (idx === -1) return baseDie || '';
  const s = Math.max(0, parseInt(steps, 10) || 0);
  return DIE_ORDER[Math.min(idx + s, DIE_ORDER.length - 1)];
}

function readFormBuilderData() {
  const fb = window.CG_FormBuilderAPI || window.FormBuilderAPI || null;
  return (fb && fb._data && typeof fb._data === 'object') ? fb._data : {};
}

function readSelectedExtraCareerIds() {
  const d = readFormBuilderData();
  const ids = new Set();
  const arr = Array.isArray(d.extraCareers) ? d.extraCareers : [];
  arr.forEach(x => {
    const id = x && x.id != null ? String(x.id) : '';
    if (id) ids.add(id);
  });
  return ids;
}

function computeCareerBoostCounts(totalBoosts) {
  const total = Math.max(0, parseInt(totalBoosts || 0, 10) || 0);
  const counts = Object.create(null);
  counts.main = 0;

  if (total <= 0) return counts;

  const d = readFormBuilderData();
  const extraIds = readSelectedExtraCareerIds();

  // Per-copy assignment only for free-choice slots that are set to 223
  let assigned = 0;

  for (let slot = 0; slot <= 2; slot++) {
    const sel = document.getElementById(`cg-free-choice-${slot}`);
    if (!sel) continue;
    if (String(sel.value || '') !== '223') continue;

    if (assigned >= total) break;

    const key = `increased_trait_career_target_${slot}`;
    let v = (d[key] != null) ? String(d[key]).trim() : '';

    // legacy fallback if present
    if (!v && d.increased_trait_career_target != null) v = String(d.increased_trait_career_target).trim();

    if (!v) v = 'main';
    if (v !== 'main' && !extraIds.has(String(v))) v = 'main';

    counts[v] = (counts[v] || 0) + 1;
    assigned++;
  }

  // Remaining copies default to main
  const remaining = Math.max(0, total - assigned);
  if (remaining) counts.main = (counts.main || 0) + remaining;

  return counts;
}

const TraitsService = {
  TRAITS,
  DICE_TYPES,

  /**
   * Build a map: { traitKey: totalBoostCount }
   * counting free, species, and career gifts, including any manifold multipliers.
   */
  calculateBoostMap() {
    const map = Object.create(null);

    function addGift(giftId) {
      if (giftId == null) return;
      const id = String(giftId).trim();
      if (!id || id === '0') return;

      const gift = GiftsState.getGiftById(id);
      if (!gift) return;

      const traitKey = BOOSTS[gift.id];
      if (!traitKey) return;

      const count = parseInt(gift.ct_gifts_manifold, 10) || 1;
      map[traitKey] = (map[traitKey] || 0) + count;
    }

    // 1) Free-choice gifts
    (Array.isArray(GiftsState.selected) ? GiftsState.selected : []).forEach(addGift);

    // 2) Species gifts
    const sp = (SpeciesService && SpeciesService.currentProfile) ? SpeciesService.currentProfile : null;
    if (sp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => addGift(sp[k]));
    }

    // 3) Career gifts (+ replacement gifts, counted ONCE)
    const cp = (CareerService && CareerService.currentProfile) ? CareerService.currentProfile : null;
    if (cp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => addGift(cp[k]));

      if (cp.career_gift_replacements && typeof cp.career_gift_replacements === 'object') {
        Object.values(cp.career_gift_replacements).forEach(v => addGift(v));
      }
    }

    try {
      if (window && window.CG_DEBUG && window.CG_DEBUG.traits_boost_map) {
        console.log('[Traits] boost map →', map);
      }
    } catch (_) {}

    return map;
  },

  enforceCounts() {
    const $    = window.jQuery;
    const freq = { d8:0, d6:0, d4:0 };

    $('.cg-trait-select').each(function(){
      const v = $(this).val();
      if (v && v in freq) freq[v]++;
    });

    $('.cg-trait-select').each(function(){
      const $sel    = $(this);
      const current = $sel.val() || '';
      let options   = '<option value="">— Select —</option>';

      DICE_TYPES.forEach(die => {
        if (freq[die] < MAX_COUNT[die] || current === die) {
          const sel = current === die ? ' selected' : '';
          options += `<option value="${die}"${sel}>${die}</option>`;
        }
      });

      $sel.html(options);
    });
  },

  updateAdjustedDisplays() {
    const $ = window.jQuery;
    const boosts = this.calculateBoostMap();

    // Career boosts are per-copy targeted; only MAIN-career badge/note should reflect boosts targeting main
    const totalCareerBoosts = boosts.trait_career || 0;
    const careerCounts = computeCareerBoostCounts(totalCareerBoosts);
    const careerMainBoosts = careerCounts.main || 0;

    TRAITS.forEach(traitKey => {
      const $sel = $(`#cg-${traitKey}`);
      if (!$sel.length) return;

      const rawBase = String($sel.val() || '').trim();

      let count = boosts[traitKey] || 0;

      if (traitKey === 'trait_career') {
        count = careerMainBoosts;
      }

      let badgeText = '–';
      if (rawBase) {
        badgeText = (count > 0) ? boostedDie(rawBase, count) : rawBase;
      }

      const $badge = $(`#cg-${traitKey}-badge`);
      if ($badge.length) $badge.text(badgeText);

      if (traitKey === 'trait_career') {
        const $pb = $('#cg-profile-trait_career-badge');
        if ($pb.length) $pb.text(badgeText);
      }

      let note = '';

      if (traitKey === 'trait_career') {
        if (careerMainBoosts > 0) {
          note = careerMainBoosts === 1 ? 'Increased by gift' : `Increased by gift ×${careerMainBoosts}`;
        }
      } else {
        const origBoosts = boosts[traitKey] || 0;
        if (origBoosts > 0) {
          note = origBoosts === 1 ? 'Increased by gift' : `Increased by gift ×${origBoosts}`;
        }
      }

      const $note = $(`#cg-${traitKey}-adjusted`);
      if ($note.length) $note.text(note);

      if (traitKey === 'trait_career') {
        const $pn = $('#cg-profile-trait_career-note');
        if ($pn.length) $pn.text(note);
      }
    });
  },

  refreshAll() {
    this.enforceCounts();
    this.updateAdjustedDisplays();
  },

  getBoostedDie(traitKey) {
    const boosts = this.calculateBoostMap();
    let cnt = boosts[traitKey] || 0;

    if (traitKey === 'trait_career') {
      const total = boosts.trait_career || 0;
      const counts = computeCareerBoostCounts(total);
      cnt = counts.main || 0;
    }

    if (!cnt) return '';

    const base = window.jQuery(`#cg-${traitKey}`).val() || 'd4';
    return boostedDie(base, cnt);
  }
};

export default TraitsService;
