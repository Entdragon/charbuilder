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
        (xhr, status, err) => {
          warn("AJAX species list failed:", status, err, xhr == null ? void 0 : xhr.responseText);
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
        (xhr, status, err) => {
          warn("AJAX species profile failed:", status, err, xhr == null ? void 0 : xhr.responseText);
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
      }).fail((xhr, status, err) => {
        warn2("AJAX career list failed:", status, err, xhr == null ? void 0 : xhr.responseText);
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
      }).fail((xhr, status, err) => {
        warn2("AJAX career profile failed:", status, err, xhr == null ? void 0 : xhr.responseText);
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
    try {
      window.__CG_EVT__ = window.__CG_EVT__ || {};
      if (window.__CG_EVT__.giftsStateResync) {
        document.removeEventListener("cg:builder:opened", window.__CG_EVT__.giftsStateResync);
        document.removeEventListener("cg:character:loaded", window.__CG_EVT__.giftsStateResync);
      }
    } catch (_) {
    }
    try {
      window.__CG_EVT__ = window.__CG_EVT__ || {};
      window.__CG_EVT__.giftsStateResync = resync;
      document.addEventListener("cg:builder:opened", window.__CG_EVT__.giftsStateResync);
      document.addEventListener("cg:character:loaded", window.__CG_EVT__.giftsStateResync);
    } catch (_) {
    }
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
      function addGift(giftId2) {
        if (giftId2 == null)
          return;
        const id = String(giftId2).trim();
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
      const $21 = window.jQuery;
      const freq = { d8: 0, d6: 0, d4: 0 };
      $21(".cg-trait-select").each(function() {
        const v = $21(this).val();
        if (v && v in freq)
          freq[v]++;
      });
      $21(".cg-trait-select").each(function() {
        const $sel = $21(this);
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
      const $21 = window.jQuery;
      const boosts = this.calculateBoostMap();
      const totalCareerBoosts = boosts.trait_career || 0;
      const careerCounts = computeCareerBoostCounts(totalCareerBoosts);
      const careerMainBoosts = careerCounts.main || 0;
      TRAITS.forEach((traitKey) => {
        const $sel = $21(`#cg-${traitKey}`);
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
        const $badge = $21(`#cg-${traitKey}-badge`);
        if ($badge.length)
          $badge.text(badgeText);
        if (traitKey === "trait_career") {
          const $pb = $21("#cg-profile-trait_career-badge");
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
        const $note = $21(`#cg-${traitKey}-adjusted`);
        if ($note.length)
          $note.text(note);
        if (traitKey === "trait_career") {
          const $pn = $21("#cg-profile-trait_career-note");
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

          <div style="display:flex; align-items:center; gap:10px; margin-top:1em;">
            <div class="cg-gift-label" style="margin-top:0;">Language</div>
            <div id="cg-language" class="cg-gift-item" style="margin:0;"></div>
          </div>

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
      const normalize32 = (arr) => {
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
        freeArr = normalize32([s0 != null ? s0 : "", s1 != null ? s1 : "", s2 != null ? s2 : ""]);
      } else if (Array.isArray(this._data.free_gifts)) {
        freeArr = normalize32(this._data.free_gifts);
      } else if (Array.isArray(this._data.freeGifts)) {
        freeArr = normalize32(this._data.freeGifts);
      } else if (this._data["free-choice-0"] != null || this._data["free-choice-1"] != null || this._data["free-choice-2"] != null) {
        freeArr = normalize32([this._data["free-choice-0"], this._data["free-choice-1"], this._data["free-choice-2"]]);
      } else {
        freeArr = normalize32([this._data.free_gift_1, this._data.free_gift_2, this._data.free_gift_3]);
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
      }).fail((xhr, status, err) => {
        console.error("[FormBuilderAPI] save.fail()", status, err, xhr == null ? void 0 : xhr.responseText);
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
      const li2 = [];
      for (let i = 1; i <= 3; i++) {
        const name = profile[`gift_${i}`] || "";
        const gid = profile[`gift_id_${i}`] || "";
        const mult = parseInt(profile[`manifold_${i}`], 10) || 1;
        const display = name ? String(name) : gid ? `Gift #${gid}` : "";
        if (!display)
          continue;
        li2.push(`<li>${escapeHtml(mult > 1 ? `${display} \xD7 ${mult}` : display)}</li>`);
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
        li.push(`<li>${escapeHtml(txt)}</li>`);
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
    $7(document).off("cg:species:changed.careergifts.cg").on("cg:species:changed.careergifts.cg", _rerenderFromSpeciesChange);
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
    _countGiftInAcquired(giftId2) {
      var _a, _b, _c;
      const target = String(giftId2);
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

  // assets/js/src/core/quals/catalog.js
  var TYPES = ["language", "literacy", "insider", "mystic", "piety"];
  function stripDiacritics(s) {
    return String(s || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  }
  function canon(s) {
    return stripDiacritics(String(s || "")).trim().replace(/\s+/g, " ").toLowerCase();
  }
  function matchCategoryLine(text) {
    const m = String(text || "").trim().match(/^(Language|Literacy|Insider|Mystic|Piety)\s*:\s*(.+)\s*$/i);
    if (!m)
      return null;
    const type = canon(m[1]);
    const value = String(m[2] || "").trim();
    if (!type || !value)
      return null;
    const key = canon(value);
    if (!key)
      return null;
    return { type, value, key };
  }
  function buildQualCatalogFromGifts(gifts = []) {
    const catalog = /* @__PURE__ */ Object.create(null);
    TYPES.forEach((t) => catalog[t] = /* @__PURE__ */ Object.create(null));
    (Array.isArray(gifts) ? gifts : []).forEach((g) => {
      var _a, _b;
      const rs = (_b = (_a = g == null ? void 0 : g.requires_special) != null ? _a : g == null ? void 0 : g.ct_gifts_requires_special) != null ? _b : "";
      const hit = matchCategoryLine(rs);
      if (!hit || !catalog[hit.type])
        return;
      const bucket = catalog[hit.type];
      if (!bucket[hit.key]) {
        bucket[hit.key] = { key: hit.key, forms: /* @__PURE__ */ Object.create(null), count: 0 };
      }
      bucket[hit.key].count += 1;
      bucket[hit.key].forms[hit.value] = (bucket[hit.key].forms[hit.value] || 0) + 1;
    });
    const out = /* @__PURE__ */ Object.create(null);
    TYPES.forEach((type) => {
      out[type] = Object.values(catalog[type]).map((entry) => {
        const forms = entry.forms || {};
        let bestLabel = Object.keys(forms)[0] || entry.key;
        let bestCount = -1;
        for (const k of Object.keys(forms)) {
          const c = forms[k] || 0;
          if (c > bestCount) {
            bestCount = c;
            bestLabel = k;
          }
        }
        return { type, key: entry.key, label: bestLabel, count: entry.count };
      }).sort((a, b) => a.label.localeCompare(b.label));
    });
    return out;
  }

  // assets/js/src/core/quals/index.js
  var Quals = {
    _catalog: { language: [], literacy: [], insider: [], mystic: [], piety: [] },
    updateFromGifts(gifts) {
      this._catalog = buildQualCatalogFromGifts(gifts || []);
      window.CG_QualCatalog = this._catalog;
    },
    get(type) {
      const t = String(type || "").toLowerCase();
      return this._catalog[t] || [];
    },
    debugTop(limit = 30) {
      const rows = [];
      Object.keys(this._catalog || {}).forEach((type) => {
        (this._catalog[type] || []).forEach((o) => {
          rows.push({ type, count: o.count, text: `${type}: ${o.label}` });
        });
      });
      rows.sort((a, b) => b.count - a.count);
      const top = rows.slice(0, limit);
      console.table(top);
      return top;
    }
  };
  window.CG_Quals = Quals;
  var quals_default = Quals;

  // assets/js/src/core/quals/state.js
  var TYPES2 = ["language", "literacy", "insider", "mystic", "piety"];
  function stripDiacritics2(s) {
    return String(s || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  }
  function canon2(s) {
    return stripDiacritics2(String(s || "")).trim().replace(/\s+/g, " ").toLowerCase();
  }
  function emptyData() {
    return {
      language: [],
      literacy: [],
      insider: [],
      mystic: [],
      piety: []
    };
  }
  function normalizeList3(v) {
    if (v == null)
      return [];
    const arr = Array.isArray(v) ? v : [v];
    const out = [];
    const seen = /* @__PURE__ */ new Set();
    arr.forEach((x) => {
      const raw = String(x || "").trim().replace(/\s+/g, " ");
      if (!raw)
        return;
      const k = canon2(raw);
      if (!k || seen.has(k))
        return;
      seen.add(k);
      out.push(raw);
    });
    return out;
  }
  function getBuilderData() {
    var _a;
    if (formBuilder_default && formBuilder_default._data)
      return formBuilder_default._data;
    if (typeof ((_a = formBuilder_default) == null ? void 0 : _a.getData) === "function")
      return formBuilder_default.getData() || {};
    return {};
  }
  var QualState = {
    data: emptyData(),
    init() {
      const src = getBuilderData();
      const q = src.qualifications || src.quals || src.cg_quals || {};
      const next = emptyData();
      TYPES2.forEach((t) => {
        next[t] = normalizeList3(q[t]);
      });
      this.data = next;
      this.persist();
    },
    persist() {
      const src = getBuilderData();
      const payload = {};
      TYPES2.forEach((t) => payload[t] = (this.data[t] || []).slice());
      if (formBuilder_default && formBuilder_default._data) {
        formBuilder_default._data.qualifications = payload;
        formBuilder_default._data.quals = payload;
        formBuilder_default._data.cg_quals = payload;
      } else {
        src.qualifications = payload;
      }
      document.dispatchEvent(new CustomEvent("cg:quals:changed", { detail: { qualifications: payload } }));
      const $21 = window.jQuery;
      if ($21)
        $21(document).trigger("cg:quals:changed", [{ qualifications: payload }]);
    },
    getAll() {
      return JSON.parse(JSON.stringify(this.data || emptyData()));
    },
    get(type) {
      const t = String(type || "").toLowerCase();
      return this.data && Array.isArray(this.data[t]) ? this.data[t] : [];
    },
    has(type, value) {
      var _a;
      const t = String(type || "").toLowerCase();
      const k = canon2(value);
      return !!(((_a = this.data) == null ? void 0 : _a[t]) || []).some((v) => canon2(v) === k);
    },
    add(type, value) {
      const t = String(type || "").toLowerCase();
      if (!TYPES2.includes(t))
        return false;
      const raw = String(value || "").trim().replace(/\s+/g, " ");
      if (!raw)
        return false;
      if (this.has(t, raw))
        return false;
      this.data[t] = (this.data[t] || []).concat([raw]);
      this.persist();
      return true;
    },
    remove(type, value) {
      const t = String(type || "").toLowerCase();
      if (!TYPES2.includes(t))
        return false;
      const k = canon2(value);
      const before = this.get(t);
      const after = before.filter((v) => canon2(v) !== k);
      if (after.length === before.length)
        return false;
      this.data[t] = after;
      this.persist();
      return true;
    }
  };
  window.CG_QualState = QualState;
  var state_default2 = QualState;

  // assets/js/src/core/gifts/free-choices.js
  function cgWin() {
    if (typeof globalThis !== "undefined")
      return globalThis;
    if (typeof window !== "undefined")
      return window;
    return {};
  }
  var W = cgWin();
  var $11 = W && W.jQuery ? W.jQuery : null;
  var log3 = (...a) => {
    try {
      console.log("[FreeChoices]", ...a);
    } catch (_) {
    }
  };
  var warn3 = (...a) => {
    try {
      console.warn("[FreeChoices]", ...a);
    } catch (_) {
    }
  };
  var ALWAYS_ACQUIRED_GIFT_IDS2 = ["242", "236"];
  function normalize3(arr) {
    const out = (Array.isArray(arr) ? arr : []).slice(0, 3).map((v) => v ? String(v) : "");
    while (out.length < 3)
      out.push("");
    return out;
  }
  function getBuilderData2() {
    var _a;
    if (formBuilder_default && formBuilder_default._data)
      return formBuilder_default._data;
    if (typeof ((_a = formBuilder_default) == null ? void 0 : _a.getData) === "function")
      return formBuilder_default.getData() || {};
    return {};
  }
  function setBuilderKey(key, value) {
    const d = getBuilderData2();
    try {
      if (formBuilder_default && formBuilder_default._data)
        formBuilder_default._data[key] = value;
      else
        d[key] = value;
    } catch (_) {
    }
  }
  function getAjax() {
    return W.CG_AJAX || W.CG_Ajax || {};
  }
  function getNonceFor(action) {
    const nonces = W.CG_NONCES || {};
    if (nonces && nonces[action])
      return nonces[action];
    const ajax = getAjax();
    if (ajax && ajax.nonce)
      return ajax.nonce;
    if (ajax && ajax.security)
      return ajax.security;
    return "";
  }
  function ajaxPost(payload) {
    const ajax = getAjax();
    const url = ajax.ajax_url || ajax.url || W.ajaxurl || "";
    const data = __spreadValues({}, payload || {});
    const nonce = getNonceFor(String(data.action || ""));
    if (nonce && !data.security && !data.nonce && !data._ajax_nonce) {
      data.security = nonce;
      data.nonce = nonce;
      data._ajax_nonce = nonce;
    }
    if ($11 && url) {
      return new Promise((resolve, reject) => {
        $11.ajax({
          url,
          method: "POST",
          data,
          dataType: "json",
          success: (res) => resolve(res),
          error: (xhr, status, err) => reject(err || status || xhr)
        });
      });
    }
    if (!url)
      return Promise.reject(new Error("Missing AJAX url"));
    const body = new URLSearchParams();
    Object.entries(data).forEach(([k, v]) => body.append(k, String(v != null ? v : "")));
    return fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body
    }).then((r) => r.json());
  }
  function modalRoot() {
    if (typeof document === "undefined")
      return null;
    return document.querySelector("#cg-modal") || null;
  }
  function getFreeGiftSlotsFromData() {
    try {
      if (state_default && typeof state_default.getFreeGifts === "function")
        return normalize3(state_default.getFreeGifts());
    } catch (_) {
    }
    const d = getBuilderData2();
    const v = d.free_gifts || d.freeGifts || d.cg_free_gifts || d.free_choices || d.freeChoices || [];
    return normalize3(Array.isArray(v) ? v : [v]);
  }
  function emitFreeGiftChanged(selected, source = "free-choices") {
    try {
      const detail = { free_gifts: selected.slice(), source: String(source || "") };
      document.dispatchEvent(new CustomEvent("cg:free-gift:changed", { detail }));
      if ($11)
        $11(document).trigger("cg:free-gift:changed", [detail]);
    } catch (_) {
    }
  }
  function setFreeGiftSlotsToData(slots, source = "free-choices") {
    const normalized = normalize3(slots);
    setBuilderKey("free_gifts", normalized);
    setBuilderKey("freeGifts", normalized);
    setBuilderKey("cg_free_gifts", normalized);
    let stateHandled = false;
    try {
      if (state_default && typeof state_default.setSelected === "function") {
        state_default.setSelected(normalized);
        stateHandled = true;
      } else if (state_default && typeof state_default.setFreeGifts === "function") {
        state_default.setFreeGifts(normalized, { source });
        stateHandled = true;
      }
    } catch (_) {
    }
    if (!stateHandled)
      emitFreeGiftChanged(normalized, source);
    return normalized;
  }
  function giftId(g) {
    var _a, _b, _c, _d;
    return String((_d = (_c = (_b = (_a = g == null ? void 0 : g.id) != null ? _a : g == null ? void 0 : g.ct_id) != null ? _b : g == null ? void 0 : g.gift_id) != null ? _c : g == null ? void 0 : g.ct_gift_id) != null ? _d : "");
  }
  function giftName(g) {
    var _a, _b, _c, _d;
    return String((_d = (_c = (_b = (_a = g == null ? void 0 : g.name) != null ? _a : g == null ? void 0 : g.title) != null ? _b : g == null ? void 0 : g.gift_name) != null ? _c : g == null ? void 0 : g.ct_gift_name) != null ? _d : "");
  }
  function requiresSpecialText(g) {
    var _a, _b, _c;
    const rs = (_c = (_b = (_a = g == null ? void 0 : g.requires_special) != null ? _a : g == null ? void 0 : g.ct_gifts_requires_special) != null ? _b : g == null ? void 0 : g.requiresSpecial) != null ? _c : "";
    return String(rs != null ? rs : "").trim();
  }
  function allowsMultiple(g) {
    var _a, _b, _c;
    const v = (_c = (_b = (_a = g == null ? void 0 : g.allows_multiple) != null ? _a : g == null ? void 0 : g.ct_gifts_manifold) != null ? _b : g == null ? void 0 : g.manifold) != null ? _c : null;
    if (v === true)
      return true;
    const n = Number(v);
    return Number.isFinite(n) && n > 1;
  }
  function extractRequiredGiftIds(g) {
    if (!g || typeof g !== "object")
      return [];
    const out = [];
    Object.keys(g).forEach((k) => {
      if (!/^ct_gifts_requires(_[a-z]+)?$/i.test(k) && !/^ct_gifts_requires_/i.test(k))
        return;
      if (k.toLowerCase() === "ct_gifts_requires_special")
        return;
      const v = g[k];
      if (v == null)
        return;
      const s = String(v).trim();
      if (!s)
        return;
      s.split(",").map((x) => x.trim()).filter(Boolean).forEach((x) => out.push(String(x)));
    });
    return [...new Set(out)];
  }
  function diceToNum(s) {
    const t = String(s || "").trim().toLowerCase();
    const m = t.match(/d\s*(4|6|8|10|12)/);
    return m ? Number(m[1]) : null;
  }
  function getTraitDieValue(traitKey) {
    const d = getBuilderData2();
    const pools = [];
    if (d && typeof d === "object") {
      if (d.traits && typeof d.traits === "object")
        pools.push(d.traits);
      if (d.cg_traits && typeof d.cg_traits === "object")
        pools.push(d.cg_traits);
      pools.push(d);
    }
    const keys = [traitKey, `trait_${traitKey}`, `${traitKey}_die`, `die_${traitKey}`];
    for (const obj of pools) {
      for (const k of keys) {
        if (!obj || !(k in obj))
          continue;
        const v = obj[k];
        const n = diceToNum(v);
        if (n != null)
          return n;
      }
    }
    return null;
  }
  function extractTraitMinimaFromRequiresSpecial(rs) {
    const text = String(rs || "");
    if (!text)
      return [];
    const traits = [
      { key: "mind", re: /\bmind\b/i },
      { key: "body", re: /\bbody\b/i },
      { key: "speed", re: /\bspeed\b/i },
      { key: "will", re: /\bwill\b/i }
    ];
    const mins = [];
    const parts = text.split(/[\n\r.;]+/).map((s) => s.trim()).filter(Boolean);
    parts.forEach((p) => {
      traits.forEach((t) => {
        if (!t.re.test(p))
          return;
        const m = p.match(/d\s*(4|6|8|10|12)/i);
        if (!m)
          return;
        const need = Number(m[1]);
        if (!Number.isFinite(need))
          return;
        mins.push({ traitKey: t.key, need, raw: p });
      });
    });
    const best = /* @__PURE__ */ new Map();
    mins.forEach((x) => {
      const cur = best.get(x.traitKey);
      if (!cur || x.need > cur.need)
        best.set(x.traitKey, x);
    });
    return Array.from(best.values());
  }
  function traitMinimaSatisfied(g) {
    const rs = requiresSpecialText(g);
    const mins = extractTraitMinimaFromRequiresSpecial(rs);
    if (!mins.length)
      return true;
    for (const m of mins) {
      const have = getTraitDieValue(m.traitKey);
      if (have == null)
        return false;
      if (have < m.need)
        return false;
    }
    return true;
  }
  function computeOwnedGiftIdSet(selectedFreeGiftIds = []) {
    const owned2 = /* @__PURE__ */ new Set();
    ALWAYS_ACQUIRED_GIFT_IDS2.forEach((id) => owned2.add(String(id)));
    (selectedFreeGiftIds || []).filter(Boolean).forEach((id) => owned2.add(String(id)));
    const d = getBuilderData2();
    const candidateKeys = [
      "gift_id_1",
      "gift_id_2",
      "gift_id_3",
      "species_gift_one",
      "species_gift_two",
      "species_gift_three",
      "career_gift_one",
      "career_gift_two",
      "career_gift_three",
      "species_gifts",
      "career_gifts",
      "gift_ids",
      "giftIds",
      "gifts",
      "career_gift_replacements",
      "cg_career_gift_replacements"
    ];
    candidateKeys.forEach((k) => {
      if (!d || !(k in d))
        return;
      const v = d[k];
      if (Array.isArray(v))
        v.forEach((x) => x && owned2.add(String(x)));
      else if (v && typeof v === "object")
        Object.values(v).forEach((x) => x && owned2.add(String(x)));
      else if (v)
        owned2.add(String(v));
    });
    const apis = [W.SpeciesAPI, W.CareerAPI].filter(Boolean);
    apis.forEach((api) => {
      const prof = (api == null ? void 0 : api.currentProfile) || (api == null ? void 0 : api.profile) || (api == null ? void 0 : api.current) || (api == null ? void 0 : api.selected) || null;
      if (!prof)
        return;
      candidateKeys.forEach((k) => {
        if (!(k in prof))
          return;
        const v = prof[k];
        if (Array.isArray(v))
          v.forEach((x) => x && owned2.add(String(x)));
        else if (v && typeof v === "object")
          Object.values(v).forEach((x) => x && owned2.add(String(x)));
        else if (v)
          owned2.add(String(v));
      });
    });
    return owned2;
  }
  function giftEligible(g, ownedSet, otherSelectedIds = /* @__PURE__ */ new Set()) {
    if (!g)
      return false;
    const id = giftId(g);
    const name = giftName(g);
    const __DBG471 = !!(W && W.__CG_DBG471__) && (String(id) === "471" || String(name).toLowerCase() === "attendant fireball");
    const __owned = typeof ownedSet !== "undefined" ? ownedSet : typeof owned !== "undefined" ? owned : null;
    const __others = typeof otherSelectedIds !== "undefined" ? otherSelectedIds : typeof others !== "undefined" ? others : null;
    if (__DBG471) {
      console.log("[FreeChoices][DBG471] start", { id, name });
      try {
        console.log("[FreeChoices][DBG471] owned", __owned ? Array.from(__owned) : __owned);
      } catch (_) {
      }
      try {
        console.log("[FreeChoices][DBG471] others", __others ? Array.from(__others) : __others);
      } catch (_) {
      }
      console.log("[FreeChoices][DBG471] fields", {
        requires: g && g.requires,
        ct_gifts_requires: g && g.ct_gifts_requires,
        manifold: g && g.manifold,
        ct_gifts_manifold: g && g.ct_gifts_manifold,
        allows_multiple: g && g.allows_multiple,
        ct_gifts_allows_multiple: g && g.ct_gifts_allows_multiple,
        requires_special: g && (g.requires_special || g.ct_gifts_requires_special || g.requiresSpecial)
      });
    }
    if (!id || !name) {
      if (__DBG471)
        console.log("[FreeChoices][DBG471] FAIL: missing id/name", { id, name });
      return false;
    }
    const reqIds = extractRequiredGiftIds(g);
    for (const rid of reqIds) {
      if (!ownedSet.has(String(rid)))
        return false;
    }
    if (!traitMinimaSatisfied(g)) {
      if (__DBG471) {
        try {
          const rs = requiresSpecialText(g);
          const mins = extractTraitMinimaFromRequiresSpecial(rs);
          const tv = {};
          mins.forEach((t) => tv[t.trait] = getTraitDieValue(t.trait));
          console.log("[FreeChoices][DBG471] FAIL: trait minima", { rs, mins, tv });
        } catch (_) {
        }
      }
      return false;
    }
    if (otherSelectedIds.has(id) && !allowsMultiple(g))
      return false;
    return true;
  }
  function ensureSectionInHost(host) {
    if (typeof document === "undefined")
      return null;
    let section = document.getElementById("cg-free-gifts");
    if (!section) {
      section = document.createElement("div");
      section.id = "cg-free-gifts";
      section.className = "cg-profile-box cg-free-gifts";
      section.innerHTML = `
      <h3>Free Gifts (3)</h3>
      <div class="cg-free-row" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;"></div>
    `;
    }
    try {
      if (host && section.parentNode !== host)
        host.appendChild(section);
    } catch (_) {
    }
    return section;
  }
  function getSlotQualMap() {
    const d = getBuilderData2();
    const m = d.free_gift_quals || d.cg_free_gift_quals || d.freeGiftQuals || {};
    if (!m || typeof m !== "object")
      return {};
    return JSON.parse(JSON.stringify(m));
  }
  function setSlotQualMap(map) {
    const clean = map && typeof map === "object" ? map : {};
    setBuilderKey("free_gift_quals", clean);
    setBuilderKey("cg_free_gift_quals", clean);
    setBuilderKey("freeGiftQuals", clean);
  }
  function stripDiacritics3(s) {
    return String(s || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  }
  function canon3(s) {
    return stripDiacritics3(String(s || "")).trim().replace(/\s+/g, " ").toLowerCase();
  }
  function detectQualTypesNeeded(g) {
    const rs = requiresSpecialText(g);
    const name = giftName(g).toLowerCase();
    const found = /* @__PURE__ */ new Set();
    const lines = String(rs || "").split(/\r?\n/).map((s) => s.trim()).filter(Boolean);
    lines.forEach((line) => {
      const m = line.match(/^(language|literacy|insider|mystic|piety)\s*:/i);
      if (m && m[1])
        found.add(String(m[1]).toLowerCase());
    });
    if (found.size === 0) {
      if (name.includes("piety"))
        found.add("piety");
      if (name.includes("mystic"))
        found.add("mystic");
      if (name.includes("insider"))
        found.add("insider");
      if (name.includes("literacy"))
        found.add("literacy");
      if (name.includes("language"))
        found.add("language");
    }
    const TYPES3 = ["language", "literacy", "insider", "mystic", "piety"];
    return TYPES3.filter((t) => found.has(t));
  }
  function getQualItemsForType(type, allGifts) {
    let items = [];
    try {
      items = quals_default && typeof quals_default.get === "function" ? quals_default.get(type) || [] : [];
    } catch (_) {
      items = [];
    }
    if (Array.isArray(items) && items.length > 0)
      return items;
    const want = String(type || "").toLowerCase();
    const rx = new RegExp(`^\\s*${want}\\s*:\\s*(.+)$`, "i");
    const keep = /* @__PURE__ */ new Map();
    const list = Array.isArray(allGifts) ? allGifts : [];
    for (const g of list) {
      const rs = requiresSpecialText(g);
      if (!rs)
        continue;
      const lines = rs.split(/\r?\n/).map((s) => s.trim()).filter(Boolean);
      for (const line of lines) {
        const m = line.match(rx);
        if (!m || !m[1])
          continue;
        const raw = String(m[1]).trim();
        const parts = raw.split(/,|\/|;|\bor\b/ig).map((s) => String(s || "").trim()).filter(Boolean);
        for (const p of parts) {
          if (/^any\b/i.test(p))
            continue;
          const k = canon3(p);
          if (!k)
            continue;
          if (!keep.has(k))
            keep.set(k, p);
        }
      }
    }
    const labels = Array.from(keep.values()).sort((a, b) => String(a).localeCompare(String(b), void 0, { sensitivity: "base" }));
    return labels.map((label) => ({ label, key: label }));
  }
  function ensureQualStateInit() {
    var _a, _b;
    try {
      (_b = (_a = state_default2) == null ? void 0 : _a.init) == null ? void 0 : _b.call(_a);
    } catch (_) {
    }
  }
  function qualStateHas(type, value) {
    var _a, _b, _c;
    type = String(type || "").toLowerCase();
    if (!value)
      return false;
    try {
      if (typeof ((_a = state_default2) == null ? void 0 : _a.has) === "function")
        return !!state_default2.has(type, value);
      const arr = ((_c = (_b = state_default2) == null ? void 0 : _b.get) == null ? void 0 : _c.call(_b, type)) || [];
      return arr.some((v) => canon3(v) === canon3(value));
    } catch (_) {
      return false;
    }
  }
  function qualStateAdd(type, value) {
    var _a, _b, _c, _d, _e, _f;
    if (!value)
      return;
    ensureQualStateInit();
    if (type === "language") {
      try {
        const cur = (((_b = (_a = state_default2) == null ? void 0 : _a.get) == null ? void 0 : _b.call(_a, "language")) || []).slice();
        const base = cur[0] || "";
        if (canon3(base) === canon3(value))
          return;
        if (qualStateHas("language", value))
          return;
        if (((_c = state_default2) == null ? void 0 : _c.data) && Array.isArray(state_default2.data.language) && typeof state_default2.persist === "function") {
          state_default2.data.language = [base, ...cur.slice(1).filter((v) => canon3(v) !== canon3(value)), value].filter(Boolean);
          state_default2.persist();
        } else if (typeof ((_d = state_default2) == null ? void 0 : _d.add) === "function") {
          state_default2.add("language", value);
        }
      } catch (_) {
      }
      return;
    }
    try {
      if (qualStateHas(type, value))
        return;
      if (typeof ((_e = state_default2) == null ? void 0 : _e.add) === "function")
        state_default2.add(type, value);
      else if (((_f = state_default2) == null ? void 0 : _f.data) && Array.isArray(state_default2.data[type]) && typeof state_default2.persist === "function") {
        state_default2.data[type] = (state_default2.data[type] || []).concat([value]);
        state_default2.persist();
      }
    } catch (_) {
    }
  }
  function qualStateRemoveIfSafe(type, value, slotMap) {
    var _a, _b, _c, _d;
    if (!value)
      return;
    ensureQualStateInit();
    const stillUsed = Object.values(slotMap || {}).some((slotObj) => {
      if (!slotObj || typeof slotObj !== "object")
        return false;
      const v = slotObj[type];
      return v && canon3(v) === canon3(value);
    });
    if (stillUsed)
      return;
    if (type === "language") {
      try {
        const cur = (((_b = (_a = state_default2) == null ? void 0 : _a.get) == null ? void 0 : _b.call(_a, "language")) || []).slice();
        const base = cur[0] || "";
        if (canon3(base) === canon3(value))
          return;
      } catch (_) {
      }
    }
    try {
      if (typeof ((_c = state_default2) == null ? void 0 : _c.remove) === "function")
        state_default2.remove(type, value);
      else if (((_d = state_default2) == null ? void 0 : _d.data) && Array.isArray(state_default2.data[type]) && typeof state_default2.persist === "function") {
        state_default2.data[type] = (state_default2.data[type] || []).filter((v) => canon3(v) !== canon3(value));
        state_default2.persist();
      }
    } catch (_) {
    }
  }
  function renderQualSelectHtml({ slot, type, value, allGifts }) {
    const items = getQualItemsForType(type, allGifts);
    const opts = (Array.isArray(items) ? items : []).map((it) => {
      var _a, _b;
      const label = String((_b = (_a = it == null ? void 0 : it.label) != null ? _a : it == null ? void 0 : it.key) != null ? _b : "").trim();
      if (!label)
        return "";
      const sel = canon3(value) === canon3(label) ? " selected" : "";
      return `<option value="${label}"${sel}>${label}</option>`;
    }).filter(Boolean).join("\n");
    const nice = type.charAt(0).toUpperCase() + type.slice(1);
    return `
    <label style="display:flex; flex-direction:column; gap:4px; margin-top:8px; min-width: 220px;">
      <span style="font-weight:600;">${nice}</span>
      <select class="cg-free-qual-select" data-slot="${slot}" data-qtype="${type}">
        <option value="">\u2014 Select ${nice} \u2014</option>
        ${opts}
      </select>
    </label>
  `;
  }
  var Existing = W.CG_FreeChoices;
  var FreeChoices = Existing && Existing.__cg_singleton ? Existing : {
    __cg_singleton: true,
    _inited: false,
    _bound: false,
    _allGifts: [],
    _byId: null,
    _loading: false,
    _renderQueued: false,
    _renderRunning: false,
    _renderPending: false,
    _scheduleRender(reason = "auto") {
      if (this._renderRunning) {
        this._renderPending = true;
        return;
      }
      if (this._renderQueued)
        return;
      this._renderQueued = true;
      const run = () => {
        this._renderQueued = false;
        this._renderRunning = true;
        try {
          this.render();
        } catch (e) {
          try {
            console.error("[FreeChoices] render failed", e);
          } catch (_) {
          }
        } finally {
          this._renderRunning = false;
          if (this._renderPending) {
            this._renderPending = false;
            this._scheduleRender("pending");
          }
        }
      };
      if (typeof requestAnimationFrame === "function")
        requestAnimationFrame(run);
      else
        setTimeout(run, 0);
    },
    init() {
      var _a, _b, _c, _d, _e, _f;
      if (this.__inited)
        return;
      this.__inited = true;
      if (this._inited)
        return;
      this._inited = true;
      try {
        (_b = (_a = state_default) == null ? void 0 : _a.init) == null ? void 0 : _b.call(_a);
      } catch (_) {
      }
      try {
        (_d = (_c = state_default2) == null ? void 0 : _c.init) == null ? void 0 : _d.call(_c);
      } catch (_) {
      }
      try {
        (_f = (_e = quals_default) == null ? void 0 : _e.init) == null ? void 0 : _f.call(_e);
      } catch (_) {
      }
      this._bindGlobalEventsOnce();
      this.refresh({ reason: "init", fetch: true });
    },
    _bindGlobalEventsOnce() {
      if (this._bound)
        return;
      this._bound = true;
      const rerenderSoon = this._rerenderSoon || ((e) => {
        this._scheduleRender(e && e.type ? e.type : "event");
      });
      this._rerenderSoon = rerenderSoon;
      if ($11 && $11.fn) {
        $11(document).off("cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices").on("cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices", rerenderSoon);
        return;
      }
      document.removeEventListener("cg:builder:opened", rerenderSoon);
      document.addEventListener("cg:builder:opened", rerenderSoon);
      try {
        const W3 = window;
        W3.__CG_EVT__ = W3.__CG_EVT__ || {};
        const EVT = W3.__CG_EVT__;
        if (EVT.freeChoicesRerenderSoon) {
          try {
            document.removeEventListener("cg:tab:changed", EVT.freeChoicesRerenderSoon);
          } catch (_) {
          }
        }
        EVT.freeChoicesRerenderSoon = rerenderSoon;
        document.addEventListener("cg:tab:changed", EVT.freeChoicesRerenderSoon);
      } catch (_) {
        try {
          document.addEventListener("cg:tab:changed", rerenderSoon);
        } catch (_2) {
        }
      }
      document.removeEventListener("cg:character:loaded", rerenderSoon);
      document.addEventListener("cg:character:loaded", rerenderSoon);
      document.removeEventListener("cg:quals:catalog-updated", rerenderSoon);
      document.addEventListener("cg:quals:catalog-updated", rerenderSoon);
      if ($11 && $11.fn) {
        $11(document).off("cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices").on("cg:builder:opened.cgfreechoices cg:tab:changed.cgfreechoices cg:character:loaded.cgfreechoices cg:quals:catalog-updated.cgfreechoices", rerenderSoon);
      }
    },
    refresh() {
      return __async(this, arguments, function* ({ fetch: fetch2 = false } = {}) {
        if (fetch2)
          yield this._fetchAllGifts();
        this._scheduleRender("refresh");
      });
    },
    _fetchAllGifts() {
      return __async(this, null, function* () {
        if (this._loading)
          return;
        this._loading = true;
        try {
          const res = yield ajaxPost({ action: "cg_get_free_gifts" });
          let list = null;
          if (Array.isArray(res))
            list = res;
          else if (res && Array.isArray(res.data))
            list = res.data;
          else if (res && res.data && Array.isArray(res.data.gifts))
            list = res.data.gifts;
          else if (res && Array.isArray(res.gifts))
            list = res.gifts;
          if (!Array.isArray(list)) {
            warn3("cg_get_free_gifts returned unexpected payload", res);
            list = [];
          }
          this._allGifts = list.slice();
          this._byId = /* @__PURE__ */ new Map();
          this._allGifts.forEach((g) => {
            const id = giftId(g);
            if (id)
              this._byId.set(id, g);
          });
          try {
            state_default.setList(this._allGifts);
          } catch (_) {
          }
          log3("Fetched gifts", { count: this._allGifts.length });
        } catch (err) {
          warn3("Failed to fetch gifts", err);
          this._allGifts = [];
          this._byId = /* @__PURE__ */ new Map();
        } finally {
          this._loading = false;
        }
      });
    },
    _getGift(id) {
      const k = String(id || "");
      if (!k)
        return null;
      if (this._byId && this._byId.get(k))
        return this._byId.get(k);
      const list = Array.isArray(this._allGifts) ? this._allGifts : [];
      return list.find((g) => giftId(g) === k) || null;
    },
    render() {
      const modal = modalRoot();
      if (!modal)
        return;
      const host = modal.querySelector("#cg-free-choices") || document.querySelector("#cg-free-choices");
      if (!host)
        return;
      const section = ensureSectionInHost(host);
      if (!section)
        return;
      this._bindSectionDelegates(section);
      const slots = getFreeGiftSlotsFromData();
      const row = section.querySelector(".cg-free-row");
      if (!row)
        return;
      const owned2 = computeOwnedGiftIdSet(slots.filter(Boolean));
      const htmlSlots = [0, 1, 2].map((i) => {
        const selectedId = String(slots[i] || "").trim();
        const others2 = new Set(slots.map((v, idx) => idx === i ? "" : String(v || "").trim()).filter(Boolean));
        const eligible = (Array.isArray(this._allGifts) ? this._allGifts : []).filter((g) => giftEligible(g, owned2, others2));
        const curGift = selectedId ? this._getGift(selectedId) : null;
        const curOpt = curGift ? [{ id: giftId(curGift), name: giftName(curGift), _forced: true }] : [];
        const seen = /* @__PURE__ */ new Set();
        const options = [].concat(curOpt.map((o) => ({ _forced: true, id: o.id, name: o.name }))).concat(eligible.map((g) => ({ id: giftId(g), name: giftName(g) }))).filter((o) => o && o.id && o.name).filter((o) => {
          if (seen.has(o.id))
            return false;
          seen.add(o.id);
          return true;
        }).map((o) => {
          const sel = String(selectedId) === String(o.id) ? " selected" : "";
          const suffix = o._forced ? " (saved)" : "";
          return `<option value="${String(o.id)}"${sel}>${String(o.name)}${suffix}</option>`;
        }).join("\n");
        return `
        <div class="cg-free-slot" data-slot="${i}" style="display:flex; flex-direction:column;">
          <label style="display:flex; flex-direction:column; gap:4px; min-width: 220px;">
            <span style="font-weight:600;">Free Gift ${i + 1}</span>
            <select id="cg-free-choice-${i}" class="cg-free-gift-select" data-slot="${i}">
              <option value="">\u2014 Select a gift \u2014</option>
              ${options}
            </select>
          </label>
          <div class="cg-free-slot-quals" data-slot="${i}"></div>
        </div>
      `;
      });
      row.innerHTML = htmlSlots.join("\n");
      this._renderSlotQuals(slots);
    },
    _bindSectionDelegates(section) {
      if (section.__cgBound)
        return;
      section.__cgBound = true;
      section.addEventListener("change", (e) => {
        const t = e.target;
        if (!t || !t.classList)
          return;
        if (t.classList.contains("cg-free-gift-select")) {
          const slot = Number(t.getAttribute("data-slot") || "0");
          const nextId = String(t.value || "").trim();
          const prevSlots = getFreeGiftSlotsFromData();
          const nextSlots = prevSlots.slice();
          nextSlots[slot] = nextId;
          this._cleanupSlotQualsOnGiftChange({ slot, prevGiftId: prevSlots[slot], nextGiftId: nextId });
          setFreeGiftSlotsToData(nextSlots, "free-gift-select");
          this._scheduleRender("free-gift-select");
          return;
        }
        if (t.classList.contains("cg-free-qual-select")) {
          const slot = String(t.getAttribute("data-slot") || "0");
          const type = String(t.getAttribute("data-qtype") || "").toLowerCase();
          const val = String(t.value || "").trim();
          const map = getSlotQualMap();
          if (!map[slot] || typeof map[slot] !== "object")
            map[slot] = {};
          const prev = String(map[slot][type] || "").trim();
          if (val)
            map[slot][type] = val;
          else
            delete map[slot][type];
          if (Object.keys(map[slot] || {}).length === 0)
            delete map[slot];
          setSlotQualMap(map);
          if (prev && canon3(prev) !== canon3(val))
            qualStateRemoveIfSafe(type, prev, map);
          if (val)
            qualStateAdd(type, val);
          this._scheduleRender("free-qual-select");
          return;
        }
      });
    },
    _cleanupSlotQualsOnGiftChange({ slot, prevGiftId, nextGiftId }) {
      const prevG = prevGiftId ? this._getGift(prevGiftId) : null;
      const nextG = nextGiftId ? this._getGift(nextGiftId) : null;
      const prevNeeds = prevG ? detectQualTypesNeeded(prevG) : [];
      const nextNeeds = nextG ? detectQualTypesNeeded(nextG) : [];
      const map = getSlotQualMap();
      const sKey = String(slot);
      const slotObj = map[sKey] && typeof map[sKey] === "object" ? map[sKey] : null;
      if (!slotObj)
        return;
      prevNeeds.forEach((type) => {
        if (nextNeeds.includes(type))
          return;
        const prevVal = String(slotObj[type] || "").trim();
        if (!prevVal)
          return;
        delete slotObj[type];
        qualStateRemoveIfSafe(type, prevVal, map);
      });
      if (slotObj && Object.keys(slotObj).length === 0)
        delete map[sKey];
      setSlotQualMap(map);
    },
    _renderSlotQuals(slots) {
      const section = document.getElementById("cg-free-gifts");
      if (!section)
        return;
      const map = getSlotQualMap();
      [0, 1, 2].forEach((i) => {
        const slotId = String(i);
        const giftIdSel = String(slots[i] || "").trim();
        const wrap = section.querySelector(`.cg-free-slot-quals[data-slot="${i}"]`);
        if (!wrap)
          return;
        if (!giftIdSel) {
          wrap.innerHTML = "";
          return;
        }
        const g = this._getGift(giftIdSel);
        if (!g) {
          wrap.innerHTML = "";
          return;
        }
        const needs = detectQualTypesNeeded(g);
        if (!needs.length) {
          wrap.innerHTML = "";
          return;
        }
        if (!map[slotId] || typeof map[slotId] !== "object")
          map[slotId] = {};
        const html = needs.map((type) => {
          const cur = String(map[slotId][type] || "").trim();
          return renderQualSelectHtml({ slot: i, type, value: cur, allGifts: this._allGifts });
        }).join("\n");
        wrap.innerHTML = html;
        setSlotQualMap(map);
      });
    },
    debug() {
      var _a, _b;
      const sizes = {};
      ["language", "literacy", "insider", "mystic", "piety"].forEach((t) => {
        try {
          sizes[t] = getQualItemsForType(t, this._allGifts).length;
        } catch (_) {
          sizes[t] = null;
        }
      });
      return {
        giftsCount: Array.isArray(this._allGifts) ? this._allGifts.length : 0,
        slots: getFreeGiftSlotsFromData(),
        slotQualMap: getSlotQualMap(),
        qualCatalogSizes: sizes,
        qualState: ((_b = (_a = state_default2) == null ? void 0 : _a.getAll) == null ? void 0 : _b.call(_a)) || null
      };
    }
  };
  W.CG_FreeChoices = FreeChoices;
  var free_choices_default = FreeChoices;

  // assets/js/src/core/quals/ui.js
  function cgWin2() {
    if (typeof globalThis !== "undefined")
      return globalThis;
    if (typeof window !== "undefined")
      return window;
    return {};
  }
  var W2 = cgWin2();
  var $12 = W2 && W2.jQuery ? W2.jQuery : null;
  function modalRoot2() {
    if (typeof document === "undefined")
      return null;
    return document.querySelector("#cg-modal") || null;
  }
  function stripDiacritics4(s) {
    return String(s || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  }
  function canon4(s) {
    return stripDiacritics4(String(s || "")).trim().replace(/\s+/g, " ").toLowerCase();
  }
  function uniqSorted(list) {
    const seen = /* @__PURE__ */ new Set();
    const out = [];
    (Array.isArray(list) ? list : []).forEach((v) => {
      const raw = String(v || "").trim().replace(/\s+/g, " ");
      if (!raw)
        return;
      const k = canon4(raw);
      if (!k || seen.has(k))
        return;
      seen.add(k);
      out.push(raw);
    });
    out.sort((a, b) => canon4(a).localeCompare(canon4(b)));
    return out;
  }
  function removeLegacyQualUIs(modal) {
    if (!modal)
      return;
    try {
      const oldBox = modal.querySelector("#cg-quals-box");
      if (oldBox)
        oldBox.remove();
    } catch (_) {
    }
    try {
      const oldInline = modal.querySelector("#cg-quals-inline");
      if (oldInline)
        oldInline.remove();
    } catch (_) {
    }
  }
  function getAllGiftsList2() {
    const FC = W2.CG_FreeChoices;
    if (FC && Array.isArray(FC._allGifts) && FC._allGifts.length)
      return FC._allGifts;
    const GS = W2.CG_GiftsState || W2.CG_Gifts || null;
    if (GS) {
      if (Array.isArray(GS._allGifts) && GS._allGifts.length)
        return GS._allGifts;
      if (Array.isArray(GS.allGifts) && GS.allGifts.length)
        return GS.allGifts;
      if (Array.isArray(GS.gifts) && GS.gifts.length)
        return GS.gifts;
    }
    return [];
  }
  function getRequiresSpecial(g) {
    var _a, _b, _c;
    return String(
      (_c = (_b = (_a = g == null ? void 0 : g.requires_special) != null ? _a : g == null ? void 0 : g.ct_gifts_requires_special) != null ? _b : g == null ? void 0 : g.ct_requires_special) != null ? _c : ""
    );
  }
  function extractLanguagesFromGifts(gifts) {
    const out = [];
    (Array.isArray(gifts) ? gifts : []).forEach((g) => {
      const rs = getRequiresSpecial(g);
      if (!rs)
        return;
      const lines = String(rs).split(/\r?\n|•|·/g).map((s) => String(s || "").trim()).filter(Boolean);
      lines.forEach((line) => {
        const m = line.match(/^language\s*:\s*(.+)$/i);
        if (!m)
          return;
        const rest = String(m[1] || "").trim();
        if (!rest)
          return;
        const restCanon = canon4(rest);
        if (restCanon === "any" || restCanon === "varies" || restCanon === "see text")
          return;
        rest.split(/\s*[;,]\s*/g).map((x) => String(x || "").trim()).filter(Boolean).forEach((x) => out.push(x));
      });
    });
    return uniqSorted(out);
  }
  function findBaseLanguageHost(modal) {
    if (!modal)
      return null;
    const cgLanguage = modal.querySelector("#cg-language");
    if (cgLanguage)
      return cgLanguage;
    const direct = modal.querySelector("#cg-free-language") || modal.querySelector("#cg-language-choice") || modal.querySelector("#cg-gift-language");
    if (direct)
      return direct;
    const freeChoices = modal.querySelector("#cg-free-choices") || document.querySelector("#cg-free-choices");
    if (freeChoices && freeChoices.parentElement)
      return freeChoices.parentElement;
    return null;
  }
  function ensureBaseLanguageContainer(modal) {
    const host = findBaseLanguageHost(modal);
    if (!host)
      return null;
    let wrap = modal.querySelector("#cg-base-language");
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.id = "cg-base-language";
      wrap.className = "cg-gift-item cg-base-language";
      wrap.innerHTML = `<div class="cg-base-language-control"></div>`;
    }
    try {
      if (host.classList && host.classList.contains("cg-gift-item")) {
        if (!host.querySelector("#cg-base-language"))
          host.appendChild(wrap);
      } else {
        const freeChoices = modal.querySelector("#cg-free-choices") || document.querySelector("#cg-free-choices");
        if (freeChoices && freeChoices.parentElement) {
          if (wrap.parentNode !== freeChoices.parentElement) {
            freeChoices.parentElement.insertBefore(wrap, freeChoices);
          }
        } else if (wrap.parentNode !== host) {
          host.appendChild(wrap);
        }
      }
    } catch (_) {
    }
    return wrap;
  }
  var _languageListCache = null;
  var _languageListLoading = false;
  function fetchLanguageList(onLoaded) {
    if (_languageListCache !== null) {
      if (onLoaded)
        onLoaded(_languageListCache);
      return;
    }
    if (_languageListLoading)
      return;
    _languageListLoading = true;
    try {
      const ajaxUrl = W2.CG_AJAX && W2.CG_AJAX.ajax_url ? W2.CG_AJAX.ajax_url : "/api/ajax";
      fetch(ajaxUrl, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "cg_get_language_list" })
      }).then((r) => r.json()).then((json) => {
        _languageListLoading = false;
        _languageListCache = json && json.success && Array.isArray(json.data) ? json.data : [];
        if (onLoaded)
          onLoaded(_languageListCache);
      }).catch(() => {
        _languageListLoading = false;
        _languageListCache = [];
        if (onLoaded)
          onLoaded(_languageListCache);
      });
    } catch (_) {
      _languageListLoading = false;
      _languageListCache = [];
    }
  }
  function getLanguageLabelsForBaseSelect() {
    if (_languageListCache !== null && _languageListCache.length)
      return _languageListCache;
    const gifts = getAllGiftsList2();
    const langs = extractLanguagesFromGifts(gifts);
    return langs;
  }
  function renderLanguageSelect(container) {
    if (!container)
      return;
    const labels = getLanguageLabelsForBaseSelect();
    const cur = state_default2 && typeof state_default2.get === "function" ? (state_default2.get("language") || [])[0] || "" : "";
    const finalLabels = uniqSorted([cur, ...labels]);
    const opts = finalLabels.map((label) => {
      const sel2 = canon4(cur) === canon4(label) ? " selected" : "";
      const safe = String(label).replace(/"/g, "&quot;");
      return `<option value="${safe}"${sel2}>${label}</option>`;
    }).join("\n");
    container.innerHTML = `
    <select id="cg-base-language-select" style="min-width:220px;">
      <option value="">\u2014 Select Language \u2014</option>
      ${opts}
    </select>
  `;
    const sel = container.querySelector("#cg-base-language-select");
    if (!sel)
      return;
    sel.addEventListener("change", (e) => {
      var _a, _b;
      const nextVal = String(e.target.value || "").trim();
      try {
        (_b = (_a = state_default2) == null ? void 0 : _a.init) == null ? void 0 : _b.call(_a);
      } catch (_) {
      }
      try {
        const current = state_default2 && typeof state_default2.get === "function" ? (state_default2.get("language") || []).slice() : [];
        const rest = current.filter((v) => canon4(v) !== canon4(nextVal) && canon4(v) !== canon4(current[0] || ""));
        const next = nextVal ? [nextVal, ...rest] : rest;
        if (state_default2 && state_default2.data && Array.isArray(state_default2.data.language) && typeof state_default2.persist === "function") {
          state_default2.data.language = next;
          state_default2.persist();
        } else {
          if (current[0])
            state_default2.remove("language", current[0]);
          if (nextVal)
            state_default2.add("language", nextVal);
        }
      } catch (_) {
      }
    });
  }
  var Existing2 = W2.CG_QualUI;
  var QualUI = Existing2 && Existing2.__cg_singleton ? Existing2 : {
    __cg_singleton: true,
    _inited: false,
    _observer: null,
    _lastModalPresent: null,
    _renderScheduled: false,
    _rendering: false,
    _onCatalogUpdated: null,
    _onBuilderOpened: null,
    _onTabChanged: null,
    init() {
      var _a, _b;
      if (this._inited)
        return;
      this._inited = true;
      try {
        (_b = (_a = state_default2) == null ? void 0 : _a.init) == null ? void 0 : _b.call(_a);
      } catch (_) {
      }
      this._bindEvents();
      this._installObserver();
      this._scheduleRender("init");
    },
    _scheduleRender(reason = "") {
      if (this._renderScheduled)
        return;
      this._renderScheduled = true;
      const run = () => {
        this._renderScheduled = false;
        if (this._rendering)
          return;
        this._rendering = true;
        try {
          this.render();
        } catch (err) {
          console.error("[QualUI] render failed", { reason }, err);
        } finally {
          this._rendering = false;
        }
      };
      if (typeof requestAnimationFrame !== "undefined")
        requestAnimationFrame(run);
      else
        setTimeout(run, 0);
    },
    _bindEvents() {
      if (typeof document === "undefined")
        return;
      if (!this._onCatalogUpdated)
        this._onCatalogUpdated = () => this._scheduleRender("catalog-updated");
      if (!this._onBuilderOpened)
        this._onBuilderOpened = () => {
          var _a, _b;
          try {
            (_b = (_a = state_default2) == null ? void 0 : _a.init) == null ? void 0 : _b.call(_a);
          } catch (_) {
          }
          this._scheduleRender("builder-opened");
        };
      if (!this._onTabChanged)
        this._onTabChanged = () => this._scheduleRender("tab-changed");
      document.removeEventListener("cg:quals:catalog-updated", this._onCatalogUpdated);
      document.addEventListener("cg:quals:catalog-updated", this._onCatalogUpdated);
      document.removeEventListener("cg:builder:opened", this._onBuilderOpened);
      document.addEventListener("cg:builder:opened", this._onBuilderOpened);
      document.removeEventListener("cg:tab:changed", this._onTabChanged);
      document.addEventListener("cg:tab:changed", this._onTabChanged);
      if ($12 && $12.fn) {
        $12(document).off("cg:quals:catalog-updated.cgqualui cg:builder:opened.cgqualui cg:tab:changed.cgqualui").on("cg:quals:catalog-updated.cgqualui", this._onCatalogUpdated).on("cg:builder:opened.cgqualui", this._onBuilderOpened).on("cg:tab:changed.cgqualui", this._onTabChanged);
      }
    },
    _installObserver() {
      if (this._observer)
        return;
      if (typeof MutationObserver === "undefined" || typeof document === "undefined")
        return;
      this._lastModalPresent = !!modalRoot2();
      this._observer = new MutationObserver(() => {
        const present = !!modalRoot2();
        if (present !== this._lastModalPresent) {
          this._lastModalPresent = present;
          if (present)
            this._scheduleRender("modal-opened");
          return;
        }
      });
      this._observer.observe(document.body, { childList: true, subtree: true });
    },
    render() {
      const modal = modalRoot2();
      if (!modal)
        return;
      removeLegacyQualUIs(modal);
      const wrap = ensureBaseLanguageContainer(modal);
      if (!wrap)
        return;
      const control = wrap.querySelector(".cg-base-language-control");
      if (!control)
        return;
      if (_languageListCache === null) {
        fetchLanguageList(() => this._scheduleRender("language-list-loaded"));
        return;
      }
      renderLanguageSelect(control);
    }
  };
  W2.CG_QualUI = QualUI;
  var ui_default = QualUI;

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
      ui_default.init();
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
  var $13 = window.jQuery;
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
      let $table = $13("#tab-skills #skills-table");
      if (!$table.length)
        $table = $13("#skills-table");
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
      $13("#tab-skills #marks-remaining, #marks-remaining").remove();
      $table.before(`
      <div id="marks-remaining" class="marks-remaining">
        Marks Remaining: <strong>${marksRemain}</strong>
      </div>
    `);
      const $thead = $13("<thead>");
      const $tr = $13("<tr>").append("<th>Skill</th>").append(`<th>${speciesNameOf(species) || ""}</th>`).append(`<th>${careerNameOf(career) || ""}</th>`);
      extraCareers.forEach((ec) => {
        $tr.append(`<th>${ec.name || "Extra Career"}</th>`);
      });
      $tr.append("<th>Marks</th>").append("<th>Dice Pool</th>").appendTo($thead);
      const spSkills = extractSkillTripletFromAny(species).map(String);
      const cpSkills = extractSkillTripletFromAny(career).map(String);
      const $tbody = $13("<tbody>");
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
        const $row = $13("<tr>").append(`<td>${name}</td>`).append(`<td>${spDie || "\u2013"}</td>`).append(`<td>${cpDie || "\u2013"}</td>`);
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
  var $14 = window.jQuery;
  var _pendingRender = false;
  function isSkillsTabActive2() {
    try {
      return String($14("#cg-modal .cg-tabs li.active").data("tab") || "") === "tab-skills";
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
      $14(document).off("cg:tab:changed.cgskills").on("cg:tab:changed.cgskills", onTabChanged);
      $14(document).off("change.cgskills", "#cg-species, #cg-career").on("change.cgskills", "#cg-species, #cg-career", () => {
        requestRender("species/career change");
      });
      $14(document).off("cg:extra-careers:changed.cgskills").on("cg:extra-careers:changed.cgskills", () => {
        requestRender("extra careers changed");
      });
      $14(document).off("click.cgskills", ".skill-mark-btn").on("click.cgskills", ".skill-mark-btn", function() {
        var _a;
        const skillId = String((_a = $14(this).data("skill-id")) != null ? _a : "");
        const markRaw = parseInt($14(this).data("mark"), 10);
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
  var $15 = window.jQuery;
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
  var $16 = window.jQuery;
  var TRAITS3 = service_default.TRAITS;
  var MARK_DIE2 = { 1: "d4", 2: "d6", 3: "d8" };
  var SummaryAPI = {
    /**
     * Entry point when the Summary tab is shown.
     */
    init() {
      if (this.__cg_inited) {
        try {
          if (typeof window !== "undefined" && window.FormBuilderAPI && typeof window.FormBuilderAPI.getData === "function") {
            this.renderSummary(window.FormBuilderAPI.getData() || {});
            try {
              this.bindExportButton();
              this.bindLiveUpdates();
            } catch (_) {
            }
            try {
              this.bindLiveUpdates();
            } catch (_) {
            }
          }
        } catch (_) {
        }
        return;
      }
      this.__cg_inited = true;
      const data = formBuilder_default.getData();
      console.log("[SummaryAPI] init \u2014 builder state:", data);
      this.renderSummary(data);
      this.bindAutoRender();
      this.bindExportButton();
    },
    /**
     * Build and inject the full summary into #cg-summary-sheet.
     */
    renderSummary(data = {}) {
      const $sheet = $16("#cg-summary-sheet").empty();
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
    // CG HARDEN: live summary updates across tabs
    bindLiveUpdates() {
      if (this.__cg_live_bound)
        return;
      this.__cg_live_bound = true;
      const sel = "#cg-modal input, #cg-modal select, #cg-modal textarea";
      const self = this;
      function scheduleRender(e) {
        try {
          if (!document.getElementById("cg-summary-sheet"))
            return;
          const t = e && e.target;
          if (!t)
            return;
          if (t.closest && t.closest("#cg-summary-sheet"))
            return;
          clearTimeout(self.__cg_live_timer);
          self.__cg_live_timer = setTimeout(() => {
            try {
              const data = formBuilder_default && typeof formBuilder_default.getData === "function" ? formBuilder_default.getData() || {} : window.FormBuilderAPI && typeof window.FormBuilderAPI.getData === "function" ? window.FormBuilderAPI.getData() || {} : {};
              self.renderSummary(data);
            } catch (err) {
              console.warn("[SummaryAPI] live update failed", err);
            }
          }, 200);
        } catch (_) {
        }
      }
      $16(document).off("input.cgSummary change.cgSummary", sel).on("input.cgSummary change.cgSummary", sel, scheduleRender);
      try {
        this.renderSummary(formBuilder_default.getData() || {});
      } catch (_) {
      }
    },
    /**
     * Open a new window, inject the summary + CSS, and print it.
     */
    // CG HARDEN: bindAutoRender (live summary updates)
    // - Debounced re-render so typing doesn't spam heavy DOM work.
    _scheduleRender(delayMs = 150) {
      try {
        if (this.__cg_render_timer)
          clearTimeout(this.__cg_render_timer);
        this.__cg_render_timer = setTimeout(() => {
          var _a, _b;
          this.__cg_render_timer = null;
          try {
            const data = typeof formBuilder_default !== "undefined" && formBuilder_default.getData ? formBuilder_default.getData() || {} : ((_b = (_a = window.FormBuilderAPI) == null ? void 0 : _a.getData) == null ? void 0 : _b.call(_a)) || {};
            this.renderSummary(data);
          } catch (_) {
          }
        }, delayMs);
      } catch (_) {
      }
    },
    bindAutoRender() {
      if (this.__cg_autobound)
        return;
      this.__cg_autobound = true;
      try {
        if (this.__cg_auto_handler) {
          document.removeEventListener("input", this.__cg_auto_handler, true);
          document.removeEventListener("change", this.__cg_auto_handler, true);
        }
      } catch (_) {
      }
      this.__cg_auto_handler = (e) => {
        try {
          const t = e && e.target;
          if (!t)
            return;
          const modal = document.getElementById("cg-modal");
          if (!modal || !modal.contains(t))
            return;
          const isChange = e.type === "change";
          this._scheduleRender(isChange ? 0 : 150);
        } catch (_) {
        }
      };
      document.addEventListener("input", this.__cg_auto_handler, true);
      document.addEventListener("change", this.__cg_auto_handler, true);
    },
    // CG HARDEN: namespaced export click
    bindExportButton() {
      $16(document).off("click.cg", "#cg-export-pdf").on("click.cg", "#cg-export-pdf", (e) => {
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
  try {
    if (typeof window !== "undefined") {
      window.SummaryAPI = window.SummaryAPI || api_default3;
      if (window.SummaryAPI && typeof window.SummaryAPI.bindAutoRender === "function") {
        window.SummaryAPI.bindAutoRender();
      }
    }
  } catch (_) {
  }
  var summary_default = {
    init() {
      api_default3.init();
    }
  };

  // assets/js/src/core/main/builder-refresh.js
  var $17 = window.jQuery;
  function refreshTab() {
    const tab = String($17("#cg-modal .cg-tabs li.active").data("tab") || "");
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
  var $18 = window.jQuery;
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
      return $18.Deferred().resolve(_cacheRows).promise();
    }
    if (!force && _cacheRows && now - _cacheAt < CACHE_MS) {
      return $18.Deferred().resolve(_cacheRows).promise();
    }
    LOG2("fetching characters via AJAX\u2026", force ? "(force)" : "");
    const req = _getListRequest();
    if (!req) {
      ERR("No listCharacters()/fetchCharacters() available on FormBuilderAPI");
      return $18.Deferred().resolve([]).promise();
    }
    const d = $18.Deferred();
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
    const onFail = (xhr, status, err) => {
      ERR("character list request failed", status, err, xhr == null ? void 0 : xhr.status, xhr == null ? void 0 : xhr.responseText);
      finalize([]);
    };
    if (typeof req.then === "function") {
      Promise.resolve(req).then(onOk).catch((err) => {
        ERR("character list request failed", err);
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
    const $sel = $18("#cg-splash-load-select");
    if (!$sel.length)
      return;
    _populating = true;
    _suppressObserver = true;
    try {
      const current = String($sel.val() || "");
      $sel.empty();
      $sel.append($18("<option>", { value: "", text: "-- Select a character --" }));
      (rows || []).forEach((r) => {
        var _a, _b;
        const id = String((_a = r == null ? void 0 : r.id) != null ? _a : "");
        const name = String((_b = r == null ? void 0 : r.name) != null ? _b : "");
        if (!id)
          return;
        $sel.append($18("<option>", { value: id, text: name || `#${id}` }));
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
    const $sel = $18("#cg-splash-load-select");
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
  var $19 = window.jQuery;
  var LOG3 = (...a) => console.log("[BuilderSave]", ...a);
  var WARN2 = (...a) => console.warn("[BuilderSave]", ...a);
  function setSaveButtonsDisabled(disabled) {
    try {
      $19("#cg-modal .cg-save-button").prop("disabled", !!disabled).toggleClass("cg-disabled", !!disabled);
    } catch (_) {
    }
  }
  function bindSaveEvents() {
    $19(document).off("click", ".cg-save-button");
    $19(document).off("click", ".cg-close-after-save");
    $19(document).on("click.cg", ".cg-save-button", function(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      const $btn = $19(this);
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
  var $20 = window.jQuery;
  var LOG4 = (...a) => console.log("[BuilderEvents]", ...a);
  var SEL = {
    species: '#cg-species, select[name="species"], select[data-cg="species"], .cg-species',
    career: '#cg-career,  select[name="career"],  select[data-cg="career"],  .cg-career'
  };
  function firstSelect(selector) {
    const $sel = $20(selector);
    const $modalSel = $20("#cg-modal").find(selector);
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
      return $20(this).text() === val;
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
      return $20.Deferred().resolve().promise();
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
        return $20.Deferred().resolve().promise();
      const API = kind === "species" ? api_default : api_default2;
      if (typeof (API == null ? void 0 : API.populateSelect) !== "function")
        return $20.Deferred().resolve().promise();
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
    return $20.Deferred(function(dfr) {
      setTimeout(() => {
        ensureOptions().then(() => {
          doApply();
          dfr.resolve();
        }).catch(() => dfr.resolve());
      }, 0);
    }).promise();
  }
  function hydrateSpeciesAndCareer(opts = {}) {
    return $20.when(
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
    $20(document).off("input.cg change.cg", "#cg-modal input, #cg-modal select, #cg-modal textarea").on("input.cg change.cg", "#cg-modal input, #cg-modal select, #cg-modal textarea", function() {
      builder_ui_default.markDirty();
      const $el = $20(this);
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
    $20(document).off("click.cg", "#cg-open-builder").on("click.cg", "#cg-open-builder", (e) => {
      e.preventDefault();
      $20("#cg-modal-splash").removeClass("cg-hidden").addClass("visible");
      try {
        const $sel = $20("#cg-splash-load-select");
        const optCount = $sel.length ? $sel.find("option").length : 0;
        if ($sel.length && optCount <= 1) {
          document.dispatchEvent(new CustomEvent("cg:characters:refresh", { detail: { source: "splash-open" } }));
        }
      } catch (_) {
      }
    });
    $20(document).off("click.cg", "#cg-new-splash").on("click.cg", "#cg-new-splash", (e) => {
      e.preventDefault();
      $20("#cg-modal-splash").removeClass("visible").addClass("cg-hidden");
      builder_ui_default.openBuilder({ isNew: true, payload: {} });
      formBuilder_default._data.skillMarks = {};
      formBuilder_default._data.species = "";
      formBuilder_default._data.career = "";
      if (window.CG_FreeChoicesState) {
        window.CG_FreeChoicesState.selected = ["", "", ""];
      }
    });
    $20(document).off("click.cg", "#cg-load-splash").on("click.cg", "#cg-load-splash", (e) => {
      e.preventDefault();
      const charId = $20("#cg-splash-load-select").val();
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
        $20("#cg-modal-splash").removeClass("visible").addClass("cg-hidden");
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
    $20(document).off("click.cg", "#cg-modal .cg-tabs li").on("click.cg", "#cg-modal .cg-tabs li", function(e) {
      e.preventDefault();
      const fromTab = $20("#cg-modal .cg-tabs li.active").data("tab");
      const tabName = $20(this).data("tab");
      $20("#cg-modal .cg-tabs li").removeClass("active");
      $20(this).addClass("active");
      $20(".tab-panel").removeClass("active");
      $20(`#${tabName}`).addClass("active");
      emitTabChanged(fromTab, tabName);
      refreshTab();
      setTimeout(() => {
        hydrateSpeciesAndCareer({ force: false });
      }, 0);
    });
    $20(document).off("click.cg", "#cg-modal-close").on("click.cg", "#cg-modal-close", (e) => {
      e.preventDefault();
      builder_ui_default.showUnsaved();
    });
    $20(document).off("click.cg", "#cg-modal-overlay").on("click.cg", "#cg-modal-overlay", function(e) {
      if (e.target !== this)
        return;
      builder_ui_default.showUnsaved();
    });
    $20(document).off("click.cg", "#unsaved-save").on("click.cg", "#unsaved-save", (e) => {
      e.preventDefault();
      console.log("[BuilderEvents] Prompt: SAVE & EXIT clicked");
      formBuilder_default.save(true);
    });
    $20(document).off("click.cg", "#unsaved-exit").on("click.cg", "#unsaved-exit", (e) => {
      e.preventDefault();
      builder_ui_default.closeBuilder();
    });
    $20(document).off("click.cg", "#unsaved-cancel").on("click.cg", "#unsaved-cancel", (e) => {
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
      } catch (err) {
        console.error("[Core] MainAPI.init() failed", err);
        throw err;
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
    } catch (err) {
      console.error("[Core] SkillsModule.init() failed", err);
    }
  }
  if (typeof window !== "undefined") {
    window.SpeciesAPI = api_default;
    window.CG_Core = Core;
    window.CG_initCore = initCore;
  }
})();
//# sourceMappingURL=core.bundle.js.map
