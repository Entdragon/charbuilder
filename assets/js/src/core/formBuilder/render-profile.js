// assets/js/src/core/formBuilder/render-profile.js

/**
 * “Profile” tab & panel
 * Species, Career & Gifts all together
 */

export default {
  renderContent(data = {}) {
    console.group('[RenderProfile] renderContent() called');
    console.log('[RenderProfile] Incoming data:', data);
    console.log('[RenderProfile] species_id:', data.species_id || '');
    console.log('[RenderProfile] career_id:', data.career_id || '');

    const html = `
      <div id="tab-profile" class="tab-panel">

        <div class="cg-profile-box">
          <h3>Species and Career</h3>
          <label for="cg-species">Species</label>
          <select 
            id="cg-species" 
            class="cg-profile-select" 
            data-selected="${data.species_id || ''}"
          ></select>
          <ul id="species-gifts" class="cg-gift-item"></ul>

          <label for="cg-career">Career</label>
          <select 
            id="cg-career" 
            class="cg-profile-select" 
            data-selected="${data.career_id || ''}"
          ></select>
          <div class="trait-adjusted" id="cg-trait_career-adjusted"></div>
          <div id="cg-extra-careers" class="cg-profile-grid"></div>
        </div>

        <div class="cg-profile-box">
          <h3>Gifts</h3>
          <div class="cg-gift-label">Local Knowledge</div>
          <div id="cg-local-knowledge" class="cg-gift-item"></div>

          <div class="cg-gift-label">Language</div>
          <div id="cg-language" class="cg-gift-item"></div>

          <div class="cg-gift-label">Species Gifts</div>
          <ul id="species-gift-block" class="cg-gift-item"></ul>

          <div class="cg-gift-label">Career Gifts</div>
          <ul id="career-gifts" class="cg-gift-item"></ul>

          <div class="cg-gift-label">Chosen</div>
          <div id="cg-free-choices" class="cg-gift-item"></div>
        </div>

      </div>
    `;

    console.groupEnd();
    return html;
  }
};
