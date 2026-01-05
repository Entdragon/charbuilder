(() => {
  var __defProp = Object.defineProperty;
  var __defProps = Object.defineProperties;
  var __getOwnPropDescs = Object.getOwnPropertyDescriptors;
  var __getOwnPropSymbols = Object.getOwnPropertySymbols;
  var __hasOwnProp = Object.prototype.hasOwnProperty;
  var __propIsEnum = Object.prototype.propertyIsEnumerable;
  var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
  var __spreadValues = (a, b) => {
    for (var prop in b || (b = {}))
      if (__hasOwnProp.call(b, prop))
        __defNormalProp(a, prop, b[prop]);
    if (__getOwnPropSymbols)
      for (var prop of __getOwnPropSymbols(b)) {
        if (__propIsEnum.call(b, prop))
          __defNormalProp(a, prop, b[prop]);
      }
    return a;
  };
  var __spreadProps = (a, b) => __defProps(a, __getOwnPropDescs(b));
  var __async = (__this, __arguments, generator) => {
    return new Promise((resolve, reject) => {
      var fulfilled = (value) => {
        try {
          step(generator.next(value));
        } catch (e) {
          reject(e);
        }
      };
      var rejected = (value) => {
        try {
          step(generator.throw(value));
        } catch (e) {
          reject(e);
        }
      };
      var step = (x) => x.done ? resolve(x.value) : Promise.resolve(x.value).then(fulfilled, rejected);
      step((generator = generator.apply(__this, __arguments)).next());
    });
  };

  // assets/js/src/core/species/api.js
  var log = (...a) => console.log("[SpeciesAPI]", ...a);
  var warn = (...a) => console.warn("[SpeciesAPI]", ...a);
  var $ = window.jQuery;
  function ajaxEnv() {
    var _a, _b;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    const ajax_url = env.ajax_url || window.ajaxurl || ((_b = (_a = document.body) == null ? void 0 : _a.dataset) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php";
    const nonce = env.nonce || env.security || window.CG_NONCE || null;
    return { ajax_url, nonce };
  }
  function normalizeList(raw) {
    if (!Array.isArray(raw))
      return [];
    return raw.map((it) => {
      var _a, _b, _c, _d;
      if (it && typeof it === "object") {
        return {
          id: String((_b = (_a = it.id) != null ? _a : it.value) != null ? _b : ""),
          name: String((_d = (_c = it.name) != null ? _c : it.title) != null ? _d : "")
        };
      }
      return { id: String(it), name: String(it) };
    }).filter((x) => x.id && x.name);
  }
  function preloadedList() {
    if (Array.isArray(window.CG_SPECIES_LIST) && window.CG_SPECIES_LIST.length) {
      return normalizeList(window.CG_SPECIES_LIST);
    }
    if (window.CG_DATA && Array.isArray(window.CG_DATA.species) && window.CG_DATA.species.length) {
      return normalizeList(window.CG_DATA.species);
    }
    if (Array.isArray(window.cgSpecies) && window.cgSpecies.length) {
      return normalizeList(window.cgSpecies);
    }
    return null;
  }
  function postJSON(url, data) {
    return $.post(url, data).then((res) => {
      try {
        return typeof res === "string" ? JSON.parse(res) : res;
      } catch (_) {
        return res;
      }
    });
  }
  var SpeciesAPI = {
    _cache: { list: null, listPromise: null },
    currentProfile: null,
    clearCache() {
      this._cache.list = null;
      this._cache.listPromise = null;
    },
    /**
     * Get the species list.
     * @param {boolean} force If true, bypasses globals/cache and hits AJAX.
     * @returns {jQuery.Promise<array>}
     */
    getList(force = false) {
      if (!force) {
        const pre = preloadedList();
        if (pre && pre.length) {
          this._cache.list = pre.slice();
          return $.Deferred().resolve(this._cache.list).promise();
        }
      }
      if (!force && this._cache.list) {
        return $.Deferred().resolve(this._cache.list).promise();
      }
      if (this._cache.listPromise) {
        return this._cache.listPromise;
      }
      const { ajax_url, nonce } = ajaxEnv();
      if (!ajax_url) {
        warn("No AJAX URL available; returning empty list.");
        return $.Deferred().resolve([]).promise();
      }
      log("Fetching species via AJAX\u2026");
      const payload = {
        action: "cg_get_species_list",
        security: nonce,
        // check_ajax_referer(...,'security')
        nonce,
        // some handlers read `nonce`
        _ajax_nonce: nonce
        // and some rely on WP’s default key
      };
      const p = postJSON(ajax_url, payload).then(
        (res) => {
          const listRaw = Array.isArray(res) ? res : (res == null ? void 0 : res.data) || [];
          const list = normalizeList(listRaw);
          this._cache.list = list;
          log("Species list fetched:", list.length);
          return list;
        },
        (xhr, status, err2) => {
          warn("AJAX species list failed:", status, err2, xhr == null ? void 0 : xhr.responseText);
          this._cache.list = [];
          return [];
        }
      ).always(() => {
        this._cache.listPromise = null;
      });
      this._cache.listPromise = p;
      return p;
    },
    /**
     * Populate a <select> with the species list.
     * Keeps prior selection if still present.
     * @param {HTMLElement|string} sel element or query selector
     * @param {{force?: boolean}} opts
     * @returns {jQuery.Promise<HTMLElement|null>}
     */
    populateSelect(sel, { force = false } = {}) {
      const el = sel instanceof Element ? sel : document.querySelector(sel);
      if (!el)
        return $.Deferred().resolve(null).promise();
      const prior = el.value || "";
      el.innerHTML = "";
      const ph = document.createElement("option");
      ph.value = "";
      ph.textContent = "\u2014 Select Species \u2014";
      el.appendChild(ph);
      return this.getList(force).then((list) => {
        (list || []).forEach(({ id, name }) => {
          const o = document.createElement("option");
          o.value = id;
          o.textContent = name;
          el.appendChild(o);
        });
        if (prior && (list || []).some((x) => String(x.id) === String(prior))) {
          el.value = String(prior);
        }
        return el;
      });
    },
    /**
     * Fetch the full profile for one species (gifts, skills, etc).
     * Caches to `currentProfile` and emits a DOM event.
     * @param {string|number} speciesId
     * @returns {jQuery.Promise<object|null>}
     */
    fetchProfile(speciesId) {
      if (!speciesId) {
        this.currentProfile = null;
        return $.Deferred().resolve(null).promise();
      }
      const { ajax_url, nonce } = ajaxEnv();
      if (!ajax_url) {
        warn("No AJAX URL available for fetchProfile.");
        return $.Deferred().resolve(null).promise();
      }
      log("Fetching species profile", speciesId);
      const payload = {
        action: "cg_get_species_profile",
        species_id: speciesId,
        // preferred key
        id: speciesId,
        // fallback
        security: nonce,
        nonce,
        _ajax_nonce: nonce
      };
      return postJSON(ajax_url, payload).then(
        (res) => {
          const profile = (res == null ? void 0 : res.data) || res || null;
          this.currentProfile = profile || null;
          document.dispatchEvent(new CustomEvent("cg:species:profile", {
            detail: { id: String(speciesId), profile: this.currentProfile }
          }));
          return this.currentProfile;
        },
        (xhr, status, err2) => {
          warn("AJAX species profile failed:", status, err2, xhr == null ? void 0 : xhr.responseText);
          this.currentProfile = null;
          return null;
        }
      );
    }
  };
  if (typeof window !== "undefined") {
    window.SpeciesAPI = SpeciesAPI;
  }
  var api_default = SpeciesAPI;

  // assets/js/src/core/career/api.js
  var log2 = (...a) => console.log("[CareerAPI]", ...a);
  var warn2 = (...a) => console.warn("[CareerAPI]", ...a);
  var $2 = window.jQuery;
  function ajaxEnv2() {
    var _a, _b;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    const ajax_url = env.ajax_url || window.ajaxurl || ((_b = (_a = document.body) == null ? void 0 : _a.dataset) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php";
    const nonce = env.nonce || env.security || window.CG_NONCE || null;
    return { ajax_url, nonce };
  }
  function normalizeList2(raw) {
    if (!Array.isArray(raw))
      return [];
    return raw.map((it) => {
      var _a, _b, _c, _d;
      if (it && typeof it === "object") {
        const out = {
          id: String((_b = (_a = it.id) != null ? _a : it.value) != null ? _b : ""),
          name: String((_d = (_c = it.name) != null ? _c : it.title) != null ? _d : "")
        };
        ["gift_id_1", "gift_id_2", "gift_id_3", "skill_one", "skill_two", "skill_three"].forEach((k) => {
          if (it[k] != null && String(it[k]) !== "")
            out[k] = String(it[k]);
        });
        return out;
      }
      return { id: String(it), name: String(it) };
    }).filter((x) => x.id && x.name);
  }
  function preloadedList2() {
    if (Array.isArray(window.CG_CAREERS_LIST) && window.CG_CAREERS_LIST.length) {
      return normalizeList2(window.CG_CAREERS_LIST);
    }
    if (Array.isArray(window.CG_CAREER_LIST) && window.CG_CAREER_LIST.length) {
      return normalizeList2(window.CG_CAREER_LIST);
    }
    if (window.CG_DATA && Array.isArray(window.CG_DATA.careers) && window.CG_DATA.careers.length) {
      return normalizeList2(window.CG_DATA.careers);
    }
    if (Array.isArray(window.cgCareers) && window.cgCareers.length) {
      return normalizeList2(window.cgCareers);
    }
    return null;
  }
  function postJSON2(url, data) {
    return $2.post(url, data).then((res) => {
      try {
        return typeof res === "string" ? JSON.parse(res) : res;
      } catch (_) {
        return res;
      }
    });
  }
  function normalizeCareerProfileShape(profile = {}) {
    const p = profile && typeof profile === "object" ? __spreadValues({}, profile) : {};
    if (!p.careerName && p.career_name)
      p.careerName = p.career_name;
    if (!p.career_name && p.careerName)
      p.career_name = p.careerName;
    if (!p.careerName && !p.career_name && p.name)
      p.careerName = p.name;
    ["skill_one", "skill_two", "skill_three"].forEach((k) => {
      if (p[k] != null)
        p[k] = String(p[k]);
    });
    ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
      if (p[k] != null)
        p[k] = String(p[k]);
    });
    ["manifold_1", "manifold_2", "manifold_3"].forEach((k) => {
      if (p[k] != null)
        p[k] = parseInt(p[k], 10) || 1;
    });
    return p;
  }
  function giftNamesFromProfile(profile = {}) {
    const names = [profile.gift_1, profile.gift_2, profile.gift_3].map((v) => v == null ? "" : String(v)).filter(Boolean);
    return names.map((name, i) => {
      const m = profile[`manifold_${i + 1}`];
      const mult = parseInt(m, 10) || 1;
      return mult > 1 ? `${name} \xD7 ${mult}` : name;
    });
  }
  var CareerAPI = {
    _cache: { list: null, listPromise: null },
    currentProfile: null,
    currentProfileId: null,
    // CG_CAREERAPI_PROFILE_RACE_GUARD
    clearCache() {
      this._cache.list = null;
      this._cache.listPromise = null;
    },
    /** getList(force=false) → Promise resolving to [{id,name}] */
    getList(force = false) {
      if (!force) {
        const pre = preloadedList2();
        if (pre && pre.length) {
          this._cache.list = pre.slice();
          return $2.Deferred().resolve(this._cache.list).promise();
        }
      }
      if (!force && this._cache.list) {
        return $2.Deferred().resolve(this._cache.list).promise();
      }
      if (this._cache.listPromise) {
        return this._cache.listPromise;
      }
      const { ajax_url, nonce } = ajaxEnv2();
      if (!ajax_url) {
        warn2("No AJAX URL available; returning empty list.");
        return $2.Deferred().resolve([]).promise();
      }
      log2("Fetching careers via AJAX\u2026");
      const payload = {
        action: "cg_get_career_list",
        security: nonce,
        nonce,
        _ajax_nonce: nonce
      };
      const p = postJSON2(ajax_url, payload).then((res) => {
        const listRaw = Array.isArray(res) ? res : (res == null ? void 0 : res.data) || [];
        const list = normalizeList2(listRaw);
        this._cache.list = list;
        log2("Career list fetched:", list.length);
        return list;
      }).fail((xhr, status, err2) => {
        warn2("AJAX career list failed:", status, err2, xhr == null ? void 0 : xhr.responseText);
        return [];
      }).always(() => {
        this._cache.listPromise = null;
      });
      this._cache.listPromise = p;
      return p;
    },
    /** populateSelect(sel) → Promise resolving to the populated select */
    populateSelect(sel, { force = false } = {}) {
      const el = sel instanceof Element ? sel : document.querySelector(sel);
      if (!el)
        return $2.Deferred().resolve(null).promise();
      const prior = el.value || "";
      el.innerHTML = "";
      const ph = document.createElement("option");
      ph.value = "";
      ph.textContent = "\u2014 Select Career \u2014";
      el.appendChild(ph);
      return this.getList(force).then((list) => {
        list.forEach(({ id, name }) => {
          const o = document.createElement("option");
          o.value = id;
          o.textContent = name;
          el.appendChild(o);
        });
        if (prior && list.some((x) => String(x.id) === String(prior))) {
          el.value = String(prior);
        }
        return el;
      });
    },
    /**
     * fetchProfile(careerId) → Promise resolving to normalized profile (or null)
     * IMPORTANT: Your PHP returns the full profile from `cg_get_career_gifts`,
     * so we call that here and treat the response as the profile.
     */
    fetchProfile(careerId) {
      const wantedId = String(careerId || "");
      if (!wantedId) {
        this.currentProfile = null;
        this.currentProfileId = null;
        return $2.Deferred().resolve(null).promise();
      }
      this.currentProfileId = wantedId;
      const { ajax_url, nonce } = ajaxEnv2();
      if (!ajax_url) {
        warn2("No AJAX URL available for fetchProfile.");
        return $2.Deferred().resolve(null).promise();
      }
      log2("Fetching career profile", careerId);
      const payload = {
        action: "cg_get_career_gifts",
        id: careerId,
        security: nonce,
        nonce,
        _ajax_nonce: nonce
      };
      return postJSON2(ajax_url, payload).then((res) => {
        const profRaw = res && res.success === true ? res.data : res && res.success === void 0 ? res : null;
        if (!profRaw) {
          warn2("Career profile response missing/invalid:", res);
          if (String(this.currentProfileId || "") === String(wantedId)) {
            this.currentProfile = null;
          }
          return null;
        }
        const normalized = normalizeCareerProfileShape(profRaw);
        this.currentProfile = normalized;
        return normalized;
      }).fail((xhr, status, err2) => {
        warn2("AJAX career profile failed:", status, err2, xhr == null ? void 0 : xhr.responseText);
        this.currentProfile = null;
        return null;
      });
    },
    /**
     * fetchGifts(careerId) → Promise<string[]>
     * For compatibility with callers that “just want gift names”
     */
    fetchGifts(careerId) {
      return this.fetchProfile(careerId).then((profile) => {
        if (!profile)
          return [];
        return giftNamesFromProfile(profile);
      });
    }
  };
  if (typeof window !== "undefined") {
    window.CareerAPI = CareerAPI;
  }
  var api_default2 = CareerAPI;

  // assets/js/src/core/gifts/state.js
  var $3 = window.jQuery;
  var State = {
    // currently selected free-gift IDs (strings; '' means none)
    selected: ["", "", ""],
    // master list of gift objects (must include id; may include ct_gifts_manifold, etc.)
    gifts: [],
    /**
     * Pull any previously saved free_gifts/freeGifts from the builder’s data.
     */
    init() {
      var _a;
      const src = formBuilder_default && typeof formBuilder_default === "object" && formBuilder_default._data ? formBuilder_default._data : typeof ((_a = formBuilder_default) == null ? void 0 : _a.getData) === "function" ? formBuilder_default.getData() : {};
      const arr = Array.isArray(src.free_gifts) ? src.free_gifts : Array.isArray(src.freeGifts) ? src.freeGifts : null;
      const normalized = (Array.isArray(arr) ? arr : []).slice(0, 3).map((v) => v ? String(v) : "");
      while (normalized.length < 3)
        normalized.push("");
      this.selected = normalized;
    },
    /**
     * Update one slot and persist back into FormBuilder’s live _data (when available).
     */
    set(index, id) {
      this.selected[index] = id ? String(id) : "";
      if (formBuilder_default && formBuilder_default._data) {
        formBuilder_default._data.free_gifts = this.selected.slice();
        formBuilder_default._data.freeGifts = this.selected.slice();
      }
    },
    /**
     * Replace selected list (normalized), and persist into builder _data if present.
     */
    setSelected(list = []) {
      const normalized = (Array.isArray(list) ? list : []).slice(0, 3).map((v) => v ? String(v) : "");
      while (normalized.length < 3)
        normalized.push("");
      this.selected = normalized;
      if (formBuilder_default && formBuilder_default._data) {
        formBuilder_default._data.free_gifts = this.selected.slice();
        formBuilder_default._data.freeGifts = this.selected.slice();
      }
    },
    /**
     * Merge incoming gift objects into our master list.
     */
    setList(giftList = []) {
      giftList.forEach((g) => {
        var _a, _b, _c, _d;
        if (!g)
          return;
        const idStr = String((_d = (_c = (_b = (_a = g.id) != null ? _a : g.ct_id) != null ? _b : g.gift_id) != null ? _c : g.ct_gift_id) != null ? _d : "");
        if (!idStr)
          return;
        const idx = this.gifts.findIndex((x) => String(x.id) === idStr);
        if (idx > -1) {
          this.gifts[idx] = __spreadProps(__spreadValues(__spreadValues({}, this.gifts[idx]), g), { id: idStr });
        } else {
          this.gifts.push(__spreadProps(__spreadValues({}, g), { id: idStr }));
        }
      });
    },
    /**
     * Find one gift object by its ID.
     */
    getGiftById(id) {
      return this.gifts.find((g) => String(g.id) === String(id));
    }
  };
  (function bindGiftStateResyncOnce() {
    if (window.__CG_GIFT_STATE_BOUND__)
      return;
    window.__CG_GIFT_STATE_BOUND__ = true;
    function isNewCharacter(evt) {
      try {
        if (evt && evt.detail && typeof evt.detail.isNew !== "undefined")
          return !!evt.detail.isNew;
      } catch (_) {
      }
      try {
        const d = formBuilder_default && formBuilder_default._data ? formBuilder_default._data : {};
        return !!d.isNew;
      } catch (_) {
      }
      return false;
    }
    function wipeBoostTargetKeys() {
      try {
        const d = formBuilder_default && formBuilder_default._data ? formBuilder_default._data : null;
        if (!d)
          return;
        delete d.increased_trait_career_target;
        delete d.increased_trait_career_target_0;
        delete d.increased_trait_career_target_1;
        delete d.increased_trait_career_target_2;
      } catch (_) {
      }
    }
    function clearCachedProfiles() {
      try {
        if (api_default) {
          api_default.currentProfile = null;
          api_default.currentProfileId = "";
          api_default.currentId = "";
        }
      } catch (_) {
      }
      try {
        if (api_default2) {
          api_default2.currentProfile = null;
          api_default2.currentProfileId = "";
          api_default2.currentId = "";
        }
      } catch (_) {
      }
    }
    function emitRefresh(reason) {
      try {
        const detail = { reason: String(reason || ""), free_gifts: State.selected.slice() };
        document.dispatchEvent(new CustomEvent("cg:free-gift:changed", { detail }));
        document.dispatchEvent(new CustomEvent("cg:traits:changed", { detail }));
        document.dispatchEvent(new CustomEvent("cg:species:changed", { detail: { id: "" } }));
        document.dispatchEvent(new CustomEvent("cg:career:changed", { detail: { id: "" } }));
        if ($3) {
          $3(document).trigger("cg:free-gift:changed", [detail]);
          $3(document).trigger("cg:traits:changed", [detail]);
          $3(document).trigger("cg:species:changed", [{ id: "" }]);
          $3(document).trigger("cg:career:changed", [{ id: "" }]);
        }
      } catch (_) {
      }
    }
    function clearSelectIfPresent(selector) {
      try {
        const el = document.querySelector(selector);
        if (!el)
          return false;
        if (String(el.value || "") !== "") {
          el.value = "";
        }
        el.dispatchEvent(new Event("change", { bubbles: true }));
        return true;
      } catch (_) {
      }
      return false;
    }
    function resync(evt) {
      try {
        State.init();
      } catch (_) {
      }
      if (isNewCharacter(evt)) {
        try {
          State.setSelected(["", "", ""]);
        } catch (_) {
        }
        wipeBoostTargetKeys();
        clearCachedProfiles();
        setTimeout(() => {
          clearSelectIfPresent("#cg-species");
          clearSelectIfPresent("#cg-career");
          emitRefresh("new-character-reset");
        }, 0);
        return;
      }
      emitRefresh("resync");
    }
    document.addEventListener("cg:builder:opened", resync);
    document.addEventListener("cg:character:loaded", resync);
  })();
  window.CG_FreeChoicesState = State;
  var state_default = State;

  // assets/js/src/core/traits/service.js
  var DIE_ORDER = ["d4", "d6", "d8", "d10", "d12"];
  var BOOSTS = {
    78: "will",
    89: "speed",
    85: "body",
    100: "mind",
    224: "trait_species",
    223: "trait_career"
  };
  var TRAITS = ["will", "speed", "body", "mind", "trait_species", "trait_career"];
  var DICE_TYPES = ["d8", "d6", "d4"];
  var MAX_COUNT = { d8: 2, d6: 3, d4: 1 };
  function boostedDie(baseDie, steps) {
    const base = String(baseDie || "").toLowerCase().trim();
    const idx = DIE_ORDER.indexOf(base);
    if (idx === -1)
      return baseDie || "";
    const s = Math.max(0, parseInt(steps, 10) || 0);
    return DIE_ORDER[Math.min(idx + s, DIE_ORDER.length - 1)];
  }
  function readFormBuilderData() {
    const fb = window.CG_FormBuilderAPI || window.FormBuilderAPI || null;
    return fb && fb._data && typeof fb._data === "object" ? fb._data : {};
  }
  function readSelectedExtraCareerIds() {
    const d = readFormBuilderData();
    const ids = /* @__PURE__ */ new Set();
    const arr = Array.isArray(d.extraCareers) ? d.extraCareers : [];
    arr.forEach((x) => {
      const id = x && x.id != null ? String(x.id) : "";
      if (id)
        ids.add(id);
    });
    return ids;
  }
  function computeCareerBoostCounts(totalBoosts) {
    const total = Math.max(0, parseInt(totalBoosts || 0, 10) || 0);
    const counts = /* @__PURE__ */ Object.create(null);
    counts.main = 0;
    if (total <= 0)
      return counts;
    const d = readFormBuilderData();
    const extraIds = readSelectedExtraCareerIds();
    let assigned = 0;
    for (let slot = 0; slot <= 2; slot++) {
      const sel = document.getElementById(`cg-free-choice-${slot}`);
      if (!sel)
        continue;
      if (String(sel.value || "") !== "223")
        continue;
      if (assigned >= total)
        break;
      const key = `increased_trait_career_target_${slot}`;
      let v = d[key] != null ? String(d[key]).trim() : "";
      if (!v && d.increased_trait_career_target != null)
        v = String(d.increased_trait_career_target).trim();
      if (!v)
        v = "main";
      if (v !== "main" && !extraIds.has(String(v)))
        v = "main";
      counts[v] = (counts[v] || 0) + 1;
      assigned++;
    }
    const remaining = Math.max(0, total - assigned);
    if (remaining)
      counts.main = (counts.main || 0) + remaining;
    return counts;
  }
  var TraitsService = {
    TRAITS,
    DICE_TYPES,
    /**
     * Build a map: { traitKey: totalBoostCount }
     * counting free, species, and career gifts, including any manifold multipliers.
     */
    calculateBoostMap() {
      const map = /* @__PURE__ */ Object.create(null);
      function addGift(giftId) {
        if (giftId == null)
          return;
        const id = String(giftId).trim();
        if (!id || id === "0")
          return;
        const gift = state_default.getGiftById(id);
        if (!gift)
          return;
        const traitKey = BOOSTS[gift.id];
        if (!traitKey)
          return;
        const count = parseInt(gift.ct_gifts_manifold, 10) || 1;
        map[traitKey] = (map[traitKey] || 0) + count;
      }
      (Array.isArray(state_default.selected) ? state_default.selected : []).forEach(addGift);
      const sp = api_default && api_default.currentProfile ? api_default.currentProfile : null;
      if (sp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => addGift(sp[k]));
      }
      const cp = api_default2 && api_default2.currentProfile ? api_default2.currentProfile : null;
      if (cp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => addGift(cp[k]));
        if (cp.career_gift_replacements && typeof cp.career_gift_replacements === "object") {
          Object.values(cp.career_gift_replacements).forEach((v) => addGift(v));
        }
      }
      try {
        if (window && window.CG_DEBUG && window.CG_DEBUG.traits_boost_map) {
          console.log("[Traits] boost map \u2192", map);
        }
      } catch (_) {
      }
      return map;
    },
    enforceCounts() {
      const $20 = window.jQuery;
      const freq = { d8: 0, d6: 0, d4: 0 };
      $20(".cg-trait-select").each(function() {
        const v = $20(this).val();
        if (v && v in freq)
          freq[v]++;
      });
      $20(".cg-trait-select").each(function() {
        const $sel = $20(this);
        const current = $sel.val() || "";
        let options = '<option value="">\u2014 Select \u2014</option>';
        DICE_TYPES.forEach((die) => {
          if (freq[die] < MAX_COUNT[die] || current === die) {
            const sel = current === die ? " selected" : "";
            options += `<option value="${die}"${sel}>${die}</option>`;
          }
        });
        $sel.html(options);
      });
    },
    updateAdjustedDisplays() {
      const $20 = window.jQuery;
      const boosts = this.calculateBoostMap();
      const totalCareerBoosts = boosts.trait_career || 0;
      const careerCounts = computeCareerBoostCounts(totalCareerBoosts);
      const careerMainBoosts = careerCounts.main || 0;
      TRAITS.forEach((traitKey) => {
        const $sel = $20(`#cg-${traitKey}`);
        if (!$sel.length)
          return;
        const rawBase = String($sel.val() || "").trim();
        let count = boosts[traitKey] || 0;
        if (traitKey === "trait_career") {
          count = careerMainBoosts;
        }
        let badgeText = "\u2013";
        if (rawBase) {
          badgeText = count > 0 ? boostedDie(rawBase, count) : rawBase;
        }
        const $badge = $20(`#cg-${traitKey}-badge`);
        if ($badge.length)
          $badge.text(badgeText);
        if (traitKey === "trait_career") {
          const $pb = $20("#cg-profile-trait_career-badge");
          if ($pb.length)
            $pb.text(badgeText);
        }
        let note = "";
        if (traitKey === "trait_career") {
          if (careerMainBoosts > 0) {
            note = careerMainBoosts === 1 ? "Increased by gift" : `Increased by gift \xD7${careerMainBoosts}`;
          }
        } else {
          const origBoosts = boosts[traitKey] || 0;
          if (origBoosts > 0) {
            note = origBoosts === 1 ? "Increased by gift" : `Increased by gift \xD7${origBoosts}`;
          }
        }
        const $note = $20(`#cg-${traitKey}-adjusted`);
        if ($note.length)
          $note.text(note);
        if (traitKey === "trait_career") {
          const $pn = $20("#cg-profile-trait_career-note");
          if ($pn.length)
            $pn.text(note);
        }
      });
    },
    refreshAll() {
      this.enforceCounts();
      this.updateAdjustedDisplays();
    },
    getBoostedDie(traitKey) {
      const boosts = this.calculateBoostMap();
      let cnt = boosts[traitKey] || 0;
      if (traitKey === "trait_career") {
        const total = boosts.trait_career || 0;
        const counts = computeCareerBoostCounts(total);
        cnt = counts.main || 0;
      }
      if (!cnt)
        return "";
      const base = window.jQuery(`#cg-${traitKey}`).val() || "d4";
      return boostedDie(base, cnt);
    }
  };
  var service_default = TraitsService;

  // assets/js/src/core/formBuilder/render-details.js
  var TRAITS2 = service_default.TRAITS;
  var DICE = service_default.DICE_TYPES;
  function escape(val) {
    const s = val == null ? "" : String(val);
    return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }
  function capitalize(str = "") {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }
  var render_details_default = {
    renderTabs() {
      return `
      <ul class="cg-tabs">
        <li data-tab="tab-details" class="active">Details</li>
        <li data-tab="tab-traits">Traits, Species, Careers</li>
        <li data-tab="tab-gifts">Gifts</li>
        <li data-tab="tab-skills">Skills</li>
        <li data-tab="tab-trappings">Trappings &amp; Equipment</li>
        <li data-tab="tab-description">Description</li>
        <li data-tab="tab-summary">Character Sheet</li>
      </ul>
    `;
    },
    renderContent(data = {}) {
      const speciesSelected = data && (data.species_id || data.species || data.profile && data.profile.species) || "";
      const careerSelected = data && (data.career_id || data.career || data.profile && data.profile.career) || "";
      const traitFields = TRAITS2.map((trait) => {
        var _a;
        const val = String((_a = data[trait]) != null ? _a : "");
        const label = trait === "trait_species" ? "Species" : trait === "trait_career" ? "Career" : capitalize(trait);
        const options = DICE.map((die) => {
          const sel = die === val ? " selected" : "";
          return `<option value="${die}"${sel}>${die}</option>`;
        }).join("");
        return `
        <div class="cg-trait">
          <label for="cg-${trait}">${label} <small>(choose one)</small></label>

          <div class="cg-trait-control">
            <select id="cg-${trait}" class="cg-trait-select" data-trait="${trait}">
              <option value="">\u2014 Select \u2014</option>
              ${options}
            </select>

            <span
              class="cg-trait-badge"
              id="cg-${trait}-badge"
              aria-label="${escape(label)} die"
              aria-live="polite"
            >\u2013</span>
          </div>

          <div class="trait-adjusted" id="cg-${trait}-adjusted"></div>
        </div>
      `;
      }).join("");
      return `
      <div id="tab-details" class="tab-panel active">
        <div class="cg-details-panel">

          <div class="cg-details-box">
            <h3>Details</h3>
            <div class="cg-profile-grid">
              <div>
                <label>Character Name</label>
                <input type="text" id="cg-name" value="${escape(data.name)}" required />
              </div>
              <div>
                <label>Player Name</label>
                <input type="text" id="cg-player-name" value="${escape(data.player_name)}" />
              </div>

              <div>
                <label>Gender</label>
                <select id="cg-gender">
                  <option value="">&mdash; Select &mdash;</option>
                  <option value="Male"      ${data.gender === "Male" ? "selected" : ""}>Male</option>
                  <option value="Female"    ${data.gender === "Female" ? "selected" : ""}>Female</option>
                  <option value="Nonbinary" ${data.gender === "Nonbinary" ? "selected" : ""}>Nonbinary</option>
                </select>
              </div>
              <div>
                <label>Age</label>
                <input type="text" id="cg-age" value="${escape(data.age)}" required />
              </div>
            </div>

            <label>Motto</label>
            <input type="text" id="cg-motto" value="${escape(data.motto)}" />

            <label>Goal 1</label>
            <input type="text" id="cg-goal1" value="${escape(data.goal1)}" />
            <label>Goal 2</label>
            <input type="text" id="cg-goal2" value="${escape(data.goal2)}" />
            <label>Goal 3</label>
            <input type="text" id="cg-goal3" value="${escape(data.goal3)}" />
          </div>

        </div>
      </div>

      <div id="tab-traits" class="tab-panel">
        <div class="cg-details-panel">

          <div class="cg-profile-box">
            <h3>Species and Career</h3>

            <label for="cg-species">Species</label>
            <select
              id="cg-species"
              class="cg-profile-select"
              data-selected="${escape(speciesSelected)}"
            ></select>

            <ul id="species-gifts" class="cg-gift-item"></ul>

            <label for="cg-career">Career</label>
            <div class="cg-trait-control cg-trait-control--profile">
              <select
                id="cg-career"
                class="cg-profile-select"
                data-selected="${escape(careerSelected)}"
              ></select>

              <span
                class="cg-trait-badge"
                id="cg-profile-trait_career-badge"
                aria-label="Career trait die"
                aria-live="polite"
              >\u2013</span>
            </div>

            <div class="trait-adjusted" id="cg-profile-trait_career-note"></div>
            <div id="cg-extra-careers" class="cg-profile-grid"></div>
          </div>

          <div class="cg-traits-box">
            <h3>Traits</h3>
            <div class="cg-profile-grid">
              ${traitFields}
            </div>

            <!-- Read-only display of extra-career trait dice (populated by career/extra.js) -->
            <div id="cg-extra-career-traits" class="cg-extra-career-traits-box"></div>
          </div>

        </div>
      </div>

      <div id="tab-description" class="tab-panel">
        <div class="cg-details-panel">
          <div class="cg-text-box">
            <h3>Description &amp; Backstory</h3>
            <label>Description</label>
            <textarea id="cg-description">${escape(data.description)}</textarea>
            <label>Backstory</label>
            <textarea id="cg-backstory">${escape(data.backstory)}</textarea>
          </div>
        </div>
      </div>
    `;
    }
  };

  // assets/js/src/core/formBuilder/render-profile.js
  var render_profile_default = {
    renderContent(_data = {}) {
      return `
      <div id="tab-gifts" class="tab-panel">

        <div class="cg-profile-box">
          <h3>Gifts</h3>

          <div class="cg-gift-label">Local Knowledge</div>
          <div id="cg-local-knowledge" class="cg-gift-item"></div>

          <div class="cg-gift-label">Language</div>
          <div id="cg-language" class="cg-gift-item"></div>

          <div class="cg-gift-label">Species Gifts</div>
          <ul id="species-gift-block" class="cg-gift-item"></ul>

          <div class="cg-gift-label">Career Gifts</div>
          <ul id="career-gifts" class="cg-gift-item"></ul>

          <div class="cg-gift-label">Chosen</div>
          <div id="cg-free-choices" class="cg-gift-item"></div>
        </div>

      </div>

      <div id="tab-trappings" class="tab-panel">
        <div class="cg-profile-box">
          <h3>Trappings &amp; Equipment</h3>
          <p><em>Coming soon.</em></p>
        </div>
      </div>
    `;
    }
  };

  // assets/js/src/core/formBuilder/render-skills.js
  var render_skills_default = {
    renderContent(_data = {}) {
      return `
      <div id="tab-skills" class="tab-panel">
        <table id="skills-table" class="cg-skills-table">
          <thead></thead>
          <tbody></tbody>
        </table>
      </div>
    `;
    }
  };

  // assets/js/src/core/formBuilder/render-summary.js
  var render_summary_default = {
    renderContent(data = {}) {
      return `
      <div id="tab-summary" class="tab-panel">
        <div class="summary-header">
          <button id="cg-export-pdf" type="button" class="button primary">
            \u{1F5A8}\uFE0F Export to PDF
          </button>
        </div>
        <div id="cg-summary-sheet"></div>
      </div>
    `;
    }
  };

  // assets/js/src/core/formBuilder/form-builder.js
  var $4 = window.jQuery;
  var FormBuilder = {
    buildForm(data = {}) {
      console.log("\u{1F6E0}\uFE0F FormBuilder.buildForm fired:", data);
      const html = `
<form id="cg-form">

  ${render_details_default.renderTabs()}
  <div class="cg-tab-wrap">
    ${render_details_default.renderContent(data)}
    ${render_profile_default.renderContent(data)}
    ${render_skills_default.renderContent(data)}
    ${render_summary_default.renderContent(data)}
  </div>

  <input type="hidden" id="cg-id"     value="${data.id || ""}" />
  <div class="cg-form-buttons">
    <button type="button" class="cg-save-button">\u{1F4BE} Save</button>
    <button type="button" class="cg-save-button cg-close-after-save">\u{1F4BE} Save & Close</button>
  </div>
</form>`;
      setTimeout(() => {
        console.log("\u{1F53D} [buildForm] SELECT VALUES AFTER RENDER:", {
          will: $4("#cg-will").val(),
          speed: $4("#cg-speed").val(),
          body: $4("#cg-body").val(),
          mind: $4("#cg-mind").val(),
          trait_species: $4("#cg-trait_species").val(),
          trait_career: $4("#cg-trait_career").val(),
          profileSpecies: $4("#cg-species").val(),
          profileCareer: $4("#cg-career").val()
        });
      }, 0);
      return html;
    }
  };
  var form_builder_default = FormBuilder;

  // assets/js/src/core/formBuilder/index.js
  var $5 = window.jQuery;
  function asString(v) {
    return v == null ? "" : String(v);
  }
  function firstNonEmpty(...vals) {
    for (const v of vals) {
      if (v == null)
        continue;
      const s = String(v);
      if (s !== "")
        return s;
    }
    return "";
  }
  function normalizeFreeGifts(arr) {
    const out = Array.isArray(arr) ? arr.slice(0, 3).map(asString) : [];
    while (out.length < 3)
      out.push("");
    return out;
  }
  function normalizeSkillMarks(src) {
    var _a, _b, _c, _d, _e, _f, _g, _h, _i;
    if (!src)
      return {};
    if (typeof src === "string") {
      try {
        src = JSON.parse(src);
      } catch (_) {
        return {};
      }
    }
    const out = {};
    if (Array.isArray(src)) {
      for (const x of src) {
        if (x == null)
          continue;
        if (Array.isArray(x) && x.length >= 2) {
          const k = x[0];
          const v = x[1];
          if (k != null)
            out[String(k)] = parseInt(v, 10) || 0;
          continue;
        }
        if (typeof x === "object") {
          const k = (_d = (_c = (_b = (_a = x.skillId) != null ? _a : x.skill_id) != null ? _b : x.id) != null ? _c : x.key) != null ? _d : null;
          const v = (_i = (_h = (_g = (_f = (_e = x.mark) != null ? _e : x.value) != null ? _f : x.val) != null ? _g : x.marks) != null ? _h : x.count) != null ? _i : null;
          if (k != null && v != null)
            out[String(k)] = parseInt(v, 10) || 0;
        }
      }
      return out;
    }
    if (typeof src === "object") {
      for (const k of Object.keys(src)) {
        out[String(k)] = parseInt(src[k], 10) || 0;
      }
      return out;
    }
    return {};
  }
  function normalizeGiftReplacements(src) {
    if (!src)
      return {};
    if (typeof src === "string") {
      try {
        src = JSON.parse(src);
      } catch (_) {
        return {};
      }
    }
    if (typeof src !== "object" || Array.isArray(src))
      return {};
    const out = {};
    for (const k of Object.keys(src)) {
      const slot = String(k).trim();
      const val = String(src[k] || "").trim();
      if (["1", "2", "3"].includes(slot) && val && val !== "0") {
        out[slot] = val;
      }
    }
    return out;
  }
  function ajaxEnv3() {
    var _a, _b;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    return {
      url: env.ajax_url || window.ajaxurl || ((_b = (_a = document.body) == null ? void 0 : _a.dataset) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php",
      nonce: env.nonce || env.security || window.CG_NONCE || null
    };
  }
  function normalizeCore(raw = {}) {
    var _a, _b, _c, _d, _e, _f, _g, _h, _i, _j, _k, _l, _m, _n, _o, _p, _q, _r;
    const species = firstNonEmpty(
      raw.species_id,
      raw.species,
      (_a = raw.profile) == null ? void 0 : _a.species,
      raw.profileSpecies
    );
    const career = firstNonEmpty(
      raw.career_id,
      raw.career,
      (_b = raw.profile) == null ? void 0 : _b.career,
      raw.profileCareer
    );
    const freeArr = normalizeFreeGifts(
      Array.isArray(raw.free_gifts) ? raw.free_gifts : Array.isArray(raw.freeGifts) ? raw.freeGifts : raw.free_gift_1 != null || raw.free_gift_2 != null || raw.free_gift_3 != null ? [raw.free_gift_1, raw.free_gift_2, raw.free_gift_3] : raw["free-choice-0"] != null || raw["free-choice-1"] != null || raw["free-choice-2"] != null ? [raw["free-choice-0"], raw["free-choice-1"], raw["free-choice-2"]] : []
    );
    const marksObj = normalizeSkillMarks(
      (_e = (_d = (_c = raw.skillMarks) != null ? _c : raw.skill_marks) != null ? _d : raw.skill_marks_json) != null ? _e : ""
    );
    const giftReplacements = normalizeGiftReplacements(raw.career_gift_replacements);
    return {
      id: raw.id || "",
      name: raw.name || "",
      player_name: raw.player_name || "",
      age: raw.age || "",
      gender: raw.gender || "",
      motto: raw.motto || "",
      // Persistable narrative fields (PHP saves these)
      goal1: (_f = raw.goal1) != null ? _f : "",
      goal2: (_g = raw.goal2) != null ? _g : "",
      goal3: (_h = raw.goal3) != null ? _h : "",
      description: (_i = raw.description) != null ? _i : "",
      backstory: (_j = raw.backstory) != null ? _j : "",
      // Optional fields PHP also supports
      local_area: (_k = raw.local_area) != null ? _k : "",
      language: (_l = raw.language) != null ? _l : "",
      will: (_m = raw.will) != null ? _m : "",
      body: (_n = raw.body) != null ? _n : "",
      mind: (_o = raw.mind) != null ? _o : "",
      speed: (_p = raw.speed) != null ? _p : "",
      trait_species: (_q = raw.trait_species) != null ? _q : "",
      trait_career: (_r = raw.trait_career) != null ? _r : "",
      // Canonical IDs (what PHP expects)
      species_id: species,
      career_id: career,
      // Compatibility fallbacks (older handlers / older code paths)
      species,
      career,
      // structured blobs
      // - send BOTH:
      //   - skillMarks (object) for PHP cg_save_character()
      //   - skill_marks (JSON string) for older/legacy paths (if any)
      skillMarks: marksObj,
      skill_marks: JSON.stringify(marksObj),
      // Career gift replacements (duplicate -> increase trait selections)
      career_gift_replacements: giftReplacements,
      traits_list: raw.traitsList ? JSON.stringify(raw.traitsList) : raw.traits_list || "",
      skills_list: raw.skillsList ? JSON.stringify(raw.skillsList) : raw.skills_list || "",
      gifts: raw.gifts ? JSON.stringify(raw.gifts) : raw.gifts || "",
      // free gifts (array + convenience slots)
      free_gifts: freeArr,
      free_gifts_json: JSON.stringify(freeArr),
      free_gift_1: freeArr[0] || "",
      free_gift_2: freeArr[1] || "",
      free_gift_3: freeArr[2] || "",
      "free-choice-0": freeArr[0] || "",
      "free-choice-1": freeArr[1] || "",
      "free-choice-2": freeArr[2] || ""
    };
  }
  function buildPayload(raw) {
    var _a, _b, _c, _d, _e, _f, _g, _h, _i, _j, _k, _l, _m;
    const core = normalizeCore(raw);
    const { nonce } = ajaxEnv3();
    const base = { action: "cg_save_character" };
    if (nonce) {
      base.security = nonce;
      base.nonce = nonce;
      base._ajax_nonce = nonce;
    }
    const flat = __spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues({}, base), core.id ? { id: core.id } : {}), core.name ? { name: core.name } : {}), core.player_name ? { player_name: core.player_name } : {}), core.age ? { age: core.age } : {}), core.gender ? { gender: core.gender } : {}), core.motto ? { motto: core.motto } : {}), core.goal1 !== "" ? { goal1: core.goal1 } : {}), core.goal2 !== "" ? { goal2: core.goal2 } : {}), core.goal3 !== "" ? { goal3: core.goal3 } : {}), core.description !== "" ? { description: core.description } : {}), core.backstory !== "" ? { backstory: core.backstory } : {}), core.will !== "" ? { will: core.will } : {}), core.body !== "" ? { body: core.body } : {}), core.mind !== "" ? { mind: core.mind } : {}), core.speed !== "" ? { speed: core.speed } : {}), core.trait_species !== "" ? { trait_species: core.trait_species } : {}), core.trait_career !== "" ? { trait_career: core.trait_career } : {}), core.species_id ? { species_id: core.species_id } : {}), core.career_id ? { career_id: core.career_id } : {}), core.species ? { species: core.species } : {}), core.career ? { career: core.career } : {}), core.skill_marks ? { skill_marks: core.skill_marks } : {}), core.traits_list ? { traits_list: core.traits_list } : {}), core.skills_list ? { skills_list: core.skills_list } : {}), core.gifts ? { gifts: core.gifts } : {}), core.free_gifts_json ? { free_gifts: core.free_gifts_json } : {}), core.free_gift_1 ? { free_gift_1: core.free_gift_1 } : {}), core.free_gift_2 ? { free_gift_2: core.free_gift_2 } : {}), core.free_gift_3 ? { free_gift_3: core.free_gift_3 } : {}), core["free-choice-0"] ? { "free-choice-0": core["free-choice-0"] } : {}), core["free-choice-1"] ? { "free-choice-1": core["free-choice-1"] } : {}), core["free-choice-2"] ? { "free-choice-2": core["free-choice-2"] } : {});
    const character = {};
    try {
      const domVals = [];
      try {
        document.querySelectorAll("select.cg-extra-career-select").forEach((sel) => {
          const v = String((sel == null ? void 0 : sel.value) || "").trim();
          const n = parseInt(v, 10) || 0;
          domVals.push(n > 0 ? String(n) : "");
        });
      } catch (_) {
      }
      while (domVals.length < 2)
        domVals.push("");
      const pick = (...vals) => {
        for (const v of vals) {
          if (v == null)
            continue;
          const sv = String(v).trim();
          const nv = parseInt(sv, 10) || 0;
          if (nv > 0)
            return String(nv);
        }
        return "";
      };
      const arg0 = arguments && arguments.length ? arguments[0] || {} : {};
      const ec1 = pick(arg0.extra_career_1, domVals[0]);
      const ec2 = pick(arg0.extra_career_2, domVals[1]);
      character.extra_career_1 = ec1;
      character.extra_career_2 = ec2;
      flat.extra_career_1 = ec1;
      flat.extra_career_2 = ec2;
    } catch (_) {
    }
    if (core.id)
      character.id = core.id;
    if (core.name)
      character.name = core.name;
    character.player_name = core.player_name || "";
    character.age = core.age || "";
    character.gender = core.gender || "";
    character.motto = core.motto || "";
    character.goal1 = (_a = core.goal1) != null ? _a : "";
    character.goal2 = (_b = core.goal2) != null ? _b : "";
    character.goal3 = (_c = core.goal3) != null ? _c : "";
    character.description = (_d = core.description) != null ? _d : "";
    character.backstory = (_e = core.backstory) != null ? _e : "";
    character.local_area = (_f = core.local_area) != null ? _f : "";
    character.language = (_g = core.language) != null ? _g : "";
    character.will = (_h = core.will) != null ? _h : "";
    character.body = (_i = core.body) != null ? _i : "";
    character.mind = (_j = core.mind) != null ? _j : "";
    character.speed = (_k = core.speed) != null ? _k : "";
    character.trait_species = (_l = core.trait_species) != null ? _l : "";
    character.trait_career = (_m = core.trait_career) != null ? _m : "";
    if (core.species_id)
      character.species_id = core.species_id;
    if (core.career_id)
      character.career_id = core.career_id;
    if (core.species)
      character.species = core.species;
    if (core.career)
      character.career = core.career;
    character.skillMarks = core.skillMarks || {};
    if (core.skill_marks)
      character.skill_marks = core.skill_marks;
    if (core.traits_list)
      character.traits_list = core.traits_list;
    if (core.skills_list)
      character.skills_list = core.skills_list;
    if (core.gifts)
      character.gifts = core.gifts;
    character.free_gifts = core.free_gifts;
    character.free_gift_1 = core.free_gift_1;
    character.free_gift_2 = core.free_gift_2;
    character.free_gift_3 = core.free_gift_3;
    character["free-choice-0"] = core["free-choice-0"];
    character["free-choice-1"] = core["free-choice-1"];
    character["free-choice-2"] = core["free-choice-2"];
    character.career_gift_replacements = core.career_gift_replacements || {};
    flat.character = character;
    flat.character_json = JSON.stringify(__spreadValues({}, core));
    return flat;
  }
  var FormBuilderAPI = {
    _data: {},
    isNew: true,
    hasData: false,
    init(payload = {}) {
      this._data = __spreadValues({}, payload);
      this.isNew = Boolean(payload.isNew);
      this.hasData = !this.isNew;
      $5("#cg-form-container").html(
        form_builder_default.buildForm(this._data)
      );
    },
    getData() {
      return __spreadValues({}, this._data);
    },
    /**
     * Read every form field from the DOM into a single payload object.
     * HARDENED: if a field is not present in DOM (tab detached), fall back to in-memory _data.
     */
    collectFormData() {
      var _a, _b, _c, _d, _e, _f, _g, _h, _i, _j, _k, _l, _m, _n, _o, _p, _q, _r, _s, _t, _u, _v, _w, _x, _y, _z, _A;
      const d = __spreadValues({}, this._data || {});
      const readIfExists = (selector) => {
        const $el = $5(selector);
        if (!$el.length)
          return void 0;
        const v = $el.val();
        return v == null ? "" : v;
      };
      const normalize3 = (arr) => {
        const out = Array.isArray(arr) ? arr.slice(0, 3) : [];
        while (out.length < 3)
          out.push("");
        return out.map((v) => v == null ? "" : String(v));
      };
      const normalizeMarks = (src) => {
        var _a2, _b2, _c2, _d2, _e2, _f2, _g2, _h2, _i2;
        const out = {};
        if (!src)
          return out;
        if (Array.isArray(src)) {
          for (const x of src) {
            if (x == null)
              continue;
            if (Array.isArray(x) && x.length >= 2) {
              const k = x[0];
              const v = x[1];
              if (k != null)
                out[String(k)] = parseInt(v, 10) || 0;
              continue;
            }
            if (typeof x === "object") {
              const k = (_d2 = (_c2 = (_b2 = (_a2 = x.skillId) != null ? _a2 : x.skill_id) != null ? _b2 : x.id) != null ? _c2 : x.key) != null ? _d2 : null;
              const v = (_i2 = (_h2 = (_g2 = (_f2 = (_e2 = x.mark) != null ? _e2 : x.value) != null ? _f2 : x.val) != null ? _g2 : x.marks) != null ? _h2 : x.count) != null ? _i2 : null;
              if (k != null && v != null)
                out[String(k)] = parseInt(v, 10) || 0;
            }
          }
          return out;
        }
        if (typeof src === "object") {
          for (const k of Object.keys(src)) {
            out[String(k)] = parseInt(src[k], 10) || 0;
          }
          return out;
        }
        return out;
      };
      if (this._data && this._data.id)
        d.id = this._data.id;
      const basic = [
        ["name", "#cg-name"],
        ["player_name", "#cg-player-name"],
        ["age", "#cg-age"],
        ["gender", "#cg-gender"],
        ["motto", "#cg-motto"],
        ["goal1", "#cg-goal1"],
        ["goal2", "#cg-goal2"],
        ["goal3", "#cg-goal3"],
        ["description", "#cg-description"],
        ["backstory", "#cg-backstory"]
      ];
      basic.forEach(([key, sel]) => {
        const v = readIfExists(sel);
        if (v !== void 0)
          d[key] = v;
        if (d[key] == null)
          d[key] = "";
      });
      const memSpecies = (_i = (_h = (_f = (_c = (_a = this._data) == null ? void 0 : _a.species_id) != null ? _c : (_b = this._data) == null ? void 0 : _b.species) != null ? _f : (_e = (_d = this._data) == null ? void 0 : _d.profile) == null ? void 0 : _e.species) != null ? _h : (_g = d.profile) == null ? void 0 : _g.species) != null ? _i : "";
      const memCareer = (_r = (_q = (_o = (_l = (_j = this._data) == null ? void 0 : _j.career_id) != null ? _l : (_k = this._data) == null ? void 0 : _k.career) != null ? _o : (_n = (_m = this._data) == null ? void 0 : _m.profile) == null ? void 0 : _n.career) != null ? _q : (_p = d.profile) == null ? void 0 : _p.career) != null ? _r : "";
      const domSpecies = readIfExists("#cg-species");
      const domCareer = readIfExists("#cg-career");
      const speciesVal = String((_s = domSpecies !== void 0 ? domSpecies : memSpecies) != null ? _s : "");
      const careerVal = String((_t = domCareer !== void 0 ? domCareer : memCareer) != null ? _t : "");
      d.species_id = speciesVal;
      d.career_id = careerVal;
      d.species = speciesVal;
      d.career = careerVal;
      d.profile = d.profile && typeof d.profile === "object" ? d.profile : {};
      d.profile.species = speciesVal;
      d.profile.career = careerVal;
      this._data = this._data || {};
      this._data.species_id = speciesVal;
      this._data.career_id = careerVal;
      this._data.species = speciesVal;
      this._data.career = careerVal;
      this._data.profile = this._data.profile && typeof this._data.profile === "object" ? this._data.profile : {};
      this._data.profile.species = speciesVal;
      this._data.profile.career = careerVal;
      service_default.TRAITS.forEach((key) => {
        const sel = `#cg-${key}`;
        const v = readIfExists(sel);
        if (v !== void 0) {
          d[key] = v;
          this._data[key] = v;
        } else if (d[key] == null && this._data && this._data[key] != null) {
          d[key] = this._data[key];
        } else if (d[key] == null) {
          d[key] = "";
        }
      });
      const mergedMarks = __spreadValues({}, normalizeMarks(this._data.skillMarks));
      $5("input.skill-marks").each((i, el) => {
        const skillId = $5(el).data("skill-id");
        const val = parseInt($5(el).val(), 10) || 0;
        if (skillId != null)
          mergedMarks[String(skillId)] = val;
      });
      const btnMarks = {};
      $5(".skill-mark-btn").each((i, el) => {
        const $b = $5(el);
        const isOn = $b.hasClass("active") || $b.hasClass("is-active") || $b.hasClass("selected") || String($b.attr("aria-pressed") || "") === "true" || String($b.data("active") || "") === "1" || String($b.attr("data-active") || "") === "1";
        if (!isOn)
          return;
        const sid = $b.data("skill-id");
        const mk = parseInt($b.data("mark"), 10) || 0;
        const key = sid == null ? "" : String(sid);
        if (!key)
          return;
        if (btnMarks[key] == null || btnMarks[key] < mk)
          btnMarks[key] = mk;
      });
      if (Object.keys(btnMarks).length) {
        Object.keys(btnMarks).forEach((k) => {
          mergedMarks[String(k)] = btnMarks[k];
        });
      }
      d.skillMarks = mergedMarks;
      this._data.skillMarks = mergedMarks;
      const s0 = readIfExists("#cg-free-choice-0");
      const s1 = readIfExists("#cg-free-choice-1");
      const s2 = readIfExists("#cg-free-choice-2");
      let freeArr;
      if (s0 !== void 0 || s1 !== void 0 || s2 !== void 0) {
        freeArr = normalize3([s0 != null ? s0 : "", s1 != null ? s1 : "", s2 != null ? s2 : ""]);
      } else if (Array.isArray(this._data.free_gifts)) {
        freeArr = normalize3(this._data.free_gifts);
      } else if (Array.isArray(this._data.freeGifts)) {
        freeArr = normalize3(this._data.freeGifts);
      } else if (this._data["free-choice-0"] != null || this._data["free-choice-1"] != null || this._data["free-choice-2"] != null) {
        freeArr = normalize3([this._data["free-choice-0"], this._data["free-choice-1"], this._data["free-choice-2"]]);
      } else {
        freeArr = normalize3([this._data.free_gift_1, this._data.free_gift_2, this._data.free_gift_3]);
      }
      d.free_gifts = freeArr;
      d.freeGifts = freeArr.slice();
      d.free_gift_1 = freeArr[0] || "";
      d.free_gift_2 = freeArr[1] || "";
      d.free_gift_3 = freeArr[2] || "";
      d["free-choice-0"] = d.free_gift_1;
      d["free-choice-1"] = d.free_gift_2;
      d["free-choice-2"] = d.free_gift_3;
      this._data.free_gifts = freeArr.slice();
      this._data.freeGifts = freeArr.slice();
      this._data.free_gift_1 = d.free_gift_1;
      this._data.free_gift_2 = d.free_gift_2;
      this._data.free_gift_3 = d.free_gift_3;
      this._data["free-choice-0"] = d["free-choice-0"];
      this._data["free-choice-1"] = d["free-choice-1"];
      this._data["free-choice-2"] = d["free-choice-2"];
      if (Array.isArray(window.CG_SKILLS_LIST))
        d.skillsList = window.CG_SKILLS_LIST;
      try {
        const domVals = [];
        try {
          document.querySelectorAll("select.cg-extra-career-select").forEach((sel) => {
            const v = String((sel == null ? void 0 : sel.value) || "").trim();
            const n = parseInt(v, 10) || 0;
            domVals.push(n > 0 ? String(n) : "");
          });
        } catch (_) {
        }
        while (domVals.length < 2)
          domVals.push("");
        const pick = (...vals) => {
          for (const v of vals) {
            if (v == null)
              continue;
            const sv = String(v).trim();
            const nv = parseInt(sv, 10) || 0;
            if (nv > 0)
              return String(nv);
          }
          return "";
        };
        const ecs = Array.isArray((_u = this._data) == null ? void 0 : _u.extraCareers) ? this._data.extraCareers : null;
        const v1 = pick(d.extra_career_1, (_v = this._data) == null ? void 0 : _v.extra_career_1, (_w = ecs == null ? void 0 : ecs[0]) == null ? void 0 : _w.id, domVals[0]);
        const v2 = pick(d.extra_career_2, (_x = this._data) == null ? void 0 : _x.extra_career_2, (_y = ecs == null ? void 0 : ecs[1]) == null ? void 0 : _y.id, domVals[1]);
        d.extra_career_1 = v1;
        d.extra_career_2 = v2;
        this._data.extra_career_1 = v1;
        this._data.extra_career_2 = v2;
      } catch (_) {
      }
      try {
        const domReplacements = {};
        document.querySelectorAll(".cg-career-gift-replace").forEach((sel) => {
          const slot = sel.getAttribute("data-slot");
          const val = sel.value;
          if (slot && val && val !== "0") {
            domReplacements[String(slot)] = String(val);
          }
        });
        const memReplacements = ((_z = this._data) == null ? void 0 : _z.career_gift_replacements) || {};
        const merged = __spreadValues({}, memReplacements);
        Object.keys(domReplacements).forEach((k) => {
          if (domReplacements[k]) {
            merged[k] = domReplacements[k];
          }
        });
        const cleaned = {};
        Object.keys(merged).forEach((k) => {
          const v = merged[k];
          if (v && String(v).trim() && String(v) !== "0") {
            cleaned[k] = String(v);
          }
        });
        d.career_gift_replacements = cleaned;
        this._data.career_gift_replacements = cleaned;
      } catch (_) {
        d.career_gift_replacements = ((_A = this._data) == null ? void 0 : _A.career_gift_replacements) || {};
      }
      return d;
    },
    /**
     * Save the character via WP-AJAX and optionally close builder.
     * Guarded to prevent double-save if multiple click handlers exist.
     */
    save(shouldClose = false) {
      if (window.CG_SAVE_IN_FLIGHT) {
        console.warn("[FormBuilderAPI] save() blocked: CG_SAVE_IN_FLIGHT already true");
        return $5.Deferred().reject("in-flight").promise();
      }
      window.CG_SAVE_IN_FLIGHT = true;
      const raw = this.collectFormData();
      console.log("[CG_SAVE_DEBUG_SKILLMARKS]", Object.keys(raw && raw.skillMarks ? raw.skillMarks : {}).length, raw && raw.skillMarks ? raw.skillMarks : {});
      console.log("[FormBuilderAPI] \u25B6 save()", raw);
      const { url } = ajaxEnv3();
      if (!url) {
        window.CG_SAVE_IN_FLIGHT = false;
        console.error("[FormBuilderAPI] save(): No AJAX URL available");
        alert("Save error: missing AJAX URL");
        return $5.Deferred().reject("no-url").promise();
      }
      const data = buildPayload(raw);
      const req = $5.post(url, data);
      req.done((res) => {
        var _a, _b;
        try {
          res = typeof res === "string" ? JSON.parse(res) : res;
        } catch (_) {
        }
        if (!res || res.success !== true) {
          console.error("[FormBuilderAPI] save.error()", res);
          alert("Save failed: " + ((res == null ? void 0 : res.data) || "Invalid payload."));
          return;
        }
        if ((_a = res.data) == null ? void 0 : _a.id) {
          this._data = __spreadProps(__spreadValues({}, this._data), { id: res.data.id });
        }
        this.isNew = false;
        this.hasData = true;
        builder_ui_default.markClean();
        if (shouldClose) {
          builder_ui_default.closeBuilder();
        } else {
          alert("Character saved");
        }
        document.dispatchEvent(new CustomEvent("cg:character:saved", { detail: { id: ((_b = res.data) == null ? void 0 : _b.id) || raw.id || null, record: raw } }));
        document.dispatchEvent(new CustomEvent("cg:characters:refresh", { detail: {} }));
      }).fail((xhr, status, err2) => {
        console.error("[FormBuilderAPI] save.fail()", status, err2, xhr == null ? void 0 : xhr.responseText);
        alert("Save failed\u2014check console for details.");
      }).always(() => {
        window.CG_SAVE_IN_FLIGHT = false;
      });
      return req;
    },
    listCharacters() {
      const { url, nonce } = ajaxEnv3();
      return $5.ajax({
        url,
        method: "POST",
        data: {
          action: "cg_load_characters",
          security: nonce
        }
      });
    },
    fetchCharacter(id) {
      const { url, nonce } = ajaxEnv3();
      console.log("[FormBuilderAPI] fetchCharacter() called with ID:", id);
      return $5.ajax({
        url,
        method: "POST",
        data: {
          action: "cg_get_character",
          id,
          security: nonce
        }
      });
    }
  };
  var formBuilder_default = FormBuilderAPI;
  try {
    window.FormBuilderAPI = window.FormBuilderAPI || FormBuilderAPI;
    window.CG_FormBuilderAPI = window.CG_FormBuilderAPI || FormBuilderAPI;
  } catch (_) {
  }

  // assets/js/src/core/species/events.js
  var $6 = window.jQuery;
  var _bound = false;
  function normalizeSpeciesProfile(raw = {}) {
    const out = __spreadValues({}, raw);
    out.speciesName = raw.speciesName || raw.species_name || raw.ct_species_name || raw.name || raw.title || "";
    if (raw.skill_one != null)
      out.skill_one = String(raw.skill_one);
    if (raw.skill_two != null)
      out.skill_two = String(raw.skill_two);
    if (raw.skill_three != null)
      out.skill_three = String(raw.skill_three);
    ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
      if (raw[k] != null)
        out[k] = String(raw[k]);
    });
    ["manifold_1", "manifold_2", "manifold_3"].forEach((k) => {
      if (raw[k] != null)
        out[k] = parseInt(raw[k], 10) || 1;
    });
    return out;
  }
  function renderGiftList($ul, items = []) {
    if (!$ul || !$ul.length)
      return;
    $ul.empty();
    items.forEach((txt) => {
      if (!txt)
        return;
      $ul.append(`<li>${txt}</li>`);
    });
  }
  function namesFromProfile(profile = {}) {
    const names = [profile.gift_1, profile.gift_2, profile.gift_3].map((v) => v == null ? "" : String(v)).filter(Boolean);
    return names.map((name, i) => {
      const m = profile[`manifold_${i + 1}`];
      const mult = parseInt(m, 10) || 1;
      return mult > 1 ? `${name} \xD7 ${mult}` : name;
    });
  }
  function bindSpeciesEvents() {
    if (_bound)
      return;
    _bound = true;
    $6(document).off("change.cg", "#cg-species").on("change.cg", "#cg-species", (e) => {
      const val = e.currentTarget && e.currentTarget.value || "";
      console.log("[SpeciesEvents] selected species \u2192", val);
      if (!val) {
        api_default.currentProfile = null;
        renderGiftList($6("#species-gift-block"), []);
        $6(document).trigger("cg:species:changed", [{ id: "", profile: null }]);
        return;
      }
      api_default.fetchProfile(val).done((profileRaw) => {
        const profile = normalizeSpeciesProfile(profileRaw || {});
        api_default.currentProfile = profile;
        const giftNames = namesFromProfile(profile);
        renderGiftList($6("#species-gift-block"), giftNames);
        $6(document).trigger("cg:species:changed", [{ id: String(val), profile }]);
      });
    });
  }

  // assets/js/src/core/species/index.js
  var SpeciesIndex = {
    _init: false,
    /**
     * One-time init: bind change events and populate the select if present.
     */
    init() {
      if (this._init)
        return;
      this._init = true;
      bindSpeciesEvents();
      const sel = document.querySelector("#cg-species");
      if (sel) {
        api_default.populateSelect(sel);
      }
    },
    /**
     * Refresh the species <select>. Will repopulate if empty or when force=true.
     * @param {{force?: boolean}} opts
     * @returns {Promise<void>|void}
     */
    refresh(opts = {}) {
      const sel = document.querySelector("#cg-species");
      if (!sel)
        return;
      const force = !!opts.force || sel.options.length <= 1;
      return api_default.populateSelect(sel, { force });
    }
  };
  var species_default = SpeciesIndex;

  // assets/js/src/core/career/events.js
  var $7 = window.jQuery;
  var _bound2 = false;
  var _giftWaitTries = 0;
  var _giftWaitTimer = null;
  function normalizeCareerProfile(raw = {}) {
    const out = __spreadValues({}, raw);
    out.careerName = raw.careerName || raw.career_name || raw.ct_career_name || raw.name || raw.title || "";
    if (raw.skill_one != null)
      out.skill_one = String(raw.skill_one);
    if (raw.skill_two != null)
      out.skill_two = String(raw.skill_two);
    if (raw.skill_three != null)
      out.skill_three = String(raw.skill_three);
    ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
      if (raw[k] != null)
        out[k] = String(raw[k]);
    });
    ["manifold_1", "manifold_2", "manifold_3"].forEach((k) => {
      if (raw[k] != null)
        out[k] = parseInt(raw[k], 10) || 1;
    });
    return out;
  }
  function escapeHtml(s) {
    return String(s != null ? s : "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }
  function normalizeReplacementMap(src) {
    if (!src)
      return {};
    if (typeof src === "string") {
      try {
        return normalizeReplacementMap(JSON.parse(src));
      } catch (_) {
        return {};
      }
    }
    if (Array.isArray(src)) {
      const out = {};
      src.forEach((it) => {
        var _a, _b, _c, _d;
        const slot = (_b = (_a = it == null ? void 0 : it.slot) != null ? _a : it == null ? void 0 : it.key) != null ? _b : it == null ? void 0 : it.k;
        const id = (_d = (_c = it == null ? void 0 : it.id) != null ? _c : it == null ? void 0 : it.value) != null ? _d : it == null ? void 0 : it.v;
        if (slot != null && id != null && String(id))
          out[String(slot)] = String(id);
      });
      return out;
    }
    if (typeof src === "object") {
      const out = {};
      Object.keys(src).forEach((k) => {
        const v = src[k];
        if (v != null && String(v))
          out[String(k)] = String(v);
      });
      return out;
    }
    return {};
  }
  function getAllGiftsList() {
    return window.CG_FreeChoices && Array.isArray(window.CG_FreeChoices._allGifts) ? window.CG_FreeChoices._allGifts : [];
  }
  function byIdMap(all = []) {
    const map = /* @__PURE__ */ Object.create(null);
    all.forEach((g) => {
      if (!g || g.id == null)
        return;
      map[String(g.id)] = g;
    });
    return map;
  }
  function isRepeatableGift(g) {
    if (!g)
      return false;
    if (g.allows_multiple)
      return true;
    const m = parseInt(g.ct_gifts_manifold, 10);
    if (!Number.isNaN(m) && m > 1)
      return true;
    const m2 = parseInt(g.manifold, 10);
    if (!Number.isNaN(m2) && m2 > 1)
      return true;
    return false;
  }
  function increaseTraitGifts(all = []) {
    const inc = (all || []).filter((g) => g && g.id != null).filter((g) => {
      const nm = String(g.name || g.title || g.ct_gift_name || "");
      return /^Increase\s+/i.test(nm) || /Increased Trait/i.test(nm);
    }).map((g) => ({ id: String(g.id), name: String(g.name || g.title || g.ct_gift_name || `Gift #${g.id}`) }));
    inc.sort((a, b) => a.name.localeCompare(b.name));
    return inc;
  }
  function getFormBuilderData() {
    const fb = window.CG_FormBuilderAPI || window.FormBuilderAPI || null;
    return fb && fb._data ? fb._data : null;
  }
  function setFormBuilderCareerReplacements(map) {
    const d = getFormBuilderData();
    if (!d)
      return;
    d.career_gift_replacements = map;
  }
  function _hasKeys(obj) {
    try {
      return !!obj && typeof obj === "object" && Object.keys(obj).length > 0;
    } catch (_) {
      return false;
    }
  }
  function _shouldPreserveOnEmptyCareer(e) {
    var _a, _b;
    const isUser = !!(e && e.originalEvent);
    if (isUser)
      return false;
    const d = getFormBuilderData();
    if (!d)
      return false;
    const fbCareer = String((_b = (_a = d.career_id) != null ? _a : d.career) != null ? _b : "").trim();
    if (!fbCareer)
      return false;
    const repl = normalizeReplacementMap(d.career_gift_replacements);
    return _hasKeys(repl);
  }
  function renderCareerGiftsWithReplacements(profile) {
    var _a, _b;
    const $ul = $7("#career-gifts");
    if (!$ul || !$ul.length)
      return;
    const sp = ((_a = api_default) == null ? void 0 : _a.currentProfile) || null;
    const spIds = new Set(
      (sp ? [sp.gift_id_1, sp.gift_id_2, sp.gift_id_3] : []).filter((v) => v != null && String(v)).map((v) => String(v))
    );
    const all = getAllGiftsList();
    const idMap = byIdMap(all);
    const inc = increaseTraitGifts(all);
    if (!all.length) {
      const labels2 = ["Career Gift One", "Career Gift Two", "Career Gift Three"];
      const li2 = [];
      for (let i = 1; i <= 3; i++) {
        const name = profile[`gift_${i}`] || "";
        const gid = profile[`gift_id_${i}`] || "";
        const mult = parseInt(profile[`manifold_${i}`], 10) || 1;
        const display = name ? String(name) : gid ? `Gift #${gid}` : "";
        if (!display)
          continue;
        li2.push(`<li><strong>${labels2[i - 1]}:</strong> ${escapeHtml(mult > 1 ? `${display} \xD7 ${mult}` : display)}</li>`);
      }
      $ul.html(li2.join(""));
      if (_giftWaitTries < 10) {
        _giftWaitTries++;
        clearTimeout(_giftWaitTimer);
        _giftWaitTimer = setTimeout(() => renderCareerGiftsWithReplacements(profile), 350);
      }
      return;
    }
    _giftWaitTries = 0;
    clearTimeout(_giftWaitTimer);
    _giftWaitTimer = null;
    const labels = ["Career Gift One", "Career Gift Two", "Career Gift Three"];
    const savedFromFB = normalizeReplacementMap((_b = getFormBuilderData()) == null ? void 0 : _b.career_gift_replacements);
    const savedFromProfile = normalizeReplacementMap(profile.career_gift_replacements);
    const saved = __spreadValues(__spreadValues({}, savedFromFB), savedFromProfile);
    const neededSlots = /* @__PURE__ */ new Set();
    const li = [];
    for (let i = 1; i <= 3; i++) {
      const gid = profile[`gift_id_${i}`] || "";
      const mult = parseInt(profile[`manifold_${i}`], 10) || 1;
      const fromProfileName = profile[`gift_${i}`] || "";
      const fromAllName = gid && idMap[String(gid)] ? idMap[String(gid)].name || idMap[String(gid)].title || idMap[String(gid)].ct_gift_name || "" : "";
      const baseName = String(fromProfileName || fromAllName || (gid ? `Gift #${gid}` : ""));
      if (!gid && !baseName)
        continue;
      const dupeWithSpecies = gid && spIds.has(String(gid));
      const repeatable = dupeWithSpecies ? isRepeatableGift(idMap[String(gid)]) : false;
      const needsReplace = !!(dupeWithSpecies && !repeatable);
      if (!needsReplace) {
        const txt = mult > 1 ? `${baseName} \xD7 ${mult}` : baseName;
        li.push(`<li><strong>${labels[i - 1]}:</strong> ${escapeHtml(txt)}</li>`);
        continue;
      }
      neededSlots.add(String(i));
      const current = saved[String(i)] || "";
      const selectId = `cg-career-gift-replace-${i}`;
      let selectHtml = "";
      if (!inc.length) {
        selectHtml = `<select id="${escapeHtml(selectId)}" class="cg-profile-select cg-career-gift-replace" data-slot="${escapeHtml(i)}" disabled>
        <option value="">(No \u201CIncrease Trait\u201D gifts found)</option>
      </select>`;
      } else {
        const opts = inc.map((g) => `<option value="${escapeHtml(g.id)}"${String(g.id) === String(current) ? " selected" : ""}>${escapeHtml(g.name)}</option>`).join("");
        selectHtml = `<select id="${escapeHtml(selectId)}" class="cg-profile-select cg-career-gift-replace" data-slot="${escapeHtml(i)}">
        <option value="">\u2014 Choose an Increase Trait gift \u2014</option>
        ${opts}
      </select>`;
      }
      li.push(
        `<li class="cg-career-gift-line cg-career-gift-line--replace">
        <strong>${labels[i - 1]}:</strong>
        <span class="cg-career-gift-dup-note">Duplicate: ${escapeHtml(baseName)} \u2192</span>
        ${selectHtml}
      </li>`
      );
    }
    const cleaned = {};
    Object.keys(saved).forEach((k) => {
      if (neededSlots.has(String(k)) && saved[k])
        cleaned[String(k)] = String(saved[k]);
    });
    profile.career_gift_replacements = cleaned;
    api_default2.currentProfile = profile;
    setFormBuilderCareerReplacements(cleaned);
    $ul.html(li.join(""));
    setTimeout(() => {
      try {
        neededSlots.forEach((slot) => {
          const v = cleaned[String(slot)] || "";
          const el = document.getElementById(`cg-career-gift-replace-${slot}`);
          if (el && v)
            el.value = String(v);
        });
      } catch (_) {
      }
    }, 0);
  }
  function _rerenderFromSpeciesChange() {
    if (!api_default2.currentProfile)
      return;
    renderCareerGiftsWithReplacements(api_default2.currentProfile);
  }
  function bindCareerEvents() {
    if (_bound2)
      return;
    _bound2 = true;
    try {
      window.__CG_BUILD_MARKERS__ = window.__CG_BUILD_MARKERS__ || {};
      window.__CG_BUILD_MARKERS__.CareerEvents = "v2026-01-04c";
    } catch (_) {
    }
    $7(document).off("change.cg", "#career-gifts .cg-career-gift-replace").on("change.cg", "#career-gifts .cg-career-gift-replace", (e) => {
      const sel = e.currentTarget;
      const slot = sel && sel.dataset && sel.dataset.slot ? String(sel.dataset.slot) : "";
      const val = sel && sel.value ? String(sel.value) : "";
      const profile = api_default2.currentProfile || null;
      if (!profile || !slot)
        return;
      const map = normalizeReplacementMap(profile.career_gift_replacements);
      if (!val)
        delete map[slot];
      else
        map[slot] = val;
      profile.career_gift_replacements = map;
      api_default2.currentProfile = profile;
      setFormBuilderCareerReplacements(map);
      const careerId = String($7("#cg-career").val() || "");
      $7(document).trigger("cg:career:changed", [{ id: careerId, profile }]);
      $7(document).trigger("cg:free-gift:changed", [{ source: "career-replacement" }]);
    });
    $7(document).off("change.cg", "#cg-career").on("change.cg", "#cg-career", (e) => {
      var _a, _b;
      const val = e.currentTarget && e.currentTarget.value || "";
      console.log("[CareerEvents] selected career \u2192", val);
      if (!val) {
        const preserve = _shouldPreserveOnEmptyCareer(e);
        api_default2.currentProfile = null;
        $7("#career-gifts").empty();
        if (!preserve) {
          setFormBuilderCareerReplacements({});
        } else {
          try {
            const d = getFormBuilderData();
            console.log("[CareerEvents] empty career during load; preserving career_gift_replacements", {
              career: String((_b = (_a = d == null ? void 0 : d.career_id) != null ? _a : d == null ? void 0 : d.career) != null ? _b : ""),
              repl: normalizeReplacementMap(d == null ? void 0 : d.career_gift_replacements)
            });
          } catch (_) {
          }
        }
        $7(document).trigger("cg:career:changed", [{ id: "", profile: null }]);
        return;
      }
      api_default2.fetchProfile(val).done((profileRaw) => {
        var _a2;
        const profile = normalizeCareerProfile(profileRaw || {});
        const saved = normalizeReplacementMap((_a2 = getFormBuilderData()) == null ? void 0 : _a2.career_gift_replacements);
        if (Object.keys(saved).length)
          profile.career_gift_replacements = saved;
        api_default2.currentProfile = profile;
        renderCareerGiftsWithReplacements(profile);
        $7(document).trigger("cg:career:changed", [{ id: String(val), profile }]);
      });
    });
    $7(document).off("cg:species:changed.careergifts").on("cg:species:changed.careergifts", _rerenderFromSpeciesChange);
    try {
      window.__CG_EVT__ = window.__CG_EVT__ || {};
      if (window.__CG_EVT__.speciesChangedCareerGiftsNative) {
        document.removeEventListener("cg:species:changed", window.__CG_EVT__.speciesChangedCareerGiftsNative);
      }
      window.__CG_EVT__.speciesChangedCareerGiftsNative = _rerenderFromSpeciesChange;
      document.addEventListener("cg:species:changed", window.__CG_EVT__.speciesChangedCareerGiftsNative);
    } catch (_) {
    }
  }

  // assets/js/src/core/career/extra.js
  var $8 = window.jQuery;
  var LOG = (...a) => console.log("[ExtraCareers]", ...a);
  var WARN = (...a) => console.warn("[ExtraCareers]", ...a);
  var EXTRA_CAREER_GIFT_ID = "184";
  var INC_TRAIT_CAREER_GIFT_ID = "223";
  var BOOST_TARGET_KEY_LEGACY = "increased_trait_career_target";
  var BOOST_TARGET_KEY_PREFIX = "increased_trait_career_target_";
  var ALWAYS_ACQUIRED_GIFT_IDS = ["242", "236"];
  var BOOST_TARGET_STYLE_ID = "cg-inc-trait-career-target-inline-style";
  function boostKey(slot) {
    return `${BOOST_TARGET_KEY_PREFIX}${slot}`;
  }
  function boostSelectId(slot) {
    return `cg-inc-trait-career-target-${slot}`;
  }
  function ajaxEnv4() {
    var _a, _b;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    const ajax_url = env.ajax_url || window.ajaxurl || ((_b = (_a = document.body) == null ? void 0 : _a.dataset) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php";
    const perAction = window.CG_NONCES && window.CG_NONCES.cg_get_career_gifts ? window.CG_NONCES.cg_get_career_gifts : null;
    const generic = env.nonce || env.security || env._ajax_nonce || window.CG_NONCE || null;
    return { ajax_url, nonce: perAction || generic };
  }
  function parseJsonMaybe(res) {
    try {
      return typeof res === "string" ? JSON.parse(res) : res;
    } catch (_) {
      return res;
    }
  }
  function normalizeCareerProfile2(raw = {}) {
    const out = raw && typeof raw === "object" ? __spreadValues({}, raw) : {};
    out.careerName = raw.careerName || raw.career_name || raw.ct_career_name || raw.name || raw.title || "";
    ["gift_id_1", "gift_id_2", "gift_id_3", "skill_one", "skill_two", "skill_three"].forEach((k) => {
      if (out[k] != null)
        out[k] = String(out[k]);
    });
    out.skills = [out.skill_one, out.skill_two, out.skill_three].map((v) => v ? String(v) : "").filter(Boolean);
    return out;
  }
  function stepDie(die, steps) {
    const order = ["d4", "d6", "d8", "d10", "d12"];
    const d = String(die || "").toLowerCase();
    const i = order.indexOf(d);
    if (i === -1)
      return die || "";
    const s = Math.max(0, parseInt(steps, 10) || 0);
    return order[Math.min(order.length - 1, i + s)];
  }
  function escapeHtml2(s) {
    return String(s != null ? s : "").replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#39;");
  }
  var ExtraCareers = {
    _inited: false,
    _bound: false,
    _profileCache: /* @__PURE__ */ new Map(),
    // careerId -> normalized profile
    _profilePromise: /* @__PURE__ */ new Map(),
    // careerId -> in-flight promise
    _eligibleCacheKey: "",
    _eligibleCache: [],
    init() {
      var _a, _b;
      if (this._inited)
        return;
      this._inited = true;
      try {
        (_b = (_a = state_default).init) == null ? void 0 : _b.call(_a);
      } catch (_) {
      }
      this._bindOnce();
      try {
        window.__CG_EVT__ = window.__CG_EVT__ || {};
        const EVT = window.__CG_EVT__;
        if (EVT.extraCareersOnBuilderOpened) {
          document.removeEventListener("cg:builder:opened", EVT.extraCareersOnBuilderOpened);
        }
        if (EVT.extraCareersOnCharacterLoaded) {
          document.removeEventListener("cg:character:loaded", EVT.extraCareersOnCharacterLoaded);
        }
        if (EVT.extraCareersOnFreeGiftChanged) {
          document.removeEventListener("cg:free-gift:changed", EVT.extraCareersOnFreeGiftChanged);
        }
        EVT.extraCareersOnBuilderOpened = () => ExtraCareers.render();
        EVT.extraCareersOnCharacterLoaded = () => ExtraCareers.render();
        EVT.extraCareersOnFreeGiftChanged = () => ExtraCareers.render();
        document.addEventListener("cg:builder:opened", EVT.extraCareersOnBuilderOpened);
        document.addEventListener("cg:character:loaded", EVT.extraCareersOnCharacterLoaded);
        document.addEventListener("cg:free-gift:changed", EVT.extraCareersOnFreeGiftChanged);
      } catch (e) {
        try {
          WARN("idempotent native listener bind failed", e);
        } catch (_) {
        }
      }
      if ($8) {
        $8(document).off("cg:species:changed.cgextra cg:career:changed.cgextra cg:free-gift:changed.cgextra").on("cg:species:changed.cgextra cg:career:changed.cgextra cg:free-gift:changed.cgextra", () => this.render());
      }
      setTimeout(() => this.render(), 0);
    },
    _bindOnce() {
      if (this._bound || !$8)
        return;
      this._bound = true;
      $8(document).off("change.cgextra", ".cg-extra-career-select").on("change.cgextra", ".cg-extra-career-select", (e) => __async(this, null, function* () {
        const el = e.currentTarget;
        const idx = parseInt(el.getAttribute("data-index") || "0", 10);
        const careerId = String(el.value || "");
        yield this._setSelection(idx, careerId);
        this.render();
      }));
      $8(document).off("change.cgextra", ".cg-inc-trait-career-target-select").on("change.cgextra", ".cg-inc-trait-career-target-select", (e) => {
        const el = e.currentTarget;
        const slot = parseInt(el.getAttribute("data-slot") || "-1", 10);
        const v = String(el.value || "main") || "main";
        if (slot >= 0)
          this._setBoostTargetForSlot(slot, v);
        this.render();
      });
    },
    _getWrap() {
      return document.querySelector("#cg-extra-careers") || null;
    },
    _getTraitsWrap() {
      return document.querySelector("#cg-extra-career-traits") || null;
    },
    _hideProfileDiceBadges() {
      try {
        const badge = document.getElementById("cg-profile-trait_career-badge");
        if (badge)
          badge.style.display = "none";
      } catch (_) {
      }
    },
    _readExtraCareersFromData() {
      var _a;
      const d = ((_a = formBuilder_default) == null ? void 0 : _a._data) || {};
      if (Array.isArray(d.extraCareers))
        return d.extraCareers.map((x) => __spreadValues({}, x));
      if (typeof d.extra_careers === "string" && d.extra_careers.trim()) {
        try {
          const arr = JSON.parse(d.extra_careers);
          if (Array.isArray(arr))
            return arr.map((x) => __spreadValues({}, x));
        } catch (_) {
        }
      }
      const out = [];
      for (let i = 1; i <= 6; i++) {
        const key = `extra_career_${i}`;
        if (!Object.prototype.hasOwnProperty.call(d, key))
          continue;
        const id = d[key] ? String(d[key]) : "";
        out.push({ id, name: "", skills: [] });
      }
      if (!out.length && Object.prototype.hasOwnProperty.call(d, "extra_career_0")) {
        const id0 = d.extra_career_0 ? String(d.extra_career_0) : "";
        if (id0)
          out.push({ id: id0, name: "", skills: [] });
      }
      return out;
    },
    _writeExtraCareersToData(list) {
      var _a;
      const normalized = (Array.isArray(list) ? list : []).filter((x) => x && x.id).map((x) => ({
        id: String(x.id),
        name: String(x.name || ""),
        skills: Array.isArray(x.skills) ? x.skills.map(String).filter(Boolean) : []
      }));
      const __cgr2 = (_a = formBuilder_default._data) == null ? void 0 : _a.career_gift_replacements;
      formBuilder_default._data = __spreadProps(__spreadValues({}, formBuilder_default._data || {}), {
        extraCareers: normalized,
        extra_careers: JSON.stringify(normalized),
        career_gift_replacements: __cgr2 || {}
      });
      normalized.forEach((x, i) => {
        const k = i + 1;
        formBuilder_default._data[`extra_career_${k}`] = String(x.id);
      });
      for (let k = normalized.length + 1; k <= 6; k++) {
        delete formBuilder_default._data[`extra_career_${k}`];
      }
      delete formBuilder_default._data.extra_career_0;
    },
    _emitChanged() {
      var _a;
      const payload = { extraCareers: ((_a = formBuilder_default._data) == null ? void 0 : _a.extraCareers) || [] };
      document.dispatchEvent(new CustomEvent("cg:extra-careers:changed", { detail: payload }));
      if ($8)
        $8(document).trigger("cg:extra-careers:changed", [payload]);
    },
    _countGiftInAcquired(giftId) {
      var _a, _b, _c;
      const target = String(giftId);
      let count = 0;
      const dFB = ((_a = formBuilder_default) == null ? void 0 : _a._data) || {};
      let free = Array.isArray(state_default.selected) && state_default.selected.length ? state_default.selected : null;
      if (!free) {
        if (Array.isArray(dFB.free_gifts) && dFB.free_gifts.length)
          free = dFB.free_gifts;
        else if (Array.isArray(dFB.freeGifts) && dFB.freeGifts.length)
          free = dFB.freeGifts;
        else {
          free = [];
          ["free-choice-0", "free-choice-1", "free-choice-2"].forEach((k) => {
            const v = dFB[k];
            if (v != null && String(v).trim())
              free.push(String(v));
          });
        }
      }
      free = Array.isArray(free) ? free : [];
      free.forEach((id) => {
        if (String(id) === target)
          count++;
      });
      const sp = ((_b = api_default) == null ? void 0 : _b.currentProfile) || null;
      if (sp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
          if (sp[k] != null && String(sp[k]) === target)
            count++;
        });
      }
      const cp = ((_c = api_default2) == null ? void 0 : _c.currentProfile) || null;
      if (cp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
          if (cp[k] != null && String(cp[k]) === target)
            count++;
        });
      }
      return count;
    },
    _mainCareerId() {
      var _a;
      const dom = document.querySelector("#cg-career");
      if (dom && dom.value)
        return String(dom.value);
      const d = ((_a = formBuilder_default) == null ? void 0 : _a._data) || {};
      return String(d.career_id || d.career || "");
    },
    _mainCareerName() {
      var _a;
      const dom = document.querySelector("#cg-career");
      if (dom) {
        const opt = (_a = dom.options) == null ? void 0 : _a[dom.selectedIndex];
        const txt = opt ? opt.textContent : "";
        if (txt)
          return String(txt).trim();
      }
      return "Main Career";
    },
    _careerTraitBaseDie() {
      var _a, _b;
      const dom = document.querySelector("#cg-trait_career") || document.querySelector("#cg-trait-career") || document.querySelector('select[name="trait_career"]') || document.querySelector('select[data-trait="career"]');
      const data = ((_a = formBuilder_default) == null ? void 0 : _a.getData) ? formBuilder_default.getData() : ((_b = formBuilder_default) == null ? void 0 : _b._data) || {};
      const v = dom && dom.value ? String(dom.value) : data && (data.trait_career || data.traitCareer) ? String(data.trait_career || data.traitCareer) : "";
      return v && v.trim() ? v.trim() : "d4";
    },
    _countExtraCareerUnlocks() {
      var _a, _b;
      let count = 0;
      const free = Array.isArray(state_default.selected) ? state_default.selected : [];
      free.forEach((id) => {
        if (String(id) === EXTRA_CAREER_GIFT_ID)
          count++;
      });
      const sp = ((_a = api_default) == null ? void 0 : _a.currentProfile) || null;
      if (sp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
          if (sp[k] != null && String(sp[k]) === EXTRA_CAREER_GIFT_ID)
            count++;
        });
      }
      const cp = ((_b = api_default2) == null ? void 0 : _b.currentProfile) || null;
      if (cp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
          if (cp[k] != null && String(cp[k]) === EXTRA_CAREER_GIFT_ID)
            count++;
        });
      }
      return count;
    },
    _acquiredGiftSet() {
      var _a, _b;
      const set = /* @__PURE__ */ new Set();
      ALWAYS_ACQUIRED_GIFT_IDS.forEach((id) => set.add(String(id)));
      const free = Array.isArray(state_default.selected) ? state_default.selected : [];
      free.forEach((id) => {
        if (id)
          set.add(String(id));
      });
      const sp = ((_a = api_default) == null ? void 0 : _a.currentProfile) || null;
      if (sp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
          if (sp[k] != null && String(sp[k]))
            set.add(String(sp[k]));
        });
      }
      const cp = ((_b = api_default2) == null ? void 0 : _b.currentProfile) || null;
      if (cp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
          if (cp[k] != null && String(cp[k]))
            set.add(String(cp[k]));
        });
      }
      return set;
    },
    _getCareerProfile(careerId) {
      const id = String(careerId || "");
      if (!id)
        return Promise.resolve(null);
      if (this._profileCache.has(id))
        return Promise.resolve(this._profileCache.get(id));
      if (this._profilePromise.has(id))
        return this._profilePromise.get(id);
      const { ajax_url, nonce } = ajaxEnv4();
      if (!ajax_url)
        return Promise.resolve(null);
      const payload = { action: "cg_get_career_gifts", id };
      if (nonce) {
        payload.security = nonce;
        payload.nonce = nonce;
        payload._ajax_nonce = nonce;
      }
      const cleanup = () => {
        this._profilePromise.delete(id);
      };
      const req = $8 && $8.post ? $8.post(ajax_url, payload) : null;
      const p = (req ? req : Promise.resolve(null)).then(
        (res) => {
          const json = parseJsonMaybe(res);
          const raw = json && json.success === true ? json.data : json && json.success === void 0 ? json : null;
          if (!raw)
            return null;
          const prof = normalizeCareerProfile2(raw);
          this._profileCache.set(id, prof);
          return prof;
        },
        () => null
      );
      if (req && typeof req.always === "function")
        req.always(cleanup);
      else if (p && typeof p.finally === "function")
        p.finally(cleanup);
      else
        setTimeout(cleanup, 0);
      this._profilePromise.set(id, p);
      return p;
    },
    _computeEligibleCareers() {
      return __async(this, null, function* () {
        let mainCareerId = String(this._mainCareerId() || "").trim();
        let mainCareerIdNum = parseInt(mainCareerId, 10) || 0;
        if (mainCareerIdNum <= 0) {
          for (let i = 0; i < 24 && mainCareerIdNum <= 0; i++) {
            yield new Promise((r) => setTimeout(r, 25));
            mainCareerId = String(this._mainCareerId() || "").trim();
            mainCareerIdNum = parseInt(mainCareerId, 10) || 0;
          }
        }
        mainCareerId = mainCareerIdNum > 0 ? String(mainCareerIdNum) : "";
        if (mainCareerIdNum > 0 && api_default2 && typeof api_default2.fetchProfile === "function") {
          try {
            const curId = String(api_default2.currentProfileId || "");
            const want = String(mainCareerIdNum);
            if (!api_default2.currentProfile || curId !== want) {
              yield Promise.resolve(api_default2.fetchProfile(want));
              api_default2.currentProfileId = want;
            }
          } catch (_) {
          }
        }
        const acquired = this._acquiredGiftSet();
        const key = [mainCareerId, ...Array.from(acquired).sort()].join("|");
        if (key === this._eligibleCacheKey && Array.isArray(this._eligibleCache)) {
          return this._eligibleCache.slice();
        }
        let list = yield api_default2.getList(false);
        let careers = Array.isArray(list) ? list : [];
        const listHasExt = careers.some((c) => c && (c.gift_id_1 != null || c.gift_id_2 != null || c.gift_id_3 != null || c.skill_one != null || c.skill_two != null || c.skill_three != null));
        if (!listHasExt) {
          list = yield api_default2.getList(true);
          careers = Array.isArray(list) ? list : [];
        }
        const results = [];
        for (const c of careers) {
          const id = String((c == null ? void 0 : c.id) || "");
          const name = String((c == null ? void 0 : c.name) || "");
          if (!id || !name)
            continue;
          if (mainCareerIdNum > 0 && String(mainCareerIdNum) === id)
            continue;
          const g1 = (c == null ? void 0 : c.gift_id_1) != null ? String(c.gift_id_1) : "";
          const g2 = (c == null ? void 0 : c.gift_id_2) != null ? String(c.gift_id_2) : "";
          const g3 = (c == null ? void 0 : c.gift_id_3) != null ? String(c.gift_id_3) : "";
          const listHasGiftIds = Boolean(g1 || g2 || g3);
          let required = [];
          let skills = [];
          if (listHasGiftIds) {
            required = [g1, g2, g3].filter((v) => v && v !== "0");
            const s1 = (c == null ? void 0 : c.skill_one) != null ? String(c.skill_one) : "";
            const s2 = (c == null ? void 0 : c.skill_two) != null ? String(c.skill_two) : "";
            const s3 = (c == null ? void 0 : c.skill_three) != null ? String(c.skill_three) : "";
            skills = [s1, s2, s3].filter((v) => v && v !== "0");
          } else {
            const prof = yield this._getCareerProfile(id);
            if (!prof)
              continue;
            required = [prof.gift_id_1, prof.gift_id_2, prof.gift_id_3].map((v) => v ? String(v) : "").filter(Boolean);
            if (Array.isArray(prof.skills) && prof.skills.length)
              skills = prof.skills.slice();
            else {
              const s1 = prof.skill_one != null ? String(prof.skill_one) : "";
              const s2 = prof.skill_two != null ? String(prof.skill_two) : "";
              const s3 = prof.skill_three != null ? String(prof.skill_three) : "";
              skills = [s1, s2, s3].filter((v) => v && v !== "0");
            }
          }
          const eligible = required.every((gid) => acquired.has(String(gid)));
          if (!eligible)
            continue;
          results.push({ id, name, skills });
        }
        const eligibleSorted = results.sort((a, b) => String(a.name).localeCompare(String(b.name)));
        this._eligibleCacheKey = key;
        this._eligibleCache = eligibleSorted.slice();
        return eligibleSorted;
      });
    },
    _setSelection(slotIndex, careerId) {
      return __async(this, null, function* () {
        const idx = parseInt(slotIndex, 10) || 0;
        const unlocks = this._countExtraCareerUnlocks();
        const maxSlots = Math.max(0, unlocks);
        let list = this._readExtraCareersFromData();
        while (list.length < maxSlots)
          list.push({ id: "", name: "", skills: [] });
        list = list.slice(0, maxSlots);
        if (!careerId) {
          list[idx] = { id: "", name: "", skills: [] };
          list = list.filter((x) => x && x.id);
          this._writeExtraCareersToData(list);
          this._emitChanged();
          return;
        }
        let name = "";
        try {
          const eligible = yield this._computeEligibleCareers();
          const hit = eligible.find((x) => String(x.id) === String(careerId));
          if (hit)
            name = hit.name || "";
        } catch (_) {
        }
        const prof = yield this._getCareerProfile(careerId);
        const skills = Array.isArray(prof == null ? void 0 : prof.skills) ? prof.skills.slice() : [];
        list[idx] = { id: String(careerId), name: String(name || (prof == null ? void 0 : prof.careerName) || ""), skills };
        const seen = /* @__PURE__ */ new Set();
        list = list.filter((x) => {
          if (!x || !x.id)
            return false;
          const id = String(x.id);
          if (seen.has(id))
            return false;
          seen.add(id);
          return true;
        });
        this._writeExtraCareersToData(list);
        this._emitChanged();
      });
    },
    _eligibleSelectedIdSet(selectedCareers) {
      return new Set((selectedCareers || []).map((x) => String((x == null ? void 0 : x.id) || "")).filter(Boolean));
    },
    _getBoostTargetForSlot(slot) {
      var _a;
      const d = ((_a = formBuilder_default) == null ? void 0 : _a._data) || {};
      const v = d[boostKey(slot)];
      if (v != null && String(v).trim())
        return String(v).trim();
      const legacy = d[BOOST_TARGET_KEY_LEGACY];
      if (legacy != null && String(legacy).trim())
        return String(legacy).trim();
      return "main";
    },
    _setBoostTargetForSlot(slot, targetValue) {
      var _a;
      const v = String(targetValue || "main") || "main";
      const __cgr = (_a = formBuilder_default._data) == null ? void 0 : _a.career_gift_replacements;
      formBuilder_default._data = __spreadProps(__spreadValues({}, formBuilder_default._data || {}), {
        [boostKey(slot)]: v,
        career_gift_replacements: __cgr || {}
      });
      try {
        delete formBuilder_default._data[BOOST_TARGET_KEY_LEGACY];
      } catch (_) {
      }
      document.dispatchEvent(new CustomEvent("cg:traits:changed", { detail: { [boostKey(slot)]: v } }));
      if ($8)
        $8(document).trigger("cg:traits:changed", [{ [boostKey(slot)]: v }]);
    },
    _findFreeChoiceSelectsFor223() {
      const out = [];
      for (let i = 0; i <= 2; i++) {
        const el = document.getElementById(`cg-free-choice-${i}`);
        if (el && String(el.value || "") === INC_TRAIT_CAREER_GIFT_ID)
          out.push({ slot: i, el });
      }
      return out;
    },
    _ensureBoostTargetInlineStyles() {
      try {
        if (document.getElementById(BOOST_TARGET_STYLE_ID))
          return;
        const st = document.createElement("style");
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
      } catch (_) {
      }
    },
    _removeBoostTargetInlineUIForSlot(slot) {
      const sid = boostSelectId(slot);
      const sel = document.getElementById(sid);
      let wrap = null;
      if (sel) {
        try {
          wrap = sel.closest(".cg-free-slot") || sel.parentElement;
        } catch (_) {
          wrap = sel.parentElement;
        }
        try {
          sel.remove();
        } catch (_) {
        }
      }
      if (wrap) {
        const lbl = wrap.querySelector(`.cg-inc-trait-career-target-label[data-slot="${slot}"]`);
        if (lbl) {
          try {
            lbl.remove();
          } catch (_) {
          }
        }
        if (!wrap.querySelector('select[id^="cg-inc-trait-career-target-"]')) {
          try {
            wrap.classList.remove("cg-free-slot--boosttarget");
          } catch (_) {
          }
        }
      }
    },
    _cleanupBoostTargetData(activeSlotsSet) {
      var _a;
      const d = ((_a = formBuilder_default) == null ? void 0 : _a._data) || {};
      for (let i = 0; i <= 2; i++) {
        if (!activeSlotsSet.has(i)) {
          try {
            delete d[boostKey(i)];
          } catch (_) {
          }
        }
      }
      try {
        delete d[BOOST_TARGET_KEY_LEGACY];
      } catch (_) {
      }
    },
    _ensureBoostTargetInlineUI(selectedCareers) {
      var _a;
      const boostsTotal = this._countGiftInAcquired(INC_TRAIT_CAREER_GIFT_ID);
      const selected = Array.isArray(selectedCareers) ? selectedCareers.filter((x) => x && x.id) : [];
      const targets = this._findFreeChoiceSelectsFor223();
      const shouldShow = boostsTotal > 0 && selected.length > 0 && targets.length > 0;
      const activeSlots = new Set(targets.map((t) => t.slot));
      for (let i = 0; i <= 2; i++) {
        if (!activeSlots.has(i))
          this._removeBoostTargetInlineUIForSlot(i);
      }
      this._cleanupBoostTargetData(activeSlots);
      if (!shouldShow)
        return;
      this._ensureBoostTargetInlineStyles();
      const selectedIds = this._eligibleSelectedIdSet(selected);
      try {
        const d = ((_a = formBuilder_default) == null ? void 0 : _a._data) || {};
        const legacy = d[BOOST_TARGET_KEY_LEGACY] != null ? String(d[BOOST_TARGET_KEY_LEGACY]).trim() : "";
        if (legacy) {
          targets.forEach(({ slot }) => {
            if (d[boostKey(slot)] == null || String(d[boostKey(slot)]).trim() === "") {
              d[boostKey(slot)] = legacy;
            }
          });
          delete d[BOOST_TARGET_KEY_LEGACY];
        }
      } catch (_) {
      }
      const mainLabel = this._mainCareerName();
      const options = [{ value: "main", label: `Main Career \u2014 ${mainLabel}` }].concat(selected.map((x) => ({ value: String(x.id), label: `Extra Career \u2014 ${String(x.name || "Extra Career")}` })));
      targets.forEach(({ slot, el }) => {
        let wrap = null;
        try {
          wrap = el.closest(".cg-free-slot");
        } catch (_) {
          wrap = null;
        }
        if (!wrap)
          wrap = el.parentElement;
        if (!wrap)
          return;
        try {
          wrap.classList.add("cg-free-slot--boosttarget");
        } catch (_) {
        }
        let lbl = wrap.querySelector(`.cg-inc-trait-career-target-label[data-slot="${slot}"]`);
        if (!lbl) {
          lbl = document.createElement("span");
          lbl.className = "cg-inc-trait-career-target-label";
          lbl.setAttribute("data-slot", String(slot));
          lbl.textContent = "Applies to:";
          try {
            el.insertAdjacentElement("afterend", lbl);
          } catch (_) {
            wrap.appendChild(lbl);
          }
        }
        const sid = boostSelectId(slot);
        let sel = document.getElementById(sid);
        if (!sel) {
          sel = document.createElement("select");
          sel.id = sid;
          sel.className = "cg-profile-select cg-inc-trait-career-target-select";
          sel.setAttribute("data-slot", String(slot));
        }
        if (sel.parentElement !== wrap) {
          try {
            wrap.appendChild(sel);
          } catch (_) {
          }
        }
        try {
          if (lbl.nextSibling !== sel)
            lbl.insertAdjacentElement("afterend", sel);
        } catch (_) {
        }
        sel.innerHTML = "";
        options.forEach((o) => sel.appendChild(new Option(o.label, o.value)));
        let v = this._getBoostTargetForSlot(slot);
        if (v !== "main" && !selectedIds.has(String(v)))
          v = "main";
        const curSaved = this._getBoostTargetForSlot(slot);
        if (String(curSaved) !== String(v))
          this._setBoostTargetForSlot(slot, v);
        sel.value = String(v);
      });
    },
    _computeCareerBoostCounts(selectedCareers) {
      const total = this._countGiftInAcquired(INC_TRAIT_CAREER_GIFT_ID);
      const counts = /* @__PURE__ */ Object.create(null);
      counts.main = 0;
      const selected = Array.isArray(selectedCareers) ? selectedCareers.filter((x) => x && x.id) : [];
      const selectedIds = this._eligibleSelectedIdSet(selected);
      const targets = this._findFreeChoiceSelectsFor223();
      let assigned = 0;
      for (const t of targets) {
        if (assigned >= total)
          break;
        const slot = t.slot;
        let v = this._getBoostTargetForSlot(slot);
        if (v !== "main" && !selectedIds.has(String(v)))
          v = "main";
        counts[v] = (counts[v] || 0) + 1;
        assigned++;
      }
      const remaining = Math.max(0, total - assigned);
      if (remaining)
        counts.main = (counts.main || 0) + remaining;
      return counts;
    },
    _careerTraitDisplayWithCounts(targetKey, boostCounts) {
      const base = this._careerTraitBaseDie();
      const count = Math.max(0, parseInt(boostCounts && boostCounts[targetKey] || 0, 10) || 0);
      if (count <= 0)
        return { base, adjusted: base, boosts: 0, suffix: "" };
      const adjusted = stepDie(base, count);
      const suffix = count === 1 ? "Increased by gift" : `Increased by gift \xD7${count}`;
      return { base, adjusted, boosts: count, suffix };
    },
    _renderTraitsTabExtraCareerDice(unlocks, selected, baseTrait, boostCounts) {
      var _a;
      const traitsWrap = this._getTraitsWrap();
      if (!traitsWrap)
        return;
      if (!unlocks) {
        traitsWrap.innerHTML = "";
        return;
      }
      const rows = [];
      for (let i = 0; i < unlocks; i++) {
        const curId = ((_a = selected[i]) == null ? void 0 : _a.id) ? String(selected[i].id) : "";
        const name = curId ? String(selected[i].name || `Extra Career ${i + 1}`) : `Extra Career ${i + 1}`;
        const traitInfo = curId ? this._careerTraitDisplayWithCounts(String(curId), boostCounts) : { adjusted: baseTrait, suffix: "" };
        const shownDie = traitInfo.adjusted || baseTrait || "d4";
        const note = curId && traitInfo.suffix ? traitInfo.suffix : "";
        rows.push(`
        <div class="cg-extra-career-trait-row">
          <span class="cg-extra-career-trait-name">${escapeHtml2(name)}</span>
          <span class="cg-trait-badge cg-trait-badge--sm" aria-label="Career trait die">${escapeHtml2(shownDie)}</span>
        </div>
        ${note ? `<div class="trait-adjusted">${escapeHtml2(note)}</div>` : `<div class="trait-adjusted"></div>`}
      `);
      }
      traitsWrap.innerHTML = `
      <h4>Extra Careers</h4>
      ${rows.join("")}
    `;
    },
    render() {
      return __async(this, null, function* () {
        var _a;
        const wrap = this._getWrap();
        const traitsWrap = this._getTraitsWrap();
        if (!wrap && traitsWrap) {
          traitsWrap.innerHTML = "";
          for (let i = 0; i <= 2; i++)
            this._removeBoostTargetInlineUIForSlot(i);
          return;
        }
        if (!wrap)
          return;
        const unlocks = this._countExtraCareerUnlocks();
        if (!unlocks) {
          wrap.innerHTML = "";
          if (traitsWrap)
            traitsWrap.innerHTML = "";
          this._writeExtraCareersToData([]);
          this._emitChanged();
          for (let i = 0; i <= 2; i++)
            this._removeBoostTargetInlineUIForSlot(i);
          return;
        }
        wrap.innerHTML = `<div class="cg-extra-careers-loading">Loading eligible extra careers\u2026</div>`;
        let eligible = [];
        try {
          eligible = yield this._computeEligibleCareers();
        } catch (e) {
          WARN("eligible compute failed", e);
          eligible = [];
        }
        let selected = this._readExtraCareersFromData();
        while (selected.length < unlocks)
          selected.push({ id: "", name: "", skills: [] });
        selected = selected.slice(0, unlocks);
        const eligibleIds = new Set(eligible.map((x) => String(x.id)));
        let changed = false;
        selected = selected.map((x) => {
          if (!x || !x.id)
            return { id: "", name: "", skills: [] };
          if (!eligibleIds.has(String(x.id))) {
            changed = true;
            return { id: "", name: "", skills: [] };
          }
          return x;
        });
        if (changed) {
          this._writeExtraCareersToData(selected.filter((x) => x.id));
          this._emitChanged();
        }
        const selectedWithId = selected.filter((x) => x && x.id);
        const boostCounts = this._computeCareerBoostCounts(selectedWithId);
        const baseTrait = this._careerTraitBaseDie();
        this._renderTraitsTabExtraCareerDice(unlocks, selected, baseTrait, boostCounts);
        const otherSelectedIds = (slot) => {
          const set = /* @__PURE__ */ new Set();
          selected.forEach((x, i) => {
            if (i === slot)
              return;
            if (x && x.id)
              set.add(String(x.id));
          });
          return set;
        };
        const blockHtml = [];
        for (let i = 0; i < unlocks; i++) {
          const cur = ((_a = selected[i]) == null ? void 0 : _a.id) ? String(selected[i].id) : "";
          const exclude = otherSelectedIds(i);
          const options = eligible.filter((c) => !exclude.has(String(c.id)) || String(c.id) === cur).map((c) => {
            const sel = String(c.id) === cur ? " selected" : "";
            return `<option value="${String(c.id)}"${sel}>${escapeHtml2(String(c.name))}</option>`;
          }).join("");
          const traitInfo = cur ? this._careerTraitDisplayWithCounts(String(cur), boostCounts) : { suffix: "" };
          const note = cur && traitInfo.suffix ? traitInfo.suffix : "";
          blockHtml.push(`
        <div class="cg-extra-career-block">
          <label for="cg-extra-career-${i}">Extra Career ${i + 1}</label>

          <div class="cg-trait-control cg-trait-control--profile cg-trait-control--extra">
            <select
              id="cg-extra-career-${i}"
              class="cg-profile-select cg-extra-career-select"
              data-index="${i}"
            >
              <option value="">\u2014 Select Career \u2014</option>
              ${options}
            </select>
          </div>

          <div class="trait-adjusted">${note ? escapeHtml2(note) : ""}</div>
        </div>
      `);
        }
        wrap.innerHTML = `${blockHtml.join("")}`;
        this._ensureBoostTargetInlineUI(selectedWithId);
        setTimeout(() => this._ensureBoostTargetInlineUI(selectedWithId), 0);
        this._hideProfileDiceBadges();
        const needsHydrate = selected.some((x) => x && x.id && (!Array.isArray(x.skills) || !x.skills.length));
        if (needsHydrate) {
          const hydrated = [];
          for (let i = 0; i < selected.length; i++) {
            const x = selected[i];
            if (!x || !x.id)
              continue;
            const prof = yield this._getCareerProfile(x.id);
            hydrated.push({
              id: String(x.id),
              name: String(x.name || (prof == null ? void 0 : prof.careerName) || ""),
              skills: Array.isArray(prof == null ? void 0 : prof.skills) ? prof.skills.slice() : []
            });
          }
          this._writeExtraCareersToData(hydrated);
          this._emitChanged();
        }
        LOG("rendered", unlocks, "slot(s). baseTrait:", baseTrait, "boostCounts:", boostCounts);
      });
    }
  };
  window.CG_ExtraCareers = ExtraCareers;
  var extra_default = ExtraCareers;

  // assets/js/src/core/career/index.js
  var CareerIndex = {
    _init: false,
    /**
     * One-time init: bind change events and populate the select if present.
     */
    init() {
      var _a, _b;
      if (this._init)
        return;
      this._init = true;
      bindCareerEvents();
      try {
        extra_default.init();
      } catch (_) {
      }
      const sel = document.querySelector("#cg-career");
      if (sel) {
        (_b = (_a = api_default2.populateSelect(sel)).catch) == null ? void 0 : _b.call(_a, () => {
        });
      }
    },
    /**
     * Refresh the career <select>. Will repopulate if empty or when force=true.
     * @param {{force?: boolean}} opts
     * @returns {Promise<void>|void}
     */
    refresh(opts = {}) {
      var _a, _b, _c, _d;
      const sel = document.querySelector("#cg-career");
      if (!sel)
        return;
      const force = !!opts.force || sel.options.length <= 1;
      const p = (_b = (_a = api_default2.populateSelect(sel, { force })).catch) == null ? void 0 : _b.call(_a, () => {
      });
      try {
        (_d = (_c = extra_default).render) == null ? void 0 : _d.call(_c);
      } catch (_) {
      }
      return p;
    }
  };
  var career_default = CareerIndex;

  // assets/js/src/core/main/builder-ui.js
  var $9 = window.jQuery;
  var isDirty = false;
  var SELECTORS = {
    // Include #cg-* as canonical IDs (current formBuilder renders these).
    species: ["#cg-species", 'select[name="species"]', 'select[data-cg="species"]', "select.cg-species"],
    career: ["#cg-career", 'select[name="career"]', 'select[data-cg="career"]', "select.cg-career"]
  };
  function first(selectorList, root = document) {
    for (let i = 0; i < selectorList.length; i++) {
      const el = root.querySelector(selectorList[i]);
      if (el)
        return el;
    }
    return null;
  }
  function waitForSelects(timeoutMs = 2e3) {
    return new Promise((resolve) => {
      const start = Date.now();
      (function tick() {
        const speciesEl = first(SELECTORS.species);
        const careerEl = first(SELECTORS.career);
        if (speciesEl && careerEl)
          return resolve({ speciesEl, careerEl });
        if (Date.now() - start > timeoutMs)
          return resolve({ speciesEl, careerEl });
        setTimeout(tick, 50);
      })();
    });
  }
  function ensureListsThenApply() {
    return __async(this, arguments, function* (record = {}) {
      var _a, _b, _c, _d;
      species_default.init();
      career_default.init();
      const { speciesEl, careerEl } = yield waitForSelects();
      if (speciesEl && speciesEl.options.length <= 1)
        species_default.refresh();
      if (careerEl && careerEl.options.length <= 1)
        career_default.refresh();
      if (speciesEl) {
        const want = (_b = (_a = record.species) != null ? _a : record.species_id) != null ? _b : "";
        if (want) {
          $9(speciesEl).val(String(want));
          if ($9(speciesEl).val() !== String(want)) {
            const byName = [...speciesEl.options].find((o) => o.textContent === String(want));
            if (byName)
              $9(speciesEl).val(byName.value);
          }
          $9(speciesEl).trigger("change");
        }
      }
      if (careerEl) {
        const want = (_d = (_c = record.career) != null ? _c : record.career_id) != null ? _d : "";
        if (want) {
          $9(careerEl).val(String(want));
          if ($9(careerEl).val() !== String(want)) {
            const byName = [...careerEl.options].find((o) => o.textContent === String(want));
            if (byName)
              $9(careerEl).val(byName.value);
          }
          $9(careerEl).trigger("change");
        }
      }
    });
  }
  function activateDefaultTab() {
    try {
      const $details = $9('#cg-modal .cg-tabs li[data-tab="tab-details"]');
      if ($details.length) {
        $details.trigger("click");
        return;
      }
      const $first = $9("#cg-modal .cg-tabs li").first();
      if ($first.length)
        $first.trigger("click");
    } catch (_) {
    }
  }
  function openBuilder({ isNew = false, payload = {} } = {}) {
    var _a, _b;
    console.log("[BuilderUI] openBuilder()", { isNew, payload });
    isDirty = false;
    $9("#cg-unsaved-confirm, #cg-unsaved-backdrop").hide().css("display", "none");
    try {
      (_b = (_a = formBuilder_default) == null ? void 0 : _a.init) == null ? void 0 : _b.call(_a, __spreadProps(__spreadValues({}, payload), { isNew }));
    } catch (e) {
      console.error("[BuilderUI] FormBuilderAPI.init error", e);
    }
    $9("#cg-modal-overlay, #cg-modal").removeClass("cg-hidden").addClass("cg-visible").css("display", "block");
    activateDefaultTab();
    ensureListsThenApply(payload).then(() => {
      document.dispatchEvent(new CustomEvent("cg:builder:opened", { detail: { isNew, payload } }));
    });
  }
  function closeBuilder() {
    $9("#cg-unsaved-backdrop").hide().css("display", "none");
    $9("#cg-unsaved-confirm").removeClass("cg-visible").addClass("cg-hidden").hide().css("display", "none");
    $9("#cg-modal, #cg-modal-overlay").removeClass("cg-visible").addClass("cg-hidden").fadeOut(200, () => {
      $9("#cg-form-container").empty();
      document.dispatchEvent(new CustomEvent("cg:builder:closed"));
    });
    isDirty = false;
  }
  function markDirty() {
    isDirty = true;
  }
  function markClean() {
    isDirty = false;
  }
  function getIsDirty() {
    return isDirty;
  }
  function showUnsaved() {
    if (!$9("#cg-unsaved-backdrop").length)
      $9('<div id="cg-unsaved-backdrop"></div>').appendTo("body");
    const $b = $9("#cg-unsaved-backdrop");
    const $p = $9("#cg-unsaved-confirm");
    if (!$p.parent().is("body"))
      $p.appendTo("body");
    $b.show().css("display", "block");
    $p.removeClass("cg-hidden").addClass("cg-visible").show().css("display", "block");
  }
  function hideUnsaved() {
    $9("#cg-unsaved-backdrop").hide().css("display", "none");
    $9("#cg-unsaved-confirm").removeClass("cg-visible").addClass("cg-hidden").hide().css("display", "none");
  }
  try {
    window.__CG_EVT__ = window.__CG_EVT__ || {};
    const EVT = window.__CG_EVT__;
    if (EVT.builderUICharacterLoaded) {
      document.removeEventListener("cg:character:loaded", EVT.builderUICharacterLoaded);
    }
    EVT.builderUICharacterLoaded = (ev) => {
      const record = (ev == null ? void 0 : ev.detail) || {};
      console.log("[BuilderUI] cg:character:loaded \u2192 rehydrate for record", record);
      ensureListsThenApply(record);
    };
    document.addEventListener("cg:character:loaded", EVT.builderUICharacterLoaded);
  } catch (e) {
    console.error("[BuilderUI] failed to bind idempotent cg:character:loaded listener", e);
  }
  var builder_ui_default = {
    open: openBuilder,
    openBuilder,
    close: closeBuilder,
    closeBuilder,
    showUnsaved,
    hideUnsaved,
    markDirty,
    markClean,
    getIsDirty
  };

  // assets/js/src/core/traits/events.js
  var $10 = window.jQuery;
  var _cgTraitsRefreshQueued = false;
  var _cgTraitsRefreshRunning = false;
  var _cgTraitsRefreshPending = false;
  function scheduleTraitsRefresh(reason = "auto") {
    if (_cgTraitsRefreshRunning) {
      _cgTraitsRefreshPending = true;
      return;
    }
    if (_cgTraitsRefreshQueued)
      return;
    _cgTraitsRefreshQueued = true;
    const run = () => {
      _cgTraitsRefreshQueued = false;
      _cgTraitsRefreshRunning = true;
      try {
        service_default.refreshAll();
      } catch (e) {
        console.error("[Traits] refreshAll failed", e);
      } finally {
        _cgTraitsRefreshRunning = false;
        if (_cgTraitsRefreshPending) {
          _cgTraitsRefreshPending = false;
          scheduleTraitsRefresh("pending");
        }
      }
    };
    if (typeof requestAnimationFrame === "function")
      requestAnimationFrame(run);
    else
      setTimeout(run, 0);
  }
  var bound = false;
  function onTraitsRefreshAll() {
    scheduleTraitsRefresh("event");
  }
  var events_default = {
    bind() {
      if (bound)
        return;
      bound = true;
      $10(function() {
        scheduleTraitsRefresh("init");
      });
      $10(document).off("change.cgtraits", ".cg-trait-select").on("change.cgtraits", ".cg-trait-select", () => {
        scheduleTraitsRefresh("trait-change");
      });
      const jqEvents = [
        "cg:free-gift:changed.cgtraits",
        "cg:species:changed.cgtraits",
        "cg:career:changed.cgtraits",
        "cg:traits:changed.cgtraits",
        "cg:extra-careers:changed.cgtraits"
      ].join(" ");
      $10(document).off(jqEvents).on(jqEvents, () => {
        scheduleTraitsRefresh("upstream");
      });
      const nativeEvents = [
        "cg:free-gift:changed",
        "cg:species:changed",
        "cg:career:changed",
        "cg:traits:changed",
        "cg:extra-careers:changed"
      ];
      nativeEvents.forEach((evt) => {
        try {
          window.__CG_EVT__ = window.__CG_EVT__ || {};
          const EVT = window.__CG_EVT__;
          const key = `traitsRefreshAll_${evt}`;
          if (EVT[key]) {
            try {
              document.removeEventListener(evt, EVT[key]);
            } catch (_) {
            }
          }
          EVT[key] = onTraitsRefreshAll;
          document.addEventListener(evt, EVT[key]);
        } catch (e) {
          try {
            document.addEventListener(evt, onTraitsRefreshAll);
          } catch (_) {
          }
        }
      });
    }
  };

  // assets/js/src/core/traits/index.js
  var traits_default = {
    init() {
      service_default.refreshAll();
      events_default.bind();
    },
    // Expose for other modules
    getBoostedDie: service_default.getBoostedDie.bind(service_default)
  };

  // assets/js/src/core/gifts/free-choices.js
  var $11 = window.jQuery;
  var dbg = () => !!window.CG_DEBUG_GIFTS;
  var log3 = (...a) => dbg() ? console.log("[FreeChoices]", ...a) : null;
  var warn3 = (...a) => dbg() ? console.warn("[FreeChoices]", ...a) : null;
  var err = (...a) => console.error("[FreeChoices]", ...a);
  var REQ_SUFFIXES = [
    "",
    "two",
    "three",
    "four",
    "five",
    "six",
    "seven",
    "eight",
    "nine",
    "ten",
    "eleven",
    "twelve",
    "thirteen",
    "fourteen",
    "fifteen",
    "sixteen",
    "seventeen",
    "eighteen",
    "nineteen"
  ];
  var ALWAYS_ACQUIRED_GIFT_IDS2 = ["242", "236"];
  function ajaxEnv5() {
    var _a, _b;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    const url = env.ajax_url || window.ajaxurl || ((_b = (_a = document.body) == null ? void 0 : _a.dataset) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php";
    const perAction = window.CG_NONCES && window.CG_NONCES.cg_get_free_gifts ? window.CG_NONCES.cg_get_free_gifts : null;
    const generic = env.nonce || env.security || env._ajax_nonce || window.CG_NONCE || null;
    return { url, nonce: perAction || generic };
  }
  function parseJsonMaybe2(res) {
    try {
      return typeof res === "string" ? JSON.parse(res) : res;
    } catch (_) {
      return res;
    }
  }
  function isTruthyBool(v) {
    if (v === true)
      return true;
    if (v === false)
      return false;
    if (v == null)
      return false;
    const s = String(v).trim().toLowerCase();
    return s === "1" || s === "true" || s === "yes" || s === "y" || s === "on";
  }
  function splitIds(val) {
    if (val == null)
      return [];
    if (Array.isArray(val))
      return val.flatMap(splitIds);
    const s = String(val).trim();
    if (!s)
      return [];
    return s.split(/[,\s]+/g).map((t) => t.trim()).filter(Boolean).map((t) => String(parseInt(t, 10)) === "NaN" ? "" : String(parseInt(t, 10))).filter(Boolean);
  }
  function extractRequires(rawGift) {
    const req = [];
    REQ_SUFFIXES.forEach((suf) => {
      const key = suf ? `ct_gifts_requires_${suf}` : "ct_gifts_requires";
      if (!Object.prototype.hasOwnProperty.call(rawGift, key))
        return;
      req.push(...splitIds(rawGift[key]));
    });
    return Array.from(new Set(req));
  }
  function normalizeGiftForState(g) {
    var _a, _b, _c, _d, _e, _f, _g, _h, _i, _j, _k;
    if (!g || typeof g !== "object")
      return null;
    const idRaw = (_e = (_d = (_c = (_b = (_a = g.id) != null ? _a : g.ct_id) != null ? _b : g.gift_id) != null ? _c : g.ct_gift_id) != null ? _d : g.ct_gifts_id) != null ? _e : null;
    if (idRaw == null || String(idRaw) === "")
      return null;
    const manifoldRaw = (_i = (_h = (_g = (_f = g.ct_gifts_manifold) != null ? _f : g.manifold) != null ? _g : g.manifold_count) != null ? _h : g.ct_manifold) != null ? _i : 1;
    const id = String(idRaw);
    return __spreadProps(__spreadValues({}, g), {
      id,
      ct_gifts_manifold: parseInt(manifoldRaw, 10) || 1,
      name: (g.name || g.ct_gifts_name || g.title || "").toString() || `Gift #${id}`,
      allows_multiple: isTruthyBool((_k = (_j = g.allows_multiple) != null ? _j : g.ct_gifts_allows_multiple) != null ? _k : 0),
      requires: extractRequires(g)
    });
  }
  function inDom(node) {
    return !!(node && document.contains(node));
  }
  var FreeChoices = {
    _inited: false,
    _mounted: false,
    _root: null,
    _host: null,
    _selects: [],
    _allGifts: [],
    _refreshInFlight: false,
    _lastRefreshAt: 0,
    _retryCount: 0,
    _maxRetries: 2,
    _mountTimer: null,
    _mountTries: 0,
    _maxMountTries: 80,
    // 20s
    _suppressEmit: false,
    _pendingFill: false,
    init() {
      if (this._inited)
        return;
      this._inited = true;
      window.CG_FormBuilderAPI = formBuilder_default;
      try {
        state_default.init();
      } catch (_) {
      }
      try {
        this._suppressEmit = true;
        const cur = this._readSelections();
        this._writeSelections(cur);
        this._suppressEmit = false;
      } catch (_) {
        this._suppressEmit = false;
      }
      document.addEventListener("cg:builder:opened", () => {
        this._ensureMounted();
        this.refresh({ force: false });
      });
      document.addEventListener("cg:builder:closed", () => {
        this._resetMount(true);
      });
      document.addEventListener("cg:builder:rendered", () => {
        this._ensureMounted();
        this._fillSelectsFiltered();
      });
      document.addEventListener("cg:character:loaded", () => {
        this._ensureMounted();
        this._fillSelectsFiltered();
      });
      document.addEventListener("cg:tab:changed", (e) => {
        var _a;
        const to = ((_a = e == null ? void 0 : e.detail) == null ? void 0 : _a.to) || "";
        if (to !== "tab-profile")
          return;
        this._ensureMounted();
        if (!this._mounted)
          return;
        if (this._pendingFill && Array.isArray(this._allGifts) && this._allGifts.length) {
          this._pendingFill = false;
          this._fillSelectsFiltered();
        } else if (!Array.isArray(this._allGifts) || !this._allGifts.length) {
          this.refresh({ force: true });
        }
      });
      if ($11) {
        $11(document).off("cg:species:changed.cgfree cg:career:changed.cgfree cg:free-gift:changed.cgfree").on("cg:species:changed.cgfree cg:career:changed.cgfree cg:free-gift:changed.cgfree", () => {
          this._fillSelectsFiltered();
        });
      }
      document.addEventListener("cg:free-gift:changed", () => this._fillSelectsFiltered());
      if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => this._ensureMounted());
      } else {
        this._ensureMounted();
      }
    },
    _clearMountTimer() {
      if (this._mountTimer) {
        clearInterval(this._mountTimer);
        this._mountTimer = null;
      }
    },
    _resetMount(clearTimer = false) {
      if (clearTimer)
        this._clearMountTimer();
      this._mounted = false;
      this._root = null;
      this._host = null;
      this._selects = [];
      this._mountTries = 0;
    },
    _ensureMounted() {
      if (this._mounted && (!inDom(this._root) || !inDom(this._host))) {
        warn3("mounted root was detached; remounting");
        this._resetMount(false);
      }
      if (this._mounted)
        return true;
      const ok = this._tryMount();
      if (ok)
        return true;
      if (this._mountTimer)
        return false;
      this._mountTries = 0;
      this._mountTimer = setInterval(() => {
        this._mountTries++;
        if (this._tryMount()) {
          this._clearMountTimer();
          if (Array.isArray(this._allGifts) && this._allGifts.length) {
            this._fillSelectsFiltered();
          } else {
            this.refresh({ force: false });
          }
          return;
        }
        if (this._mountTries >= this._maxMountTries) {
          this._clearMountTimer();
        }
      }, 250);
      return false;
    },
    _tryMount() {
      let root = document.querySelector("#cg-free-choices");
      if (!root) {
        const profilePanel = document.getElementById("tab-profile");
        const container = profilePanel || document.querySelector("#cg-form-container");
        if (!container)
          return false;
        let section = document.getElementById("cg-free-gifts");
        if (!section) {
          section = document.createElement("section");
          section.id = "cg-free-gifts";
          section.innerHTML = `<h3>Free Gifts (3)</h3><div class="cg-free-row"></div>`;
          container.appendChild(section);
        }
        root = section;
      }
      if (!root)
        return false;
      let row = root.querySelector(".cg-free-row");
      if (!row) {
        row = document.createElement("div");
        row.className = "cg-free-row";
        root.appendChild(row);
      } else {
        if (!row.classList.contains("cg-free-row"))
          row.classList.add("cg-free-row");
      }
      if (row.getAttribute("style"))
        row.removeAttribute("style");
      const ensureSelect = (slot) => {
        const id = `cg-free-choice-${slot}`;
        let sel = row.querySelector(`#${id}`);
        if (!sel) {
          sel = document.createElement("select");
          sel.id = id;
          sel.className = "cg-free-select";
          sel.setAttribute("data-slot", String(slot));
          row.appendChild(sel);
        } else {
          if (!sel.classList.contains("cg-free-select"))
            sel.classList.add("cg-free-select");
        }
        if (sel.getAttribute("style"))
          sel.removeAttribute("style");
        return sel;
      };
      const s0 = ensureSelect(0);
      const s1 = ensureSelect(1);
      const s2 = ensureSelect(2);
      this._root = root;
      this._host = row;
      this._selects = [s0, s1, s2];
      this._selects.forEach((sel) => {
        if ($11) {
          $11(sel).off("change.cgfree").on("change.cgfree", () => this._onSelectChange(sel));
        } else {
          sel.onchange = () => this._onSelectChange(sel);
        }
      });
      this._mounted = true;
      this._drawPlaceholders();
      return true;
    },
    _drawPlaceholders() {
      if (!this._mounted)
        return;
      const placeholders = [
        "\u2014 Select gift #1 \u2014",
        "\u2014 Select gift #2 \u2014",
        "\u2014 Select gift #3 \u2014"
      ];
      this._selects.forEach((sel, idx) => {
        sel.innerHTML = "";
        sel.appendChild(new Option(placeholders[idx], ""));
      });
    },
    _readSelections() {
      let src = null;
      try {
        if (formBuilder_default && formBuilder_default._data) {
          src = formBuilder_default._data.free_gifts || formBuilder_default._data.freeGifts || null;
        }
      } catch (_) {
      }
      if (!Array.isArray(src)) {
        try {
          if (Array.isArray(state_default.selected))
            src = state_default.selected;
        } catch (_) {
        }
      }
      if (!Array.isArray(src))
        src = this._selects.map((s) => s.value || "");
      const out = (Array.isArray(src) ? src : []).slice(0, 3).map((v) => v ? String(v) : "");
      while (out.length < 3)
        out.push("");
      return out;
    },
    _writeSelections(arrStr) {
      const normalized = (Array.isArray(arrStr) ? arrStr : []).slice(0, 3).map((v) => v ? String(v) : "");
      while (normalized.length < 3)
        normalized.push("");
      try {
        if (formBuilder_default && formBuilder_default._data) {
          formBuilder_default._data.free_gifts = normalized.slice();
          formBuilder_default._data.freeGifts = normalized.slice();
        }
      } catch (_) {
      }
      try {
        state_default.setSelected(normalized);
      } catch (_) {
      }
      if (this._suppressEmit)
        return;
      document.dispatchEvent(new CustomEvent("cg:free-gift:changed", {
        detail: { free_gifts: normalized }
      }));
      if ($11)
        $11(document).trigger("cg:free-gift:changed", [{ free_gifts: normalized }]);
    },
    _onSelectChange(sel) {
      const slot = parseInt(sel.getAttribute("data-slot") || "0", 10);
      const cur = this._readSelections();
      cur[slot] = sel.value || "";
      this._writeSelections(cur);
      this._fillSelectsFiltered();
    },
    _acquiredGiftIdSet(selections) {
      var _a, _b;
      const set = /* @__PURE__ */ new Set();
      ALWAYS_ACQUIRED_GIFT_IDS2.forEach((id) => set.add(String(id)));
      (selections || []).forEach((id) => {
        if (id)
          set.add(String(id));
      });
      const sp = ((_a = api_default) == null ? void 0 : _a.currentProfile) || null;
      if (sp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
          if (sp[k] != null && String(sp[k]))
            set.add(String(sp[k]));
        });
      }
      const cp = ((_b = api_default2) == null ? void 0 : _b.currentProfile) || null;
      if (cp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((k) => {
          if (cp[k] != null && String(cp[k]))
            set.add(String(cp[k]));
        });
      }
      return set;
    },
    _isEligibleGift(g, acquiredSet) {
      const req = Array.isArray(g.requires) ? g.requires : [];
      if (!req.length)
        return true;
      return req.every((id) => acquiredSet.has(String(id)));
    },
    _optionsForSlot(slot, selections, acquiredSet) {
      const cur = selections[slot] || "";
      const otherSelected = new Set(
        selections.map((id, i) => i === slot ? "" : id || "").filter(Boolean)
      );
      return (this._allGifts || []).filter((g) => {
        if (!g || !g.id)
          return false;
        if (!this._isEligibleGift(g, acquiredSet))
          return false;
        if (!g.allows_multiple && otherSelected.has(String(g.id)) && String(g.id) !== String(cur)) {
          return false;
        }
        return true;
      }).map((g) => ({ id: String(g.id), name: String(g.name || `Gift #${g.id}`) }));
    },
    _fillSelectsFiltered() {
      if (!this._mounted)
        return;
      if (!Array.isArray(this._allGifts) || !this._allGifts.length) {
        this._drawPlaceholders();
        return;
      }
      const placeholders = [
        "\u2014 Select gift #1 \u2014",
        "\u2014 Select gift #2 \u2014",
        "\u2014 Select gift #3 \u2014"
      ];
      let selections = this._readSelections().slice();
      for (let pass = 0; pass < 3; pass++) {
        let changed = false;
        const acquired = this._acquiredGiftIdSet(selections);
        for (let slot = 0; slot < this._selects.length; slot++) {
          const opts = this._optionsForSlot(slot, selections, acquired);
          const cur = selections[slot] || "";
          const isCurValid = !cur || opts.some((o) => o.id === String(cur));
          if (cur && !isCurValid) {
            log3("Clearing invalid selection", { slot, cur });
            selections[slot] = "";
            changed = true;
          }
        }
        if (!changed)
          break;
      }
      const before = this._readSelections().slice();
      if (selections.join("|") !== before.join("|")) {
        this._suppressEmit = true;
        this._writeSelections(selections);
        this._suppressEmit = false;
      }
      const acquiredFinal = this._acquiredGiftIdSet(selections);
      const fillSelect = (sel, options, placeholder, prior) => {
        sel.innerHTML = "";
        sel.appendChild(new Option(placeholder, ""));
        options.forEach((opt) => sel.appendChild(new Option(opt.name, opt.id)));
        if (prior && options.some((o) => o.id === String(prior)))
          sel.value = String(prior);
        else
          sel.value = "";
      };
      this._selects.forEach((sel, idx) => {
        const options = this._optionsForSlot(idx, selections, acquiredFinal);
        fillSelect(sel, options, placeholders[idx], selections[idx]);
      });
      const nowSel = this._selects.map((s) => s.value || "");
      if (nowSel.join("|") !== selections.join("|")) {
        this._suppressEmit = true;
        this._writeSelections(nowSel);
        this._suppressEmit = false;
      }
    },
    refresh({ force = false } = {}) {
      this._ensureMounted();
      const now = Date.now();
      if (!force && now - this._lastRefreshAt < 3e3)
        return;
      if (this._refreshInFlight)
        return;
      this._refreshInFlight = true;
      const { url, nonce } = ajaxEnv5();
      const payload = { action: "cg_get_free_gifts" };
      if (nonce) {
        payload.security = nonce;
        payload.nonce = nonce;
        payload._ajax_nonce = nonce;
      }
      const onDone = (res) => {
        this._refreshInFlight = false;
        this._lastRefreshAt = Date.now();
        const json = parseJsonMaybe2(res);
        if (!json || json.success !== true || !Array.isArray(json.data)) {
          warn3("Unexpected response:", json);
          if (this._mounted)
            this._drawPlaceholders();
          this._maybeRetry();
          return;
        }
        const giftRows = json.data.map(normalizeGiftForState).filter(Boolean);
        this._allGifts = giftRows;
        try {
          state_default.setList(giftRows);
        } catch (_) {
        }
        this._retryCount = 0;
        if (this._mounted)
          this._fillSelectsFiltered();
        else
          this._pendingFill = true;
      };
      const onFail = (status, errorText, responseText) => {
        this._refreshInFlight = false;
        this._lastRefreshAt = Date.now();
        err("AJAX failed", status, errorText, responseText || "");
        if (this._mounted)
          this._drawPlaceholders();
        this._maybeRetry();
      };
      if ($11 && $11.post) {
        $11.post(url, payload).done(onDone).fail((xhr, status, e) => onFail(status, e, xhr == null ? void 0 : xhr.responseText));
      } else {
        const body = new URLSearchParams(payload).toString();
        fetch(url, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
          body,
          credentials: "same-origin"
        }).then((r) => r.text().then((t) => ({ status: r.status, text: t }))).then(({ status, text }) => {
          let j = null;
          try {
            j = JSON.parse(text);
          } catch (_) {
          }
          if (status >= 200 && status < 300)
            onDone(j);
          else
            onFail(status, "http_error", text);
        }).catch((e) => onFail("fetch_error", (e == null ? void 0 : e.message) || String(e), ""));
      }
    },
    _maybeRetry() {
      if (this._retryCount >= this._maxRetries)
        return;
      this._retryCount++;
      const delay = 600 * this._retryCount;
      setTimeout(() => {
        if (!this._allGifts || !this._allGifts.length) {
          this.refresh({ force: true });
        }
      }, delay);
    }
  };
  window.CG_FreeChoices = FreeChoices;
  var free_choices_default = FreeChoices;

  // assets/js/src/core/gifts/index.js
  function init() {
    try {
      state_default.init();
    } catch (_) {
    }
    try {
      free_choices_default.init();
    } catch (_) {
    }
    try {
      free_choices_default.refresh({ force: false });
    } catch (_) {
    }
  }
  function refresh(opts = {}) {
    try {
      free_choices_default.refresh(opts);
    } catch (_) {
    }
  }
  function getSelected() {
    return Array.isArray(state_default.selected) ? state_default.selected : [];
  }
  var GiftsAPI = { init, refresh, getSelected };
  var gifts_default = GiftsAPI;

  // assets/js/src/core/skills/render.js
  var $12 = window.jQuery;
  var MARK_DIE = {
    1: "d4",
    2: "d6",
    3: "d8"
  };
  function isSkillsTabActive() {
    try {
      const li = document.querySelector("#cg-modal .cg-tabs li.active");
      const tab = li ? li.getAttribute("data-tab") || "" : "";
      if (tab === "tab-skills")
        return true;
      const panel = document.getElementById("tab-skills");
      if (panel && panel.classList.contains("active"))
        return true;
    } catch (_) {
    }
    return false;
  }
  function pickFirst(obj, keys) {
    for (const k of keys) {
      if (obj && Object.prototype.hasOwnProperty.call(obj, k) && obj[k] != null && obj[k] !== "") {
        return obj[k];
      }
    }
    return null;
  }
  function extractSkillTripletFromAny(input) {
    const asStr = (v) => v == null ? "" : String(v);
    if (Array.isArray(input)) {
      const ids = input.map((x) => {
        var _a, _b, _c;
        return typeof x === "object" ? (_c = (_b = (_a = x.id) != null ? _a : x.skill_id) != null ? _b : x.value) != null ? _c : null : x;
      }).filter((v) => v != null).slice(0, 3).map(asStr);
      while (ids.length < 3)
        ids.push("");
      return ids;
    }
    if (input && typeof input === "object") {
      if (input.skills != null)
        return extractSkillTripletFromAny(input.skills);
      if (input.career_skills != null)
        return extractSkillTripletFromAny(input.career_skills);
      if (input.species_skills != null)
        return extractSkillTripletFromAny(input.species_skills);
      if (input.data != null)
        return extractSkillTripletFromAny(input.data);
      const id1 = pickFirst(input, [
        "skill_one",
        "skill1",
        "skill_id_1",
        "skill_one_id",
        "career_skill_1",
        "career_skill_one"
      ]);
      const id2 = pickFirst(input, [
        "skill_two",
        "skill2",
        "skill_id_2",
        "skill_two_id",
        "career_skill_2",
        "career_skill_two"
      ]);
      const id3 = pickFirst(input, [
        "skill_three",
        "skill3",
        "skill_id_3",
        "skill_three_id",
        "career_skill_3",
        "career_skill_three"
      ]);
      return [asStr(id1), asStr(id2), asStr(id3)];
    }
    return ["", "", ""];
  }
  function speciesNameOf(sp) {
    return (sp == null ? void 0 : sp.speciesName) || (sp == null ? void 0 : sp.species_name) || (sp == null ? void 0 : sp.name) || "";
  }
  function careerNameOf(cp) {
    return (cp == null ? void 0 : cp.careerName) || (cp == null ? void 0 : cp.career_name) || (cp == null ? void 0 : cp.name) || "";
  }
  function parseExtraCareersFromData(d) {
    if (!d)
      return [];
    if (Array.isArray(d.extraCareers))
      return d.extraCareers;
    if (typeof d.extra_careers === "string" && d.extra_careers.trim()) {
      try {
        const arr = JSON.parse(d.extra_careers);
        if (Array.isArray(arr))
          return arr;
      } catch (_) {
      }
    }
    return [];
  }
  var render_default = {
    render() {
      if (!isSkillsTabActive())
        return;
      let $table = $12("#tab-skills #skills-table");
      if (!$table.length)
        $table = $12("#skills-table");
      if (!$table.length)
        return;
      const data = formBuilder_default.getData();
      const skills = data.skillsList || window.CG_SKILLS_LIST || [];
      const species = api_default.currentProfile || {};
      const career = api_default2.currentProfile || {};
      const extraCareers = parseExtraCareersFromData(data).filter((x) => x && x.id).map((x) => ({
        id: String(x.id),
        name: String(x.name || ""),
        skills: Array.isArray(x.skills) ? x.skills.map(String) : []
      }));
      data.skillMarks = data.skillMarks || {};
      const MAX_MARKS = 13;
      const usedMarks = Object.values(data.skillMarks).reduce((sum, v) => sum + (parseInt(v, 10) || 0), 0);
      const marksRemain = Math.max(0, MAX_MARKS - usedMarks);
      $12("#tab-skills #marks-remaining, #marks-remaining").remove();
      $table.before(`
      <div id="marks-remaining" class="marks-remaining">
        Marks Remaining: <strong>${marksRemain}</strong>
      </div>
    `);
      const $thead = $12("<thead>");
      const $tr = $12("<tr>").append("<th>Skill</th>").append(`<th>${speciesNameOf(species) || ""}</th>`).append(`<th>${careerNameOf(career) || ""}</th>`);
      extraCareers.forEach((ec) => {
        $tr.append(`<th>${ec.name || "Extra Career"}</th>`);
      });
      $tr.append("<th>Marks</th>").append("<th>Dice Pool</th>").appendTo($thead);
      const spSkills = extractSkillTripletFromAny(species).map(String);
      const cpSkills = extractSkillTripletFromAny(career).map(String);
      const $tbody = $12("<tbody>");
      skills.forEach((skill) => {
        const id = String(skill.id);
        const name = skill.name;
        const spDie = spSkills.includes(id) ? "d4" : "";
        const cpDie = cpSkills.includes(id) ? "d6" : "";
        const extraDies = extraCareers.map((ec) => (ec.skills || []).includes(id) ? "d4" : "");
        const myMarks = parseInt(data.skillMarks[id], 10) || 0;
        let buttonsHtml = "";
        [1, 2, 3].forEach((n) => {
          const disabled = usedMarks >= MAX_MARKS && myMarks < n ? " disabled" : "";
          const active = myMarks >= n ? " active" : "";
          buttonsHtml += `<button
          type="button"
          class="skill-mark-btn${active}"
          data-skill-id="${id}"
          data-mark="${n}"
          ${disabled}
        ></button>`;
        });
        const markDie = myMarks ? MARK_DIE[myMarks] : "";
        const markDisplay = markDie || "\u2013";
        const poolDice = [spDie, cpDie].concat(extraDies).concat([markDie]).filter(Boolean);
        const poolStr = poolDice.length ? poolDice.join(" + ") : "\u2013";
        const $row = $12("<tr>").append(`<td>${name}</td>`).append(`<td>${spDie || "\u2013"}</td>`).append(`<td>${cpDie || "\u2013"}</td>`);
        extraDies.forEach((die) => {
          $row.append(`<td>${die || "\u2013"}</td>`);
        });
        $row.append(`<td>
                   <div class="marks-buttons">${buttonsHtml}</div>
                   <div class="marks-display">${markDisplay}</div>
                 </td>`).append(`<td>${poolStr}</td>`);
        $tbody.append($row);
      });
      $table.empty().append($thead).append($tbody);
    }
  };

  // assets/js/src/core/skills/events.js
  var $13 = window.jQuery;
  var _pendingRender = false;
  function isSkillsTabActive2() {
    try {
      return String($13("#cg-modal .cg-tabs li.active").data("tab") || "") === "tab-skills";
    } catch (_) {
      return false;
    }
  }
  function requestRender(_reason = "") {
    if (isSkillsTabActive2()) {
      _pendingRender = false;
      render_default.render();
    } else {
      _pendingRender = true;
    }
  }
  function onTabChanged(e) {
    const detail = e && e.detail || e && e.originalEvent && e.originalEvent.detail || {};
    const to = String(detail.to || "");
    if (to === "tab-skills") {
      _pendingRender = false;
      render_default.render();
    }
  }
  var events_default2 = {
    bind() {
      $13(document).off("cg:tab:changed.cgskills").on("cg:tab:changed.cgskills", onTabChanged);
      $13(document).off("change.cgskills", "#cg-species, #cg-career").on("change.cgskills", "#cg-species, #cg-career", () => {
        requestRender("species/career change");
      });
      $13(document).off("cg:extra-careers:changed.cgskills").on("cg:extra-careers:changed.cgskills", () => {
        requestRender("extra careers changed");
      });
      $13(document).off("click.cgskills", ".skill-mark-btn").on("click.cgskills", ".skill-mark-btn", function() {
        var _a;
        const skillId = String((_a = $13(this).data("skill-id")) != null ? _a : "");
        const markRaw = parseInt($13(this).data("mark"), 10);
        if (!skillId)
          return;
        const mark = Number.isFinite(markRaw) ? markRaw : 0;
        formBuilder_default._data = formBuilder_default._data || {};
        formBuilder_default._data.skillMarks = formBuilder_default._data.skillMarks && typeof formBuilder_default._data.skillMarks === "object" ? formBuilder_default._data.skillMarks : {};
        const current = parseInt(formBuilder_default._data.skillMarks[skillId], 10) || 0;
        let next = current === mark ? Math.max(0, mark - 1) : mark;
        if (next < 0)
          next = 0;
        if (next > 3)
          next = 3;
        formBuilder_default._data.skillMarks[skillId] = next;
        try {
          builder_ui_default.markDirty();
        } catch (_) {
        }
        render_default.render();
      });
      if (isSkillsTabActive2() || _pendingRender) {
        _pendingRender = false;
        render_default.render();
      }
    }
  };

  // assets/js/src/core/skills/index.js
  var $14 = window.jQuery;
  var _inited = false;
  function isSkillsTabActive3() {
    try {
      const li = document.querySelector("#cg-modal .cg-tabs li.active");
      const tab = li ? li.getAttribute("data-tab") || "" : "";
      if (tab === "tab-skills")
        return true;
      const panel = document.getElementById("tab-skills");
      if (panel && panel.classList.contains("active"))
        return true;
    } catch (_) {
    }
    return false;
  }
  var skills_default = {
    init() {
      if (!_inited) {
        _inited = true;
        if (window.CG_DEBUG_SKILLS) {
          console.log("[SkillsIndex] init \u2014 builder state:", formBuilder_default._data);
        }
        if (!Array.isArray(formBuilder_default._data.skillsList)) {
          formBuilder_default._data.skillsList = window.CG_SKILLS_LIST || [];
        }
        if (typeof formBuilder_default._data.skillMarks !== "object" || !formBuilder_default._data.skillMarks) {
          formBuilder_default._data.skillMarks = {};
        }
        events_default2.bind();
      }
      if (isSkillsTabActive3()) {
        render_default.render();
      }
    }
  };

  // assets/js/src/core/summary/utils.js
  function capitalize2(str) {
    if (typeof str !== "string")
      return "";
    const cleaned = str.replace(/[_\-]/g, " ");
    return cleaned.charAt(0).toUpperCase() + cleaned.slice(1);
  }

  // assets/js/src/core/summary/api.js
  var $15 = window.jQuery;
  var TRAITS3 = service_default.TRAITS;
  var MARK_DIE2 = { 1: "d4", 2: "d6", 3: "d8" };
  var SummaryAPI = {
    /**
     * Entry point when the Summary tab is shown.
     */
    init() {
      const data = formBuilder_default.getData();
      console.log("[SummaryAPI] init \u2014 builder state:", data);
      this.renderSummary(data);
      this.bindExportButton();
    },
    /**
     * Build and inject the full summary into #cg-summary-sheet.
     */
    renderSummary(data = {}) {
      const $sheet = $15("#cg-summary-sheet").empty();
      const name = data.name || "\u2014";
      const age = data.age || "\u2014";
      const gender = data.gender || "\u2014";
      const motto = data.motto || "\u2014";
      const goals = [1, 2, 3].map((i) => data[`goal${i}`] || "\u2014").filter((v) => v !== "\u2014").join(", ") || "\u2014";
      const description = data.description || "\u2014";
      const backstory = data.backstory || "\u2014";
      const species = api_default.currentProfile || {};
      const career = api_default2.currentProfile || {};
      const skills = window.CG_SKILLS_LIST || [];
      const marks = data.skillMarks || {};
      const battle = data.battle || [];
      let html = `
      <div class="summary-section summary-basic">
        <h2>${name}</h2>
        <p><strong>Age:</strong> ${age}</p>
        <p><strong>Gender:</strong> ${gender}</p>
        <p><strong>Motto:</strong> ${motto}</p>
      </div>

      <div class="summary-section summary-goals">
        <p><strong>Goals:</strong> ${goals}</p>
      </div>

      <div class="summary-section summary-description">
        <p><strong>Description:</strong> ${description}</p>
      </div>

      <div class="summary-section summary-backstory">
        <p><strong>Backstory:</strong> ${backstory}</p>
      </div>

      <div class="summary-section summary-species">
        <h3>Species: ${species.speciesName || "\u2014"}</h3>
        <ul>
    `;
      ["gift_1", "gift_2", "gift_3"].forEach((_, idx) => {
        const gift = species[`gift_${idx + 1}`];
        const mult = species[`manifold_${idx + 1}`] || 1;
        if (gift) {
          html += `<li><strong>Gift ${idx + 1}:</strong> ${gift} \xD7 ${mult}</li>`;
        }
      });
      html += `</ul></div>`;
      html += `
      <div class="summary-section summary-career">
        <h3>Career: ${career.careerName || "\u2014"}</h3>
        <ul>
    `;
      ["gift_1", "gift_2", "gift_3"].forEach((_, idx) => {
        const gift = career[`gift_${idx + 1}`];
        const mult = career[`manifold_${idx + 1}`] || 1;
        if (gift) {
          html += `<li><strong>Gift ${idx + 1}:</strong> ${gift} \xD7 ${mult}</li>`;
        }
      });
      html += `</ul></div>`;
      html += `
      <div class="summary-section summary-traits">
        <h3>Traits</h3>
        <ul>
    `;
      TRAITS3.forEach((key) => {
        let label = key.replace(/^trait_/, "");
        if (label === "species")
          label = "Species";
        else if (label === "career")
          label = "Career";
        else
          label = capitalize2(label);
        const base = data[key] || "\u2014";
        const boost = service_default.getBoostedDie(key);
        const display = boost ? `${base} \u2192 ${boost}` : base;
        html += `<li><strong>${label}:</strong> ${display}</li>`;
      });
      html += `</ul></div>`;
      html += `
      <div class="summary-section summary-skills">
        <h3>Skills</h3>
        <table class="cg-summary-skills">
          <thead><tr><th>Skill</th><th>Dice Pool</th></tr></thead>
          <tbody>
    `;
      const spIds = [species.skill_one, species.skill_two, species.skill_three].map(String);
      const cpIds = [career.skill_one, career.skill_two, career.skill_three].map(String);
      skills.forEach((skill) => {
        const id = String(skill.id);
        const sp = spIds.includes(id) ? "d4" : "";
        const cp = cpIds.includes(id) ? "d6" : "";
        const mk = MARK_DIE2[marks[id]] || "";
        const pool = [sp, cp, mk].filter(Boolean).join(" + ") || "\u2014";
        html += `<tr><td>${skill.name}</td><td>${pool}</td></tr>`;
      });
      html += `
          </tbody>
        </table>
      </div>
    `;
      if (battle.length) {
        html += `
        <div class="summary-section summary-battle">
          <h3>Battle</h3>
          <ul>
      `;
        battle.forEach((item) => {
          html += `<li><strong>${capitalize2(item.key)}:</strong> ${item.value}</li>`;
        });
        html += `</ul></div>`;
      }
      $sheet.html(html);
    },
    /**
     * Open a new window, inject the summary + CSS, and print it.
     */
    bindExportButton() {
      $15(document).off("click", "#cg-export-pdf").on("click", "#cg-export-pdf", (e) => {
        e.preventDefault();
        console.log("[SummaryAPI] Export to PDF clicked");
        const sheetHtml = document.getElementById("cg-summary-sheet").outerHTML;
        const cssLinks = Array.from(
          document.querySelectorAll('link[rel="stylesheet"]')
        ).map((link) => link.outerHTML).join("\n");
        const printWin = window.open("", "_blank", "width=800,height=600");
        printWin.document.open();
        printWin.document.write(`
          <!doctype html>
          <html>
            <head>
              <meta charset="utf-8">
              <title>Character Sheet</title>
              ${cssLinks}
              <style>
                @page { margin: 1cm; }
                body { margin:0; padding:0; }
              </style>
            </head>
            <body>
              ${sheetHtml}
            </body>
          </html>
        `);
        printWin.document.close();
        printWin.focus();
        setTimeout(() => {
          printWin.print();
          printWin.close();
        }, 300);
      });
    }
  };
  var api_default3 = SummaryAPI;

  // assets/js/src/core/summary/index.js
  var summary_default = {
    init() {
      api_default3.init();
    }
  };

  // assets/js/src/core/main/builder-refresh.js
  var $16 = window.jQuery;
  function refreshTab() {
    const tab = String($16("#cg-modal .cg-tabs li.active").data("tab") || "");
    switch (tab) {
      case "tab-details":
        break;
      case "tab-traits":
        species_default.init();
        career_default.init();
        setTimeout(() => {
          try {
            traits_default.init();
          } catch (e) {
            console.error("[builder-refresh] TraitsAPI.init failed", e);
          }
        }, 0);
        break;
      case "tab-gifts":
        gifts_default.init();
        break;
      case "tab-skills":
        skills_default.init();
        break;
      case "tab-trappings":
        break;
      case "tab-description":
        break;
      case "tab-summary":
        summary_default.init();
        break;
    }
  }

  // assets/js/src/core/main/builder-load.js
  var $17 = window.jQuery;
  var LOG2 = (...a) => console.log("[BuilderLoad]", ...a);
  var ERR = (...a) => console.error("[BuilderLoad]", ...a);
  var _inited2 = false;
  var _cacheRows = null;
  var _cacheAt = 0;
  var CACHE_MS = 3e4;
  var _inFlight = null;
  var _populating = false;
  var _suppressObserver = false;
  var _lastEnsureAt = 0;
  var ENSURE_THROTTLE_MS = 250;
  var _lastForceFetchAt = 0;
  var FORCE_THROTTLE_MS = 2e3;
  function _getListRequest() {
    if (formBuilder_default && typeof formBuilder_default.listCharacters === "function") {
      return formBuilder_default.listCharacters();
    }
    if (formBuilder_default && typeof formBuilder_default.fetchCharacters === "function") {
      return formBuilder_default.fetchCharacters();
    }
    return null;
  }
  function _parseRows(resp) {
    var _a;
    let parsed = resp;
    if (typeof resp === "string") {
      try {
        parsed = JSON.parse(resp);
      } catch (_) {
      }
    }
    if (parsed && typeof parsed === "object" && Object.prototype.hasOwnProperty.call(parsed, "success")) {
      if (parsed.success !== true) {
        ERR("AJAX returned success:false", parsed.data || parsed);
        return [];
      }
      if (Array.isArray(parsed.data))
        return parsed.data;
      if (Array.isArray((_a = parsed.data) == null ? void 0 : _a.data))
        return parsed.data.data;
      return [];
    }
    if (Array.isArray(parsed))
      return parsed;
    if (parsed && typeof parsed === "object" && Array.isArray(parsed.data))
      return parsed.data;
    return [];
  }
  function fetchCharacterList(force = false) {
    if (_inFlight)
      return _inFlight;
    const now = Date.now();
    if (force && now - _lastForceFetchAt < FORCE_THROTTLE_MS && _cacheRows) {
      return $17.Deferred().resolve(_cacheRows).promise();
    }
    if (!force && _cacheRows && now - _cacheAt < CACHE_MS) {
      return $17.Deferred().resolve(_cacheRows).promise();
    }
    LOG2("fetching characters via AJAX\u2026", force ? "(force)" : "");
    const req = _getListRequest();
    if (!req) {
      ERR("No listCharacters()/fetchCharacters() available on FormBuilderAPI");
      return $17.Deferred().resolve([]).promise();
    }
    const d = $17.Deferred();
    _inFlight = d.promise();
    if (force)
      _lastForceFetchAt = now;
    const finalize = (rows) => {
      _inFlight = null;
      d.resolve(rows);
    };
    const onOk = (resp) => {
      const rows = _parseRows(resp);
      _cacheRows = Array.isArray(rows) ? rows : [];
      _cacheAt = Date.now();
      LOG2(`fetched ${_cacheRows.length} records`);
      finalize(_cacheRows);
    };
    const onFail = (xhr, status, err2) => {
      ERR("character list request failed", status, err2, xhr == null ? void 0 : xhr.status, xhr == null ? void 0 : xhr.responseText);
      finalize([]);
    };
    if (typeof req.then === "function") {
      Promise.resolve(req).then(onOk).catch((err2) => {
        ERR("character list request failed", err2);
        finalize([]);
      });
    } else if (typeof req.done === "function" && typeof req.fail === "function") {
      req.done(onOk).fail(onFail);
    } else {
      ERR("character list request returned an unsupported value:", req);
      finalize([]);
    }
    return _inFlight;
  }
  function populateLoadSelect(rows) {
    const $sel = $17("#cg-splash-load-select");
    if (!$sel.length)
      return;
    _populating = true;
    _suppressObserver = true;
    try {
      const current = String($sel.val() || "");
      $sel.empty();
      $sel.append($17("<option>", { value: "", text: "-- Select a character --" }));
      (rows || []).forEach((r) => {
        var _a, _b;
        const id = String((_a = r == null ? void 0 : r.id) != null ? _a : "");
        const name = String((_b = r == null ? void 0 : r.name) != null ? _b : "");
        if (!id)
          return;
        $sel.append($17("<option>", { value: id, text: name || `#${id}` }));
      });
      if (current)
        $sel.val(current);
    } finally {
      setTimeout(() => {
        _suppressObserver = false;
        _populating = false;
      }, 0);
    }
  }
  function ensurePopulated({ force = false } = {}) {
    const now = Date.now();
    if (now - _lastEnsureAt < ENSURE_THROTTLE_MS)
      return;
    _lastEnsureAt = now;
    if (_populating || _suppressObserver)
      return;
    const $sel = $17("#cg-splash-load-select");
    if (!$sel.length)
      return;
    const count = $sel.find("option").length;
    if (count > 1)
      return;
    if (!force && _cacheRows && now - _cacheAt < CACHE_MS) {
      populateLoadSelect(_cacheRows);
      return;
    }
    LOG2("populating #cg-splash-load-select");
    fetchCharacterList(force).always(populateLoadSelect);
  }
  function _makeDebouncedEnsure() {
    let t = null;
    return () => {
      if (t)
        return;
      t = setTimeout(() => {
        t = null;
        ensurePopulated({ force: false });
      }, 50);
    };
  }
  function bindLoadEvents() {
    if (_inited2)
      return;
    _inited2 = true;
    LOG2("init");
    ensurePopulated({ force: false });
    try {
      window.__CG_EVT__ = window.__CG_EVT__ || {};
      if (window.__CG_EVT__.builderLoadObserver) {
        try {
          window.__CG_EVT__.builderLoadObserver.disconnect();
        } catch (_) {
        }
      }
      const debouncedEnsure = _makeDebouncedEnsure();
      window.__CG_EVT__.builderLoadObserver = new MutationObserver(() => {
        if (_suppressObserver)
          return;
        debouncedEnsure();
      });
      try {
        window.__CG_EVT__.builderLoadObserver.observe(document.body, { childList: true, subtree: true });
      } catch (_) {
      }
      if (window.__CG_EVT__.charactersRefreshLoad) {
        document.removeEventListener("cg:characters:refresh", window.__CG_EVT__.charactersRefreshLoad);
      }
      window.__CG_EVT__.charactersRefreshLoad = () => {
        LOG2("received cg:characters:refresh \u2192 repopulating load list");
        fetchCharacterList(true).always(populateLoadSelect);
      };
      document.addEventListener("cg:characters:refresh", window.__CG_EVT__.charactersRefreshLoad);
    } catch (e) {
      ERR("failed to bind load listeners", e);
    }
    try {
      window.CG_BuilderLoad = window.CG_BuilderLoad || {};
      window.CG_BuilderLoad.refresh = () => fetchCharacterList(true).always(populateLoadSelect);
      window.CG_BuilderLoad.ensure = () => ensurePopulated({ force: false });
      window.CG_BuilderLoad._debug = () => ({
        cacheAgeMs: _cacheAt ? Date.now() - _cacheAt : null,
        cacheCount: Array.isArray(_cacheRows) ? _cacheRows.length : null,
        inFlight: !!_inFlight,
        populating: _populating,
        suppressObserver: _suppressObserver
      });
    } catch (_) {
    }
  }

  // assets/js/src/core/main/builder-save.js
  var $18 = window.jQuery;
  var LOG3 = (...a) => console.log("[BuilderSave]", ...a);
  var WARN2 = (...a) => console.warn("[BuilderSave]", ...a);
  function setSaveButtonsDisabled(disabled) {
    try {
      $18("#cg-modal .cg-save-button").prop("disabled", !!disabled).toggleClass("cg-disabled", !!disabled);
    } catch (_) {
    }
  }
  function bindSaveEvents() {
    $18(document).off("click", ".cg-save-button");
    $18(document).off("click", ".cg-close-after-save");
    $18(document).on("click.cg", ".cg-save-button", function(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      const $btn = $18(this);
      const shouldClose = $btn.hasClass("cg-close-after-save");
      if (window.CG_SAVE_IN_FLIGHT) {
        WARN2("Save click ignored: CG_SAVE_IN_FLIGHT already true", { shouldClose });
        return;
      }
      setSaveButtonsDisabled(true);
      LOG3("\u25B6 Saving character...", { shouldClose });
      const req = formBuilder_default.save(!!shouldClose);
      if (req && typeof req.always === "function") {
        req.always(() => setSaveButtonsDisabled(false));
      } else {
        setTimeout(() => setSaveButtonsDisabled(false), 1500);
      }
    });
  }

  // assets/js/src/core/main/builder-events.js
  var $19 = window.jQuery;
  var LOG4 = (...a) => console.log("[BuilderEvents]", ...a);
  var SEL = {
    species: '#cg-species, select[name="species"], select[data-cg="species"], .cg-species',
    career: '#cg-career,  select[name="career"],  select[data-cg="career"],  .cg-career'
  };
  function firstSelect(selector) {
    const $sel = $19(selector);
    const $modalSel = $19("#cg-modal").find(selector);
    if ($modalSel.length)
      return $modalSel.first();
    return $sel.length ? $sel.first() : null;
  }
  function setSelectValue($sel, want) {
    if (!$sel || !$sel.length || !want)
      return false;
    const valRaw = String(want);
    const val = valRaw.trim();
    const dice = /* @__PURE__ */ new Set(["d4", "d6", "d8", "d10", "d12", "\u2013", "-"]);
    if (dice.has(val.toLowerCase()))
      return false;
    $sel.val(val);
    if (String($sel.val() || "") === val)
      return true;
    const $byText = $sel.find("option").filter(function() {
      return $19(this).text() === val;
    }).first();
    if ($byText.length) {
      $sel.val($byText.val());
      return true;
    }
    return false;
  }
  function emitTabChanged(fromTab, toTab) {
    try {
      if (!toTab)
        return;
      if (fromTab && String(fromTab) === String(toTab))
        return;
      document.dispatchEvent(new CustomEvent("cg:tab:changed", {
        detail: {
          from: fromTab ? String(fromTab) : "",
          to: String(toTab)
        }
      }));
    } catch (_) {
    }
  }
  function hydrateSelect(kind, { force = false, record = null } = {}) {
    const key = kind === "species" ? "species" : "career";
    const selector = kind === "species" ? SEL.species : SEL.career;
    const $sel = firstSelect(selector);
    if (!$sel) {
      LOG4(`no ${kind} select found`);
      return $19.Deferred().resolve().promise();
    }
    const el = $sel.get(0);
    const beforeVal = String($sel.val() || "").trim();
    try {
      const Index = kind === "species" ? species_default : career_default;
      if (Index == null ? void 0 : Index.refresh)
        Index.refresh();
      else if (Index == null ? void 0 : Index.render)
        Index.render();
    } catch (_) {
    }
    const ensureOptions = () => {
      if (el.options.length > 1 && !force)
        return $19.Deferred().resolve().promise();
      const API = kind === "species" ? api_default : api_default2;
      if (typeof (API == null ? void 0 : API.populateSelect) !== "function")
        return $19.Deferred().resolve().promise();
      return API.populateSelect(el, { force: !!force });
    };
    const doApply = () => {
      var _a, _b;
      if (!record)
        return;
      const wantRaw = (_b = (_a = record[key]) != null ? _a : record[`${key}_id`]) != null ? _b : "";
      const want = String(wantRaw || "").trim();
      if (!want)
        return;
      const applied = setSelectValue($sel, want);
      const afterVal = String($sel.val() || "").trim();
      if (afterVal) {
        if (applied || afterVal !== beforeVal) {
          $sel.trigger("change");
        }
      }
    };
    return $19.Deferred(function(dfr) {
      setTimeout(() => {
        ensureOptions().then(() => {
          doApply();
          dfr.resolve();
        }).catch(() => dfr.resolve());
      }, 0);
    }).promise();
  }
  function hydrateSpeciesAndCareer(opts = {}) {
    return $19.when(
      hydrateSelect("species", opts),
      hydrateSelect("career", opts)
    );
  }
  function onCharactersRefresh() {
    setTimeout(() => {
      hydrateSpeciesAndCareer({ force: true });
    }, 0);
  }
  function bindUIEvents() {
    var _a, _b;
    LOG4("bindUIEvents() called");
    try {
      (_b = (_a = gifts_default) == null ? void 0 : _a.init) == null ? void 0 : _b.call(_a);
    } catch (_) {
    }
    $19(document).off("input.cg change.cg", "#cg-modal input, #cg-modal select, #cg-modal textarea").on("input.cg change.cg", "#cg-modal input, #cg-modal select, #cg-modal textarea", function() {
      builder_ui_default.markDirty();
      const $el = $19(this);
      if ($el.hasClass("skill-marks")) {
        const skillId = $el.data("skill-id");
        const val = parseInt($el.val(), 10) || 0;
        formBuilder_default._data.skillMarks = formBuilder_default._data.skillMarks || {};
        formBuilder_default._data.skillMarks[skillId] = val;
        return;
      }
      const id = this.id;
      if (!id)
        return;
      const key = id.replace(/^cg-/, "");
      formBuilder_default._data[key] = $el.val();
    });
    $19(document).off("click.cg", "#cg-open-builder").on("click.cg", "#cg-open-builder", (e) => {
      e.preventDefault();
      $19("#cg-modal-splash").removeClass("cg-hidden").addClass("visible");
      try {
        const $sel = $19("#cg-splash-load-select");
        const optCount = $sel.length ? $sel.find("option").length : 0;
        if ($sel.length && optCount <= 1) {
          document.dispatchEvent(new CustomEvent("cg:characters:refresh", { detail: { source: "splash-open" } }));
        }
      } catch (_) {
      }
    });
    $19(document).off("click.cg", "#cg-new-splash").on("click.cg", "#cg-new-splash", (e) => {
      e.preventDefault();
      $19("#cg-modal-splash").removeClass("visible").addClass("cg-hidden");
      builder_ui_default.openBuilder({ isNew: true, payload: {} });
      formBuilder_default._data.skillMarks = {};
      formBuilder_default._data.species = "";
      formBuilder_default._data.career = "";
      if (window.CG_FreeChoicesState) {
        window.CG_FreeChoicesState.selected = ["", "", ""];
      }
    });
    $19(document).off("click.cg", "#cg-load-splash").on("click.cg", "#cg-load-splash", (e) => {
      e.preventDefault();
      const charId = $19("#cg-splash-load-select").val();
      if (!charId) {
        alert("Please select a character to load.");
        return;
      }
      formBuilder_default.fetchCharacter(charId).done((resp) => {
        console.log("\u{1F50E} [AJAX] raw cg_get_character response:", resp);
        const parsed = typeof resp === "string" ? JSON.parse(resp) : resp;
        console.log("\u{1F50D} [AJAX] parsed.data:", parsed == null ? void 0 : parsed.data);
        const record = (parsed == null ? void 0 : parsed.data) || parsed;
        if (!record || !record.id) {
          alert("Character could not be loaded.");
          return;
        }
        $19("#cg-modal-splash").removeClass("visible").addClass("cg-hidden");
        builder_ui_default.openBuilder({ isNew: false, payload: record });
        setTimeout(() => {
          hydrateSpeciesAndCareer({ force: true, record });
        }, 0);
      }).fail((xhr) => {
        console.error("Load failed:", (xhr == null ? void 0 : xhr.responseText) || xhr);
        alert("Could not load character. Check console for details.");
      });
    });
    bindLoadEvents();
    bindSaveEvents();
    $19(document).off("click.cg", "#cg-modal .cg-tabs li").on("click.cg", "#cg-modal .cg-tabs li", function(e) {
      e.preventDefault();
      const fromTab = $19("#cg-modal .cg-tabs li.active").data("tab");
      const tabName = $19(this).data("tab");
      $19("#cg-modal .cg-tabs li").removeClass("active");
      $19(this).addClass("active");
      $19(".tab-panel").removeClass("active");
      $19(`#${tabName}`).addClass("active");
      emitTabChanged(fromTab, tabName);
      refreshTab();
      setTimeout(() => {
        hydrateSpeciesAndCareer({ force: false });
      }, 0);
    });
    $19(document).off("click.cg", "#cg-modal-close").on("click.cg", "#cg-modal-close", (e) => {
      e.preventDefault();
      builder_ui_default.showUnsaved();
    });
    $19(document).off("click.cg", "#cg-modal-overlay").on("click.cg", "#cg-modal-overlay", function(e) {
      if (e.target !== this)
        return;
      builder_ui_default.showUnsaved();
    });
    $19(document).off("click.cg", "#unsaved-save").on("click.cg", "#unsaved-save", (e) => {
      e.preventDefault();
      console.log("[BuilderEvents] Prompt: SAVE & EXIT clicked");
      formBuilder_default.save(true);
    });
    $19(document).off("click.cg", "#unsaved-exit").on("click.cg", "#unsaved-exit", (e) => {
      e.preventDefault();
      builder_ui_default.closeBuilder();
    });
    $19(document).off("click.cg", "#unsaved-cancel").on("click.cg", "#unsaved-cancel", (e) => {
      e.preventDefault();
      builder_ui_default.hideUnsaved();
    });
    try {
      window.__CG_EVT__ = window.__CG_EVT__ || {};
      if (window.__CG_EVT__.charactersRefreshEvents) {
        document.removeEventListener("cg:characters:refresh", window.__CG_EVT__.charactersRefreshEvents);
      }
      window.__CG_EVT__.charactersRefreshEvents = onCharactersRefresh;
      document.addEventListener("cg:characters:refresh", window.__CG_EVT__.charactersRefreshEvents);
    } catch (_) {
    }
  }

  // assets/js/src/core/main/index.js
  var MainAPI = {
    init() {
      console.log("[MainAPI] init() called");
      bindUIEvents();
    }
  };
  var main_default = MainAPI;

  // assets/js/src/core/index.js
  function cgGlobal() {
    if (typeof globalThis !== "undefined")
      return globalThis;
    if (typeof window !== "undefined")
      return window;
    return {};
  }
  var G = cgGlobal();
  var LOADS_KEY = "__CG_CORE_BUNDLE_LOADS__";
  var BOOT_KEY = "__CG_CORE_BOOTED__";
  var INITCORE_KEY = "__CG_INITCORE_RAN__";
  G[LOADS_KEY] = (G[LOADS_KEY] || 0) + 1;
  console.log(`[Core] bundle loaded (#${G[LOADS_KEY]})`);
  var Core = {
    init(reason = "auto") {
      const g = cgGlobal();
      if (g[BOOT_KEY])
        return;
      g[BOOT_KEY] = true;
      console.log("[Core] init() called");
      try {
        main_default.init();
      } catch (err2) {
        console.error("[Core] MainAPI.init() failed", err2);
        throw err2;
      }
    }
  };
  function bootOnce() {
    Core.init("domready");
  }
  if (typeof document !== "undefined") {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", bootOnce, { once: true });
    } else {
      bootOnce();
    }
  }
  function initCore() {
    const g = cgGlobal();
    if (g[INITCORE_KEY])
      return;
    g[INITCORE_KEY] = true;
    Core.init("initCore");
    try {
      skills_default.init();
    } catch (err2) {
      console.error("[Core] SkillsModule.init() failed", err2);
    }
  }
  if (typeof window !== "undefined") {
    window.SpeciesAPI = api_default;
    window.CG_Core = Core;
    window.CG_initCore = initCore;
  }
})();
//# sourceMappingURL=core.bundle.js.map
