(() => {
  var __create = Object.create;
  var __defProp = Object.defineProperty;
  var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
  var __getOwnPropNames = Object.getOwnPropertyNames;
  var __getProtoOf = Object.getPrototypeOf;
  var __hasOwnProp = Object.prototype.hasOwnProperty;
  var __require = /* @__PURE__ */ ((x) => typeof require !== "undefined" ? require : typeof Proxy !== "undefined" ? new Proxy(x, {
    get: (a, b) => (typeof require !== "undefined" ? require : a)[b]
  }) : x)(function(x) {
    if (typeof require !== "undefined")
      return require.apply(this, arguments);
    throw Error('Dynamic require of "' + x + '" is not supported');
  });
  var __commonJS = (cb, mod) => function __require2() {
    return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
  };
  var __copyProps = (to, from, except, desc) => {
    if (from && typeof from === "object" || typeof from === "function") {
      for (let key of __getOwnPropNames(from))
        if (!__hasOwnProp.call(to, key) && key !== except)
          __defProp(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable });
    }
    return to;
  };
  var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(
    // If the importer is in node compatibility mode or this is not an ESM
    // file that has been converted to a CommonJS file using a Babel-
    // compatible transform (i.e. "__esModule" has not been set), then set
    // "default" to the CommonJS "module.exports" for node compatibility.
    isNodeMode || !mod || !mod.__esModule ? __defProp(target, "default", { value: mod, enumerable: true }) : target,
    mod
  ));

  // assets/js/src/gifts/gift-utils.js
  var require_gift_utils = __commonJS({
    "assets/js/src/gifts/gift-utils.js"() {
      (function($6) {
        window.CG_GiftUtils = {
          /**
           * Render a labeled dropdown.
           * @param {string} label - Label text
           * @param {string} value - Default selected value
           * @param {Array<string>} options - Dropdown options
           * @param {boolean} editable - Whether dropdown should be enabled
           * @param {string} name - Name attribute for <select> (optional)
           * @returns {string} HTML
           */
          renderDropdown(label, value, options = [], editable = false, name = "") {
            const opts = options.length ? options.map((v) => `<option value="${v}"${v === value ? " selected" : ""}>${v}</option>`).join("") : `<option>${value || "\u2014"}</option>`;
            const props = editable ? `name="${name}"` : "disabled";
            return `
        <label><strong>${label}</strong></label>
        <select ${props}>${opts}</select>
      `;
          }
        };
      })(jQuery);
    }
  });

  // assets/js/src/gifts/gift-definitions.js
  (function($6) {
    window.CG_GiftLibrary = {
      gifts: {},
      register(giftList) {
        console.log("[CG_GiftLibrary] Registering gifts:", giftList);
        giftList.forEach((g) => {
          this.gifts[+g.id] = g;
        });
      },
      allowsMultiple(id) {
        const g = this.gifts[+id];
        const result = (g == null ? void 0 : g.ct_gifts_allows_multiple) == 1 || (g == null ? void 0 : g.ct_gifts_manifold) == 1;
        console.log("[CG_GiftLibrary] Gift", id, "allows multiple:", result);
        return result;
      },
      isIncreaseTrait(id) {
        const g = this.gifts[+id];
        const result = (g == null ? void 0 : g.ct_gifts_type) === "Increase Trait";
        console.log("[CG_GiftLibrary] Gift", id, "is Increase Trait:", result);
        return result;
      },
      isExcludedFromFreeSelection(id, selectedIds = []) {
        const g = this.gifts[+id];
        if (!g)
          return false;
        const alreadySelected = selectedIds.includes(+id);
        const isAllowedMultiple = g.ct_gifts_allows_multiple == 1 || g.ct_gifts_manifold == 1;
        const reqFields = [
          "ct_gifts_requires",
          "ct_gifts_requires_two",
          "ct_gifts_requires_three",
          "ct_gifts_requires_four",
          "ct_gifts_requires_five",
          "ct_gifts_requires_six",
          "ct_gifts_requires_seven",
          "ct_gifts_requires_eight",
          "ct_gifts_requires_nine",
          "ct_gifts_requires_ten",
          "ct_gifts_requires_eleven",
          "ct_gifts_requires_twelve",
          "ct_gifts_requires_thirteen",
          "ct_gifts_requires_fourteen",
          "ct_gifts_requires_fifteen",
          "ct_gifts_requires_sixteen",
          "ct_gifts_requires_seventeen",
          "ct_gifts_requires_eighteen",
          "ct_gifts_requires_nineteen"
        ];
        const requiresIds = reqFields.map((f) => +g[f]).filter((v) => !isNaN(v) && v > 0);
        const hasRequirementData = requiresIds.length > 0;
        const hasConflicts = requiresIds.some((req) => selectedIds.includes(req));
        console.log(`[CG_GiftLibrary] Evaluating gift ${id}`, {
          alreadySelected,
          isAllowedMultiple,
          requiresIds,
          hasRequirementData,
          hasConflicts,
          selectedIds
        });
        if (hasRequirementData && selectedIds.length === 0)
          return true;
        if (alreadySelected && !isAllowedMultiple)
          return true;
        if (hasConflicts)
          return true;
        return false;
      }
    };
  })(jQuery);

  // assets/js/src/gifts/index.js
  var import_gift_utils2 = __toESM(require_gift_utils());

  // assets/js/src/gifts/localKnowledge.js
  var LocalKnowledge = {
    // Holds the last‐entered local knowledge text
    loadedLocalText: "",
    // Called from index.js to kick things off
    init() {
      this.loadLocalKnowledge();
    },
    /**
     * Fetches the “local knowledge” gift and renders a dropdown + text input.
     * @param {string} prefill  Optional text to prefill the input with
     */
    loadLocalKnowledge(prefill = "") {
      this.loadedLocalText = prefill || this.loadedLocalText || "";
      const $container = jQuery("#cg-local-knowledge");
      if (!$container.length)
        return;
      jQuery.post(CG_Ajax.ajax_url, {
        action: "cg_get_local_knowledge",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (!res.success)
          return;
        const g = res.data;
        const dropdown = CG_GiftUtils.renderDropdown(g.ct_gifts_name, g.ct_gifts_name);
        const input = `<input
                          type="text"
                          id="cg-local-knowledge-area"
                          placeholder="Enter area"
                          value="${this.loadedLocalText}"
                        />`;
        $container.html(`${dropdown}${input}`);
      });
    }
  };
  var localKnowledge_default = LocalKnowledge;

  // assets/js/src/gifts/language.js
  var Language = {
    // Holds the last‐entered language text
    loadedLangText: "",
    // Called from index.js to kick things off
    init() {
      this.loadLanguage();
    },
    /**
     * Fetches the “language” gift and renders a dropdown + text input.
     * @param {string} prefill  Optional text to prefill the input with
     */
    loadLanguage(prefill = "") {
      this.loadedLangText = prefill || this.loadedLangText || "";
      const $container = jQuery("#cg-language");
      if (!$container.length)
        return;
      jQuery.post(CG_Ajax.ajax_url, {
        action: "cg_get_language_gift",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (!res.success)
          return;
        const g = res.data;
        const dropdown = CG_GiftUtils.renderDropdown(g.ct_gifts_name, g.ct_gifts_name);
        const input = `<input
                          type="text"
                          id="cg-language-area"
                          placeholder="Specify language"
                          value="${this.loadedLangText}"
                        />`;
        $container.html(`${dropdown}${input}`);
      });
    }
  };
  var language_default = Language;

  // assets/js/src/gifts/freeChoices.js
  var import_jquery3 = __toESM(__require("jquery"));

  // assets/js/src/gifts/boost.js
  var import_jquery = __toESM(__require("jquery"));
  var Boost = {
    // Tracks which trait key is boosted for each free‐choice index
    boostTargets: {},
    /**
     * Bind handlers for:
     * - Selecting a boost target from the injected selector
     * - Changing extra‐career selects (to recalc boost options)
     */
    init() {
      (0, import_jquery.default)(document).on("change", ".cg-free-boost", (e) => {
        const idx = +(0, import_jquery.default)(e.currentTarget).data("index");
        this.boostTargets[idx] = (0, import_jquery.default)(e.currentTarget).val();
        CG_Traits.updateAdjustedTraitDisplays();
        CG_Skills.refreshAll();
      }).on("change", ".cg-extra-career-block select", () => {
        this.updateBoostUI();
        CG_Skills.refreshAll();
      });
    },
    /**
     * Injects the “Boost Career Trait” selector for Gift 223 at free‐choice index `idx`.
     * Automatically picks the only option if there's just one career.
     */
    injectBoostSelector(idx) {
      var _a;
      const careers = [];
      const primaryName = (0, import_jquery.default)("#cg-career option:selected").text().trim();
      if (primaryName) {
        careers.push({ key: "trait_career", name: primaryName });
      }
      (0, import_jquery.default)('.cg-extra-career-block select[id^="cg-extra-career-"]').each((i, sel) => {
        const txt = (0, import_jquery.default)(sel).find("option:selected").text().trim();
        if (txt) {
          careers.push({ key: `trait_career_${i + 1}`, name: txt });
        }
      });
      (0, import_jquery.default)(`.cg-free-boost-container[data-index="${idx}"]`).remove();
      const $label = (0, import_jquery.default)(`.cg-free-choice-label[data-index="${idx}"]`);
      let html;
      if (careers.length === 1) {
        this.boostTargets[idx] = "trait_career";
        html = `
        <div class="cg-free-boost-container" data-index="${idx}">
          <small>Boosted ${careers[0].name} automatically</small>
        </div>
      `;
      } else {
        const opts = careers.map((o) => `<option value="${o.key}">${o.name}</option>`).join("");
        html = `
        <label class="cg-free-boost-container" data-index="${idx}">
          <strong>Boost Career Trait:</strong>
          <select class="cg-free-boost" data-index="${idx}">
            ${opts}
          </select>
        </label>
      `;
      }
      $label.append(html);
      const prev = this.boostTargets[idx] || ((_a = careers[0]) == null ? void 0 : _a.key);
      if (prev) {
        this.boostTargets[idx] = prev;
        $label.find(".cg-free-boost").val(prev);
      }
    },
    /**
     * For each free‐choice gift of ID 223, remove any old boost UI and re‐inject it.
     * Called after career/extra‐career changes or initial render.
     */
    updateBoostUI() {
      freeChoices_default.selected.forEach((giftId, i) => {
        if (giftId === 223) {
          const idx = i + 1;
          (0, import_jquery.default)(`.cg-free-boost-container[data-index="${idx}"]`).remove();
          this.injectBoostSelector(idx);
        }
      });
      CG_Traits.updateAdjustedTraitDisplays();
      CG_Skills.refreshAll();
    }
  };
  var boost_default = Boost;

  // assets/js/src/gifts/extraCareer.js
  var import_jquery2 = __toESM(__require("jquery"));
  var ExtraCareer = {
    // Preserve selections across renders
    extraSelected: {},
    init() {
      this.renderExtraCareerUI();
    },
    renderExtraCareerUI() {
      const $container = (0, import_jquery2.default)("#cg-extra-careers");
      const primaryId = Number((0, import_jquery2.default)("#cg-career").val()) || null;
      const count184 = freeChoices_default.selected.filter((id) => id === 184).length;
      if (!$container.length || !primaryId || count184 === 0) {
        this.extraSelected = {};
        $container.empty();
        return;
      }
      const giftIds = [
        ...freeChoices_default.selected.filter(Boolean),
        ...window.CG_Career.currentProfile ? ["gift_id_1", "gift_id_2", "gift_id_3"].map((k) => +window.CG_Career.currentProfile[k]).filter(Boolean) : [],
        ...window.CG_Species.currentProfile ? ["gift_id_1", "gift_id_2", "gift_id_3"].map((k) => +window.CG_Species.currentProfile[k]).filter(Boolean) : []
      ];
      import_jquery2.default.post(CG_Ajax.ajax_url, {
        action: "cg_get_eligible_extra_careers",
        gifts: giftIds,
        security: CG_Ajax.nonce
      }).done((res) => {
        if (!res.success)
          return;
        const list = res.data.filter((c) => +c.id !== primaryId);
        let html = "";
        for (let i = 1; i <= count184; i++) {
          const selId = this.extraSelected[i] || "";
          html += `
          <div class="cg-extra-career-block">
            <label><strong>Extra Career ${i}</strong></label>
            <select id="cg-extra-career-${i}" class="cg-profile-select">
              <option value="">\u2014 Select \u2014</option>
              ${list.map(
            (c) => `<option value="${c.id}"${c.id == selId ? " selected" : ""}>
                  ${c.name}
                </option>`
          ).join("")}
            </select>

            <label><strong>Trait</strong></label>
            <select id="cg-trait_career_${i}" class="cg-trait-career-select">
              <option value="d4">d4</option>
            </select>

            <div class="trait-adjusted" id="cg-trait_career_${i}-adjusted"></div>
          </div>
        `;
        }
        $container.html(html);
        boost_default.updateBoostUI();
        CG_Skills.loadSkillsList(() => {
          CG_Skills.scanExtraCareers();
          CG_Skills.refreshAll();
        });
      });
    }
  };
  var extraCareer_default = ExtraCareer;

  // assets/js/src/gifts/freeChoices.js
  var FreeChoices = {
    allGifts: [],
    selected: [],
    freeLoaded: false,
    init() {
      this.loadFreeChoices();
    },
    /**
     * Load the full list of free‐choice gifts via AJAX,
     * register them, then render selectors.
     */
    loadFreeChoices() {
      if (this.freeLoaded)
        return;
      const $container = (0, import_jquery3.default)("#cg-free-choices");
      if (!$container.length)
        return;
      import_jquery3.default.post(CG_Ajax.ajax_url, {
        action: "cg_get_free_gifts",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (!res.success)
          return;
        this.allGifts = res.data;
        if (window.CG_GiftLibrary)
          CG_GiftLibrary.register(res.data);
        this.freeLoaded = true;
        this.renderFreeChoiceSelectors();
      });
    },
    /**
     * Render three free‐choice dropdowns and initialize their state.
     */
    renderFreeChoiceSelectors() {
      const html = [1, 2, 3].map((i) => `
      <label class="cg-free-choice-label" data-index="${i}">
        <strong>Free Choice ${i}:</strong>
        <select class="cg-free-gift" data-index="${i}">
          <option value="">\u2014 Select \u2014</option>
        </select>
      </label>
    `).join("");
      (0, import_jquery3.default)("#cg-free-choices").html(html);
      this.updateOptions(() => {
        [1, 2, 3].forEach((i) => {
          const v = this.selected[i - 1] || "";
          (0, import_jquery3.default)(`.cg-free-gift[data-index="${i}"]`).val(v);
        });
      });
      this.bindFreeChoiceHandlers();
      boost_default.updateBoostUI();
    },
    /**
     * Bind change handlers on the free‐choice selects.
     * Updates state, re-filters options, triggers boosts & extra careers.
     */
    bindFreeChoiceHandlers() {
      (0, import_jquery3.default)("#cg-free-choices").off("change.fc").on("change.fc", ".cg-free-gift", (e) => {
        const idx = +(0, import_jquery3.default)(e.currentTarget).data("index");
        const gift = +(0, import_jquery3.default)(e.currentTarget).val() || null;
        this.selected[idx - 1] = gift;
        (0, import_jquery3.default)('.cg-free-boost-container[data-index="' + idx + '"]').remove();
        if (gift === 223) {
          boost_default.injectBoostSelector(idx);
        }
        this.updateOptions();
        CG_Traits.updateAdjustedTraitDisplays();
        CG_Skills.refreshAll();
        extraCareer_default.renderExtraCareerUI();
        boost_default.updateBoostUI();
      });
    },
    /**
     * Repopulate each free‐gift dropdown based on current context:
     * - Exclude gifts via CG_GiftLibrary
     * - Show gift 184 only if a career is selected
     */
    updateOptions(callback) {
      var _a, _b;
      const selIds = this.selected.filter(Boolean);
      const careerIds = ((_a = window.CG_Career) == null ? void 0 : _a.currentProfile) ? ["gift_id_1", "gift_id_2", "gift_id_3"].map((k) => +CG_Career.currentProfile[k]).filter((id) => id > 0) : [];
      const speciesIds = ((_b = window.CG_Species) == null ? void 0 : _b.currentProfile) ? ["gift_id_1", "gift_id_2", "gift_id_3"].map((k) => +CG_Species.currentProfile[k]).filter((id) => id > 0) : [];
      const allSelected = [...selIds, ...careerIds, ...speciesIds];
      const hasCareer = !!(0, import_jquery3.default)("#cg-career").val();
      (0, import_jquery3.default)(".cg-free-gift").each((_, el) => {
        const $sel = (0, import_jquery3.default)(el);
        const cur = +$sel.val() || null;
        $sel.empty().append('<option value="">\u2014 Select \u2014</option>');
        this.allGifts.forEach((g) => {
          const id = +g.id;
          const excluded = CG_GiftLibrary.isExcludedFromFreeSelection(id, allSelected);
          const allow184 = id === 184 && hasCareer;
          if (id === cur || (!excluded || allow184)) {
            const $opt = (0, import_jquery3.default)("<option>").val(id).text(g.ct_gifts_name);
            if (id === cur)
              $opt.prop("selected", true);
            $sel.append($opt);
          }
        });
      });
      if (typeof callback === "function")
        callback();
    }
  };
  var freeChoices_default = FreeChoices;

  // assets/js/src/gifts/speciesCareerHooks.js
  var import_jquery4 = __toESM(__require("jquery"));
  var import_gift_utils = __toESM(require_gift_utils());
  var SpeciesCareerHooks = {
    init() {
      this.bindSpeciesChange();
      this.bindCareerChange();
    },
    bindSpeciesChange() {
      (0, import_jquery4.default)(document).on("change", "#cg-species", () => {
        const id = (0, import_jquery4.default)("#cg-species").val();
        const $block = (0, import_jquery4.default)("#species-gift-block");
        if (!id) {
          $block.empty();
          return;
        }
        import_jquery4.default.post(CG_Ajax.ajax_url, {
          action: "cg_get_species_profile",
          id,
          security: CG_Ajax.nonce
        }).done((res) => {
          if (!res.success)
            return;
          const s = res.data;
          window.CG_Species.currentProfile = s;
          const giftDropdowns = [
            [s.gift_1, s.gift_id_1],
            [s.gift_2, s.gift_id_2],
            [s.gift_3, s.gift_id_3]
          ].filter(([name]) => !!name).map(([name], i) => import_gift_utils.default.renderDropdown(`Species Gift ${i + 1}`, name)).join("");
          $block.html(giftDropdowns);
          freeChoices_default.updateOptions();
          extraCareer_default.renderExtraCareerUI();
        });
      });
    },
    bindCareerChange() {
      (0, import_jquery4.default)(document).on("change", "#cg-career", () => {
        const id = (0, import_jquery4.default)("#cg-career").val();
        const $block = (0, import_jquery4.default)("#career-gifts");
        if (!id) {
          $block.empty();
          return;
        }
        import_jquery4.default.post(CG_Ajax.ajax_url, {
          action: "cg_get_career_gifts",
          id,
          security: CG_Ajax.nonce
        }).done((res) => {
          if (!res.success)
            return;
          const c = res.data;
          window.CG_Career.currentProfile = c;
          const giftDropdowns = [
            [c.gift_1, c.gift_id_1],
            [c.gift_2, c.gift_id_2],
            [c.gift_3, c.gift_id_3]
          ].filter(([name]) => !!name).map(([name], i) => import_gift_utils.default.renderDropdown(`Career Gift ${i + 1}`, name)).join("");
          $block.html(giftDropdowns);
          freeChoices_default.updateOptions();
          extraCareer_default.renderExtraCareerUI();
        });
      });
    }
  };
  var speciesCareerHooks_default = SpeciesCareerHooks;

  // assets/js/src/gifts/index.js
  var import_jquery5 = __toESM(__require("jquery"));
  (0, import_jquery5.default)(function() {
    localKnowledge_default.init();
    language_default.init();
    freeChoices_default.init();
    speciesCareerHooks_default.init();
    extraCareer_default.init();
    boost_default.init();
  });
})();
//# sourceMappingURL=gifts.bundle.js.map
