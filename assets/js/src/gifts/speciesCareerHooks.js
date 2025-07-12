// assets/js/gifts/speciesCareerHooks.js

import $ from 'jquery';
import FreeChoices    from './freeChoices.js';
import ExtraCareer    from './extraCareer.js';
import CG_GiftUtils   from './gift-utils.js';  // for renderDropdown

const SpeciesCareerHooks = {
  init() {
    this.bindSpeciesChange();
    this.bindCareerChange();
  },

  bindSpeciesChange() {
    $(document).on('change', '#cg-species', () => {
      const id = $('#cg-species').val();
      const $block = $('#species-gift-block');

      if (!id) {
        $block.empty();
        return;
      }

      $.post(CG_Ajax.ajax_url, {
        action:   'cg_get_species_profile',
        id,
        security: CG_Ajax.nonce
      }).done(res => {
        if (!res.success) return;
        const s = res.data;
        window.CG_Species.currentProfile = s;

        // Build the three species gift dropdowns
        const giftDropdowns = [
          [s.gift_1, s.gift_id_1],
          [s.gift_2, s.gift_id_2],
          [s.gift_3, s.gift_id_3]
        ]
        .filter(([name]) => !!name)
        .map(([name], i) => CG_GiftUtils.renderDropdown(`Species Gift ${i+1}`, name))
        .join('');

        $block.html(giftDropdowns);

        // Refresh free‐choice filters and extra career UI
        FreeChoices.updateOptions();
        ExtraCareer.renderExtraCareerUI();
      });
    });
  },

  bindCareerChange() {
    $(document).on('change', '#cg-career', () => {
      const id = $('#cg-career').val();
      const $block = $('#career-gifts');

      if (!id) {
        $block.empty();
        return;
      }

      $.post(CG_Ajax.ajax_url, {
        action:   'cg_get_career_gifts',
        id,
        security: CG_Ajax.nonce
      }).done(res => {
        if (!res.success) return;
        const c = res.data;
        window.CG_Career.currentProfile = c;

        // Build the three career gift dropdowns
        const giftDropdowns = [
          [c.gift_1, c.gift_id_1],
          [c.gift_2, c.gift_id_2],
          [c.gift_3, c.gift_id_3]
        ]
        .filter(([name]) => !!name)
        .map(([name], i) => CG_GiftUtils.renderDropdown(`Career Gift ${i+1}`, name))
        .join('');

        $block.html(giftDropdowns);

        // Refresh free‐choice filters and extra career UI
        FreeChoices.updateOptions();
        ExtraCareer.renderExtraCareerUI();
      });
    });
  }
};

export default SpeciesCareerHooks;
