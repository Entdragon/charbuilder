// assets/js/src/gifts/index.js

// correct relative paths (all these files live in the same folder)
import './gift-definitions.js';
import './gift-utils.js';
import LocalKnowledge      from './localKnowledge.js';
import Language            from './language.js';
import FreeChoices         from './freeChoices.js';
import SpeciesCareerHooks  from './speciesCareerHooks.js';
import ExtraCareer         from './extraCareer.js';
import Boost               from './boost.js';

import $ from 'jquery';

// Initialize each module in turn:
$(function(){
  LocalKnowledge.init();
  Language.init();
  FreeChoices.init();
  SpeciesCareerHooks.init();
  ExtraCareer.init();
  Boost.init();
});
