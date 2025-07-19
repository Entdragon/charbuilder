// assets/js/src/core/main/index.js

import bindUIEvents from './builder-events.js';

const MainAPI = {
  init() {
    console.log('[MainAPI] init() called');
    // now bind all your UI events
    bindUIEvents();
  }
};

export default MainAPI;
