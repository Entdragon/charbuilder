import MainAPI from './main';

const Core = {
  init() {
    console.log('[Core] init() called');           // ← you should see this
    MainAPI.init();
  }
};

// log when the bundle loads
console.log('[Core] bundle loaded');

// fire when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', Core.init);
} else {
  Core.init();
}

import SkillsModule from './skills/index.js';
// … other imports …

export default function initCore() {
  // … existing binds …
  SkillsModule.init();
  // … builder‐events …
}
