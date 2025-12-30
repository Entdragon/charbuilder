import TraitsAPI      from '../traits';
import SpeciesIndex   from '../species';
import CareerIndex    from '../career';
import GiftsAPI       from '../gifts';
import SkillsAPI      from '../skills';
import SummaryAPI     from '../summary';

const $ = window.jQuery;

// Only re-init the active tab’s logic (no DOM→state merging here)
export default function refreshTab() {
  const tab = $('#cg-modal .cg-tabs li.active').data('tab');

  switch (tab) {
    case 'tab-traits':
      TraitsAPI.init();
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
