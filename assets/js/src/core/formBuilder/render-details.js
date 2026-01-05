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
        <li data-tab="tab-details" class="active">Details</li>
        <li data-tab="tab-traits">Traits, Species, Careers</li>
        <li data-tab="tab-gifts">Gifts</li>
        <li data-tab="tab-skills">Skills</li>
        <li data-tab="tab-trappings">Trappings &amp; Equipment</li>
        <li data-tab="tab-description">Description</li>
        <li data-tab="tab-summary">Character Sheet</li>
      </ul>
    `;
  },

  renderContent(data = {}) {
    const speciesSelected =
      (data && (data.species_id || data.species || (data.profile && data.profile.species))) || '';
    const careerSelected =
      (data && (data.career_id || data.career || (data.profile && data.profile.career))) || '';

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
          <label for="cg-${trait}">${label} <small>(choose one)</small></label>

          <div class="cg-trait-control">
            <select id="cg-${trait}" class="cg-trait-select" data-trait="${trait}">
              <option value="">— Select —</option>
              ${options}
            </select>

            <span
              class="cg-trait-badge"
              id="cg-${trait}-badge"
              aria-label="${escape(label)} die"
              aria-live="polite"
            >–</span>
          </div>

          <div class="trait-adjusted" id="cg-${trait}-adjusted"></div>
        </div>
      `;
    }).join('');

    return `
      <div id="tab-details" class="tab-panel active">
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
                  <option value="Male"      ${data.gender === 'Male'      ? 'selected' : ''}>Male</option>
                  <option value="Female"    ${data.gender === 'Female'    ? 'selected' : ''}>Female</option>
                  <option value="Nonbinary" ${data.gender === 'Nonbinary' ? 'selected' : ''}>Nonbinary</option>
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

        </div>
      </div>

      <div id="tab-traits" class="tab-panel">
        <div class="cg-details-panel">

          <div class="cg-profile-box">
            <h3>Species and Career</h3>

            <label for="cg-species">Species</label>
            <select
              id="cg-species"
              class="cg-profile-select"
              data-selected="${escape(speciesSelected)}"
            ></select>

            <ul id="species-gifts" class="cg-gift-item"></ul>

            <label for="cg-career">Career</label>
            <div class="cg-trait-control cg-trait-control--profile">
              <select
                id="cg-career"
                class="cg-profile-select"
                data-selected="${escape(careerSelected)}"
              ></select>

              <span
                class="cg-trait-badge"
                id="cg-profile-trait_career-badge"
                aria-label="Career trait die"
                aria-live="polite"
              >–</span>
            </div>

            <div class="trait-adjusted" id="cg-profile-trait_career-note"></div>
            <div id="cg-extra-careers" class="cg-profile-grid"></div>
          </div>

          <div class="cg-traits-box">
            <h3>Traits</h3>
            <div class="cg-profile-grid">
              ${traitFields}
            </div>

            <!-- Read-only display of extra-career trait dice (populated by career/extra.js) -->
            <div id="cg-extra-career-traits" class="cg-extra-career-traits-box"></div>
          </div>

        </div>
      </div>

      <div id="tab-description" class="tab-panel">
        <div class="cg-details-panel">
          <div class="cg-text-box">
            <h3>Description &amp; Backstory</h3>
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
