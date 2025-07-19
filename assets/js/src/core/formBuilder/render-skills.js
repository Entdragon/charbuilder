// assets/js/src/core/formBuilder/render-skills.js

import SpeciesService from '../species/api.js';
import CareerService  from '../career/api.js';
import FormBuilderAPI from '../formBuilder';

function escape(str = '') {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

export default {
  renderContent(data = {}) {
    const speciesName  = SpeciesService.currentProfile?.species_name || '';
    const careerName   = CareerService.currentProfile?.career_name  || '';
    const extraCareers = data.extraCareers || []; 
    const skillList    = data.skillsList || [];

    // Build header columns
    const careerCols = [`<th>Career: ${escape(careerName)}</th>`]
      .concat(
        extraCareers.map((c,i) =>
          `<th>Career ${i+2}: ${escape(c.name)}</th>`
        )
      ).join('');

    // Build each skill row
    const rows = skillList.map(skill => `
      <tr data-skill-id="${skill.id}">
        <td>${escape(skill.name)}</td>
        <td class="skill-species-die"></td>
        ${extraCareers.map(() => '<td></td>').join('')}
        <td>
          <input
            type="number"
            class="skill-marks"
            data-skill-id="${skill.id}"
            min="0"
            value="${escape(data.skillMarks?.[skill.id]||0)}"
          />
        </td>
        <td class="skill-total">—</td>
      </tr>
    `).join('');

    return `
      <div id="tab-skills" class="tab-panel">
        <table id="skills-table" class="cg-skills-table">
          <thead>
            <tr>
              <th>Skill Name</th>
              <th>Species: ${escape(speciesName)}</th>
              ${careerCols}
              <th>Skill Marks</th>
              <th>Dice Pool</th>
            </tr>
          </thead>
          <tbody>
            ${rows}
          </tbody>
        </table>
      </div>
    `;
  }
};
