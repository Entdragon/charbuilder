// assets/js/src/core/utils/bind-once.js
const $ = window.jQuery;

/**
 * Bind an event with a required namespace and prevent duplicate handlers.
 * Example:
 *   bindOnce(document, 'click', '#cg-open', onOpen);              // => click.cg
 *   bindOnce(document, 'change.cg', '#cg-select', onChange);      // already namespaced OK
 */
export default function bindOnce(root, eventName, selector, handler, ns = 'cg') {
  if (!eventName.includes('.')) eventName = `${eventName}.${ns}`;
  $(root).off(eventName, selector).on(eventName, selector, handler);
}

/**
 * Idempotent init helper to ensure a module binds only once.
 * Usage:
 *   const init = makeInitGuard('builder-events');
 *   export default function bindUIEvents() { if (!init()) return; ... }
 */
export function makeInitGuard(key) {
  const w = window;
  w.__CG_INITED__ = w.__CG_INITED__ || new Set();
  return function guard() {
    if (w.__CG_INITED__.has(key)) return false;
    w.__CG_INITED__.add(key);
    return true;
  };
}
