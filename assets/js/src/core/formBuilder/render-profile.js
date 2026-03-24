// assets/js/src/core/formBuilder/render-profile.js
//
// "Gifts" + "Trappings" panels
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
          <div id="cg-language-effect" class="cg-gift-item"></div>

          <div class="cg-gift-label" style="margin-top:1em;">Combat Save</div>
          <div id="cg-combat-save" class="cg-gift-item"></div>

          <div class="cg-gift-label">Personality</div>
          <div id="cg-personality" class="cg-gift-item"></div>

          <div class="cg-gift-label">Species Gifts</div>
          <ul id="species-gift-block" class="cg-gift-item"></ul>

          <div class="cg-gift-label">Career Gifts</div>
          <ul id="career-gifts" class="cg-gift-item"></ul>

          <div class="cg-gift-label">Gifts</div>
          <div id="cg-free-choices" class="cg-gift-item"></div>

          <div id="cg-xp-gifts" class="cg-gift-item cg-xp-gifts-container"></div>
        </div>

      </div>

      <div id="tab-trappings" class="tab-panel">
        <div class="cg-profile-box cg-battle-box">
          <h3>Battle &amp; Equipment</h3>
          <div id="cg-battle-panel">
            <p class="cg-battle-loading"><em>Loading battle array…</em></p>
          </div>
        </div>

        <div class="cg-profile-box cg-trappings-box">
          <h3>Trappings</h3>
          <div id="cg-trappings-panel">
            <p class="cg-battle-loading"><em>Loading trappings…</em></p>
          </div>
        </div>

        <div class="cg-profile-box cg-money-box">
          <h3>Money</h3>
          <div id="cg-money-panel">
            <p class="cg-battle-loading"><em>Loading currencies…</em></p>
          </div>
        </div>

        <div class="cg-profile-box cg-catalog-box">
          <h3>Equipment Shop</h3>
          <p class="cg-catalog-intro">Purchase additional equipment from the catalog below. Career and gift trappings above are free — only shop purchases deduct money.</p>
          <div class="cg-catalog-controls">
            <input type="text" id="cg-equip-search" class="cg-catalog-search"
              placeholder="Search items…" autocomplete="off" />
            <select id="cg-equip-filter-kind" class="cg-free-select cg-catalog-filter">
              <option value="">All types</option>
              <option value="equipment">Equipment only</option>
              <option value="weapon">Weapons only</option>
            </select>
            <button type="button" id="cg-equip-browse-btn" class="cg-btn cg-btn-gold">Browse All Items</button>
          </div>
          <div id="cg-equip-catalog-panel">
            <p class="cg-catalog-hint"><em>Search above or click "Browse All Items" to see the full catalog.</em></p>
          </div>
        </div>
      </div>
    `;
  }
};
