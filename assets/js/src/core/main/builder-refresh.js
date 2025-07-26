import TraitsAPI    from '../traits';
import SpeciesIndex from '../species';
import CareerIndex  from '../career';
import GiftsAPI     from '../../gifts';
import SkillsAPI    from '../skills';
import SummaryAPI   from '../summary';

const $ = window.jQuery;

// Only re-init the active tab’s logic (no DOM→state merging here)
export default function refreshTab() {
  console.group('[BuilderRefresh] 🔁 refreshTab()');

  const tab = $('#cg-modal .cg-tabs li.active').data('tab');
  console.log(`[BuilderRefresh] 📌 Active tab detected: ${tab}`);

  switch (tab) {
    case 'tab-traits':
      console.log('[BuilderRefresh] 🔧 Initializing Traits tab...');
      TraitsAPI.init();
      console.log('[BuilderRefresh] ✅ TraitsAPI.init() complete');
      break;

    case 'tab-profile':
      console.log('[BuilderRefresh] 🔧 Initializing Profile tab...');

      console.log('[BuilderRefresh] ▶ SpeciesIndex.init()');
      SpeciesIndex.init();
      console.log('[BuilderRefresh] ✅ SpeciesIndex initialized');

      console.log('[BuilderRefresh] ▶ CareerIndex.init()');
      CareerIndex.init();
      console.log('[BuilderRefresh] ✅ CareerIndex initialized');

      console.log('[BuilderRefresh] ▶ GiftsAPI.init()');
      GiftsAPI.init();
      console.log('[BuilderRefresh] ✅ GiftsAPI initialized');

      break;

    case 'tab-skills':
      console.log('[BuilderRefresh] 🔧 Initializing Skills tab...');
      SkillsAPI.init();
      console.log('[BuilderRefresh] ✅ SkillsAPI.init() complete');
      break;

    case 'tab-summary':
      console.log('[BuilderRefresh] 🔧 Initializing Summary tab...');
      SummaryAPI.init();
      console.log('[BuilderRefresh] ✅ SummaryAPI.init() complete');
      break;

    default:
      console.warn(`[BuilderRefresh] ⚠️ Unrecognized tab: ${tab}`);
      break;
  }

  console.groupEnd();
}
