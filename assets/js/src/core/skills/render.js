// assets/js/src/core/skills/render.js
//
// TAB-RESTRUCTURE HARDENING (Dec 2025):
// - Do not touch DOM unless Skills tab is active AND #skills-table exists.
// - This prevents background refresh calls from other tabs from mutating UI.

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

function isSkillsTabActive() {
  try {
    const li  = document.querySelector('#cg-modal .cg-tabs li.active');
    const tab = li ? (li.getAttribute('data-tab') || '') : '';
    if (tab === 'tab-skills') return true;

    const panel = document.getElementById('tab-skills');
    if (panel && panel.classList.contains('active')) return true;
  } catch (_) {}
  return false;
}

/** -----------------------------
 * Tolerant utilities
 * ------------------------------*/
function pickFirst(obj, keys) {
  for (const k of keys) {
    if (obj && Object.prototype.hasOwnProperty.call(obj, k) && obj[k] != null && obj[k] !== '') {
      return obj[k];
    }
  }
  return null;
}

/**
 * Extract up to 3 skill IDs from *any* shape:
 * - flat keys: skill_one/two/three, skill_id_1..3, career_skill_1..3, etc.
 * - arrays (of ids or objects with id/skill_id/value)
 * - nested under .skills / .career_skills / .data
 */
function extractSkillTripletFromAny(input) {
  const asStr = v => (v == null ? '' : String(v));

  if (Array.isArray(input)) {
    const ids = input
      .map(x => (typeof x === 'object') ? (x.id ?? x.skill_id ?? x.value ?? null) : x)
      .filter(v => v != null)
      .slice(0, 3)
      .map(asStr);
    while (ids.length < 3) ids.push('');
    return ids;
  }

  if (input && typeof input === 'object') {
    if (input.skills != null)         return extractSkillTripletFromAny(input.skills);
    if (input.career_skills != null)  return extractSkillTripletFromAny(input.career_skills);
    if (input.species_skills != null) return extractSkillTripletFromAny(input.species_skills);
    if (input.data != null)           return extractSkillTripletFromAny(input.data);

    const id1 = pickFirst(input, [
      'skill_one', 'skill1', 'skill_id_1', 'skill_one_id', 'career_skill_1', 'career_skill_one'
    ]);
    const id2 = pickFirst(input, [
      'skill_two', 'skill2', 'skill_id_2', 'skill_two_id', 'career_skill_2', 'career_skill_two'
    ]);
    const id3 = pickFirst(input, [
      'skill_three', 'skill3', 'skill_id_3', 'skill_three_id', 'career_skill_3', 'career_skill_three'
    ]);
    return [asStr(id1), asStr(id2), asStr(id3)];
  }

  return ['', '', ''];
}

/** Best-effort name helpers */
function speciesNameOf(sp) {
  return (
    sp?.speciesName ||
    sp?.species_name ||
    sp?.name ||
    ''
  );
}

function careerNameOf(cp) {
  return (
    cp?.careerName ||
    cp?.career_name ||
    cp?.name ||
    ''
  );
}

function parseExtraCareersFromData(d) {
  if (!d) return [];
  if (Array.isArray(d.extraCareers)) return d.extraCareers;

  if (typeof d.extra_careers === 'string' && d.extra_careers.trim()) {
    try {
      const arr = JSON.parse(d.extra_careers);
      if (Array.isArray(arr)) return arr;
    } catch (_) {}
  }
  return [];
}

export default {
  render() {
    // HARD GATE: only run when Skills tab is active
    if (!isSkillsTabActive()) return;

    // HARD GATE: only run if the skills table exists
    let $table = $('#tab-skills #skills-table');
    if (!$table.length) $table = $('#skills-table');
    if (!$table.length) return;

    const data    = FormBuilderAPI.getData();
    const skills  = data.skillsList || window.CG_SKILLS_LIST || [];
    const species = SpeciesAPI.currentProfile || {};
    const career  = CareerAPI.currentProfile  || {};

    // Extra careers from state (written by career/extra.js)
    const extraCareers = parseExtraCareersFromData(data)
      .filter(x => x && x.id)
      .map(x => ({
        id: String(x.id),
        name: String(x.name || ''),
        skills: Array.isArray(x.skills) ? x.skills.map(String) : []
      }));

    // Read marks state & remaining budget
    data.skillMarks = data.skillMarks || {};
    const MAX_MARKS = 13;
    const usedMarks = Object.values(data.skillMarks)
      .reduce((sum, v) => sum + (parseInt(v, 10) || 0), 0);
    const marksRemain = Math.max(0, MAX_MARKS - usedMarks);

    // Inject “Marks Remaining” display (scoped to the skills area)
    $('#tab-skills #marks-remaining, #marks-remaining').remove();
    $table.before(`
      <div id="marks-remaining" class="marks-remaining">
        Marks Remaining: <strong>${marksRemain}</strong>
      </div>
    `);

    // Build table header
    const $thead = $('<thead>');
    const $tr = $('<tr>')
      .append('<th>Skill</th>')
      .append(`<th>${speciesNameOf(species) || ''}</th>`)
      .append(`<th>${careerNameOf(career) || ''}</th>`);

    // Add extra-career columns
    extraCareers.forEach(ec => {
      $tr.append(`<th>${ec.name || 'Extra Career'}</th>`);
    });

    $tr.append('<th>Marks</th>')
      .append('<th>Dice Pool</th>')
      .appendTo($thead);

    // Species & Career skill IDs (tolerant)
    const spSkills = extractSkillTripletFromAny(species).map(String);
    const cpSkills = extractSkillTripletFromAny(career).map(String);

    const $tbody = $('<tbody>');

    skills.forEach(skill => {
      const id   = String(skill.id);
      const name = skill.name;

      // base dice
      const spDie = spSkills.includes(id) ? 'd4' : '';
      const cpDie = cpSkills.includes(id) ? 'd6' : '';

      // Extra careers contribute d4 for matching skills
      const extraDies = extraCareers.map(ec => (ec.skills || []).includes(id) ? 'd4' : '');

      // mark buttons: empty content, active if mark index ≤ myMarks
      const myMarks = parseInt(data.skillMarks[id], 10) || 0;
      let buttonsHtml = '';
      [1, 2, 3].forEach(n => {
        // disable any button above remaining budget (unless already active)
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

      const markDie= myMarks ? MARK_DIE[myMarks] : '';
      const markDisplay = markDie || '–';

      // dice pool = species + career + extra careers + marks
      const poolDice = [spDie, cpDie].concat(extraDies).concat([markDie]).filter(Boolean);
      const poolStr  = poolDice.length ? poolDice.join(' + ') : '–';

      const $row = $('<tr>')
        .append(`<td>${name}</td>`)
        .append(`<td>${spDie || '–'}</td>`)
        .append(`<td>${cpDie || '–'}</td>`);

      // Extra career cells
      extraDies.forEach(die => {
        $row.append(`<td>${die || '–'}</td>`);
      });

      $row
        .append(`<td>
                   <div class="marks-buttons">${buttonsHtml}</div>
                   <div class="marks-display">${markDisplay}</div>
                 </td>`)
        .append(`<td>${poolStr}</td>`);

      $tbody.append($row);
    });

    // Inject into DOM
    $table
      .empty()
      .append($thead)
      .append($tbody);
  }
};
