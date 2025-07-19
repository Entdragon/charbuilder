import bindUIEvents from './builder-events.js';

export default {
  init() {
    console.log('[Main] init() called');           // ← and then this
    bindUIEvents();
  }
};
