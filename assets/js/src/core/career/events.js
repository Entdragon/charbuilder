// assets/js/src/core/career/events.js
// One-time, namespaced bindings for Career select.

const $ = window.jQuery;
let _bound = false;

export default function bindCareerEvents() {
  if (_bound) return;
  _bound = true;

  $(document)
    .off('change.cg', '#cg-career')
    .on('change.cg', '#cg-career', e => {
      const val = (e.currentTarget && e.currentTarget.value) || '';
      console.log('[CareerEvents] selected career →', val);
      $(document).trigger('cg:career:changed', [{ id: val }]);
    });
}
