// assets/js/src/core/skills/render.js
//
// TAB-RESTRUCTURE HARDENING (Dec 2025):
// - Do not touch DOM unless Skills tab is active AND #skills-table exists.
// - This prevents background refresh calls from other tabs from mutating UI.

import FormBuilderAPI from '../formBuilder';
import SpeciesAPI     from '../species/api.js';
import CareerAPI      from '../career/api.js';
import { marksToDice } from '../../utils/marks-dice.js';

const $ = window.jQuery;

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

function speciesNameOf(sp) {
  return sp?.speciesName || sp?.species_name || sp?.name || '';
}

function careerNameOf(cp) {
  return cp?.careerName || cp?.career_name || cp?.name || '';
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

    // ── Creation marks state & budget ────────────────────────────────────────
    data.skillMarks = data.skillMarks || {};
    const MAX_CREATION_MARKS = 13;
    const MAX_MARKS_PER_SKILL = 3;
    const usedCreationMarks = Object.values(data.skillMarks)
      .reduce((sum, v) => sum + (parseInt(v, 10) || 0), 0);
    const creationMarksRemain = Math.max(0, MAX_CREATION_MARKS - usedCreationMarks);

    // ── XP marks state ───────────────────────────────────────────────────────
    const xpMarksBudget = parseInt(data.xpMarksBudget, 10) || 0;
    const xpSkillMarks  = data.xpSkillMarks || {};
    const xpMarksPlaced = Object.values(xpSkillMarks)
      .reduce((sum, v) => sum + (parseInt(v, 10) || 0), 0);
    const xpMarksRemain = Math.max(0, xpMarksBudget - xpMarksPlaced);

    // ── Remove stale header ──────────────────────────────────────────────────
    $('#tab-skills #marks-remaining, #marks-remaining').remove();

    // Build header widget
    let headerHtml = `
      <div id="marks-remaining" class="marks-remaining">
        <div class="marks-remaining-group">
          <span class="marks-lbl">Starting Marks Remaining:</span>
          <strong>${creationMarksRemain}</strong>
          <span class="marks-note">/ ${MAX_CREATION_MARKS} · max ${MAX_MARKS_PER_SKILL} per skill</span>
        </div>
    `;
    if (xpMarksBudget > 0) {
      headerHtml += `
        <div class="marks-remaining-group xp-marks-group">
          <span class="marks-lbl">XP Marks Remaining:</span>
          <strong class="${xpMarksRemain === 0 ? 'marks-zero' : 'marks-xp'}">${xpMarksRemain}</strong>
          <span class="marks-note">/ ${xpMarksBudget} · no per-skill cap · extends beyond d8</span>
        </div>
      `;
    }
    headerHtml += `</div>`;
    $table.before(headerHtml);

    // ── Build table header ───────────────────────────────────────────────────
    const $thead = $('<thead>');
    const $tr = $('<tr>')
      .append('<th>Skill</th>')
      .append(`<th>${speciesNameOf(species) || ''}</th>`)
      .append(`<th>${careerNameOf(career) || ''}</th>`);

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

      const spDie    = spSkills.includes(id) ? 'd4' : '';
      const cpDie    = cpSkills.includes(id) ? 'd6' : '';
      const extraDies = extraCareers.map(ec => (ec.skills || []).includes(id) ? 'd4' : '');

      // ── Creation marks buttons (1–3, budget-capped) ──────────────────────
      const myMarks = parseInt(data.skillMarks[id], 10) || 0;
      let creationBtnsHtml = '';
      [1, 2, 3].forEach(n => {
        const disabled = (usedCreationMarks >= MAX_CREATION_MARKS && myMarks < n) ? ' disabled' : '';
        const active   = myMarks >= n ? ' active' : '';
        creationBtnsHtml += `<button
          type="button"
          class="skill-mark-btn${active}"
          data-skill-id="${id}"
          data-mark="${n}"
          data-mark-type="creation"
          ${disabled}
        ></button>`;
      });

      // ── XP marks control (+/− per skill) ─────────────────────────────────
      const myXpMarks = parseInt(xpSkillMarks[id], 10) || 0;
      let xpCtrlHtml = '';
      if (xpMarksBudget > 0) {
        const canAdd    = xpMarksRemain > 0;
        const canRemove = myXpMarks > 0;
        xpCtrlHtml = `
          <div class="xp-marks-row">
            <button type="button"
              class="xp-skill-btn xp-skill-minus${canRemove ? '' : ' disabled'}"
              data-skill-id="${id}" data-xp-action="minus"
              ${canRemove ? '' : 'disabled'}>−</button>
            <span class="xp-skill-count${myXpMarks > 0 ? ' xp-skill-count--active' : ''}"
              title="XP marks on this skill">${myXpMarks > 0 ? '+' + myXpMarks : '·'}</span>
            <button type="button"
              class="xp-skill-btn xp-skill-plus${canAdd ? '' : ' disabled'}"
              data-skill-id="${id}" data-xp-action="plus"
              ${canAdd ? '' : 'disabled'}>+</button>
          </div>
        `;
      }

      // ── Total marks & die display ─────────────────────────────────────────
      const totalMarks  = myMarks + myXpMarks;
      const markDie     = marksToDice(totalMarks);
      const markDisplay = totalMarks > 0
        ? (myXpMarks > 0 ? `<span class="marks-total-die">${markDie}</span>` : markDie)
        : '–';

      // ── Dice pool ─────────────────────────────────────────────────────────
      // For dice pool: marks contribute to the HIGHEST die only (standard Ironclaw rule)
      // The pool shows the base mark die (which may be a compound like d4+d12)
      const poolDice = [spDie, cpDie].concat(extraDies).filter(Boolean);
      if (markDie) poolDice.push(markDie);
      const poolStr = poolDice.length ? poolDice.join(' + ') : '–';

      const $row = $('<tr>')
        .append(`<td>${name}</td>`)
        .append(`<td>${spDie || '–'}</td>`)
        .append(`<td>${cpDie || '–'}</td>`);

      extraDies.forEach(die => {
        $row.append(`<td>${die || '–'}</td>`);
      });

      $row
        .append(`<td>
                   <div class="marks-buttons">${creationBtnsHtml}</div>
                   ${xpCtrlHtml}
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
