// assets/js/src/core/species/index.js

import SpeciesAPI     from './api.js';
import SpeciesEvents  from './events.js';
import FormBuilderAPI from '../formBuilder';

// make sure jQuery’s $ is defined
const $ = window.jQuery;

export default {
  init() {
    console.log('[SpeciesIndex] init()');

    // bind your change‐handler once
    if (!SpeciesEvents.bound) {
      SpeciesEvents.bind();
    }

    // grab saved species from the builder data
    const data = FormBuilderAPI.getData();

    // always reload the dropdown
    SpeciesAPI.loadSpeciesList(() => {
      // after options are in place, re‐apply selection
      if (data.profile && data.profile.species) {
        $('#cg-species')
          .val(data.profile.species)
          .trigger('change');
      }
    });
  }
};
