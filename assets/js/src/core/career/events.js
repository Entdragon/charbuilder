// assets/js/src/core/career/events.js

import CareerAPI      from './api.js';
import CareerRender   from './render.js';
import SkillsRender   from '../skills/render.js';
import FormBuilderAPI from '../formBuilder';
import GiftsState     from '../../gifts/state.js';
import SpeciesRender  from '../species/render.js';
import TraitsService  from '../traits/service.js';

const $ = window.jQuery;

export default {
  bound: false,

  bind() {
    if (this.bound) return;
    this.bound = true;

    $(document)
      .off('change', '#cg-career')
      .on('change', '#cg-career', () => this.handleCareerChange());
  },

  handleCareerChange() {
    const careerId = $('#cg-career').val();
    const data     = FormBuilderAPI.getData();

    console.log('[CareerEvents] ⬇ Career changed →', careerId);

    // Save raw selection
    data.career = careerId;

    if (!careerId) {
      console.log('[CareerEvents] ⚠️ Career cleared — resetting profile and UI');
      CareerAPI.currentProfile = null;

      // Preserve other profile data, only clear career-related parts
      data.profile = {
        ...(data.profile || {}),
        careerName: '',
        gift_1: null,
        gift_2: null,
        gift_3: null,
        skill_one: null,
        skill_two: null,
        skill_three: null
      };

      GiftsState.setList([]);
      CareerRender.clearGifts();
      TraitsService.refreshAll();
      SkillsRender.render();
      return;
    }

    // Fetch the career profile (includes skills and gifts)
    CareerAPI.loadGifts(careerId, profile => {
      console.log('[CareerEvents] 📦 Loaded career profile →', profile);

      CareerAPI.currentProfile = profile;
      data.profile = { ...(data.profile || {}), ...profile };

      // Extract and structure the 3 gifts for state & boost calculation
      const crGifts = [1, 2, 3].map(i => {
        const id       = profile[`gift_id_${i}`];
        const manifold = parseInt(profile[`manifold_${i}`], 10) || 1;
        return id ? { id, ct_gifts_manifold: manifold } : null;
      }).filter(Boolean);

      console.log('[CareerEvents] 🧬 Parsed career gifts →', crGifts);
      GiftsState.setList(crGifts);

      // 1) Update UI for visible gift list
      CareerRender.renderGifts(profile);

      // 2) Restore species gifts in UI (if present)
      const sp = data.profile || {};
      if (sp.gift_id_1 || sp.gift_id_2 || sp.gift_id_3) {
        console.log('[CareerEvents] 🔁 Re-rendering species gifts to preserve UI');
        SpeciesRender.renderGifts(sp);
      }

      // 3) Recalculate boosts and enforce dice rules
      TraitsService.refreshAll();

      // 4) Update skill display with career dice
      SkillsRender.render();
    });
  }
};
