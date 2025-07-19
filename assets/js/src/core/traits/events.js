// binds DOM events for traits

import TraitsService from './service.js';

const $ = window.jQuery;
let bound = false;

export default {
  bind() {
    if (bound) return;
    bound = true;

    // on initial page load (or panel insert)
    $(function(){
      TraitsService.refreshAll();
    });

    // re-apply limits & labels on any dropdown change
    $(document)
      .off('change', '.cg-trait-select')
      .on('change', '.cg-trait-select', () => {
        TraitsService.refreshAll();
      });
  }
};
