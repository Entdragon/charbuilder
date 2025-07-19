// assets/js/src/core/species/api.js
const $ = window.jQuery;

const SpeciesAPI = {
  /**
   * Populate the #cg-species dropdown.
   */
  loadSpeciesList(cb) {
    const $sel = $('#cg-species').html('<option value="">— Select Species —</option>');

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_species_list',
      security: CG_Ajax.nonce
    })
    .done(res => {
      if (!res.success) return;
      // note: we're now using `name` (aliased in PHP) instead of `ct_species_name`
      res.data.forEach(({ id, name }) => {
        $sel.append(`<option value="${id}">${name}</option>`);
      });
    })
    .always(() => {
      if (typeof cb === 'function') cb();
    });
  },

  /**
   * Fetch the full profile for one species (gifts, skills, etc).
   */
  loadSpeciesProfile(speciesId, cb) {
    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_species_profile',
      id:        speciesId,
      security: CG_Ajax.nonce
    })
    .done(res => {
      if (res.success && typeof cb === 'function') {
        cb(res.data);
      }
    });
  }
};

export default SpeciesAPI;
