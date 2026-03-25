// assets/js/src/core/battle/index.js
//
// Battle Array module — Ironclaw character sheet battle stats.
// Computes Initiative/Dodge/Soak pools from trait values.
// Manages structured weapon and armor rows.
// Saves/restores via FormBuilderAPI._data.weapons / ._data.armor.

import FormBuilderAPI        from '../formBuilder/index.js';
import CareerAPI             from '../career/api.js';
import SpeciesAPI            from '../species/api.js';
import { marksToDice }       from '../../utils/marks-dice.js';
import { resolveAttackPool } from '../../utils/resolve-attack-pool.js';

const $ = window.jQuery;

// ---------------------------------------------------------------------------
// Ensure skillsList is loaded before resolveAttackPool can work correctly.
// The Skills tab fetches it on first visit; if Battle tab opens first we
// fetch it ourselves so weapon/dodge pools resolve properly.
// ---------------------------------------------------------------------------
let _skillsEnsured = false;

function ajaxEnv() {
  const env  = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  const base = (typeof window.CG_API_BASE === 'string' && window.CG_API_BASE)
    ? window.CG_API_BASE.replace(/\/+$/, '') : '';
  const ajax_url =
    (base ? base + '/api/ajax' : '') ||
    env.ajax_url || window.ajaxurl ||
    document.body?.dataset?.ajaxUrl || '/wp-admin/admin-ajax.php';
  const nonce = env.nonce || env.security || window.CG_NONCE || null;
  return { ajax_url, nonce };
}

function normaliseSkillsList(raw) {
  return raw.map(s => ({
    id:   String(s.id ?? s.skill_id ?? ''),
    name: String(s.name ?? s.ct_skill_name ?? s.skill_name ?? '')
  })).filter(s => s.id && s.name);
}

function ensureSkillsList() {
  // Already done this session
  if (_skillsEnsured) return $.Deferred().resolve().promise();

  const data = FormBuilderAPI._data || {};

  // Already in _data
  if (Array.isArray(data.skillsList) && data.skillsList.length) {
    _skillsEnsured = true;
    return $.Deferred().resolve().promise();
  }

  // Available on window (Skills tab may have already fetched it)
  if (Array.isArray(window.CG_SKILLS_LIST) && window.CG_SKILLS_LIST.length) {
    FormBuilderAPI._data = data;
    FormBuilderAPI._data.skillsList = window.CG_SKILLS_LIST;
    _skillsEnsured = true;
    return $.Deferred().resolve().promise();
  }

  // Fetch from server
  const { ajax_url, nonce } = ajaxEnv();
  const dfd = $.Deferred();
  $.post(ajax_url, { action: 'cg_get_skills_list', security: nonce, nonce, _ajax_nonce: nonce })
    .then(res => {
      let list = [];
      if (res && res.success && Array.isArray(res.data)) list = res.data;
      else if (Array.isArray(res)) list = res;
      list = normaliseSkillsList(list);
      window.CG_SKILLS_LIST = list;
      FormBuilderAPI._data = FormBuilderAPI._data || {};
      FormBuilderAPI._data.skillsList = list;
      _skillsEnsured = true;
      dfd.resolve();
    })
    .catch(() => dfd.resolve()); // resolve even on failure; pools will degrade gracefully
  return dfd.promise();
}

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


function buildCombatPools() {
  const speed = traitDie('speed');
  const will  = traitDie('will');
  const body  = traitDie('body');
  const data  = FormBuilderAPI?._data || {};
  const armorSoak = (Array.isArray(data.armor) ? data.armor : []).map(a => a.soak).filter(Boolean);

  // Dodge = Speed + Dodge skill pool (species die, career die, marks die).
  // resolveAttackPool may return the literal "Dodge" string if the skill has no dice;
  // filter to only valid dice-notation tokens (d4/d6/d8/…).
  const dodgeSkillPool = resolveAttackPool('Dodge');
  const diePat         = /^d\d+$/;
  const dodgeDice      = [speed, ...dodgeSkillPool.split('+').map(s => s.trim()).filter(s => diePat.test(s))];

  return {
    initiative: poolString(speed, will),
    dodge:      poolString(...dodgeDice),
    soak:       poolString(body, ...armorSoak),
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
          <span class="cg-pool-note">(Speed + Dodge skill)</span>
        </div>
        <div class="cg-pool-block">
          <span class="cg-pool-label">Soak</span>
          <span class="cg-pool-dice" id="cg-battle-soak">${escape(pools.soak)}</span>
          <span class="cg-pool-note">(Body + Armour)</span>
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

    const hadSkills = !!(
      Array.isArray((FormBuilderAPI?._data || {}).skillsList) &&
      (FormBuilderAPI._data.skillsList || []).length
    ) || !!(Array.isArray(window.CG_SKILLS_LIST) && window.CG_SKILLS_LIST.length);

    this._render(container);

    // If skills weren't loaded yet, fetch them and re-render once ready so
    // resolveAttackPool can substitute skill dice into weapon / dodge pools.
    if (!hadSkills) {
      ensureSkillsList().then(() => { this._render(container); });
    }
  },

  _render(container) {
    const data    = FormBuilderAPI?._data || {};
    const weapons = Array.isArray(data.weapons) ? data.weapons : [];
    const armor   = Array.isArray(data.armor)   ? data.armor   : [];
    const pools   = buildCombatPools();

    container.innerHTML =
      renderPoolsSection(pools) +
      renderWeaponsTable(weapons) +
      renderArmorTable(armor);

    // Persist so that computed attack pools (from _attack_dice_raw)
    // are available to the summary tab even if the user never edits the battle tab.
    persist();

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
