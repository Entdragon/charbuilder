// assets/js/core/index.js

import $ from 'jquery';

// Core modules (folder-per-feature)
import FormBuilder from './formBuilder/form-builder.js';
import Traits      from './traits/traits.js';
import Species     from './species/species.js';
import Career      from './career/career.js';
import Skills      from './skills/skills.js';
import Summary     from './summary/summary.js';
import Main        from './main/main.js';

;(function($){
  $(function(){
    // Initialize each feature in sequence
    FormBuilder.init();  
    Traits.init();       
    Species.init();      
    Career.init();       
    Skills.init();       
    Summary.init();      
    Main.init();         
  });
})(jQuery);
