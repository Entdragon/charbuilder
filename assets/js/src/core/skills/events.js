// assets/js/src/core/skills/events.js

import FormBuilderAPI from '../formBuilder';
import SkillsRender   from './render.js';

const $ = window.jQuery;

console.log('🔥 [SkillsEvents] Module loaded');

export default {
  bind() {
    console.group('[SkillsEvents] 🧷 bind() called');

    // On tab click or species/career change, re-render
    console.log('[SkillsEvents] 📎 Binding click/change on #tab-skills, #cg-species, #cg-career');
    $(document)
      .off('click', '#tab-skills, #cg-species, #cg-career')
      .on('click change', '#tab-skills, #cg-species, #cg-career', () => {
        console.log('[SkillsEvents] 🔄 Tab or selector change detected — calling SkillsRender.render()');
        SkillsRender.render();
      });

    // Handle mark‐button clicks
    console.log('[SkillsEvents] 📎 Binding click on .skill-mark-btn');
    $(document)
      .off('click', '.skill-mark-btn')
      .on('click', '.skill-mark-btn', function () {
        const skillId = String($(this).data('skill-id'));
        const mark = parseInt($(this).data('mark'), 10);
        const data = FormBuilderAPI.getData();

        console.log(`[SkillsEvents] 🎯 Clicked skill mark button → Skill ID: ${skillId}, Mark: ${mark}`);
        console.log('[SkillsEvents] 📦 Current skillMarks before update:', data.skillMarks);

        data.skillMarks = data.skillMarks || {};
        data.skillMarks[skillId] = mark;

        console.log('[SkillsEvents] ✅ Updated skillMarks:', data.skillMarks);

        // Re-render to update buttons, marks remaining & dice pools
        console.log('[SkillsEvents] 🔁 Calling SkillsRender.render() after mark update');
        SkillsRender.render();
      });

    console.groupEnd();
  }
};
