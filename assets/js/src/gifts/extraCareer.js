// assets/js/gifts/extraCareer.js

import $ from 'jquery';
import FreeChoices from './freeChoices.js';
import Boost       from './boost.js';

/**
 * Renders “Extra Career” UI blocks when Gift 184 is chosen.
 * Depends on:
 * - A primary career selected (#cg-career)
 * - Gift 184 in free choices
 * - Gifts from career, species, free choices to send to server
 */
const ExtraCareer = {
  // Preserve selections across renders
  extraSelected: {},

  init() {
    // Initial render in case state was prefilled
    this.renderExtraCareerUI();
  },

  renderExtraCareerUI() {
    const $container = $('#cg-extra-careers');
    const primaryId  = Number($('#cg-career').val()) || null;
    const count184   = FreeChoices.selected.filter(id => id === 184).length;

    // Bail out if no extra career block or prerequisites missing
    if (!$container.length || !primaryId || count184 === 0) {
      this.extraSelected = {};
      $container.empty();
      return;
    }

    // Gather all gift IDs: free choices, career profile, species profile
    const giftIds = [
      ...FreeChoices.selected.filter(Boolean),
      ...(window.CG_Career.currentProfile
        ? ['gift_id_1','gift_id_2','gift_id_3']
            .map(k => +window.CG_Career.currentProfile[k])
            .filter(Boolean)
        : []),
      ...(window.CG_Species.currentProfile
        ? ['gift_id_1','gift_id_2','gift_id_3']
            .map(k => +window.CG_Species.currentProfile[k])
            .filter(Boolean)
        : [])
    ];

    // Call server for eligible extra careers
    $.post(CG_Ajax.ajax_url, {
      action:   'cg_get_eligible_extra_careers',
      gifts:    giftIds,
      security: CG_Ajax.nonce
    }).done(res => {
      if (!res.success) return;

      // Exclude primary career from list
      const list = res.data.filter(c => +c.id !== primaryId);

      // Build HTML for each extra career slot
      let html = '';
      for (let i = 1; i <= count184; i++) {
        const selId = this.extraSelected[i] || '';
        html += `
          <div class="cg-extra-career-block">
            <label><strong>Extra Career ${i}</strong></label>
            <select id="cg-extra-career-${i}" class="cg-profile-select">
              <option value="">— Select —</option>
              ${list.map(c =>
                `<option value="${c.id}"${c.id == selId ? ' selected' : ''}>
                  ${c.name}
                </option>`
              ).join('')}
            </select>

            <label><strong>Trait</strong></label>
            <select id="cg-trait_career_${i}" class="cg-trait-career-select">
              <option value="d4">d4</option>
            </select>

            <div class="trait-adjusted" id="cg-trait_career_${i}-adjusted"></div>
          </div>
        `;
      }

      $container.html(html);

      // Reapply boost UI and reload skills for the new extra careers
      Boost.updateBoostUI();
      CG_Skills.loadSkillsList(() => {
        CG_Skills.scanExtraCareers();
        CG_Skills.refreshAll();
      });
    });
  }
};

export default ExtraCareer;
