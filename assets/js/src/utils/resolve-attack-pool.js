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

export function resolveAttackPool(raw, { FormBuilderAPI, SpeciesAPI, CareerAPI } = {}) {
  if (!raw) return '';

  const FB   = FormBuilderAPI || window.CG_FormBuilderAPI || window.FormBuilderAPI;
  const data = (FB && FB._data) || {};

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

  const spNames = [SP.skill_one, SP.skill_two, SP.skill_three]
    .filter(Boolean).map(s => String(s).toLowerCase());
  const spIds   = [SP.skill_one_id, SP.skill_two_id, SP.skill_three_id]
    .filter(s => s != null && s !== '').map(s => String(s));
  const spDie   = traitDieFromData('trait_species', data);

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
    if ((spNames.includes(key) || spIds.includes(id)) && spDie) pool.push(spDie);
    if (cpIds.includes(id)    && cpDie) pool.push(cpDie);
    if (markDie)                        pool.push(markDie);

    if (pool.length) skillPoolMap[key] = pool.join(' + ');
  }

  const vsIdx    = raw.toLowerCase().indexOf(' vs.');
  const poolPart = vsIdx > -1 ? raw.slice(0, vsIdx) : raw;
  const parts    = poolPart.split(',').map(s => s.trim()).filter(Boolean);

  const allDice = [];
  for (const p of parts) {
    const key = p.toLowerCase();
    if (traitMap[key]) {
      allDice.push(traitMap[key]);
    } else if (skillPoolMap[key]) {
      allDice.push(...skillPoolMap[key].split(' + ').map(d => d.trim()).filter(Boolean));
    } else {
      allDice.push(p);
    }
  }
  return allDice.filter(Boolean).join(' + ');
}
