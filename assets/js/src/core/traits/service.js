// assets/js/src/core/traits/service.js

import GiftsState     from '../../gifts/state.js';
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

const TraitsService = {
  TRAITS,
  DICE_TYPES,

  /**
   * Build a map: { traitKey: totalBoostCount }
   * counting free, species, and career gifts, including any manifold multipliers.
   */
  calculateBoostMap() {
    const map = {};

    /** 
     * Look up one gift, see if it’s in BOOSTS, then add
     * its manifold count (default 1) to that traitKey in map.
     */
    function addGift(giftId) {
      if (!giftId) return;
      // Find the full gift object (with manifold, requires, etc)
      const gift = GiftsState.getGiftById(giftId);
      if (!gift) return;

      // Only process if it’s a known boost gift
      const traitKey = BOOSTS[gift.id];
      if (!traitKey) return;

      // How many steps does this gift boost? `ct_gifts_manifold` or 1
      const count = parseInt(gift.ct_gifts_manifold, 10) || 1;

      map[traitKey] = (map[traitKey] || 0) + count;
    }

    // 1) Free‐choice gifts
    GiftsState.selected.forEach(addGift);

    // 2) Species gifts (profile.gift_id_1 … gift_id_3)
    const sp = SpeciesService.currentProfile;
    if (sp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(key => {
        addGift(sp[key]);
      });
    }

    // 3) Career gifts
    const cp = CareerService.currentProfile;
    if (cp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(key => {
        addGift(cp[key]);
      });
    }

    console.log('[Traits] boost map →', map);
    return map;
  },

  /**
   * Enforce dice‐count limits on the trait selects.
   */
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

  /**
   * Update the “adjusted” labels under each trait select.
   */
  updateAdjustedDisplays() {
    const $      = window.jQuery;
    const boosts = this.calculateBoostMap();

    TRAITS.forEach(traitKey => {
      const $sel   = $(`#cg-${traitKey}`);
      const base   = $sel.val() || 'd4';
      const idx    = DIE_ORDER.indexOf(base);
      const count  = boosts[traitKey] || 0;
      const boosted = DIE_ORDER[Math.min(idx + count, DIE_ORDER.length - 1)];
      $(`#cg-${traitKey}-adjusted`).text(boosted);
    });
  },

  /**
   * Re-run both counts & adjusted displays.
   */
  refreshAll() {
    this.enforceCounts();
    this.updateAdjustedDisplays();
  },

  /**
   * Get a single trait’s boosted die (for external use).
   */
  getBoostedDie(traitKey) {
    const boosts = this.calculateBoostMap();
    const cnt    = boosts[traitKey] || 0;
    if (!cnt) return '';
    const base = window.jQuery(`#cg-${traitKey}`).val() || 'd4';
    const idx  = DIE_ORDER.indexOf(base);
    return DIE_ORDER[Math.min(idx + cnt, DIE_ORDER.length - 1)];
  }
};

export default TraitsService;
