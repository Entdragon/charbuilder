// assets/js/src/core/formBuilder/form-builder.js
const $ = window.jQuery;

import DetailsTabRenderer   from './render-details.js';
import ProfileTabRenderer   from './render-profile.js';
import SkillsTabRenderer    from './render-skills.js';
import SummaryTabRenderer   from './render-summary.js';

const FormBuilder = {
  buildForm(data = {}) {
    console.log('🛠️ FormBuilder.buildForm fired:', data);

    // 1) Build the HTML into a variable
    const html = `
<form id="cg-form">

  ${DetailsTabRenderer.renderTabs()}
  <div class="cg-tab-wrap">
    ${DetailsTabRenderer.renderContent(data)}
    ${ProfileTabRenderer.renderContent(data)}
    ${SkillsTabRenderer.renderContent(data)}
    ${SummaryTabRenderer.renderContent(data)}
  </div>

  <input type="hidden" id="cg-id"     value="${data.id || ''}" />
  <div class="cg-form-buttons">
    <button type="button" class="cg-save-button">💾 Save</button>
    <button type="button" class="cg-save-button cg-close-after-save">💾 Save & Close</button>
  </div>
</form>`;

    // 2) AFTER the form is injected into the DOM, give the browser a tick
    //    then read every <select> value out of the rendered HTML:
    setTimeout(() => {
      console.log('🔽 [buildForm] SELECT VALUES AFTER RENDER:', {
        will:            $('#cg-will').val(),
        speed:           $('#cg-speed').val(),
        body:            $('#cg-body').val(),
        mind:            $('#cg-mind').val(),
        trait_species:   $('#cg-trait_species').val(),
        trait_career:    $('#cg-trait_career').val(),
        profileSpecies:  $('#cg-species').val(),
        profileCareer:   $('#cg-career').val(),
      });
    }, 0);

    // 3) Return the HTML string to the caller
    return html;
  }
};

export default FormBuilder;
