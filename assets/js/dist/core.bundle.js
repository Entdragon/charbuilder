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

  // assets/js/src/core/species/api.js
  var $ = window.jQuery;
  var SpeciesAPI = {
    /**
     * Populate the #cg-species dropdown.
     */
    loadSpeciesList(cb) {
      const $sel = $("#cg-species").html('<option value="">\u2014 Select Species \u2014</option>');
      $.post(CG_Ajax.ajax_url, {
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
      $.post(CG_Ajax.ajax_url, {
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
  var $2 = window.jQuery;
  var CareerAPI = {
    loadList(callback) {
      const $sel = $2("#cg-career").html('<option value="">\u2014 Select Career \u2014</option>');
      $2.post(CG_Ajax.ajax_url, {
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
      $2.post(CG_Ajax.ajax_url, {
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
      const $24 = window.jQuery;
      const freq = { d8: 0, d6: 0, d4: 0 };
      $24(".cg-trait-select").each(function() {
        const v = $24(this).val();
        if (v && v in freq) freq[v]++;
      });
      $24(".cg-trait-select").each(function() {
        const $sel = $24(this);
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
      const $24 = window.jQuery;
      const boosts = this.calculateBoostMap();
      TRAITS.forEach((traitKey) => {
        const $sel = $24(`#cg-${traitKey}`);
        const base = $sel.val() || "d4";
        const idx = DIE_ORDER.indexOf(base);
        const count = boosts[traitKey] || 0;
        const boosted = DIE_ORDER[Math.min(idx + count, DIE_ORDER.length - 1)];
        $24(`#cg-${traitKey}-adjusted`).text(boosted);
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
  var $3 = window.jQuery;
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
          will: $3("#cg-will").val(),
          speed: $3("#cg-speed").val(),
          body: $3("#cg-body").val(),
          mind: $3("#cg-mind").val(),
          trait_species: $3("#cg-trait_species").val(),
          trait_career: $3("#cg-trait_career").val(),
          profileSpecies: $3("#cg-species").val(),
          profileCareer: $3("#cg-career").val()
        });
      }, 0);
      return html;
    }
  };
  var form_builder_default = FormBuilder;

  // assets/js/src/core/formBuilder/index.js
  var $4 = window.jQuery;
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
      $4("#cg-form-container").html(
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
      d.name = $4("#cg-name").val();
      d.player_name = $4("#cg-player-name").val();
      d.age = $4("#cg-age").val();
      d.gender = $4("#cg-gender").val();
      d.motto = $4("#cg-motto").val();
      d.goal1 = $4("#cg-goal1").val();
      d.goal2 = $4("#cg-goal2").val();
      d.goal3 = $4("#cg-goal3").val();
      d.description = $4("#cg-description").val();
      d.backstory = $4("#cg-backstory").val();
      d.species_id = $4("#cg-species").val();
      d.career_id = $4("#cg-career").val();
      service_default.TRAITS.forEach((key) => {
        d[key] = $4(`#cg-${key}`).val();
      });
      const mergedMarks = __spreadValues({}, this._data.skillMarks || {});
      $4("input.skill-marks").each((i, el) => {
        const skillId = $4(el).data("skill-id");
        const val = parseInt($4(el).val(), 10) || 0;
        mergedMarks[skillId] = val;
      });
      d.skillMarks = mergedMarks;
      d.free_gifts = [
        $4("#cg-free-choice-0").val() || "",
        $4("#cg-free-choice-1").val() || "",
        $4("#cg-free-choice-2").val() || ""
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
      return $4.ajax({
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
      return $4.ajax({
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
      return $4.ajax({
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

  // assets/js/src/core/main/builder-ui.js
  var $5 = window.jQuery;
  var isDirty = false;
  function openBuilder({ isNew = false, payload = {} } = {}) {
    console.log("[BuilderUI] openBuilder() called with opts:", { isNew, payload });
    isDirty = false;
    $5("#cg-unsaved-confirm, #cg-unsaved-backdrop").hide().css("display", "none");
    const data = __spreadProps(__spreadValues({}, payload), {
      isNew
    });
    formBuilder_default.init(data);
    $5("#cg-modal-overlay, #cg-modal").removeClass("cg-hidden").addClass("cg-visible").css("display", "block");
    if (isNew) {
      $5('#cg-modal .cg-tabs li[data-tab="tab-traits"]').click();
    }
  }
  function closeBuilder() {
    $5("#cg-unsaved-backdrop").hide().css("display", "none");
    $5("#cg-unsaved-confirm").removeClass("cg-visible").addClass("cg-hidden").hide().css("display", "none");
    $5("#cg-modal, #cg-modal-overlay").removeClass("cg-visible").addClass("cg-hidden").fadeOut(200, () => {
      $5("#cg-form-container").empty();
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
    if (!$5("#cg-unsaved-backdrop").length) {
      $5('<div id="cg-unsaved-backdrop"></div>').appendTo("body");
    }
    const $backdrop = $5("#cg-unsaved-backdrop");
    const $prompt = $5("#cg-unsaved-confirm");
    if (!$prompt.parent().is("body")) {
      $prompt.appendTo("body");
    }
    $backdrop.show().css("display", "block");
    $prompt.removeClass("cg-hidden").addClass("cg-visible").show().css("display", "block");
  }
  function hideUnsaved() {
    $5("#cg-unsaved-backdrop").hide().css("display", "none");
    $5("#cg-unsaved-confirm").removeClass("cg-visible").addClass("cg-hidden").hide().css("display", "none");
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

  // assets/js/src/core/traits/events.js
  var $6 = window.jQuery;
  var bound = false;
  var events_default = {
    bind() {
      if (bound) return;
      bound = true;
      $6(function() {
        service_default.refreshAll();
      });
      $6(document).off("change", ".cg-trait-select").on("change", ".cg-trait-select", () => {
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

  // assets/js/src/core/species/render.js
  var $7 = window.jQuery;
  var render_default = {
    renderGifts(profile) {
      const gifts = [
        { label: "Species Gift One", name: profile.gift_1 },
        { label: "Species Gift Two", name: profile.gift_2 },
        { label: "Species Gift Three", name: profile.gift_3 }
      ].filter((g) => g.name);
      const html = gifts.map((g) => `<li><strong>${g.label}:</strong> ${g.name}</li>`).join("");
      $7("#species-gift-block").html(html);
    },
    clearUI() {
      $7("#species-gift-block").empty();
      $7("#cg-species").val("");
    }
  };

  // assets/js/src/core/species/events.js
  var $8 = window.jQuery;
  var events_default2 = {
    bound: false,
    bind() {
      if (this.bound) return;
      this.bound = true;
      $8(document).off("change", "#cg-species").on("change", "#cg-species", () => {
        const speciesId = $8("#cg-species").val();
        const data = formBuilder_default.getData();
        data.profile = data.profile || {};
        data.profile.species = speciesId;
        console.log("[SpeciesEvents] selected species \u2192", speciesId);
        if (!speciesId) {
          api_default.currentProfile = null;
          render_default.clearUI();
          service_default.refreshAll();
          return;
        }
        api_default.loadSpeciesProfile(speciesId, (profileData) => {
          api_default.currentProfile = profileData;
          data.profile = __spreadValues(__spreadValues({}, data.profile), profileData);
          console.log("[SpeciesEvents] loaded profile \u2192", data.profile);
          const spGifts = [1, 2, 3].map((i) => {
            const id = profileData[`gift_id_${i}`];
            const manifold = parseInt(profileData[`manifold_${i}`], 10) || 1;
            return id ? { id, ct_gifts_manifold: manifold } : null;
          }).filter(Boolean);
          state_default.setList(spGifts);
          render_default.renderGifts(profileData);
          service_default.refreshAll();
        });
      });
    }
  };

  // assets/js/src/core/species/index.js
  var $9 = window.jQuery;
  var species_default = {
    init() {
      console.log("[SpeciesIndex] init()");
      if (!events_default2.bound) {
        events_default2.bind();
      }
      const data = formBuilder_default.getData();
      api_default.loadSpeciesList(() => {
        if (data.profile && data.profile.species) {
          $9("#cg-species").val(data.profile.species).trigger("change");
        }
      });
    }
  };

  // assets/js/src/core/skills/render.js
  var $10 = window.jQuery;
  var MARK_DIE = {
    1: "d4",
    2: "d6",
    3: "d8"
  };
  var render_default2 = {
    render() {
      const skills = window.CG_SKILLS_LIST || [];
      const species = api_default.currentProfile || {};
      const career = api_default2.currentProfile || {};
      const data = formBuilder_default.getData();
      data.skillMarks = data.skillMarks || {};
      const MAX_MARKS = 13;
      const usedMarks = Object.values(data.skillMarks).reduce((sum, v) => sum + v, 0);
      const marksRemain = Math.max(0, MAX_MARKS - usedMarks);
      $10("#marks-remaining").remove();
      $10("#skills-table").before(`
      <div id="marks-remaining" class="marks-remaining">
        Marks Remaining: <strong>${marksRemain}</strong>
      </div>
    `);
      const $thead = $10("<thead>");
      $10("<tr>").append("<th>Skill</th>").append(`<th>${species.speciesName || ""}</th>`).append(`<th>${career.careerName || ""}</th>`).append("<th>Marks</th>").append("<th>Dice Pool</th>").appendTo($thead);
      const spSkills = [species.skill_one, species.skill_two, species.skill_three].map(String);
      const cpSkills = [career.skill_one, career.skill_two, career.skill_three].map(String);
      const $tbody = $10("<tbody>");
      skills.forEach((skill) => {
        const id = String(skill.id);
        const name = skill.name;
        const spDie = spSkills.includes(id) ? "d4" : "";
        const cpDie = cpSkills.includes(id) ? "d6" : "";
        const myMarks = data.skillMarks[id] || 0;
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
        const $row = $10("<tr>").append(`<td>${name}</td>`).append(`<td>${spDie || "\u2013"}</td>`).append(`<td>${cpDie || "\u2013"}</td>`).append(`<td>
                   <div class="marks-buttons">${buttonsHtml}</div>
                   <div class="marks-display">${markDisplay}</div>
                 </td>`).append(`<td>${poolStr}</td>`);
        $tbody.append($row);
      });
      $10("#skills-table").empty().append($thead).append($tbody);
    }
  };

  // assets/js/src/core/career/render.js
  var $11 = window.jQuery;
  var render_default3 = {
    /**
     * Render the three career gifts into your UI and then
     * re-render the Skills table so the career dice appear.
     */
    renderGifts(profile) {
      const gifts = [
        { label: "Career Gift One", name: profile.gift_1 },
        { label: "Career Gift Two", name: profile.gift_2 },
        { label: "Career Gift Three", name: profile.gift_3 }
      ].filter((g) => g.name);
      const html = gifts.map((g) => `<li><strong>${g.label}:</strong> ${g.name}</li>`).join("");
      $11("#career-gifts").html(html);
      render_default2.render();
    },
    /**
     * Clear the career‐gift UI and re-render Skills.
     */
    clearGifts() {
      $11("#career-gifts").empty();
      $11("#cg-career").val("");
      render_default2.render();
    }
  };

  // assets/js/src/core/career/events.js
  var $12 = window.jQuery;
  var events_default3 = {
    bound: false,
    bind() {
      if (this.bound) return;
      this.bound = true;
      $12(document).off("change", "#cg-career").on("change", "#cg-career", () => this.handleCareerChange());
    },
    handleCareerChange() {
      const careerId = $12("#cg-career").val();
      const data = formBuilder_default.getData();
      data.career = careerId;
      if (!careerId) {
        api_default2.currentProfile = null;
        data.profile = __spreadProps(__spreadValues({}, data.profile || {}), {
          careerName: "",
          gift_1: null,
          gift_2: null,
          gift_3: null,
          skill_one: null,
          skill_two: null,
          skill_three: null
        });
        render_default3.clearGifts();
        service_default.refreshAll();
        render_default2.render();
        return;
      }
      api_default2.loadGifts(careerId, (profile) => {
        api_default2.currentProfile = profile;
        data.profile = __spreadValues(__spreadValues({}, data.profile || {}), profile);
        const crGifts = [1, 2, 3].map((i) => {
          const id = profile[`gift_id_${i}`];
          const manifold = parseInt(profile[`manifold_${i}`], 10) || 1;
          return id ? { id, ct_gifts_manifold: manifold } : null;
        }).filter(Boolean);
        state_default.setList(crGifts);
        render_default3.renderGifts(profile);
        const sp = data.profile || {};
        if (sp.gift_id_1 || sp.gift_id_2 || sp.gift_id_3) {
          render_default.renderGifts(sp);
        }
        service_default.refreshAll();
        render_default2.render();
      });
    }
  };

  // assets/js/src/core/career/index.js
  var $13 = window.jQuery;
  var career_default = {
    init() {
      console.log("[CareerIndex] init()");
      events_default3.bind();
      const data = formBuilder_default.getData();
      api_default2.loadList(() => {
        if (data.career) {
          $13("#cg-career").val(data.career).trigger("change");
        }
      });
    }
  };

  // assets/js/src/gifts/api.js
  var $14 = window.jQuery;
  var api_default3 = {
    fetchLocalKnowledge(cb) {
      $14.post(CG_Ajax.ajax_url, {
        action: "cg_get_local_knowledge",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (res.success && typeof cb === "function") cb(res.data);
      });
    },
    fetchLanguageGift(cb) {
      $14.post(CG_Ajax.ajax_url, {
        action: "cg_get_language_gift",
        security: CG_Ajax.nonce
      }).done((res) => {
        if (res.success && typeof cb === "function") cb(res.data);
      });
    },
    fetchFreeChoices(cb) {
      $14.post(CG_Ajax.ajax_url, {
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
  var $15 = window.jQuery;
  var local_knowledge_default = {
    init() {
      const $container = $15("#cg-local-knowledge");
      api_default3.fetchLocalKnowledge((data) => {
        $container.text(data.name);
      });
    }
  };

  // assets/js/src/gifts/language.js
  var $16 = window.jQuery;
  var language_default = {
    init() {
      const $container = $16("#cg-language");
      api_default3.fetchLanguageGift((data) => {
        $container.text(data.name);
      });
    }
  };

  // assets/js/src/gifts/free-choices.js
  var $17 = window.jQuery;
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
        const $wrap = $17("#cg-free-choices").empty();
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
        $17(document).off("change", "#cg-free-choices select").on("change", "#cg-free-choices select", (e) => {
          const $sel = $17(e.currentTarget);
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
  var gifts_default = {
    init() {
      local_knowledge_default.init();
      language_default.init();
      free_choices_default.init();
    }
  };

  // assets/js/src/core/skills/events.js
  var $18 = window.jQuery;
  var events_default4 = {
    bind() {
      $18(document).off("click", "#tab-skills, #cg-species, #cg-career").on("click change", "#tab-skills, #cg-species, #cg-career", () => {
        render_default2.render();
      });
      $18(document).off("click", ".skill-mark-btn").on("click", ".skill-mark-btn", function() {
        const skillId = String($18(this).data("skill-id"));
        const mark = parseInt($18(this).data("mark"), 10);
        const data = formBuilder_default.getData();
        data.skillMarks = data.skillMarks || {};
        data.skillMarks[skillId] = mark;
        render_default2.render();
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
      events_default4.bind();
      render_default2.render();
    }
  };

  // assets/js/src/core/summary/utils.js
  function capitalize2(str) {
    if (typeof str !== "string") return "";
    const cleaned = str.replace(/[_\-]/g, " ");
    return cleaned.charAt(0).toUpperCase() + cleaned.slice(1);
  }

  // assets/js/src/core/summary/api.js
  var $19 = window.jQuery;
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
      const $sheet = $19("#cg-summary-sheet").empty();
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
        if (label === "species") label = "Species";
        else if (label === "career") label = "Career";
        else label = capitalize2(label);
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
      $19(document).off("click", "#cg-export-pdf").on("click", "#cg-export-pdf", (e) => {
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
  var api_default4 = SummaryAPI;

  // assets/js/src/core/summary/index.js
  var summary_default = {
    init() {
      api_default4.init();
    }
  };

  // assets/js/src/core/main/builder-refresh.js
  var $20 = window.jQuery;
  function refreshTab() {
    const tab = $20("#cg-modal .cg-tabs li.active").data("tab");
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
  var $21 = window.jQuery;
  function bindLoadEvents() {
    $21(document).off("click", "#cg-load-confirm").on("click", "#cg-load-confirm", (e) => {
      e.preventDefault();
      const charId = $21("#cg-splash-load-select").val();
      if (!charId) {
        alert("Please select a character.");
        return;
      }
      console.log("[BuilderLoad] #cg-load-confirm clicked \u2192 fetch ID", charId);
      formBuilder_default.fetchCharacter(charId).done((res) => {
        const parsed = typeof res === "string" ? JSON.parse(res) : res;
        const record = parsed.data || parsed;
        if (!record || !record.id) {
          alert("Could not load character.");
          return;
        }
        $21("#cg-modal-splash").removeClass("visible").addClass("cg-hidden");
        builder_ui_default.openBuilder({
          isNew: false,
          payload: record
        });
      }).fail((xhr, status, err) => {
        console.error("[BuilderLoad] fetchCharacter failed:", xhr.responseText);
        alert("Load error. See console.");
      });
    });
    $21(document).off("click", "#cg-splash-back").on("click", "#cg-splash-back", (e) => {
      e.preventDefault();
      console.log("[BuilderLoad] #cg-splash-back clicked \u2192 show NEW form");
      builder_ui_default.openBuilder({ isNew: true });
    });
  }

  // assets/js/src/core/main/builder-save.js
  var $22 = window.jQuery;
  function bindSaveEvents() {
    $22(document).off("click", ".cg-save-button").on("click", ".cg-save-button", function(e) {
      e.preventDefault();
      const closeAfter = $22(this).hasClass("cg-close-after-save");
      const payload = formBuilder_default.collectFormData();
      console.log("[BuilderSave] \u25B6 saving payload:", payload);
      $22.post(CG_Ajax.ajax_url, {
        action: "cg_save_character",
        character: payload,
        security: CG_Ajax.nonce
      }).done((res) => {
        if (!res.success) {
          console.error("[BuilderSave] server error:", res.data);
          return alert("Save error: " + res.data);
        }
        payload.id = res.data.id;
        formBuilder_default._data = __spreadValues(__spreadValues({}, formBuilder_default._data), payload);
        builder_ui_default.markClean();
        if (closeAfter) {
          builder_ui_default.closeBuilder();
        } else {
          alert("Character saved");
        }
      }).fail((xhr, err) => {
        console.error("[BuilderSave] AJAX error:", err);
        alert("AJAX save error \u2014 see console");
      });
    });
  }

  // assets/js/src/core/main/builder-events.js
  var $23 = window.jQuery;
  function bindUIEvents() {
    console.log("[BuilderEvents] bindUIEvents() called");
    $23(document).off("input change", "#cg-modal input, #cg-modal select, #cg-modal textarea").on("input change", "#cg-modal input, #cg-modal select, #cg-modal textarea", function() {
      builder_ui_default.markDirty();
      const $el = $23(this);
      if ($el.hasClass("skill-marks")) {
        const skillId = $el.data("skill-id");
        const val = parseInt($el.val(), 10) || 0;
        formBuilder_default._data.skillMarks = formBuilder_default._data.skillMarks || {};
        formBuilder_default._data.skillMarks[skillId] = val;
        return;
      }
      const id = this.id;
      if (!id) return;
      const key = id.replace(/^cg-/, "");
      formBuilder_default._data[key] = $el.val();
    });
    $23(document).off("click", "#cg-open-builder").on("click", "#cg-open-builder", (e) => {
      e.preventDefault();
      $23("#cg-modal-splash").removeClass("cg-hidden").addClass("visible");
      formBuilder_default.listCharacters().done((resp) => {
        const $sel = $23("#cg-splash-load-select").empty();
        resp.data.forEach((c) => {
          $sel.append(`<option value="${c.id}">${c.name}</option>`);
        });
      });
    });
    $23(document).off("click", "#cg-new-splash").on("click", "#cg-new-splash", (e) => {
      e.preventDefault();
      $23("#cg-modal-splash").removeClass("visible").addClass("cg-hidden");
      builder_ui_default.openBuilder({ isNew: true, payload: {} });
      formBuilder_default._data.skillMarks = {};
      formBuilder_default._data.species = "";
      formBuilder_default._data.career = "";
      if (window.CG_FreeChoicesState) {
        window.CG_FreeChoicesState.selected = ["", "", ""];
        window.CG_FreeChoicesState.gifts = [];
      }
    });
    $23(document).off("click", "#cg-load-splash").on("click", "#cg-load-splash", (e) => {
      e.preventDefault();
      const charId = $23("#cg-splash-load-select").val();
      if (!charId) {
        alert("Please select a character to load.");
        return;
      }
      formBuilder_default.fetchCharacter(charId).done((resp) => {
        console.log("\u{1F50E} [AJAX] raw cg_get_character response:", resp);
        const parsed = typeof resp === "string" ? JSON.parse(resp) : resp;
        console.log("\u{1F50D} [AJAX] parsed.data:", parsed.data);
        const record = parsed.data || parsed;
        if (!record || !record.id) {
          return alert("Character could not be loaded.");
        }
        $23("#cg-modal-splash").removeClass("visible").addClass("cg-hidden");
        builder_ui_default.openBuilder({
          isNew: false,
          payload: record
        });
      }).fail((xhr, status, err) => {
        console.error("Load failed:", xhr.responseText);
        alert("Could not load character. Check console for details.");
      });
    });
    bindLoadEvents();
    bindSaveEvents();
    $23(document).off("click", "#cg-modal .cg-tabs li").on("click", "#cg-modal .cg-tabs li", function(e) {
      e.preventDefault();
      const tabName = $23(this).data("tab");
      $23("#cg-modal .cg-tabs li").removeClass("active");
      $23(this).addClass("active");
      $23(".tab-panel").removeClass("active");
      $23(`#${tabName}`).addClass("active");
      refreshTab();
    });
    $23(document).off("click", "#cg-modal-close").on("click", "#cg-modal-close", (e) => {
      e.preventDefault();
      builder_ui_default.showUnsaved();
    });
    $23(document).off("click", "#cg-modal-overlay").on("click", "#cg-modal-overlay", function(e) {
      if (e.target !== this) return;
      builder_ui_default.showUnsaved();
    });
    $23(document).off("click", "#unsaved-save").on("click", "#unsaved-save", (e) => {
      e.preventDefault();
      console.log("[BuilderEvents] Prompt: SAVE & EXIT clicked");
      formBuilder_default.save(true);
    });
    $23(document).off("click", "#unsaved-exit").on("click", "#unsaved-exit", (e) => {
      e.preventDefault();
      builder_ui_default.closeBuilder();
    });
    $23(document).off("click", "#unsaved-cancel").on("click", "#unsaved-cancel", (e) => {
      e.preventDefault();
      builder_ui_default.hideUnsaved();
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
})();
//# sourceMappingURL=core.bundle.js.map
