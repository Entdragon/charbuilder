// assets/js/src/gifts/free-choices.js

import API from './api.js';
import State from './state.js';
import TraitsService from '../core/traits/service.js';

const $ = window.jQuery;

export default {
  /**
   * Initialize the three free-choice gift selectors,
   * filter by requirements, and wire up change events.
   */
  init() {
    // Load any previously saved picks
    State.init();

    // Fetch the full list of free-choice gift objects
    API.fetchFreeChoices(gifts => {
      // 1) Merge all into State (so manifold & requirements are stored)
      State.setList(gifts);
      console.log('[FreeChoices] State.gifts →', State.gifts);
      console.log('[FreeChoices] State.selected →', State.selected);

      // --- Filtering logic (unchanged) ---
      const suffixes = [
        '', 'two','three','four','five','six',
        'seven','eight','nine','ten','eleven',
        'twelve','thirteen','fourteen','fifteen',
        'sixteen','seventeen','eighteen','nineteen'
      ];
      const selectedIds = State.selected.map(String);
      const available = gifts.filter(g => {
        return suffixes.every(s => {
          const key = s
            ? `ct_gifts_requires_${s}`
            : 'ct_gifts_requires';
          const req = g[key];
          return !req || selectedIds.includes(String(req));
        });
      });
      console.log('[FreeChoices] after filtering →', available);

      // --- Render dropdowns (unchanged) ---
      const $wrap = $('#cg-free-choices').empty();
      for (let i = 0; i < 3; i++) {
        const selId = `cg-free-choice-${i}`;
        const prev  = State.selected[i] || '';
        const options = available.map(g => {
          const sel = g.id == prev ? ' selected' : '';
          return `<option value="${g.id}"${sel}>${g.name}</option>`;
        }).join('');
        $wrap.append(`
          <select id="${selId}" data-index="${i}">
            <option value="">— Select Gift —</option>
            ${options}
          </select>
        `);
      }

      // --- Bind change handlers ---
      $(document)
        .off('change', '#cg-free-choices select')
        .on('change', '#cg-free-choices select', e => {
          const $sel = $(e.currentTarget);
          const idx  = $sel.data('index');
          const id   = $sel.val();

          // Persist the choice
          State.set(idx, id);

          // Merge the newly selected gift object into State
          const chosen = gifts.find(g => String(g.id) === String(id));
          if (chosen) {
            State.setList([chosen]);
          }

          // Recalculate all trait boosts
          TraitsService.refreshAll();
        });
    });
  }
};
