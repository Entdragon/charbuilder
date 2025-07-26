import MainAPI from './main/index.js';
import TraitsService from './traits/service.js'; // ✅ CORRECT relative path

// ✅ Expose TraitsService globally
window.TraitsService = TraitsService;

const Core = {
  init() {
    console.log('[Core] init() called');
    MainAPI.init();
  }
};

console.log('[Core] bundle loaded');

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', Core.init);
} else {
  Core.init();
}
