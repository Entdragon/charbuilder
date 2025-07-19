// assets/js/src/core/skills/events.js

import FormBuilderAPI from '../formBuilder';
import SkillsRender   from './render.js';

const $ = window.jQuery;

export default {
  bind() {
    // On tab click or species/career change, re-render
    $(document)
      .off('click', '#tab-skills, #cg-species, #cg-career')
      .on('click change', '#tab-skills, #cg-species, #cg-career', () => {
        SkillsRender.render();
      });

    // Handle mark‐button clicks
    $(document)
      .off('click', '.skill-mark-btn')
      .on('click', '.skill-mark-btn', function() {
        const skillId = String($(this).data('skill-id'));
        const mark    = parseInt($(this).data('mark'), 10);
        const data    = FormBuilderAPI.getData();

        data.skillMarks = data.skillMarks || {};
        data.skillMarks[skillId] = mark;

        // Re-render to update buttons, marks remaining & dice pools
        SkillsRender.render();
      });
  }
};
