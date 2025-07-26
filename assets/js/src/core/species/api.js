// assets/js/src/core/species/api.js
const $ = window.jQuery;

const SpeciesAPI = {
  /**
   * Populate the #cg-species dropdown.
   */
  loadSpeciesList(cb) {
    console.group('[SpeciesAPI] 🔄 loadSpeciesList() called');

    const $sel = $('#cg-species');
    if (!$sel.length) {
      console.warn('[SpeciesAPI] ❌ Selector #cg-species not found in DOM');
      console.groupEnd();
      return;
    }

    $sel.html('<option value="">— Select Species —</option>');

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_species_list',
      security: CG_Ajax.nonce
    })
    .done(res => {
      console.log('[SpeciesAPI] ✅ AJAX success:', res);

      if (!res.success) {
        console.warn('[SpeciesAPI] ❌ Species list response unsuccessful');
        return;
      }

      if (!Array.isArray(res.data)) {
        console.warn('[SpeciesAPI] ⚠️ Unexpected data format:', res.data);
        return;
      }

      res.data.forEach(({ id, name }) => {
        $sel.append(`<option value="${id}">${name}</option>`);
        console.log(`[SpeciesAPI] ➕ Added species option: ${name} (ID: ${id})`);
      });

      const currentVal = $sel.data('selected') || '';
      if (currentVal) {
        $sel.val(currentVal);
        console.log(`[SpeciesAPI] 🔁 Re-applying selected species → ${currentVal}`);
      } else {
        console.log('[SpeciesAPI] ℹ️ No selected species to re-apply');
      }
    })
    .fail((xhr, status, error) => {
      console.error('[SpeciesAPI] ❌ AJAX failed for species list:', {
        status,
        error,
        response: xhr.responseText
      });
    })
    .always(() => {
      if (typeof cb === 'function') {
        console.log('[SpeciesAPI] 📞 Executing loadSpeciesList callback');
        cb();
      } else {
        console.log('[SpeciesAPI] ⚠️ No callback provided to loadSpeciesList');
      }
      console.groupEnd();
    });
  },

  /**
   * Fetch the full profile for one species (gifts, skills, etc).
   */
  loadSpeciesProfile(speciesId, cb) {
    console.group(`[SpeciesAPI] 🔄 loadSpeciesProfile(${speciesId}) called`);

    if (!speciesId) {
      console.warn('[SpeciesAPI] ❗ No species ID provided');
      console.groupEnd();
      return;
    }

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_species_profile',
      id:       speciesId,
      security: CG_Ajax.nonce
    })
    .done(res => {
      console.log('[SpeciesAPI] ✅ AJAX success for profile:', res);

      if (res.success && typeof cb === 'function') {
        console.log('[SpeciesAPI] 📞 Executing profile callback with data:', res.data);
        cb(res.data);
      } else if (!res.success) {
        console.warn('[SpeciesAPI] ❌ Profile load was not successful');
      } else {
        console.warn('[SpeciesAPI] ⚠️ No valid callback function provided');
      }
    })
    .fail((xhr, status, error) => {
      console.error('[SpeciesAPI] ❌ AJAX failed for species profile:', {
        status,
        error,
        response: xhr.responseText
      });
    })
    .always(() => {
      console.groupEnd();
    });
  }
};

export default SpeciesAPI;
