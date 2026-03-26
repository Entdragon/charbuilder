// assets/js/src/core/experience/retrain.js
//
// Retraining system: remove marks or gifts from the character sheet in
// exchange for Experience Points (+2 per mark, +5 per gift).
//
// Math note: when an XP-purchased mark (slot) or gift slot is retrained,
// we decrement the budget AND add a retrainPenalty so the *net* gain is
// +2 / +5 rather than +6 / +15 (which would be the raw budget-freed + earned).
//
// calcSpent = xpMarksBudget*4 + xpGiftSlots*10 + retrainPenalty

import FormBuilderAPI from '../formBuilder';
import FreeChoices from '../gifts/free-choices.js';

const W = window;
const $ = W.jQuery;

// ── Helpers ───────────────────────────────────────────────────────────────────

function getData()  { return FormBuilderAPI._data || {}; }
function setData(p) { FormBuilderAPI._data = { ...(FormBuilderAPI._data || {}), ...p }; }

function getSkillName(skillId) {
  const id = String(skillId);
  const list = W.CG_SKILLS_LIST || (getData().skillsList) || [];
  const found = Array.isArray(list) ? list.find(s => String(s.id) === id) : null;
  return found ? String(found.name || id) : id;
}

function getGiftName(giftId) {
  const id = String(giftId);
  const all = (FreeChoices._allGifts) || [];
  const found = all.find(g => String(g.ct_id || g.id || '') === id);
  return found ? String(found.ct_gift_name || found.name || id) : id;
}

function getCareerGiftIds() {
  const prof = W.CareerAPI?.currentProfile || W.CG_CareerAPI?.currentProfile || null;
  if (!prof) return new Set();
  return new Set(
    ['gift_id_1', 'gift_id_2', 'gift_id_3']
      .map(k => String(prof[k] || '').trim())
      .filter(Boolean)
  );
}

function getRetrainPenalty() { return parseInt(getData().retrainPenalty, 10) || 0; }
function getRetrainLog()     { return Array.isArray(getData().retrainLog) ? getData().retrainLog : []; }

function appendRetrainLog(entry) {
  const log = getRetrainLog().slice();
  log.push({ ...entry, ts: Date.now() });
  setData({ retrainLog: log });
}

// ── Retrain Actions ────────────────────────────────────────────────────────────

/**
 * Retrain one creation mark from a skill (source = 'creation').
 * +2 XP, no penalty adjustment needed (mark wasn't bought with XP budget).
 */
function retrainCreationMark(skillId) {
  const marks = { ...(getData().skillMarks || {}) };
  const cur = parseInt(marks[skillId], 10) || 0;
  if (cur <= 0) return false;

  if (cur <= 1) delete marks[skillId];
  else marks[skillId] = cur - 1;

  const earned = (parseInt(getData().experience_points, 10) || 0) + 2;
  setData({ skillMarks: marks, experience_points: earned });
  appendRetrainLog({ type: 'mark', skillId, skillName: getSkillName(skillId), source: 'creation', xpGained: 2 });
  return true;
}

/**
 * Retrain one XP mark from a skill (source = 'xp').
 * Decrements xpMarksBudget, adds retrainPenalty += 4 to offset the freed budget,
 * and adds +2 to earned XP. Net: +2 available XP.
 */
function retrainXpMark(skillId) {
  const xpMarks = { ...(getData().xpSkillMarks || {}) };
  const cur = parseInt(xpMarks[skillId], 10) || 0;
  if (cur <= 0) return false;

  if (cur <= 1) delete xpMarks[skillId];
  else xpMarks[skillId] = cur - 1;

  const budget  = Math.max(0, (parseInt(getData().xpMarksBudget, 10) || 0) - 1);
  const earned  = (parseInt(getData().experience_points, 10) || 0) + 2;
  const penalty = getRetrainPenalty() + 4; // offsets the 4 XP freed by budget decrease

  setData({ xpSkillMarks: xpMarks, xpMarksBudget: budget, experience_points: earned, retrainPenalty: penalty });
  appendRetrainLog({ type: 'mark', skillId, skillName: getSkillName(skillId), source: 'xp', xpGained: 2 });
  return true;
}

/**
 * Retrain a free gift from slot index 0, 1, or 2.
 * +5 XP, no penalty (the slot isn't part of the XP budget).
 */
function retrainFreeGift(slotIndex) {
  const key   = `free_gift_${slotIndex + 1}`;
  const giftId = String(getData()[key] || '').trim();
  if (!giftId || giftId === '0') return false;

  const giftName = getGiftName(giftId);

  // Also clear the qual for this slot
  const freeQuals = { ...(getData().free_gift_quals || {}) };
  delete freeQuals[String(slotIndex)];

  const earned = (parseInt(getData().experience_points, 10) || 0) + 5;
  setData({ [key]: '', free_gift_quals: freeQuals, experience_points: earned });
  appendRetrainLog({ type: 'gift', giftId, giftName, source: 'free', slotIndex, xpGained: 5 });
  return true;
}

/**
 * Retrain an XP gift at array index i.
 * Decrements xpGiftSlots, adds retrainPenalty += 10, adds +5 earned XP.
 * Net: +5 available XP.
 */
function retrainXpGift(slotIndex) {
  const gifts = (getData().xpGifts || []).slice();
  if (slotIndex < 0 || slotIndex >= gifts.length) return false;
  const giftId = String(gifts[slotIndex] || '').trim();
  if (!giftId) return false;

  const giftName = getGiftName(giftId);

  // Clear qual for this slot
  const xpQuals = { ...(getData().xp_gift_quals || {}) };
  delete xpQuals[String(slotIndex)];

  // Rebuild xpGifts without this slot and renumber xpQuals
  const newGifts = gifts.filter((_, i) => i !== slotIndex);
  const newQuals = {};
  let qi = 0;
  gifts.forEach((_, i) => {
    if (i === slotIndex) return;
    if (xpQuals[String(i)]) newQuals[String(qi)] = xpQuals[String(i)];
    qi++;
  });

  const slots   = Math.max(0, (parseInt(getData().xpGiftSlots, 10) || 0) - 1);
  const earned  = (parseInt(getData().experience_points, 10) || 0) + 5;
  const penalty = getRetrainPenalty() + 10;

  setData({ xpGifts: newGifts, xpGiftSlots: slots, xp_gift_quals: newQuals, experience_points: earned, retrainPenalty: penalty });
  appendRetrainLog({ type: 'gift', giftId, giftName, source: 'xp', slotIndex, xpGained: 5 });
  return true;
}

// ── Render ─────────────────────────────────────────────────────────────────────

function renderRetrainPanel() {
  const $panel = $('#cg-retrain-panel');
  if (!$panel.length) return;

  const d = getData();
  const careerGiftIds = getCareerGiftIds();

  // ── Marks ────────────────────────────────────────────────────────────────
  const creationMarks = d.skillMarks && typeof d.skillMarks === 'object' ? d.skillMarks : {};
  const xpMarks       = d.xpSkillMarks && typeof d.xpSkillMarks === 'object' ? d.xpSkillMarks : {};

  const markRows = [];

  // Creation marks
  Object.entries(creationMarks).forEach(([skillId, cnt]) => {
    const n = parseInt(cnt, 10) || 0;
    if (n <= 0) return;
    const name = getSkillName(skillId);
    markRows.push(`
      <div class="retrain-row" data-retrain-type="mark-creation" data-skill-id="${skillId}">
        <span class="retrain-item-name">${name}</span>
        <span class="retrain-item-detail">${n} starting mark${n > 1 ? 's' : ''}</span>
        <button type="button" class="retrain-btn" data-action="retrain-mark-creation" data-skill-id="${skillId}">
          Retrain 1 Mark <em>(+2 XP)</em>
        </button>
      </div>
    `);
  });

  // XP marks
  Object.entries(xpMarks).forEach(([skillId, cnt]) => {
    const n = parseInt(cnt, 10) || 0;
    if (n <= 0) return;
    const name = getSkillName(skillId);
    markRows.push(`
      <div class="retrain-row" data-retrain-type="mark-xp" data-skill-id="${skillId}">
        <span class="retrain-item-name">${name}</span>
        <span class="retrain-item-detail">${n} XP mark${n > 1 ? 's' : ''}</span>
        <button type="button" class="retrain-btn" data-action="retrain-mark-xp" data-skill-id="${skillId}">
          Retrain 1 Mark <em>(+2 XP)</em>
        </button>
      </div>
    `);
  });

  // ── Gifts ────────────────────────────────────────────────────────────────
  const giftRows = [];

  // Free gift slots
  [0, 1, 2].forEach(i => {
    const key    = `free_gift_${i + 1}`;
    const giftId = String(d[key] || '').trim();
    if (!giftId || giftId === '0') return;
    if (careerGiftIds.has(giftId)) return; // career gift — not retrain-able
    const name = getGiftName(giftId);
    giftRows.push(`
      <div class="retrain-row" data-retrain-type="gift-free" data-slot="${i}">
        <span class="retrain-item-name">${name}</span>
        <span class="retrain-item-detail">Free gift slot ${i + 1}</span>
        <button type="button" class="retrain-btn" data-action="retrain-gift-free" data-slot="${i}">
          Retrain Gift <em>(+5 XP)</em>
        </button>
      </div>
    `);
  });

  // XP gift slots
  const xpGifts = Array.isArray(d.xpGifts) ? d.xpGifts : [];
  xpGifts.forEach((giftId, i) => {
    const id = String(giftId || '').trim();
    if (!id) return;
    if (careerGiftIds.has(id)) return;
    const name = getGiftName(id);
    giftRows.push(`
      <div class="retrain-row" data-retrain-type="gift-xp" data-slot="${i}">
        <span class="retrain-item-name">${name}</span>
        <span class="retrain-item-detail">XP gift slot ${i + 1}</span>
        <button type="button" class="retrain-btn" data-action="retrain-gift-xp" data-slot="${i}">
          Retrain Gift <em>(+5 XP)</em>
        </button>
      </div>
    `);
  });

  // ── Log ──────────────────────────────────────────────────────────────────
  const log = getRetrainLog();
  const logHtml = log.length > 0
    ? `<div class="retrain-history">
        <h4>Retrained This Session</h4>
        <ul class="retrain-log-list">
          ${log.map(entry => {
            const label = entry.type === 'mark'
              ? `${entry.skillName || entry.skillId} mark (${entry.source})`
              : `${entry.giftName || entry.giftId} gift (${entry.source})`;
            return `<li><span class="retrain-log-item">${label}</span> <span class="retrain-log-xp">+${entry.xpGained} XP</span></li>`;
          }).join('')}
        </ul>
      </div>`
    : '';

  // ── Assemble ──────────────────────────────────────────────────────────────
  const marksSection = markRows.length > 0
    ? `<div class="retrain-section">
        <h4>Skill Marks <span class="retrain-xp-tag">+2 XP each</span></h4>
        ${markRows.join('')}
      </div>`
    : `<div class="retrain-section retrain-empty">
        <h4>Skill Marks <span class="retrain-xp-tag">+2 XP each</span></h4>
        <p class="retrain-none">No marks to retrain.</p>
      </div>`;

  const giftsSection = giftRows.length > 0
    ? `<div class="retrain-section">
        <h4>Gifts <span class="retrain-xp-tag">+5 XP each</span></h4>
        <p class="retrain-note">Career starting gifts cannot be retrained.</p>
        ${giftRows.join('')}
      </div>`
    : `<div class="retrain-section retrain-empty">
        <h4>Gifts <span class="retrain-xp-tag">+5 XP each</span></h4>
        <p class="retrain-note">Career starting gifts cannot be retrained.</p>
        <p class="retrain-none">No retrain-able gifts on your sheet.</p>
      </div>`;

  $panel.html(`
    <h3 class="retrain-heading">Retraining
      <span class="retrain-gh-note">Game Host approval required</span>
    </h3>
    <p class="retrain-intro">
      Remove Marks or Gifts from your character sheet to gain Experience Points.
      <br>+2 XP per Mark retrained &nbsp;·&nbsp; +5 XP per Gift retrained
    </p>
    ${marksSection}
    ${giftsSection}
    ${logHtml}
  `);
}

// ── Events ─────────────────────────────────────────────────────────────────────

let _bound = false;

function bindRetrainEvents(onAfterRetrain) {
  if (_bound) return;
  _bound = true;

  $(document).on('click.cgretrain', '[data-action^="retrain-"]', function() {
    const action  = String($(this).data('action') || '');
    const skillId = String($(this).data('skill-id') || '');
    const slot    = parseInt($(this).data('slot'), 10);
    let changed   = false;

    if (action === 'retrain-mark-creation') {
      changed = retrainCreationMark(skillId);
    } else if (action === 'retrain-mark-xp') {
      changed = retrainXpMark(skillId);
    } else if (action === 'retrain-gift-free') {
      changed = retrainFreeGift(slot);
    } else if (action === 'retrain-gift-xp') {
      changed = retrainXpGift(slot);
    }

    if (changed) {
      if (typeof onAfterRetrain === 'function') onAfterRetrain();
      renderRetrainPanel();
    }
  });
}

// ── Public API ─────────────────────────────────────────────────────────────────

const RetrainAPI = {
  render:     renderRetrainPanel,
  bindEvents: bindRetrainEvents,
  getLog:     getRetrainLog,
  getPenalty: getRetrainPenalty,
};

export default RetrainAPI;
