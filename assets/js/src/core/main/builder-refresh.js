import TraitsAPI      from '../traits';
import SpeciesIndex   from '../species';
import CareerIndex    from '../career';
import GiftsAPI       from '../gifts';
import SkillsAPI      from '../skills';
import SummaryAPI     from '../summary';
import ExperienceAPI  from '../experience';
import BattleAPI      from '../battle/index.js';
import TrappingsAPI   from '../trappings/index.js';

const $ = window.jQuery;

// Only re-init the active tab's logic (no DOM→state merging here)
export default function refreshTab() {
  const tab = String($('#cg-modal .cg-tabs li.active').data('tab') || '');

  switch (tab) {
    case 'tab-details':
      ExperienceAPI.initWidget();
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
      ExperienceAPI.renderXpGifts();
      break;

    case 'tab-skills':
      SkillsAPI.init();
      break;

    case 'tab-trappings': {
      BattleAPI.init();
      try { TrappingsAPI._renderAll(); } catch (_) {}

      // Recovery: if career/species trappings are missing (e.g. timing race
      // where events fired before listeners were bound), fill them now.
      try {
        const FB   = window.CG_FormBuilderAPI || window.FormBuilderAPI;
        const data = FB?._data || {};
        const list = Array.isArray(data.trappings_list) ? data.trappings_list : [];

        const careerId = parseInt(data.career_id || data.career || '0', 10);
        if (careerId > 0 && !list.some(t => t.source === 'career')) {
          TrappingsAPI._fillCareerTrappings(careerId);
        }

        const hasSpeciesWeapons = list.some(t => t.source === 'species');
        if (!hasSpeciesWeapons) {
          TrappingsAPI._fillSpeciesWeapons();
        }
      } catch (_) {}

      try {
        if (TrappingsAPI._catalogCache) {
          TrappingsAPI._renderCatalog();
        } else {
          TrappingsAPI._ensureCatalog().then(() => TrappingsAPI._renderCatalog());
        }
      } catch (_) {}
      break;
    }

    case 'tab-description':
      // Pure inputs; no module init required.
      break;

    case 'tab-summary':
      SummaryAPI.init();
      break;
  }
}
