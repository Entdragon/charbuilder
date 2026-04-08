// assets/js/src/core/ally/index.js
//
// Ally subsystem — shown when Ally gift (126) is active.
// Manages a separate mini-character panel for the character's ally:
//   - Name, description, species, career
//   - Auto-derived gifts (career + species + main char language)
//   - Improved Ally free-gift slots (one per time gift 218 is taken)
//   - Traits (all d6), battle array, trappings, money, equipment shop

import FormBuilderAPI from '../formBuilder/index.js';
import CareerAPI      from '../career/api.js';
import SpeciesAPI     from '../species/api.js';

const $ = window.jQuery;
const LOG  = (...a) => console.log('[AllyModule]', ...a);
const WARN = (...a) => console.warn('[AllyModule]', ...a);

const ALLY_GIFT_ID         = '126';
const IMPROVED_ALLY_GIFT_ID = '218';

function esc(v) {
  return String(v == null ? '' : v)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

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

const AllyModule = {
  _bound: false,
  _careerProfile: null,
  _speciesProfile: null,
  _giftList: null,
  _giftListLoading: false,
  _catalogData: null,   // equipment catalog (shared with main trappings)
  _catalogOpen: false,

  // ── Data helpers ────────────────────────────────────────────────────────────

  _getData() {
    return (FormBuilderAPI._data && FormBuilderAPI._data.ally)
      ? FormBuilderAPI._data.ally
      : {};
  },

  _patch(obj) {
    FormBuilderAPI._data = FormBuilderAPI._data || {};
    FormBuilderAPI._data.ally = { ...this._getData(), ...obj };
  },

  // ── Gift detection ───────────────────────────────────────────────────────────

  _allMainGiftIds() {
    const d = FormBuilderAPI._data || {};
    const ids = [];
    ['free_gift_1','free_gift_2','free_gift_3'].forEach(k => {
      if (d[k] && String(d[k]) !== '0') ids.push(String(d[k]));
    });
    (Array.isArray(d.free_gifts) ? d.free_gifts : []).forEach(id => {
      if (id && String(id) !== '0') ids.push(String(id));
    });
    (Array.isArray(d.xpGifts) ? d.xpGifts : []).forEach(id => {
      if (id && String(id) !== '0') ids.push(String(id));
    });
    // Career gift replacements
    const repl = d.career_gift_replacements || {};
    Object.values(repl).forEach(id => { if (id && String(id) !== '0') ids.push(String(id)); });
    // Career/species profile gifts
    const cp = (CareerAPI && CareerAPI.currentProfile) || {};
    const sp = (SpeciesAPI && SpeciesAPI.currentProfile) || {};
    ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
      if (cp[k] && String(cp[k]) !== '0') ids.push(String(cp[k]));
      if (sp[k] && String(sp[k]) !== '0') ids.push(String(sp[k]));
    });
    return ids;
  },

  _hasAllyGift() {
    return this._allMainGiftIds().includes(ALLY_GIFT_ID);
  },

  _improvedAllyCount() {
    return this._allMainGiftIds().filter(id => id === IMPROVED_ALLY_GIFT_ID).length;
  },

  // ── Initialisation ───────────────────────────────────────────────────────────

  init() {
    this._bindOnce();
    this._syncTabVisibility();
    if (this._hasAllyGift()) {
      this._render();
      const ally = this._getData();
      if (ally.species_id) {
        SpeciesAPI.fetchProfile(ally.species_id).then(p => {
          this._speciesProfile = p;
          this._refreshGiftsArea();
          this._refreshBattleArea();
        });
      }
      if (ally.career_id) {
        this._loadCareerProfile(ally.career_id);
      }
      this._ensureGiftList();
      this._populateSelects();
    }
  },

  _bindOnce() {
    if (this._bound) return;
    this._bound = true;

    // Re-init whenever the builder form is (re)built (character loaded or new)
    document.addEventListener('cg:builder:opened', () => {
      // Allow FormBuilderAPI._data to be seeded before we inspect gifts
      setTimeout(() => {
        this._careerProfile  = null;
        this._speciesProfile = null;
        this._syncTabVisibility();
        if (this._hasAllyGift()) {
          this._render();
          const ally = this._getData();
          if (ally.species_id) {
            SpeciesAPI.fetchProfile(ally.species_id).then(p => {
              this._speciesProfile = p;
              this._refreshGiftsArea();
              this._refreshBattleArea();
            });
          }
          if (ally.career_id) this._loadCareerProfile(ally.career_id);
          this._ensureGiftList();
          this._populateSelects();
        }
      }, 0);
    });

    // React to main character gift changes
    $(document).on(
      'cg:free-gift:changed cg:xp-gift:changed change.cg-ally-gifts',
      () => {
        this._syncTabVisibility();
        if (this._hasAllyGift()) {
          const panel = document.getElementById('cg-ally-inner');
          if (!panel || !panel.dataset.rendered) {
            this._render();
            this._populateSelects();
          } else {
            this._refreshImprovedSlots();
          }
        }
      }
    );

    // Tab click — re-populate selects when ally tab becomes visible
    $(document).on('click', '[data-tab="tab-ally"]', () => {
      setTimeout(() => {
        this._populateSelects();
        this._ensureGiftList().then(() => this._refreshImprovedSlots());
      }, 50);
    });

    // Ally identity inputs
    $(document).on('input.ally', '#cg-ally-name', e => {
      this._patch({ name: e.target.value });
    });
    $(document).on('input.ally', '#cg-ally-description', e => {
      this._patch({ description: e.target.value });
    });

    // Ally species / career
    $(document).on('change.ally', '#cg-ally-species', e => {
      this._onSpeciesChange(e.target.value);
    });
    $(document).on('change.ally', '#cg-ally-career', e => {
      this._onCareerChange(e.target.value);
    });

    // Improved Ally gift selection
    $(document).on('change.ally', '.cg-ally-improved-gift-select', () => {
      this._onImprovedGiftChange();
    });

    // Equipment catalog
    $(document).on('click.ally', '#cg-ally-shop-btn', () => {
      this._openCatalog();
    });
    $(document).on('click.ally', '.cg-ally-catalog-buy', e => {
      this._buyItem(e.currentTarget.dataset);
    });
    $(document).on('click.ally', '.cg-ally-trapping-remove', e => {
      this._removeItem(parseInt(e.currentTarget.dataset.idx, 10));
    });
    $(document).on('click.ally', '#cg-ally-catalog-close', () => {
      this._closeCatalog();
    });
  },

  // ── Tab visibility ───────────────────────────────────────────────────────────

  _syncTabVisibility() {
    const tab   = document.querySelector('[data-tab="tab-ally"]');
    const panel = document.getElementById('tab-ally');
    const show  = this._hasAllyGift();
    if (tab)   tab.style.display   = show ? '' : 'none';
    if (panel) panel.style.display = show ? ''  : 'none';
  },

  // ── Populate species/career selects ─────────────────────────────────────────

  _populateSelects() {
    const allySp = document.getElementById('cg-ally-species');
    const allyCr = document.getElementById('cg-ally-career');
    if (!allySp && !allyCr) return;

    const ally = this._getData();

    if (allySp) {
      SpeciesAPI.getList().then(list => {
        const prior = allySp.value || ally.species_id || '';
        allySp.innerHTML = '<option value="">— Select Species —</option>' +
          (list || []).map(({ id, name }) =>
            `<option value="${esc(id)}"${String(id) === String(prior) ? ' selected' : ''}>${esc(name)}</option>`
          ).join('');
        if (prior) allySp.value = prior;
      });
    }

    if (allyCr) {
      CareerAPI.getList().then(list => {
        const prior = allyCr.value || ally.career_id || '';
        allyCr.innerHTML = '<option value="">— Select Career —</option>' +
          (list || []).map(({ id, name }) =>
            `<option value="${esc(id)}"${String(id) === String(prior) ? ' selected' : ''}>${esc(name)}</option>`
          ).join('');
        if (prior) allyCr.value = prior;
      });
    }
  },

  // ── Profile loading ──────────────────────────────────────────────────────────

  _onSpeciesChange(speciesId) {
    this._patch({ species_id: speciesId });
    this._speciesProfile = null;
    this._refreshGiftsArea();
    this._refreshBattleArea();
    if (!speciesId) return;
    SpeciesAPI.fetchProfile(speciesId).then(p => {
      this._speciesProfile = p;
      this._refreshGiftsArea();
      this._refreshBattleArea();
    });
  },

  _onCareerChange(careerId) {
    this._patch({ career_id: careerId });
    this._careerProfile = null;
    this._refreshGiftsArea();
    this._refreshBattleArea();
    this._refreshMoneyArea();
    if (!careerId) return;
    this._loadCareerProfile(careerId);
  },

  _loadCareerProfile(careerId) {
    CareerAPI.fetchProfile(careerId).then(p => {
      this._careerProfile = p;
      this._refreshGiftsArea();
      this._refreshBattleArea();
      this._refreshMoneyArea();
    });
  },

  _onImprovedGiftChange() {
    const ids = [];
    document.querySelectorAll('.cg-ally-improved-gift-select').forEach(sel => {
      if (sel.value) ids.push(sel.value);
    });
    this._patch({ improved_gift_ids: ids });
    this._refreshGiftsArea();
  },

  // ── Main render ──────────────────────────────────────────────────────────────

  _render() {
    const panel = document.getElementById('cg-ally-inner');
    if (!panel) return;

    const ally = this._getData();
    panel.dataset.rendered = '1';
    panel.innerHTML = this._buildIdentityHtml(ally)
      + this._buildProfileHtml(ally)
      + this._buildTraitsHtml()
      + `<div id="cg-ally-gifts-area">${this._buildGiftsHtml()}</div>`
      + `<div id="cg-ally-battle-area">${this._buildBattleHtml()}</div>`
      + `<div id="cg-ally-trappings-area">${this._buildTrappingsHtml()}</div>`
      + `<div id="cg-ally-money-area">${this._buildMoneyHtml()}</div>`
      + this._buildShopHtml();
  },

  _refreshGiftsArea() {
    const el = document.getElementById('cg-ally-gifts-area');
    if (el) el.innerHTML = this._buildGiftsHtml();
  },
  _refreshBattleArea() {
    const el = document.getElementById('cg-ally-battle-area');
    if (el) el.innerHTML = this._buildBattleHtml();
  },
  _refreshMoneyArea() {
    const el = document.getElementById('cg-ally-money-area');
    if (el) el.innerHTML = this._buildMoneyHtml();
  },
  _refreshTrappingsArea() {
    const el = document.getElementById('cg-ally-trappings-area');
    if (el) el.innerHTML = this._buildTrappingsHtml();
  },
  _refreshImprovedSlots() {
    const el = document.getElementById('cg-ally-improved-slots');
    if (!el) return;
    const ally = this._getData();
    const count = this._improvedAllyCount();
    const ids = Array.isArray(ally.improved_gift_ids) ? ally.improved_gift_ids : [];
    el.innerHTML = this._buildImprovedSlotsHtml(count, ids);
  },

  // ── Section builders ─────────────────────────────────────────────────────────

  _buildIdentityHtml(ally) {
    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Identity</h4>
      <div class="cg-ally-field-row">
        <label class="cg-ally-label" for="cg-ally-name">Name</label>
        <input type="text" id="cg-ally-name" class="cg-ally-input"
               value="${esc(ally.name || '')}" placeholder="Ally's name" />
      </div>
      <div class="cg-ally-field-row">
        <label class="cg-ally-label" for="cg-ally-description">Description</label>
        <textarea id="cg-ally-description" class="cg-ally-textarea"
                  placeholder="Appearance, personality, background…">${esc(ally.description || '')}</textarea>
      </div>
    </div>`;
  },

  _buildProfileHtml(ally) {
    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Species &amp; Career</h4>
      <div class="cg-ally-field-row">
        <label class="cg-ally-label" for="cg-ally-species">Species</label>
        <select id="cg-ally-species" class="cg-ally-select">
          <option value="">— Select Species —</option>
        </select>
      </div>
      <div class="cg-ally-field-row">
        <label class="cg-ally-label" for="cg-ally-career">Career</label>
        <select id="cg-ally-career" class="cg-ally-select">
          <option value="">— Select Career —</option>
        </select>
      </div>
    </div>`;
  },

  _buildTraitsHtml() {
    const traits = [
      ['Body','d6'], ['Speed','d6'], ['Will','d6'],
      ['Mind','d6'], ['Species','d6'], ['Career','d6'],
    ];
    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Traits <span class="cg-ally-note">(all d6)</span></h4>
      <div class="cg-ally-traits-grid">
        ${traits.map(([label, die]) =>
          `<div class="cg-ally-trait"><span class="cg-ally-trait-label">${label}</span><span class="cg-ally-trait-die">${die}</span></div>`
        ).join('')}
      </div>
    </div>`;
  },

  _buildGiftsHtml() {
    const ally = this._getData();
    const sp   = this._speciesProfile || {};
    const cp   = this._careerProfile  || {};

    const mainLang = this._getMainLang();
    const spGifts  = [sp.gift_1, sp.gift_2, sp.gift_3].filter(Boolean);
    const cpGifts  = [cp.gift_1, cp.gift_2, cp.gift_3].filter(Boolean);
    const count    = this._improvedAllyCount();
    const ids      = Array.isArray(ally.improved_gift_ids) ? ally.improved_gift_ids : [];

    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Gifts</h4>

      <div class="cg-ally-gift-group">
        <div class="cg-ally-gift-label">Language</div>
        <div class="cg-ally-gift-item">${esc(mainLang || '(same as character)')}</div>
      </div>

      ${spGifts.length ? `
      <div class="cg-ally-gift-group">
        <div class="cg-ally-gift-label">Species Gifts</div>
        <ul class="cg-ally-gift-list">
          ${spGifts.map(g => `<li>${esc(g)}</li>`).join('')}
        </ul>
      </div>` : ''}

      ${cpGifts.length ? `
      <div class="cg-ally-gift-group">
        <div class="cg-ally-gift-label">Career Gifts</div>
        <ul class="cg-ally-gift-list">
          ${cpGifts.map(g => `<li>${esc(g)}</li>`).join('')}
        </ul>
      </div>` : ''}

      <div id="cg-ally-improved-slots">
        ${this._buildImprovedSlotsHtml(count, ids)}
      </div>
    </div>`;
  },

  _buildImprovedSlotsHtml(count, selectedIds) {
    if (count === 0) return '';
    let html = `<div class="cg-ally-gift-group">
      <div class="cg-ally-gift-label">Improved Ally Gifts (${count})</div>`;
    for (let i = 0; i < count; i++) {
      const sel = selectedIds[i] || '';
      html += `<div class="cg-ally-improved-slot">
        <select class="cg-ally-improved-gift-select cg-ally-select" data-slot="${i}">
          <option value="">— Choose a Gift —</option>
          ${this._buildGiftOptions(sel)}
        </select>
      </div>`;
    }
    html += `</div>`;
    return html;
  },

  _buildGiftOptions(selectedId) {
    if (!this._giftList) return '';
    const owned = this._getAllyOwnedGiftIds();
    return (this._giftList || [])
      .filter(g => {
        const id = String(g.id || g.ct_id || '');
        if (!id || id === '0') return false;
        // No major gifts for allies
        if (parseInt(g.ct_gifts_major || g.major || 0, 10) > 0) return false;
        // Exclude Local Knowledge
        if (id === '242') return false;
        // Don't show gifts the ally already owns from species/career
        // (unless it allows multiple)
        const allows = parseInt(g.ct_gifts_manifold || g.allows_multiple || g.manifold || 0, 10) > 0;
        if (!allows && owned.has(id) && id !== selectedId) return false;
        return true;
      })
      .map(g => {
        const id   = String(g.id || g.ct_id || '');
        const name = String(g.name || g.ct_gifts_name || '');
        const sel  = id === selectedId ? ' selected' : '';
        return `<option value="${esc(id)}"${sel}>${esc(name)}</option>`;
      })
      .join('');
  },

  _getAllyOwnedGiftIds() {
    const sp = this._speciesProfile || {};
    const cp = this._careerProfile  || {};
    const ids = new Set();
    ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
      if (sp[k]) ids.add(String(sp[k]));
      if (cp[k]) ids.add(String(cp[k]));
    });
    return ids;
  },

  _buildBattleHtml() {
    const sp = this._speciesProfile || {};
    const cp = this._careerProfile  || {};

    const weapons = this._deriveAllyWeapons(sp, cp);
    const armor   = this._deriveAllyArmor(sp, cp);

    let soakParts = ['d6']; // body
    armor.forEach(a => { if (a.soak) soakParts.push(a.soak); });
    const soak = soakParts.join(' + ');

    let html = `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Battle Array</h4>
      <div class="cg-ally-pools">
        <div class="cg-ally-pool">
          <span class="cg-ally-pool-label">Initiative</span>
          <strong class="cg-ally-pool-dice">d6 + d6</strong>
          <span class="cg-ally-pool-note">(Speed + Mind)</span>
        </div>
        <div class="cg-ally-pool">
          <span class="cg-ally-pool-label">Dodge</span>
          <strong class="cg-ally-pool-dice">d6</strong>
          <span class="cg-ally-pool-note">(Speed)</span>
        </div>
        <div class="cg-ally-pool">
          <span class="cg-ally-pool-label">Soak</span>
          <strong class="cg-ally-pool-dice">${esc(soak)}</strong>
          <span class="cg-ally-pool-note">(Body + Armour)</span>
        </div>
      </div>`;

    if (weapons.length) {
      html += `<h5 class="cg-ally-table-head">Weapons</h5>
      <table class="cg-ally-table">
        <thead><tr><th>Name</th><th>Attack</th><th>Damage</th><th>Range</th></tr></thead>
        <tbody>${weapons.map(w =>
          `<tr><td>${esc(w.name)}</td><td>${esc(w.attack)}</td><td>${esc(w.damage)}</td><td>${esc(w.range)}</td></tr>`
        ).join('')}</tbody>
      </table>`;
    }

    if (armor.length) {
      html += `<h5 class="cg-ally-table-head">Armour</h5>
      <table class="cg-ally-table">
        <thead><tr><th>Name</th><th>Soak Dice</th></tr></thead>
        <tbody>${armor.map(a =>
          `<tr><td>${esc(a.name)}</td><td>${esc(a.soak)}</td></tr>`
        ).join('')}</tbody>
      </table>`;
    }

    html += `</div>`;
    return html;
  },

  _buildTrappingsHtml() {
    const ally     = this._getData();
    const list     = Array.isArray(ally.trappings_list) ? ally.trappings_list : [];
    const sp       = this._speciesProfile || {};
    const cp       = this._careerProfile  || {};
    const autoWeps = this._deriveAllyWeapons(sp, cp);
    const autoArm  = this._deriveAllyArmor(sp, cp);

    // Auto trappings (from career/species)
    const autoItems = [...autoWeps, ...autoArm].map(t => `<li class="cg-ally-trapping-auto">${esc(t.name)}</li>`).join('');

    // Purchased items
    const purchItems = list.map((t, i) =>
      `<li class="cg-ally-trapping-item">
        ${esc(t.name)}
        <button type="button" class="cg-ally-trapping-remove cg-btn-tiny" data-idx="${i}" title="Remove">✕</button>
      </li>`
    ).join('');

    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Trappings</h4>
      ${autoItems || purchItems ? `
      <ul class="cg-ally-trappings-list">
        ${autoItems}${purchItems}
      </ul>` : '<p class="cg-ally-empty">No trappings yet.</p>'}
    </div>`;
  },

  _buildMoneyHtml() {
    const ally     = this._getData();
    const holdings = (ally.money_holdings && typeof ally.money_holdings === 'object')
      ? ally.money_holdings : {};
    const denar = parseInt(holdings.denar || 0, 10);
    // Ally career die = d6 → starting money = 6 denar (same as career/trappings logic)
    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Money</h4>
      <div class="cg-ally-money">
        <span class="cg-ally-money-label">Denar</span>
        <input type="number" id="cg-ally-money-denar" class="cg-ally-money-input"
               value="${denar}" min="0" />
      </div>
    </div>`;
  },

  _buildShopHtml() {
    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Equipment Shop</h4>
      <p class="cg-ally-note">Purchase equipment for the ally. Purchases deduct from the ally's money.</p>
      <button type="button" id="cg-ally-shop-btn" class="cg-btn cg-btn-gold">Open Equipment Shop</button>
      <div id="cg-ally-catalog" style="display:none"></div>
    </div>`;
  },

  // ── Trapping derivation ──────────────────────────────────────────────────────

  _deriveAllyWeapons(sp, cp) {
    // Pull attack dice formulas from species/career trappings data stored in their profiles.
    // Both profiles may expose a `trappings` array similar to main character trappings.
    const weapons = [];
    const seen = new Set();

    for (const profile of [sp, cp]) {
      const list = Array.isArray(profile.trappings) ? profile.trappings : [];
      for (const t of list) {
        if ((t.kind || t.type) !== 'weapon') continue;
        if (seen.has(t.name)) continue;
        seen.add(t.name);
        const dmg = t.damage_mod != null ? `+${t.damage_mod}` : '';
        // Ally has d6 for all traits; resolve attack pool simply
        const attack = this._resolveAllyPool(t.attack_dice || '');
        weapons.push({ name: t.name || '', attack, damage: dmg, range: t.range_band || 'Melee' });
      }
    }
    return weapons;
  },

  _deriveAllyArmor(sp, cp) {
    const armor = [];
    const seen  = new Set();
    for (const profile of [sp, cp]) {
      const list = Array.isArray(profile.trappings) ? profile.trappings : [];
      for (const t of list) {
        if (!t.armor_dice) continue;
        if (seen.has(t.name)) continue;
        seen.add(t.name);
        armor.push({ name: t.name || '', soak: t.armor_dice });
      }
    }
    return armor;
  },

  /** Resolve an attack pool string for an ally (all traits = d6, no skill marks). */
  _resolveAllyPool(raw) {
    if (!raw) return '';
    const TRAIT = { body:'d6', speed:'d6', will:'d6', mind:'d6', species:'d6', career:'d6' };
    const vsIdx = raw.toLowerCase().indexOf(' vs.');
    const part  = vsIdx > -1 ? raw.slice(0, vsIdx) : raw;
    const parts = part.split(',').map(s => s.trim()).filter(Boolean);
    const dice  = [];
    for (const p of parts) {
      const k = p.toLowerCase();
      if (TRAIT[k]) { dice.push(TRAIT[k]); }
      else { dice.push(p); }  // pass through (skill name, etc.)
    }
    return dice.filter(Boolean).join(' + ');
  },

  // ── Gift list (for Improved Ally slots) ─────────────────────────────────────

  _ensureGiftList() {
    if (this._giftList) return Promise.resolve(this._giftList);
    if (this._giftListLoading) return new Promise(resolve => {
      const wait = setInterval(() => {
        if (this._giftList) { clearInterval(wait); resolve(this._giftList); }
      }, 100);
    });

    this._giftListLoading = true;
    const { ajax_url, nonce } = ajaxEnv();

    return new Promise(resolve => {
      $.post(ajax_url, { action: 'cg_get_free_gifts', security: nonce, nonce, _ajax_nonce: nonce })
        .then(res => {
          let list = [];
          if (res && res.success && Array.isArray(res.data)) list = res.data;
          else if (Array.isArray(res)) list = res;
          this._giftList = list;
          this._giftListLoading = false;
          resolve(list);
        })
        .catch(() => {
          this._giftListLoading = false;
          resolve([]);
        });
    });
  },

  // ── Language helper ──────────────────────────────────────────────────────────

  _getMainLang() {
    try {
      const QualState = window.CG_QualState || window.QualState;
      if (QualState && QualState.data && Array.isArray(QualState.data.language) && QualState.data.language[0]) {
        return QualState.data.language[0];
      }
    } catch (_) {}
    const d = FormBuilderAPI._data || {};
    return d.language || d.qualifications?.language?.[0] || '';
  },

  // ── Equipment catalog ────────────────────────────────────────────────────────

  _openCatalog() {
    const el = document.getElementById('cg-ally-catalog');
    if (!el) return;
    this._catalogOpen = true;
    el.style.display = '';
    el.innerHTML = '<p class="cg-ally-loading">Loading catalog…</p>';

    const { ajax_url, nonce } = ajaxEnv();
    $.post(ajax_url, { action: 'cg_get_equipment_catalog', security: nonce, nonce, _ajax_nonce: nonce })
      .then(res => {
        const items = (res && res.success && Array.isArray(res.data)) ? res.data : [];
        this._catalogData = items;
        el.innerHTML = this._buildCatalogHtml(items);
      })
      .catch(() => {
        el.innerHTML = '<p>Could not load catalog.</p>';
      });
  },

  _closeCatalog() {
    const el = document.getElementById('cg-ally-catalog');
    if (el) el.style.display = 'none';
    this._catalogOpen = false;
  },

  _buildCatalogHtml(items) {
    if (!items.length) return '<p class="cg-ally-empty">No items available.</p>';
    const rows = items.map(it => `
      <tr>
        <td>${esc(it.name || '')}</td>
        <td>${esc(it.cost_d || it.price || '')}</td>
        <td>
          <button type="button" class="cg-ally-catalog-buy cg-btn-tiny cg-btn-gold"
            data-name="${esc(it.name || '')}" data-cost="${esc(it.cost_d || 0)}">Buy</button>
        </td>
      </tr>`).join('');
    return `
      <div class="cg-ally-catalog-wrap">
        <div class="cg-ally-catalog-header">
          <h5>Equipment Shop</h5>
          <button type="button" id="cg-ally-catalog-close" class="cg-btn-tiny">✕ Close</button>
        </div>
        <table class="cg-ally-table">
          <thead><tr><th>Item</th><th>Cost (denar)</th><th></th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>`;
  },

  _buyItem(dataset) {
    const name = dataset.name || '';
    const cost = parseInt(dataset.cost || 0, 10);
    if (!name) return;

    const ally     = this._getData();
    const holdings = (ally.money_holdings && typeof ally.money_holdings === 'object')
      ? { ...ally.money_holdings } : {};
    const denarEl = document.getElementById('cg-ally-money-denar');
    const current = parseInt(denarEl?.value || holdings.denar || 0, 10);

    if (cost > 0 && current < cost) {
      alert(`Not enough money. Need ${cost} denar, have ${current}.`);
      return;
    }

    const newDenar = Math.max(0, current - cost);
    holdings.denar = newDenar;
    if (denarEl) denarEl.value = newDenar;

    const trappings = Array.isArray(ally.trappings_list) ? [...ally.trappings_list] : [];
    trappings.push({ name, cost_d: cost });
    this._patch({ trappings_list: trappings, money_holdings: holdings });
    this._refreshTrappingsArea();
  },

  _removeItem(idx) {
    const ally      = this._getData();
    const trappings = Array.isArray(ally.trappings_list) ? [...ally.trappings_list] : [];
    trappings.splice(idx, 1);
    this._patch({ trappings_list: trappings });
    this._refreshTrappingsArea();
  },

  // ── Money input sync ─────────────────────────────────────────────────────────

  _syncMoneyFromDom() {
    const el = document.getElementById('cg-ally-money-denar');
    if (!el) return;
    const ally     = this._getData();
    const holdings = (ally.money_holdings && typeof ally.money_holdings === 'object')
      ? { ...ally.money_holdings } : {};
    holdings.denar = parseInt(el.value || 0, 10);
    this._patch({ money_holdings: holdings });
  },
};

// ── Module export & global handle ────────────────────────────────────────────

$(document).on('input.ally-money', '#cg-ally-money-denar', () => {
  AllyModule._syncMoneyFromDom();
});

window.CG_AllyModule = AllyModule;

export default AllyModule;
