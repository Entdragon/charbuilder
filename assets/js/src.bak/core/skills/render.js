// assets/js/src/core/skills/render.js
import FormBuilderAPI from '../formBuilder';
import SpeciesAPI     from '../species/api.js';
import CareerAPI      from '../career/api.js';

const $ = window.jQuery;

// Map #marks → die
const MARK_DIE = {
  1: 'd4',
  2: 'd6',
  3: 'd8'
};

export default {
  render() {
    const skills  = window.CG_SKILLS_LIST || [];
    const species = SpeciesAPI.currentProfile || {};
    const career  = CareerAPI.currentProfile  || {};
    const data    = FormBuilderAPI.getData();

    data.skillMarks = data.skillMarks || {};
    const MAX_MARKS = 13;
    const usedMarks = Object.values(data.skillMarks)
                          .reduce((sum, v) => sum + v, 0);
    const marksRemain = Math.max(0, MAX_MARKS - usedMarks);

    // Inject remaining‐marks display
    $('#marks-remaining').remove();
    $('#skills-table').before(`
      <div id="marks-remaining" class="marks-remaining">
        Marks Remaining: <strong>${marksRemain}</strong>
      </div>
    `);

    // Build table header
    const $thead = $('<thead>');
    $('<tr>')
      .append('<th>Skill</th>')
      .append(`<th>${species.speciesName || ''}</th>`)
      .append(`<th>${career.careerName || ''}</th>`)
      .append('<th>Marks</th>')
      .append('<th>Dice Pool</th>')
      .appendTo($thead);

    // Rows
    const spSkills = [species.skill_one, species.skill_two, species.skill_three].map(String);
    const cpSkills = [career.skill_one,  career.skill_two,  career.skill_three ].map(String);
    const $tbody = $('<tbody>');

    skills.forEach(skill => {
      const id   = String(skill.id);
      const name = skill.name;

      // base dice
      const spDie = spSkills.includes(id) ? 'd4' : '';
      const cpDie = cpSkills.includes(id) ? 'd6' : '';

      // mark buttons: empty content, active if mark index ≤ myMarks
      const myMarks = data.skillMarks[id] || 0;
      let buttonsHtml = '';
      [1,2,3].forEach(n => {
        // disable any button above remaining budget
        const disabled = (usedMarks >= MAX_MARKS && myMarks < n) ? ' disabled' : '';
        const active   = myMarks >= n ? ' active' : '';
        buttonsHtml += `<button
          type="button"
          class="skill-mark-btn${active}"
          data-skill-id="${id}"
          data-mark="${n}"
          ${disabled}
        ></button>`;
      });

      const markDie    = myMarks ? MARK_DIE[myMarks] : '';
      const markDisplay = markDie || '–';

      // dice pool = species + career + marks
      const poolDice = [spDie, cpDie, markDie].filter(Boolean);
      const poolStr  = poolDice.length ? poolDice.join(' + ') : '–';

      const $row = $('<tr>')
        .append(`<td>${name}</td>`)
        .append(`<td>${spDie || '–'}</td>`)
        .append(`<td>${cpDie || '–'}</td>`)
        .append(`<td>
                   <div class="marks-buttons">${buttonsHtml}</div>
                   <div class="marks-display">${markDisplay}</div>
                 </td>`)
        .append(`<td>${poolStr}</td>`);

      $tbody.append($row);
    });

    // Inject into DOM
    $('#skills-table')
      .empty()
      .append($thead)
      .append($tbody);
  }
};
