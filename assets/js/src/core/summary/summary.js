;(function($){
  window.CG_Summary = {
    init: function(){
      $('#cg-app').on('click', '[data-tab="tab-summary"]', () => {
        this.renderSummary();
      });

      $('#cg-app').on('click', '#cg-export-pdf', () => {
        alert('PDF export coming soon.');
      });
    },

    renderSummary: function(){
      const $sheet = $('#cg-summary-sheet').empty();
      const cap    = CG_FormBuilder.capitalize;
      const traits = CG_Traits.TRAITS;

      // Basic Fields
      const name    = $('#cg-name').val() || '—';
      const age     = $('#cg-age').val()  || '—';
      const gender  = $('#cg-gender').val() || '—';
      const motto   = $('#cg-motto').val() || '—';
      const goal1   = $('#cg-goal1').val() || '—';
      const goal2   = $('#cg-goal2').val() || '—';
      const goal3   = $('#cg-goal3').val() || '—';
      const description = $('#cg-description').val() || '—';
      const backstory   = $('#cg-backstory').val()   || '—';

      // Traits block (with label overrides)
      const traitBlock = traits.map(t => {
        const base    = $(`#cg-${t}`).val() || '';
        const boosted = CG_Traits.getBoostedDie(t);
        let label = cap(t);
        if (t === 'trait_species') label = 'Species';
        if (t === 'trait_career')  label = 'Career';

        const text = boosted
          ? `${base} ➔ ${boosted}`
          : (base || '—');
        return `<li><strong>${label}:</strong> ${text}</li>`;
      }).join('');

      // Species & Career
      const species = $('#cg-species option:selected').text().trim() || '—';
      const speciesGifts = $('#species-gifts li').length
        ? $('#species-gifts').html()
        : '<li>—</li>';

      const career = $('#cg-career option:selected').text().trim() || '—';
      const careerGifts = $('#career-gifts li').length
        ? $('#career-gifts').html()
        : '<li>—</li>';

      // Extra careers
      let extraHTML = '';
      $('.cg-extra-career-block').each((i) => {
        const idx     = i + 1;
        const txt     = $(`#cg-extra-career-${idx} option:selected`).text().trim() || '—';
        const key     = `trait_career_${idx}`;
        const boosted = CG_Traits.getBoostedDie(key);
        const raw     = $(`#cg-${key}`).val() || '';
        const val     = boosted || raw || '—';
        extraHTML += `<li><strong>Extra Career ${idx}:</strong> ${txt} (Trait: ${val})</li>`;
      });

      // Gifts
      const localTitle = $('#cg-local-knowledge label strong').text() || 'Local Knowledge';
      const localVal   = $('#cg-local-knowledge-area').val() || '—';

      const langTitle  = $('#cg-language label strong').text() || 'Language';
      const langVal    = $('#cg-language-area').val() || '—';

      const freeChoices = $('#cg-free-choices select').map((i, el) => {
        const txt = $(el).find('option:selected').text().trim() || '—';
        return `<li><strong>Free Choice ${i + 1}:</strong> ${txt}</li>`;
      }).get().join('');

      const bonusGiftHtml = `
        <h4>Bonus Gift</h4>
        <ul class="cg-summary-list"><li>Combat Save</li></ul>
      `;

      // Battle Array
      const speedDie = CG_Traits.getBoostedDie('speed') || $('#cg-speed').val() || '';
      const mindDie  = CG_Traits.getBoostedDie('mind')  || $('#cg-mind').val() || '';
      const bodyDie  = CG_Traits.getBoostedDie('body')  || $('#cg-body').val() || '';
      let dodgeDie = '';
      $('#skills-table tbody tr').each(function(){
        if ($(this).find('td:first').text().trim().toLowerCase() === 'dodge') {
          dodgeDie = $(this).find('.skill-total').text().trim();
        }
      });

      const initiativePool = (speedDie && mindDie)
        ? `${speedDie} + ${mindDie}` : '—';
      const dodgePool = speedDie
        ? `${speedDie}${dodgeDie ? ' + ' + dodgeDie : ''}` : '—';
      const soakPool  = bodyDie || '—';

      const dieMax = { d4:4, d6:6, d8:8, d10:10, d12:12 };
      function halfDie(d) {
        const n = parseInt(d.slice(1), 10);
        return `d${Math.ceil(n / 2)}`;
      }

      const stridePool = '1';
      const dashDice   = speedDie ? [halfDie(speedDie)] : [];
      if (bodyDie && dieMax[bodyDie] > dieMax[speedDie]) {
        dashDice.push(bodyDie);
      }
      const dashPool   = dashDice.length ? dashDice.join(' + ') : '—';
      const sprintPool = speedDie || '—';
      const runDice    = bodyDie || speedDie || dashDice.length ? [bodyDie, speedDie, ...dashDice].filter(Boolean) : [];
      const runPool    = runDice.length ? runDice.join(' + ') : '—';

      // Skills table
      let skillsHTML = '';
      $('#skills-table tbody tr').each(function(){
        const name = $(this).find('td:first').text().trim();
        const pool = $(this).find('.skill-total').text().trim() || '—';
        skillsHTML += `<tr><td>${name}</td><td>${pool}</td></tr>`;
      });

      // Final markup
      const html = `
        <h3>Character Summary</h3>
        <p><strong>Name:</strong> ${name}</p>
        <p><strong>Age:</strong> ${age}</p>
        <p><strong>Gender:</strong> ${gender}</p>

        <h4>Motto</h4>
        <p><em>“${motto}”</em></p>

        <h4>Goals</h4>
        <ul class="cg-summary-list">
          <li>${goal1}</li>
          <li>${goal2}</li>
          <li>${goal3}</li>
        </ul>

        <h4>Traits</h4>
        <ul class="cg-summary-list">${traitBlock}</ul>
        <hr>

        <h4>Species: ${species}</h4>
        <ul class="cg-summary-list">${speciesGifts}</ul>

        <h4>Career: ${career}</h4>
        <ul class="cg-summary-list">${careerGifts}</ul>

        ${extraHTML ? `
          <h4>Additional Careers</h4>
          <ul class="cg-summary-list">${extraHTML}</ul>
        ` : ''}

        <h4>${localTitle}</h4>
        <p><em>Area:</em> ${localVal}</p>

        <h4>${langTitle}</h4>
        <p><em>Choice:</em> ${langVal}</p>

        <h4>Free Choice Gifts</h4>
        <ul class="cg-summary-list">${freeChoices}</ul>

        ${bonusGiftHtml}

        <h3>Battle Array</h3>
        <ul class="cg-summary-list">
          <li><strong>Initiative:</strong> ${initiativePool}</li>
          <li><strong>Dodge:</strong> ${dodgePool}</li>
          <li><strong>Soak:</strong> ${soakPool}</li>
        </ul>

        <h3>Movement</h3>
        <ul class="cg-summary-list">
          <li><strong>Stride:</strong> ${stridePool}</li>
          <li><strong>Dash:</strong> ${dashPool}</li>
          <li><strong>Sprint:</strong> ${sprintPool}</li>
          <li><strong>Run:</strong> ${runPool}</li>
        </ul>
        <hr>

        <h4>Skills & Dice Pools</h4>
        <table class="cg-summary-skills" style="width:100%; border-collapse: collapse;">
          <thead>
            <tr>
              <th style="text-align:left; border-bottom:1px solid #aaa;">Skill</th>
              <th style="text-align:left; border-bottom:1px solid #aaa;">Dice Pool</th>
            </tr>
          </thead>
          <tbody>
            ${skillsHTML}
          </tbody>
        </table>

        <hr>

                <h4>Description</h4>
        <p>${description}</p>

        <h4>Backstory</h4>
        <p>${backstory}</p>
      `;

      $sheet.html(html);
    },

    bindExportButton: function(){
      $('#cg-export-pdf').on('click', function(){
        alert('PDF export not implemented yet.');
      });
    }
  };

  $(function(){
    CG_Summary.init();
  });
})(jQuery);
