// assets/js/src/core/main/builder-load.js
// Populates Species/Career on builder open and wires Gifts free-choice.

import SpeciesIndex from '../species';
import CareerIndex  from '../career';
import FreeChoices  from '../../../gifts/free-choices.js';

const $ = window.jQuery;

export default function builderLoadInit() {
  // When the modal opens and the Profile tab is shown, initialize modules.
  // If you have a dedicated tab event, hook that here; otherwise run on open.

  // Ensure selects exist (non-fatal if template injects later)
  const ensureProfileSkeleton = () => {
    const container = document.querySelector('#cg-form-container');
    if (!container) return;

    if (!container.querySelector('#cg-species')) {
      const block = document.createElement('div');
      block.innerHTML = `
        <section id="cg-profile-panel" class="cg-panel">
          <label>Species<br><select id="cg-species"></select></label>
          <label>Career<br><select  id="cg-career"></select></label>
        </section>`;
      container.appendChild(block.firstElementChild);
    }
  };

  ensureProfileSkeleton();

  // Initialize index modules (safe to call multiple times)
  SpeciesIndex.init();
  CareerIndex.init();
  FreeChoices.init();

  // Optional: if your UI has a tab system, re-run population when the
  // Profile tab is first shown.
  $(document)
    .off('cg:profile:show.cgload')
    .on('cg:profile:show.cgload', () => {
      SpeciesIndex.init();
      CareerIndex.init();
      FreeChoices.init();
    });
}
