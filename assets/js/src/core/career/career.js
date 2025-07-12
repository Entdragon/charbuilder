;(function($){
  window.CG_Career = {
    CAREERS: {},
    currentProfile: null,

    loadCareerList(callback){
      const $sel = $('#cg-career');
      if (!$sel.length) {
        console.warn('[CG_Career] Missing #cg-career select');
        if (typeof callback === 'function') callback();
        return;
      }

      $sel.html('<option value="">— Select Career —</option>');

      $.post(CG_Ajax.ajax_url, {
        action: 'cg_get_career_list',
        security: CG_Ajax.nonce
      }).done(res => {
        if (res.success){
          res.data.forEach(row => {
            this.CAREERS[row.id] = row.ct_career_name;
            $sel.append(`<option value="${row.id}">${row.ct_career_name}</option>`);
          });
        }
        if (typeof callback === 'function') callback();
      });
    },

    bindCareerHandler(){
      $(document).on('change', '#cg-career', () => {
        const careerId = $('#cg-career').val();
        const $gifts   = $('#career-gifts');

        if (!$gifts.length) {
          console.warn('[CG_Career] Missing #career-gifts container');
        } else {
          $gifts.empty();
        }

        if (!careerId) {
          CG_Skills.populateSkillDice('career', '', []);
          if (window.CG_Gifts) CG_Gifts.renderExtraCareerUI();
          return;
        }

        $.post(CG_Ajax.ajax_url, {
          action: 'cg_get_career_gifts',
          id: careerId,
          security: CG_Ajax.nonce
        }).done(res => {
          if (!res.success) {
            console.warn('[CG_Career] cg_get_career_gifts failed');
            return;
          }

          const c = res.data;
          CG_Career.currentProfile = c;

          const dice = $('#cg-trait_career').val() || '';
          const skillIds = [c.skill_one, c.skill_two, c.skill_three]
            .map(Number).filter(n => !isNaN(n));
          CG_Skills.populateSkillDice('career', dice, skillIds);

          const giftDropdowns = [[c.gift_1, c.gift_id_1],
                                 [c.gift_2, c.gift_id_2],
                                 [c.gift_3, c.gift_id_3]]
            .filter(([name]) => !!name)
            .map(([name], i) => CG_GiftUtils.renderDropdown(`Career Gift ${i + 1}`, name))
            .join('');

          $gifts.html(giftDropdowns);

          if (window.CG_Traits) CG_Traits.updateAdjustedTraitDisplays();
          if (window.CG_Gifts) CG_Gifts.renderExtraCareerUI();
        }).fail(() => {
          console.error('[CG_Career] AJAX cg_get_career_gifts error');
        });
      });
    }
  };

  $(function(){
    CG_Career.bindCareerHandler();
  });
})(jQuery);
