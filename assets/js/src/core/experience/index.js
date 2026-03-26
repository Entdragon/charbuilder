// assets/js/src/core/experience/index.js
//
// Experience Points manager.
// - initWidget()    → called when Details tab is active; binds +/− controls
// - renderXpGifts() → called when Gifts tab is active; renders extra gift dropdowns

import FormBuilderAPI from '../formBuilder';
import { marksToDice } from '../../utils/marks-dice.js';
import FreeChoices from '../gifts/free-choices.js';

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

function calcSpent() {
  return getXpMarksBudget() * XP_MARK_COST + getXpGiftSlots() * XP_GIFT_COST;
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
    // Remove the last gift from the array
    const gifts = getXpGifts().slice(0, newSlots);
    setData({ xpGiftSlots: newSlots, xpGifts: gifts });
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

  const selected = getXpGifts(); // array of gift ID strings

  let html = `<div class="xp-gift-label">Experience Gifts (${slots} slot${slots > 1 ? 's' : ''})</div>`;

  for (let i = 0; i < slots; i++) {
    const curId = String(selected[i] || '').trim();

    // Use FreeChoices eligibility filtering when its catalogue is ready
    let eligible;
    if (Array.isArray(FreeChoices._allGifts) && FreeChoices._allGifts.length > 0) {
      eligible = FreeChoices.getEligibleGiftsForSlot(selected, i);
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
      </div>
    `;
  }

  $container.html(html);

  // Bind change events for XP gift selects
  $container.off('change.cgxpgifts').on('change.cgxpgifts', '.xp-gift-select', function() {
    const slot = parseInt($(this).data('xp-slot'), 10);
    const val  = String($(this).val() || '');
    const arr  = getXpGifts().slice();
    while (arr.length <= slot) arr.push('');
    arr[slot] = val;
    while (arr.length && !arr[arr.length - 1]) arr.pop();
    setData({ xpGifts: arr });
    // Notify all systems that care about gift selection (soak, traits, trappings, battle)
    emitXpGiftChanged();
    // Re-render so other slots' eligibility lists and effect texts update
    renderXpGifts();
  });
}

// ── Public API ────────────────────────────────────────────────────────────────

const ExperienceAPI = {
  initWidget() {
    bindWidgetEvents();
  },

  renderXpGifts,

  // Called by skills/render.js to get XP mark budget info
  getXpMarksBudget,
  getXpSkillMarks,
  calcAvailable,
};

export default ExperienceAPI;
