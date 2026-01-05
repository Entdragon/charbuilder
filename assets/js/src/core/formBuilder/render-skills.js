// assets/js/src/core/formBuilder/render-skills.js
//
// TAB-RESTRUCTURE HARDENING (Dec 2025):
// - FormBuilder should NOT pre-render/populate the Skills table rows.
// - This file now renders ONLY the Skills tab shell (panel + empty table).
// - The real Skills UI is populated by assets/js/src/core/skills/render.js
//   when (and only when) the Skills tab is active.
//
// This prevents Skills DOM from being built while the user is on Traits/Profile/Summary.

export default {
  renderContent(_data = {}) {
    return `
      <div id="tab-skills" class="tab-panel">
        <table id="skills-table" class="cg-skills-table">
          <thead></thead>
          <tbody></tbody>
        </table>
      </div>
    `;
  }
};
