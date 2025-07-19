// handles all AJAX calls for careers

const $ = window.jQuery;


const CareerAPI = {
  loadList(callback) {
    const $sel = $('#cg-career').html('<option value="">— Select Career —</option>');

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_career_list',
      security: CG_Ajax.nonce
    })
    .done(res => {
      if (res.success) {
        res.data.forEach(item =>
          $sel.append(`<option value="${item.id}">${item.name}</option>`)
        );
      }
    })
    .always(() => {
      if (typeof callback === 'function') callback();
    });
  },

  loadGifts(careerId, callback) {
    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_career_gifts',
      id:       careerId,
      security: CG_Ajax.nonce
    })
    .done(res => {
      if (res.success && typeof callback === 'function') {
        callback(res.data);
      }
    });
  },

//  loadEligibleExtraCareers(giftIds, callback) {
//    $.post(CG_Ajax.ajax_url, {
//      action:   'cg_get_eligible_extra_careers',
//      gifts:    giftIds,
//      security: CG_Ajax.nonce
//    })
//    .done(res => {
//      if (res.success && typeof callback === 'function') {
//        callback(res.data);
//      }
//    });
//  }
};

export default CareerAPI;
