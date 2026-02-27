// assets/js/src/core/experience/render.js
// Static skeleton for the Experience tab.
// The XP module (index.js) populates dynamic content on init().

export default {
  renderContent() {
    return `
      <div id="tab-experience" class="tab-panel">

        <div class="xp-balance-panel">
          <div class="xp-earned-wrap">
            <label class="xp-label" for="xp-earned">Total XP Earned</label>
            <input type="number" id="xp-earned" class="xp-earned-input" min="0" value="0" placeholder="0">
          </div>
          <div class="xp-balance-grid">
            <div class="xp-balance-cell">
              <span class="xp-balance-label">Spent on Marks</span>
              <span class="xp-balance-val" id="xp-marks-cost">0</span>
            </div>
            <div class="xp-balance-cell">
              <span class="xp-balance-label">Spent on Gifts</span>
              <span class="xp-balance-val" id="xp-gifts-cost">0</span>
            </div>
            <div class="xp-balance-cell xp-balance-cell--total">
              <span class="xp-balance-label">Total Spent</span>
              <span class="xp-balance-val" id="xp-total-cost">0</span>
            </div>
            <div class="xp-balance-cell xp-balance-cell--avail" id="xp-avail-cell">
              <span class="xp-balance-label">Available</span>
              <span class="xp-balance-val" id="xp-available">0</span>
            </div>
          </div>
        </div>

        <div class="xp-section">
          <div class="xp-section-header">
            <h4>Extra Skill Marks</h4>
            <span class="xp-cost-badge">4 XP each</span>
          </div>
          <p class="xp-section-note">
            Marks bought here stack with your starting marks. Total marks per skill cap at 3 (d4 → d6 → d8).
          </p>
          <div id="xp-marks-list" class="xp-marks-list"></div>
          <div class="xp-add-row">
            <select id="xp-add-skill-select" class="xp-add-select">
              <option value="">— Choose a skill to mark —</option>
            </select>
            <button type="button" id="xp-add-skill-btn" class="btn btn-outline xp-add-btn" disabled>
              + Add Mark
            </button>
          </div>
        </div>

        <div class="xp-section">
          <div class="xp-section-header">
            <h4>Experience Gifts</h4>
            <span class="xp-cost-badge">10 XP each</span>
          </div>
          <p class="xp-section-note">
            These gifts are purchased post-character-creation with earned XP.
          </p>
          <div id="xp-gifts-list" class="xp-gifts-list"></div>
          <div class="xp-add-row">
            <select id="xp-add-gift-select" class="xp-add-select">
              <option value="">— Choose a gift —</option>
            </select>
            <button type="button" id="xp-add-gift-btn" class="btn btn-outline xp-add-btn" disabled>
              + Add Gift
            </button>
          </div>
        </div>

      </div>
    `;
  }
};
