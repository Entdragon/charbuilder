// assets/js/src/core/trappings/index.js
//
// Equipment, money & trappings system.
// - Career trappings autofill (from trappings_map via API)
// - Species natural weapons autofill
// - Equipment catalog browse/purchase
// - Battle Array autofill from equipped weapons/armor
// - Money tracking (starting from career trait_career die roll, deducted on purchase)

import FormBuilderAPI from '../formBuilder/index.js';
import SpeciesAPI     from '../species/api.js';
import CareerAPI      from '../career/api.js';

const $ = window.jQuery;

const LOG  = (...a) => console.log('[Trappings]', ...a);
const WARN = (...a) => console.warn('[Trappings]', ...a);

function ajaxEnv() {
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  const base = (typeof window.CG_API_BASE === 'string' && window.CG_API_BASE)
    ? window.CG_API_BASE.replace(/\/+$/, '') : '';
  const ajax_url =
    (base ? base + '/api/ajax' : '') ||
    env.ajax_url ||
    window.ajaxurl ||
    document.body?.dataset?.ajaxUrl ||
    '/wp-admin/admin-ajax.php';
  const nonce = env.nonce || env.security || window.CG_NONCE || null;
  return { ajax_url, nonce };
}

function postJSON(url, data) {
  return $.post(url, data).then(res => {
    try { return (typeof res === 'string') ? JSON.parse(res) : res; }
    catch (_) { return res; }
  });
}

function escape(v) {
  return String(v == null ? '' : v)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Die face to numeric value for starting money
function dieToNumber(die) {
  const m = String(die || '').match(/d(\d+)/i);
  if (!m) return 0;
  return parseInt(m[1], 10) || 0;
}

// ── Data helpers ─────────────────────────────────────────────────────────────

function getTrappingsList() {
  return Array.isArray(FormBuilderAPI._data?.trappings_list)
    ? FormBuilderAPI._data.trappings_list
    : [];
}

function setTrappingsList(list) {
  FormBuilderAPI._data = FormBuilderAPI._data || {};
  FormBuilderAPI._data.trappings_list = Array.isArray(list) ? list : [];
}

function getMoneyHoldings() {
  const h = FormBuilderAPI._data?.money_holdings;
  return (h && typeof h === 'object' && !Array.isArray(h)) ? { ...h } : {};
}

function setMoneyHoldings(h) {
  FormBuilderAPI._data = FormBuilderAPI._data || {};
  FormBuilderAPI._data.money_holdings = (h && typeof h === 'object') ? h : {};
}

// ── Money helpers ─────────────────────────────────────────────────────────────

const TrappingsAPI = {
  _bound:       false,
  _inited:      false,
  _currencyList: [],         // from cg_get_money_list
  _currencyBySlug: {},
  _catalogCache: null,       // equipment catalog rows
  _pendingCareer: null,      // career_id being fetched

  init() {
    if (this._inited) return;
    this._inited = true;

    this._fetchCurrency().then(() => {
      this._bindEvents();
      this._renderAll();
    });
  },

  _bindEvents() {
    if (this._bound) return;
    this._bound = true;

    // Career changed → autofill trappings
    $(document).on('cg:career:changed.trappings', (e, detail) => {
      const id = detail?.id ? parseInt(detail.id, 10) : 0;
      if (id > 0) {
        this._fillCareerTrappings(id);
      } else {
        this._removeCareerTrappings();
        this._renderAll();
      }
    });

    // Species changed → autofill natural weapons (jQuery + native listener)
    $(document).on('cg:species:changed.trappings', () => {
      this._fillSpeciesWeapons();
      this._renderAll();
    });
    try {
      window.__CG_EVT__ = window.__CG_EVT__ || {};
      if (window.__CG_EVT__.trappingsSpeciesChanged) {
        document.removeEventListener('cg:species:changed', window.__CG_EVT__.trappingsSpeciesChanged);
      }
      window.__CG_EVT__.trappingsSpeciesChanged = () => {
        if (!window.jQuery) {
          this._fillSpeciesWeapons();
          this._renderAll();
        }
      };
      document.addEventListener('cg:species:changed', window.__CG_EVT__.trappingsSpeciesChanged);
    } catch (_) {}

    // Character loaded → restore (idempotent)
    try {
      window.__CG_EVT__ = window.__CG_EVT__ || {};
      if (window.__CG_EVT__.trappingsCharacterLoaded) {
        document.removeEventListener('cg:character:loaded', window.__CG_EVT__.trappingsCharacterLoaded);
      }
      window.__CG_EVT__.trappingsCharacterLoaded = () => {
        this._onCharacterLoaded();
      };
      document.addEventListener('cg:character:loaded', window.__CG_EVT__.trappingsCharacterLoaded);
    } catch (_) {}

    // Delegated: remove trapping
    $(document).on('click.trappings', '.cg-trap-remove-btn', (e) => {
      const btn = e.currentTarget;
      const uid = btn.dataset.uid;
      if (!uid) return;
      const list = getTrappingsList().filter(t => t.uid !== uid);
      setTrappingsList(list);
      this._renderAll();
    });

    // Delegated: equipment catalog search
    $(document).on('input.trappings', '#cg-equip-search', () => {
      this._renderCatalog();
    });

    $(document).on('change.trappings', '#cg-equip-filter-kind', () => {
      this._renderCatalog();
    });

    // Delegated: add item from catalog
    $(document).on('click.trappings', '.cg-equip-add-btn', (e) => {
      const btn  = e.currentTarget;
      const slug = btn.dataset.slug;
      const kind = btn.dataset.kind;
      this._purchaseItem(slug, kind);
    });

    // Delegated: money input change
    $(document).on('change.trappings input.trappings', '.cg-money-input', (e) => {
      const inp  = e.currentTarget;
      const slug = inp.dataset.slug;
      if (!slug) return;
      const val = parseFloat(inp.value) || 0;
      const h   = getMoneyHoldings();
      h[slug]   = val;
      setMoneyHoldings(h);
    });

    // Delegated: exchange Zhongguo ↔ Calabrese
    $(document).on('click.trappings', '#cg-money-exchange-btn', () => {
      this._showExchangeModal();
    });
  },

  // ── Career trappings autofill ───────────────────────────────────────────────

  async _fillCareerTrappings(careerId) {
    if (this._pendingCareer === careerId) return;
    this._pendingCareer = careerId;

    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) return;

    try {
      const res = await postJSON(ajax_url, {
        action:    'cg_get_career_trappings',
        career_id: careerId,
        security:  nonce,
        nonce,
      });
      if (!res || res.success !== true) return;
      const fetched = Array.isArray(res.data) ? res.data : [];

      const existing = getTrappingsList();
      const nonCareer = existing.filter(t => t.source !== 'career');
      const careerItems = fetched.map(t => ({
        uid:     `career-${t.map_id}`,
        source:  'career',
        kind:    t.kind || 'equipment',
        name:    t.name || t.token || '',
        qty:     t.qty  || 1,
        slug:    t.slug || '',
        token:   t.token || '',
        // stats for battle array autofill
        armor_dice:  t.armor_dice  || '',
        cover_dice:  t.cover_dice  || '',
        attack_dice: t.attack_dice || '',
        damage_mod:  t.damage_mod  || 0,
        range_band:  t.range_band  || 'Melee',
        parry_die:   t.parry_die   || '',
        effect:      t.effect      || '',
        cost_d:      t.cost_d      || null,
        source_book: t.source_book || '',
        pg_no:       t.pg_no       || '',
      }));

      setTrappingsList([...nonCareer, ...careerItems]);
      this._syncBattleArray();
      this._renderAll();

      // Initialize starting money from trait_career die if money not yet set
      this._initStartingMoney();
    } catch (err) {
      WARN('Failed to fetch career trappings:', err);
    } finally {
      this._pendingCareer = null;
    }
  },

  _removeCareerTrappings() {
    const list = getTrappingsList().filter(t => t.source !== 'career');
    setTrappingsList(list);
    this._syncBattleArray();
  },

  // ── Species natural weapons autofill ───────────────────────────────────────

  _fillSpeciesWeapons() {
    const sp = SpeciesAPI?.currentProfile || null;
    if (!sp) {
      const list = getTrappingsList().filter(t => t.source !== 'species');
      setTrappingsList(list);
      this._syncBattleArray();
      return;
    }

    const weapons = [sp.weapon_1, sp.weapon_2, sp.weapon_3]
      .filter(Boolean)
      .map((name, i) => ({
        uid:        `species-weapon-${i}`,
        source:     'species',
        kind:       'weapon',
        name:       name,
        qty:        1,
        slug:       '',
        token:      name,
        attack_dice: '',
        damage_mod:  0,
        range_band:  'Melee',
        parry_die:   '',
        cover_die:   '',
        effect:      '',
        cost_d:      null,
      }));

    const nonSpecies = getTrappingsList().filter(t => t.source !== 'species');
    setTrappingsList([...nonSpecies, ...weapons]);
    this._syncBattleArray();
  },

  // ── Equipment purchase ──────────────────────────────────────────────────────

  async _ensureCatalog() {
    if (this._catalogCache) return this._catalogCache;
    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) return [];

    const res = await postJSON(ajax_url, {
      action:   'cg_get_equipment_catalog',
      security: nonce,
      nonce,
    });
    this._catalogCache = (res && res.success && Array.isArray(res.data)) ? res.data : [];
    return this._catalogCache;
  },

  _totalDenarii() {
    const holdings = getMoneyHoldings();
    return this._currencyList.reduce((sum, c) => {
      const count = parseFloat(holdings[c.slug] || 0);
      const rate  = parseFloat(c.value_denarii || 0);
      return sum + (count * rate);
    }, 0);
  },

  _deductCost(costD) {
    if (costD <= 0) return true;

    const holdings = getMoneyHoldings();
    const totalVal = this._totalDenarii();
    if (totalVal < costD - 0.001) return false;

    let remaining = costD;

    const sorted = this._currencyList
      .filter(c => parseFloat(c.value_denarii || 0) > 0)
      .sort((a, b) => parseFloat(a.value_denarii) - parseFloat(b.value_denarii));

    for (const c of sorted) {
      if (remaining <= 0.001) break;
      const rate  = parseFloat(c.value_denarii || 0);
      if (rate <= 0) continue;
      const have  = parseFloat(holdings[c.slug] || 0);
      if (have <= 0) continue;

      const needed = Math.min(have, Math.ceil((remaining / rate) * 1000) / 1000);
      const spent  = needed * rate;
      holdings[c.slug] = Math.max(0, have - needed);
      remaining -= spent;
    }

    setMoneyHoldings(holdings);
    return true;
  },

  _purchaseItem(slug, kind) {
    const catalog = this._catalogCache || [];
    const item    = catalog.find(c => c.slug === slug && c.kind === kind);
    if (!item) { WARN('Item not found in catalog:', slug, kind); return; }

    const cost = parseFloat(item.cost_d) || 0;

    if (cost > 0) {
      const totalVal = this._totalDenarii();
      if (totalVal < cost - 0.001) {
        alert(`Not enough funds. Need ${cost}D, have ${totalVal.toFixed(2)}D total across all currencies.`);
        return;
      }
      if (!this._deductCost(cost)) {
        alert(`Could not deduct ${cost}D from holdings.`);
        return;
      }
    }

    const uid = `purchase-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
    const trapping = {
      uid,
      source:     'purchase',
      kind:       item.kind,
      name:       item.name,
      qty:        1,
      slug:       item.slug,
      token:      item.name,
      armor_dice:  item.armor_dice  || '',
      cover_dice:  item.cover_dice  || '',
      attack_dice: item.attack_dice || '',
      damage_mod:  item.damage_mod  || 0,
      range_band:  item.range_band  || 'Melee',
      parry_die:   item.parry_die   || '',
      effect:      item.effect      || '',
      cost_d:      item.cost_d      || null,
    };

    const list = getTrappingsList();
    list.push(trapping);
    setTrappingsList(list);
    this._syncBattleArray();
    this._renderAll();
  },

  // ── Battle Array autofill ───────────────────────────────────────────────────

  _syncBattleArray() {
    const list = getTrappingsList();

    const weaponTrappings = list.filter(t => t.kind === 'weapon');
    const armorTrappings  = list.filter(t => t.kind === 'equipment' && t.armor_dice);

    // Sync weapons
    const existingWeapons = Array.isArray(FormBuilderAPI._data?.weapons)
      ? FormBuilderAPI._data.weapons.filter(w => !w._from_trappings)
      : [];

    const trappingWeapons = weaponTrappings.map(t => {
      let attack = t.attack_dice || '';
      if (!attack && t.damage_mod) {
        attack = `Damage +${t.damage_mod}`;
      }
      let range = t.range_band || 'Melee';
      const rmap = {
        'Close': 'Melee', 'Short': 'Short', 'Medium': 'Medium', 'Long': 'Long',
        'Thrown': 'Thrown', 'Melee': 'Melee'
      };
      range = rmap[range] || 'Melee';

      return {
        _from_trappings: true,
        _trapping_uid:   t.uid,
        name:   t.name,
        attack: attack,
        damage: t.damage_mod != null ? `+${t.damage_mod}` : '',
        range:  range,
        notes:  t.effect || '',
      };
    });

    FormBuilderAPI._data.weapons = [...existingWeapons, ...trappingWeapons];

    // Sync armor
    const existingArmor = Array.isArray(FormBuilderAPI._data?.armor)
      ? FormBuilderAPI._data.armor.filter(a => !a._from_trappings)
      : [];

    const trappingArmor = armorTrappings.map(t => ({
      _from_trappings: true,
      _trapping_uid:   t.uid,
      name:   t.name,
      soak:   t.armor_dice || '',
      penalty:'',
      notes:  '',
    }));

    FormBuilderAPI._data.armor = [...existingArmor, ...trappingArmor];

    // Re-render BattleAPI if available
    try {
      const BattleAPI = window.CG_BattleAPI;
      if (BattleAPI && typeof BattleAPI.init === 'function') {
        BattleAPI.init();
      }
    } catch (_) {}
  },

  // ── Starting money ──────────────────────────────────────────────────────────

  _initStartingMoney() {
    const holdings = getMoneyHoldings();
    if (Object.keys(holdings).length > 0) return;

    const die = FormBuilderAPI._data?.trait_career || '';
    const amount = dieToNumber(die);
    if (amount > 0) {
      holdings['denar'] = amount;
      setMoneyHoldings(holdings);
    }
  },

  // ── Currency ─────────────────────────────────────────────────────────────────

  async _fetchCurrency() {
    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) return;

    try {
      const res = await postJSON(ajax_url, {
        action:   'cg_get_money_list',
        security: nonce,
        nonce,
      });
      if (res && res.success && Array.isArray(res.data)) {
        this._currencyList = res.data;
        this._currencyBySlug = {};
        res.data.forEach(c => {
          this._currencyBySlug[c.slug] = c;
        });
      }
    } catch (err) {
      WARN('Failed to fetch currency list:', err);
    }
  },

  // ── Character loaded ──────────────────────────────────────────────────────────

  _onCharacterLoaded() {
    this._syncBattleArray();
    this._renderAll();

    const careerId = parseInt(FormBuilderAPI._data?.career_id || '0', 10);
    const careerTrappings = getTrappingsList().filter(t => t.source === 'career');
    if (careerId > 0 && careerTrappings.length === 0) {
      this._fillCareerTrappings(careerId);
    }

    this._fillSpeciesWeapons();
  },

  // ── Rendering ─────────────────────────────────────────────────────────────────

  _renderAll() {
    this._renderTrappingsPanel();
    this._renderMoneyPanel();
  },

  _renderTrappingsPanel() {
    const panel = document.getElementById('cg-trappings-panel');
    if (!panel) return;

    const list = getTrappingsList();

    const bySource = { career: [], species: [], purchase: [], manual: [] };
    list.forEach(t => {
      const src = t.source || 'manual';
      if (!bySource[src]) bySource[src] = [];
      bySource[src].push(t);
    });

    const renderGroup = (items, label, cls) => {
      if (!items.length) return '';
      const rows = items.map(t => `
        <tr class="cg-trap-row cg-trap-row--${escape(t.source || 'manual')}">
          <td class="cg-trap-qty">${escape(t.qty || 1)}</td>
          <td class="cg-trap-name">${escape(t.name || t.token || '')}</td>
          <td class="cg-trap-kind">${escape(t.kind === 'weapon' ? '⚔' : '🎒')}</td>
          <td class="cg-trap-stats">${escape(this._statSummary(t))}</td>
          <td class="cg-trap-actions">
            ${(t.source === 'purchase' || t.source === 'manual')
              ? `<button type="button" class="cg-trap-remove-btn cg-btn-sm" data-uid="${escape(t.uid)}" title="Remove">✕</button>`
              : ''}
          </td>
        </tr>
      `).join('');
      return `
        <div class="cg-trap-group">
          <div class="cg-trap-group-label ${cls}">${label}</div>
          <table class="cg-trap-table">
            <thead><tr><th>Qty</th><th>Item</th><th></th><th>Stats</th><th></th></tr></thead>
            <tbody>${rows}</tbody>
          </table>
        </div>
      `;
    };

    const html = `
      <div class="cg-trappings-inner">
        ${renderGroup(bySource.species,  'Natural Weapons (Species)',  'cg-trap-label--species')}
        ${renderGroup(bySource.career,   'Career Starting Trappings',  'cg-trap-label--career')}
        ${renderGroup(bySource.purchase, 'Purchased Equipment',        'cg-trap-label--purchase')}
        ${renderGroup(bySource.manual,   'Other Trappings',            'cg-trap-label--manual')}
        ${list.length === 0 ? '<p class="cg-trap-empty">No trappings yet. Select a career or browse the catalog below.</p>' : ''}
      </div>
    `;

    panel.innerHTML = html;
  },

  _statSummary(t) {
    if (t.kind === 'weapon') {
      const parts = [];
      if (t.attack_dice) parts.push(t.attack_dice);
      if (t.damage_mod)  parts.push(`Dmg +${t.damage_mod}`);
      if (t.effect)      parts.push(t.effect.slice(0, 40));
      return parts.join(', ');
    } else {
      const parts = [];
      if (t.armor_dice) parts.push(`Armor ${t.armor_dice}`);
      if (t.cover_dice) parts.push(`Cover ${t.cover_dice}`);
      return parts.join(', ');
    }
  },

  _renderMoneyPanel() {
    const panel = document.getElementById('cg-money-panel');
    if (!panel) return;

    const holdings = getMoneyHoldings();
    const currencies = this._currencyList;

    if (!currencies.length) {
      panel.innerHTML = '<p class="cg-money-loading"><em>Loading currency…</em></p>';
      return;
    }

    const calabrese = currencies.filter(c => c.source_book == null || c.source_book !== 101);
    const zhongguo  = currencies.filter(c => c.source_book == 101);

    const renderCurrencyRow = (c) => {
      const val = parseFloat(holdings[c.slug] || 0);
      const xr  = c.exchange_rate_text ? `<span class="cg-money-xr">${escape(c.exchange_rate_text)}</span>` : '';
      return `
        <tr class="cg-money-row">
          <td class="cg-money-name">${escape(c.name)}</td>
          <td class="cg-money-val">
            <input type="number" class="cg-money-input" data-slug="${escape(c.slug)}"
              value="${val}" min="0" step="0.001" />
          </td>
          <td class="cg-money-xr-cell">${xr}</td>
        </tr>
      `;
    };

    const totalDenarii = currencies.reduce((sum, c) => {
      const count = parseFloat(holdings[c.slug] || 0);
      const rate  = parseFloat(c.value_denarii || 0);
      return sum + (count * rate);
    }, 0);

    panel.innerHTML = `
      <div class="cg-money-inner">
        <div class="cg-money-total">
          Total value: <strong>${totalDenarii.toFixed(2)}D</strong>
        </div>

        <h5 class="cg-money-subhead">Calabrese Coins</h5>
        <table class="cg-money-table">
          <thead><tr><th>Currency</th><th>Amount</th><th>Exchange rate</th></tr></thead>
          <tbody>${calabrese.map(renderCurrencyRow).join('')}</tbody>
        </table>

        ${zhongguo.length ? `
          <h5 class="cg-money-subhead">Zhongguo Coins</h5>
          <table class="cg-money-table">
            <thead><tr><th>Currency</th><th>Amount</th><th>Exchange rate</th></tr></thead>
            <tbody>${zhongguo.map(renderCurrencyRow).join('')}</tbody>
          </table>
        ` : ''}
      </div>
    `;
  },

  async _renderCatalog() {
    const panel = document.getElementById('cg-equip-catalog-panel');
    if (!panel) return;

    const catalog = await this._ensureCatalog();

    const searchEl  = document.getElementById('cg-equip-search');
    const filterEl  = document.getElementById('cg-equip-filter-kind');
    const search    = (searchEl?.value || '').toLowerCase().trim();
    const filterKind = filterEl?.value || '';

    let items = catalog;
    if (search)     items = items.filter(c => (c.name || '').toLowerCase().includes(search));
    if (filterKind) items = items.filter(c => c.kind === filterKind);

    if (!items.length) {
      panel.innerHTML = '<p class="cg-catalog-empty">No items match your search.</p>';
      return;
    }

    const totalVal = this._totalDenarii();

    const rows = items.slice(0, 200).map(c => {
      const cost       = parseFloat(c.cost_d) || 0;
      const costText   = cost > 0 ? `${cost}D` : (c.cost_text || '—');
      const canAfford  = cost === 0 || totalVal >= cost - 0.001;
      const stats      = this._catalogStatSummary(c);
      return `
        <tr class="cg-catalog-row">
          <td class="cg-catalog-name">${escape(c.name)}</td>
          <td class="cg-catalog-kind">${escape(c.kind === 'weapon' ? 'Weapon' : 'Equipment')}</td>
          <td class="cg-catalog-cat">${escape(c.category || '')}</td>
          <td class="cg-catalog-cost">${escape(costText)}</td>
          <td class="cg-catalog-stats cg-text-sm">${escape(stats)}</td>
          <td class="cg-catalog-action">
            <button type="button" class="cg-equip-add-btn cg-btn-sm${canAfford ? '' : ' cg-btn-disabled'}"
              data-slug="${escape(c.slug)}" data-kind="${escape(c.kind)}"
              title="Add to trappings"${canAfford ? '' : ' disabled'}>
              + Add
            </button>
          </td>
        </tr>
      `;
    }).join('');

    panel.innerHTML = `
      <table class="cg-catalog-table">
        <thead>
          <tr><th>Name</th><th>Type</th><th>Category</th><th>Cost</th><th>Stats</th><th></th></tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
      ${items.length > 200 ? `<p class="cg-catalog-note">Showing 200 of ${items.length} items. Use search to narrow results.</p>` : ''}
    `;
  },

  _catalogStatSummary(c) {
    if (c.kind === 'weapon') {
      const parts = [];
      if (c.attack_dice) parts.push(c.attack_dice);
      if (c.damage_mod)  parts.push(`Dmg +${c.damage_mod}`);
      if (c.range_band)  parts.push(c.range_band);
      if (c.effect)      parts.push(c.effect.slice(0, 40));
      return parts.join(', ');
    } else {
      const parts = [];
      if (c.armor_dice) parts.push(`Armor ${c.armor_dice}`);
      if (c.cover_dice) parts.push(`Cover ${c.cover_dice}`);
      return parts.join(', ');
    }
  },

  _showExchangeModal() {
    const currencies = this._currencyList.filter(c => parseFloat(c.value_denarii || 0) > 0);
    if (currencies.length < 2) {
      alert('Not enough currencies available for exchange.');
      return;
    }

    const holdings = getMoneyHoldings();

    const optionsHtml = currencies.map(c =>
      `<option value="${escape(c.slug)}">${escape(c.name)} (${parseFloat(c.value_denarii || 0)}D each)</option>`
    ).join('');

    const modal = document.createElement('div');
    modal.className = 'cg-exchange-overlay';
    modal.innerHTML = `
      <div class="cg-exchange-modal">
        <h4>Currency Exchange</h4>
        <p class="cg-exchange-info">Exchange between currencies at table-defined rates.</p>
        <div class="cg-exchange-row">
          <label>From:</label>
          <select id="cg-xch-from" class="cg-free-select">${optionsHtml}</select>
          <input type="number" id="cg-xch-amount" class="cg-money-input" value="1" min="0.001" step="0.001" />
        </div>
        <div class="cg-exchange-row">
          <label>To:</label>
          <select id="cg-xch-to" class="cg-free-select">${optionsHtml}</select>
        </div>
        <div id="cg-xch-preview" class="cg-exchange-preview"></div>
        <div class="cg-exchange-actions">
          <button type="button" id="cg-xch-confirm" class="cg-btn cg-btn-gold">Exchange</button>
          <button type="button" id="cg-xch-cancel" class="cg-btn">Cancel</button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    const fromEl   = modal.querySelector('#cg-xch-from');
    const toEl     = modal.querySelector('#cg-xch-to');
    const amtEl    = modal.querySelector('#cg-xch-amount');
    const previewEl = modal.querySelector('#cg-xch-preview');

    if (currencies.length > 1) toEl.selectedIndex = 1;

    const updatePreview = () => {
      const fromSlug = fromEl.value;
      const toSlug   = toEl.value;
      const amount   = parseFloat(amtEl.value) || 0;
      const fromCur  = this._currencyBySlug[fromSlug];
      const toCur    = this._currencyBySlug[toSlug];
      if (!fromCur || !toCur || amount <= 0) {
        previewEl.textContent = '';
        return;
      }
      const fromRate = parseFloat(fromCur.value_denarii || 0);
      const toRate   = parseFloat(toCur.value_denarii || 0);
      if (toRate <= 0) { previewEl.textContent = 'Cannot convert to this currency.'; return; }
      const result = (amount * fromRate) / toRate;
      const have   = parseFloat(holdings[fromSlug] || 0);
      previewEl.textContent = `${amount} ${fromCur.name} = ${result.toFixed(3)} ${toCur.name}` +
        (have < amount ? ` (you only have ${have})` : '');
    };

    fromEl.addEventListener('change', updatePreview);
    toEl.addEventListener('change', updatePreview);
    amtEl.addEventListener('input', updatePreview);
    updatePreview();

    modal.querySelector('#cg-xch-cancel').addEventListener('click', () => {
      modal.remove();
    });

    modal.querySelector('#cg-xch-confirm').addEventListener('click', () => {
      const fromSlug = fromEl.value;
      const toSlug   = toEl.value;
      const amount   = parseFloat(amtEl.value) || 0;
      const fromCur  = this._currencyBySlug[fromSlug];
      const toCur    = this._currencyBySlug[toSlug];
      if (!fromCur || !toCur || amount <= 0) return;
      if (fromSlug === toSlug) { alert('Cannot exchange a currency for itself.'); return; }

      const h = getMoneyHoldings();
      const have = parseFloat(h[fromSlug] || 0);
      if (have < amount) { alert(`Not enough ${fromCur.name}. You have ${have}.`); return; }

      const fromRate = parseFloat(fromCur.value_denarii || 0);
      const toRate   = parseFloat(toCur.value_denarii || 0);
      if (toRate <= 0) { alert('Cannot convert to this currency.'); return; }

      const result = (amount * fromRate) / toRate;
      h[fromSlug] = Math.max(0, have - amount);
      h[toSlug]   = (parseFloat(h[toSlug] || 0)) + result;
      setMoneyHoldings(h);

      modal.remove();
      this._renderAll();
      this._renderCatalog();
    });

    modal.addEventListener('click', (e) => {
      if (e.target === modal) modal.remove();
    });
  },
};

export default TrappingsAPI;

if (typeof window !== 'undefined') {
  window.CG_TrappingsAPI = TrappingsAPI;
}
