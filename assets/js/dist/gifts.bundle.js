var CG_Gifts = (() => {
  var __defProp = Object.defineProperty;
  var __defProps = Object.defineProperties;
  var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
  var __getOwnPropDescs = Object.getOwnPropertyDescriptors;
  var __getOwnPropNames = Object.getOwnPropertyNames;
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
  var __export = (target, all) => {
    for (var name in all)
      __defProp(target, name, { get: all[name], enumerable: true });
  };
  var __copyProps = (to, from, except, desc) => {
    if (from && typeof from === "object" || typeof from === "function") {
      for (let key of __getOwnPropNames(from))
        if (!__hasOwnProp.call(to, key) && key !== except)
          __defProp(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable });
    }
    return to;
  };
  var __toCommonJS = (mod) => __copyProps(__defProp({}, "__esModule", { value: true }), mod);

  // assets/js/src/gifts/index.js
  var index_exports = {};
  __export(index_exports, {
    default: () => index_default
  });

  // assets/js/src/core/main/builder-ui.js
  var $ = window.jQuery;
  var isDirty = false;
  function openBuilder({ isNew = false, payload = {} } = {}) {
    console.log("[BuilderUI] openBuilder() called with opts:", { isNew, payload });
    isDirty = false;
    $("#cg-unsaved-confirm, #cg-unsaved-backdrop").hide().css("display", "none");
    const data = __spreadProps(__spreadValues({}, payload), {
      isNew
    });
    formBuilder_default.init(data);
    $("#cg-modal-overlay, #cg-modal").removeClass("cg-hidden").addClass("cg-visible").css("display", "block");
    if (isNew) {
      $('#cg-modal .cg-tabs li[data-tab="tab-traits"]').click();
    }
  }
  function closeBuilder() {
    $("#cg-unsaved-backdrop").hide().css("display", "none");
    $("#cg-unsaved-confirm").removeClass("cg-visible").addClass("cg-hidden").hide().css("display", "none");
    $("#cg-modal, #cg-modal-overlay").removeClass("cg-visible").addClass("cg-hidden").fadeOut(200, () => {
      $("#cg-form-container").empty();
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
    if (!$("#cg-unsaved-backdrop").length) {
      $('<div id="cg-unsaved-backdrop"></div>').appendTo("body");
    }
    const $backdrop = $("#cg-unsaved-backdrop");
    const $prompt = $("#cg-unsaved-confirm");
    if (!$prompt.parent().is("body")) {
      $prompt.appendTo("body");
    }
    $backdrop.show().css("display", "block");
    $prompt.removeClass("cg-hidden").addClass("cg-visible").show().css("display", "block");
  }
  function hideUnsaved() {
    $("#cg-unsaved-backdrop").hide().css("display", "none");
    $("#cg-unsaved-confirm").removeClass("cg-visible").addClass("cg-hidden").hide().css("display", "none");
  }
  var builder_ui_default = {
    openBuilder,
    closeBuilder,
    showUnsaved,
    hideUnsaved,
    markDirty,
    markClean,
    getIsDirty
  };

  // assets/js/src/core/species/api.js
  var $2 = window.jQuery;
  var SpeciesAPI = {
    /**
     * Populate the #cg-species dropdown.
     */
    loadSpeciesList(cb) {
      const $sel = $2("#cg-species").html('<option value="">\u2014 Select Species \u2014</option>');
      $2.post(CG_Ajax.ajax_url, {
        action: "cg_get_species_list",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (!res.success) return;
        res.data.forEach(({ id, name }) => {
          $sel.append(`<option value="${id}">${name}</option>`);
        });
      }).always(() => {
        if (typeof cb === "function") cb();
      });
    },
    /**
     * Fetch the full profile for one species (gifts, skills, etc).
     */
    loadSpeciesProfile(speciesId, cb) {
      $2.post(CG_Ajax.ajax_url, {
        action: "cg_get_species_profile",
        id: speciesId,
        security: CG_Ajax.nonce
      }).done((res) => {
        if (res.success && typeof cb === "function") {
          cb(res.data);
        }
      });
    }
  };
  var api_default = SpeciesAPI;

  // assets/js/src/core/career/api.js
  var $3 = window.jQuery;
  var CareerAPI = {
    loadList(callback) {
      const $sel = $3("#cg-career").html('<option value="">\u2014 Select Career \u2014</option>');
      $3.post(CG_Ajax.ajax_url, {
        action: "cg_get_career_list",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (res.success) {
          res.data.forEach(
            (item) => $sel.append(`<option value="${item.id}">${item.name}</option>`)
          );
        }
      }).always(() => {
        if (typeof callback === "function") callback();
      });
    },
    loadGifts(careerId, callback) {
      $3.post(CG_Ajax.ajax_url, {
        action: "cg_get_career_gifts",
        id: careerId,
        security: CG_Ajax.nonce
      }).done((res) => {
        if (res.success && typeof callback === "function") {
          callback(res.data);
        }
      });
    }
    //  loadEligibleExtraCareers(giftIds, callback) {
    //    $.post(CG_Ajax.ajax_url, {
    //      action:   'cg_get_eligible_extra_careers',
    //      gifts:    giftIds,
    //      security: CG_Ajax.nonce
    //    })
    //    .done(res => {
    //      if (res.success && typeof callback === 'function') {
    //        callback(res.data);
    //      }
    //    });
    //  }
  };
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
        if (!giftId) return;
        const gift = state_default.getGiftById(giftId);
        if (!gift) return;
        const traitKey = BOOSTS[gift.id];
        if (!traitKey) return;
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
      const $10 = window.jQuery;
      const freq = { d8: 0, d6: 0, d4: 0 };
      $10(".cg-trait-select").each(function() {
        const v = $10(this).val();
        if (v && v in freq) freq[v]++;
      });
      $10(".cg-trait-select").each(function() {
        const $sel = $10(this);
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
      const $10 = window.jQuery;
      const boosts = this.calculateBoostMap();
      TRAITS.forEach((traitKey) => {
        const $sel = $10(`#cg-${traitKey}`);
        const base = $sel.val() || "d4";
        const idx = DIE_ORDER.indexOf(base);
        const count = boosts[traitKey] || 0;
        const boosted = DIE_ORDER[Math.min(idx + count, DIE_ORDER.length - 1)];
        $10(`#cg-${traitKey}-adjusted`).text(boosted);
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
      if (!cnt) return "";
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
      d.species_id = $5("#cg-species").val();
      d.career_id = $5("#cg-career").val();
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
      return d;
    },
    /**
     * Save the character via WP-AJAX and optionally close builder.
     *
     * @param {boolean} shouldClose
     * @returns {Promise}
     */
    save(shouldClose = false) {
      const payload = this.collectFormData();
      console.log("[FormBuilderAPI] \u25B6 save()", payload);
      return $5.ajax({
        url: CG_Ajax.ajax_url,
        method: "POST",
        data: {
          action: "cg_save_character",
          character: payload,
          security: CG_Ajax.nonce
        }
      }).done((res) => {
        if (!res.success) {
          console.error("[FormBuilderAPI] save.error()", res.data);
          alert("Save failed: " + res.data);
          return;
        }
        this._data = __spreadProps(__spreadValues({}, this._data), { id: res.data.id });
        this.isNew = false;
        this.hasData = true;
        builder_ui_default.markClean();
        if (shouldClose) {
          builder_ui_default.closeBuilder();
        }
      }).fail((xhr, status, err) => {
        console.error("[FormBuilderAPI] save.fail()", status, err, xhr.responseText);
        alert("Save failed\u2014check console for details.");
      });
    },
    /**
     * Fetch a list of saved characters (for the Load splash).
     */
    listCharacters() {
      return $5.ajax({
        url: CG_Ajax.ajax_url,
        method: "POST",
        data: {
          action: "cg_load_characters",
          security: CG_Ajax.nonce
        }
      });
    },
    /**
     * Fetch one character’s full data by ID.
     *
     * @param {number|string} id
     */
    fetchCharacter(id) {
      console.log("[FormBuilderAPI] fetchCharacter() called with ID:", id);
      return $5.ajax({
        url: CG_Ajax.ajax_url,
        method: "POST",
        data: {
          action: "cg_get_character",
          id,
          security: CG_Ajax.nonce
        }
      });
    }
  };
  var formBuilder_default = FormBuilderAPI;

  // assets/js/src/gifts/state.js
  var State = {
    // currently selected free‐gift IDs
    selected: [],
    // the last fetched gift objects (with name, manifold, requires…)
    gifts: [],
    /**
     * Pull any previously saved freeGifts from the builder’s data.
     */
    init() {
      const data = formBuilder_default.getData();
      this.selected = Array.isArray(data.freeGifts) ? data.freeGifts : ["", "", ""];
    },
    /**
     * Update one slot and persist back into FormBuilder’s data.
     */
    set(index, id) {
      this.selected[index] = id;
      const data = formBuilder_default.getData();
      data.freeGifts = this.selected;
    },
    /**
     * Merge incoming gift objects into our master list,
     * keeping manifold, requires, etc.
     */
    setList(giftList = []) {
      giftList.forEach((g) => {
        const idStr = String(g.id);
        const idx = this.gifts.findIndex((x) => String(x.id) === idStr);
        if (idx > -1) {
          this.gifts[idx] = __spreadValues(__spreadValues({}, this.gifts[idx]), g);
        } else {
          this.gifts.push(g);
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

  // assets/js/src/gifts/api.js
  var $6 = window.jQuery;
  var api_default3 = {
    fetchLocalKnowledge(cb) {
      $6.post(CG_Ajax.ajax_url, {
        action: "cg_get_local_knowledge",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (res.success && typeof cb === "function") cb(res.data);
      });
    },
    fetchLanguageGift(cb) {
      $6.post(CG_Ajax.ajax_url, {
        action: "cg_get_language_gift",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (res.success && typeof cb === "function") cb(res.data);
      });
    },
    fetchFreeChoices(cb) {
      $6.post(CG_Ajax.ajax_url, {
        action: "cg_get_free_gifts",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (!res.success || typeof cb !== "function") return;
        const gifts = res.data.map((g) => __spreadProps(__spreadValues({}, g), {
          // ensure id is string, name is present, and manifold is a Number
          id: String(g.id),
          name: g.name,
          ct_gifts_manifold: parseInt(g.ct_gifts_manifold, 10) || 1
        }));
        cb(gifts);
      });
    }
  };

  // assets/js/src/gifts/local-knowledge.js
  var $7 = window.jQuery;
  var local_knowledge_default = {
    init() {
      const $container = $7("#cg-local-knowledge");
      api_default3.fetchLocalKnowledge((data) => {
        $container.text(data.name);
      });
    }
  };

  // assets/js/src/gifts/language.js
  var $8 = window.jQuery;
  var language_default = {
    init() {
      const $container = $8("#cg-language");
      api_default3.fetchLanguageGift((data) => {
        $container.text(data.name);
      });
    }
  };

  // assets/js/src/gifts/free-choices.js
  var $9 = window.jQuery;
  var free_choices_default = {
    /**
     * Initialize the three free-choice gift selectors,
     * filter by requirements, and wire up change events.
     */
    init() {
      state_default.init();
      api_default3.fetchFreeChoices((gifts) => {
        state_default.setList(gifts);
        console.log("[FreeChoices] State.gifts \u2192", state_default.gifts);
        console.log("[FreeChoices] State.selected \u2192", state_default.selected);
        const suffixes = [
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
        const selectedIds = state_default.selected.map(String);
        const available = gifts.filter((g) => {
          return suffixes.every((s) => {
            const key = s ? `ct_gifts_requires_${s}` : "ct_gifts_requires";
            const req = g[key];
            return !req || selectedIds.includes(String(req));
          });
        });
        console.log("[FreeChoices] after filtering \u2192", available);
        const $wrap = $9("#cg-free-choices").empty();
        for (let i = 0; i < 3; i++) {
          const selId = `cg-free-choice-${i}`;
          const prev = state_default.selected[i] || "";
          const options = available.map((g) => {
            const sel = g.id == prev ? " selected" : "";
            return `<option value="${g.id}"${sel}>${g.name}</option>`;
          }).join("");
          $wrap.append(`
          <select id="${selId}" data-index="${i}">
            <option value="">\u2014 Select Gift \u2014</option>
            ${options}
          </select>
        `);
        }
        $9(document).off("change", "#cg-free-choices select").on("change", "#cg-free-choices select", (e) => {
          const $sel = $9(e.currentTarget);
          const idx = $sel.data("index");
          const id = $sel.val();
          state_default.set(idx, id);
          const chosen = gifts.find((g) => String(g.id) === String(id));
          if (chosen) {
            state_default.setList([chosen]);
          }
          service_default.refreshAll();
        });
      });
    }
  };

  // assets/js/src/gifts/index.js
  var index_default = {
    init() {
      local_knowledge_default.init();
      language_default.init();
      free_choices_default.init();
    }
  };
  return __toCommonJS(index_exports);
})();
//# sourceMappingURL=gifts.bundle.js.map
