// assets/js/src/core/formBuilder/render-profile.js
//
// “Gifts” + “Trappings” panels
// NOTE: Element IDs are intentionally unchanged so existing APIs keep working.

export default {
  renderContent(_data = {}) {
    return `
      <div id="tab-gifts" class="tab-panel">

        <div class="cg-profile-box">
          <h3>Gifts</h3>

          <div class="cg-gift-label">Local Knowledge</div>
          <div id="cg-local-knowledge" class="cg-gift-item"></div>

          <div style="display:flex; align-items:center; gap:10px; margin-top:1em;">
            <div class="cg-gift-label" style="margin-top:0;">Language</div>
            <div id="cg-language" class="cg-gift-item" style="margin:0;"></div>
          </div>

          <div class="cg-gift-label">Species Gifts</div>
          <ul id="species-gift-block" class="cg-gift-item"></ul>

          <div class="cg-gift-label">Career Gifts</div>
          <ul id="career-gifts" class="cg-gift-item"></ul>

          <div class="cg-gift-label">Chosen</div>
          <div id="cg-free-choices" class="cg-gift-item"></div>
        </div>

      </div>

      <div id="tab-trappings" class="tab-panel">
        <div class="cg-profile-box">
          <h3>Trappings &amp; Equipment</h3>
          <p><em>Coming soon.</em></p>
        </div>
      </div>
    `;
  }
};
