// assets/js/src/core/formBuilder/render-summary.js

/**
 * “Summary” tab & panel skeleton
 */
export default {
  renderContent(data = {}) {
    return `
      <div id="tab-summary" class="tab-panel">
        <div class="summary-header">
          <button id="cg-export-pdf" type="button" class="button primary">
            🖨️ Export to PDF
          </button>
        </div>
        <div id="cg-summary-sheet"></div>
      </div>
    `;
  }
};
