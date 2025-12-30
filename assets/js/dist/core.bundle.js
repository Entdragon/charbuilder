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

  // assets/js/src/core/utils/bind-once.js
  var $ = window.jQuery;

  // assets/js/src/core/gifts/state.js
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
     * Merge incoming gift objects into our master list,
     * keeping manifold, requires, etc.
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
  window.CG_FreeChoicesState = State;
  var state_default = State;

  // assets/js/src/core/species/api.js
  var log = (...a) => console.log("[SpeciesAPI]", ...a);
  var warn = (...a) => console.warn("[SpeciesAPI]", ...a);
  var $2 = window.jQuery;
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
    return $2.post(url, data).then((res) => {
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
     * @returns {jqXHR|Promise<array>}
     */
    getList(force = false) {
      if (!force) {
        const pre = preloadedList();
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
      const { ajax_url, nonce } = ajaxEnv();
      if (!ajax_url) {
        warn("No AJAX URL available; returning empty list.");
        return $2.Deferred().resolve([]).promise();
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
      const p = postJSON(ajax_url, payload).then((res) => {
        const listRaw = Array.isArray(res) ? res : (res == null ? void 0 : res.data) || [];
        const list = normalizeList(listRaw);
        this._cache.list = list;
        log("Species list fetched:", list.length);
        return list;
      }).fail((xhr, status, err2) => {
        warn("AJAX species list failed:", status, err2, xhr == null ? void 0 : xhr.responseText);
        return [];
      }).always(() => {
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
     * @returns {Promise<HTMLElement|null>}
     */
    populateSelect(sel, { force = false } = {}) {
      const el = sel instanceof Element ? sel : document.querySelector(sel);
      if (!el)
        return $2.Deferred().resolve(null).promise();
      const prior = el.value || "";
      el.innerHTML = "";
      const ph = document.createElement("option");
      ph.value = "";
      ph.textContent = "\u2014 Select Species \u2014";
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
     * Fetch the full profile for one species (gifts, skills, etc).
     * Caches to `currentProfile` and emits a DOM event.
     * @param {string|number} speciesId
     * @returns {jqXHR|Promise<object|null>}
     */
    fetchProfile(speciesId) {
      if (!speciesId) {
        this.currentProfile = null;
        return $2.Deferred().resolve(null).promise();
      }
      const { ajax_url, nonce } = ajaxEnv();
      if (!ajax_url) {
        warn("No AJAX URL available for fetchProfile.");
        return $2.Deferred().resolve(null).promise();
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
      return postJSON(ajax_url, payload).then((res) => {
        const profile = (res == null ? void 0 : res.data) || res || null;
        this.currentProfile = profile || null;
        document.dispatchEvent(new CustomEvent("cg:species:profile", {
          detail: { id: String(speciesId), profile: this.currentProfile }
        }));
        return this.currentProfile;
      }).fail((xhr, status, err2) => {
        warn("AJAX species profile failed:", status, err2, xhr == null ? void 0 : xhr.responseText);
        this.currentProfile = null;
        return null;
      });
    }
  };
  if (typeof window !== "undefined") {
    window.SpeciesAPI = SpeciesAPI;
  }
  var api_default = SpeciesAPI;

  // assets/js/src/core/career/api.js
  var log2 = (...a) => console.log("[CareerAPI]", ...a);
  var warn2 = (...a) => console.warn("[CareerAPI]", ...a);
  var $3 = window.jQuery;
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
        return {
          id: String((_b = (_a = it.id) != null ? _a : it.value) != null ? _b : ""),
          name: String((_d = (_c = it.name) != null ? _c : it.title) != null ? _d : "")
        };
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
    return $3.post(url, data).then((res) => {
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
          return $3.Deferred().resolve(this._cache.list).promise();
        }
      }
      if (!force && this._cache.list) {
        return $3.Deferred().resolve(this._cache.list).promise();
      }
      if (this._cache.listPromise) {
        return this._cache.listPromise;
      }
      const { ajax_url, nonce } = ajaxEnv2();
      if (!ajax_url) {
        warn2("No AJAX URL available; returning empty list.");
        return $3.Deferred().resolve([]).promise();
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
        return $3.Deferred().resolve(null).promise();
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
      if (!careerId) {
        this.currentProfile = null;
        return $3.Deferred().resolve(null).promise();
      }
      const { ajax_url, nonce } = ajaxEnv2();
      if (!ajax_url) {
        warn2("No AJAX URL available for fetchProfile.");
        return $3.Deferred().resolve(null).promise();
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
          this.currentProfile = null;
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
  var TraitsService = {
    TRAITS,
    DICE_TYPES,
    /**
     * Build a map: { traitKey: totalBoostCount }
     * counting free, species, and career gifts, including any manifold multipliers.
     */
    calculateBoostMap() {
      const map = {};
      function addGift(giftId) {
        if (!giftId)
          return;
        const gift = state_default.getGiftById(giftId);
        if (!gift)
          return;
        const traitKey = BOOSTS[gift.id];
        if (!traitKey)
          return;
        const count = parseInt(gift.ct_gifts_manifold, 10) || 1;
        map[traitKey] = (map[traitKey] || 0) + count;
      }
      state_default.selected.forEach(addGift);
      const sp = api_default.currentProfile;
      if (sp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((key) => {
          addGift(sp[key]);
        });
      }
      const cp = api_default2.currentProfile;
      if (cp) {
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((key) => {
          addGift(cp[key]);
        });
      }
      console.log("[Traits] boost map \u2192", map);
      return map;
    },
    /**
     * Enforce dice‐count limits on the trait selects.
     */
    enforceCounts() {
      const $18 = window.jQuery;
      const freq = { d8: 0, d6: 0, d4: 0 };
      $18(".cg-trait-select").each(function() {
        const v = $18(this).val();
        if (v && v in freq)
          freq[v]++;
      });
      $18(".cg-trait-select").each(function() {
        const $sel = $18(this);
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
    /**
     * Update the “adjusted” labels under each trait select.
     */
    updateAdjustedDisplays() {
      const $18 = window.jQuery;
      const boosts = this.calculateBoostMap();
      TRAITS.forEach((traitKey) => {
        const $sel = $18(`#cg-${traitKey}`);
        const base = $sel.val() || "d4";
        const idx = DIE_ORDER.indexOf(base);
        const count = boosts[traitKey] || 0;
        const boosted = DIE_ORDER[Math.min(idx + count, DIE_ORDER.length - 1)];
        $18(`#cg-${traitKey}-adjusted`).text(boosted);
      });
    },
    /**
     * Re-run both counts & adjusted displays.
     */
    refreshAll() {
      this.enforceCounts();
      this.updateAdjustedDisplays();
    },
    /**
     * Get a single trait’s boosted die (for external use).
     */
    getBoostedDie(traitKey) {
      const boosts = this.calculateBoostMap();
      const cnt = boosts[traitKey] || 0;
      if (!cnt)
        return "";
      const base = window.jQuery(`#cg-${traitKey}`).val() || "d4";
      const idx = DIE_ORDER.indexOf(base);
      return DIE_ORDER[Math.min(idx + cnt, DIE_ORDER.length - 1)];
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
        <li data-tab="tab-traits" class="active">Details & Traits</li>
        <li data-tab="tab-profile">Profile: Species, Career & Gifts</li>
        <li data-tab="tab-skills">Skills</li>
        <li data-tab="tab-summary">Summary</li>
      </ul>
    `;
    },
    renderContent(data = {}) {
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
          <label>${label} <small>(choose one)</small></label>
          <select id="cg-${trait}" class="cg-trait-select">
            <option value="">&mdash; Select &mdash;</option>
            ${options}
          </select>
          <div
            class="trait-adjusted"
            id="cg-${trait}-adjusted"
            style="color:#0073aa;font-weight:bold;"
          ></div>
        </div>
      `;
      }).join("");
      return `
      <div id="tab-traits" class="tab-panel active">
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
                  <option value="Male"     ${data.gender === "Male" ? "selected" : ""}>Male</option>
                  <option value="Female"   ${data.gender === "Female" ? "selected" : ""}>Female</option>
                  <option value="Nonbinary"${data.gender === "Nonbinary" ? "selected" : ""}>Nonbinary</option>
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

          <div class="cg-traits-box">
            <h3>Traits</h3>
            <div class="cg-profile-grid">
              ${traitFields}
            </div>
          </div>

          <div class="cg-text-box">
            <h3>Description & Backstory</h3>
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
    renderContent(data = {}) {
      return `
      <div id="tab-profile" class="tab-panel">

        <div class="cg-profile-box">
          <h3>Species and Career</h3>
          <label for="cg-species">Species</label>
          <select 
            id="cg-species" 
            class="cg-profile-select" 
            data-selected="${data.species_id || ""}"
          ></select>
          <ul id="species-gifts" class="cg-gift-item"></ul>

          <label for="cg-career">Career</label>
          <select 
            id="cg-career" 
            class="cg-profile-select" 
            data-selected="${data.career_id || ""}"
          ></select>
          <div class="trait-adjusted" id="cg-trait_career-adjusted"></div>
          <div id="cg-extra-careers" class="cg-profile-grid"></div>
        </div>

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
    `;
    }
  };

  // assets/js/src/core/formBuilder/render-skills.js
  function escape2(str = "") {
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }
  var render_skills_default = {
    renderContent(data = {}) {
      var _a, _b;
      const speciesName = ((_a = api_default.currentProfile) == null ? void 0 : _a.species_name) || "";
      const careerName = ((_b = api_default2.currentProfile) == null ? void 0 : _b.career_name) || "";
      const extraCareers = data.extraCareers || [];
      const skillList = data.skillsList || [];
      const careerCols = [`<th>Career: ${escape2(careerName)}</th>`].concat(
        extraCareers.map(
          (c, i) => `<th>Career ${i + 2}: ${escape2(c.name)}</th>`
        )
      ).join("");
      const rows = skillList.map((skill) => {
        var _a2;
        return `
      <tr data-skill-id="${skill.id}">
        <td>${escape2(skill.name)}</td>
        <td class="skill-species-die"></td>
        ${extraCareers.map(() => "<td></td>").join("")}
        <td>
          <input
            type="number"
            class="skill-marks"
            data-skill-id="${skill.id}"
            min="0"
            value="${escape2(((_a2 = data.skillMarks) == null ? void 0 : _a2[skill.id]) || 0)}"
          />
        </td>
        <td class="skill-total">\u2014</td>
      </tr>
    `;
      }).join("");
      return `
      <div id="tab-skills" class="tab-panel">
        <table id="skills-table" class="cg-skills-table">
          <thead>
            <tr>
              <th>Skill Name</th>
              <th>Species: ${escape2(speciesName)}</th>
              ${careerCols}
              <th>Skill Marks</th>
              <th>Dice Pool</th>
            </tr>
          </thead>
          <tbody>
            ${rows}
          </tbody>
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
  function ajaxEnv3() {
    var _a, _b;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    return {
      url: env.ajax_url || window.ajaxurl || ((_b = (_a = document.body) == null ? void 0 : _a.dataset) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php",
      nonce: env.nonce || env.security || window.CG_NONCE || null
    };
  }
  function normalizeCore(raw = {}) {
    var _a, _b, _c, _d, _e, _f, _g, _h, _i, _j;
    const species = (_c = (_b = (_a = raw.species) != null ? _a : raw.species_id) != null ? _b : raw.trait_species) != null ? _c : "";
    const career = (_f = (_e = (_d = raw.career) != null ? _d : raw.career_id) != null ? _e : raw.trait_career) != null ? _f : "";
    return {
      id: raw.id || "",
      name: raw.name || "",
      player_name: raw.player_name || "",
      age: raw.age || "",
      gender: raw.gender || "",
      motto: raw.motto || "",
      will: (_g = raw.will) != null ? _g : "",
      body: (_h = raw.body) != null ? _h : "",
      mind: (_i = raw.mind) != null ? _i : "",
      speed: (_j = raw.speed) != null ? _j : "",
      species,
      career,
      // optional structured blobs (keep them available to PHP if needed)
      skill_marks: raw.skillMarks ? JSON.stringify(raw.skillMarks) : "",
      traits_list: raw.traitsList ? JSON.stringify(raw.traitsList) : "",
      skills_list: raw.skillsList ? JSON.stringify(raw.skillsList) : "",
      gifts: raw.gifts ? JSON.stringify(raw.gifts) : "",
      // keep the free_gifts array as-is if present
      free_gifts: Array.isArray(raw.free_gifts) ? raw.free_gifts : void 0
    };
  }
  function buildPayload(raw) {
    const core = normalizeCore(raw);
    const { nonce } = ajaxEnv3();
    const base = { action: "cg_save_character" };
    if (nonce) {
      base.security = nonce;
      base.nonce = nonce;
      base._ajax_nonce = nonce;
    }
    const flat = __spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues({}, base), core.id ? { id: core.id } : {}), core.name ? { name: core.name } : {}), core.player_name ? { player_name: core.player_name } : {}), core.age ? { age: core.age } : {}), core.gender ? { gender: core.gender } : {}), core.motto ? { motto: core.motto } : {}), core.will !== "" ? { will: core.will } : {}), core.body !== "" ? { body: core.body } : {}), core.mind !== "" ? { mind: core.mind } : {}), core.speed !== "" ? { speed: core.speed } : {}), core.species ? { species: core.species } : {}), core.career ? { career: core.career } : {}), core.skill_marks ? { skill_marks: core.skill_marks } : {}), core.traits_list ? { traits_list: core.traits_list } : {}), core.skills_list ? { skills_list: core.skills_list } : {}), core.gifts ? { gifts: core.gifts } : {});
    const character = {};
    if (core.id)
      character.id = core.id;
    if (core.name)
      character.name = core.name;
    if (core.player_name)
      character.player_name = core.player_name;
    if (core.age)
      character.age = core.age;
    if (core.gender)
      character.gender = core.gender;
    if (core.motto)
      character.motto = core.motto;
    if (core.will !== "")
      character.will = core.will;
    if (core.body !== "")
      character.body = core.body;
    if (core.mind !== "")
      character.mind = core.mind;
    if (core.speed !== "")
      character.speed = core.speed;
    if (core.species)
      character.species = core.species;
    if (core.career)
      character.career = core.career;
    if (core.skill_marks)
      character.skill_marks = core.skill_marks;
    if (core.traits_list)
      character.traits_list = core.traits_list;
    if (core.skills_list)
      character.skills_list = core.skills_list;
    if (core.gifts)
      character.gifts = core.gifts;
    if (Array.isArray(core.free_gifts))
      character.free_gifts = core.free_gifts;
    flat.character = character;
    flat.character_json = JSON.stringify(__spreadValues({}, core));
    return flat;
  }
  var FormBuilderAPI = {
    _data: {},
    isNew: true,
    hasData: false,
    /**
     * Initialize the builder state and render the form.
     *
     * @param {Object} payload
     */
    init(payload = {}) {
      this._data = __spreadValues({}, payload);
      this.isNew = Boolean(payload.isNew);
      this.hasData = !this.isNew;
      $5("#cg-form-container").html(
        form_builder_default.buildForm(this._data)
      );
    },
    /**
     * Return a shallow copy of the in-memory data.
     */
    getData() {
      return __spreadValues({}, this._data);
    },
    /**
     * Read every form field from the DOM into a single payload object,
     * merging in-memory skillMarks to avoid losing them when inputs
     * aren't present on the current tab.
     */
    collectFormData() {
      const d = {};
      if (this._data.id) {
        d.id = this._data.id;
      }
      d.name = $5("#cg-name").val();
      d.player_name = $5("#cg-player-name").val();
      d.age = $5("#cg-age").val();
      d.gender = $5("#cg-gender").val();
      d.motto = $5("#cg-motto").val();
      d.goal1 = $5("#cg-goal1").val();
      d.goal2 = $5("#cg-goal2").val();
      d.goal3 = $5("#cg-goal3").val();
      d.description = $5("#cg-description").val();
      d.backstory = $5("#cg-backstory").val();
      const speciesVal = $5("#cg-species").val() || "";
      const careerVal = $5("#cg-career").val() || "";
      d.species_id = speciesVal;
      d.career_id = careerVal;
      d.species = speciesVal;
      d.career = careerVal;
      service_default.TRAITS.forEach((key) => {
        d[key] = $5(`#cg-${key}`).val();
      });
      const mergedMarks = __spreadValues({}, this._data.skillMarks || {});
      $5("input.skill-marks").each((i, el) => {
        const skillId = $5(el).data("skill-id");
        const val = parseInt($5(el).val(), 10) || 0;
        mergedMarks[skillId] = val;
      });
      d.skillMarks = mergedMarks;
      d.free_gifts = [
        $5("#cg-free-choice-0").val() || "",
        $5("#cg-free-choice-1").val() || "",
        $5("#cg-free-choice-2").val() || ""
      ];
      if (Array.isArray(window.CG_SKILLS_LIST))
        d.skillsList = window.CG_SKILLS_LIST;
      return d;
    },
    /**
     * Save the character via WP-AJAX and optionally close builder.
     * Posts flat + nested character[...] + JSON as a compatibility belt & suspenders.
     *
     * @param {boolean} shouldClose
     * @returns {Promise}
     */
    save(shouldClose = false) {
      const raw = this.collectFormData();
      console.log("[FormBuilderAPI] \u25B6 save()", raw);
      const { url } = ajaxEnv3();
      if (!url) {
        console.error("[FormBuilderAPI] save(): No AJAX URL available");
        alert("Save error: missing AJAX URL");
        return $5.Deferred().reject("no-url").promise();
      }
      const data = buildPayload(raw);
      return $5.post(url, data).done((res) => {
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
      });
    },
    /**
     * Fetch a list of saved characters (for the Load splash).
     */
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
    /**
     * Fetch one character’s full data by ID.
     *
     * @param {number|string} id
     */
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
  function renderGiftList2($ul, items = []) {
    if (!$ul || !$ul.length)
      return;
    $ul.empty();
    items.forEach((txt) => {
      if (!txt)
        return;
      $ul.append(`<li>${txt}</li>`);
    });
  }
  function namesFromProfile2(profile = {}) {
    const names = [profile.gift_1, profile.gift_2, profile.gift_3].map((v) => v == null ? "" : String(v)).filter(Boolean);
    return names.map((name, i) => {
      const m = profile[`manifold_${i + 1}`];
      const mult = parseInt(m, 10) || 1;
      return mult > 1 ? `${name} \xD7 ${mult}` : name;
    });
  }
  function bindCareerEvents() {
    if (_bound2)
      return;
    _bound2 = true;
    $7(document).off("change.cg", "#cg-career").on("change.cg", "#cg-career", (e) => {
      const val = e.currentTarget && e.currentTarget.value || "";
      console.log("[CareerEvents] selected career \u2192", val);
      if (!val) {
        api_default2.currentProfile = null;
        renderGiftList2($7("#career-gifts"), []);
        $7(document).trigger("cg:career:changed", [{ id: "", profile: null }]);
        return;
      }
      api_default2.fetchProfile(val).done((profileRaw) => {
        const profile = normalizeCareerProfile(profileRaw || {});
        api_default2.currentProfile = profile;
        const $ul = $7("#career-gifts");
        const fromProfile = namesFromProfile2(profile);
        if (fromProfile.length) {
          renderGiftList2($ul, fromProfile);
          $7(document).trigger("cg:career:changed", [{ id: String(val), profile }]);
        } else {
          api_default2.fetchGifts(val).done((gifts) => {
            const giftNames = [].concat(gifts || []).map((g) => g && typeof g === "object" ? g.name || g.title || g.ct_gift_name || "" : String(g)).filter(Boolean);
            renderGiftList2($ul, giftNames);
            $7(document).trigger("cg:career:changed", [{ id: String(val), profile }]);
          });
        }
      });
    });
  }

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
      var _a, _b;
      const sel = document.querySelector("#cg-career");
      if (!sel)
        return;
      const force = !!opts.force || sel.options.length <= 1;
      return (_b = (_a = api_default2.populateSelect(sel, { force })).catch) == null ? void 0 : _b.call(_a, () => {
      });
    }
  };
  var career_default = CareerIndex;

  // assets/js/src/core/main/builder-ui.js
  var $8 = window.jQuery;
  var isDirty = false;
  var SELECTORS = {
    species: ['select[name="species"]', 'select[data-cg="species"]', "select.cg-species"],
    career: ['select[name="career"]', 'select[data-cg="career"]', "select.cg-career"]
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
          $8(speciesEl).val(String(want));
          if ($8(speciesEl).val() !== String(want)) {
            const byName = [...speciesEl.options].find((o) => o.textContent === String(want));
            if (byName)
              $8(speciesEl).val(byName.value);
          }
          $8(speciesEl).trigger("change");
        }
      }
      if (careerEl) {
        const want = (_d = (_c = record.career) != null ? _c : record.career_id) != null ? _d : "";
        if (want) {
          $8(careerEl).val(String(want));
          if ($8(careerEl).val() !== String(want)) {
            const byName = [...careerEl.options].find((o) => o.textContent === String(want));
            if (byName)
              $8(careerEl).val(byName.value);
          }
          $8(careerEl).trigger("change");
        }
      }
    });
  }
  function openBuilder({ isNew = false, payload = {} } = {}) {
    var _a, _b;
    console.log("[BuilderUI] openBuilder()", { isNew, payload });
    isDirty = false;
    $8("#cg-unsaved-confirm, #cg-unsaved-backdrop").hide().css("display", "none");
    try {
      (_b = (_a = formBuilder_default) == null ? void 0 : _a.init) == null ? void 0 : _b.call(_a, __spreadProps(__spreadValues({}, payload), { isNew }));
    } catch (e) {
      console.error("[BuilderUI] FormBuilderAPI.init error", e);
    }
    $8("#cg-modal-overlay, #cg-modal").removeClass("cg-hidden").addClass("cg-visible").css("display", "block");
    if (isNew)
      $8('#cg-modal .cg-tabs li[data-tab="tab-traits"]').trigger("click");
    ensureListsThenApply(payload).then(() => {
      document.dispatchEvent(new CustomEvent("cg:builder:opened", { detail: { isNew, payload } }));
    });
  }
  function closeBuilder() {
    $8("#cg-unsaved-backdrop").hide().css("display", "none");
    $8("#cg-unsaved-confirm").removeClass("cg-visible").addClass("cg-hidden").hide().css("display", "none");
    $8("#cg-modal, #cg-modal-overlay").removeClass("cg-visible").addClass("cg-hidden").fadeOut(200, () => {
      $8("#cg-form-container").empty();
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
    if (!$8("#cg-unsaved-backdrop").length)
      $8('<div id="cg-unsaved-backdrop"></div>').appendTo("body");
    const $b = $8("#cg-unsaved-backdrop");
    const $p = $8("#cg-unsaved-confirm");
    if (!$p.parent().is("body"))
      $p.appendTo("body");
    $b.show().css("display", "block");
    $p.removeClass("cg-hidden").addClass("cg-visible").show().css("display", "block");
  }
  function hideUnsaved() {
    $8("#cg-unsaved-backdrop").hide().css("display", "none");
    $8("#cg-unsaved-confirm").removeClass("cg-visible").addClass("cg-hidden").hide().css("display", "none");
  }
  document.addEventListener("cg:character:loaded", (ev) => {
    const record = (ev == null ? void 0 : ev.detail) || {};
    console.log("[BuilderUI] cg:character:loaded \u2192 rehydrate for record", record);
    ensureListsThenApply(record);
  });
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
  var $9 = window.jQuery;
  var bound = false;
  var events_default = {
    bind() {
      if (bound)
        return;
      bound = true;
      $9(function() {
        service_default.refreshAll();
      });
      $9(document).off("change", ".cg-trait-select").on("change", ".cg-trait-select", () => {
        service_default.refreshAll();
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
  var $10 = window.jQuery;
  var log3 = (...a) => window.CG_DEBUG_GIFTS ? console.log("[FreeChoices]", ...a) : null;
  var warn3 = (...a) => console.warn("[FreeChoices]", ...a);
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
  var ALWAYS_ACQUIRED_GIFT_IDS = ["242", "236"];
  function ajaxEnv4() {
    var _a, _b;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    const url = env.ajax_url || window.ajaxurl || ((_b = (_a = document.body) == null ? void 0 : _a.dataset) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php";
    const perAction = window.CG_NONCES && window.CG_NONCES.cg_get_free_gifts ? window.CG_NONCES.cg_get_free_gifts : null;
    const generic = env.nonce || env.security || env._ajax_nonce || window.CG_NONCE || null;
    return { url, nonce: perAction || generic };
  }
  function parseJsonMaybe(res) {
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
    // placeholder (#cg-free-choices or fallback section)
    _host: null,
    // row wrapper
    _selects: [],
    _allGifts: [],
    // normalized full gifts from endpoint (includes requires/allows_multiple)
    _refreshInFlight: false,
    _lastRefreshAt: 0,
    _retryCount: 0,
    _maxRetries: 2,
    _mountTimer: null,
    _mountTries: 0,
    _maxMountTries: 80,
    // 80 * 250ms = 20s
    _suppressEmit: false,
    init() {
      if (this._inited)
        return;
      this._inited = true;
      window.CG_FormBuilderAPI = formBuilder_default;
      try {
        state_default.init();
      } catch (_) {
      }
      this._ensureMounted();
      document.addEventListener("cg:builder:opened", () => {
        this._ensureMounted();
        this.refresh({ force: false });
      });
      if ($10) {
        $10(document).off("cg:species:changed.cgfree cg:career:changed.cgfree cg:free-gift:changed.cgfree").on("cg:species:changed.cgfree cg:career:changed.cgfree cg:free-gift:changed.cgfree", () => {
          this._fillSelectsFiltered();
        });
      }
      document.addEventListener("cg:free-gift:changed", () => this._fillSelectsFiltered());
      if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => this._ensureMounted());
      }
    },
    _resetMount() {
      this._mounted = false;
      this._root = null;
      this._host = null;
      this._selects = [];
    },
    _ensureMounted() {
      if (this._mounted && (!inDom(this._root) || !inDom(this._host))) {
        warn3("mounted root was detached; remounting");
        this._resetMount();
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
          clearInterval(this._mountTimer);
          this._mountTimer = null;
          this.refresh({ force: false });
          return;
        }
        if (this._mountTries >= this._maxMountTries) {
          clearInterval(this._mountTimer);
          this._mountTimer = null;
        }
      }, 250);
      return false;
    },
    _tryMount() {
      let root = document.querySelector("#cg-free-choices");
      if (!root) {
        const container = document.querySelector("#cg-form-container");
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
      }
      row.style.display = "flex";
      row.style.flexWrap = "wrap";
      row.style.gap = "12px";
      row.style.marginTop = "8px";
      row.style.width = "100%";
      row.style.maxWidth = "100%";
      row.style.boxSizing = "border-box";
      row.style.alignItems = "flex-start";
      const ensureSelect = (slot) => {
        const id = `cg-free-choice-${slot}`;
        let sel = row.querySelector(`#${id}`);
        if (!sel) {
          sel = document.createElement("select");
          sel.id = id;
          sel.className = "cg-free-select";
          sel.setAttribute("data-slot", String(slot));
          row.appendChild(sel);
        }
        sel.style.flex = "1 1 240px";
        sel.style.minWidth = "200px";
        sel.style.maxWidth = "100%";
        sel.style.width = "100%";
        sel.style.boxSizing = "border-box";
        return sel;
      };
      const s0 = ensureSelect(0);
      const s1 = ensureSelect(1);
      const s2 = ensureSelect(2);
      this._root = root;
      this._host = row;
      this._selects = [s0, s1, s2];
      this._selects.forEach((sel) => {
        if ($10) {
          $10(sel).off("change.cgfree").on("change.cgfree", () => this._onSelectChange(sel));
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
      if ($10)
        $10(document).trigger("cg:free-gift:changed", [{ free_gifts: normalized }]);
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
      ALWAYS_ACQUIRED_GIFT_IDS.forEach((id) => set.add(String(id)));
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
      let changed = false;
      for (let pass = 0; pass < 3; pass++) {
        changed = false;
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
        if (prior && options.some((o) => o.id === String(prior))) {
          sel.value = String(prior);
        } else {
          sel.value = "";
        }
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
      if (!this._mounted)
        return;
      const now = Date.now();
      if (!force && now - this._lastRefreshAt < 3e3)
        return;
      if (this._refreshInFlight)
        return;
      this._refreshInFlight = true;
      const { url, nonce } = ajaxEnv4();
      const payload = { action: "cg_get_free_gifts" };
      if (nonce) {
        payload.security = nonce;
        payload.nonce = nonce;
        payload._ajax_nonce = nonce;
      }
      const onDone = (res) => {
        this._refreshInFlight = false;
        this._lastRefreshAt = Date.now();
        const json = parseJsonMaybe(res);
        if (!json || json.success !== true || !Array.isArray(json.data)) {
          warn3("Unexpected response:", json);
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
        this._fillSelectsFiltered();
      };
      const onFail = (status, errorText, responseText) => {
        this._refreshInFlight = false;
        this._lastRefreshAt = Date.now();
        err("AJAX failed", status, errorText, responseText || "");
        this._drawPlaceholders();
        this._maybeRetry();
      };
      if ($10 && $10.post) {
        $10.post(url, payload).done(onDone).fail((xhr, status, e) => onFail(status, e, xhr == null ? void 0 : xhr.responseText));
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
      if (!this._mounted)
        return;
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
  var $11 = window.jQuery;
  var MARK_DIE = {
    1: "d4",
    2: "d6",
    3: "d8"
  };
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
  var render_default = {
    render() {
      const data = formBuilder_default.getData();
      const skills = data.skillsList || window.CG_SKILLS_LIST || [];
      const species = api_default.currentProfile || {};
      const career = api_default2.currentProfile || {};
      data.skillMarks = data.skillMarks || {};
      const MAX_MARKS = 13;
      const usedMarks = Object.values(data.skillMarks).reduce((sum, v) => sum + (parseInt(v, 10) || 0), 0);
      const marksRemain = Math.max(0, MAX_MARKS - usedMarks);
      $11("#marks-remaining").remove();
      $11("#skills-table").before(`
      <div id="marks-remaining" class="marks-remaining">
        Marks Remaining: <strong>${marksRemain}</strong>
      </div>
    `);
      const $thead = $11("<thead>");
      $11("<tr>").append("<th>Skill</th>").append(`<th>${speciesNameOf(species) || ""}</th>`).append(`<th>${careerNameOf(career) || ""}</th>`).append("<th>Marks</th>").append("<th>Dice Pool</th>").appendTo($thead);
      const spSkills = extractSkillTripletFromAny(species).map(String);
      const cpSkills = extractSkillTripletFromAny(career).map(String);
      const $tbody = $11("<tbody>");
      skills.forEach((skill) => {
        const id = String(skill.id);
        const name = skill.name;
        const spDie = spSkills.includes(id) ? "d4" : "";
        const cpDie = cpSkills.includes(id) ? "d6" : "";
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
        const poolDice = [spDie, cpDie, markDie].filter(Boolean);
        const poolStr = poolDice.length ? poolDice.join(" + ") : "\u2013";
        const $row = $11("<tr>").append(`<td>${name}</td>`).append(`<td>${spDie || "\u2013"}</td>`).append(`<td>${cpDie || "\u2013"}</td>`).append(`<td>
                   <div class="marks-buttons">${buttonsHtml}</div>
                   <div class="marks-display">${markDisplay}</div>
                 </td>`).append(`<td>${poolStr}</td>`);
        $tbody.append($row);
      });
      $11("#skills-table").empty().append($thead).append($tbody);
    }
  };

  // assets/js/src/core/skills/events.js
  var $12 = window.jQuery;
  var events_default2 = {
    bind() {
      $12(document).off("click", "#tab-skills, #cg-species, #cg-career").on("click change", "#tab-skills, #cg-species, #cg-career", () => {
        render_default.render();
      });
      $12(document).off("click", ".skill-mark-btn").on("click", ".skill-mark-btn", function() {
        const skillId = String($12(this).data("skill-id"));
        const mark = parseInt($12(this).data("mark"), 10);
        const data = formBuilder_default.getData();
        data.skillMarks = data.skillMarks || {};
        data.skillMarks[skillId] = mark;
        render_default.render();
      });
    }
  };

  // assets/js/src/core/skills/index.js
  var skills_default = {
    init() {
      console.log("[SkillsIndex] init \u2014 builder state:", formBuilder_default._data);
      if (!Array.isArray(formBuilder_default._data.skillsList)) {
        formBuilder_default._data.skillsList = window.CG_SKILLS_LIST || [];
      }
      if (typeof formBuilder_default._data.skillMarks !== "object") {
        formBuilder_default._data.skillMarks = {};
      }
      events_default2.bind();
      render_default.render();
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
  var $13 = window.jQuery;
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
      const $sheet = $13("#cg-summary-sheet").empty();
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
      $13(document).off("click", "#cg-export-pdf").on("click", "#cg-export-pdf", (e) => {
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
  var $14 = window.jQuery;
  function refreshTab() {
    const tab = $14("#cg-modal .cg-tabs li.active").data("tab");
    switch (tab) {
      case "tab-traits":
        traits_default.init();
        break;
      case "tab-profile":
        species_default.init();
        career_default.init();
        gifts_default.init();
        break;
      case "tab-skills":
        skills_default.init();
        break;
      case "tab-summary":
        summary_default.init();
        break;
    }
  }

  // assets/js/src/core/main/builder-load.js
  var $15 = window.jQuery;
  var LOG = (...a) => console.log("[BuilderLoad]", ...a);
  var WARN = (...a) => console.warn("[BuilderLoad]", ...a);
  var _inited = false;
  var _observer = null;
  var _inFlight = false;
  function nonceFor(action) {
    const per = window.CG_NONCES && window.CG_NONCES[action] ? window.CG_NONCES[action] : null;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    return per || env.nonce || env.security || env._ajax_nonce || window.CG_NONCE || null;
  }
  function ajaxUrl() {
    var _a, _b;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    return env.ajax_url || window.ajaxurl || ((_b = (_a = document.body) == null ? void 0 : _a.dataset) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php";
  }
  function parseJsonMaybe2(res) {
    try {
      return typeof res === "string" ? JSON.parse(res) : res;
    } catch (_) {
      return res;
    }
  }
  function normalizeItems(arr) {
    if (!Array.isArray(arr))
      return [];
    return arr.map((it) => {
      var _a, _b, _c, _d;
      if (it && typeof it === "object") {
        return { id: String((_b = (_a = it.id) != null ? _a : it.value) != null ? _b : ""), name: String((_d = (_c = it.name) != null ? _c : it.title) != null ? _d : "") };
      }
      return { id: String(it), name: String(it) };
    }).filter((it) => it.id && it.name);
  }
  function getSelect() {
    if (!$15)
      return null;
    const $sel = $15("#cg-splash-load-select").first();
    return $sel && $sel.length ? $sel : null;
  }
  function populateSelect($sel, items, { preserveSelection = true } = {}) {
    if (!$sel || !$sel.length)
      return;
    const prior = preserveSelection ? $sel.val() || "" : "";
    $sel.empty();
    $sel.append(new Option("\u2014 Select a character \u2014", ""));
    const normalized = normalizeItems(items);
    normalized.forEach((it) => $sel.append(new Option(it.name, it.id)));
    if (preserveSelection && prior && normalized.some((x) => x.id === String(prior))) {
      $sel.val(String(prior));
    }
  }
  function fetchCharacters() {
    const url = ajaxUrl();
    const nonce = nonceFor("cg_load_characters");
    const payload = { action: "cg_load_characters" };
    if (nonce) {
      payload.security = nonce;
      payload.nonce = nonce;
      payload._ajax_nonce = nonce;
    }
    return $15.post(url, payload).then((res) => {
      const json = parseJsonMaybe2(res);
      if (!json || json.success !== true) {
        return $15.Deferred().reject(json).promise();
      }
      return Array.isArray(json.data) ? json.data : [];
    });
  }
  function ensurePopulated({ force = false, preserveSelection = false } = {}) {
    var _a;
    if (!$15)
      return;
    const $sel = getSelect();
    if (!$sel)
      return;
    const optCount = $sel.find("option").length;
    if (!force && optCount > 1)
      return;
    const preload = ((_a = window.CG_DATA) == null ? void 0 : _a.characters) || window.cgCharacters || null;
    if (preload && Array.isArray(preload)) {
      LOG("using preloaded characters:", preload.length);
      populateSelect($sel, preload, { preserveSelection });
      return;
    }
    if (_inFlight)
      return;
    _inFlight = true;
    LOG("fetching characters via AJAX\u2026");
    fetchCharacters().done((items) => {
      LOG("fetched", (items || []).length, "records");
      populateSelect($sel, items, { preserveSelection });
    }).fail((a, b, c) => {
      const msg = a && a.data ? String(a.data) : a && a.responseText ? String(a.responseText) : typeof a === "string" ? a : b || c || "unknown error";
      WARN("character list fetch failed:", msg);
      populateSelect($sel, [], { preserveSelection: false });
    }).always(() => {
      _inFlight = false;
    });
  }
  function startObserver() {
    if (_observer || !window.MutationObserver)
      return;
    const tryNow = () => {
      const $sel = getSelect();
      if ($sel) {
        LOG("found #cg-splash-load-select (late) \u2014 populating");
        ensurePopulated({ force: false, preserveSelection: false });
        return true;
      }
      return false;
    };
    if (tryNow())
      return;
    LOG("waiting for #cg-splash-load-select to appear\u2026");
    _observer = new MutationObserver(() => {
      if (tryNow()) {
        _observer.disconnect();
        _observer = null;
      }
    });
    _observer.observe(document.body, { childList: true, subtree: true });
    setTimeout(() => {
      if (_observer) {
        _observer.disconnect();
        _observer = null;
        LOG("stop waiting (timeout) \u2014 splash select never appeared on this page");
      }
    }, 15e3);
  }
  function bindLoadEvents() {
    if (_inited)
      return;
    _inited = true;
    LOG("init");
    if (!$15) {
      WARN("jQuery not present; load dropdown will not populate.");
      return;
    }
    ensurePopulated({ force: false, preserveSelection: false });
    startObserver();
    $15(document).off("click.cgload", "#cg-load-refresh").on("click.cgload", "#cg-load-refresh", (e) => {
      e.preventDefault();
      LOG("manual refresh");
      ensurePopulated({ force: true, preserveSelection: true });
    });
    document.addEventListener("cg:characters:refresh", () => {
      LOG("received cg:characters:refresh \u2192 repopulating load list");
      ensurePopulated({ force: true, preserveSelection: true });
    });
  }

  // assets/js/src/core/main/builder-save.js
  var $16 = window.jQuery;
  function ajaxEnv5() {
    var _a, _b;
    const env = window.CG_AJAX || window.CG_Ajax || window.cgAjax || {};
    return {
      url: env.ajax_url || window.ajaxurl || ((_b = (_a = document.body) == null ? void 0 : _a.dataset) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php",
      nonce: env.nonce || env.security || window.CG_NONCE || null
    };
  }
  function normalizeCore2(raw = {}) {
    var _a, _b, _c, _d, _e, _f, _g, _h, _i, _j, _k, _l;
    const species = (_c = (_b = (_a = raw.species_id) != null ? _a : raw.species) != null ? _b : raw.trait_species) != null ? _c : "";
    const career = (_f = (_e = (_d = raw.career_id) != null ? _d : raw.career) != null ? _e : raw.trait_career) != null ? _f : "";
    return {
      id: raw.id || "",
      name: raw.name || "",
      player_name: raw.player_name || "",
      age: raw.age || "",
      gender: raw.gender || "",
      motto: raw.motto || "",
      will: (_g = raw.will) != null ? _g : "",
      speed: (_h = raw.speed) != null ? _h : "",
      body: (_i = raw.body) != null ? _i : "",
      mind: (_j = raw.mind) != null ? _j : "",
      trait_species: (_k = raw.trait_species) != null ? _k : "",
      trait_career: (_l = raw.trait_career) != null ? _l : "",
      species,
      career,
      // structured blobs (stringify to be safe for PHP)
      skill_marks: raw.skillMarks ? JSON.stringify(raw.skillMarks) : "",
      free_gifts: raw.free_gifts ? JSON.stringify(raw.free_gifts) : "",
      traits_list: raw.traitsList ? JSON.stringify(raw.traitsList) : "",
      skills_list: raw.skillsList ? JSON.stringify(raw.skillsList) : "",
      gifts: raw.gifts ? JSON.stringify(raw.gifts) : ""
    };
  }
  function buildPayload2(raw) {
    const core = normalizeCore2(raw);
    const { nonce } = ajaxEnv5();
    const base = { action: "cg_save_character" };
    if (nonce) {
      base.security = nonce;
      base.nonce = nonce;
      base._ajax_nonce = nonce;
    }
    const flat = __spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues(__spreadValues({}, base), core.id ? { id: core.id } : {}), core.name ? { name: core.name } : {}), core.player_name ? { player_name: core.player_name } : {}), core.age ? { age: core.age } : {}), core.gender ? { gender: core.gender } : {}), core.motto ? { motto: core.motto } : {}), core.will !== "" ? { will: core.will } : {}), core.speed !== "" ? { speed: core.speed } : {}), core.body !== "" ? { body: core.body } : {}), core.mind !== "" ? { mind: core.mind } : {}), core.trait_species !== "" ? { trait_species: core.trait_species } : {}), core.trait_career !== "" ? { trait_career: core.trait_career } : {}), core.species ? { species: core.species } : {}), core.career ? { career: core.career } : {}), core.skill_marks ? { skill_marks: core.skill_marks } : {}), core.free_gifts ? { free_gifts: core.free_gifts } : {}), core.traits_list ? { traits_list: core.traits_list } : {}), core.skills_list ? { skills_list: core.skills_list } : {}), core.gifts ? { gifts: core.gifts } : {});
    const character = {};
    if (core.id)
      character.id = core.id;
    if (core.name)
      character.name = core.name;
    if (core.player_name)
      character.player_name = core.player_name;
    if (core.age)
      character.age = core.age;
    if (core.gender)
      character.gender = core.gender;
    if (core.motto)
      character.motto = core.motto;
    if (core.will !== "")
      character.will = core.will;
    if (core.speed !== "")
      character.speed = core.speed;
    if (core.body !== "")
      character.body = core.body;
    if (core.mind !== "")
      character.mind = core.mind;
    if (core.trait_species !== "")
      character.trait_species = core.trait_species;
    if (core.trait_career !== "")
      character.trait_career = core.trait_career;
    if (core.species)
      character.species = core.species;
    if (core.career)
      character.career = core.career;
    if (core.skill_marks)
      character.skill_marks = core.skill_marks;
    if (core.free_gifts)
      character.free_gifts = core.free_gifts;
    if (core.traits_list)
      character.traits_list = core.traits_list;
    if (core.skills_list)
      character.skills_list = core.skills_list;
    if (core.gifts)
      character.gifts = core.gifts;
    flat.character = character;
    flat.character_json = JSON.stringify(__spreadValues({}, core));
    return flat;
  }
  function bindSaveEvents() {
    $16(document).off("click.cg", ".cg-save-button").on("click.cg", ".cg-save-button", function(e) {
      e.preventDefault();
      const closeAfter = $16(this).hasClass("cg-close-after-save");
      const raw = formBuilder_default.collectFormData();
      console.log("[BuilderSave] \u25B6 saving payload:", raw);
      const { url } = ajaxEnv5();
      if (!url) {
        console.error("[BuilderSave] No AJAX URL available");
        alert("Save error: missing AJAX URL");
        return;
      }
      const data = buildPayload2(raw);
      $16.post(url, data).done((res) => {
        var _a, _b;
        try {
          res = typeof res === "string" ? JSON.parse(res) : res;
        } catch (_) {
        }
        if (!res || res.success !== true) {
          console.error("[BuilderSave] save failed:", res);
          alert("Save error: " + ((res == null ? void 0 : res.data) || "Invalid payload."));
          return;
        }
        if ((_a = res.data) == null ? void 0 : _a.id) {
          raw.id = res.data.id;
          formBuilder_default._data = __spreadProps(__spreadValues({}, formBuilder_default._data), { id: res.data.id });
        }
        builder_ui_default.markClean();
        if (closeAfter) {
          builder_ui_default.closeBuilder();
        } else {
          alert("Character saved");
        }
        document.dispatchEvent(new CustomEvent("cg:character:saved", { detail: { id: ((_b = res.data) == null ? void 0 : _b.id) || raw.id || null, record: raw } }));
        document.dispatchEvent(new CustomEvent("cg:characters:refresh", { detail: {} }));
      }).fail((xhr, status, err2) => {
        console.error("[BuilderSave] AJAX error:", status, err2, xhr == null ? void 0 : xhr.responseText);
        alert("AJAX save error \u2014 see console");
      });
    });
  }

  // assets/js/src/core/main/builder-events.js
  var $17 = window.jQuery;
  var LOG2 = (...a) => console.log("[BuilderEvents]", ...a);
  var SEL = {
    species: '#cg-species, select[name="species"], select[name="trait_species"], select[data-cg="species"], .cg-species',
    career: '#cg-career,  select[name="career"],  select[name="trait_career"],  select[data-cg="career"],  .cg-career'
  };
  function firstSelect(selector) {
    const $sel = $17(selector);
    const $modalSel = $17("#cg-modal").find(selector);
    if ($modalSel.length)
      return $modalSel.first();
    return $sel.length ? $sel.first() : null;
  }
  function setSelectValue($sel, want) {
    if (!$sel || !$sel.length || !want)
      return false;
    const val = String(want);
    $sel.val(val);
    if ($sel.val() === val)
      return true;
    const $byText = $sel.find("option").filter(function() {
      return $17(this).text() === val;
    }).first();
    if ($byText.length) {
      $sel.val($byText.val());
      return true;
    }
    return false;
  }
  function triggerDownstream() {
    var _a, _b, _c, _d, _e, _f, _g, _h, _i;
    try {
      (_c = (_b = (_a = traits_default) == null ? void 0 : _a.Service) == null ? void 0 : _b.updateAdjustedDisplays) == null ? void 0 : _c.call(_b);
    } catch (_) {
    }
    try {
      (_e = (_d = gifts_default) == null ? void 0 : _d.refresh) == null ? void 0 : _e.call(_d);
    } catch (_) {
    }
    try {
      (_g = (_f = skills_default) == null ? void 0 : _f.refresh) == null ? void 0 : _g.call(_f);
    } catch (_) {
    }
    try {
      (_i = (_h = summary_default) == null ? void 0 : _h.refresh) == null ? void 0 : _i.call(_h);
    } catch (_) {
    }
  }
  function hydrateSelect(kind, { force = false, record = null } = {}) {
    const key = kind === "species" ? "species" : "career";
    const selector = kind === "species" ? SEL.species : SEL.career;
    const $sel = firstSelect(selector);
    if (!$sel) {
      LOG2(`no ${kind} select found`);
      return $17.Deferred().resolve().promise();
    }
    const el = $sel.get(0);
    const hadCount = el.options.length;
    try {
      const Index = kind === "species" ? species_default : career_default;
      if (Index == null ? void 0 : Index.refresh)
        Index.refresh();
      else if (Index == null ? void 0 : Index.render)
        Index.render();
    } catch (e) {
    }
    const doApply = () => {
      var _a, _b;
      if (record) {
        const want = (_b = (_a = record[key]) != null ? _a : record[`${key}_id`]) != null ? _b : "";
        if (want)
          setSelectValue($sel, want);
      }
      if (el.options.length > hadCount || record && (record[key] || record[`${key}_id`])) {
        $sel.trigger("change");
        triggerDownstream();
      }
    };
    const ensureOptions = () => {
      if (el.options.length > 1 && !force)
        return $17.Deferred().resolve().promise();
      const API = kind === "species" ? api_default : api_default2;
      if (typeof (API == null ? void 0 : API.populateSelect) !== "function")
        return $17.Deferred().resolve().promise();
      return API.populateSelect(el, { force: !!force });
    };
    return $17.Deferred(function(dfr) {
      setTimeout(() => {
        ensureOptions().then(() => {
          doApply();
          dfr.resolve();
        }).catch(() => dfr.resolve());
      }, 0);
    }).promise();
  }
  function hydrateSpeciesAndCareer(opts = {}) {
    return $17.when(
      hydrateSelect("species", opts),
      hydrateSelect("career", opts)
    );
  }
  function bindUIEvents() {
    var _a, _b;
    LOG2("bindUIEvents() called");
    try {
      (_b = (_a = gifts_default) == null ? void 0 : _a.init) == null ? void 0 : _b.call(_a);
    } catch (_) {
    }
    $17(document).off("input.cg change.cg", "#cg-modal input, #cg-modal select, #cg-modal textarea").on("input.cg change.cg", "#cg-modal input, #cg-modal select, #cg-modal textarea", function() {
      builder_ui_default.markDirty();
      const $el = $17(this);
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
    $17(document).off("click.cg", "#cg-open-builder").on("click.cg", "#cg-open-builder", (e) => {
      e.preventDefault();
      $17("#cg-modal-splash").removeClass("cg-hidden").addClass("visible");
      try {
        const $sel = $17("#cg-splash-load-select");
        const optCount = $sel.length ? $sel.find("option").length : 0;
        if ($sel.length && optCount <= 1) {
          document.dispatchEvent(new CustomEvent("cg:characters:refresh", { detail: { source: "splash-open" } }));
        }
      } catch (_) {
      }
    });
    $17(document).off("click.cg", "#cg-new-splash").on("click.cg", "#cg-new-splash", (e) => {
      e.preventDefault();
      $17("#cg-modal-splash").removeClass("visible").addClass("cg-hidden");
      builder_ui_default.openBuilder({ isNew: true, payload: {} });
      formBuilder_default._data.skillMarks = {};
      formBuilder_default._data.species = "";
      formBuilder_default._data.career = "";
      if (window.CG_FreeChoicesState) {
        window.CG_FreeChoicesState.selected = ["", "", ""];
      }
      setTimeout(() => {
        hydrateSpeciesAndCareer({ force: false, record: {} });
      }, 0);
    });
    $17(document).off("click.cg", "#cg-load-splash").on("click.cg", "#cg-load-splash", (e) => {
      e.preventDefault();
      const charId = $17("#cg-splash-load-select").val();
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
        $17("#cg-modal-splash").removeClass("visible").addClass("cg-hidden");
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
    $17(document).off("click.cg", "#cg-modal .cg-tabs li").on("click.cg", "#cg-modal .cg-tabs li", function(e) {
      e.preventDefault();
      const tabName = $17(this).data("tab");
      $17("#cg-modal .cg-tabs li").removeClass("active");
      $17(this).addClass("active");
      $17(".tab-panel").removeClass("active");
      $17(`#${tabName}`).addClass("active");
      refreshTab();
      setTimeout(() => {
        hydrateSpeciesAndCareer({ force: false });
      }, 0);
    });
    $17(document).off("click.cg", "#cg-modal-close").on("click.cg", "#cg-modal-close", (e) => {
      e.preventDefault();
      builder_ui_default.showUnsaved();
    });
    $17(document).off("click.cg", "#cg-modal-overlay").on("click.cg", "#cg-modal-overlay", function(e) {
      if (e.target !== this)
        return;
      builder_ui_default.showUnsaved();
    });
    $17(document).off("click.cg", "#unsaved-save").on("click.cg", "#unsaved-save", (e) => {
      e.preventDefault();
      console.log("[BuilderEvents] Prompt: SAVE & EXIT clicked");
      formBuilder_default.save(true);
    });
    $17(document).off("click.cg", "#unsaved-exit").on("click.cg", "#unsaved-exit", (e) => {
      e.preventDefault();
      builder_ui_default.closeBuilder();
    });
    $17(document).off("click.cg", "#unsaved-cancel").on("click.cg", "#unsaved-cancel", (e) => {
      e.preventDefault();
      builder_ui_default.hideUnsaved();
    });
    document.addEventListener("cg:characters:refresh", () => {
      setTimeout(() => {
        hydrateSpeciesAndCareer({ force: true });
      }, 0);
    });
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
  var Core = {
    init() {
      console.log("[Core] init() called");
      main_default.init();
    }
  };
  console.log("[Core] bundle loaded");
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", Core.init);
  } else {
    Core.init();
  }
  function initCore() {
    skills_default.init();
  }
  if (typeof window !== "undefined")
    window.SpeciesAPI = api_default;
})();
//# sourceMappingURL=core.bundle.js.map
