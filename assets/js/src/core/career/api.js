const CareerAPI = {
  /**
   * Load the career dropdown list.
   */
  loadList(callback) {
    console.group('[CareerAPI] 🔄 loadList() called');

    const $sel = $('#cg-career');
    if (!$sel.length) {
      console.warn('[CareerAPI] ❌ Selector #cg-career not found in DOM');
      console.groupEnd();
      return;
    }

    $sel.html('<option value="">— Select Career —</option>');

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_career_list',
      security: CG_Ajax.nonce
    })
    .done(res => {
      console.log('[CareerAPI] ✅ AJAX success for career list:', res);

      if (!res.success) {
        console.warn('[CareerAPI] ❌ Career list response unsuccessful');
        return;
      }

      if (!Array.isArray(res.data)) {
        console.warn('[CareerAPI] ⚠️ Unexpected response format for career list:', res.data);
        return;
      }

      res.data.forEach(({ id, name }) => {
        $sel.append(`<option value="${id}">${name}</option>`);
        console.log(`[CareerAPI] ➕ Added option: ${name} (ID: ${id})`);
      });

      const currentVal = $sel.data('selected') || '';
      if (currentVal) {
        $sel.val(currentVal);
        console.log(`[CareerAPI] 🔁 Re-applying selected career → ${currentVal}`);
      } else {
        console.log('[CareerAPI] ℹ️ No selected career to re-apply');
      }
    })
    .fail((xhr, status, error) => {
      console.error('[CareerAPI] ❌ AJAX failed for career list:', {
        status,
        error,
        response: xhr.responseText
      });
    })
    .always(() => {
      if (typeof callback === 'function') {
        console.log('[CareerAPI] 📞 Executing loadList callback');
        callback();
      } else {
        console.log('[CareerAPI] ⚠️ No callback provided to loadList');
      }
      console.groupEnd();
    });
  },

  /**
   * Load the gift profile for a selected career.
   */
  loadGifts(careerId, callback) {
    console.group(`[CareerAPI] 🔍 loadGifts(${careerId}) called`);

    if (!careerId) {
      console.warn('[CareerAPI] ❗ No career ID provided');
      console.groupEnd();
      return;
    }

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_career_gifts',
      id:       careerId,
      security: CG_Ajax.nonce
    })
    .done(res => {
      console.log('[CareerAPI] ✅ AJAX success for career gifts:', res);

      if (res.success && typeof callback === 'function') {
        console.log('[CareerAPI] 📞 Executing loadGifts callback with data:', res.data);
        callback(res.data);
      } else if (!res.success) {
        console.warn('[CareerAPI] ❌ Response unsuccessful for career gifts');
      } else {
        console.warn('[CareerAPI] ⚠️ No valid callback provided for career gifts');
      }
    })
    .fail((xhr, status, error) => {
      console.error(`[CareerAPI] ❌ AJAX failed for career gifts ID ${careerId}:`, {
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

export default CareerAPI;
