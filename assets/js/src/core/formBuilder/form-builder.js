;(function($){
  window.CG_FormBuilder = {
    capitalize: str => str.charAt(0).toUpperCase() + str.slice(1),
    safe: str => (str || '')
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#039;'),

    buildForm(data = {}) {
      const cap = this.capitalize;
      const TRAITS = window.CG_Traits.TRAITS;
      const DICE = CG_Traits.DICE_TYPES;

      return `
<form id="cg-form">

  <ul class="cg-tabs">
    <li data-tab="tab-traits" class="active">Details & Traits</li>
    <li data-tab="tab-profile">Profile<br>(Species/Career/Gifts)</li>
    <li data-tab="tab-skills">Skills</li>
    <li data-tab="tab-summary">Summary</li>
  </ul>

  <div class="cg-tab-wrap">

    <!-- DETAILS & TRAITS -->
    <div id="tab-traits" class="tab-panel active">
      <div class="cg-nav-buttons"><button type="button" class="cg-nav-next">Next →</button></div>
      <div class="cg-details-panel">

        <!-- DETAILS BOX -->
        <div class="cg-details-box">
          <h3>Details</h3>
          <label>Name</label><input type="text" id="cg-name" value="${this.safe(data.name)}" required />
          <label>Age</label><input type="text" id="cg-age" value="${this.safe(data.age)}" required />
          <label>Gender</label>
          <select id="cg-gender">
            <option value="">&mdash; Select &mdash;</option>
            <option value="Male" ${data.gender==='Male'?'selected':''}>Male</option>
            <option value="Female" ${data.gender==='Female'?'selected':''}>Female</option>
            <option value="Nonbinary" ${data.gender==='Nonbinary'?'selected':''}>Nonbinary</option>
          </select>
          <label>Motto</label><input type="text" id="cg-motto" value="${this.safe(data.motto)}" />
          <label>Goal 1</label><input type="text" id="cg-goal1" value="${this.safe(data.goal1)}" />
          <label>Goal 2</label><input type="text" id="cg-goal2" value="${this.safe(data.goal2)}" />
          <label>Goal 3</label><input type="text" id="cg-goal3" value="${this.safe(data.goal3)}" />
        </div>

        <!-- TRAITS BOX -->
        <div class="cg-traits-box">
          <h3>Traits</h3>
          <div class="cg-traits">
            ${TRAITS.map(trait => {
              const val = data[trait] || '';
              const opts = DICE.map(d => `<option value="${d}"${val===d?' selected':''}>${d}</option>`).join('');
              let label = cap(trait);
              if (trait==='trait_species') label='Species';
              if (trait==='trait_career')  label='Career';
              return `
                <div class="cg-trait">
                  <label>${label} <small>(choose one)</small></label>
                  <select id="cg-${trait}" class="cg-trait-select">
                    <option value="">&mdash; Select &mdash;</option>
                    ${opts}
                  </select>
                  <div class="trait-adjusted" id="cg-${trait}-adjusted"></div>
                </div>`;
            }).join('')}
          </div>
        </div>

        <!-- DESCRIPTION BOX -->
        <div class="cg-text-box">
          <h3>Description & Backstory</h3>
          <label>Description</label>
          <textarea id="cg-description">${this.safe(data.description)}</textarea>
          <label>Backstory</label>
          <textarea id="cg-backstory">${this.safe(data.backstory)}</textarea>
        </div>
      </div>
      <div class="cg-nav-buttons"><button type="button" class="cg-nav-next">Next →</button></div>
    </div>

    <!-- PROFILE TAB -->
    <div id="tab-profile" class="tab-panel">
      <div class="cg-nav-buttons">
        <button type="button" class="cg-nav-prev">← Previous</button>
        <button type="button" class="cg-nav-next">Next →</button>
      </div>

      <!-- BOX: Species and Careers -->
      <div class="cg-profile-box">
        <h3>Species and Careers</h3>
        <label for="cg-species">Species</label>
        <select id="cg-species" class="cg-profile-select"></select>
        <ul id="species-gifts" class="cg-gift-item"></ul>

        <label for="cg-career">Career</label>
        <select id="cg-career" class="cg-profile-select"></select>
        <div class="trait-adjusted" id="cg-trait_career-adjusted"></div>

        <div id="cg-extra-careers" class="cg-profile-grid"></div>
      </div>

      <!-- BOX: Gifts -->
      <div class="cg-profile-box">
        <h3>Gifts</h3>

        <div class="cg-gift-label">Local Knowledge</div>
        <div id="cg-local-knowledge" class="cg-gift-item"></div>

        <div class="cg-gift-label">Language</div>
        <div id="cg-language" class="cg-gift-item"></div>

        <div class="cg-gift-label">Species Gifts</div>
        <ul id="species-gift-block" class="cg-gift-item"></ul>

        <div class="cg-gift-label">Career</div>
        <ul id="career-gifts" class="cg-gift-item"></ul>

        <div class="cg-gift-label">Chosen</div>
        <div id="cg-free-choices" class="cg-gift-item"></div>
      </div>

      <div class="cg-nav-buttons">
        <button type="button" class="cg-nav-prev">← Previous</button>
        <button type="button" class="cg-nav-next">Next →</button>
      </div>
    </div>

    <!-- SKILLS TAB -->
    <div id="tab-skills" class="tab-panel">
      <div class="cg-nav-buttons">
        <button type="button" class="cg-nav-prev">← Previous</button>
        <button type="button" class="cg-nav-next">Next →</button>
      </div>
      <table id="skills-table" class="cg-skills-table">
        <thead><tr></tr></thead>
        <tbody></tbody>
      </table>
    </div>

    <!-- SUMMARY TAB -->
    <div id="tab-summary" class="tab-panel">
      <div class="cg-nav-buttons">
        <button type="button" class="cg-nav-prev">← Previous</button>
      </div>
      <div id="cg-summary-sheet">Summary will display here.</div>
      <button id="cg-export-pdf" type="button">Export to PDF</button>
    </div>

  </div>

  <input type="hidden" id="cg-id" value="${this.safe(data.id)}" />
  <div class="cg-form-buttons">
    <button type="button" class="cg-save-button">💾 Save</button>
    <button type="button" class="cg-save-button cg-close-after-save">💾 Save & Close</button>
  </div>
</form>`;
    }
  };
})(jQuery);
