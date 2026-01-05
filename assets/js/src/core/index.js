import MainAPI from './main';
import SkillsModule from './skills/index.js';
import SpeciesAPI from './species/api.js';

function cgGlobal() {
  // Works in browsers and avoids ReferenceError in other contexts
  if (typeof globalThis !== 'undefined') return globalThis;
  if (typeof window !== 'undefined') return window;
  return {};
}

const G = cgGlobal();

const LOADS_KEY = '__CG_CORE_BUNDLE_LOADS__';
const BOOT_KEY = '__CG_CORE_BOOTED__';
const INITCORE_KEY = '__CG_INITCORE_RAN__';

// Track bundle loads to detect double-enqueue quickly
G[LOADS_KEY] = (G[LOADS_KEY] || 0) + 1;
console.log(`[Core] bundle loaded (#${G[LOADS_KEY]})`);

const Core = {
  init(reason = 'auto') {
    const g = cgGlobal();
    if (g[BOOT_KEY]) return;              // hard guard: only once per page
    g[BOOT_KEY] = true;

    console.log('[Core] init() called');

    try {
      MainAPI.init();
    } catch (err) {
      console.error('[Core] MainAPI.init() failed', err);
      throw err;
    }
  }
};

// Boot once when DOM is ready (safe even if this file is evaluated twice)
function bootOnce() {
  Core.init('domready');
}

if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootOnce, { once: true });
  } else {
    bootOnce();
  }
}

/**
 * Legacy / secondary entrypoint.
 * Safe to call repeatedly; will only run once.
 */
export default function initCore() {
  const g = cgGlobal();
  if (g[INITCORE_KEY]) return;
  g[INITCORE_KEY] = true;

  // Ensure Core/MainAPI is booted
  Core.init('initCore');

  // Any one-time binds / module inits that should only occur once
  try { SkillsModule.init(); } catch (err) { console.error('[Core] SkillsModule.init() failed', err); }
}

// Debug handles (helpful in Console)
if (typeof window !== 'undefined') {
  window.SpeciesAPI = SpeciesAPI;
  window.CG_Core = Core;
  window.CG_initCore = initCore;
}
