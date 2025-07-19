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

    // Persist the raw selection
    data.career = careerId;

    // If cleared, wipe out gifts & recalc
    if (!careerId) {
      CareerAPI.currentProfile = null;

      // Clear out any merged career fields
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

      CareerRender.clearGifts();
      TraitsService.refreshAll();
      SkillsRender.render();
      return;
    }

    // Fetch the career profile
    CareerAPI.loadGifts(careerId, profile => {
      // Expose for trait logic
      CareerAPI.currentProfile = profile;

      // Merge full profile into data.profile
      data.profile = { ...(data.profile || {}), ...profile };

      // Build career-gifts list for trait boosts
      const crGifts = [1, 2, 3].map(i => {
        const id       = profile[`gift_id_${i}`];
        const manifold = parseInt(profile[`manifold_${i}`], 10) || 1;
        return id ? { id, ct_gifts_manifold: manifold } : null;
      }).filter(Boolean);
      GiftsState.setList(crGifts);

      // 1) Render career gifts UI
      CareerRender.renderGifts(profile);

      // 2) Re-render species gifts (so they don’t vanish)
      const sp = data.profile || {};
      if (sp.gift_id_1 || sp.gift_id_2 || sp.gift_id_3) {
        SpeciesRender.renderGifts(sp);
      }

      // 3) Recalculate all trait boosts
      TraitsService.refreshAll();

      // 4) Finally, repaint the Skills tab
      SkillsRender.render();
    });
  }
};
