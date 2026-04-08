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
// Shared gift-filter pipeline — rules added to gift-filter.js apply here AND
// to the main gifts tab automatically (no duplicate logic to keep in sync).
import {
  diceToNum          as filterDiceToNum,
  giftIneligibleReason as filterGiftIneligibleReason,
} from '../gifts/gift-filter.js';

const $ = window.jQuery;
const LOG  = (...a) => console.log('[AllyModule]', ...a);
const WARN = (...a) => console.warn('[AllyModule]', ...a);

const ALLY_GIFT_ID         = '126';
const IMPROVED_ALLY_GIFT_ID = '218';

const ALLY_DIE_ORDER = ['d4','d6','d8','d10','d12'];

const ALLY_TRAIT_BOOSTS = {
  '78':  'will',
  '89':  'speed',
  '85':  'body',
  '100': 'mind',
  '224': 'trait_species',
  '223': 'trait_career'
};

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
  _allyGiftTrappings: {},  // giftId → fetched items[] (G1)
  _allySpells: [],          // cached spell list (G3)
  _lastAllySpellKey: '',    // sorted gift IDs key for spell cache (G3)
  _currencyList: [],        // from cg_get_money_list (G4)
  _currencyBySlug: {},      // slug → currency object (G4)

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
    this._fetchCurrency();
    if (this._hasAllyGift()) {
      this._render();
      const ally = this._getData();
      if (ally.species_id) {
        SpeciesAPI.fetchProfile(ally.species_id).then(p => {
          this._speciesProfile = p;
          this._refreshGiftsArea();
          this._refreshBattleArea();
          this._refreshTrappingsArea();
          this._fetchAllyGiftTrappings();
          this._fetchAllySpells();
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
        this._careerProfile      = null;
        this._speciesProfile     = null;
        this._allyGiftTrappings  = {};
        this._allySpells         = [];
        this._lastAllySpellKey   = '';
        this._fetchCurrency();
        this._syncTabVisibility();
        if (this._hasAllyGift()) {
          this._render();
          const ally = this._getData();
          if (ally.species_id) {
            SpeciesAPI.fetchProfile(ally.species_id).then(p => {
              this._speciesProfile = p;
              this._refreshGiftsArea();
              this._refreshBattleArea();
              this._refreshTrappingsArea();
              this._fetchAllyGiftTrappings();
              this._fetchAllySpells();
            });
          }
          if (ally.career_id) this._loadCareerProfile(ally.career_id);
          this._ensureGiftList();
          this._populateSelects();
        }
      }, 0);
    });

    // React to gift changes — covers:
    //   • cg:free-gift:changed  (new-character reset path in state.js)
    //   • cg:xp-gift:changed    (XP gift purchases)
    //   • .cg-free-gift-select  (the ACTUAL dropdown — setFreeGiftSlotsToData never emits the event)
    const onGiftChange = () => {
      // Delay one tick so FormBuilderAPI._data.free_gifts is updated before we read it
      setTimeout(() => {
        this._syncTabVisibility();
        if (this._hasAllyGift()) {
          const panel = document.getElementById('cg-ally-inner');
          if (!panel || !panel.dataset.rendered) {
            this._render();
            this._ensureGiftList();
            this._populateSelects();
          } else {
            this._refreshImprovedSlots();
          }
        }
      }, 0);
    };

    $(document).on('cg:free-gift:changed cg:xp-gift:changed', onGiftChange);
    // Also catch the raw dropdown change (the primary path used by free-choices.js)
    $(document).on('change.ally-gift-watch', '.cg-free-gift-select', onGiftChange);

    // Tab click — re-populate selects when ally tab becomes visible
    $(document).on('click', '[data-tab="tab-ally"]', () => {
      setTimeout(() => {
        this._populateSelects();
        this._ensureGiftList().then(() => this._refreshImprovedSlots());
      }, 50);
    });

    // When the skills list loads (after first visiting the Skills tab),
    // refresh the ally skills area in case it currently shows numeric IDs
    document.addEventListener('cg:skills-list:loaded', () => {
      const el = document.getElementById('cg-ally-skills-area');
      if (el) el.innerHTML = this._buildSkillsHtml();
    });

    // Ally identity inputs
    $(document).on('input.ally', '#cg-ally-name', e => {
      this._patch({ name: e.target.value });
    });
    $(document).on('input.ally', '#cg-ally-age', e => {
      this._patch({ age: e.target.value });
    });
    $(document).on('change.ally', '#cg-ally-gender', e => {
      this._patch({ gender: e.target.value });
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

    // Print / PDF export
    $(document).on('click.ally', '#cg-ally-export-pdf', () => {
      this._openPrintWindow();
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
    this._refreshTrappingsArea();
    if (!speciesId) {
      this._fetchAllyGiftTrappings();
      this._fetchAllySpells();
      return;
    }
    SpeciesAPI.fetchProfile(speciesId).then(p => {
      this._speciesProfile = p;
      this._refreshGiftsArea();
      this._refreshBattleArea();
      this._refreshTrappingsArea();
      this._fetchAllyGiftTrappings();
      this._fetchAllySpells();
    });
  },

  _onCareerChange(careerId) {
    this._patch({ career_id: careerId });
    this._careerProfile = null;
    this._refreshGiftsArea();
    this._refreshBattleArea();
    this._refreshTrappingsArea();
    this._refreshMoneyArea();
    if (!careerId) return;
    this._loadCareerProfile(careerId);
  },

  _loadCareerProfile(careerId) {
    const { ajax_url, nonce } = ajaxEnv();

    // Run the career-gifts profile fetch AND the career-trappings fetch in parallel.
    // The gifts endpoint (CareerAPI) returns only gifts/skills; weapons and armour
    // live in the trappings_map table and must be fetched separately.
    const profilePromise   = CareerAPI.fetchProfile(careerId);
    const trappingsPromise = ajax_url
      ? $.post(ajax_url, {
          action:    'cg_get_career_trappings',
          career_id: careerId,
          security:  nonce,
          nonce,
          _ajax_nonce: nonce,
        }).then(res => (res && res.success && Array.isArray(res.data)) ? res.data : [])
        .catch(() => [])
      : Promise.resolve([]);

    Promise.all([profilePromise, trappingsPromise]).then(([p, trappings]) => {
      // Merge trappings into the career profile so _deriveAllyWeapons /
      // _deriveAllyArmor (which both look at profile.trappings) pick them up.
      this._careerProfile = Object.assign({}, p, { trappings });
      this._refreshGiftsArea();
      this._refreshBattleArea();
      this._refreshTrappingsArea();
      this._refreshMoneyArea();
      this._fetchAllyGiftTrappings();
      this._fetchAllySpells();
    });
  },

  _onImprovedGiftChange() {
    const ids = [];
    document.querySelectorAll('.cg-ally-improved-gift-select').forEach(sel => {
      if (sel.value) ids.push(sel.value);
    });
    this._patch({ improved_gift_ids: ids });
    this._refreshGiftsArea();
    this._refreshBattleArea();
    this._fetchAllyGiftTrappings();
    this._fetchAllySpells();
  },

  // ── Main render ──────────────────────────────────────────────────────────────

  _render() {
    const panel = document.getElementById('cg-ally-inner');
    if (!panel) return;

    // Proactively fetch the full skills list so _buildSkillsHtml() can render
    // the complete table without waiting for the Skills tab to be visited.
    this._ensureSkillsList();

    const ally = this._getData();
    panel.dataset.rendered = '1';
    panel.innerHTML =
        `<div class="cg-ally-export-bar">
           <button type="button" id="cg-ally-export-pdf" class="cg-ally-export-btn">🖨 Print Ally Sheet</button>
         </div>`
      + this._buildIdentityHtml(ally)
      + this._buildProfileHtml(ally)
      + `<div id="cg-ally-traits-area">${this._buildTraitsHtml()}</div>`
      + `<div id="cg-ally-gifts-area">${this._buildGiftsHtml()}</div>`
      + `<div id="cg-ally-skills-area">${this._buildSkillsHtml()}</div>`
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
    const elT = document.getElementById('cg-ally-traits-area');
    if (elT) elT.innerHTML = this._buildTraitsHtml();
    const el = document.getElementById('cg-ally-battle-area');
    if (el) el.innerHTML = this._buildBattleHtml();
    const elS = document.getElementById('cg-ally-skills-area');
    if (elS) elS.innerHTML = this._buildSkillsHtml();
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
    const gender = ally.gender || '';
    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Identity</h4>
      <div class="cg-ally-field-row">
        <label class="cg-ally-label" for="cg-ally-name">Name</label>
        <input type="text" id="cg-ally-name" class="cg-ally-input"
               value="${esc(ally.name || '')}" placeholder="Ally's name" />
      </div>
      <div class="cg-ally-field-row cg-ally-field-row--inline">
        <div class="cg-ally-field-half">
          <label class="cg-ally-label" for="cg-ally-age">Age</label>
          <input type="text" id="cg-ally-age" class="cg-ally-input"
                 value="${esc(ally.age || '')}" placeholder="Age" />
        </div>
        <div class="cg-ally-field-half">
          <label class="cg-ally-label" for="cg-ally-gender">Gender</label>
          <select id="cg-ally-gender" class="cg-ally-select">
            <option value="">—</option>
            <option value="Male"      ${gender === 'Male'      ? 'selected' : ''}>Male</option>
            <option value="Female"    ${gender === 'Female'    ? 'selected' : ''}>Female</option>
            <option value="Nonbinary" ${gender === 'Nonbinary' ? 'selected' : ''}>Nonbinary</option>
            <option value="Other"     ${gender === 'Other'     ? 'selected' : ''}>Other</option>
          </select>
        </div>
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

  _resolveAllyTraits() {
    const base = { body: 'd6', speed: 'd6', will: 'd6', mind: 'd6', trait_species: 'd6', trait_career: 'd6' };
    const sp   = this._speciesProfile || {};
    const cp   = this._careerProfile  || {};
    const ally = this._getData();

    const giftIds = new Set();
    ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
      if (sp[k]) giftIds.add(String(sp[k]));
      if (cp[k]) giftIds.add(String(cp[k]));
    });
    const improvedIds = Array.isArray(ally.improved_gift_ids) ? ally.improved_gift_ids : [];
    improvedIds.forEach(id => { if (id) giftIds.add(String(id)); });

    for (const id of giftIds) {
      const key = ALLY_TRAIT_BOOSTS[id];
      if (!key) continue;
      const curr = base[key] || 'd6';
      const idx  = ALLY_DIE_ORDER.indexOf(curr);
      if (idx !== -1 && idx < ALLY_DIE_ORDER.length - 1) {
        base[key] = ALLY_DIE_ORDER[idx + 1];
      }
    }
    return base;
  },

  _buildTraitsHtml() {
    const t = this._resolveAllyTraits();
    const allBase = Object.values(t).every(v => v === 'd6');
    const traits = [
      ['Body',    t.body],
      ['Speed',   t.speed],
      ['Will',    t.will],
      ['Mind',    t.mind],
      ['Species', t.trait_species],
      ['Career',  t.trait_career],
    ];
    const note = allBase ? '(all d6)' : '';
    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Traits${note ? ` <span class="cg-ally-note">${note}</span>` : ''}</h4>
      <div class="cg-ally-traits-grid">
        ${traits.map(([label, die]) =>
          `<div class="cg-ally-trait"><span class="cg-ally-trait-label">${label}</span><span class="cg-ally-trait-die">${esc(die)}</span></div>`
        ).join('')}
      </div>
    </div>`;
  },

  /** Return trigger/effect text for a gift by ID (from the loaded gift list). */
  _giftTrigger(giftId) {
    if (!giftId || !this._giftList) return '';
    const g = this._giftList.find(x => String(x.id || x.ct_id || '') === String(giftId));
    if (!g) return '';
    return String(g.trigger || g.effect_description || g.effect || '').trim();
  },

  /** Render one gift row as "Name — trigger text" matching the player sheet style. */
  _giftRowHtml(name, trigger) {
    if (!trigger) return `<li class="cg-ally-gift-item">${esc(name)}</li>`;
    return `<li class="cg-ally-gift-item">${esc(name)}<span class="cg-ally-gift-trigger"> — ${esc(trigger)}</span></li>`;
  },

  _buildGiftsHtml() {
    const ally = this._getData();
    const sp   = this._speciesProfile || {};
    const cp   = this._careerProfile  || {};

    const mainLang = this._getMainLang();
    const count    = this._improvedAllyCount();
    const ids      = Array.isArray(ally.improved_gift_ids) ? ally.improved_gift_ids : [];

    const spGiftRows = [1,2,3].map(i => {
      const name = sp[`gift_${i}`];
      if (!name) return '';
      return this._giftRowHtml(name, this._giftTrigger(sp[`gift_id_${i}`]));
    }).filter(Boolean);

    const cpGiftRows = [1,2,3].map(i => {
      const name = cp[`gift_${i}`];
      if (!name) return '';
      return this._giftRowHtml(name, this._giftTrigger(cp[`gift_id_${i}`]));
    }).filter(Boolean);

    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Gifts</h4>

      <div class="cg-ally-gift-group">
        <div class="cg-ally-gift-label">Language</div>
        <div class="cg-ally-gift-item">${esc(mainLang || '(same as character)')}</div>
      </div>

      ${spGiftRows.length ? `
      <div class="cg-ally-gift-group">
        <div class="cg-ally-gift-label">Species Gifts</div>
        <ul class="cg-ally-gift-list">${spGiftRows.join('')}</ul>
      </div>` : ''}

      ${cpGiftRows.length ? `
      <div class="cg-ally-gift-group">
        <div class="cg-ally-gift-label">Career Gifts</div>
        <ul class="cg-ally-gift-list">${cpGiftRows.join('')}</ul>
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
          ${this._buildGiftOptions(sel, i)}
        </select>
      </div>`;
    }
    html += `</div>`;
    return html;
  },

  /**
   * Builds the context object required by the shared gift-filter.js pipeline.
   * To propagate new rules to the ally: add them to gift-filter.js — no change
   * needed here because both sides call the same giftIneligibleReason().
   */
  _allyGiftCtx() {
    const sp = this._speciesProfile || {};
    const tr = this._resolveAllyTraits();
    // Trait key map: filter uses 'body'/'speed'/'mind'/'will'/'species';
    // ally stores them as tr.body/speed/mind/will and tr.trait_species.
    const traitMap = {
      body:    tr.body,
      speed:   tr.speed,
      mind:    tr.mind,
      will:    tr.will,
      species: tr.trait_species,
    };
    return {
      speciesName: String(sp.name || sp.speciesName || sp.species_name || '').trim().toLowerCase(),
      getTraitDie: (key) => filterDiceToNum(traitMap[String(key).toLowerCase()] || null),
    };
  },

  _buildGiftOptions(selectedId, slotIndex = -1) {
    if (!this._giftList) return '';
    const owned = this._getAllyOwnedGiftIds(slotIndex);
    // Build a set of IDs chosen in OTHER improved slots only
    const ally = this._getData();
    const otherSlotIds = new Set(
      (Array.isArray(ally.improved_gift_ids) ? ally.improved_gift_ids : [])
        .filter((id, idx) => idx !== slotIndex && id)
        .map(String)
    );

    const items = [];
    // Always keep the currently-selected gift visible even if it would fail checks
    if (selectedId) {
      const cur = (this._giftList || []).find(x => String(x.id || x.ct_id || '') === selectedId);
      if (cur) items.push({ id: selectedId, name: String(cur.name || ''), saved: true });
    }

    // Use the shared pipeline — the same giftIneligibleReason that the main
    // gifts tab calls. New rules added to gift-filter.js propagate here for free.
    const ctx = this._allyGiftCtx();
    for (const g of (this._giftList || [])) {
      const id = String(g.id || g.ct_id || '');
      if (!id || id === selectedId) continue; // already added as saved above
      // Extra ally-specific exclusions not in the shared filter
      if (id === '242') continue; // Local Knowledge excluded for allies
      const reason = filterGiftIneligibleReason(g, owned, otherSlotIds, ctx, { skipQualCheck: true });
      if (reason) continue; // hide ineligible gifts
      items.push({ id, name: String(g.name || '') });
    }

    return items.map(o => {
      const label = o.saved ? `${o.name} (saved)` : o.name;
      const sel   = o.id === selectedId ? ' selected' : '';
      return `<option value="${esc(o.id)}"${sel}>${esc(label)}</option>`;
    }).join('');
  },

  /** IDs of all gifts the ally already has.
   *  excludeSlotIndex: the improved-slot position being built (excluded so
   *  the current slot's own value doesn't block re-selecting the same gift). */
  _getAllyOwnedGiftIds(excludeSlotIndex = -1) {
    const sp   = this._speciesProfile || {};
    const cp   = this._careerProfile  || {};
    const ally = this._getData();
    const ids  = new Set();
    ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
      if (sp[k]) ids.add(String(sp[k]));
      if (cp[k]) ids.add(String(cp[k]));
    });
    // Include other improved-ally slot selections
    const improvedIds = Array.isArray(ally.improved_gift_ids) ? ally.improved_gift_ids : [];
    improvedIds.forEach((id, idx) => {
      if (idx !== excludeSlotIndex && id) ids.add(String(id));
    });
    return ids;
  },

  /**
   * Collects every gift ID the ally currently has as a Set of strings.
   * Used to check passive effects (Resolve → Will in Soak, Natural Armor → Species in Soak, etc.)
   */
  _collectAllyGiftIds() {
    const sp   = this._speciesProfile || {};
    const cp   = this._careerProfile  || {};
    const ally = this._getData();
    const ids  = new Set();
    ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
      if (sp[k] && String(sp[k]) !== '0') ids.add(String(sp[k]));
      if (cp[k] && String(cp[k]) !== '0') ids.add(String(cp[k]));
    });
    (Array.isArray(ally.improved_gift_ids) ? ally.improved_gift_ids : []).forEach(id => {
      if (id) ids.add(String(id));
    });
    return ids;
  },

  /**
   * Build the soak parts array and note text from traits + armor + passive gifts.
   * Mirrors the main character logic in battle/index.js.
   *  - Gift 21 (Resolve):      add Will die
   *  - Gift 79 (Natural Armor): add Species die
   *  - Gift 133 (Armored Fighter): raise each armour soak die one step
   */
  _buildAllysoakParts(tr, armor) {
    const DIE_STEPS = ['d4','d6','d8','d10','d12'];
    const raiseDie  = d => {
      const idx = DIE_STEPS.indexOf((d||'').toLowerCase());
      return (idx >= 0 && idx < DIE_STEPS.length - 1) ? DIE_STEPS[idx + 1] : d;
    };
    const giftIds = this._collectAllyGiftIds();
    const armoredFighter = giftIds.has('133');

    const parts = [tr.body];
    const noteExtra = [];
    armor.forEach(a => {
      if (a.soak) parts.push(armoredFighter ? raiseDie(a.soak) : a.soak);
    });
    if (giftIds.has('21') && tr.will) {
      parts.push(tr.will);
      noteExtra.push('Will');
    }
    if (giftIds.has('79') && tr.trait_species) {
      parts.push(tr.trait_species);
      noteExtra.push('Species');
    }
    const noteBase = 'Body' + (armor.length ? ' + Armour' : '');
    const note     = noteExtra.length ? `${noteBase} + ${noteExtra.join(' + ')}` : noteBase;
    return { soakPool: parts.join(' + '), soakNote: note };
  },

  _buildBattleHtml() {
    const sp = this._speciesProfile || {};
    const cp = this._careerProfile  || {};
    const tr = this._resolveAllyTraits();

    const weapons = this._deriveAllyWeapons(sp, cp, tr);
    const armor   = this._deriveAllyArmor(sp, cp);

    const { soakPool, soakNote } = this._buildAllysoakParts(tr, armor);
    const initP  = `${tr.speed} + ${tr.mind}`;
    const dodgeP = tr.speed;

    let html = `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Battle Array</h4>
      <div class="cg-ally-pools">
        <div class="cg-ally-pool">
          <span class="cg-ally-pool-label">Initiative</span>
          <strong class="cg-ally-pool-dice">${esc(initP)}</strong>
          <span class="cg-ally-pool-note">(Speed + Mind)</span>
        </div>
        <div class="cg-ally-pool">
          <span class="cg-ally-pool-label">Dodge</span>
          <strong class="cg-ally-pool-dice">${esc(dodgeP)}</strong>
          <span class="cg-ally-pool-note">(Speed)</span>
        </div>
        <div class="cg-ally-pool">
          <span class="cg-ally-pool-label">Soak</span>
          <strong class="cg-ally-pool-dice">${esc(soakPool)}</strong>
          <span class="cg-ally-pool-note">(${esc(soakNote)})</span>
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

    // Spells (cached from last _fetchAllySpells call)
    const spells = Array.isArray(this._allySpells) ? this._allySpells : [];
    if (spells.length) {
      const spellGroups = {};
      const spellOrder  = [];
      spells.forEach(s => {
        const g = s.gift_name || 'Spells';
        if (!spellGroups[g]) { spellGroups[g] = []; spellOrder.push(g); }
        spellGroups[g].push(s);
      });
      spellOrder.forEach(giftName => {
        html += `<h5 class="cg-ally-table-head">Spells — ${esc(giftName)}</h5>
        <table class="cg-ally-table">
          <thead><tr><th>Name</th><th>Attack Pool</th><th>Equip</th><th>Range</th><th>Effect</th></tr></thead>
          <tbody>${spellGroups[giftName].map(s =>
            `<tr><td>${esc(s.name)}</td><td>${esc(s.attack_dice || '—')}</td><td>${esc(s.equip || '')}</td><td>${esc(s.range || '')}</td><td>${esc(s.effect || '')}</td></tr>`
          ).join('')}</tbody>
        </table>`;
      });
    }

    html += `</div>`;
    return html;
  },

  _resolveSkillName(val) {
    if (!val) return '';
    const s = String(val).trim();
    // If it looks like a pure number it's an ID — look up in skills list
    if (/^\d+$/.test(s)) {
      const list = window.CG_SKILLS_LIST;
      if (Array.isArray(list)) {
        const found = list.find(x => String(x.id || x.skill_id || '') === s);
        if (found) return String(found.name || found.ct_skill_name || s);
      }
      return s; // return raw ID if list not loaded yet
    }
    return s;
  },

  /** Ensure CG_SKILLS_LIST is loaded; returns a promise resolving to the list. */
  _ensureSkillsList() {
    if (Array.isArray(window.CG_SKILLS_LIST) && window.CG_SKILLS_LIST.length) {
      return Promise.resolve(window.CG_SKILLS_LIST);
    }
    const env = ajaxEnv();
    return new Promise(resolve => {
      $.post(env.ajax_url, {
        action: 'cg_get_skills_list',
        security: env.nonce,
        nonce: env.nonce,
        _ajax_nonce: env.nonce,
      }).then(res => {
        let list = [];
        if (res && res.success && Array.isArray(res.data)) list = res.data;
        else if (Array.isArray(res)) list = res;
        list = list.map(s => ({
          id:   String(s.id   || s.skill_id  || ''),
          name: String(s.name || s.ct_skill_name || ''),
        })).filter(s => s.id && s.name);
        window.CG_SKILLS_LIST = list;
        resolve(list);
      }).catch(() => resolve([]));
    });
  },

  /** Build the set of skill names the ally knows from species. */
  _allySpSkillNames(sp) {
    const set  = new Set();
    const spIds = [sp.skill_one_id, sp.skill_two_id, sp.skill_three_id];
    ['skill_one','skill_two','skill_three'].forEach((k, i) => {
      const raw  = sp[k] ? String(sp[k]).trim() : '';
      const name = this._resolveSkillName(raw || spIds[i]);
      if (name && !/^\d+$/.test(name)) set.add(name.toLowerCase());
    });
    return set;
  },

  /** Build the set of skill names the ally knows from career. */
  _allyCpSkillNames(cp) {
    const set = new Set();
    ['skill_name_one','skill_name_two','skill_name_three'].forEach((k, i) => {
      const fallbackKey = ['skill_one','skill_two','skill_three'][i];
      const raw  = String(cp[k] || cp[fallbackKey] || '').trim();
      const name = this._resolveSkillName(raw);
      if (name && !/^\d+$/.test(name)) set.add(name.toLowerCase());
    });
    return set;
  },

  _buildSkillsHtml() {
    const sp = this._speciesProfile || {};
    const cp = this._careerProfile  || {};
    const tr = this._resolveAllyTraits();

    const spSkillNames = this._allySpSkillNames(sp);
    const cpSkillNames = this._allyCpSkillNames(cp);

    // Column headers use the species/career display names
    const speciesLabel = esc(sp.name || sp.speciesName || sp.species_name || 'Species');
    const careerLabel  = esc(cp.name || cp.careerName  || cp.career_name  || 'Career');

    const allSkills = Array.isArray(window.CG_SKILLS_LIST) ? window.CG_SKILLS_LIST : [];

    if (!allSkills.length) {
      this._ensureSkillsList().then(() => {
        const el = document.getElementById('cg-ally-skills-area');
        if (el) el.innerHTML = this._buildSkillsHtml();
      });
      return `<div class="cg-ally-box">
        <h4 class="cg-ally-subhead">Skills</h4>
        <p class="cg-ally-loading">Loading skills…</p>
      </div>`;
    }

    const rows = allSkills.map(skill => {
      const name = String(skill.name || '');
      const lc   = name.toLowerCase();
      const inSp = spSkillNames.has(lc);
      const inCp = cpSkillNames.has(lc);
      const spDie = inSp ? tr.trait_species : '—';
      const cpDie = inCp ? tr.trait_career  : '—';
      let pool = '—';
      if (inSp && inCp) pool = `${tr.trait_species} + ${tr.trait_career}`;
      else if (inSp)   pool = tr.trait_species;
      else if (inCp)   pool = tr.trait_career;
      return { name, spDie, cpDie, pool };
    });

    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Skills</h4>
      <table class="cg-ally-table cg-ally-skills-table">
        <thead>
          <tr>
            <th>Skill</th>
            <th>${speciesLabel}</th>
            <th>${careerLabel}</th>
            <th>Dice Pool</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map(r => `<tr>
            <td>${esc(r.name)}</td>
            <td>${esc(r.spDie)}</td>
            <td>${esc(r.cpDie)}</td>
            <td>${esc(r.pool)}</td>
          </tr>`).join('')}
        </tbody>
      </table>
    </div>`;
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

    // Gift trappings (from active gifts — G1)
    const giftTrappingItems = this._collectAllyGiftIds();
    let giftItems = '';
    giftTrappingItems.forEach(giftId => {
      const items = this._allyGiftTrappings[giftId];
      if (!Array.isArray(items)) return;
      items.forEach(t => {
        giftItems += `<li class="cg-ally-trapping-auto">${esc(t.name || t.token || '')} <em>(gift)</em></li>`;
      });
    });

    // Purchased items
    const purchItems = list.map((t, i) =>
      `<li class="cg-ally-trapping-item">
        ${esc(t.name)}
        <button type="button" class="cg-ally-trapping-remove cg-btn-tiny" data-idx="${i}" title="Remove">✕</button>
      </li>`
    ).join('');

    const hasAny = autoItems || giftItems || purchItems;
    return `
    <div class="cg-ally-box">
      <h4 class="cg-ally-subhead">Trappings</h4>
      ${hasAny ? `
      <ul class="cg-ally-trappings-list">
        ${autoItems}${giftItems}${purchItems}
      </ul>` : '<p class="cg-ally-empty">No trappings yet.</p>'}
    </div>`;
  },

  _buildMoneyHtml() {
    const ally     = this._getData();
    const holdings = (ally.money_holdings && typeof ally.money_holdings === 'object')
      ? ally.money_holdings : {};

    // Full denomination list (G4) — fall back to denar-only if not yet loaded
    if (this._currencyList.length) {
      const rows = this._currencyList.map(c => {
        const val = parseFloat(holdings[c.slug] || 0);
        return `<div class="cg-ally-money">
          <span class="cg-ally-money-label">${esc(c.name)}</span>
          <input type="number" class="cg-ally-money-input cg-ally-denom-input"
                 data-slug="${esc(c.slug)}" value="${val}" min="0" step="any" />
        </div>`;
      }).join('');
      return `
      <div class="cg-ally-box">
        <h4 class="cg-ally-subhead">Money</h4>
        ${rows}
      </div>`;
    }

    // Fallback: denar only
    const denar = parseInt(holdings.denar || 0, 10);
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

  _deriveAllyWeapons(sp, cp, traitMap) {
    // Pull attack dice formulas from species/career trappings data stored in their profiles.
    // Both profiles may expose a `trappings` array similar to main character trappings.
    const weapons = [];
    const seen = new Set();

    for (const profile of [sp, cp]) {
      // Species weapons are stored as weapon_1/weapon_2/weapon_3; career as trappings[]
      const spWeapons = [profile.weapon_1, profile.weapon_2, profile.weapon_3]
        .filter(w => w && typeof w === 'object' && w.name)
        .map(w => ({ kind: 'weapon', name: w.name, attack_dice: w.attack_dice || '',
                     damage_mod: w.damage_mod, range_band: w.range_band || 'Close', effect: w.effect || '' }));
      const trappingWeapons = Array.isArray(profile.trappings)
        ? profile.trappings.filter(t => (t.kind || t.type) === 'weapon')
        : [];
      const list = [...spWeapons, ...trappingWeapons];

      for (const t of list) {
        if (seen.has(t.name)) continue;
        seen.add(t.name);
        const dmgMod = (t.damage_mod != null ? Number(t.damage_mod) : null);
        // Fallback: parse "Damage +N" from effect when damage_mod is null
        const effectM   = dmgMod === null ? (t.effect || '').match(/\b(?:Damage|Dmg)\s*\+(-?\d+)/i) : null;
        const effectDmg = effectM ? parseInt(effectM[1], 10) : null;
        const resolved  = dmgMod !== null ? dmgMod : effectDmg;
        const dmg       = resolved !== null ? (resolved >= 0 ? `+${resolved}` : `${resolved}`) : '';
        const attack    = this._resolveAllyPool(t.attack_dice || '', traitMap);
        weapons.push({ name: t.name || '', attack, damage: dmg, range: t.range_band || 'Close' });
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

  /** Resolve an attack pool string for an ally using the provided trait map. */
  _resolveAllyPool(raw, traitMap) {
    if (!raw) return '';
    const t = traitMap || { body:'d6', speed:'d6', will:'d6', mind:'d6', species:'d6', career:'d6' };
    const lookup = {
      body:    t.body    || 'd6',
      speed:   t.speed   || 'd6',
      will:    t.will    || 'd6',
      mind:    t.mind    || 'd6',
      species: t.trait_species || t.species || 'd6',
      career:  t.trait_career  || t.career  || 'd6',
    };
    const vsIdx = raw.toLowerCase().indexOf(' vs.');
    const part  = vsIdx > -1 ? raw.slice(0, vsIdx) : raw;
    const parts = part.split(',').map(s => s.trim()).filter(Boolean);
    const dice  = [];
    for (const p of parts) {
      const k = p.toLowerCase();
      if (lookup[k]) { dice.push(lookup[k]); }
      else { dice.push(p); }
    }
    return dice.filter(Boolean).join(' + ');
  },

  // ── Gift trappings (G1) ───────────────────────────────────────────────────────

  async _fetchAllyGiftTrappings() {
    const giftIds = [...this._collectAllyGiftIds()];
    if (!giftIds.length) {
      this._refreshTrappingsArea();
      return;
    }

    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) return;

    // Fetch only gift IDs not yet cached
    const newIds = giftIds.filter(id => this._allyGiftTrappings[id] === undefined);
    await Promise.all(newIds.map(async giftId => {
      try {
        const res = await $.post(ajax_url, {
          action:   'cg_get_gift_trappings',
          gift_id:  giftId,
          security: nonce,
          nonce,
          _ajax_nonce: nonce,
        });
        this._allyGiftTrappings[giftId] =
          (res && res.success && Array.isArray(res.data)) ? res.data : [];
      } catch (_) {
        this._allyGiftTrappings[giftId] = [];
      }
    }));

    this._refreshTrappingsArea();
  },

  // ── Spells (G3) ───────────────────────────────────────────────────────────────

  _fetchAllySpells() {
    const giftIds = [...this._collectAllyGiftIds()];
    const key     = giftIds.slice().sort().join(',');

    if (key === this._lastAllySpellKey) return;
    this._lastAllySpellKey = key;

    if (!giftIds.length) {
      this._allySpells = [];
      this._refreshBattleArea();
      return;
    }

    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) return;

    const postData = { action: 'cg_get_spells_for_gifts', security: nonce, nonce, _ajax_nonce: nonce };
    giftIds.forEach((id, i) => { postData[`gift_ids[${i}]`] = id; });

    $.post(ajax_url, postData)
      .then(res => {
        let spells = [];
        if (res && res.success && Array.isArray(res.data)) spells = res.data;
        // Only update if gift set hasn't changed while fetching
        if (key === this._lastAllySpellKey) {
          this._allySpells = spells;
          this._refreshBattleArea();
        }
      })
      .catch(() => {});
  },

  // ── Currency / money (G4) ────────────────────────────────────────────────────

  _fetchCurrency() {
    if (this._currencyList.length) return; // already loaded

    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) return;

    $.post(ajax_url, { action: 'cg_get_money_list', security: nonce, nonce, _ajax_nonce: nonce })
      .then(res => {
        const list = (res && res.success && Array.isArray(res.data)) ? res.data : [];
        if (list.length) {
          this._currencyList = list;
          // Rebuild the currency list indexed by slug for quick lookups
          this._currencyBySlug = {};
          list.forEach(c => { this._currencyBySlug[c.slug] = c; });
          this._refreshMoneyArea();
        }
      })
      .catch(() => {});
  },

  _allyTotalDenarii() {
    const ally     = this._getData();
    const holdings = (ally.money_holdings && typeof ally.money_holdings === 'object') ? ally.money_holdings : {};
    return this._currencyList.reduce((sum, c) => {
      const count = parseFloat(holdings[c.slug] || 0);
      const rate  = parseFloat(c.value_denarii || 0);
      return sum + (count * rate);
    }, 0);
  },

  _allyDeductCost(costD) {
    if (costD <= 0) return true;
    const ally     = this._getData();
    const holdings = Object.assign({}, (ally.money_holdings && typeof ally.money_holdings === 'object') ? ally.money_holdings : {});
    const totalVal = this._allyTotalDenarii();
    if (totalVal < costD - 0.001) return false;

    let remaining = costD;
    const sorted  = this._currencyList
      .filter(c => parseFloat(c.value_denarii || 0) > 0)
      .sort((a, b) => parseFloat(a.value_denarii) - parseFloat(b.value_denarii));

    for (const c of sorted) {
      if (remaining <= 0.001) break;
      const rate  = parseFloat(c.value_denarii || 0);
      if (rate <= 0) continue;
      const have  = parseFloat(holdings[c.slug] || 0);
      if (have <= 0) continue;
      const needed = Math.min(have, Math.ceil((remaining / rate) * 1000) / 1000);
      holdings[c.slug] = Math.max(0, have - needed);
      remaining -= needed * rate;
    }

    this._patch({ money_holdings: holdings });
    return true;
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

  // ── Print / PDF export ───────────────────────────────────────────────────────

  _openPrintWindow() {
    const sheetHtml = this._buildPrintHtml();

    const cssLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
      .map(l => l.outerHTML).join('\n');

    const win = window.open('', '_blank', 'width=900,height=700');
    if (!win) { alert('Please allow pop-ups to print the Ally sheet.'); return; }

    win.document.open();
    win.document.write(`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Ally Sheet</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap">
  ${cssLinks}
  <style>
    @page { size: A4; margin: 1.2cm 1.6cm; }
    body  { margin: 0; padding: 0; background: white; }
  </style>
</head>
<body class="cg-print-window">
  ${sheetHtml}
</body>
</html>`);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); }, 800);
  },

  _buildPrintHtml() {
    const ally = this._getData();
    const sp   = this._speciesProfile || {};
    const cp   = this._careerProfile  || {};

    // ── Identity ──────────────────────────────────────────────────────────────
    const name         = ally.name        || '—';
    const age          = ally.age         || '—';
    const gender       = ally.gender      || '—';
    const desc         = ally.description || '';
    const speciesName  = sp.name || sp.speciesName || '';
    const careerName   = cp.name || cp.careerName  || '';
    const mainCharName = FormBuilderAPI._data?.name || '';
    const mainLang     = this._getMainLang();

    // ── Gift description helper (uses loaded _giftList) ───────────────────────
    const _giftDesc = (id) => {
      if (!id || !this._giftList) return '';
      const g = this._giftList.find(x => String(x.id || x.ct_id || '') === String(id));
      if (!g) return '';
      return String(g.trigger || g.ct_gift_trigger || g.effect || g.ct_gifts_effect_description || '').trim();
    };
    const _giftNameById = (id) => {
      if (!id || !this._giftList) return String(id);
      const g = this._giftList.find(x => String(x.id || x.ct_id || '') === String(id));
      return g ? String(g.name || g.ct_gifts_name || id) : String(id);
    };

    // ── Combat pools ─────────────────────────────────────────────────────────
    const tr      = this._resolveAllyTraits();
    const weapons = this._deriveAllyWeapons(sp, cp, tr);
    const armor   = this._deriveAllyArmor(sp, cp);
    const { soakPool: soak, soakNote } = this._buildAllysoakParts(tr, armor);
    const initP  = `${tr.speed} + ${tr.mind}`;
    const dodgeP = tr.speed;

    // ── Movement (mirrors summary/api.js logic) ───────────────────────────────
    const _dieMax = { d4: 4, d6: 6, d8: 8, d10: 10, d12: 12 };
    const maxSpeed = _dieMax[(tr.speed || '').toLowerCase()] || 0;
    const maxBody  = _dieMax[(tr.body  || '').toLowerCase()] || 0;
    const _dash    = maxSpeed > 0
      ? Math.floor(maxSpeed / 2) + (maxBody > maxSpeed ? 1 : 0)
      : '—';
    const _dashNum = typeof _dash === 'number' ? _dash : 0;
    const _run     = (maxBody > 0 && maxSpeed > 0) ? maxBody + maxSpeed + _dashNum : '—';
    const hqCount  = maxBody > 0 ? maxBody : 8;
    const hqCircles = Array.from({ length: hqCount }, () => `<span class="cg-hq-circle"></span>`).join('');

    // ── Trappings + money ─────────────────────────────────────────────────────
    const trappings     = Array.isArray(ally.trappings_list) ? ally.trappings_list : [];
    const autoTrappings = [...weapons, ...armor];
    const _holdings = (ally.money_holdings && typeof ally.money_holdings === 'object') ? ally.money_holdings : {};

    // Gift trappings from cached fetch (G1)
    const printGiftTrappings = [];
    this._collectAllyGiftIds().forEach(giftId => {
      const items = this._allyGiftTrappings[giftId];
      if (!Array.isArray(items)) return;
      items.forEach(t => printGiftTrappings.push(t));
    });

    // Spells (G3)
    const printSpells = Array.isArray(this._allySpells) ? this._allySpells : [];
    const printSpellGroups = {};
    const printSpellOrder  = [];
    printSpells.forEach(s => {
      const g = s.gift_name || 'Spells';
      if (!printSpellGroups[g]) { printSpellGroups[g] = []; printSpellOrder.push(g); }
      printSpellGroups[g].push(s);
    });

    // ── Skills (4-col: Skill | Species | Career | Pool) ───────────────────────
    const spSkillNamesP = this._allySpSkillNames(sp);
    const cpSkillNamesP = this._allyCpSkillNames(cp);
    const allSkillsP    = Array.isArray(window.CG_SKILLS_LIST) ? window.CG_SKILLS_LIST : [];
    const printSpLabel  = esc(speciesName || 'Species');
    const printCpLabel  = esc(careerName  || 'Career');
    const skillsRows = allSkillsP.map(skill => {
      const n  = String(skill.name || '');
      const lc = n.toLowerCase();
      const inSp = spSkillNamesP.has(lc);
      const inCp = cpSkillNamesP.has(lc);
      const spDie = inSp ? tr.trait_species : '—';
      const cpDie = inCp ? tr.trait_career  : '—';
      let pool = '—';
      if (inSp && inCp) pool = `${tr.trait_species} + ${tr.trait_career}`;
      else if (inSp)    pool = tr.trait_species;
      else if (inCp)    pool = tr.trait_career;
      return `<tr><td>${esc(n)}</td><td>${esc(spDie)}</td><td>${esc(cpDie)}</td><td>${esc(pool)}</td></tr>`;
    });

    // ── Weapons / Armour table rows ───────────────────────────────────────────
    const weaponRows = weapons.map(w =>
      `<tr><td>${esc(w.name)}</td><td>${esc(w.attack)}</td><td>${esc(w.damage)}</td><td>${esc(w.range)}</td></tr>`
    ).join('');
    const armorRows = armor.map(a =>
      `<tr><td>${esc(a.name)}</td><td>${esc(a.soak)}</td></tr>`
    ).join('');

    // ── Trapping equipment list rows ──────────────────────────────────────────
    const trappingRows = [
      ...autoTrappings.map(t => `<li>${esc(t.name)} <em>(auto)</em></li>`),
      ...printGiftTrappings.map(t => `<li>${esc(t.name || t.token || '')} <em>(gift)</em></li>`),
      ...trappings.map(t => `<li>${esc(t.name)}${t.cost_d ? ` — ${esc(t.cost_d)}d` : ''}</li>`),
    ].join('');

    // ── Species gifts block (with descriptions — mirrors main char sheet) ──────
    let speciesGiftsHtml = '';
    if (mainLang) {
      speciesGiftsHtml += `<li><strong>Language:</strong> <em>${esc(mainLang)}</em></li>`;
    }
    [1,2,3].forEach(i => {
      const giftName = sp[`gift_${i}`];
      const giftId   = sp[`gift_id_${i}`];
      if (!giftName) return;
      const d = _giftDesc(giftId);
      speciesGiftsHtml += `<li><strong>${esc(giftName)}</strong>${d ? `<span class="summary-gift-desc"> — ${esc(d)}</span>` : ''}</li>`;
    });

    // ── Career gifts block (with descriptions) ────────────────────────────────
    let careerGiftsHtml = '';
    [1,2,3].forEach(i => {
      const giftName = cp[`gift_${i}`];
      const giftId   = cp[`gift_id_${i}`];
      if (!giftName) return;
      const d = _giftDesc(giftId);
      careerGiftsHtml += `<li><strong>${esc(giftName)}</strong>${d ? `<span class="summary-gift-desc"> — ${esc(d)}</span>` : ''}</li>`;
    });

    // ── Improved Ally gifts block ─────────────────────────────────────────────
    const improvedIds = Array.isArray(ally.improved_gift_ids) ? ally.improved_gift_ids : [];
    let improvedGiftsHtml = '';
    improvedIds.filter(Boolean).forEach(id => {
      const n = _giftNameById(id);
      const d = _giftDesc(id);
      improvedGiftsHtml += `<li><strong>${esc(n)}</strong>${d ? `<span class="summary-gift-desc"> — ${esc(d)}</span>` : ''}</li>`;
    });

    // ── Traits list (vertical, matches main char right-col format) ────────────
    const traitItems = [
      ['Will',    tr.will],
      ['Mind',    tr.mind],
      ['Speed',   tr.speed],
      ['Species', tr.trait_species],
      ['Body',    tr.body],
      ['Career',  tr.trait_career],
    ];
    const traitsHtml = traitItems.map(([label, die]) =>
      `<li><strong>${label}:</strong> ${esc(die || '—')}</li>`
    ).join('');

    return `
<div id="cg-summary-sheet">

  <!-- ══ ALLY SHEET BANNER ══ -->
  <div class="summary-ally-banner">Ally Sheet</div>

  <!-- ══ HEADER ══ -->
  <div class="summary-header-block">
    <h2>${esc(name)}</h2>
    <div class="summary-basic-row">
      <span><strong>Age:</strong> ${esc(age)}</span>
      <span><strong>Gender:</strong> ${esc(gender)}</span>
      ${speciesName  ? `<span><strong>Species:</strong> ${esc(speciesName)}</span>`   : ''}
      ${careerName   ? `<span><strong>Career:</strong> ${esc(careerName)}</span>`     : ''}
      ${mainCharName ? `<span><strong>Ally of:</strong> ${esc(mainCharName)}</span>` : ''}
    </div>
    ${desc ? `<div class="summary-motto"><em>${esc(desc)}</em></div>` : ''}
  </div>

  <!-- ══ PAGE 1 — two columns ══ -->
  <div class="summary-page1-body">

    <!-- Left column: Species / Career / Gifts -->
    <div class="summary-col-left">

      ${speciesName ? `
      <div class="summary-section summary-species">
        <h3>Species: ${esc(speciesName)}</h3>
        ${(() => {
          const parts = [];
          if (sp.habitat) parts.push(`<span><strong>Habitat:</strong> ${esc(sp.habitat)}</span>`);
          if (sp.diet)    parts.push(`<span><strong>Diet:</strong> ${esc(sp.diet)}</span>`);
          if (sp.cycle)   parts.push(`<span><strong>Cycle:</strong> ${esc(sp.cycle)}</span>`);
          const senses = [sp.sense_1, sp.sense_2, sp.sense_3].filter(Boolean);
          if (senses.length) parts.push(`<span><strong>Senses:</strong> ${esc(senses.join(', '))}</span>`);
          return parts.length ? `<div class="summary-species-traits">${parts.join(' &nbsp;|&nbsp; ')}</div>` : '';
        })()}
        ${speciesGiftsHtml ? `<ul>${speciesGiftsHtml}</ul>` : ''}
      </div>` : ''}

      ${careerName ? `
      <div class="summary-section summary-career">
        <h3>Career: ${esc(careerName)}</h3>
        ${careerGiftsHtml ? `<ul>${careerGiftsHtml}</ul>` : ''}
      </div>` : ''}

      ${improvedGiftsHtml ? `
      <div class="summary-section summary-gifts">
        <h3>Improved Ally</h3>
        <ul>${improvedGiftsHtml}</ul>
      </div>` : ''}

    </div><!-- /col-left -->

    <!-- Right column: Traits / Battle Array / Movement / Damage / Weapons / Armour -->
    <div class="summary-col-right">

      <div class="summary-section summary-traits">
        <h3>Traits</h3>
        <ul>${traitsHtml}</ul>
      </div>

      <div class="summary-section summary-battle">
        <h3>Battle Array</h3>
        <div class="summary-battle-pools">
          <table class="cg-battle-summary-table cg-battle-pools-table">
            <thead><tr><th>Pool</th><th>Dice</th></tr></thead>
            <tbody>
              <tr><td>Initiative</td><td>${esc(initP)}</td></tr>
              <tr><td>Dodge</td><td>${esc(dodgeP)}</td></tr>
              <tr><td>Soak (${esc(soakNote)})</td><td>${esc(soak)}</td></tr>
            </tbody>
          </table>
        </div>

        <div class="summary-movement">
          <h4 class="summary-sub-heading">Movement</h4>
          <table class="cg-battle-summary-table">
            <thead><tr><th>Mode</th><th>Value</th><th>Formula</th></tr></thead>
            <tbody>
              <tr><td>Stride</td><td>1</td><td>(1)</td></tr>
              <tr><td>Dash</td><td>${_dash}</td><td>(½ Max Speed, +1 if Body&gt;Speed)</td></tr>
              <tr><td>Sprint</td><td>${esc(tr.speed) || '—'}</td><td>(Speed die)</td></tr>
              <tr><td>Run</td><td>${_run}</td><td>(Max Body + Max Speed + Dash)</td></tr>
            </tbody>
          </table>
        </div>

        <div class="summary-damage-track">
          <h4 class="summary-sub-heading">Damage &amp; Other Status</h4>
          <div class="cg-damage-track-layout">
            <div class="cg-hit-states">
              <div class="cg-hit-row"><span class="cg-hit-count">no hits</span><span class="cg-hit-pip"></span><span class="cg-hit-name">Reeling</span><span class="cg-hit-note">(penalty d8; no Counters)</span></div>
              <div class="cg-hit-row"><span class="cg-hit-count">1 hit</span><span class="cg-hit-pip"></span><span class="cg-hit-name">Hurt</span><span class="cg-hit-note">(+1 damage)</span></div>
              <div class="cg-hit-row"><span class="cg-hit-count">2 hits</span><span class="cg-hit-pip"></span><span class="cg-hit-name">Afraid</span><span class="cg-hit-note">(cannot attack or rally)</span></div>
              <div class="cg-hit-row"><span class="cg-hit-count">3 hits</span><span class="cg-hit-pip"></span><span class="cg-hit-name">Injured</span><span class="cg-hit-note">(+1 damage)</span></div>
              <div class="cg-healing-quota"><span class="cg-hq-label">Healing Quota</span><span class="cg-hq-circles">${hqCircles}</span></div>
              <div class="cg-hit-row"><span class="cg-hit-count">4 hits</span><span class="cg-hit-pip"></span><span class="cg-hit-name">Dying</span><span class="cg-hit-note">(get first aid!)</span></div>
              <div class="cg-hit-row"><span class="cg-hit-count">5 hits</span><span class="cg-hit-pip"></span><span class="cg-hit-name">Dead</span><span class="cg-hit-note">(beyond mortal help)</span></div>
            </div>
            <div class="cg-status-conditions">
              <div class="cg-status-row"><span class="cg-hit-pip"></span><span class="cg-status-name">Burdened</span><span class="cg-hit-note">(Dash is zero, limit of d8 to Speed Skills)</span></div>
              <div class="cg-status-row"><span class="cg-hit-pip"></span><span class="cg-status-name">Over-Burdened</span><span class="cg-hit-note">(Burdened, can't run, disadvantaged)</span></div>
              <div class="cg-status-row"><span class="cg-hit-pip"></span><span class="cg-status-name">Knockdown</span><span class="cg-hit-note">(disadvantaged, can't retreat)</span></div>
              <div class="cg-status-row"><span class="cg-hit-pip"></span><span class="cg-status-name">Unconscious</span><span class="cg-hit-note">(helpless)</span></div>
            </div>
          </div>
        </div>

        ${weaponRows ? `
        <h4 class="summary-sub-heading">Weapons</h4>
        <table class="cg-battle-summary-table">
          <thead><tr><th>Name</th><th>Attack Pool</th><th>Damage</th><th>Range</th></tr></thead>
          <tbody>${weaponRows}</tbody>
        </table>` : ''}

        ${armorRows ? `
        <h4 class="summary-sub-heading">Armour</h4>
        <table class="cg-battle-summary-table">
          <thead><tr><th>Name</th><th>Soak Dice</th></tr></thead>
          <tbody>${armorRows}</tbody>
        </table>` : ''}

        ${printSpellOrder.map(gName => `
        <h4 class="summary-sub-heading">Spells — ${esc(gName)}</h4>
        <table class="cg-battle-summary-table">
          <thead><tr><th>Name</th><th>Attack Pool</th><th>Equip</th><th>Range</th><th>Effect</th></tr></thead>
          <tbody>${printSpellGroups[gName].map(s =>
            `<tr><td>${esc(s.name)}</td><td>${esc(s.attack_dice || '—')}</td><td>${esc(s.equip || '')}</td><td>${esc(s.range || '')}</td><td>${esc(s.effect || '')}</td></tr>`
          ).join('')}</tbody>
        </table>`).join('')}

      </div><!-- /summary-battle -->

    </div><!-- /col-right -->

  </div><!-- /page1-body -->

  <!-- ══ PAGE 2 ══ -->
  <div class="summary-page2">

    ${skillsRows.length ? `
    <div class="summary-section summary-skills">
      <h3>Skills</h3>
      <div class="cg-summary-skills-wrap">
        <table class="cg-summary-skills">
          <thead><tr><th>Skill</th><th>${printSpLabel}</th><th>${printCpLabel}</th><th>Dice Pool</th></tr></thead>
          <tbody>${skillsRows.join('')}</tbody>
        </table>
      </div>
    </div>` : ''}

    ${trappingRows ? `
    <div class="summary-section summary-equipment">
      <h3>Equipment</h3>
      <ul>${trappingRows}</ul>
    </div>` : ''}

    ${(() => {
      if (this._currencyList.length) {
        const denomRows = this._currencyList
          .map(c => {
            const val = parseFloat(_holdings[c.slug] || 0);
            return val > 0 ? `<span><strong>${esc(c.name)}:</strong> ${val}</span>` : '';
          })
          .filter(Boolean)
          .join(' &nbsp;|&nbsp; ');
        return denomRows ? `
        <div class="summary-section summary-money">
          <h3>Money</h3>
          <div class="summary-money-row">${denomRows}</div>
        </div>` : '';
      }
      const denar = parseInt(_holdings.denar || 0, 10);
      return denar > 0 ? `
      <div class="summary-section summary-money">
        <h3>Money</h3>
        <div class="summary-money-row"><span><strong>Denar:</strong> ${denar}</span></div>
      </div>` : '';
    })()}

    ${desc ? `
    <div class="summary-section summary-description">
      <h3>Description</h3>
      <p>${esc(desc)}</p>
    </div>` : ''}

  </div><!-- /page2 -->

</div>`;
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

    if (cost > 0) {
      if (this._currencyList.length) {
        // Multi-denomination path (G4): sync DOM → holdings first, then deduct
        this._syncMoneyFromDom();
        const totalD = this._allyTotalDenarii();
        if (totalD < cost - 0.001) {
          alert(`Not enough funds. Need ${cost}D, have ${totalD.toFixed(2)}D total.`);
          return;
        }
        if (!this._allyDeductCost(cost)) {
          alert(`Could not deduct ${cost}D from holdings.`);
          return;
        }
      } else {
        // Legacy denar-only path
        const ally     = this._getData();
        const holdings = (ally.money_holdings && typeof ally.money_holdings === 'object')
          ? { ...ally.money_holdings } : {};
        const denarEl = document.getElementById('cg-ally-money-denar');
        const current = parseInt(denarEl?.value || holdings.denar || 0, 10);
        if (current < cost) {
          alert(`Not enough money. Need ${cost} denar, have ${current}.`);
          return;
        }
        holdings.denar = Math.max(0, current - cost);
        if (denarEl) denarEl.value = holdings.denar;
        const ally2 = this._getData();
        const trappings2 = Array.isArray(ally2.trappings_list) ? [...ally2.trappings_list] : [];
        trappings2.push({ name, cost_d: cost });
        this._patch({ trappings_list: trappings2, money_holdings: holdings });
        this._refreshTrappingsArea();
        this._refreshMoneyArea();
        return;
      }
    }

    const ally3 = this._getData();
    const trappings3 = Array.isArray(ally3.trappings_list) ? [...ally3.trappings_list] : [];
    trappings3.push({ name, cost_d: cost });
    this._patch({ trappings_list: trappings3 });
    this._refreshTrappingsArea();
    this._refreshMoneyArea();
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
    const ally     = this._getData();
    const holdings = (ally.money_holdings && typeof ally.money_holdings === 'object')
      ? { ...ally.money_holdings } : {};

    // Multi-denomination inputs (G4)
    document.querySelectorAll('.cg-ally-denom-input[data-slug]').forEach(inp => {
      const slug = inp.dataset.slug;
      if (slug) holdings[slug] = parseFloat(inp.value) || 0;
    });

    // Legacy denar-only fallback
    const denarEl = document.getElementById('cg-ally-money-denar');
    if (denarEl) holdings.denar = parseInt(denarEl.value || 0, 10);

    this._patch({ money_holdings: holdings });
  },
};

// ── Module export & global handle ────────────────────────────────────────────

$(document).on('input.ally-money', '#cg-ally-money-denar, .cg-ally-denom-input', () => {
  AllyModule._syncMoneyFromDom();
});

window.CG_AllyModule = AllyModule;

export default AllyModule;
