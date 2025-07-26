console.log("🔥 TraitsService loaded and exposing globally");

import GiftsState     from '../../gifts/state.js';
import SpeciesService from '../species/api.js';
import CareerService  from '../career/api.js';

const DIE_ORDER = ['d4', 'd6', 'd8', 'd10', 'd12'];

const BOOSTS = {
  78:  'will',
  89:  'speed',
  85:  'body',
  100: 'mind',
  224: 'trait_species',
  223: 'trait_career'
};

const TRAITS     = ['will', 'speed', 'body', 'mind', 'trait_species', 'trait_career'];
const DICE_TYPES = ['d8', 'd6', 'd4'];
const MAX_COUNT  = { d8: 2, d6: 3, d4: 1 };

const TraitsService = {
  TRAITS,
  DICE_TYPES,

  calculateBoostMap() {
    console.group('[TraitsService] 🧮 calculateBoostMap()');
    const map = {};

    function addGift(giftId) {
      console.log(`[TraitsService] ➕ addGift() called with ID: ${giftId}`);
      if (!giftId) return;
      const gift = GiftsState.getGiftById(giftId);
      if (!gift) {
        console.warn(`[TraitsService] ⚠️ Gift not found: ${giftId}`);
        return;
      }

      console.log(`[TraitsService] 🎁 Gift resolved: ${gift.name} (ID ${gift.id})`);
      const traitKey = BOOSTS[gift.id];
      if (!traitKey) {
        console.log(`[TraitsService] 🛑 No trait boost associated with gift ID ${gift.id}`);
        return;
      }

      const count = parseInt(gift.ct_gifts_manifold, 10) || 1;
      map[traitKey] = (map[traitKey] || 0) + count;

      console.log(`[TraitsService] ✅ Boost applied → ${traitKey} +${count}`);
    }

    console.log('[TraitsService] ➕ GiftsState.selected:', GiftsState.selected);
    GiftsState.selected.forEach(addGift);

    const sp = SpeciesService.currentProfile;
    console.log('[TraitsService] 🐾 SpeciesService.currentProfile:', sp);
    if (sp) {
      console.log('[TraitsService] 🐾 Processing species gifts');
      ['gift_id_1', 'gift_id_2', 'gift_id_3'].forEach(key => addGift(sp[key]));
    }

    const cp = CareerService.currentProfile;
    console.log('[TraitsService] 🧢 CareerService.currentProfile:', cp);
    if (cp) {
      console.log('[TraitsService] 🧢 Processing career gifts');
      ['gift_id_1', 'gift_id_2', 'gift_id_3'].forEach(key => addGift(cp[key]));
    }

    console.log('[TraitsService] ✅ Final boost map:', map);
    console.groupEnd();
    return map;
  },

  enforceCounts() {
    console.group('[TraitsService] ✅ enforceCounts()');
    const $ = window.jQuery;
    const freq = { d8: 0, d6: 0, d4: 0 };

    $('.cg-trait-select').each(function () {
      const v = $(this).val();
      if (v && v in freq) freq[v]++;
    });

    console.log('[TraitsService] 📊 Dice usage counts →', freq);

    $('.cg-trait-select').each(function () {
      const $sel    = $(this);
      const current = $sel.val() || '';
      let options   = '<option value="">— Select —</option>';

      DICE_TYPES.forEach(die => {
        if (freq[die] < MAX_COUNT[die] || current === die) {
          const sel = current === die ? ' selected' : '';
          options += `<option value="${die}"${sel}>${die}</option>`;
        }
      });

      console.log(`[TraitsService] 🔁 Refreshing dropdown for #${$sel.attr('id')} → current: "${current}"`);
      $sel.html(options);
    });
    console.groupEnd();
  },

  updateAdjustedDisplays() {
    console.group('[TraitsService] 🚀 updateAdjustedDisplays()');
    const $ = window.jQuery;
    const boosts = this.calculateBoostMap();

    TRAITS.forEach(traitKey => {
      const $sel = $(`#cg-${traitKey}`);
      if (!$sel.length) {
        console.warn(`[TraitsService] ⚠️ Selector not found: #cg-${traitKey}`);
        return;
      }

      const raw = $sel.val();
      console.log(`[TraitsService] 🔍 Trait: ${traitKey} | Raw: "${raw}"`);

      let label;

      if (!raw || !DIE_ORDER.includes(raw)) {
        label = '–';
      } else {
        const idx  = DIE_ORDER.indexOf(raw);
        const step = boosts[traitKey] || 0;
        const cap  = Math.min(idx + step, DIE_ORDER.length - 1);
        label = DIE_ORDER[cap];
      }

      const count = boosts[traitKey] || 0;
      let suffix = '';
      if (count === 1) suffix = ' (increased by gift)';
      else if (count > 1) suffix = ` (increased by gift ${count} times)`;

      console.log(`[TraitsService] 🎲 ${traitKey}: ${label}${suffix}`);

      $(`#cg-${traitKey}-adjusted`).text(label + suffix);
    });
    console.groupEnd();
  },

  refreshAll() {
    console.group('[TraitsService] 🔁 refreshAll()');
    this.enforceCounts();
    this.updateAdjustedDisplays();
    console.groupEnd();
  },

  getBoostedDie(traitKey) {
    console.group(`[TraitsService] ➕ getBoostedDie("${traitKey}")`);
    const boosts = this.calculateBoostMap();
    const cnt = boosts[traitKey] || 0;

    const base = window.jQuery(`#cg-${traitKey}`).val();
    console.log(`[TraitsService] 🔎 Base: "${base}", Boost: ${cnt}`);

    if (!DIE_ORDER.includes(base)) {
      console.log('[TraitsService] ⛔ Invalid base die — returning "–"');
      console.groupEnd();
      return '–';
    }

    const idx = DIE_ORDER.indexOf(base);
    const result = DIE_ORDER[Math.min(idx + cnt, DIE_ORDER.length - 1)];
    console.log(`[TraitsService] ✅ Result: ${result}`);
    console.groupEnd();
    return result;
  }
};

window.TraitsService = TraitsService;
export default TraitsService;
