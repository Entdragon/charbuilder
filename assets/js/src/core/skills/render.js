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

// Read trait die from DOM first (same pattern as BattleAPI.traitDie),
// then fall back to _data, then to getData().
function traitDie(key) {
  const dom = document.getElementById(`cg-${key}`);
  if (dom && dom.value) return dom.value;
  const FB = window.CG_FormBuilderAPI || window.FormBuilderAPI;
  const d  = (FB && FB._data) || {};
  return d[key] || '';
}

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

    // Sync mark-button DOM state into _data BEFORE we read getData().
    // This captures any clicks that updated the DOM but not _data (edge cases),
    // and ensures we never render stale marks after tab switches.
    const _syncMarks = FormBuilderAPI._data.skillMarks || {};
    $table.find('.skill-mark-btn').each((i, el) => {
      const $b  = $(el);
      const sid = String($b.data('skill-id') ?? '');
      const mk  = parseInt($b.data('mark'), 10) || 0;
      if (!sid) return;
      const isOn = $b.hasClass('active') || String($b.attr('aria-pressed') || '') === 'true';
      if (isOn) {
        const cur = parseInt(_syncMarks[sid], 10) || 0;
        if (mk > cur) _syncMarks[sid] = mk;
      }
    });
    FormBuilderAPI._data.skillMarks = _syncMarks;

    const data    = FormBuilderAPI.getData();
    const skills  = data.skillsList || window.CG_SKILLS_LIST || [];
    const species = SpeciesAPI.currentProfile || {};
    const career  = CareerAPI.currentProfile  || {};

    /* DEBUG — remove after confirming species die works
    console.log('[SkillsRender] sp profile:', {
      skill_one: species.skill_one, skill_one_id: species.skill_one_id,
      skill_two: species.skill_two, skill_two_id: species.skill_two_id,
      skill_three: species.skill_three, skill_three_id: species.skill_three_id,
      spTraitDie: traitDie('trait_species'),
    });
    console.log('[SkillsRender] skillMarks:', JSON.stringify(data.skillMarks));
    */

    // Extra careers from state (written by career/extra.js)
    const extraCareers = parseExtraCareersFromData(data)
      .filter(x => x && x.id)
      .map(x => ({
        id: String(x.id),
        name: String(x.name || ''),
        skills: Array.isArray(x.skills) ? x.skills.map(String) : []
      }));

    // ── Skill notes (favourite use) ───────────────────────────────────────────
    data.skill_notes = (data.skill_notes && typeof data.skill_notes === 'object' && !Array.isArray(data.skill_notes))
      ? data.skill_notes
      : {};

    // ── Gift-granted marks ────────────────────────────────────────────────────
    data.gift_skill_marks = (data.gift_skill_marks && typeof data.gift_skill_marks === 'object' && !Array.isArray(data.gift_skill_marks))
      ? data.gift_skill_marks
      : {};

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
          <span class="marks-note">/ ${MAX_CREATION_MARKS} · max ${MAX_MARKS_PER_SKILL} per skill (+ gift marks)</span>
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

    // Actual trait die values — read DOM first so live select values are used
    // even if _data hasn't been synced yet (same pattern as BattleAPI)
    const spTraitDie = traitDie('trait_species');
    const cpTraitDie = traitDie('trait_career');

    // Species skills: match by NAME (text_value) OR by ID (ref_id exposed as *_id).
    // Both paths are checked so it works regardless of how the DB stores the value.
    // Career skills are stored as numeric SKILL IDs.
    const spNames  = [species.skill_one, species.skill_two, species.skill_three]
      .filter(Boolean).map(s => String(s).toLowerCase());
    const spIds    = [species.skill_one_id, species.skill_two_id, species.skill_three_id]
      .filter(s => s != null && s !== '').map(s => String(s));
    const cpSkills = extractSkillTripletFromAny(career).map(String);

    const $tbody = $('<tbody>');

    skills.forEach(skill => {
      const id      = String(skill.id);
      const name    = skill.name;
      const nameLow = name.toLowerCase();

      const isSpSkill = spNames.includes(nameLow) || spIds.includes(id);
      const spDie    = (spTraitDie && isSpSkill) ? spTraitDie : '';
      const cpDie    = (cpTraitDie && cpSkills.includes(id)) ? cpTraitDie : '';
      const extraDies = extraCareers.map(ec => (ec.skills || []).includes(id) ? 'd4' : '');

      // ── Gift-granted marks for this skill ─────────────────────────────────
      const giftMarks = parseInt(data.gift_skill_marks[id], 10) || 0;

      // ── Creation marks buttons ────────────────────────────────────────────
      // Each skill can have up to 3 manual creation marks PLUS any gift-granted marks.
      // The gift mark expands the button budget: 3 manual marks can still be placed on top.
      // Clamp to 3 in case of stale/edited saves.
      const myMarks = Math.min(parseInt(data.skillMarks[id], 10) || 0, MAX_MARKS_PER_SKILL);
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

      // Gift marks indicator (shown when gift grants marks)
      let giftMarkHtml = '';
      if (giftMarks > 0) {
        giftMarkHtml = `<span class="skill-gift-mark-badge" title="${giftMarks} mark(s) from Knack For gift">+${giftMarks} gift</span>`;
      }

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
      const totalMarks  = myMarks + giftMarks + myXpMarks;
      const markDie     = marksToDice(totalMarks);
      const markDisplay = totalMarks > 0
        ? (myXpMarks > 0 ? `<span class="marks-total-die">${markDie}</span>` : markDie)
        : '–';

      // ── Dice pool ─────────────────────────────────────────────────────────
      const poolDice = [spDie, cpDie].concat(extraDies).filter(Boolean);
      if (markDie) poolDice.push(markDie);
      const poolStr = poolDice.length ? poolDice.join(' + ') : '–';

      // ── Favourite use input ───────────────────────────────────────────────
      const noteVal = String(data.skill_notes[id] || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
      const favouriteUseHtml = `<input
        type="text"
        class="skill-fav-input"
        data-skill-id="${id}"
        placeholder="Favourite use…"
        value="${noteVal}"
        maxlength="120"
        title="Note your favourite use of this skill"
      />`;

      const $row = $('<tr>')
        .append(`<td>
          <div class="skill-name-cell">
            <span>${name}</span>
            ${favouriteUseHtml}
          </div>
        </td>`)
        .append(`<td>${spDie || '–'}</td>`)
        .append(`<td>${cpDie || '–'}</td>`);

      extraDies.forEach(die => {
        $row.append(`<td>${die || '–'}</td>`);
      });

      $row
        .append(`<td>
                   <div class="marks-buttons">${creationBtnsHtml}${giftMarkHtml}</div>
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
