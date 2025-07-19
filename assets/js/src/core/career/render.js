// assets/js/src/core/career/render.js

import SkillsRender from '../skills/render.js';

const $ = window.jQuery;

export default {
  /**
   * Render the three career gifts into your UI and then
   * re-render the Skills table so the career dice appear.
   */
  renderGifts(profile) {
    // Build an array of gift labels + names, filtering out any empty slots
    const gifts = [
      { label: 'Career Gift One',   name: profile.gift_1 },
      { label: 'Career Gift Two',   name: profile.gift_2 },
      { label: 'Career Gift Three', name: profile.gift_3 }
    ].filter(g => g.name);

    // Turn each into an <li> matching your species template
    const html = gifts
      .map(g => `<li><strong>${g.label}:</strong> ${g.name}</li>`)
      .join('');

    // Dump into your <ul id="career-gifts">
    $('#career-gifts').html(html);

    // Finally, refresh the Skills table
    SkillsRender.render();
  },

  /**
   * Clear the career‐gift UI and re-render Skills.
   */
  clearGifts() {
    $('#career-gifts').empty();
    // also clear the dropdown if you like:
    $('#cg-career').val('');
    SkillsRender.render();
  }
};
