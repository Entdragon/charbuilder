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

        // Persist selection in builder data
        data.profile = data.profile || {};
        data.profile.species = speciesId;

        console.log('[SpeciesEvents] selected species →', speciesId);

        // If no species selected, clear UI and traits
        if (!speciesId) {
          SpeciesAPI.currentProfile = null;
          SpeciesRender.clearUI();
          TraitsService.refreshAll();
          return;
        }

        // Fetch the full species profile
        SpeciesAPI.loadSpeciesProfile(speciesId, profileData => {
          // Expose for trait calculations
          SpeciesAPI.currentProfile = profileData;

          // Merge into saved profile
          data.profile = { ...data.profile, ...profileData };
          console.log('[SpeciesEvents] loaded profile →', data.profile);

          // Prepare species gifts for state (include manifold)
          const spGifts = [1, 2, 3].map(i => {
            const id       = profileData[`gift_id_${i}`];
            const manifold = parseInt(profileData[`manifold_${i}`], 10) || 1;
            return id ? { id, ct_gifts_manifold: manifold } : null;
          }).filter(Boolean);

          // Merge into central GiftsState
          GiftsState.setList(spGifts);

          // Render gifts and refresh trait boosts
          SpeciesRender.renderGifts(profileData);
          TraitsService.refreshAll();
        });
      });
  }
};
