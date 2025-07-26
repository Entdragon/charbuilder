import SpeciesService from '../species/api.js';
import CareerService  from '../career/api.js';
import TraitsService  from './service.js';

const $ = window.jQuery;
let bound = false;

function bind() {
  if (bound) {
    console.log('[TraitsEvents] ⚠️ Already bound, skipping re-bind.');
    return;
  }
  bound = true;

  console.group('[TraitsEvents] 🔗 Binding Trait Events');

  $(function () {
    console.log('[TraitsEvents] ✅ Page ready → Initial TraitsService.refreshAll()');
    TraitsService.refreshAll();
  });

  $(document)
    .off('change', '.cg-trait-select')
    .on('change', '.cg-trait-select', function () {
      const traitId = $(this).attr('id');
      const value = $(this).val();
      console.log(`[TraitsEvents] 🔄 Trait changed → ID: ${traitId}, New Value: ${value}`);
      TraitsService.refreshAll();
    });

  $(document)
    .off('cg-profile-updated.traits')
    .on('cg-profile-updated.traits', () => {
      console.log('[TraitsEvents] 🔔 Event: cg-profile-updated.traits → TraitsService.refreshAll()');
      TraitsService.refreshAll();
    });

  console.groupEnd();
}

const TraitsEvents = { bind };

export default TraitsEvents;
