// assets/js/gifts/boost.js

import $ from 'jquery';
import FreeChoices from './freeChoices.js';

const Boost = {
  // Tracks which trait key is boosted for each free‐choice index
  boostTargets: {},

  /**
   * Bind handlers for:
   * - Selecting a boost target from the injected selector
   * - Changing extra‐career selects (to recalc boost options)
   */
  init() {
    $(document)
      .on('change', '.cg-free-boost', e => {
        const idx = +$(e.currentTarget).data('index');
        this.boostTargets[idx] = $(e.currentTarget).val();
        CG_Traits.updateAdjustedTraitDisplays();
        CG_Skills.refreshAll();
      })
      .on('change', '.cg-extra-career-block select', () => {
        this.updateBoostUI();
        CG_Skills.refreshAll();
      });
  },

  /**
   * Injects the “Boost Career Trait” selector for Gift 223 at free‐choice index `idx`.
   * Automatically picks the only option if there's just one career.
   */
  injectBoostSelector(idx) {
    // Build list of career trait options
    const careers = [];
    const primaryName = $('#cg-career option:selected').text().trim();
    if (primaryName) {
      careers.push({ key: 'trait_career', name: primaryName });
    }

    $('.cg-extra-career-block select[id^="cg-extra-career-"]').each((i, sel) => {
      const txt = $(sel).find('option:selected').text().trim();
      if (txt) {
        careers.push({ key: `trait_career_${i+1}`, name: txt });
      }
    });

    // Remove any existing boost container for this index
    $(`.cg-free-boost-container[data-index="${idx}"]`).remove();

    // Render appropriate HTML
    const $label = $(`.cg-free-choice-label[data-index="${idx}"]`);
    let html;
    if (careers.length === 1) {
      this.boostTargets[idx] = 'trait_career';
      html = `
        <div class="cg-free-boost-container" data-index="${idx}">
          <small>Boosted ${careers[0].name} automatically</small>
        </div>
      `;
    } else {
      const opts = careers
        .map(o => `<option value="${o.key}">${o.name}</option>`)
        .join('');
      html = `
        <label class="cg-free-boost-container" data-index="${idx}">
          <strong>Boost Career Trait:</strong>
          <select class="cg-free-boost" data-index="${idx}">
            ${opts}
          </select>
        </label>
      `;
    }

    $label.append(html);

    // Restore previous selection or default to first
    const prev = this.boostTargets[idx] || careers[0]?.key;
    if (prev) {
      this.boostTargets[idx] = prev;
      $label.find('.cg-free-boost').val(prev);
    }
  },

  /**
   * For each free‐choice gift of ID 223, remove any old boost UI and re‐inject it.
   * Called after career/extra‐career changes or initial render.
   */
  updateBoostUI() {
    FreeChoices.selected.forEach((giftId, i) => {
      if (giftId === 223) {
        const idx = i + 1;
        $(`.cg-free-boost-container[data-index="${idx}"]`).remove();
        this.injectBoostSelector(idx);
      }
    });

    CG_Traits.updateAdjustedTraitDisplays();
    CG_Skills.refreshAll();
  }
};

export default Boost;
