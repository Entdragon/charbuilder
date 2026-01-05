// assets/js/src/utils/logger.js
// Simple, reliable logger used across the Character Builder bundle.
//
// Exports:
//   - named:  log, warn, error
//   - default: { log, warn, error }
//
// Behavior:
//   - log() is debug-gated (to reduce console noise)
//   - warn()/error() always print
//
// Debug enable (any of these):
//   window.CG_DEBUG === true
//   window.cgDebug === true
//   localStorage["cg_debug"] in: "1", "true", "yes", "on"

function isDebugEnabled() {
  try {
    if (typeof window !== 'undefined') {
      if (window.CG_DEBUG === true) return true;
      if (window.cgDebug === true) return true;

      const v = window.localStorage ? window.localStorage.getItem('cg_debug') : null;
      if (!v) return false;
      return ['1', 'true', 'yes', 'on'].includes(String(v).toLowerCase());
    }
  } catch (_) { /* ignore */ }
  return false;
}

function prefix() {
  return '[CG]';
}

function log(...args) {
  try {
    if (!isDebugEnabled()) return;
    // eslint-disable-next-line no-console
    console.log(prefix(), ...args);
  } catch (_) { /* ignore */ }
}

function warn(...args) {
  try {
    // eslint-disable-next-line no-console
    console.warn(prefix(), ...args);
  } catch (_) { /* ignore */ }
}

function error(...args) {
  try {
    // eslint-disable-next-line no-console
    console.error(prefix(), ...args);
  } catch (_) { /* ignore */ }
}

const Logger = { log, warn, error };

// Useful globals for debugging (non-breaking)
if (typeof window !== 'undefined') {
  window.CG_LOGGER = Logger;
  window.cgLogger = Logger;
}

export { log, warn, error };
export default Logger;
