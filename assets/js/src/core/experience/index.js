// assets/js/src/core/experience/index.js
// XP (Experience Points) module.
// Manages XP earned, marks bought with XP, and gifts bought with XP.

import FormBuilderAPI from '../formBuilder';

const $ = window.jQuery;

const XP_MARK_COST = 4;
const XP_GIFT_COST = 10;
const MAX_MARKS    = 3;

const MARK_DIE = { 1: 'd4', 2: 'd6', 3: 'd8' };

// ── State helpers ─────────────────────────────────────────────────────────────

function getData()           { return FormBuilderAPI._data || {}; }
function getXpEarned()       { return parseInt(getData().experience_points, 10) || 0; }
function getXpSkillMarks()   { return getData().xpSkillMarks || {}; }
function getXpGifts()        { return Array.isArray(getData().xpGifts) ? getData().xpGifts : []; }
function getCreationMarks()  { return getData().skillMarks || {}; }

function setXpEarned(val) {
  FormBuilderAPI._data = { ...FormBuilderAPI._data, experience_points: Math.max(0, parseInt(val, 10) || 0) };
}

function setXpSkillMarks(marks) {
  FormBuilderAPI._data = { ...FormBuilderAPI._data, xpSkillMarks: { ...marks } };
}

function setXpGifts(gifts) {
  FormBuilderAPI._data = { ...FormBuilderAPI._data, xpGifts: [...gifts] };
}

// Total XP marks (creation + XP) for a skill, capped at MAX_MARKS
function totalMarks(skillId) {
  const id = String(skillId);
  const c  = parseInt(getCreationMarks()[id], 10) || 0;
  const x  = parseInt(getXpSkillMarks()[id],  10) || 0;
  return Math.min(MAX_MARKS, c + x);
}

// How many XP marks are on a skill (capped so total doesn't exceed MAX_MARKS)
function xpMarksFor(skillId) {
  const id = String(skillId);
  const c  = parseInt(getCreationMarks()[id], 10) || 0;
  const x  = parseInt(getXpSkillMarks()[id],  10) || 0;
  return Math.min(x, Math.max(0, MAX_MARKS - c));
}

function calcMarksCost() {
  const marks = getXpSkillMarks();
  return Object.values(marks).reduce((sum, v) => sum + ((parseInt(v, 10) || 0) * XP_MARK_COST), 0);
}

function calcGiftsCost() {
  return getXpGifts().length * XP_GIFT_COST;
}

function calcAvailable() {
  return getXpEarned() - calcMarksCost() - calcGiftsCost();
}

// ── Gift list fetching ────────────────────────────────────────────────────────

let _giftsCache = null;

async function fetchGiftList() {
  if (_giftsCache) return _giftsCache;

  const env = window.CG_AJAX || {};
  const url = env.ajax_url || '/api/ajax';

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'cg_get_free_gifts', nonce: env.nonce || '1' })
    });
    const json = await res.json();
    const list = (json.success && Array.isArray(json.data)) ? json.data : [];
    _giftsCache = list.map(g => ({
      id:   String(g.ct_id || g.id || ''),
      name: String(g.ct_gift_name || g.name || g.gift_name || '')
    })).filter(g => g.id && g.name).sort((a, b) => a.name.localeCompare(b.name));
    return _giftsCache;
  } catch (_) {
    return [];
  }
}

// ── Render ────────────────────────────────────────────────────────────────────

function renderBalance() {
  const mc = calcMarksCost();
  const gc = calcGiftsCost();
  const tc = mc + gc;
  const av = getXpEarned() - tc;

  const $earned = $('#xp-earned');
  if ($earned.length && $earned.val() !== String(getXpEarned())) {
    $earned.val(getXpEarned());
  }

  $('#xp-marks-cost').text(mc);
  $('#xp-gifts-cost').text(gc);
  $('#xp-total-cost').text(tc);
  $('#xp-available').text(av);

  const $avail = $('#xp-avail-cell');
  $avail.toggleClass('xp-balance-cell--negative', av < 0);
  $avail.toggleClass('xp-balance-cell--ok',       av >= 0);
}

function renderMarksList() {
  const skills    = window.CG_SKILLS_LIST || [];
  const xpMarks   = getXpSkillMarks();
  const skillsById = {};
  skills.forEach(s => { skillsById[String(s.id)] = s.name; });

  // Show only skills that have XP marks
  const activeSkills = Object.keys(xpMarks)
    .filter(id => parseInt(xpMarks[id], 10) > 0)
    .map(id => ({ id, name: skillsById[id] || `Skill #${id}` }))
    .sort((a, b) => a.name.localeCompare(b.name));

  const $list = $('#xp-marks-list').empty();
  if (!activeSkills.length) {
    $list.append('<p class="xp-empty">No extra marks yet. Use the dropdown below to add some.</p>');
    return;
  }

  activeSkills.forEach(({ id, name }) => {
    const creationMk = parseInt(getCreationMarks()[id], 10) || 0;
    const xpMk       = xpMarksFor(id);
    const maxMore     = MAX_MARKS - creationMk;

    let btnsHtml = '';
    for (let n = 1; n <= maxMore; n++) {
      const active = n <= xpMk ? ' active' : '';
      btnsHtml += `<button type="button" class="skill-mark-btn xp-mark-btn${active}"
        data-skill-id="${id}" data-mark="${n}" title="${n} mark${n > 1 ? 's' : ''} (${n * XP_MARK_COST} XP)"></button>`;
    }

    const totalDie = MARK_DIE[Math.min(MAX_MARKS, creationMk + xpMk)] || '—';
    const xpCost   = xpMk * XP_MARK_COST;

    $list.append(`
      <div class="xp-mark-row" data-skill-id="${id}">
        <span class="xp-mark-name">${name}</span>
        <div class="xp-mark-info">
          <span class="xp-mark-creation" title="Starting marks: ${creationMk}">Start: ${creationMk > 0 ? MARK_DIE[creationMk] : '—'}</span>
          <div class="xp-mark-btns">${btnsHtml}</div>
          <span class="xp-mark-die" title="Total die">= ${totalDie}</span>
          <span class="xp-mark-cost">${xpCost} XP</span>
          <button type="button" class="xp-remove-mark btn-ghost" data-skill-id="${id}" title="Remove XP marks for this skill">✕</button>
        </div>
      </div>
    `);
  });
}

function renderGiftsList() {
  const gifts = getXpGifts();
  const $list = $('#xp-gifts-list').empty();

  if (!gifts.length) {
    $list.append('<p class="xp-empty">No gifts purchased yet. Use the dropdown below to add one.</p>');
    return;
  }

  gifts.forEach((gift, idx) => {
    $list.append(`
      <div class="xp-gift-row" data-index="${idx}">
        <span class="xp-gift-name">${gift.name}</span>
        <span class="xp-gift-cost">${XP_GIFT_COST} XP</span>
        <button type="button" class="xp-remove-gift btn-ghost" data-index="${idx}" title="Remove gift">✕</button>
      </div>
    `);
  });
}

function render() {
  renderBalance();
  renderMarksList();
  renderGiftsList();
}

// ── Populate dropdowns ────────────────────────────────────────────────────────

function populateSkillDropdown() {
  const skills  = window.CG_SKILLS_LIST || [];
  const $select = $('#xp-add-skill-select').empty();
  $select.append('<option value="">— Choose a skill to mark —</option>');

  skills
    .slice()
    .sort((a, b) => String(a.name).localeCompare(String(b.name)))
    .forEach(s => {
      const id = String(s.id);
      const creationMk = parseInt(getCreationMarks()[id], 10) || 0;
      const maxMore    = MAX_MARKS - creationMk;
      if (maxMore <= 0) return; // already capped
      $select.append(`<option value="${id}">${s.name}</option>`);
    });
}

async function populateGiftDropdown() {
  const gifts   = await fetchGiftList();
  const $select = $('#xp-add-gift-select').empty();
  $select.append('<option value="">— Choose a gift —</option>');
  gifts.forEach(g => {
    $select.append(`<option value="${g.id}" data-name="${g.name}">${g.name}</option>`);
  });
}

// ── Event binding ─────────────────────────────────────────────────────────────

let _bound = false;

function bindEvents() {
  if (_bound) return;
  _bound = true;

  // XP earned input
  $(document).on('input.cgxp change.cgxp', '#xp-earned', function () {
    setXpEarned(parseInt($(this).val(), 10) || 0);
    render();
  });

  // Mark buttons (toggle XP mark level for a skill)
  $(document).on('click.cgxp', '.xp-mark-btn', function () {
    const id = String($(this).data('skill-id'));
    const n  = parseInt($(this).data('mark'), 10);
    const cur = xpMarksFor(id);
    const marks = { ...getXpSkillMarks() };
    // Clicking the current level removes that level; otherwise sets to n
    marks[id] = (cur === n) ? Math.max(0, n - 1) : n;
    if (marks[id] <= 0) delete marks[id];
    setXpSkillMarks(marks);
    render();
  });

  // Remove all XP marks for a skill
  $(document).on('click.cgxp', '.xp-remove-mark', function () {
    const id = String($(this).data('skill-id'));
    const marks = { ...getXpSkillMarks() };
    delete marks[id];
    setXpSkillMarks(marks);
    render();
  });

  // Skill select change → enable/disable add button
  $(document).on('change.cgxp', '#xp-add-skill-select', function () {
    $('#xp-add-skill-btn').prop('disabled', !$(this).val());
  });

  // Gift select change → enable/disable add button
  $(document).on('change.cgxp', '#xp-add-gift-select', function () {
    $('#xp-add-gift-btn').prop('disabled', !$(this).val());
  });

  // Add skill mark button
  $(document).on('click.cgxp', '#xp-add-skill-btn', function () {
    const id = String($('#xp-add-skill-select').val() || '');
    if (!id) return;

    const creationMk = parseInt(getCreationMarks()[id], 10) || 0;
    const maxMore    = MAX_MARKS - creationMk;
    if (maxMore <= 0) return;

    // Add 1 XP mark (first available slot)
    const marks = { ...getXpSkillMarks() };
    const cur   = parseInt(marks[id], 10) || 0;
    const next  = Math.min(maxMore, cur + 1);
    if (next > cur) marks[id] = next;
    setXpSkillMarks(marks);

    $('#xp-add-skill-select').val('');
    $('#xp-add-skill-btn').prop('disabled', true);
    populateSkillDropdown();
    render();
  });

  // Add XP gift
  $(document).on('click.cgxp', '#xp-add-gift-btn', function () {
    const $opt = $('#xp-add-gift-select option:selected');
    const id   = String($opt.val() || '');
    const name = String($opt.data('name') || $opt.text() || '');
    if (!id || !name) return;

    const gifts = [...getXpGifts(), { id, name }];
    setXpGifts(gifts);

    $('#xp-add-gift-select').val('');
    $('#xp-add-gift-btn').prop('disabled', true);
    render();
  });

  // Remove XP gift
  $(document).on('click.cgxp', '.xp-remove-gift', function () {
    const idx   = parseInt($(this).data('index'), 10);
    const gifts = getXpGifts().filter((_, i) => i !== idx);
    setXpGifts(gifts);
    render();
  });
}

// ── Public API ────────────────────────────────────────────────────────────────

const ExperienceAPI = {
  async init() {
    bindEvents();
    populateSkillDropdown();
    populateGiftDropdown(); // async, fills in as gifts load
    render();
  }
};

export default ExperienceAPI;
