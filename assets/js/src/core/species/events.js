// assets/js/src/core/species/events.js

import FormBuilderAPI from '../formBuilder';
import SpeciesAPI     from './api.js';
import SpeciesRender  from './render.js';
import TraitsService  from '../traits/service.js';
import GiftsState     from '../../gifts/state.js';

const $ = window.jQuery;

export default {
  bound: false,

  bind() {
    if (this.bound) return;
    this.bound = true;

    $(document)
      .off('change', '#cg-species')
      .on('change', '#cg-species', () => {
        const speciesId = $('#cg-species').val();
        const data      = FormBuilderAPI.getData();

        console.log('[SpeciesEvents] ⬇ Species changed →', speciesId);

        data.profile = data.profile || {};
        data.profile.species = speciesId;

        if (!speciesId) {
          console.log('[SpeciesEvents] ⚠️ No species selected, clearing state.');
          SpeciesAPI.currentProfile = null;
          SpeciesRender.clearUI();
          GiftsState.setList([]); // 🧼 Clear gifts to prevent leaks
          TraitsService.refreshAll();
          return;
        }

        SpeciesAPI.loadSpeciesProfile(speciesId, profileData => {
          console.log('[SpeciesEvents] 📦 Loaded species profile →', profileData);
          SpeciesAPI.currentProfile = profileData;
          data.profile = { ...data.profile, ...profileData };

          const spGifts = [1, 2, 3].map(i => {
            const id       = profileData[`gift_id_${i}`];
            const manifold = parseInt(profileData[`manifold_${i}`], 10) || 1;
            return id ? { id, ct_gifts_manifold: manifold } : null;
          }).filter(Boolean);

          console.log('[SpeciesEvents] 🧬 Species gifts parsed →', spGifts);

          // Update gift state and THEN refresh traits
          GiftsState.setList(spGifts);
          console.log('[SpeciesEvents] ✅ GiftState set. Now rendering and refreshing...');

          SpeciesRender.renderGifts(profileData);
          TraitsService.refreshAll();
        });
      });
  }
};
