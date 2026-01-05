import TraitsAPI      from '../traits';
import SpeciesIndex   from '../species';
import CareerIndex    from '../career';
import GiftsAPI       from '../gifts';
import SkillsAPI      from '../skills';
import SummaryAPI     from '../summary';

const $ = window.jQuery;

// Only re-init the active tab’s logic (no DOM→state merging here)
export default function refreshTab() {
  const tab = String($('#cg-modal .cg-tabs li.active').data('tab') || '');

  switch (tab) {
    case 'tab-details':
      // Pure inputs; no module init required.
      break;

    case 'tab-traits':
      // Traits + Species + Career live together now.
      // IMPORTANT ORDER:
      // Career init (and career/extra.js render) creates the "boost target" radios.
      // TraitsService reads those radios; if we run Traits first it defaults to 'main'.
      SpeciesIndex.init();
      CareerIndex.init();

      // Next-tick Traits init so Career/Extra UI has a chance to render first.
      setTimeout(() => {
        try { TraitsAPI.init(); } catch (e) { console.error('[builder-refresh] TraitsAPI.init failed', e); }
      }, 0);

      break;

    case 'tab-gifts':
      GiftsAPI.init();
      break;

    case 'tab-skills':
      SkillsAPI.init();
      break;

    case 'tab-trappings':
      // Placeholder tab for now.
      break;

    case 'tab-description':
      // Pure inputs; no module init required.
      break;

    case 'tab-summary':
      SummaryAPI.init();
      break;
  }
}
