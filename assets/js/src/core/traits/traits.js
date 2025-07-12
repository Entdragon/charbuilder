;(function($){
  window.CG_Traits = {
    TRAITS: ['will','speed','body','mind','trait_species','trait_career'],
    DICE_TYPES: ['d8','d6','d4'],
    MAX_COUNT: { d8: 2, d6: 3, d4: 1 },

    BOOSTS: {
      78:  'will',
      89:  'speed',
      85:  'body',
      100: 'mind',
      224: 'trait_species',
      223: 'trait_career'
    },

    enforceCounts: function(){
      const freq = { d8: 0, d6: 0, d4: 0 };

      $('.cg-traits select').each(function(){
        const val = $(this).val();
        if (val && freq.hasOwnProperty(val)) freq[val]++;
      });

      $('.cg-traits select').each(function(){
        const $sel = $(this);
        const current = $sel.val();
        let html = '<option value="">— Select —</option>';

        CG_Traits.DICE_TYPES.forEach(die => {
          if (freq[die] < CG_Traits.MAX_COUNT[die] || current === die) {
            html += `<option value="${die}"${current === die ? ' selected' : ''}>${die}</option>`;
          }
        });

        $sel.html(html);
      });

      CG_Traits.updateAdjustedTraitDisplays();
    },

    updateAdjustedTraitDisplays: function(){
      const boosts = {};

      // Free choice boosts
      if (CG_Gifts?.selected?.length) {
        CG_Gifts.selected.forEach(id => {
          const trait = CG_Traits.BOOSTS[id];
          if (trait) boosts[trait] = (boosts[trait] || 0) + 1;
        });
      }

      // Species gift boosts
      const s = window?.CG_Species?.currentProfile;
      if (s) {
        ['gift_id_1','gift_id_2','gift_id_3'].forEach(key => {
          const id = s[key];
          const trait = CG_Traits.BOOSTS[id];
          if (trait) boosts[trait] = (boosts[trait] || 0) + 1;
        });
      }

      // Career gift boosts
      const c = window?.CG_Career?.currentProfile;
      if (c) {
        ['gift_id_1','gift_id_2','gift_id_3'].forEach(key => {
          const id = c[key];
          const trait = CG_Traits.BOOSTS[id];
          if (trait) boosts[trait] = (boosts[trait] || 0) + 1;
        });
      }

      // Apply and display boosted value
      const order = ['d4','d6','d8','d10','d12'];
      CG_Traits.TRAITS.forEach(trait => {
        const $sel = $(`#cg-${trait}`);
        const base = $sel.val() || 'd4';
        const index = order.indexOf(base);
        const upgrade = boosts[trait] || 0;
        const boostedIndex = Math.min(index + upgrade, order.length - 1);
        const boosted = order[boostedIndex];
        const label = upgrade > 0
          ? `Increased by gift ×${upgrade}: ${boosted}`
          : '';
        $(`#cg-${trait}-adjusted`).text(label);
      });
    }
  };

  $(function(){
    CG_Traits.enforceCounts();

    $(document).on('change', '.cg-traits select', function(){
      CG_Traits.enforceCounts();
    });
  });
})(jQuery);
