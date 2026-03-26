// assets/js/src/core/experience/index.js
//
// Experience Points manager.
// - initWidget()    → called when Details tab is active; binds +/− controls
// - renderXpGifts() → called when Gifts tab is active; renders extra gift dropdowns

import FormBuilderAPI from '../formBuilder';
import { marksToDice } from '../../utils/marks-dice.js';
import FreeChoices from '../gifts/free-choices.js';
import RetrainAPI from './retrain.js';

const $ = window.jQuery;

const XP_MARK_COST = 4;
const XP_GIFT_COST = 10;

// ── State helpers ─────────────────────────────────────────────────────────────

function getData()           { return FormBuilderAPI._data || {}; }
function getXpEarned()       { return parseInt(getData().experience_points, 10) || 0; }
function getXpMarksBudget()  { return parseInt(getData().xpMarksBudget,     10) || 0; }
function getXpGiftSlots()    { return parseInt(getData().xpGiftSlots,        10) || 0; }
function getXpGifts()        { return Array.isArray(getData().xpGifts) ? getData().xpGifts : []; }
function getXpSkillMarks()   { return getData().xpSkillMarks || {}; }

function getXpGiftQuals() {
  const v = getData().xp_gift_quals;
  return (v && typeof v === 'object' && !Array.isArray(v)) ? v : {};
}

function setXpGiftQuals(map) {
  setData({ xp_gift_quals: map });
}

function getRetrainPenalty() { return parseInt(getData().retrainPenalty, 10) || 0; }

function calcSpent() {
  return getXpMarksBudget() * XP_MARK_COST + getXpGiftSlots() * XP_GIFT_COST + getRetrainPenalty();
}

function calcAvailable() {
  return getXpEarned() - calcSpent();
}

function setData(patch) {
  FormBuilderAPI._data = { ...(FormBuilderAPI._data || {}), ...patch };
}

// ── Widget (Details tab) ──────────────────────────────────────────────────────

let _widgetBound = false;

function updateWidgetDisplay() {
  const earned  = getXpEarned();
  const spent   = calcSpent();
  const avail   = earned - spent;
  const budget  = getXpMarksBudget();
  const slots   = getXpGiftSlots();

  $('#xp-available-display').text(avail);
  $('#xp-spent-display').text(spent);
  $('#xp-marks-bought').text(budget);
  $('#xp-gifts-bought').text(slots);

  const $statAvail = $('#xp-stat-avail');
  $statAvail.toggleClass('xp-stat--negative', avail < 0);
  $statAvail.toggleClass('xp-stat--ok', avail >= 0);

  // Disable + buttons when not enough XP
  $('#xp-marks-plus').prop('disabled', avail < XP_MARK_COST);
  $('#xp-gifts-plus').prop('disabled', avail < XP_GIFT_COST);

  // Disable − buttons when at zero
  $('#xp-marks-minus').prop('disabled', budget <= 0);
  $('#xp-gifts-minus').prop('disabled', slots <= 0);
}

function bindWidgetEvents() {
  if (_widgetBound) {
    updateWidgetDisplay();
    return;
  }
  _widgetBound = true;

  // XP earned input
  $(document).on('input.cgxp change.cgxp', '#xp-earned', function() {
    const val = Math.max(0, parseInt($(this).val(), 10) || 0);
    setData({ experience_points: val });
    updateWidgetDisplay();
  });

  // Buy a mark (+4 XP)
  $(document).on('click.cgxp', '#xp-marks-plus', function() {
    if (calcAvailable() < XP_MARK_COST) return;
    setData({ xpMarksBudget: getXpMarksBudget() + 1 });
    updateWidgetDisplay();
    triggerSkillsRefresh();
  });

  // Refund a mark
  $(document).on('click.cgxp', '#xp-marks-minus', function() {
    const budget = getXpMarksBudget();
    if (budget <= 0) return;
    const newBudget = budget - 1;

    // Trim placed marks if they exceed new budget
    const placed   = getXpSkillMarks();
    const total    = Object.values(placed).reduce((s, v) => s + (parseInt(v, 10) || 0), 0);
    const newPlaced = { ...placed };
    if (total > newBudget) {
      // Remove from the last-placed skill
      const keys = Object.keys(newPlaced).filter(k => parseInt(newPlaced[k], 10) > 0);
      for (let i = keys.length - 1; i >= 0 && Object.values(newPlaced).reduce((s, v) => s + (parseInt(v, 10) || 0), 0) > newBudget; i--) {
        const k = keys[i];
        newPlaced[k] = Math.max(0, (parseInt(newPlaced[k], 10) || 0) - 1);
        if (newPlaced[k] === 0) delete newPlaced[k];
      }
    }

    setData({ xpMarksBudget: newBudget, xpSkillMarks: newPlaced });
    updateWidgetDisplay();
    triggerSkillsRefresh();
  });

  // Buy a gift slot (+10 XP)
  $(document).on('click.cgxp', '#xp-gifts-plus', function() {
    if (calcAvailable() < XP_GIFT_COST) return;
    setData({ xpGiftSlots: getXpGiftSlots() + 1 });
    updateWidgetDisplay();
  });

  // Refund a gift slot
  $(document).on('click.cgxp', '#xp-gifts-minus', function() {
    const slots = getXpGiftSlots();
    if (slots <= 0) return;
    const newSlots = slots - 1;
    // Remove the last gift from the array and its qual data
    const gifts = getXpGifts().slice(0, newSlots);
    const quals = { ...getXpGiftQuals() };
    const removedQuals = quals[String(newSlots)] || {};
    delete quals[String(newSlots)];
    // Remove any qual values that were only used by the removed slot
    Object.entries(removedQuals).forEach(([type, value]) => {
      if (value) FreeChoices.qualRemoveIfSafe(type, value, quals);
    });
    setData({ xpGiftSlots: newSlots, xpGifts: gifts, xp_gift_quals: quals });
    updateWidgetDisplay();
  });

  updateWidgetDisplay();
}

function triggerSkillsRefresh() {
  try {
    $(document).trigger('cg:xp:marks:changed');
  } catch (_) {}
}

// ── XP Gifts (Gifts tab) ──────────────────────────────────────────────────────

let _giftsCache = null;

async function fetchGiftList() {
  if (_giftsCache) return _giftsCache;

  const env = window.CG_AJAX || {};
  const url = env.ajax_url || '/api/ajax';

  try {
    const body = new URLSearchParams({ action: 'cg_get_free_gifts', nonce: env.nonce || '1' });
    const res  = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body
    });
    const json = await res.json();
    const list = (json.success && Array.isArray(json.data)) ? json.data : [];
    _giftsCache = list
      .map(g => ({
        id:   String(g.ct_id || g.id || ''),
        name: String(g.ct_gift_name || g.name || g.gift_name || '')
      }))
      .filter(g => g.id && g.name)
      .sort((a, b) => a.name.localeCompare(b.name));
    return _giftsCache;
  } catch (_) {
    return [];
  }
}

function xpGiftEffectHtml(giftId) {
  if (!giftId) return '';
  const g = (FreeChoices._allGifts || []).find(x => String(x.ct_id || x.id || '') === String(giftId));
  if (!g) return '';
  const desc = String(g.effect ?? g.effect_description ?? g.ct_gifts_effect_description ?? '').trim();
  if (!desc) return '';
  const clean = desc.replace(/@@\w+[:\s]*/g, ' ').replace(/\s+/g, ' ').trim();
  const short = clean.length > 240 ? clean.slice(0, 237) + '…' : clean;
  return `<div class="cg-default-gift-effect xp-gift-effect">${short.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>`;
}

/**
 * Build the HTML for qual sub-selectors needed by a given XP gift slot.
 * Excludes values already chosen in other sources (base language, free-gift
 * qual selectors, and other XP gift slots).
 */
function xpGiftQualHtml(slotIndex, giftObj, xpQualMap) {
  if (!giftObj) return '';
  const types = FreeChoices.detectQualTypes(giftObj);
  if (!types.length) return '';

  const slotKey = String(slotIndex);
  const curQuals = (xpQualMap[slotKey] && typeof xpQualMap[slotKey] === 'object')
    ? xpQualMap[slotKey] : {};

  return types.map(type => {
    const curVal = String(curQuals[type] || '').trim();

    // --- Build exclusion list ---
    const excludeValues = [];

    // 1) Base language from QualState index[0]
    if (type === 'language') {
      const base = FreeChoices.getBaseLanguage();
      if (base) excludeValues.push(base);
    }

    // 2) Same type already chosen in free-gift qual slots
    const freeQuals = FreeChoices.getFreeGiftQuals();
    [0, 1, 2].forEach(j => {
      const v = String((freeQuals[String(j)] || {})[type] || '').trim();
      if (v) excludeValues.push(v);
    });

    // 3) Same type already chosen in OTHER XP gift slots
    Object.entries(xpQualMap).forEach(([k, slotObj]) => {
      if (k === slotKey) return;
      const v = String((slotObj || {})[type] || '').trim();
      if (v) excludeValues.push(v);
    });

    // Use class name prefixed with 'xp-' so we don't conflict with free-gift handlers
    const html = FreeChoices.buildQualHtml({
      slot: slotIndex,
      type,
      value: curVal,
      excludeValues,
    });

    // Replace the class names so our event handlers target xp-qual selects
    return html
      .replace(/cg-free-qual-select/g, 'cg-xp-qual-select')
      .replace(/cg-qual-custom-input cg-free-select/g, 'cg-xp-qual-custom-input');
  }).join('\n');
}

function emitXpGiftChanged() {
  // Notify all systems that listen for gift changes so follow-on effects
  // (soak pools, trait boosts, trappings, battle pools) recompute.
  try {
    const all = getXpGifts();
    const detail = { xpGifts: all, free_gifts: all, source: 'xp-gifts' };
    document.dispatchEvent(new CustomEvent('cg:free-gift:changed',    { detail }));
    document.dispatchEvent(new CustomEvent('cg:freechoices:changed',  { detail }));
    document.dispatchEvent(new CustomEvent('cg:gifts:changed',        { detail }));
    if ($) {
      $(document).trigger('cg:free-gift:changed',   [detail]);
      $(document).trigger('cg:freechoices:changed', [detail]);
      $(document).trigger('cg:gifts:changed',       [detail]);
    }
  } catch (_) {}
}

async function renderXpGifts() {
  const $container = $('#cg-xp-gifts');
  if (!$container.length) return;

  const slots = getXpGiftSlots();
  if (slots === 0) {
    $container.html('<p class="xp-gifts-empty">Buy extra gift slots on the Details tab using Experience Points.</p>');
    return;
  }

  // Ensure FreeChoices has its gift list loaded; fall back to raw list if not
  const fcReady = Array.isArray(FreeChoices._allGifts) && FreeChoices._allGifts.length > 0;
  if (!fcReady) {
    await fetchGiftList(); // warm the cache
  }

  const selected  = getXpGifts();     // array of gift ID strings
  const xpQualMap = getXpGiftQuals(); // { "0": { language: "Calabrian" }, ... }

  let html = `<div class="xp-gift-label">Experience Gifts (${slots} slot${slots > 1 ? 's' : ''})</div>`;

  for (let i = 0; i < slots; i++) {
    const curId = String(selected[i] || '').trim();

    // Use FreeChoices eligibility filtering when its catalogue is ready
    let eligible;
    if (Array.isArray(FreeChoices._allGifts) && FreeChoices._allGifts.length > 0) {
      // Merge free-choice selections so non-repeatable gifts already picked there are excluded
      const builderData = getData();
      const freeChoiceIds = [
        ...(Array.isArray(builderData.free_gifts) ? builderData.free_gifts : []),
        builderData.free_gift_1, builderData.free_gift_2, builderData.free_gift_3,
      ];
      const mergedSelected = [...selected, ...freeChoiceIds.filter(Boolean)];
      // Adjust slotIndex to still refer to the XP slot within mergedSelected
      eligible = FreeChoices.getEligibleGiftsForSlot(mergedSelected, i);
    } else {
      // Fallback: unfiltered list from cache
      const raw = await fetchGiftList();
      eligible = raw.map(g => ({ ct_id: g.id, ct_gift_name: g.name }));
    }

    // Always keep the currently saved gift visible even if it no longer qualifies
    const curGift = curId
      ? (FreeChoices._allGifts || []).find(g => String(g.ct_id || g.id || '') === curId) || null
      : null;

    const seen = new Set();
    const options = []
      .concat(curGift ? [{ _saved: true, id: curId, name: String(curGift.ct_gift_name || curGift.name || curId) }] : [])
      .concat(eligible.map(g => ({ id: String(g.ct_id || g.id || ''), name: String(g.ct_gift_name || g.name || '') })))
      .filter(o => o.id && o.name)
      .filter(o => { if (seen.has(o.id)) return false; seen.add(o.id); return true; })
      .map(o => {
        const sel    = o.id === curId ? ' selected' : '';
        const suffix = o._saved ? ' (saved)' : '';
        return `<option value="${o.id}"${sel}>${o.name}${suffix}</option>`;
      })
      .join('\n');

    // Show effect description for the currently selected gift
    const effectHtml = xpGiftEffectHtml(curId);

    // Qual sub-selectors (language, literacy, etc.)
    const qualHtml = xpGiftQualHtml(i, curGift, xpQualMap);

    html += `
      <div class="cg-free-slot xp-gift-slot" data-xp-slot="${i}">
        <div style="display:flex; align-items:center; gap:6px;">
          <span style="font-weight:600; white-space:nowrap; font-size:0.88rem; color:var(--cg-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Gift</span>
          <select id="cg-xp-gift-${i}" class="cg-free-gift-select xp-gift-select" data-xp-slot="${i}">
            <option value="">— Select a gift —</option>
            ${options}
          </select>
        </div>
        ${effectHtml}
        <div class="xp-gift-qual-wrap" data-xp-slot="${i}">${qualHtml}</div>
      </div>
    `;
  }

  $container.html(html);

  // ── Bind change events for XP gift selects ───────────────────────────────
  $container.off('change.cgxpgifts').on('change.cgxpgifts', '.xp-gift-select', function() {
    const slot    = parseInt($(this).data('xp-slot'), 10);
    const val     = String($(this).val() || '');
    const arr     = getXpGifts().slice();
    while (arr.length <= slot) arr.push('');
    arr[slot] = val;
    while (arr.length && !arr[arr.length - 1]) arr.pop();

    // Clear qual for this slot (gift changed — old qual irrelevant)
    const quals   = { ...getXpGiftQuals() };
    const oldQuals = quals[String(slot)] || {};
    Object.entries(oldQuals).forEach(([type, value]) => {
      if (value) {
        const remaining = { ...quals };
        delete remaining[String(slot)];
        FreeChoices.qualRemoveIfSafe(type, value, remaining);
      }
    });
    delete quals[String(slot)];

    setData({ xpGifts: arr, xp_gift_quals: quals });
    emitXpGiftChanged();
    renderXpGifts();
  });

  // ── Bind qual SELECT change ───────────────────────────────────────────────
  $container.off('change.cgxpqual').on('change.cgxpqual', '.cg-xp-qual-select', function() {
    const slot  = parseInt($(this).data('slot'), 10);
    const type  = String($(this).data('qtype') || '');
    const val   = String($(this).val() || '');
    const slotKey = String(slot);

    const quals = { ...getXpGiftQuals() };
    if (!quals[slotKey] || typeof quals[slotKey] !== 'object') quals[slotKey] = {};

    const oldVal = String(quals[slotKey][type] || '').trim();

    if (val === '__other__') {
      // Show the custom input, don't persist yet
      const $wrap = $container.find(`.xp-gift-qual-wrap[data-xp-slot="${slot}"]`);
      $wrap.find(`input.cg-xp-qual-custom-input[data-slot="${slot}"][data-qtype="${type}"]`).css('display', '');
      return;
    }

    // Remove old qual from QualState if changed
    if (oldVal && oldVal !== val) {
      const remaining = { ...quals };
      const remainingSlot = { ...(remaining[slotKey] || {}) };
      delete remainingSlot[type];
      remaining[slotKey] = remainingSlot;
      FreeChoices.qualRemoveIfSafe(type, oldVal, remaining);
    }

    quals[slotKey][type] = val;
    setXpGiftQuals(quals);

    if (val) FreeChoices.qualAdd(type, val);

    // Update qual wrappers in other slots without full re-render
    _updateOtherQualWraps(slot);
    // Trigger free-choice re-render so those exclusions also update
    try { FreeChoices._scheduleRender?.('xp-qual-changed'); } catch (_) {}
  });

  // ── Bind qual custom INPUT ────────────────────────────────────────────────
  $container.off('input.cgxpqualcustom').on('input.cgxpqualcustom', '.cg-xp-qual-custom-input', function() {
    const slot    = parseInt($(this).data('slot'), 10);
    const type    = String($(this).data('qtype') || '');
    const val     = String($(this).val() || '').trim();
    const slotKey = String(slot);

    const quals   = { ...getXpGiftQuals() };
    if (!quals[slotKey] || typeof quals[slotKey] !== 'object') quals[slotKey] = {};

    const oldVal  = String(quals[slotKey][type] || '').trim();
    if (oldVal && oldVal !== val) {
      const remaining = { ...quals };
      const rs = { ...(remaining[slotKey] || {}) };
      delete rs[type];
      remaining[slotKey] = rs;
      FreeChoices.qualRemoveIfSafe(type, oldVal, remaining);
    }

    quals[slotKey][type] = val;
    setXpGiftQuals(quals);

    if (val) FreeChoices.qualAdd(type, val);
    _updateOtherQualWraps(slot);
  });
}

/**
 * Re-render qual wrappers for all XP slots EXCEPT the one that just changed,
 * so their exclusion lists reflect the new selection without disturbing the
 * gift select dropdown that the user may currently have open.
 */
function _updateOtherQualWraps(changedSlot) {
  try {
    const slots     = getXpGiftSlots();
    const selected  = getXpGifts();
    const xpQualMap = getXpGiftQuals();
    const $container = $('#cg-xp-gifts');

    for (let i = 0; i < slots; i++) {
      if (i === changedSlot) continue;
      const curId  = String(selected[i] || '').trim();
      const curGift = curId
        ? (FreeChoices._allGifts || []).find(g => String(g.ct_id || g.id || '') === curId) || null
        : null;
      const qualHtml = xpGiftQualHtml(i, curGift, xpQualMap);
      $container.find(`.xp-gift-qual-wrap[data-xp-slot="${i}"]`).html(qualHtml);
    }
  } catch (_) {}
}

// ── Public API ────────────────────────────────────────────────────────────────

const ExperienceAPI = {
  initWidget() {
    bindWidgetEvents();
    // Bind retrain events once; re-render panel each time the widget is initialised
    // (which happens on each Details tab visit, so the panel stays fresh).
    RetrainAPI.bindEvents(() => {
      updateWidgetDisplay();
      // Trigger a skills re-render so retrained marks are reflected immediately.
      try { $(document).trigger('cg:skill:marks:changed'); } catch (_) {}
      // Trigger free-choices re-render so retrained gift slots clear.
      try { $(document).trigger('cg:gifts:changed'); } catch (_) {}
    });
    RetrainAPI.render();
  },

  renderXpGifts,

  // Called by skills/render.js to get XP mark budget info
  getXpMarksBudget,
  getXpSkillMarks,
  calcAvailable,

  // Exposed for save/load
  getXpGiftQuals,

  // Retrain sub-system
  RetrainAPI,
};

export default ExperienceAPI;
