;(function($){
  window.CG_Species = {
    currentProfile: null,

    loadSpeciesList(callback) {
      $.post(CG_Ajax.ajax_url, {
        action: 'cg_get_species_list',
        security: CG_Ajax.nonce
      }, res => {
        const $sel = $('#cg-species');
        $sel.html('<option value="">— Select Species —</option>');
        if (res.success){
          res.data.forEach(row => {
            $sel.append(`<option value="${row.id}">${row.ct_species_name}</option>`);
          });
        }
        if (typeof callback === 'function') callback();
      });
    },

    bindSpeciesHandler() {
      $('#cg-app').on('change', '#cg-species', function() {
        const id = $(this).val();
        if (!id) {
          $('#species-gifts').empty();
          $('#species-gift-block').empty();
          return;
        }

        $.post(CG_Ajax.ajax_url, {
          action: 'cg_get_species_profile',
          id,
          security: CG_Ajax.nonce
        }, res => {
          if (!res.success) return;
          const s = res.data;
          CG_Species.currentProfile = s;

          const traits = [];
          if (s.habitat) traits.push(`<li><strong>Habitat:</strong> ${s.habitat}</li>`);
          if (s.diet) traits.push(`<li><strong>Diet:</strong> ${s.diet}</li>`);
          if (s.cycle) traits.push(`<li><strong>Life Cycle:</strong> ${s.cycle}</li>`);

          ['sense_1','sense_2','sense_3'].forEach((key, i) => {
            if (s[key]) traits.push(`<li><strong>Sense ${i+1}:</strong> ${s[key]}</li>`);
          });

          ['weapon_1','weapon_2','weapon_3'].forEach((key, i) => {
            if (s[key]) traits.push(`<li><strong>Weapon ${i+1}:</strong> ${s[key]}</li>`);
          });

          $('#species-gifts').html(`<ul>${traits.join('')}</ul>`);

          const giftDropdowns = [[s.gift_1, s.gift_id_1],
                                 [s.gift_2, s.gift_id_2],
                                 [s.gift_3, s.gift_id_3]]
            .filter(([name]) => !!name)
            .map(([name], i) => CG_GiftUtils.renderDropdown(`Species Gift ${i + 1}`, name))
            .join('');

          $('#species-gift-block').html(giftDropdowns);

          if (window.CG_Traits) CG_Traits.updateAdjustedTraitDisplays();

          setTimeout(() => {
            if (window.CG_Skills) {
              const skillIds = [s.skill_one, s.skill_two, s.skill_three].map(Number).filter(n => !isNaN(n));
              CG_Skills.populateSkillDice('species', null, skillIds);
            }
          }, 100);
        });
      });
    }
  };
})(jQuery);
