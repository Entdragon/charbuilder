// assets/js/src/core/summary/api.js

import FormBuilderAPI        from '../formBuilder';
import * as Utils            from './utils.js';
import TraitsService         from '../traits/service.js';
import SpeciesAPI            from '../species/api.js';
import CareerAPI             from '../career/api.js';

import { marksToDice }       from '../../utils/marks-dice.js';
import { resolveAttackPool } from '../../utils/resolve-attack-pool.js';
import { compactPool }       from '../../utils/compact-pool.js';

const $ = window.jQuery;
const TRAITS = TraitsService.TRAITS;

const SummaryAPI = {
  /**
   * Entry point when the Summary tab is shown.
   */
  init() {
    // CG HARDEN: make init() idempotent
    // builder-refresh may call init() many times; this prevents duplicate bindings.
    if (this.__cg_inited) {
      try {
        // Still refresh summary from current builder state on repeated calls
        if (typeof window !== 'undefined' && window.FormBuilderAPI && typeof window.FormBuilderAPI.getData === 'function') {
          this.renderSummary(window.FormBuilderAPI.getData() || {});
            // Keep Summary bindings alive even on repeated init() calls
            try { this.bindExportButton();

      // Live updates: keep #cg-summary-sheet in sync while typing on other tabs
      this.bindLiveUpdates(); } catch (_) {}
            try { this.bindLiveUpdates(); } catch (_) {}

        }
      } catch (_) {}
      return;
    }
    this.__cg_inited = true;
    const data = FormBuilderAPI.getData();
    console.log('[SummaryAPI] init — builder state:', data);

    // 1) Render all summary sections
    this.renderSummary(data);

    

      // CG HARDEN: auto-render on form changes
      this.bindAutoRender();// 2) Wire up the Export to PDF button
    this.bindExportButton();
  },

  /**
   * Build and inject the full summary into #cg-summary-sheet.
   */
  renderSummary(data = {}) {
    const $sheet = $('#cg-summary-sheet').empty();

    // ── Source data ────────────────────────────────────────────
    const name       = data.name        || '—';
    const age        = data.age         || '—';
    const gender     = data.gender      || '—';
    const playerName = data.player_name || '';
    const motto      = data.motto       || '—';
    const description = data.description || '';
    const backstory   = data.backstory   || '';

    const goal1 = data.goal1 || '';
    const goal2 = data.goal2 || '';
    const goal3 = data.goal3 || '';
    const goalsList = [goal1, goal2, goal3].filter(Boolean);

    const species = SpeciesAPI.currentProfile || {};
    const career  = CareerAPI.currentProfile  || {};

    let extraCareers = [];
    if (Array.isArray(data.extraCareers) && data.extraCareers.length) {
      extraCareers = data.extraCareers.filter(ec => ec && ec.id);
    } else if (typeof data.extra_careers === 'string' && data.extra_careers.trim()) {
      try { extraCareers = JSON.parse(data.extra_careers).filter(ec => ec && ec.id); } catch (_) {}
    }

    const DIE_ORDER = ['d4','d6','d8','d10','d12'];
    function stepDie(die, steps) {
      const i = DIE_ORDER.indexOf(String(die || '').toLowerCase());
      if (i === -1) return die || 'd4';
      return DIE_ORDER[Math.min(DIE_ORDER.length - 1, i + (parseInt(steps, 10) || 0))];
    }
    function boostCountFor(careerId) {
      let n = 0;
      const target = String(careerId);
      for (let slot = 0; slot <= 2; slot++) {
        const v = data[`increased_trait_career_target_${slot}`];
        if (v != null && String(v) === target) n++;
      }
      if (n === 0) {
        const leg = data['increased_trait_career_target'];
        if (leg != null && String(leg) === target) n++;
      }
      return n;
    }

    const skills       = window.CG_SKILLS_LIST || [];
    const marks        = data.skillMarks        || {};
    const xpMarks      = data.xpSkillMarks      || {};
    const xpGifts      = Array.isArray(data.xpGifts) ? data.xpGifts : [];
    const xpEarned     = parseInt(data.experience_points, 10) || 0;
    const xpMarksBudget = parseInt(data.xpMarksBudget, 10) || 0;
    const xpGiftSlots   = parseInt(data.xpGiftSlots,   10) || 0;
    const xpSpent       = xpMarksBudget * 4 + xpGiftSlots * 10;

    const weapons = Array.isArray(data.weapons) ? data.weapons : [];
    const armor   = Array.isArray(data.armor)   ? data.armor   : [];
    const spells  = Array.isArray(FormBuilderAPI._data?.spells)
      ? FormBuilderAPI._data.spells
      : (Array.isArray(data.spells) ? data.spells : []);

    // Full trappings list (from TrappingsAPI — stored directly on FormBuilderAPI._data)
    const trappingsList = Array.isArray(FormBuilderAPI._data?.trappings_list)
      ? FormBuilderAPI._data.trappings_list
      : [];

    // Money holdings (from TrappingsAPI — stored directly on FormBuilderAPI._data)
    const moneyHoldings  = (FormBuilderAPI._data?.money_holdings && typeof FormBuilderAPI._data.money_holdings === 'object')
      ? FormBuilderAPI._data.money_holdings
      : {};
    const trappingsAPI   = window.CG_TrappingsAPI;
    const currencyList   = (trappingsAPI && Array.isArray(trappingsAPI._currencyList))
      ? trappingsAPI._currencyList
      : [];
    const hasMoney = currencyList.some(c => parseFloat(moneyHoldings[c.slug] || 0) > 0)
      || Object.values(moneyHoldings).some(v => parseFloat(v) > 0);

    const skillNotes = (data.skill_notes && typeof data.skill_notes === 'object') ? data.skill_notes : {};

    function dicePools(...dice) { return dice.filter(Boolean).join(' + ') || '—'; }
    const initiative = compactPool(dicePools(data.speed, data.will));
    const dodgeSkillPool = resolveAttackPool('Dodge');
    const _diePat = /^d\d+$/;
    const dodge = compactPool(dicePools(data.speed, ...dodgeSkillPool.split('+').map(s => s.trim()).filter(s => _diePat.test(s))));
    const armorSoakDice = armor.map(a => a.soak).filter(Boolean);

    // Collect all active gift IDs (mirrors collectAllGiftIds in battle/index.js)
    const _activeGiftIds = (() => {
      const ids = new Set();
      (Array.isArray(data.free_gifts) ? data.free_gifts : []).forEach(id => { if (id) ids.add(String(id)); });
      if (data.free_gift_1) ids.add(String(data.free_gift_1));
      if (data.free_gift_2) ids.add(String(data.free_gift_2));
      if (data.free_gift_3) ids.add(String(data.free_gift_3));
      (Array.isArray(data.xpGifts) ? data.xpGifts : []).forEach(id => { if (id) ids.add(String(id)); });
      const cp = (CareerAPI && CareerAPI.currentProfile) || {};
      ['gift_id_1','gift_id_2','gift_id_3','gift1_id','gift2_id','gift3_id'].forEach(k => { if (cp[k]) ids.add(String(cp[k])); });
      const repl = data.career_gift_replacements || {};
      Object.values(repl).forEach(id => { if (id) ids.add(String(id)); });
      const sp = (SpeciesAPI && SpeciesAPI.currentProfile) || {};
      ['gift_id_1','gift_id_2','gift_id_3','gift1_id','gift2_id','gift3_id'].forEach(k => { if (sp[k]) ids.add(String(sp[k])); });
      return [...ids].filter(id => id && id !== '0');
    })();
    const _extraSoak = [];
    if (_activeGiftIds.includes('21') && data.will)          _extraSoak.push(data.will);          // Resolve
    if (_activeGiftIds.includes('79') && data.trait_species) _extraSoak.push(data.trait_species); // Natural Armor

    const soak = compactPool(dicePools(data.body, ...armorSoakDice, ..._extraSoak));

    const allCareerNames = [career.careerName].filter(Boolean);
    extraCareers.forEach(ec => { if (ec.name) allCareerNames.push(ec.name); });
    const careerLabel = allCareerNames.length ? allCareerNames.join(' / ') : '—';

    // ── Gift lookup helpers ───────────────────────────────────
    // Defaults module caches: { 242: lkGift, 236: langGift, 159: csGift }
    const _defaultsCache = (() => {
      const d = window.CG_GiftsDefaults;
      const map = {};
      if (d) {
        if (d._lkGift)          map['242'] = d._lkGift;
        if (d._langGift)        map['236'] = d._langGift;
        if (d._csGift)          map['159'] = d._csGift;
        if (d._personalityGift) map[String(d._personalityGift.id || '')] = d._personalityGift;
      }
      return map;
    })();

    // Personality gift ID for summary desc lookup
    const _personalityGiftId = (() => {
      const d = window.CG_GiftsDefaults;
      return (d && d._personalityGift && d._personalityGift.id) ? String(d._personalityGift.id) : '';
    })();

    function _findGift(giftId) {
      if (!giftId) return null;
      const sid = String(giftId);
      // check defaults cache first (these may be filtered out of _allGifts)
      if (_defaultsCache[sid]) return _defaultsCache[sid];
      const fc = window.CG_FreeChoices;
      const allGifts = (fc && Array.isArray(fc._allGifts)) ? fc._allGifts : [];
      return allGifts.find(g => String(g.ct_id || g.id || '') === sid) || null;
    }

    function giftDesc(giftId) {
      const g = _findGift(giftId);
      if (!g) return '';
      const short = String(g.effect ?? '').trim();
      return short || String(g.effect_description || g.ct_gifts_effect_description || '').trim();
    }

    function giftName(giftId) {
      const g = _findGift(giftId);
      return g ? String(g.ct_gift_name || g.name || giftId) : String(giftId);
    }

    // ── Species gifts block ────────────────────────────────────
    let speciesGiftsHtml = '';
    ['gift_1','gift_2','gift_3'].forEach((_, idx) => {
      const gift   = species[`gift_${idx+1}`];
      const giftId = species[`gift_id_${idx+1}`];
      const mult   = species[`manifold_${idx+1}`] || 1;
      if (gift) {
        const desc = giftDesc(giftId);
        speciesGiftsHtml += `<li><strong>${gift}${mult > 1 ? ` × ${mult}` : ''}</strong>${desc ? `<span class="summary-gift-desc"> — ${desc}</span>` : ''}</li>`;
      }
    });

    // ── Career gifts block ─────────────────────────────────────
    let careerGiftsHtml = '';
    ['gift_1','gift_2','gift_3'].forEach((_, idx) => {
      const gift   = career[`gift_${idx+1}`];
      const giftId = career[`gift_id_${idx+1}`];
      const mult   = career[`manifold_${idx+1}`] || 1;
      if (gift) {
        const desc = giftDesc(giftId);
        careerGiftsHtml += `<li><strong>${gift}${mult > 1 ? ` × ${mult}` : ''}</strong>${desc ? `<span class="summary-gift-desc"> — ${desc}</span>` : ''}</li>`;
      }
    });

    // ── Traits block ───────────────────────────────────────────
    let traitsHtml = '';
    TRAITS.forEach(key => {
      let label = key.replace(/^trait_/, '');
      if (label === 'species') label = 'Species';
      else if (label === 'career') label = 'Career';
      else label = Utils.capitalize(label);
      const base    = data[key] || '—';
      const boost   = TraitsService.getBoostedDie(key);
      const display = boost ? `${base} → ${boost}` : base;
      traitsHtml += `<li><strong>${label}:</strong> ${display}</li>`;
    });
    if (extraCareers.length) {
      extraCareers.forEach(ec => {
        const ecName = ec.name || 'Extra Career';
        const boosts = boostCountFor(String(ec.id));
        const ecDie  = boosts > 0 ? stepDie('d4', boosts) : 'd4';
        const suffix = boosts > 0 ? ` → ${ecDie}` : '';
        traitsHtml += `<li><strong>${ecName} (Career):</strong> d4${suffix}</li>`;
      });
    }

    // ── Skills block ───────────────────────────────────────────
    // Species skills are stored as TEXT NAMES; career skills are stored as numeric IDs.
    // Read trait die from DOM first (same as BattleAPI.traitDie) so live values are used.
    const _traitDie = (key) => {
      const dom = document.getElementById(`cg-${key}`);
      if (dom && dom.value) return dom.value;
      return data[key] || '';
    };

    // ── Movement stats ──────────────────────────────────────────
    const _dieMax   = { d4: 4, d6: 6, d8: 8, d10: 10, d12: 12 };
    const _dieToMax = d => _dieMax[String(d || '').toLowerCase()] || 0;
    const _speedDie = _traitDie('speed');
    const _maxSpeed = _dieToMax(_speedDie);
    const _maxBody  = _dieToMax(_traitDie('body'));
    const _dash     = _maxSpeed > 0
      ? Math.floor(_maxSpeed / 2) + (_maxBody > _maxSpeed ? 1 : 0)
      : '—';
    const _dashNum  = typeof _dash === 'number' ? _dash : 0;
    const _run      = (_maxBody > 0 && _maxSpeed > 0)
      ? _maxBody + _maxSpeed + _dashNum
      : '—';
    const _hqCount  = _maxBody > 0 ? _maxBody : 8;
    const _hqCircles = Array.from({ length: _hqCount }, () =>
      `<span class="cg-hq-circle"></span>`).join('');

    const spTraitDie = _traitDie('trait_species');
    const cpTraitDie = _traitDie('trait_career');
    const spSkillIds = [species.skill_one, species.skill_two, species.skill_three]
      .filter(s => s != null && s !== '').map(s => String(s));
    const cpIds      = [career.skill_one, career.skill_two, career.skill_three].map(String);
    const ecSkillSets = extraCareers.map(ec =>
      (Array.isArray(ec.skills) ? ec.skills : []).map(String)
    );
    let skillsHtml = '';
    skills.forEach(skill => {
      const id    = String(skill.id);
      const spDie = (spTraitDie && spSkillIds.includes(id)) ? spTraitDie : '';
      const cpDie   = (cpTraitDie && cpIds.includes(id))        ? cpTraitDie : '';
      const ecDies = ecSkillSets.map(set => set.includes(id) ? 'd4' : '').filter(Boolean);
      const totalMk = (parseInt(marks[id], 10) || 0) + (parseInt(xpMarks[id], 10) || 0);
      const mkDie   = marksToDice(totalMk);
      const poolDice = [spDie, cpDie].concat(ecDies).concat([mkDie]).filter(Boolean);
      const pool = poolDice.length ? compactPool(poolDice.join(' + ')) : '—';
      const note = skillNotes[id] ? String(skillNotes[id]).trim() : '';
      const nameCell = note
        ? `${skill.name}<span class="summary-skill-note"> (${note})</span>`
        : skill.name;
      skillsHtml += `<tr><td>${nameCell}</td><td>${pool}</td></tr>`;
    });

    // ── Weapons block ──────────────────────────────────────────
    let weaponsHtml = '';
    if (weapons.length) {
      weaponsHtml = `
        <h4 class="summary-sub-heading">Weapons</h4>
        <table class="cg-battle-summary-table">
          <thead><tr><th>Name</th><th>Attack Pool</th><th>Damage</th><th>Range</th><th>Notes</th></tr></thead>
          <tbody>${weapons.map(w => {
            const _atkRaw = w.attack || (w._attack_dice_raw ? resolveAttackPool(w._attack_dice_raw) : '');
            const atk = _atkRaw ? compactPool(_atkRaw) : _atkRaw;
            return `<tr>
              <td>${w.name   || '—'}</td><td>${atk || '—'}</td>
              <td>${w.damage || '—'}</td><td>${w.range  || 'Melee'}</td>
              <td>${w.notes  || ''}</td>
            </tr>`;
          }).join('')}</tbody>
        </table>`;
    }

    // ── Spells block ───────────────────────────────────────────
    let spellsHtml = '';
    if (spells.length) {
      // Group by gift_name
      const spellGroups = {};
      const spellOrder  = [];
      spells.forEach(s => {
        const g = s.gift_name || 'Spells';
        if (!spellGroups[g]) { spellGroups[g] = []; spellOrder.push(g); }
        spellGroups[g].push(s);
      });

      let spellTables = '';
      spellOrder.forEach(gName => {
        const label = gName === 'Common Magic' ? 'Common Magic Spells' : gName;
        const rows = spellGroups[gName].map(s => {
          const pool = compactPool(resolveAttackPool(s.attack_dice || '')) || (s.attack_dice || '—');
          return `<tr>
            <td>${s.name || '—'}</td>
            <td>${pool || '—'}</td>
            <td>${s.equip || '—'}</td>
            <td>${s.range || '—'}</td>
            <td>${s.effect || ''}</td>
            <td>${s.descriptors || ''}</td>
          </tr>`;
        }).join('');
        spellTables += `
          <h5 class="summary-spell-group-head">${label}</h5>
          <table class="cg-battle-summary-table cg-spells-summary-table">
            <thead><tr><th>Name</th><th>Attack Pool</th><th>Equip</th><th>Range</th><th>Effect</th><th>Descriptors</th></tr></thead>
            <tbody>${rows}</tbody>
          </table>`;
      });

      spellsHtml = `
        <h4 class="summary-sub-heading">Spells</h4>
        ${spellTables}`;
    }

    // ── Armor block ────────────────────────────────────────────
    let armorHtml = '';
    if (armor.length) {
      armorHtml = `
        <h4 class="summary-sub-heading">Armor</h4>
        <table class="cg-battle-summary-table">
          <thead><tr><th>Name</th><th>Soak Dice</th><th>Penalty</th><th>Notes</th></tr></thead>
          <tbody>${armor.map(a => `<tr>
            <td>${a.name    || '—'}</td><td>${a.soak    || '—'}</td>
            <td>${a.penalty || '—'}</td><td>${a.notes   || ''}</td>
          </tr>`).join('')}</tbody>
        </table>`;
    }

    // ── Equipment & Trappings section ──────────────────────────
    let equipmentHtml = '';
    {
      let equipList = '';
      // Weapons (from synced battle array)
      weapons.forEach(w => {
        if (w.name) {
          const _wAtkRaw = w.attack || (w._attack_dice_raw ? resolveAttackPool(w._attack_dice_raw) : '');
          const wAtk = _wAtkRaw ? compactPool(_wAtkRaw) : _wAtkRaw;
          const details = [wAtk, w.damage, w.range !== 'Melee' ? w.range : ''].filter(Boolean).join(', ');
          equipList += `<li><strong>${w.name}</strong>${details ? ` — ${details}` : ''}${w.notes ? ` (${w.notes})` : ''}</li>`;
        }
      });
      // Armor (from synced battle array)
      armor.forEach(a => {
        if (a.name) {
          const details = [a.soak ? `Soak ${a.soak}` : '', a.penalty || ''].filter(Boolean).join(', ');
          equipList += `<li><strong>${a.name}</strong>${details ? ` — ${details}` : ''}${a.notes ? ` (${a.notes})` : ''}</li>`;
        }
      });
      // Other (non-weapon, non-armor) trappings from the live trappings list
      const gearItems = trappingsList.filter(t => t.kind !== 'weapon' && !t.armor_dice);
      gearItems.forEach(t => {
        if (t.name) {
          const qty = (t.qty && t.qty > 1) ? `${t.qty}× ` : '';
          equipList += `<li>${qty}<strong>${t.name}</strong>${t.token && t.token !== t.name ? ` <em>(${t.token})</em>` : ''}</li>`;
        }
      });
      if (equipList) {
        equipmentHtml = `
          <div class="summary-section summary-equipment">
            <h3>Equipment &amp; Trappings</h3>
            <ul>${equipList}</ul>
          </div>`;
      }
    }

    // ── Money section ───────────────────────────────────────────
    let moneyHtml = '';
    if (hasMoney) {
      let parts = [];
      if (currencyList.length) {
        currencyList.forEach(c => {
          const amt = parseFloat(moneyHoldings[c.slug] || 0);
          if (amt > 0) parts.push(`<span><strong>${c.name}:</strong> ${amt % 1 === 0 ? amt : amt.toFixed(3)}</span>`);
        });
      } else {
        // Fallback: show raw slug:value pairs if currency list not loaded
        Object.entries(moneyHoldings).forEach(([slug, amt]) => {
          if (parseFloat(amt) > 0) parts.push(`<span><strong>${slug}:</strong> ${amt}</span>`);
        });
      }
      if (parts.length) {
        moneyHtml = `
          <div class="summary-section summary-money">
            <h3>Money</h3>
            <div class="summary-money-row">${parts.join('')}</div>
          </div>`;
      }
    }

    // ── XP block ───────────────────────────────────────────────
    let xpHtml = '';
    if (xpGifts.length > 0 || xpEarned > 0 || xpMarksBudget > 0) {
      let xpGiftsListHtml = '';
      if (xpGifts.length > 0) {
        const fc = window.CG_FreeChoices;
        const allGifts = (fc && Array.isArray(fc._allGifts)) ? fc._allGifts : [];
        xpGiftsListHtml = `<ul>${xpGifts.map(gId => {
          const gObj = allGifts.find(g => String(g.ct_id || g.id || '') === String(gId));
          const name = gObj ? String(gObj.ct_gift_name || gObj.name || gId) : String(gId);
          const desc = gObj ? (String(gObj.effect ?? '').trim() || String(gObj.effect_description || gObj.ct_gifts_effect_description || '').trim()) : '';
          return `<li><strong>${name}</strong>${desc ? `<span class="summary-gift-desc"> — ${desc}</span>` : ''}</li>`;
        }).join('')}</ul>`;
      }
      xpHtml = `
        <div class="summary-section summary-xp">
          <h3>Experience Points</h3>
          <p><strong>Earned:</strong> ${xpEarned} &nbsp;|&nbsp;
             <strong>Spent:</strong> ${xpSpent} &nbsp;|&nbsp;
             <strong>Available:</strong> ${xpEarned - xpSpent}</p>
          ${xpGiftsListHtml}
        </div>`;
    }

    // ── Assemble two-page layout ───────────────────────────────
    const html = `

      <!-- ══ HEADER ══ -->
      <div class="summary-header-block">
        <h2>${name}</h2>
        <div class="summary-basic-row">
          <span><strong>Age:</strong> ${age}</span>
          <span><strong>Gender:</strong> ${gender}</span>
          ${playerName ? `<span><strong>Player:</strong> ${playerName}</span>` : ''}
        </div>
        ${motto !== '—' ? `<div class="summary-motto"><em>"${motto}"</em></div>` : ''}
      </div>

      <!-- ══ PAGE 1 — two columns ══ -->
      <div class="summary-page1-body">

        <!-- Left column -->
        <div class="summary-col-left">

          ${goalsList.length ? `
          <div class="summary-section summary-goals">
            <h3>Goals</h3>
            <ul>${goalsList.map(g => `<li>${g}</li>`).join('')}</ul>
          </div>` : ''}

          <div class="summary-section summary-species">
            <h3>Species: ${species.speciesName || '—'}</h3>
            ${(() => {
              const parts = [];
              if (species.habitat) parts.push(`<span><strong>Habitat:</strong> ${species.habitat}</span>`);
              if (species.diet)    parts.push(`<span><strong>Diet:</strong> ${species.diet}</span>`);
              if (species.cycle)   parts.push(`<span><strong>Cycle:</strong> ${species.cycle}</span>`);
              const senses = [species.sense_1, species.sense_2, species.sense_3].filter(Boolean);
              if (senses.length)   parts.push(`<span><strong>Senses:</strong> ${senses.join(', ')}</span>`);
              return parts.length ? `<div class="summary-species-traits">${parts.join(' &nbsp;|&nbsp; ')}</div>` : '';
            })()}
            ${speciesGiftsHtml ? `<ul>${speciesGiftsHtml}</ul>` : ''}
          </div>

          <div class="summary-section summary-career">
            <h3>Career: ${careerLabel}</h3>
            ${careerGiftsHtml ? `<ul>${careerGiftsHtml}</ul>` : ''}
          </div>

          ${(() => {
            const fc   = window.CG_FreeChoices;
            const allG = (fc && Array.isArray(fc._allGifts)) ? fc._allGifts : [];

            const freeIds = Array.isArray(data.free_gifts) ? data.free_gifts
              : [data.free_gift_1, data.free_gift_2, data.free_gift_3];

            const giftItems = [];

            // ── Always-acquired defaults ──────────────────────────
            const lkDesc = giftDesc('242');
            const lkRegion = String(data.local_knowledge_region || '').trim();
            giftItems.push(`<li><strong>Local Knowledge</strong>${lkRegion ? ` <em>(${lkRegion})</em>` : ''}${lkDesc ? `<span class="summary-gift-desc"> — ${lkDesc}</span>` : ''}</li>`);

            const quals = data.qualifications || data.quals || data.cg_quals || {};
            const rawLang = quals.language ?? data.language ?? '';
            const langList = Array.isArray(rawLang) ? rawLang : (rawLang ? [rawLang] : []);
            const langDisplay = langList.filter(Boolean).join(', ');
            const langDesc = giftDesc('236');
            giftItems.push(`<li><strong>Language</strong>${langDisplay ? ` <em>(${langDisplay})</em>` : ''}${langDesc ? `<span class="summary-gift-desc"> — ${langDesc}</span>` : ''}</li>`);

            const csDesc = giftDesc('159');
            giftItems.push(`<li><strong>Combat Save</strong>${csDesc ? `<span class="summary-gift-desc"> — ${csDesc}</span>` : ''}</li>`);

            const personality = String(data.personality_trait || '').trim();
            if (personality) {
              const pDesc = _personalityGiftId ? giftDesc(_personalityGiftId) : '';
              giftItems.push(`<li><strong>Personality:</strong> ${personality}${pDesc ? `<span class="summary-gift-desc"> — ${pDesc}</span>` : ''}</li>`);
            }

            // ── Free-choice gifts ─────────────────────────────────
            (freeIds || []).forEach(id => {
              const sid = String(id || '').trim();
              if (!sid) return;
              const name = giftName(sid);
              const desc = giftDesc(sid);
              if (name && name !== sid) {
                giftItems.push(`<li><strong>${name}</strong>${desc ? `<span class="summary-gift-desc"> — ${desc}</span>` : ''}</li>`);
              }
            });

            return giftItems.length ? `
          <div class="summary-section summary-gifts">
            <h3>Gifts</h3>
            <ul>${giftItems.join('')}</ul>
          </div>` : '';
          })()}

        </div><!-- /col-left -->

        <!-- Right column -->
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
                  <tr><td>Initiative</td><td>${initiative}</td></tr>
                  <tr><td>Dodge</td><td>${dodge}</td></tr>
                  <tr><td>Soak (Body + Armour)</td><td>${soak}</td></tr>
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
                  <tr><td>Sprint</td><td>${_speedDie || '—'}</td><td>(Speed die)</td></tr>
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
                  <div class="cg-healing-quota"><span class="cg-hq-label">Healing Quota</span><span class="cg-hq-circles">${_hqCircles}</span></div>
                  <div class="cg-hit-row"><span class="cg-hit-count">4 hits</span><span class="cg-hit-pip"></span><span class="cg-hit-name">Dying</span><span class="cg-hit-note">(get first aid!)</span></div>
                  <div class="cg-hit-row"><span class="cg-hit-count">5 hits</span><span class="cg-hit-pip"></span><span class="cg-hit-name">Dead</span><span class="cg-hit-note">(beyond mortal help)</span></div>
                  <div class="cg-hit-row"><span class="cg-hit-count">6 hits</span><span class="cg-hit-pip"></span><span class="cg-hit-name">Overkilled</span><span class="cg-hit-note">(allies become Afraid)</span></div>
                  <div class="cg-hit-row"><span class="cg-hit-count cg-hit-count--empty"></span><span class="cg-hit-pip"></span><span class="cg-hit-name">Sick</span><span class="cg-hit-note">(Reeling causes Knockdown)</span></div>
                  <div class="cg-healing-quota"><span class="cg-hq-label">Healing Quota</span><span class="cg-hq-circles">${_hqCircles}</span></div>
                </div>
                <div class="cg-status-conditions">
                  <div class="cg-status-row"><span class="cg-hit-pip"></span><span class="cg-status-name">Burdened</span><span class="cg-hit-note">(Dash is zero, limit of d8 to Speed Skills)</span></div>
                  <div class="cg-status-row"><span class="cg-hit-pip"></span><span class="cg-status-name">Over-Burdened</span><span class="cg-hit-note">(Burdened, can't run, disadvantaged)</span></div>
                  <div class="cg-status-row"><span class="cg-hit-pip"></span><span class="cg-status-name">Knockdown</span><span class="cg-hit-note">(disadvantaged, can't retreat)</span></div>
                  <div class="cg-status-row"><span class="cg-hit-pip"></span><span class="cg-status-name">Unconscious</span><span class="cg-hit-note">(helpless)</span></div>
                </div>
              </div>
            </div>

            ${weaponsHtml}
            ${armorHtml}
            ${spellsHtml}
          </div>

        </div><!-- /col-right -->

      </div><!-- /page1-body -->

      <!-- ══ PAGE 2 ══ -->
      <div class="summary-page2">

        <div class="summary-section summary-skills">
          <h3>Skills</h3>
          <div class="cg-summary-skills-wrap">
            <table class="cg-summary-skills">
              <thead><tr><th>Skill</th><th>Dice Pool</th></tr></thead>
              <tbody>${skillsHtml}</tbody>
            </table>
          </div>
        </div>

        ${equipmentHtml}

        ${moneyHtml}

        ${xpHtml}

        ${description ? `
        <div class="summary-section summary-description">
          <h3>Description</h3>
          <p>${description}</p>
        </div>` : ''}

        ${backstory ? `
        <div class="summary-section summary-backstory">
          <h3>Backstory</h3>
          <p>${backstory}</p>
        </div>` : ''}

      </div><!-- /page2 -->
    `;

    $sheet.html(html);
  },
  // CG HARDEN: live summary updates across tabs
  bindLiveUpdates() {
    // One-time binding
    if (this.__cg_live_bound) return;
    this.__cg_live_bound = true;
  
    const sel = '#cg-modal input, #cg-modal select, #cg-modal textarea';
    const self = this;
  
    function scheduleRender(e) {
      try {
        // Only if the summary sheet exists in DOM
        if (!document.getElementById('cg-summary-sheet')) return;
  
        const t = e && e.target;
        if (!t) return;
  
        // Ignore anything inside the summary sheet itself (avoid feedback loops)
        if (t.closest && t.closest('#cg-summary-sheet')) return;
  
        clearTimeout(self.__cg_live_timer);
        self.__cg_live_timer = setTimeout(() => {
          try {
            const data = (FormBuilderAPI && typeof FormBuilderAPI.getData === 'function')
              ? (FormBuilderAPI.getData() || {})
              : ((window.FormBuilderAPI && typeof window.FormBuilderAPI.getData === 'function')
                ? (window.FormBuilderAPI.getData() || {})
                : {});
            self.renderSummary(data);
          } catch (err) {
            console.warn('[SummaryAPI] live update failed', err);
          }
        }, 200);
      } catch (_) {}
    }
  
    // Delegated + namespaced (idempotent)
    $(document)
      .off('input.cgSummary change.cgSummary', sel)
      .on('input.cgSummary change.cgSummary', sel, scheduleRender);
  
    // First sync (in case user already typed before Summary tab was opened)
    try { this.renderSummary(FormBuilderAPI.getData() || {}); } catch (_) {}
  },


  /**
   * Open a new window, inject the summary + CSS, and print it.
   */

  // CG HARDEN: bindAutoRender (live summary updates)
  // - Debounced re-render so typing doesn't spam heavy DOM work.
  _scheduleRender(delayMs = 150) {
    try {
      if (this.__cg_render_timer) clearTimeout(this.__cg_render_timer);
      this.__cg_render_timer = setTimeout(() => {
        this.__cg_render_timer = null;
        try {
          const data = (typeof FormBuilderAPI !== 'undefined' && FormBuilderAPI.getData)
            ? (FormBuilderAPI.getData() || {})
            : (window.FormBuilderAPI?.getData?.() || {});
          this.renderSummary(data);
        } catch (_) {}
      }, delayMs);
    } catch (_) {}
  },

  bindAutoRender() {
    // Idempotent: bind only once per page-load
    if (this.__cg_autobound) return;
    this.__cg_autobound = true;

    // Remove old listeners if they exist (paranoia / hot reload)
    try {
      if (this.__cg_auto_handler) {
        document.removeEventListener('input',  this.__cg_auto_handler, true);
        document.removeEventListener('change', this.__cg_auto_handler, true);
      }
    } catch (_) {}

    this.__cg_auto_handler = (e) => {
      try {
        const t = e && e.target;
        if (!t) return;
        const modal = document.getElementById('cg-modal');
        if (!modal || !modal.contains(t)) return;

        // input = typing (debounce a bit), change = selects/radios (render sooner)
        const isChange = (e.type === 'change');
        this._scheduleRender(isChange ? 0 : 150);
      } catch (_) {}
    };

    document.addEventListener('input',  this.__cg_auto_handler, true);
    document.addEventListener('change', this.__cg_auto_handler, true);
  },

  // CG HARDEN: namespaced export click

  bindExportButton() {
    $(document)
      .off('click.cg', '#cg-export-pdf')
      .on('click.cg', '#cg-export-pdf', e => {
        e.preventDefault();
        console.log('[SummaryAPI] Export to PDF clicked');

        // 1) Grab the summary HTML
        const sheetHtml = document
          .getElementById('cg-summary-sheet')
          .outerHTML;

        // 2) Collect current <link rel="stylesheet"> tags
        const cssLinks = Array.from(
          document.querySelectorAll('link[rel="stylesheet"]')
        )
        .map(link => link.outerHTML)
        .join('\n');

        // 3) Open a print window and write clean HTML
        const printWin = window.open('', '_blank', 'width=900,height=700');
        printWin.document.open();
        printWin.document.write(`
          <!doctype html>
          <html>
            <head>
              <meta charset="utf-8">
              <title>Character Sheet</title>
              <link rel="preconnect" href="https://fonts.googleapis.com">
              <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
              <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap">
              ${cssLinks}
              <style>
                @page { size: A4; margin: 1.4cm 1.8cm; }
                body  { margin: 0; padding: 0; background: white; }
              </style>
            </head>
            <body class="cg-print-window">
              ${sheetHtml}
            </body>
          </html>
        `);
        printWin.document.close();
        printWin.focus();

        // 4) Wait for fonts to load, then print
        setTimeout(() => {
          printWin.print();
        }, 800);
      });
  }
};

export default SummaryAPI;
