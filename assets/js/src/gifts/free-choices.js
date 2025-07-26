// assets/js/src/gifts/free-choices.js

import API from './api.js';
import State from './state.js';
import TraitsService from '../core/traits/service.js';

const $ = window.jQuery;

console.log('🔥 [FreeChoices] Module loaded');

export default {
  /**
   * Initialize the three free-choice gift selectors,
   * filter by requirements, and wire up change events.
   */
  init() {
    console.group('[FreeChoices] 🔁 init() called');

    // Load any previously saved picks
    console.log('[FreeChoices] 🧠 Initializing saved state');
    State.init();

    // Fetch the full list of free-choice gift objects
    console.log('[FreeChoices] 📡 Fetching free-choice gifts via API');
    API.fetchFreeChoices(gifts => {
      console.log('[FreeChoices] ✅ Gift data received:', gifts);

      // 1) Merge all into State (so manifold & requirements are stored)
      State.setList(gifts);
      console.log('[FreeChoices] 📦 State.gifts →', State.gifts);
      console.log('[FreeChoices] 🗂️ State.selected →', State.selected);

      // --- Filtering logic ---
      console.log('[FreeChoices] 🔍 Filtering available gifts...');
      const suffixes = [
        '', 'two','three','four','five','six',
        'seven','eight','nine','ten','eleven',
        'twelve','thirteen','fourteen','fifteen',
        'sixteen','seventeen','eighteen','nineteen'
      ];
      const selectedIds = State.selected.map(String);
      const available = gifts.filter(g => {
        return suffixes.every(s => {
          const key = s ? `ct_gifts_requires_${s}` : 'ct_gifts_requires';
          const req = g[key];
          return !req || selectedIds.includes(String(req));
        });
      });
      console.log('[FreeChoices] ✅ Filtered gift list:', available);

      // --- Render dropdowns ---
      console.log('[FreeChoices] 🖌️ Rendering gift dropdowns...');
      const $wrap = $('#cg-free-choices').empty();
      for (let i = 0; i < 3; i++) {
        const selId = `cg-free-choice-${i}`;
        const prev  = State.selected[i] || '';
        const options = available.map(g => {
          const sel = g.id == prev ? ' selected' : '';
          return `<option value="${g.id}"${sel}>${g.name}</option>`;
        }).join('');

        console.log(`[FreeChoices] 🎨 Rendering dropdown ${i} → selected gift ID: ${prev}`);
        $wrap.append(`
          <select id="${selId}" data-index="${i}">
            <option value="">— Select Gift —</option>
            ${options}
          </select>
        `);
      }

      // --- Bind change handlers ---
      console.log('[FreeChoices] 🧷 Binding change handlers for dropdowns...');
      $(document)
        .off('change', '#cg-free-choices select')
        .on('change', '#cg-free-choices select', e => {
          const $sel = $(e.currentTarget);
          const idx  = $sel.data('index');
          const id   = $sel.val();

          console.log(`[FreeChoices] 📝 Dropdown change — index: ${idx}, selected ID: ${id}`);

          // Persist the choice
          State.set(idx, id);

          // Merge the newly selected gift object into State
          const chosen = gifts.find(g => String(g.id) === String(id));
          if (chosen) {
            console.log('[FreeChoices] 📥 Merging selected gift into state:', chosen);
            State.setList([chosen]);
          } else {
            console.log('[FreeChoices] ⚠️ Selected gift not found in list');
          }

          // Recalculate all trait boosts
          console.log('[FreeChoices] 🔁 Calling TraitsService.refreshAll() after selection');
          TraitsService.refreshAll();
        });

      console.groupEnd();
    });
  }
};
