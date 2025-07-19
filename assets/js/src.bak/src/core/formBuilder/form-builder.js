// assets/js/src/core/formBuilder/form-builder.js

import DetailsTabRenderer   from './render-details.js';
import ProfileTabRenderer   from './render-profile.js';
import SkillsTabRenderer    from './render-skills.js';
import SummaryTabRenderer   from './render-summary.js';

const FormBuilder = {
  buildForm(data = {}) {
    console.log('🛠️ FormBuilder.buildForm fired:', data);

    return `
<form id="cg-form">

  ${DetailsTabRenderer.renderTabs()}
  <div class="cg-tab-wrap">
    ${DetailsTabRenderer.renderContent(data)}
    ${ProfileTabRenderer.renderContent(data)}
    ${SkillsTabRenderer.renderContent(data)}
    ${SummaryTabRenderer.renderContent(data)}
  </div>

  <input type="hidden" id="cg-id" value="${data.id || ''}" />
  <div class="cg-form-buttons">
    <button type="button" class="cg-save-button">💾 Save</button>
    <button type="button" class="cg-save-button cg-close-after-save">💾 Save & Close</button>
  </div>
</form>`;
  }
};

export default FormBuilder;