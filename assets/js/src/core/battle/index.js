// assets/js/src/core/battle/index.js
//
// Battle Array module — Ironclaw character sheet battle stats.
// Computes Initiative/Dodge/Soak pools from trait values.
// Manages structured weapon and armor rows.
// Saves/restores via FormBuilderAPI._data.weapons / ._data.armor.
// Shows spells granted by the character's gifts (read-only, fetched via AJAX).

import FormBuilderAPI        from '../formBuilder/index.js';
import CareerAPI             from '../career/api.js';
import SpeciesAPI            from '../species/api.js';
import { marksToDice }       from '../../utils/marks-dice.js';
import { resolveAttackPool } from '../../utils/resolve-attack-pool.js';
import { compactPool }       from '../../utils/compact-pool.js';

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


// Gift IDs whose effects are fully passive and automatically modify combat pools
const GIFT_SOAK_ADD_WILL    = 21;  // Resolve: "Include Will Dice with Soak Dice"
const GIFT_SOAK_ADD_SPECIES = 79;  // Natural Armor: "Add Species Die to Soak Dice"

function buildCombatPools() {
  const speed   = traitDie('speed');
  const will    = traitDie('will');
  const body    = traitDie('body');
  const mind    = traitDie('mind');
  const species = traitDie('trait_species');
  const data    = FormBuilderAPI?._data || {};
  const armorSoak = (Array.isArray(data.armor) ? data.armor : []).map(a => a.soak).filter(Boolean);

  // Dodge = Speed + Dodge skill pool (species die, career die, marks die).
  // resolveAttackPool may return the literal "Dodge" string if the skill has no dice;
  // filter to only valid dice-notation tokens (d4/d6/d8/…).
  const dodgeSkillPool = resolveAttackPool('Dodge');
  const diePat         = /^d\d+$/;
  const dodgeDice      = [speed, ...dodgeSkillPool.split('+').map(s => s.trim()).filter(s => diePat.test(s))];

  // Check which passive gift bonuses are active
  const activeGifts  = collectAllGiftIds();
  const extraSoak    = [];
  if (activeGifts.has(String(GIFT_SOAK_ADD_WILL))    && will)    extraSoak.push(will);
  if (activeGifts.has(String(GIFT_SOAK_ADD_SPECIES)) && species) extraSoak.push(species);

  return {
    initiative: poolString(speed, mind),
    dodge:      poolString(...dodgeDice),
    soak:       poolString(body, ...armorSoak, ...extraSoak),
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
          <span class="cg-pool-note">(Speed + Mind)</span>
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
  const trappingAttr = w._from_trappings ? ' data-from-trappings="1"' : '';
  return `
    <tr class="cg-weapon-row" data-idx="${idx}"${trappingAttr}>
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
  const trappingAttr = a._from_trappings ? ' data-from-trappings="1"' : '';
  return `
    <tr class="cg-armor-row" data-idx="${idx}"${trappingAttr}>
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

// ---------------------------------------------------------------------------
// Spells — read-only section rendered below weapons
// ---------------------------------------------------------------------------

/**
 * Collect every gift ID the character currently has:
 *   free choices + career gifts + species gifts + XP gifts.
 */
function collectAllGiftIds() {
  const data = FormBuilderAPI._data || {};
  const ids  = new Set();

  // Free-choice gifts (array or individual slots)
  (Array.isArray(data.free_gifts) ? data.free_gifts : []).forEach(id => { if (id) ids.add(String(id)); });
  if (data.free_gift_1) ids.add(String(data.free_gift_1));
  if (data.free_gift_2) ids.add(String(data.free_gift_2));
  if (data.free_gift_3) ids.add(String(data.free_gift_3));

  // XP gifts
  (Array.isArray(data.xpGifts) ? data.xpGifts : []).forEach(id => { if (id) ids.add(String(id)); });

  // Career gifts from current profile
  const cp = (CareerAPI && CareerAPI.currentProfile) || {};
  ['gift_id_1','gift_id_2','gift_id_3','gift1_id','gift2_id','gift3_id'].forEach(k => {
    if (cp[k]) ids.add(String(cp[k]));
  });
  // Career gift replacements (slot overrides)
  const repl = data.career_gift_replacements || {};
  Object.values(repl).forEach(id => { if (id) ids.add(String(id)); });

  // Species gifts from current profile
  const sp = (SpeciesAPI && SpeciesAPI.currentProfile) || {};
  ['gift_id_1','gift_id_2','gift_id_3','gift1_id','gift2_id','gift3_id'].forEach(k => {
    if (sp[k]) ids.add(String(sp[k]));
  });

  return [...ids].filter(id => id && id !== '0');
}

let _lastSpellGiftKey = '';

/**
 * Fetch spells from the server for the character's current gift set.
 * Returns a jQuery Deferred that resolves with the spells array.
 * Results are cached on _data.spells and only re-fetched when the gift set changes.
 */
function fetchSpells() {
  const giftIds = collectAllGiftIds();
  const key     = giftIds.slice().sort().join(',');

  // Cache hit — same gift set
  const data = FormBuilderAPI._data || {};
  if (key === _lastSpellGiftKey && Array.isArray(data.spells)) {
    return $.Deferred().resolve(data.spells).promise();
  }

  if (!giftIds.length) {
    FormBuilderAPI._data = data;
    FormBuilderAPI._data.spells = [];
    _lastSpellGiftKey = key;
    return $.Deferred().resolve([]).promise();
  }

  const { ajax_url, nonce } = ajaxEnv();
  const dfd = $.Deferred();

  const postData = { action: 'cg_get_spells_for_gifts', security: nonce, nonce, _ajax_nonce: nonce };
  giftIds.forEach((id, i) => { postData[`gift_ids[${i}]`] = id; });

  $.post(ajax_url, postData)
    .then(res => {
      let spells = [];
      if (res && res.success && Array.isArray(res.data)) spells = res.data;
      FormBuilderAPI._data = FormBuilderAPI._data || {};
      FormBuilderAPI._data.spells = spells;
      _lastSpellGiftKey = key;
      dfd.resolve(spells);
    })
    .catch(() => {
      dfd.resolve([]); // non-fatal — spells section just stays empty
    });

  return dfd.promise();
}

function spellAttackPool(spell) {
  const raw = spell.attack_dice || '';
  if (!raw) return '—';
  const resolved = resolveAttackPool(raw);
  return compactPool(resolved) || raw;
}

function renderSpellsTable(spells) {
  if (!Array.isArray(spells) || !spells.length) return '';

  // Group by gift_name for display
  const groups = {};
  const order  = [];
  spells.forEach(s => {
    const g = s.gift_name || 'Spells';
    if (!groups[g]) { groups[g] = []; order.push(g); }
    groups[g].push(s);
  });

  let html = `<div class="cg-battle-section cg-spells-section">
    <h4 class="cg-battle-subhead">Spells</h4>`;

  order.forEach(giftName => {
    const label = giftName === 'Common Magic' ? 'Common Magic Spells' : giftName;
    html += `
    <div class="cg-spell-group">
      <h5 class="cg-spell-group-head">${escape(label)}</h5>
      <table class="cg-battle-table cg-spells-table">
        <thead>
          <tr>
            <th>Name</th><th>Attack Pool</th><th>Equip</th><th>Range</th><th>Effect</th><th>Descriptors</th>
          </tr>
        </thead>
        <tbody>`;

    groups[giftName].forEach(s => {
      html += `<tr class="cg-spell-row">
        <td class="cg-spell-name">${escape(s.name)}</td>
        <td class="cg-spell-pool">${escape(spellAttackPool(s))}</td>
        <td class="cg-spell-equip">${escape(s.equip)}</td>
        <td class="cg-spell-range">${escape(s.range)}</td>
        <td class="cg-spell-effect">${escape(s.effect)}</td>
        <td class="cg-spell-desc">${escape(s.descriptors)}</td>
      </tr>`;
    });

    html += `</tbody></table></div>`;
  });

  html += `</div>`;
  return html;
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

    // Fetch spells for current gifts and render/update the spells section.
    this._loadSpells(container);
  },

  _render(container) {
    const data    = FormBuilderAPI?._data || {};
    const weapons = Array.isArray(data.weapons) ? data.weapons : [];
    const armor   = Array.isArray(data.armor)   ? data.armor   : [];
    const spells  = Array.isArray(data.spells)  ? data.spells  : [];
    const pools   = buildCombatPools();

    container.innerHTML =
      renderPoolsSection(pools) +
      renderWeaponsTable(weapons) +
      renderArmorTable(armor) +
      renderSpellsTable(spells);

    // Persist so that computed attack pools (from _attack_dice_raw)
    // are available to the summary tab even if the user never edits the battle tab.
    persist();

    this._bindEvents(container);
  },

  /**
   * Fetch spells for the current gift set, then inject/replace the spells section.
   * Re-uses the _data.spells cache if the gift set hasn't changed.
   */
  _loadSpells(container) {
    fetchSpells().then(spells => {
      // If the container was re-rendered in the meantime, find the spells slot
      const existing = container.querySelector('.cg-spells-section');
      const newHtml  = renderSpellsTable(spells);

      if (existing) {
        if (newHtml) {
          existing.outerHTML = newHtml;
        } else {
          existing.remove();
        }
      } else if (newHtml) {
        container.insertAdjacentHTML('beforeend', newHtml);
      }
    });
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
      if (t && t.id && /^cg-(speed|will|body|mind|trait_species)$/.test(t.id)) refreshPools();
    });

    // Re-fetch spells AND refresh pools when gift-related events fire
    // (Some gifts modify combat pools, e.g. Resolve adds Will to Soak)
    document.addEventListener('cg:career:profile',      () => { refreshPools(); this._loadSpells(container); });
    document.addEventListener('cg:species:profile',     () => { refreshPools(); this._loadSpells(container); });
    document.addEventListener('cg:freechoices:changed', () => { refreshPools(); this._loadSpells(container); });
    document.addEventListener('cg:gifts:changed',       () => { refreshPools(); this._loadSpells(container); });
  },

  // Called by collectFormData — ensures DOM state is flushed
  flush() {
    persist();
  },
};

export default BattleAPI;
