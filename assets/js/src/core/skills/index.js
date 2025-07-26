// assets/js/src/core/skills/index.js

import FormBuilderAPI from '../formBuilder';
import SkillsEvents   from './events.js';
import SkillsRender   from './render.js';

console.log('🔥 [SkillsIndex] Module loaded');

export default {
  init() {
    console.group('[SkillsIndex] 🚀 init() called');

    // 1) Logging the initial builder state
    console.log('[SkillsIndex] 🧾 Initial FormBuilderAPI._data:', FormBuilderAPI._data);

    // 2) Ensure we have the localized skills list
    if (!Array.isArray(FormBuilderAPI._data.skillsList)) {
      console.log('[SkillsIndex] 📥 No skillsList found, pulling from CG_SKILLS_LIST...');
      FormBuilderAPI._data.skillsList = window.CG_SKILLS_LIST || [];
    } else {
      console.log('[SkillsIndex] ✅ Existing skillsList found');
    }

    console.log('[SkillsIndex] 📦 skillsList:', FormBuilderAPI._data.skillsList);

    // 3) Initialize marks‐allocation storage if missing
    if (typeof FormBuilderAPI._data.skillMarks !== 'object') {
      console.warn('[SkillsIndex] ⚠️ skillMarks not found, initializing empty object');
      FormBuilderAPI._data.skillMarks = {};
    } else {
      console.log('[SkillsIndex] ✅ Existing skillMarks found');
    }

    console.log('[SkillsIndex] 🧮 skillMarks:', FormBuilderAPI._data.skillMarks);

    // 4) Wire up click/change handlers for tab, species, career, and mark buttons
    console.log('[SkillsIndex] 🧷 Binding event handlers via SkillsEvents.bind()');
    SkillsEvents.bind();

    // 5) Initial render of the full skills table (headers, buttons, pools, marks remaining)
    console.log('[SkillsIndex] 🖼️ Rendering UI via SkillsRender.render()');
    SkillsRender.render();

    console.groupEnd();
  }
};
