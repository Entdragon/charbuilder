// assets/js/src/core/formBuilder/render-details.js

import TraitsService from '../traits/service.js';

const TRAITS = TraitsService.TRAITS;       // ["will","speed","body","mind","trait_species","trait_career"]
const DICE   = TraitsService.DICE_TYPES;   // ["d8","d6","d4"]

function escape(val) {
  const s = val == null ? '' : String(val);
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function capitalize(str = '') {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

export default {
  renderTabs() {
    return `
      <ul class="cg-tabs">
        <li data-tab="tab-traits" class="active">Details & Traits</li>
        <li data-tab="tab-profile">Profile: Species, Career & Gifts</li>
        <li data-tab="tab-skills">Skills</li>
        <li data-tab="tab-summary">Summary</li>
      </ul>
    `;
  },

  renderContent(data = {}) {
    // Build each trait’s <select> using the full DICE array—
    // no pool removal, so every option is always rendered.
    const traitFields = TRAITS.map(trait => {
      const val   = String(data[trait] ?? '');
      const label = trait === 'trait_species' ? 'Species'
                  : trait === 'trait_career'  ? 'Career'
                  : capitalize(trait);

      const options = DICE.map(die => {
        const sel = (die === val) ? ' selected' : '';
        return `<option value="${die}"${sel}>${die}</option>`;
      }).join('');

      return `
        <div class="cg-trait">
          <label>${label} <small>(choose one)</small></label>
          <select id="cg-${trait}" class="cg-trait-select">
            <option value="">&mdash; Select &mdash;</option>
            ${options}
          </select>
          <div
            class="trait-adjusted"
            id="cg-${trait}-adjusted"
            style="color:#0073aa;font-weight:bold;"
          ></div>
        </div>
      `;
    }).join('');

    return `
      <div id="tab-traits" class="tab-panel active">
        <div class="cg-details-panel">

          <div class="cg-details-box">
            <h3>Details</h3>
            <div class="cg-profile-grid">
              <div>
                <label>Character Name</label>
                <input type="text" id="cg-name" value="${escape(data.name)}" required />
              </div>
              <div>
                <label>Player Name</label>
                <input type="text" id="cg-player-name" value="${escape(data.player_name)}" />
              </div>

              <div>
                <label>Gender</label>
                <select id="cg-gender">
                  <option value="">&mdash; Select &mdash;</option>
                  <option value="Male"     ${data.gender === 'Male'     ? 'selected' : ''}>Male</option>
                  <option value="Female"   ${data.gender === 'Female'   ? 'selected' : ''}>Female</option>
                  <option value="Nonbinary"${data.gender === 'Nonbinary'? 'selected' : ''}>Nonbinary</option>
                </select>
              </div>
              <div>
                <label>Age</label>
                <input type="text" id="cg-age" value="${escape(data.age)}" required />
              </div>
            </div>

            <label>Motto</label>
            <input type="text" id="cg-motto" value="${escape(data.motto)}" />

            <label>Goal 1</label>
            <input type="text" id="cg-goal1" value="${escape(data.goal1)}" />
            <label>Goal 2</label>
            <input type="text" id="cg-goal2" value="${escape(data.goal2)}" />
            <label>Goal 3</label>
            <input type="text" id="cg-goal3" value="${escape(data.goal3)}" />
          </div>

          <div class="cg-traits-box">
            <h3>Traits</h3>
            <div class="cg-profile-grid">
              ${traitFields}
            </div>
          </div>

          <div class="cg-text-box">
            <h3>Description & Backstory</h3>
            <label>Description</label>
            <textarea id="cg-description">${escape(data.description)}</textarea>
            <label>Backstory</label>
            <textarea id="cg-backstory">${escape(data.backstory)}</textarea>
          </div>
        </div>
      </div>
    `;
  }
};
