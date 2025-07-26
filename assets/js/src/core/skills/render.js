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
    console.group('[SkillsRender] 🧩 render() called');

    const skills  = window.CG_SKILLS_LIST || [];
    const species = SpeciesAPI.currentProfile || {};
    const career  = CareerAPI.currentProfile  || {};
    const data    = FormBuilderAPI.getData();

    console.log('[SkillsRender] 🧬 Loaded species profile:', species);
    console.log('[SkillsRender] 🧬 Loaded career profile:', career);
    console.log('[SkillsRender] 📦 Current builder data:', data);

    data.skillMarks = data.skillMarks || {};
    const MAX_MARKS = 13;
    const usedMarks = Object.values(data.skillMarks).reduce((sum, v) => sum + v, 0);
    const marksRemain = Math.max(0, MAX_MARKS - usedMarks);

    console.log(`[SkillsRender] ✅ Used Marks: ${usedMarks}, Remaining: ${marksRemain}`);

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

    const spSkills = [species.skill_one, species.skill_two, species.skill_three].map(String);
    const cpSkills = [career.skill_one,  career.skill_two,  career.skill_three ].map(String);

    const $tbody = $('<tbody>');

    skills.forEach(skill => {
      const id   = String(skill.id);
      const name = skill.name;

      const spDie = spSkills.includes(id) ? 'd4' : '';
      const cpDie = cpSkills.includes(id) ? 'd6' : '';
      const myMarks = data.skillMarks[id] || 0;

      console.log(`[SkillsRender] 🎯 Skill "${name}" (ID: ${id})`);
      console.log(`  ↪ Species die: ${spDie}, Career die: ${cpDie}, Marks: ${myMarks}`);

      let buttonsHtml = '';
      [1,2,3].forEach(n => {
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

      const poolDice = [spDie, cpDie, markDie].filter(Boolean);
      const poolStr  = poolDice.length ? poolDice.join(' + ') : '–';

      console.log(`  ↪ Pool Dice: ${poolDice.join(', ')} → Display: ${poolStr}`);

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
    console.log('[SkillsRender] 🧱 Injecting built table into #skills-table');
    $('#skills-table')
      .empty()
      .append($thead)
      .append($tbody);

    console.groupEnd();
  }
};
