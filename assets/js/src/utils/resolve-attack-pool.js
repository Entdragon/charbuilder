// assets/js/src/utils/resolve-attack-pool.js
//
// Shared helper: converts a raw attack_dice formula string (e.g. "Body, Brawling vs. defense")
// into a resolved dice pool string (e.g. "d8 + d6 + d8") by substituting:
//   - Trait keywords → current trait die (read from DOM first, then FormBuilderAPI._data)
//   - Skill names    → full skill dice pool (species die + career die + marks die)

import { marksToDice } from './marks-dice.js';

function traitDieFromData(key, data) {
  const dom = document.getElementById(`cg-${key}`);
  if (dom && dom.value) return dom.value;
  return (data && data[key]) || '';
}

let _debugLogged = false;
export function resolveAttackPool(raw, { FormBuilderAPI, SpeciesAPI, CareerAPI } = {}) {
  if (!raw) return '';

  const FB   = FormBuilderAPI || window.CG_FormBuilderAPI || window.FormBuilderAPI;
  const data = (FB && FB._data) || {};

  if (!_debugLogged) {
    _debugLogged = true;
    console.log('[resolveAttackPool DEBUG] raw:', raw,
      '\n  skillsList length:', (data.skillsList || []).length,
      '\n  skillMarks:', JSON.stringify(data.skillMarks),
      '\n  gift_skill_marks:', JSON.stringify(data.gift_skill_marks),
      '\n  xpSkillMarks:', JSON.stringify(data.xpSkillMarks),
      '\n  trappings_list length:', (data.trappings_list || []).length,
      '\n  first trapping attack_dice:', data.trappings_list?.[0]?.attack_dice
    );
  }

  const traitMap = {
    'body':    traitDieFromData('body',         data),
    'speed':   traitDieFromData('speed',        data),
    'will':    traitDieFromData('will',         data),
    'mind':    traitDieFromData('mind',         data),
    'species': traitDieFromData('trait_species', data),
    'career':  traitDieFromData('trait_career',  data),
  };

  const skillsList = data.skillsList        || [];
  const skillMarks = data.skillMarks        || {};
  const giftMarks  = data.gift_skill_marks  || {};
  const xpMarks    = data.xpSkillMarks      || {};

  const SP  = (SpeciesAPI && SpeciesAPI.currentProfile) || (window.CG_SpeciesAPI && window.CG_SpeciesAPI.currentProfile) || {};
  const CP  = (CareerAPI  && CareerAPI.currentProfile)  || (window.CG_CareerAPI  && window.CG_CareerAPI.currentProfile)  || {};

  // Species skill_one/two/three store numeric skill IDs in text_value (same format as career).
  const spSkillIds = [SP.skill_one, SP.skill_two, SP.skill_three]
    .filter(s => s != null && s !== '').map(s => String(s));
  const spDie      = traitDieFromData('trait_species', data);

  const cpIds = [CP.skill_one, CP.skill_two, CP.skill_three]
    .filter(Boolean).map(s => String(s));
  const cpDie = traitDieFromData('trait_career', data);

  const skillPoolMap = {};
  for (const skill of skillsList) {
    const id    = String(skill.id);
    const key   = skill.name.toLowerCase();
    const my    = parseInt(skillMarks[id], 10) || 0;
    const gift  = parseInt(giftMarks[id],  10) || 0;
    const xp    = parseInt(xpMarks[id],    10) || 0;
    const markDie = marksToDice(my + gift + xp);

    const pool = [];
    if (spSkillIds.includes(id) && spDie) pool.push(spDie);
    if (cpIds.includes(id)    && cpDie) pool.push(cpDie);
    if (markDie)                        pool.push(markDie);

    if (pool.length) skillPoolMap[key] = pool.join(' + ');
  }

  const vsIdx    = raw.toLowerCase().indexOf(' vs.');
  const poolPart = vsIdx > -1 ? raw.slice(0, vsIdx) : raw;
  const parts    = poolPart.split(',').map(s => s.trim()).filter(Boolean);

  // Pattern: NdX literal (e.g. "3d6", "2d8") — expand into N individual dice.
  const ndxPat = /^(\d+)(d\d+)$/i;

  const allDice = [];
  for (const p of parts) {
    const key = p.toLowerCase();

    if (traitMap[key]) {
      allDice.push(traitMap[key]);
    } else if (skillPoolMap[key]) {
      allDice.push(...skillPoolMap[key].split(' + ').map(d => d.trim()).filter(Boolean));
    } else {
      // NdX literal (e.g. "3d6") — push N copies of the die
      const ndx = key.match(ndxPat);
      if (ndx) {
        const n   = parseInt(ndx[1], 10);
        const die = ndx[2].toLowerCase();
        for (let i = 0; i < n; i++) allDice.push(die);
      } else {
        // Unknown token — pass through as-is so caller can decide
        allDice.push(p);
      }
    }
  }
  return allDice.filter(Boolean).join(' + ');
}
