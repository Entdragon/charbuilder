// assets/js/src/core/skills/index.js

import FormBuilderAPI from '../formBuilder';
import SkillsEvents   from './events.js';
import SkillsRender   from './render.js';

export default {
  init() {
    // 1) Logging the initial builder state
    console.log('[SkillsIndex] init — builder state:', FormBuilderAPI._data);

    // 2) Ensure we have the localized skills list
    if (!Array.isArray(FormBuilderAPI._data.skillsList)) {
      FormBuilderAPI._data.skillsList = window.CG_SKILLS_LIST || [];
    }

    // 3) Initialize marks‐allocation storage if missing
    if (typeof FormBuilderAPI._data.skillMarks !== 'object') {
      FormBuilderAPI._data.skillMarks = {};
    }

    // 4) Wire up click/change handlers for tab, species, career, and mark buttons
    SkillsEvents.bind();

    // 5) Initial render of the full skills table (headers, buttons, pools, marks remaining)
    SkillsRender.render();
  }
};
