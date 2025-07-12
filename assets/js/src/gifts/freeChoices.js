// assets/js/gifts/freeChoices.js

import $ from 'jquery';
import Boost        from './boost.js';
import ExtraCareer  from './extraCareer.js';

const FreeChoices = {
  allGifts: [],
  selected: [],
  freeLoaded: false,

  init() {
    this.loadFreeChoices();
  },

  /**
   * Load the full list of free‐choice gifts via AJAX,
   * register them, then render selectors.
   */
  loadFreeChoices() {
    if (this.freeLoaded) return;
    const $container = $('#cg-free-choices');
    if (!$container.length) return;

    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_free_gifts',
      security: CG_Ajax.nonce
    }).done(res => {
      if (!res.success) return;
      this.allGifts   = res.data;
      if (window.CG_GiftLibrary) CG_GiftLibrary.register(res.data);
      this.freeLoaded = true;
      this.renderFreeChoiceSelectors();
    });
  },

  /**
   * Render three free‐choice dropdowns and initialize their state.
   */
  renderFreeChoiceSelectors() {
    const html = [1,2,3].map(i => `
      <label class="cg-free-choice-label" data-index="${i}">
        <strong>Free Choice ${i}:</strong>
        <select class="cg-free-gift" data-index="${i}">
          <option value="">— Select —</option>
        </select>
      </label>
    `).join('');

    $('#cg-free-choices').html(html);

    this.updateOptions(() => {
      [1,2,3].forEach(i => {
        const v = this.selected[i-1] || '';
        $(`.cg-free-gift[data-index="${i}"]`).val(v);
      });
    });

    this.bindFreeChoiceHandlers();
    Boost.updateBoostUI();
  },

  /**
   * Bind change handlers on the free‐choice selects.
   * Updates state, re-filters options, triggers boosts & extra careers.
   */
  bindFreeChoiceHandlers() {
    $('#cg-free-choices')
      .off('change.fc')
      .on('change.fc', '.cg-free-gift', e => {
        const idx  = +$(e.currentTarget).data('index');
        const gift = +$(e.currentTarget).val() || null;
        this.selected[idx - 1] = gift;

        // handle boost selector injection/cleanup
        $('.cg-free-boost-container[data-index="'+idx+'"]').remove();
        if (gift === 223) {
          Boost.injectBoostSelector(idx);
        }

        this.updateOptions();
        CG_Traits.updateAdjustedTraitDisplays();
        CG_Skills.refreshAll();
        ExtraCareer.renderExtraCareerUI();
        Boost.updateBoostUI();
      });
  },

  /**
   * Repopulate each free‐gift dropdown based on current context:
   * - Exclude gifts via CG_GiftLibrary
   * - Show gift 184 only if a career is selected
   */
  updateOptions(callback) {
    const selIds = this.selected.filter(Boolean);

    const careerIds = window.CG_Career?.currentProfile
      ? ['gift_id_1','gift_id_2','gift_id_3']
          .map(k => +CG_Career.currentProfile[k])
          .filter(id => id > 0)
      : [];

    const speciesIds = window.CG_Species?.currentProfile
      ? ['gift_id_1','gift_id_2','gift_id_3']
          .map(k => +CG_Species.currentProfile[k])
          .filter(id => id > 0)
      : [];

    const allSelected = [...selIds, ...careerIds, ...speciesIds];
    const hasCareer   = !!$('#cg-career').val();

    $('.cg-free-gift').each((_, el) => {
      const $sel = $(el);
      const cur  = +$sel.val() || null;
      $sel.empty().append('<option value="">— Select —</option>');

      this.allGifts.forEach(g => {
        const id = +g.id;
        const excluded = CG_GiftLibrary.isExcludedFromFreeSelection(id, allSelected);
        const allow184 = id === 184 && hasCareer;

        if (id === cur || (!excluded || allow184)) {
          const $opt = $('<option>')
            .val(id)
            .text(g.ct_gifts_name);
          if (id === cur) $opt.prop('selected', true);
          $sel.append($opt);
        }
      });
    });

    if (typeof callback === 'function') callback();
  }
};

export default FreeChoices;
