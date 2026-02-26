// assets/js/src/core/career/extra.js
//
// Extra careers (Gift 184) + "Increased Trait: Career" targeting (Gift 223)
//
// Current behavior (Jan 2026):
// - Extra Career selection UI lives on the Profile tab
// - Extra Career trait dice display lives on the Traits tab
// - Gift 223 targeting UI appears INLINE next to each free-choice slot that is set to 223
//   (per-copy targeting: each free-choice copy gets its own target <select>)
// - Any non-free-choice copies of 223 (species/career gifts) default to Main Career

import FormBuilderAPI from '../formBuilder';
import SpeciesAPI from '../species/api.js';
import CareerAPI from './api.js';
import GiftsState from '../gifts/state.js';

const $ = window.jQuery;

const LOG  = (...a) => console.log('[ExtraCareers]', ...a);
const WARN = (...a) => console.warn('[ExtraCareers]', ...a);

const EXTRA_CAREER_GIFT_ID = '184';
const INC_TRAIT_CAREER_GIFT_ID = '223'; // "Increased Trait: Career"

// Legacy single-target key (migration only)
const BOOST_TARGET_KEY_LEGACY = 'increased_trait_career_target';

// Per-copy keys (per free-choice slot)
const BOOST_TARGET_KEY_PREFIX = 'increased_trait_career_target_'; // + slotIndex (0..2)

const ALWAYS_ACQUIRED_GIFT_IDS = ['242', '236']; // Local Knowledge, Language

const BOOST_TARGET_STYLE_ID  = 'cg-inc-trait-career-target-inline-style';

function boostKey(slot) { return `${BOOST_TARGET_KEY_PREFIX}${slot}`; }
function boostSelectId(slot) { return `cg-inc-trait-career-target-${slot}`; }

function ajaxEnv() {
  const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
  const ajax_url =
    env.ajax_url ||
    window.ajaxurl ||
    document.body?.dataset?.ajaxUrl ||
    '/wp-admin/admin-ajax.php';

  const perAction = (window.CG_NONCES && window.CG_NONCES.cg_get_career_gifts)
    ? window.CG_NONCES.cg_get_career_gifts
    : null;

  const generic = env.nonce || env.security || env._ajax_nonce || window.CG_NONCE || null;
  return { ajax_url, nonce: (perAction || generic) };
}

function parseJsonMaybe(res) {
  try { return (typeof res === 'string') ? JSON.parse(res) : res; }
  catch (_) { return res; }
}

function normalizeCareerProfile(raw = {}) {
  const out = (raw && typeof raw === 'object') ? { ...raw } : {};

  out.careerName =
    raw.careerName ||
    raw.career_name ||
    raw.ct_career_name ||
    raw.name ||
    raw.title ||
    '';

  ['gift_id_1','gift_id_2','gift_id_3','skill_one','skill_two','skill_three'].forEach(k => {
    if (out[k] != null) out[k] = String(out[k]);
  });

  out.skills = [out.skill_one, out.skill_two, out.skill_three]
    .map(v => (v ? String(v) : ''))
    .filter(Boolean);

  return out;
}

function stepDie(die, steps) {
  const order = ['d4', 'd6', 'd8', 'd10', 'd12'];
  const d = String(die || '').toLowerCase();
  const i = order.indexOf(d);
  if (i === -1) return die || '';
  const s = Math.max(0, parseInt(steps, 10) || 0);
  return order[Math.min(order.length - 1, i + s)];
}

function escapeHtml(s) {
  return String(s ?? '')
    .replaceAll('&','&amp;')
    .replaceAll('<','&lt;')
    .replaceAll('>','&gt;')
    .replaceAll('"','&quot;')
    .replaceAll("'", '&#39;');
}

const ExtraCareers = {
  _inited: false,
  _bound: false,

  _profileCache: new Map(),   // careerId -> normalized profile
  _profilePromise: new Map(), // careerId -> in-flight promise

  _eligibleCacheKey: '',
  _eligibleCache: [],

  init() {
    if (this._inited) return;
    this._inited = true;

    try { GiftsState.init?.(); } catch (_) {}

    this._bindOnce();

    // Native listeners must be idempotent across accidental double-enqueue / double-boot.
    try {
      window.__CG_EVT__ = window.__CG_EVT__ || {};
      const EVT = window.__CG_EVT__;

      if (EVT.extraCareersOnBuilderOpened) {
        document.removeEventListener('cg:builder:opened', EVT.extraCareersOnBuilderOpened);
      }
      if (EVT.extraCareersOnCharacterLoaded) {
        document.removeEventListener('cg:character:loaded', EVT.extraCareersOnCharacterLoaded);
      }
      if (EVT.extraCareersOnFreeGiftChanged) {
        document.removeEventListener('cg:free-gift:changed', EVT.extraCareersOnFreeGiftChanged);
      }

      EVT.extraCareersOnBuilderOpened    = () => ExtraCareers.render();
      EVT.extraCareersOnCharacterLoaded  = () => ExtraCareers.render();
      EVT.extraCareersOnFreeGiftChanged  = () => ExtraCareers.render();

      document.addEventListener('cg:builder:opened',   EVT.extraCareersOnBuilderOpened);
      document.addEventListener('cg:character:loaded', EVT.extraCareersOnCharacterLoaded);
      document.addEventListener('cg:free-gift:changed', EVT.extraCareersOnFreeGiftChanged);
    } catch (e) {
      try { WARN('idempotent native listener bind failed', e); } catch (_) {}
    }

    if ($) {
      $(document)
        .off('cg:species:changed.cgextra cg:career:changed.cgextra cg:free-gift:changed.cgextra')
        .on('cg:species:changed.cgextra cg:career:changed.cgextra cg:free-gift:changed.cgextra', () => this.render());
    }

    setTimeout(() => this.render(), 0);
  },

  _bindOnce() {
    if (this._bound || !$) return;
    this._bound = true;

    // Delegated change handler for any extra-career select
    $(document)
      .off('change.cgextra', '.cg-extra-career-select')
      .on('change.cgextra', '.cg-extra-career-select', async (e) => {
        const el = e.currentTarget;
        const idx = parseInt(el.getAttribute('data-index') || '0', 10);
        const careerId = String(el.value || '');

        await this._setSelection(idx, careerId);
        this.render();
      });

    // Delegated change handler for per-copy Gift-223 target selects
    $(document)
      .off('change.cgextra', '.cg-inc-trait-career-target-select')
      .on('change.cgextra', '.cg-inc-trait-career-target-select', (e) => {
        const el = e.currentTarget;
        const slot = parseInt(el.getAttribute('data-slot') || '-1', 10);
        const v = String(el.value || 'main') || 'main';
        if (slot >= 0) this._setBoostTargetForSlot(slot, v);
        this.render();
      });
  },

  _getWrap() {
    return document.querySelector('#cg-extra-careers') || null;
  },

  _getTraitsWrap() {
    return document.querySelector('#cg-extra-career-traits') || null;
  },

  _hideProfileDiceBadges() {
    try {
      const badge = document.getElementById('cg-profile-trait_career-badge');
      if (badge) badge.style.display = 'none';
    } catch (_) {}
  },

  _readExtraCareersFromData() {
    const d = FormBuilderAPI?._data || {};

    if (Array.isArray(d.extraCareers)) return d.extraCareers.map(x => ({ ...x }));

    if (typeof d.extra_careers === 'string' && d.extra_careers.trim()) {
      try {
        const arr = JSON.parse(d.extra_careers);
        if (Array.isArray(arr)) return arr.map(x => ({ ...x }));
      } catch (_) {}
    }

    const out = [];
    for (let i = 1; i <= 6; i++) {
      const key = `extra_career_${i}`;
      if (!Object.prototype.hasOwnProperty.call(d, key)) continue;
      const id = d[key] ? String(d[key]) : '';
      out.push({ id, name: '', skills: [] });
    }

    if (!out.length && Object.prototype.hasOwnProperty.call(d, 'extra_career_0')) {
      const id0 = d.extra_career_0 ? String(d.extra_career_0) : '';
      if (id0) out.push({ id: id0, name: '', skills: [] });
    }

    return out;
  },

  _writeExtraCareersToData(list) {
    const normalized = (Array.isArray(list) ? list : [])
      .filter(x => x && x.id)
      .map(x => ({
        id: String(x.id),
        name: String(x.name || ''),
        skills: Array.isArray(x.skills) ? x.skills.map(String).filter(Boolean) : []
      }));

    const __cgr2 = FormBuilderAPI._data?.career_gift_replacements;

    FormBuilderAPI._data = {
      ...(FormBuilderAPI._data || {}),
      extraCareers: normalized,
      extra_careers: JSON.stringify(normalized),
      career_gift_replacements: __cgr2 || {}
    };

    normalized.forEach((x, i) => {
      const k = i + 1;
      FormBuilderAPI._data[`extra_career_${k}`] = String(x.id);
    });
    for (let k = normalized.length + 1; k <= 6; k++) {
      delete FormBuilderAPI._data[`extra_career_${k}`];
    }
    delete FormBuilderAPI._data.extra_career_0;
  },

  _emitChanged() {
    const payload = { extraCareers: (FormBuilderAPI._data?.extraCareers || []) };
    document.dispatchEvent(new CustomEvent('cg:extra-careers:changed', { detail: payload }));
    if ($) $(document).trigger('cg:extra-careers:changed', [payload]);
  },

  _countGiftInAcquired(giftId) {
    const target = String(giftId);
    let count = 0;

    const dFB = FormBuilderAPI?._data || {};
    let free = (Array.isArray(GiftsState.selected) && GiftsState.selected.length)
      ? GiftsState.selected
      : null;

    if (!free) {
      if (Array.isArray(dFB.free_gifts) && dFB.free_gifts.length) free = dFB.free_gifts;
      else if (Array.isArray(dFB.freeGifts) && dFB.freeGifts.length) free = dFB.freeGifts;
      else {
        free = [];
        ['free-choice-0','free-choice-1','free-choice-2'].forEach(k => {
          const v = dFB[k];
          if (v != null && String(v).trim()) free.push(String(v));
        });
      }
    }

    free = Array.isArray(free) ? free : [];
    free.forEach(id => { if (String(id) === target) count++; });

    const sp = SpeciesAPI?.currentProfile || null;
    if (sp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
        if (sp[k] != null && String(sp[k]) === target) count++;
      });
    }

    const cp = CareerAPI?.currentProfile || null;
    if (cp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
        if (cp[k] != null && String(cp[k]) === target) count++;
      });
    }

    return count;
  },

  _mainCareerId() {
    const dom = document.querySelector('#cg-career');
    if (dom && dom.value) return String(dom.value);

    const d = FormBuilderAPI?._data || {};
    return String(d.career_id || d.career || '');
  },

  _mainCareerName() {
    const dom = document.querySelector('#cg-career');
    if (dom) {
      const opt = dom.options?.[dom.selectedIndex];
      const txt = opt ? opt.textContent : '';
      if (txt) return String(txt).trim();
    }
    return 'Main Career';
  },

  _careerTraitBaseDie() {
    const dom =
      document.querySelector('#cg-trait_career') ||
      document.querySelector('#cg-trait-career') ||
      document.querySelector('select[name="trait_career"]') ||
      document.querySelector('select[data-trait="career"]');

    const data = FormBuilderAPI?.getData ? FormBuilderAPI.getData() : (FormBuilderAPI?._data || {});

    const v =
      (dom && dom.value) ? String(dom.value) :
      (data && (data.trait_career || data.traitCareer)) ? String(data.trait_career || data.traitCareer) :
      '';

    return (v && v.trim()) ? v.trim() : 'd4';
  },

  _countExtraCareerUnlocks() {
    let count = 0;

    const free = Array.isArray(GiftsState.selected) ? GiftsState.selected : [];
    free.forEach(id => { if (String(id) === EXTRA_CAREER_GIFT_ID) count++; });

    const sp = SpeciesAPI?.currentProfile || null;
    if (sp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
        if (sp[k] != null && String(sp[k]) === EXTRA_CAREER_GIFT_ID) count++;
      });
    }

    const cp = CareerAPI?.currentProfile || null;
    if (cp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
        if (cp[k] != null && String(cp[k]) === EXTRA_CAREER_GIFT_ID) count++;
      });
    }

    return count;
  },

  _acquiredGiftSet() {
    const set = new Set();
    ALWAYS_ACQUIRED_GIFT_IDS.forEach(id => set.add(String(id)));

    const free = Array.isArray(GiftsState.selected) ? GiftsState.selected : [];
    free.forEach(id => { if (id) set.add(String(id)); });

    const sp = SpeciesAPI?.currentProfile || null;
    if (sp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
        if (sp[k] != null && String(sp[k])) set.add(String(sp[k]));
      });
    }

    const cp = CareerAPI?.currentProfile || null;
    if (cp) {
      ['gift_id_1','gift_id_2','gift_id_3'].forEach(k => {
        if (cp[k] != null && String(cp[k])) set.add(String(cp[k]));
      });
    }

    return set;
  },

  _getCareerProfile(careerId) {
    const id = String(careerId || '');
    if (!id) return Promise.resolve(null);

    if (this._profileCache.has(id)) return Promise.resolve(this._profileCache.get(id));
    if (this._profilePromise.has(id)) return this._profilePromise.get(id);

    const { ajax_url, nonce } = ajaxEnv();
    if (!ajax_url) return Promise.resolve(null);

    const payload = { action: 'cg_get_career_gifts', id };
    if (nonce) {
      payload.security = nonce;
      payload.nonce = nonce;
      payload._ajax_nonce = nonce;
    }

    const cleanup = () => { this._profilePromise.delete(id); };

    const req = ($ && $.post) ? $.post(ajax_url, payload) : null;

    const p = (req ? req : Promise.resolve(null)).then(
      (res) => {
        const json = parseJsonMaybe(res);
        const raw =
          (json && json.success === true) ? json.data :
          (json && json.success === undefined) ? json :
          null;

        if (!raw) return null;

        const prof = normalizeCareerProfile(raw);
        this._profileCache.set(id, prof);
        return prof;
      },
      () => null
    );

    if (req && typeof req.always === 'function') req.always(cleanup);
    else if (p && typeof p.finally === 'function') p.finally(cleanup);
    else setTimeout(cleanup, 0);

    this._profilePromise.set(id, p);
    return p;
  },

  async _computeEligibleCareers() {
    let mainCareerId = String(this._mainCareerId() || '').trim();
    let mainCareerIdNum = parseInt(mainCareerId, 10) || 0;

    if (mainCareerIdNum <= 0) {
      for (let i = 0; i < 24 && mainCareerIdNum <= 0; i++) {
        await new Promise(r => setTimeout(r, 25));
        mainCareerId = String(this._mainCareerId() || '').trim();
        mainCareerIdNum = parseInt(mainCareerId, 10) || 0;
      }
    }

    mainCareerId = (mainCareerIdNum > 0) ? String(mainCareerIdNum) : '';

    if (mainCareerIdNum > 0 && CareerAPI && typeof CareerAPI.fetchProfile === 'function') {
      try {
        const curId = String(CareerAPI.currentProfileId || '');
        const want = String(mainCareerIdNum);
        if (!CareerAPI.currentProfile || curId !== want) {
          await Promise.resolve(CareerAPI.fetchProfile(want));
          CareerAPI.currentProfileId = want;
        }
      } catch (_) {}
    }

    const acquired = this._acquiredGiftSet();
    const key = [mainCareerId, ...Array.from(acquired).sort()].join('|');

    if (key === this._eligibleCacheKey && Array.isArray(this._eligibleCache)) {
      return this._eligibleCache.slice();
    }

    let list = await CareerAPI.getList(false);
    let careers = Array.isArray(list) ? list : [];
    const listHasExt = careers.some(c => c && (
      c.gift_id_1 != null || c.gift_id_2 != null || c.gift_id_3 != null ||
      c.skill_one != null || c.skill_two != null || c.skill_three != null
    ));

    if (!listHasExt) {
      list = await CareerAPI.getList(true);
      careers = Array.isArray(list) ? list : [];
    }

    const results = [];
    for (const c of careers) {
      const id = String(c?.id || '');
      const name = String(c?.name || '');
      if (!id || !name) continue;

      if (mainCareerIdNum > 0 && String(mainCareerIdNum) === id) continue;

      const g1 = (c?.gift_id_1 != null) ? String(c.gift_id_1) : '';
      const g2 = (c?.gift_id_2 != null) ? String(c.gift_id_2) : '';
      const g3 = (c?.gift_id_3 != null) ? String(c.gift_id_3) : '';
      const listHasGiftIds = Boolean(g1 || g2 || g3);

      let required = [];
      let skills = [];

      if (listHasGiftIds) {
        required = [g1, g2, g3].filter(v => v && v !== '0');

        const s1 = (c?.skill_one != null) ? String(c.skill_one) : '';
        const s2 = (c?.skill_two != null) ? String(c.skill_two) : '';
        const s3 = (c?.skill_three != null) ? String(c.skill_three) : '';
        skills = [s1, s2, s3].filter(v => v && v !== '0');
      } else {
        const prof = await this._getCareerProfile(id);
        if (!prof) continue;

        required = [prof.gift_id_1, prof.gift_id_2, prof.gift_id_3]
          .map(v => (v ? String(v) : ''))
          .filter(Boolean);

        if (Array.isArray(prof.skills) && prof.skills.length) skills = prof.skills.slice();
        else {
          const s1 = (prof.skill_one != null) ? String(prof.skill_one) : '';
          const s2 = (prof.skill_two != null) ? String(prof.skill_two) : '';
          const s3 = (prof.skill_three != null) ? String(prof.skill_three) : '';
          skills = [s1, s2, s3].filter(v => v && v !== '0');
        }
      }

      const eligible = required.every(gid => acquired.has(String(gid)));
      if (!eligible) continue;

      results.push({ id, name, skills });
    }

    const eligibleSorted = results.sort((a, b) => String(a.name).localeCompare(String(b.name)));

    this._eligibleCacheKey = key;
    this._eligibleCache = eligibleSorted.slice();

    return eligibleSorted;
  },

  async _setSelection(slotIndex, careerId) {
    const idx = parseInt(slotIndex, 10) || 0;

    const unlocks = this._countExtraCareerUnlocks();
    const maxSlots = Math.max(0, unlocks);

    let list = this._readExtraCareersFromData();
    while (list.length < maxSlots) list.push({ id: '', name: '', skills: [] });
    list = list.slice(0, maxSlots);

    if (!careerId) {
      list[idx] = { id: '', name: '', skills: [] };
      list = list.filter(x => x && x.id);
      this._writeExtraCareersToData(list);
      this._emitChanged();
      return;
    }

    let name = '';
    try {
      const eligible = await this._computeEligibleCareers();
      const hit = eligible.find(x => String(x.id) === String(careerId));
      if (hit) name = hit.name || '';
    } catch (_) {}

    const prof = await this._getCareerProfile(careerId);
    const skills = Array.isArray(prof?.skills) ? prof.skills.slice() : [];

    list[idx] = { id: String(careerId), name: String(name || prof?.careerName || ''), skills };

    const seen = new Set();
    list = list.filter(x => {
      if (!x || !x.id) return false;
      const id = String(x.id);
      if (seen.has(id)) return false;
      seen.add(id);
      return true;
    });

    this._writeExtraCareersToData(list);
    this._emitChanged();
  },

  _eligibleSelectedIdSet(selectedCareers) {
    return new Set((selectedCareers || []).map(x => String(x?.id || '')).filter(Boolean));
  },

  _getBoostTargetForSlot(slot) {
    const d = FormBuilderAPI?._data || {};
    const v = d[boostKey(slot)];
    if (v != null && String(v).trim()) return String(v).trim();

    // Legacy migration fallback
    const legacy = d[BOOST_TARGET_KEY_LEGACY];
    if (legacy != null && String(legacy).trim()) return String(legacy).trim();

    return 'main';
  },

  _setBoostTargetForSlot(slot, targetValue) {
    const v = String(targetValue || 'main') || 'main';
    const __cgr = FormBuilderAPI._data?.career_gift_replacements;
    FormBuilderAPI._data = {
      ...(FormBuilderAPI._data || {}),
      [boostKey(slot)]: v,
      career_gift_replacements: __cgr || {}
    };

    // If legacy key exists, remove it to avoid future confusion
    try { delete FormBuilderAPI._data[BOOST_TARGET_KEY_LEGACY]; } catch (_) {}

    document.dispatchEvent(new CustomEvent('cg:traits:changed', { detail: { [boostKey(slot)]: v } }));
    if ($) $(document).trigger('cg:traits:changed', [{ [boostKey(slot)]: v }]);
  },

  _findFreeChoiceSelectsFor223() {
    const out = [];
    for (let i = 0; i <= 2; i++) {
      const el = document.getElementById(`cg-free-choice-${i}`);
      if (el && String(el.value || '') === INC_TRAIT_CAREER_GIFT_ID) out.push({ slot: i, el });
    }
    return out;
  },

  _ensureBoostTargetInlineStyles() {
    try {
      if (document.getElementById(BOOST_TARGET_STYLE_ID)) return;
      const st = document.createElement('style');
      st.id = BOOST_TARGET_STYLE_ID;
      st.textContent = `
        .cg-free-slot--boosttarget {
          display: flex;
          gap: 12px;
          align-items: center;
          flex-wrap: wrap;
        }
        .cg-free-slot--boosttarget .cg-inc-trait-career-target-label {
          font-weight: 600;
          white-space: nowrap;
          opacity: .85;
        }
        .cg-free-slot--boosttarget .cg-inc-trait-career-target-select {
          min-width: 220px;
          width: auto;
        }
        @media (max-width: 520px) {
          .cg-free-slot--boosttarget {
            flex-direction: column;
            align-items: stretch;
          }
          .cg-free-slot--boosttarget .cg-inc-trait-career-target-label {
            width: 100%;
          }
          .cg-free-slot--boosttarget .cg-inc-trait-career-target-select {
            width: 100%;
          }
        }
      `;
      document.head.appendChild(st);
    } catch (_) {}
  },

  _removeBoostTargetInlineUIForSlot(slot) {
    const sid = boostSelectId(slot);
    const sel = document.getElementById(sid);
    let wrap = null;

    if (sel) {
      try { wrap = sel.closest('.cg-free-slot') || sel.parentElement; } catch (_) { wrap = sel.parentElement; }
      try { sel.remove(); } catch (_) {}
    }

    if (wrap) {
      const lbl = wrap.querySelector(`.cg-inc-trait-career-target-label[data-slot="${slot}"]`);
      if (lbl) { try { lbl.remove(); } catch (_) {} }

      // If no more target selects inside, remove the wrapper marker class
      if (!wrap.querySelector('select[id^="cg-inc-trait-career-target-"]')) {
        try { wrap.classList.remove('cg-free-slot--boosttarget'); } catch (_) {}
      }
    }
  },

  _cleanupBoostTargetData(activeSlotsSet) {
    const d = FormBuilderAPI?._data || {};
    for (let i = 0; i <= 2; i++) {
      if (!activeSlotsSet.has(i)) {
        try { delete d[boostKey(i)]; } catch (_) {}
      }
    }
    // also drop legacy key once we're in per-slot world
    try { delete d[BOOST_TARGET_KEY_LEGACY]; } catch (_) {}
  },

  _ensureBoostTargetInlineUI(selectedCareers) {
    const boostsTotal = this._countGiftInAcquired(INC_TRAIT_CAREER_GIFT_ID);
    const selected = Array.isArray(selectedCareers) ? selectedCareers.filter(x => x && x.id) : [];
    const targets = this._findFreeChoiceSelectsFor223();

    const shouldShow = (boostsTotal > 0) && (selected.length > 0) && (targets.length > 0);

    // Always remove any stale slot UI that no longer has 223
    const activeSlots = new Set(targets.map(t => t.slot));
    for (let i = 0; i <= 2; i++) {
      if (!activeSlots.has(i)) this._removeBoostTargetInlineUIForSlot(i);
    }
    this._cleanupBoostTargetData(activeSlots);

    if (!shouldShow) return;

    this._ensureBoostTargetInlineStyles();

    const selectedIds = this._eligibleSelectedIdSet(selected);

    // Legacy migration: if legacy exists and per-slot keys are missing, seed them
    try {
      const d = FormBuilderAPI?._data || {};
      const legacy = (d[BOOST_TARGET_KEY_LEGACY] != null) ? String(d[BOOST_TARGET_KEY_LEGACY]).trim() : '';
      if (legacy) {
        targets.forEach(({ slot }) => {
          if (d[boostKey(slot)] == null || String(d[boostKey(slot)]).trim() === '') {
            d[boostKey(slot)] = legacy;
          }
        });
        delete d[BOOST_TARGET_KEY_LEGACY];
      }
    } catch (_) {}

    // Build options (Main + selected extra careers)
    const mainLabel = this._mainCareerName();
    const options = [{ value: 'main', label: `Main Career — ${mainLabel}` }]
      .concat(selected.map(x => ({ value: String(x.id), label: `Extra Career — ${String(x.name || 'Extra Career')}` })));

    // Ensure each active slot gets its own inline selector
    targets.forEach(({ slot, el }) => {
      let wrap = null;
      try { wrap = el.closest('.cg-free-slot'); } catch (_) { wrap = null; }
      if (!wrap) wrap = el.parentElement;
      if (!wrap) return;

      try { wrap.classList.add('cg-free-slot--boosttarget'); } catch (_) {}

      // Label
      let lbl = wrap.querySelector(`.cg-inc-trait-career-target-label[data-slot="${slot}"]`);
      if (!lbl) {
        lbl = document.createElement('span');
        lbl.className = 'cg-inc-trait-career-target-label';
        lbl.setAttribute('data-slot', String(slot));
        lbl.textContent = 'Applies to:';
        // Insert right after the Gift-223 free-choice select
        try { el.insertAdjacentElement('afterend', lbl); }
        catch (_) { wrap.appendChild(lbl); }
      }

      // Select
      const sid = boostSelectId(slot);
      let sel = document.getElementById(sid);
      if (!sel) {
        sel = document.createElement('select');
        sel.id = sid;
        sel.className = 'cg-profile-select cg-inc-trait-career-target-select';
        sel.setAttribute('data-slot', String(slot));
      }

      // Put it immediately after the label
      if (sel.parentElement !== wrap) {
        try { wrap.appendChild(sel); } catch (_) {}
      }
      try {
        if (lbl.nextSibling !== sel) lbl.insertAdjacentElement('afterend', sel);
      } catch (_) {}

      // Fill options
      sel.innerHTML = '';
      options.forEach(o => sel.appendChild(new Option(o.label, o.value)));

      // Validate saved value
      let v = this._getBoostTargetForSlot(slot);

      if (v !== 'main' && !selectedIds.has(String(v))) v = 'main';

      // Persist coerced value if needed
      const curSaved = this._getBoostTargetForSlot(slot);
      if (String(curSaved) !== String(v)) this._setBoostTargetForSlot(slot, v);

      sel.value = String(v);
    });
  },

  _computeCareerBoostCounts(selectedCareers) {
    const total = this._countGiftInAcquired(INC_TRAIT_CAREER_GIFT_ID);
    const counts = Object.create(null);
    counts.main = 0;

    const selected = Array.isArray(selectedCareers) ? selectedCareers.filter(x => x && x.id) : [];
    const selectedIds = this._eligibleSelectedIdSet(selected);

    // Assign one boost per free-choice slot that is set to 223 (per-copy targeting)
    const targets = this._findFreeChoiceSelectsFor223();

    let assigned = 0;

    for (const t of targets) {
      if (assigned >= total) break; // safety: if totals mismatch, don't go negative
      const slot = t.slot;

      let v = this._getBoostTargetForSlot(slot);
      if (v !== 'main' && !selectedIds.has(String(v))) v = 'main';

      counts[v] = (counts[v] || 0) + 1;
      assigned++;
    }

    // Remaining copies (e.g., from species/career gifts) default to main
    const remaining = Math.max(0, total - assigned);
    if (remaining) counts.main = (counts.main || 0) + remaining;

    return counts;
  },

  _careerTraitDisplayWithCounts(targetKey, boostCounts) {
    // Extra career traits always start from d4 — only the main career uses the
    // primary career trait die as its base.
    const base = (targetKey === 'main') ? this._careerTraitBaseDie() : 'd4';
    const count = Math.max(0, parseInt((boostCounts && boostCounts[targetKey]) || 0, 10) || 0);

    if (count <= 0) return { base, adjusted: base, boosts: 0, suffix: '' };

    const adjusted = stepDie(base, count);
    const suffix = count === 1 ? 'Increased by gift' : `Increased by gift ×${count}`;
    return { base, adjusted, boosts: count, suffix };
  },

  _renderTraitsTabExtraCareerDice(unlocks, selected, baseTrait, boostCounts) {
    const traitsWrap = this._getTraitsWrap();
    if (!traitsWrap) return;

    if (!unlocks) {
      traitsWrap.innerHTML = '';
      return;
    }

    const rows = [];

    for (let i = 0; i < unlocks; i++) {
      const curId = selected[i]?.id ? String(selected[i].id) : '';
      const name = curId
        ? String(selected[i].name || `Extra Career ${i + 1}`)
        : `Extra Career ${i + 1}`;

      const traitInfo = curId
        ? this._careerTraitDisplayWithCounts(String(curId), boostCounts)
        : { adjusted: 'd4', suffix: '' };

      const shownDie = traitInfo.adjusted || 'd4';
      const note = (curId && traitInfo.suffix) ? traitInfo.suffix : '';

      rows.push(`
        <div class="cg-extra-career-trait-row">
          <span class="cg-extra-career-trait-name">${escapeHtml(name)}</span>
          <span class="cg-trait-badge cg-trait-badge--sm" aria-label="Career trait die">${escapeHtml(shownDie)}</span>
        </div>
        ${note ? `<div class="trait-adjusted">${escapeHtml(note)}</div>` : `<div class="trait-adjusted"></div>`}
      `);
    }

    traitsWrap.innerHTML = `
      <h4>Extra Careers</h4>
      ${rows.join('')}
    `;
  },

  async render() {
    const wrap = this._getWrap();
    const traitsWrap = this._getTraitsWrap();

    if (!wrap && traitsWrap) {
      traitsWrap.innerHTML = '';
      // still clean per-slot UI if builder remount happens
      for (let i = 0; i <= 2; i++) this._removeBoostTargetInlineUIForSlot(i);
      return;
    }
    if (!wrap) return;

    const unlocks = this._countExtraCareerUnlocks();

    if (!unlocks) {
      wrap.innerHTML = '';
      if (traitsWrap) traitsWrap.innerHTML = '';
      this._writeExtraCareersToData([]);
      this._emitChanged();

      for (let i = 0; i <= 2; i++) this._removeBoostTargetInlineUIForSlot(i);
      return;
    }

    wrap.innerHTML = `<div class="cg-extra-careers-loading">Loading eligible extra careers…</div>`;

    let eligible = [];
    try {
      eligible = await this._computeEligibleCareers();
    } catch (e) {
      WARN('eligible compute failed', e);
      eligible = [];
    }

    let selected = this._readExtraCareersFromData();
    while (selected.length < unlocks) selected.push({ id: '', name: '', skills: [] });
    selected = selected.slice(0, unlocks);

    const eligibleIds = new Set(eligible.map(x => String(x.id)));
    let changed = false;

    selected = selected.map(x => {
      if (!x || !x.id) return { id: '', name: '', skills: [] };
      if (!eligibleIds.has(String(x.id))) { changed = true; return { id: '', name: '', skills: [] }; }
      return x;
    });

    if (changed) {
      this._writeExtraCareersToData(selected.filter(x => x.id));
      this._emitChanged();
    }

    const selectedWithId = selected.filter(x => x && x.id);
    const boostCounts = this._computeCareerBoostCounts(selectedWithId);

    const baseTrait = this._careerTraitBaseDie();

    // Populate the Traits tab extra-career dice display
    this._renderTraitsTabExtraCareerDice(unlocks, selected, baseTrait, boostCounts);

    const otherSelectedIds = (slot) => {
      const set = new Set();
      selected.forEach((x, i) => {
        if (i === slot) return;
        if (x && x.id) set.add(String(x.id));
      });
      return set;
    };

    const blockHtml = [];
    for (let i = 0; i < unlocks; i++) {
      const cur = selected[i]?.id ? String(selected[i].id) : '';
      const exclude = otherSelectedIds(i);

      const options = eligible
        .filter(c => !exclude.has(String(c.id)) || String(c.id) === cur)
        .map(c => {
          const sel = (String(c.id) === cur) ? ' selected' : '';
          return `<option value="${String(c.id)}"${sel}>${escapeHtml(String(c.name))}</option>`;
        })
        .join('');

      const traitInfo = cur
        ? this._careerTraitDisplayWithCounts(String(cur), boostCounts)
        : { suffix: '' };

      const note = (cur && traitInfo.suffix) ? traitInfo.suffix : '';

      blockHtml.push(`
        <div class="cg-extra-career-block">
          <label for="cg-extra-career-${i}">Extra Career ${i + 1}</label>

          <div class="cg-trait-control cg-trait-control--profile cg-trait-control--extra">
            <select
              id="cg-extra-career-${i}"
              class="cg-profile-select cg-extra-career-select"
              data-index="${i}"
            >
              <option value="">— Select Career —</option>
              ${options}
            </select>
          </div>

          <div class="trait-adjusted">${note ? escapeHtml(note) : ''}</div>
        </div>
      `);
    }

    wrap.innerHTML = `${blockHtml.join('')}`;

    // Inline per-copy selectors next to each free-choice slot that is set to Gift 223
    this._ensureBoostTargetInlineUI(selectedWithId);
    setTimeout(() => this._ensureBoostTargetInlineUI(selectedWithId), 0);

    this._hideProfileDiceBadges();

    const needsHydrate = selected.some(x => x && x.id && (!Array.isArray(x.skills) || !x.skills.length));
    if (needsHydrate) {
      const hydrated = [];
      for (let i = 0; i < selected.length; i++) {
        const x = selected[i];
        if (!x || !x.id) continue;
        const prof = await this._getCareerProfile(x.id);
        hydrated.push({
          id: String(x.id),
          name: String(x.name || prof?.careerName || ''),
          skills: Array.isArray(prof?.skills) ? prof.skills.slice() : []
        });
      }
      this._writeExtraCareersToData(hydrated);
      this._emitChanged();
    }

    LOG('rendered', unlocks, 'slot(s). baseTrait:', baseTrait, 'boostCounts:', boostCounts);
  }
};

window.CG_ExtraCareers = ExtraCareers;
export default ExtraCareers;
