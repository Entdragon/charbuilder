// career/index.js
import CareerAPI      from './api.js';
import CareerEvents   from './events.js';
import ExtraCareer    from './extra.js';
import FormBuilderAPI from '../formBuilder';

const $ = window.jQuery;

export default {
  init() {
    console.log('[CareerIndex] init()');

    // bind onChange once
    CareerEvents.bind();

    // grab last‐saved career
    const data = FormBuilderAPI.getData();

    // always rebuild the dropdown then reapply selection
    CareerAPI.loadList(() => {
      if (data.career) {
        $('#cg-career')
          .val(data.career)
          .trigger('change');
      }
    });

    // rebuild any extra‐career slots
   // ExtraCareer.init();
  }
};
