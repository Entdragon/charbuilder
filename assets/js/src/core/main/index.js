// assets/js/src/core/main/index.js

import $               from 'jquery';
import SkillsModule    from '../skills/index.js';
import FormBuilderAPI  from '../formBuilder/index.js';
import BuilderUI       from './builder-ui.js';

function bindUIEvents() {
  // initialize skills panel
  SkillsModule.init();

  // “New Character” trigger
  $(document).on('click', '.cg-open-builder', (e) => {
    e.preventDefault();
    BuilderUI.openBuilder({ isNew: true, payload: {} });
  });

  // “Edit Character” triggers
  $(document).on('click', '.cg-edit-character', (e) => {
    e.preventDefault();
    const id = $(e.currentTarget).data('character-id');
    FormBuilderAPI
      .fetchCharacter(id)
      .done(character => {
        BuilderUI.openBuilder({ isNew: false, payload: character });
      });
  });
}

export default {
  init() {
    console.log('[MainAPI] init() called');
    bindUIEvents();
  }
};
