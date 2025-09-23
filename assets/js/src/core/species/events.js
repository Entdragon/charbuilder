// assets/js/src/core/species/events.js
// One-time, namespaced bindings for Species select.

import SpeciesAPI from './api.js';

const $ = window.jQuery;
let _bound = false;

export default function bindSpeciesEvents() {
  if (_bound) return;
  _bound = true;

  $(document)
    .off('change.cg', '#cg-species')
    .on('change.cg', '#cg-species', e => {
      const val = (e.currentTarget && e.currentTarget.value) || '';
      console.log('[SpeciesEvents] selected species →', val);
      // Optional: fetch the profile and broadcast a custom event
      SpeciesAPI.fetchProfile(val).done(profile => {
        $(document).trigger('cg:species:changed', [{ id: val, profile }]);
      });
    });
}
