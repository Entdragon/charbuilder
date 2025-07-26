import DetailsTabRenderer   from './render-details.js';
import ProfileTabRenderer   from './render-profile.js';
import SkillsTabRenderer    from './render-skills.js';
import SummaryTabRenderer   from './render-summary.js';
import TraitsService        from '../traits/service.js';

const $ = window.jQuery;

const FormBuilder = {
  buildForm(data = {}) {
    console.groupCollapsed('🛠️ [FormBuilder] buildForm()');
    console.log('📦 Incoming data payload:', JSON.stringify(data, null, 2));

    // 1) Build the HTML
    console.log('🧱 [FormBuilder] Rendering tab panels...');
    const html = `
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

    console.log('✅ [FormBuilder] HTML markup generated.');
    console.groupEnd();

    // 2) Delay to allow DOM rendering before syncing UI state
    setTimeout(() => {
      console.group('⏳ [FormBuilder] Post-render UI sync');

      const currentValues = {
        will:            $('#cg-will').val(),
        speed:           $('#cg-speed').val(),
        body:            $('#cg-body').val(),
        mind:            $('#cg-mind').val(),
        trait_species:   $('#cg-trait_species').val(),
        trait_career:    $('#cg-trait_career').val(),
        profileSpecies:  $('#cg-species').val(),
        profileCareer:   $('#cg-career').val(),
      };

      console.table(currentValues);

      if ($('#cg-id').val()) {
        console.log('[FormBuilder] 🆔 Character ID:', $('#cg-id').val());
      } else {
        console.log('[FormBuilder] ℹ️ No character ID found (new character)');
      }

      console.log('🔁 [FormBuilder] Calling TraitsService.refreshAll()');
      TraitsService.refreshAll();

      console.log('✅ [FormBuilder] Traits UI synced and boosted values rendered');
      console.groupEnd();
    }, 0);

    // 3) Return HTML string
    console.log('🚀 [FormBuilder] Returning form HTML');
    return html;
  }
};

export default FormBuilder;
