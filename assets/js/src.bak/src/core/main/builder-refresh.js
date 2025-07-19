// assets/js/src/core/main/builder-refresh.js

import FormBuilderAPI from '../formBuilder';
import TraitsAPI      from '../traits';
import SpeciesIndex   from '../species';
import CareerIndex    from '../career';
import GiftsAPI       from '../../gifts';
import SkillsAPI      from '../skills';
import SummaryAPI     from '../summary';

const $ = window.jQuery;

export default function refreshTab() {
  // gather current form values
  const flat = {};
  $('#cg-form input, #cg-form select, #cg-form textarea').each(function() {
    if (!this.id) return;
    flat[this.id.replace(/^cg-/, '')] = $(this).val();
  });
  FormBuilderAPI._data = { ...FormBuilderAPI.getData(), ...flat };

  // figure out which tab is active
  const tab = $('#cg-modal .cg-tabs li.active').data('tab');

  switch (tab) {
    case 'tab-traits':
      TraitsAPI.init();    // re-bind & enforce trait logic
      break;
    case 'tab-profile':
      SpeciesIndex.init();
      CareerIndex.init();
      GiftsAPI.init();
      break;
    case 'tab-skills':
      SkillsAPI.init();
      break;
    case 'tab-summary':
      SummaryAPI.init();
      break;
  }
}
