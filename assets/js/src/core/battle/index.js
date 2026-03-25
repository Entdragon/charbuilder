// assets/js/src/core/battle/index.js
//
// Battle Array module — Ironclaw character sheet battle stats.
// Computes Initiative/Dodge/Soak pools from trait values.
// Manages structured weapon and armor rows.
// Saves/restores via FormBuilderAPI._data.weapons / ._data.armor.

import FormBuilderAPI        from '../formBuilder/index.js';
import { marksToDice }       from '../../utils/marks-dice.js';

const $ = window.jQuery;

const WOUND_LEVELS = ['Hurt', 'Injured', 'Mauled', 'Crippled', 'Dead'];


function escape(val) {
  return String(val == null ? '' : val)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function traitDie(key) {
  const dom = document.getElementById(`cg-${key}`);
  if (dom && dom.value) return dom.value;
  const d = FormBuilderAPI._data || {};
  return d[key] || '';
}

function poolString(...dice) {
  return dice.filter(Boolean).join(' + ') || '—';
}

// Parse a raw attack_dice string like "Body, Species, Brawling vs. defense"
// and replace known trait and skill keywords with actual die values.
// Traits are read live from the DOM; skills use skillMarks + marksToDice().
function resolveAttackPool(raw) {
  if (!raw) return '';

  // Trait keyword → current die value
  const traitMap = {
    'body':    traitDie('body'),
    'speed':   traitDie('speed'),
    'will':    traitDie('will'),
    'mind':    traitDie('mind'),
    'species': traitDie('trait_species'),
    'career':  traitDie('trait_career'),
  };

  // Skill name → die value derived from character's skill marks
  const skillsList = FormBuilderAPI._data?.skillsList || [];
  const skillMarks = FormBuilderAPI._data?.skillMarks || {};
  const skillDieMap = {};
  for (const skill of skillsList) {
    const marks = parseInt(skillMarks[skill.id], 10) || 0;
    if (marks > 0) {
      skillDieMap[skill.name.toLowerCase()] = marksToDice(marks);
    }
  }

  const vsIdx    = raw.toLowerCase().indexOf(' vs.');
  const poolPart = vsIdx > -1 ? raw.slice(0, vsIdx) : raw;
  const parts    = poolPart.split(',').map(s => s.trim()).filter(Boolean);
  const dice     = parts.map(p => traitMap[p.toLowerCase()] || skillDieMap[p.toLowerCase()] || p).filter(Boolean);
  return dice.join(' + ');
}

function buildCombatPools() {
  const speed = traitDie('speed');
  const will  = traitDie('will');
  const body  = traitDie('body');
  return {
    initiative: poolString(speed, will),
    dodge:      poolString(speed, will),
    soak:       poolString(body),
  };
}

function renderPoolsSection(pools) {
  return `
    <div class="cg-battle-pools">
      <h4 class="cg-battle-subhead">Computed Battle Pools</h4>
      <div class="cg-battle-pool-grid">
        <div class="cg-pool-block">
          <span class="cg-pool-label">Initiative</span>
          <span class="cg-pool-dice" id="cg-battle-initiative">${escape(pools.initiative)}</span>
          <span class="cg-pool-note">(Speed + Will)</span>
        </div>
        <div class="cg-pool-block">
          <span class="cg-pool-label">Dodge</span>
          <span class="cg-pool-dice" id="cg-battle-dodge">${escape(pools.dodge)}</span>
          <span class="cg-pool-note">(Speed + Will)</span>
        </div>
        <div class="cg-pool-block">
          <span class="cg-pool-label">Soak</span>
          <span class="cg-pool-dice" id="cg-battle-soak">${escape(pools.soak)}</span>
          <span class="cg-pool-note">(Body)</span>
        </div>
      </div>
      <div class="cg-wound-track">
        <h4 class="cg-battle-subhead">Wound Track</h4>
        <div class="cg-wound-levels">
          ${WOUND_LEVELS.map(w => `
            <div class="cg-wound-level">
              <span class="cg-wound-box"></span>
              <span class="cg-wound-name">${w}</span>
            </div>
          `).join('')}
        </div>
      </div>
    </div>
  `;
}

function weaponRowHtml(w = {}, idx) {
  // For trapping-sourced weapons, compute the attack pool from trait dice at render time
  // (the DOM is fully ready when the battle tab opens, so traitDie() returns live values).
  const attackVal = w._attack_dice_raw
    ? resolveAttackPool(w._attack_dice_raw)
    : (w.attack || '');
  return `
    <tr class="cg-weapon-row" data-idx="${idx}">
      <td><input class="cg-battle-input cg-weapon-name"   value="${escape(w.name   || '')}" placeholder="e.g. Short Sword" /></td>
      <td><input class="cg-battle-input cg-weapon-attack" value="${escape(attackVal)}"       placeholder="e.g. d6+d8" /></td>
      <td><input class="cg-battle-input cg-weapon-damage" value="${escape(w.damage  || '')}" placeholder="e.g. +1" /></td>
      <td><input class="cg-battle-input cg-weapon-range"  value="${escape(w.range   || '')}" placeholder="e.g. Close" /></td>
      <td><input class="cg-battle-input cg-weapon-notes"  value="${escape(w.notes   || '')}" placeholder="optional" /></td>
      <td><button type="button" class="cg-battle-remove-btn" data-target="weapon" data-idx="${idx}" title="Remove">✕</button></td>
    </tr>
  `;
}

function armorRowHtml(a = {}, idx) {
  return `
    <tr class="cg-armor-row" data-idx="${idx}">
      <td><input class="cg-battle-input cg-armor-name"    value="${escape(a.name    || '')}" placeholder="e.g. Leather Cuirass" /></td>
      <td><input class="cg-battle-input cg-armor-soak"    value="${escape(a.soak    || '')}" placeholder="e.g. d4" /></td>
      <td><input class="cg-battle-input cg-armor-penalty" value="${escape(a.penalty || '')}" placeholder="e.g. −1 Speed" /></td>
      <td><input class="cg-battle-input cg-armor-notes"   value="${escape(a.notes   || '')}" placeholder="optional" /></td>
      <td><button type="button" class="cg-battle-remove-btn" data-target="armor" data-idx="${idx}" title="Remove">✕</button></td>
    </tr>
  `;
}

function renderWeaponsTable(weapons) {
  const rows = (Array.isArray(weapons) ? weapons : []).map((w, i) => weaponRowHtml(w, i)).join('');
  return `
    <div class="cg-battle-section">
      <h4 class="cg-battle-subhead">Weapons</h4>
      <table class="cg-battle-table cg-weapons-table">
        <thead>
          <tr>
            <th>Name</th><th>Attack Pool</th><th>Damage</th><th>Range</th><th>Notes</th><th></th>
          </tr>
        </thead>
        <tbody id="cg-weapons-tbody">${rows}</tbody>
      </table>
      <button type="button" class="cg-battle-add-btn" id="cg-add-weapon">+ Add Weapon</button>
    </div>
  `;
}

function renderArmorTable(armor) {
  const rows = (Array.isArray(armor) ? armor : []).map((a, i) => armorRowHtml(a, i)).join('');
  return `
    <div class="cg-battle-section">
      <h4 class="cg-battle-subhead">Armor</h4>
      <table class="cg-battle-table cg-armor-table">
        <thead>
          <tr>
            <th>Name</th><th>Soak Dice</th><th>Penalty</th><th>Notes</th><th></th>
          </tr>
        </thead>
        <tbody id="cg-armor-tbody">${rows}</tbody>
      </table>
      <button type="button" class="cg-battle-add-btn" id="cg-add-armor">+ Add Armor</button>
    </div>
  `;
}

function readWeaponsFromDom() {
  const out = [];
  document.querySelectorAll('#cg-weapons-tbody .cg-weapon-row').forEach(row => {
    out.push({
      name:   row.querySelector('.cg-weapon-name')?.value   || '',
      attack: row.querySelector('.cg-weapon-attack')?.value || '',
      damage: row.querySelector('.cg-weapon-damage')?.value || '',
      range:  row.querySelector('.cg-weapon-range')?.value  || '',
      notes:  row.querySelector('.cg-weapon-notes')?.value  || '',
    });
  });
  return out;
}

function readArmorFromDom() {
  const out = [];
  document.querySelectorAll('#cg-armor-tbody .cg-armor-row').forEach(row => {
    out.push({
      name:    row.querySelector('.cg-armor-name')?.value    || '',
      soak:    row.querySelector('.cg-armor-soak')?.value    || '',
      penalty: row.querySelector('.cg-armor-penalty')?.value || '',
      notes:   row.querySelector('.cg-armor-notes')?.value   || '',
    });
  });
  return out;
}

function persist() {
  if (!FormBuilderAPI) return;
  FormBuilderAPI._data = FormBuilderAPI._data || {};
  FormBuilderAPI._data.weapons = readWeaponsFromDom();
  FormBuilderAPI._data.armor   = readArmorFromDom();
}

function refreshPools() {
  const pools = buildCombatPools();
  const iEl = document.getElementById('cg-battle-initiative');
  const dEl = document.getElementById('cg-battle-dodge');
  const sEl = document.getElementById('cg-battle-soak');
  if (iEl) iEl.textContent = pools.initiative;
  if (dEl) dEl.textContent = pools.dodge;
  if (sEl) sEl.textContent = pools.soak;
}

const BattleAPI = {
  _bound: false,

  init() {
    const container = document.getElementById('cg-battle-panel');
    if (!container) return;

    const data    = FormBuilderAPI?._data || {};
    const weapons = Array.isArray(data.weapons) ? data.weapons : [];
    const armor   = Array.isArray(data.armor)   ? data.armor   : [];
    const pools   = buildCombatPools();

    container.innerHTML =
      renderPoolsSection(pools) +
      renderWeaponsTable(weapons) +
      renderArmorTable(armor);

    this._bindEvents(container);
  },

  _bindEvents(container) {
    if (this._bound) {
      // Re-bind after re-render
      this._bound = false;
    }
    this._bound = true;

    // Add weapon row
    container.querySelector('#cg-add-weapon')?.addEventListener('click', () => {
      persist();
      const data    = FormBuilderAPI?._data || {};
      const weapons = Array.isArray(data.weapons) ? data.weapons : [];
      weapons.push({ name: '', attack: '', damage: '', range: '', notes: '' });
      FormBuilderAPI._data.weapons = weapons;
      const tbody = document.getElementById('cg-weapons-tbody');
      if (tbody) {
        const idx = tbody.querySelectorAll('.cg-weapon-row').length;
        tbody.insertAdjacentHTML('beforeend', weaponRowHtml({}, idx));
      }
    });

    // Add armor row
    container.querySelector('#cg-add-armor')?.addEventListener('click', () => {
      persist();
      const data  = FormBuilderAPI?._data || {};
      const armor = Array.isArray(data.armor) ? data.armor : [];
      armor.push({ name: '', soak: '', penalty: '', notes: '' });
      FormBuilderAPI._data.armor = armor;
      const tbody = document.getElementById('cg-armor-tbody');
      if (tbody) {
        const idx = tbody.querySelectorAll('.cg-armor-row').length;
        tbody.insertAdjacentHTML('beforeend', armorRowHtml({}, idx));
      }
    });

    // Remove rows (delegated)
    container.addEventListener('click', e => {
      const btn = e.target.closest('.cg-battle-remove-btn');
      if (!btn) return;
      const row = btn.closest('tr');
      if (row) row.remove();
      persist();
    });

    // Persist on any input change inside the container
    container.addEventListener('input',  () => persist(), true);
    container.addEventListener('change', () => persist(), true);

    // Refresh pools when traits change
    document.addEventListener('cg:traits:updated', refreshPools);
    document.addEventListener('change', e => {
      const t = e.target;
      if (t && t.id && /^cg-(speed|will|body)$/.test(t.id)) refreshPools();
    });
  },

  // Called by collectFormData — ensures DOM state is flushed
  flush() {
    persist();
  },
};

export default BattleAPI;
