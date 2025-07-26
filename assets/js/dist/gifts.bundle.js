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
  var gifts_exports = {};
  __export(gifts_exports, {
    default: () => gifts_default
  });

  // assets/js/src/core/species/api.js
  var $2 = window.jQuery;
  var SpeciesAPI = {
    /**
     * Populate the #cg-species dropdown.
     */
    loadSpeciesList(cb) {
      console.group("[SpeciesAPI] \u{1F504} loadSpeciesList() called");
      const $sel = $2("#cg-species");
      if (!$sel.length) {
        console.warn("[SpeciesAPI] \u274C Selector #cg-species not found in DOM");
        console.groupEnd();
        return;
      }
      $sel.html('<option value="">\u2014 Select Species \u2014</option>');
      $2.post(CG_Ajax.ajax_url, {
        action: "cg_get_species_list",
        security: CG_Ajax.nonce
      }).done((res) => {
        console.log("[SpeciesAPI] \u2705 AJAX success:", res);
        if (!res.success) {
          console.warn("[SpeciesAPI] \u274C Species list response unsuccessful");
          return;
        }
        if (!Array.isArray(res.data)) {
          console.warn("[SpeciesAPI] \u26A0\uFE0F Unexpected data format:", res.data);
          return;
        }
        res.data.forEach(({ id, name }) => {
          $sel.append(`<option value="${id}">${name}</option>`);
          console.log(`[SpeciesAPI] \u2795 Added species option: ${name} (ID: ${id})`);
        });
        const currentVal = $sel.data("selected") || "";
        if (currentVal) {
          $sel.val(currentVal);
          console.log(`[SpeciesAPI] \u{1F501} Re-applying selected species \u2192 ${currentVal}`);
        } else {
          console.log("[SpeciesAPI] \u2139\uFE0F No selected species to re-apply");
        }
      }).fail((xhr, status, error) => {
        console.error("[SpeciesAPI] \u274C AJAX failed for species list:", {
          status,
          error,
          response: xhr.responseText
        });
      }).always(() => {
        if (typeof cb === "function") {
          console.log("[SpeciesAPI] \u{1F4DE} Executing loadSpeciesList callback");
          cb();
        } else {
          console.log("[SpeciesAPI] \u26A0\uFE0F No callback provided to loadSpeciesList");
        }
        console.groupEnd();
      });
    },
    /**
     * Fetch the full profile for one species (gifts, skills, etc).
     */
    loadSpeciesProfile(speciesId, cb) {
      console.group(`[SpeciesAPI] \u{1F504} loadSpeciesProfile(${speciesId}) called`);
      if (!speciesId) {
        console.warn("[SpeciesAPI] \u2757 No species ID provided");
        console.groupEnd();
        return;
      }
      $2.post(CG_Ajax.ajax_url, {
        action: "cg_get_species_profile",
        id: speciesId,
        security: CG_Ajax.nonce
      }).done((res) => {
        console.log("[SpeciesAPI] \u2705 AJAX success for profile:", res);
        if (res.success && typeof cb === "function") {
          console.log("[SpeciesAPI] \u{1F4DE} Executing profile callback with data:", res.data);
          cb(res.data);
        } else if (!res.success) {
          console.warn("[SpeciesAPI] \u274C Profile load was not successful");
        } else {
          console.warn("[SpeciesAPI] \u26A0\uFE0F No valid callback function provided");
        }
      }).fail((xhr, status, error) => {
        console.error("[SpeciesAPI] \u274C AJAX failed for species profile:", {
          status,
          error,
          response: xhr.responseText
        });
      }).always(() => {
        console.groupEnd();
      });
    }
  };
  var api_default = SpeciesAPI;

  // assets/js/src/core/career/api.js
  var CareerAPI = {
    /**
     * Load the career dropdown list.
     */
    loadList(callback) {
      console.group("[CareerAPI] \u{1F504} loadList() called");
      const $sel = $("#cg-career");
      if (!$sel.length) {
        console.warn("[CareerAPI] \u274C Selector #cg-career not found in DOM");
        console.groupEnd();
        return;
      }
      $sel.html('<option value="">\u2014 Select Career \u2014</option>');
      $.post(CG_Ajax.ajax_url, {
        action: "cg_get_career_list",
        security: CG_Ajax.nonce
      }).done((res) => {
        console.log("[CareerAPI] \u2705 AJAX success for career list:", res);
        if (!res.success) {
          console.warn("[CareerAPI] \u274C Career list response unsuccessful");
          return;
        }
        if (!Array.isArray(res.data)) {
          console.warn("[CareerAPI] \u26A0\uFE0F Unexpected response format for career list:", res.data);
          return;
        }
        res.data.forEach(({ id, name }) => {
          $sel.append(`<option value="${id}">${name}</option>`);
          console.log(`[CareerAPI] \u2795 Added option: ${name} (ID: ${id})`);
        });
        const currentVal = $sel.data("selected") || "";
        if (currentVal) {
          $sel.val(currentVal);
          console.log(`[CareerAPI] \u{1F501} Re-applying selected career \u2192 ${currentVal}`);
        } else {
          console.log("[CareerAPI] \u2139\uFE0F No selected career to re-apply");
        }
      }).fail((xhr, status, error) => {
        console.error("[CareerAPI] \u274C AJAX failed for career list:", {
          status,
          error,
          response: xhr.responseText
        });
      }).always(() => {
        if (typeof callback === "function") {
          console.log("[CareerAPI] \u{1F4DE} Executing loadList callback");
          callback();
        } else {
          console.log("[CareerAPI] \u26A0\uFE0F No callback provided to loadList");
        }
        console.groupEnd();
      });
    },
    /**
     * Load the gift profile for a selected career.
     */
    loadGifts(careerId, callback) {
      console.group(`[CareerAPI] \u{1F50D} loadGifts(${careerId}) called`);
      if (!careerId) {
        console.warn("[CareerAPI] \u2757 No career ID provided");
        console.groupEnd();
        return;
      }
      $.post(CG_Ajax.ajax_url, {
        action: "cg_get_career_gifts",
        id: careerId,
        security: CG_Ajax.nonce
      }).done((res) => {
        console.log("[CareerAPI] \u2705 AJAX success for career gifts:", res);
        if (res.success && typeof callback === "function") {
          console.log("[CareerAPI] \u{1F4DE} Executing loadGifts callback with data:", res.data);
          callback(res.data);
        } else if (!res.success) {
          console.warn("[CareerAPI] \u274C Response unsuccessful for career gifts");
        } else {
          console.warn("[CareerAPI] \u26A0\uFE0F No valid callback provided for career gifts");
        }
      }).fail((xhr, status, error) => {
        console.error(`[CareerAPI] \u274C AJAX failed for career gifts ID ${careerId}:`, {
          status,
          error,
          response: xhr.responseText
        });
      }).always(() => {
        console.groupEnd();
      });
    }
  };
  var api_default2 = CareerAPI;

  // assets/js/src/core/traits/service.js
  console.log("\u{1F525} TraitsService loaded and exposing globally");
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
    calculateBoostMap() {
      console.group("[TraitsService] \u{1F9EE} calculateBoostMap()");
      const map = {};
      function addGift(giftId) {
        console.log(`[TraitsService] \u2795 addGift() called with ID: ${giftId}`);
        if (!giftId)
          return;
        const gift = state_default.getGiftById(giftId);
        if (!gift) {
          console.warn(`[TraitsService] \u26A0\uFE0F Gift not found: ${giftId}`);
          return;
        }
        console.log(`[TraitsService] \u{1F381} Gift resolved: ${gift.name} (ID ${gift.id})`);
        const traitKey = BOOSTS[gift.id];
        if (!traitKey) {
          console.log(`[TraitsService] \u{1F6D1} No trait boost associated with gift ID ${gift.id}`);
          return;
        }
        const count = parseInt(gift.ct_gifts_manifold, 10) || 1;
        map[traitKey] = (map[traitKey] || 0) + count;
        console.log(`[TraitsService] \u2705 Boost applied \u2192 ${traitKey} +${count}`);
      }
      console.log("[TraitsService] \u2795 GiftsState.selected:", state_default.selected);
      state_default.selected.forEach(addGift);
      const sp = api_default.currentProfile;
      console.log("[TraitsService] \u{1F43E} SpeciesService.currentProfile:", sp);
      if (sp) {
        console.log("[TraitsService] \u{1F43E} Processing species gifts");
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((key) => addGift(sp[key]));
      }
      const cp = api_default2.currentProfile;
      console.log("[TraitsService] \u{1F9E2} CareerService.currentProfile:", cp);
      if (cp) {
        console.log("[TraitsService] \u{1F9E2} Processing career gifts");
        ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((key) => addGift(cp[key]));
      }
      console.log("[TraitsService] \u2705 Final boost map:", map);
      console.groupEnd();
      return map;
    },
    enforceCounts() {
      console.group("[TraitsService] \u2705 enforceCounts()");
      const $9 = window.jQuery;
      const freq = { d8: 0, d6: 0, d4: 0 };
      $9(".cg-trait-select").each(function() {
        const v = $9(this).val();
        if (v && v in freq)
          freq[v]++;
      });
      console.log("[TraitsService] \u{1F4CA} Dice usage counts \u2192", freq);
      $9(".cg-trait-select").each(function() {
        const $sel = $9(this);
        const current = $sel.val() || "";
        let options = '<option value="">\u2014 Select \u2014</option>';
        DICE_TYPES.forEach((die) => {
          if (freq[die] < MAX_COUNT[die] || current === die) {
            const sel = current === die ? " selected" : "";
            options += `<option value="${die}"${sel}>${die}</option>`;
          }
        });
        console.log(`[TraitsService] \u{1F501} Refreshing dropdown for #${$sel.attr("id")} \u2192 current: "${current}"`);
        $sel.html(options);
      });
      console.groupEnd();
    },
    updateAdjustedDisplays() {
      console.group("[TraitsService] \u{1F680} updateAdjustedDisplays()");
      const $9 = window.jQuery;
      const boosts = this.calculateBoostMap();
      TRAITS.forEach((traitKey) => {
        const $sel = $9(`#cg-${traitKey}`);
        if (!$sel.length) {
          console.warn(`[TraitsService] \u26A0\uFE0F Selector not found: #cg-${traitKey}`);
          return;
        }
        const raw = $sel.val();
        console.log(`[TraitsService] \u{1F50D} Trait: ${traitKey} | Raw: "${raw}"`);
        let label;
        if (!raw || !DIE_ORDER.includes(raw)) {
          label = "\u2013";
        } else {
          const idx = DIE_ORDER.indexOf(raw);
          const step = boosts[traitKey] || 0;
          const cap = Math.min(idx + step, DIE_ORDER.length - 1);
          label = DIE_ORDER[cap];
        }
        const count = boosts[traitKey] || 0;
        let suffix = "";
        if (count === 1)
          suffix = " (increased by gift)";
        else if (count > 1)
          suffix = ` (increased by gift ${count} times)`;
        console.log(`[TraitsService] \u{1F3B2} ${traitKey}: ${label}${suffix}`);
        $9(`#cg-${traitKey}-adjusted`).text(label + suffix);
      });
      console.groupEnd();
    },
    refreshAll() {
      console.group("[TraitsService] \u{1F501} refreshAll()");
      this.enforceCounts();
      this.updateAdjustedDisplays();
      console.groupEnd();
    },
    getBoostedDie(traitKey) {
      console.group(`[TraitsService] \u2795 getBoostedDie("${traitKey}")`);
      const boosts = this.calculateBoostMap();
      const cnt = boosts[traitKey] || 0;
      const base = window.jQuery(`#cg-${traitKey}`).val();
      console.log(`[TraitsService] \u{1F50E} Base: "${base}", Boost: ${cnt}`);
      if (!DIE_ORDER.includes(base)) {
        console.log('[TraitsService] \u26D4 Invalid base die \u2014 returning "\u2013"');
        console.groupEnd();
        return "\u2013";
      }
      const idx = DIE_ORDER.indexOf(base);
      const result = DIE_ORDER[Math.min(idx + cnt, DIE_ORDER.length - 1)];
      console.log(`[TraitsService] \u2705 Result: ${result}`);
      console.groupEnd();
      return result;
    }
  };
  window.TraitsService = TraitsService;
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
      console.log("[RenderDetails] renderTabs() called");
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
      console.group("[RenderDetails] renderContent()");
      console.log("[RenderDetails] Input data:", data);
      const boostMap = service_default.calculateBoostMap();
      console.log("[RenderDetails] boostMap result:", boostMap);
      const traitFields = TRAITS2.map((trait) => {
        const rawVal = data[trait];
        const val = rawVal == null || rawVal === "" ? "" : String(rawVal);
        const label = trait === "trait_species" ? "Species" : trait === "trait_career" ? "Career" : capitalize(trait);
        console.log(`\u25B6\uFE0F [RenderDetails] Trait: ${trait}`);
        console.log(`    \u21B3 Raw value: "${rawVal}"`);
        console.log(`    \u21B3 Cleaned val: "${val}"`);
        console.log(`    \u21B3 Label: "${label}"`);
        let options = [`<option value="">\u2014 Select \u2014</option>`];
        DICE.forEach((die) => {
          const sel = die === val ? " selected" : "";
          options.push(`<option value="${die}"${sel}>${die}</option>`);
        });
        const boostCount = boostMap[trait] || 0;
        let adjusted = "\u2013";
        if (DICE.includes(val)) {
          const DIE_ORDER2 = ["d4", "d6", "d8", "d10", "d12"];
          const baseIndex = DIE_ORDER2.indexOf(val);
          const boostedIndex = Math.min(baseIndex + boostCount, DIE_ORDER2.length - 1);
          adjusted = DIE_ORDER2[boostedIndex];
          console.log(`    \u21B3 BaseIndex: ${baseIndex}, BoostCount: ${boostCount}, Adjusted: ${adjusted}`);
        }
        let suffix = "";
        if (boostCount === 1)
          suffix = " (increased by gift)";
        else if (boostCount > 1)
          suffix = ` (increased by gift ${boostCount} times)`;
        const display = adjusted + suffix;
        console.log(`    \u21B3 Final display: ${display}`);
        return `
        <div class="cg-trait">
          <label>${label} <small>(choose one)</small></label>
          <select id="cg-${trait}" class="cg-trait-select">
            ${options.join("\n")}
          </select>
          <div
            class="trait-adjusted"
            id="cg-${trait}-adjusted"
            style="color:#0073aa;font-weight:bold;"
          >${display}</div>
        </div>
      `;
      }).join("");
      console.groupEnd();
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
                  <option value="">\u2014 Select \u2014</option>
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
      console.group("[RenderProfile] renderContent() called");
      console.log("[RenderProfile] Incoming data:", data);
      console.log("[RenderProfile] species_id:", data.species_id || "");
      console.log("[RenderProfile] career_id:", data.career_id || "");
      const html = `
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
      console.groupEnd();
      return html;
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
      console.groupCollapsed("\u{1F6E0}\uFE0F [FormBuilder] buildForm()");
      console.log("\u{1F4E6} Incoming data payload:", JSON.stringify(data, null, 2));
      console.log("\u{1F9F1} [FormBuilder] Rendering tab panels...");
      const html = `
<form id="cg-form">

  ${render_details_default.renderTabs()}
  <div class="cg-tab-wrap">
    ${render_details_default.renderContent(data)}
    ${render_profile_default.renderContent(data)}
    ${render_skills_default.renderContent(data)}
    ${render_summary_default.renderContent(data)}
  </div>

  <input type="hidden" id="cg-id" value="${data.id || ""}" />
  <div class="cg-form-buttons">
    <button type="button" class="cg-save-button">\u{1F4BE} Save</button>
    <button type="button" class="cg-save-button cg-close-after-save">\u{1F4BE} Save & Close</button>
  </div>
</form>`;
      console.log("\u2705 [FormBuilder] HTML markup generated.");
      console.groupEnd();
      setTimeout(() => {
        console.group("\u23F3 [FormBuilder] Post-render UI sync");
        const currentValues = {
          will: $3("#cg-will").val(),
          speed: $3("#cg-speed").val(),
          body: $3("#cg-body").val(),
          mind: $3("#cg-mind").val(),
          trait_species: $3("#cg-trait_species").val(),
          trait_career: $3("#cg-trait_career").val(),
          profileSpecies: $3("#cg-species").val(),
          profileCareer: $3("#cg-career").val()
        };
        console.table(currentValues);
        if ($3("#cg-id").val()) {
          console.log("[FormBuilder] \u{1F194} Character ID:", $3("#cg-id").val());
        } else {
          console.log("[FormBuilder] \u2139\uFE0F No character ID found (new character)");
        }
        console.log("\u{1F501} [FormBuilder] Calling TraitsService.refreshAll()");
        service_default.refreshAll();
        console.log("\u2705 [FormBuilder] Traits UI synced and boosted values rendered");
        console.groupEnd();
      }, 0);
      console.log("\u{1F680} [FormBuilder] Returning form HTML");
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
      console.log("[FormBuilderAPI] \u{1F680} init() called with payload:", payload);
      this._data = __spreadValues({}, payload);
      this.isNew = Boolean(payload.isNew);
      this.hasData = !this.isNew;
      console.log("[FormBuilderAPI] \u{1F4E6} Initial state:", {
        _data: this._data,
        isNew: this.isNew,
        hasData: this.hasData
      });
      const html = form_builder_default.buildForm(this._data);
      $4("#cg-form-container").html(html);
      console.log("[FormBuilderAPI] \u{1F9F1} Form rendered");
    },
    /**
     * Return a shallow copy of the in-memory data.
     */
    getData() {
      console.log("[FormBuilderAPI] \u{1F4E4} getData() called");
      return __spreadValues({}, this._data);
    },
    /**
     * Read every form field from the DOM into a single payload object,
     * merging in-memory skillMarks to avoid losing them when inputs
     * aren't present on the current tab.
     */
    collectFormData() {
      console.log("[FormBuilderAPI] \u{1F5C3}\uFE0F collectFormData() called");
      const d = {};
      if (this._data.id) {
        d.id = this._data.id;
        console.log("[FormBuilderAPI] \u23F3 Merging existing ID:", d.id);
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
      d.skillMarks = {};
      $4("input.skill-marks").each((i, el) => {
        const skillId = $4(el).data("skill-id");
        d.skillMarks[skillId] = parseInt($4(el).val(), 10) || 0;
      });
      d.free_gifts = [
        $4("#cg-free-choice-0").val() || "",
        $4("#cg-free-choice-1").val() || "",
        $4("#cg-free-choice-2").val() || ""
      ];
      d.traits = {};
      $4(".cg-trait-select").each((i, sel) => {
        const key = $4(sel).attr("id").replace("cg-", "");
        const val = $4(sel).val();
        d.traits[key] = val;
      });
      console.log("[FormBuilderAPI] \u{1F4E6} Form data collected:", d);
      return d;
    },
    /**
     * Save the character via WP-AJAX and optionally close builder.
     *
     * @param {boolean} shouldClose
     * @returns {Promise}
     */
    save(shouldClose = false) {
      console.log("[FormBuilderAPI] \u{1F4BE} save() called. shouldClose:", shouldClose);
      const payload = this.collectFormData();
      return $4.ajax({
        url: CG_Ajax.ajax_url,
        method: "POST",
        data: {
          action: "cg_save_character",
          character: payload,
          security: CG_Ajax.nonce
        }
      }).done((res) => {
        console.log("[FormBuilderAPI] \u2705 save done. Response:", res);
        if (!res.success) {
          console.error("[FormBuilderAPI] \u274C save error:", res.data);
          alert("Save failed: " + res.data);
          return;
        }
        this._data = __spreadProps(__spreadValues({}, this._data), { id: res.data.id });
        this.isNew = false;
        this.hasData = true;
        console.log("[FormBuilderAPI] \u{1F4BE} save complete. Updated state:", {
          _data: this._data,
          isNew: this.isNew,
          hasData: this.hasData
        });
        FormBuilderAPI.onSaveClean();
        if (shouldClose) {
          FormBuilderAPI.onSaveClose();
        }
      }).fail((xhr, status, err) => {
        console.error("[FormBuilderAPI] \u274C save failed", status, err, xhr.responseText);
        alert("Save failed\u2014check console for details.");
      });
    },
    /**
     * Fetch a list of saved characters (for the Load splash).
     */
    listCharacters() {
      console.log("[FormBuilderAPI] \u{1F4C4} listCharacters() called");
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
      console.log("[FormBuilderAPI] \u{1F4E5} fetchCharacter() called with ID:", id);
      return $4.ajax({
        url: CG_Ajax.ajax_url,
        method: "POST",
        data: {
          action: "cg_get_character",
          id,
          security: CG_Ajax.nonce
        }
      });
    },
    // Hooks assigned by builder-ui.js:
    onSaveClean: () => {
      console.log("[FormBuilderAPI] \u{1F9F9} onSaveClean() called");
    },
    onSaveClose: () => {
      console.log("[FormBuilderAPI] \u{1F6D1} onSaveClose() called");
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
      console.group("[FreeChoicesState] \u{1F504} init() called");
      const data = formBuilder_default.getData();
      const initial = Array.isArray(data.freeGifts) ? data.freeGifts : ["", "", ""];
      this.selected = initial;
      console.log("[FreeChoicesState] \u{1F4E5} Fetched from FormBuilderAPI \u2192", data);
      console.log("[FreeChoicesState] \u2705 Initial free gift selection set \u2192", this.selected);
      console.groupEnd();
    },
    /**
     * Update one slot and persist back into FormBuilder’s data.
     */
    set(index, id) {
      console.group(`[FreeChoicesState] \u{1F4DD} set(index=${index}, id=${id})`);
      if (index < 0 || index >= 3) {
        console.warn(`[FreeChoicesState] \u26A0\uFE0F Invalid index: ${index}`);
        console.groupEnd();
        return;
      }
      this.selected[index] = id;
      const data = formBuilder_default.getData();
      data.freeGifts = [...this.selected];
      formBuilder_default._data.freeGifts = [...this.selected];
      console.log("[FreeChoicesState] \u{1F4CC} Updated internal selected array:", this.selected);
      console.log("[FreeChoicesState] \u{1F504} Synced with FormBuilderAPI._data.freeGifts:", formBuilder_default._data.freeGifts);
      console.groupEnd();
    },
    /**
     * Merge incoming gift objects into our master list,
     * keeping manifold, requires, etc.
     */
    setList(giftList = []) {
      console.group("[FreeChoicesState] \u{1F4E6} setList() called");
      console.log("[FreeChoicesState] \u{1F4BE} Received gift list with", giftList.length, "items");
      giftList.forEach((g) => {
        const idStr = String(g.id);
        const idx = this.gifts.findIndex((x) => String(x.id) === idStr);
        if (idx > -1) {
          console.log(`[FreeChoicesState] \u{1F501} Updating existing gift ID ${idStr}`);
          this.gifts[idx] = __spreadValues(__spreadValues({}, this.gifts[idx]), g);
        } else {
          console.log(`[FreeChoicesState] \u2795 Adding new gift ID ${idStr}`);
          this.gifts.push(g);
        }
      });
      console.log("[FreeChoicesState] \u{1F4CA} Updated gift cache \u2192", this.gifts);
      console.groupEnd();
    },
    /**
     * Find one gift object by its ID.
     */
    getGiftById(id) {
      console.group(`[FreeChoicesState] \u{1F50D} getGiftById(${id})`);
      const result = this.gifts.find((g) => String(g.id) === String(id));
      if (result) {
        console.log(`[FreeChoicesState] \u2705 Found gift object \u2192`, result);
      } else {
        console.warn(`[FreeChoicesState] \u274C Gift ID ${id} not found in current list`);
      }
      console.groupEnd();
      return result;
    }
  };
  window.CG_FreeChoicesState = State;
  console.log("\u{1F525} [FreeChoicesState] Module loaded & available as CG_FreeChoicesState");
  var state_default = State;

  // assets/js/src/gifts/api.js
  var $5 = window.jQuery;
  console.log("\u{1F525} [GiftsAPI] Module loaded");
  var api_default3 = {
    fetchLocalKnowledge(cb) {
      console.log("[GiftsAPI] \u{1F4E1} fetchLocalKnowledge() called");
      $5.post(CG_Ajax.ajax_url, {
        action: "cg_get_local_knowledge",
        security: CG_Ajax.nonce
      }).done((res) => {
        console.log("[GiftsAPI] \u2705 Response from cg_get_local_knowledge:", res);
        if (res.success && typeof cb === "function") {
          console.log("[GiftsAPI] \u{1F4DE} Calling callback with data:", res.data);
          cb(res.data);
        } else {
          console.warn("[GiftsAPI] \u274C Unsuccessful or invalid callback");
        }
      }).fail((xhr, status, error) => {
        console.error("[GiftsAPI] \u274C AJAX error in fetchLocalKnowledge:", { status, error, response: xhr.responseText });
      });
    },
    fetchLanguageGift(cb) {
      console.log("[GiftsAPI] \u{1F4E1} fetchLanguageGift() called");
      $5.post(CG_Ajax.ajax_url, {
        action: "cg_get_language_gift",
        security: CG_Ajax.nonce
      }).done((res) => {
        console.log("[GiftsAPI] \u2705 Response from cg_get_language_gift:", res);
        if (res.success && typeof cb === "function") {
          console.log("[GiftsAPI] \u{1F4DE} Calling callback with data:", res.data);
          cb(res.data);
        } else {
          console.warn("[GiftsAPI] \u274C Unsuccessful or invalid callback");
        }
      }).fail((xhr, status, error) => {
        console.error("[GiftsAPI] \u274C AJAX error in fetchLanguageGift:", { status, error, response: xhr.responseText });
      });
    },
    fetchFreeChoices(cb) {
      console.log("[GiftsAPI] \u{1F4E1} fetchFreeChoices() called");
      $5.post(CG_Ajax.ajax_url, {
        action: "cg_get_free_gifts",
        security: CG_Ajax.nonce
      }).done((res) => {
        console.log("[GiftsAPI] \u2705 Response from cg_get_free_gifts:", res);
        if (!res.success || typeof cb !== "function") {
          console.warn("[GiftsAPI] \u274C Unsuccessful or no callback provided");
          return;
        }
        const gifts = res.data.map((g) => {
          const parsed = __spreadProps(__spreadValues({}, g), {
            id: String(g.id),
            name: g.name,
            ct_gifts_manifold: parseInt(g.ct_gifts_manifold, 10) || 1
          });
          console.log(`[GiftsAPI] \u{1F9EA} Parsed gift \u2192 ID: ${parsed.id}, Name: "${parsed.name}", Manifold: ${parsed.ct_gifts_manifold}`);
          return parsed;
        });
        console.log("[GiftsAPI] \u{1F4E6} Parsed gift list ready. Calling callback...");
        cb(gifts);
      }).fail((xhr, status, error) => {
        console.error("[GiftsAPI] \u274C AJAX error in fetchFreeChoices:", { status, error, response: xhr.responseText });
      });
    }
  };

  // assets/js/src/gifts/local-knowledge.js
  var $6 = window.jQuery;
  var local_knowledge_default = {
    init() {
      const $container = $6("#cg-local-knowledge");
      api_default3.fetchLocalKnowledge((data) => {
        $container.text(data.name);
      });
    }
  };

  // assets/js/src/gifts/language.js
  var $7 = window.jQuery;
  var language_default = {
    init() {
      const $container = $7("#cg-language");
      api_default3.fetchLanguageGift((data) => {
        $container.text(data.name);
      });
    }
  };

  // assets/js/src/gifts/free-choices.js
  var $8 = window.jQuery;
  console.log("\u{1F525} [FreeChoices] Module loaded");
  var free_choices_default = {
    /**
     * Initialize the three free-choice gift selectors,
     * filter by requirements, and wire up change events.
     */
    init() {
      console.group("[FreeChoices] \u{1F501} init() called");
      console.log("[FreeChoices] \u{1F9E0} Initializing saved state");
      state_default.init();
      console.log("[FreeChoices] \u{1F4E1} Fetching free-choice gifts via API");
      api_default3.fetchFreeChoices((gifts) => {
        console.log("[FreeChoices] \u2705 Gift data received:", gifts);
        state_default.setList(gifts);
        console.log("[FreeChoices] \u{1F4E6} State.gifts \u2192", state_default.gifts);
        console.log("[FreeChoices] \u{1F5C2}\uFE0F State.selected \u2192", state_default.selected);
        console.log("[FreeChoices] \u{1F50D} Filtering available gifts...");
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
        console.log("[FreeChoices] \u2705 Filtered gift list:", available);
        console.log("[FreeChoices] \u{1F58C}\uFE0F Rendering gift dropdowns...");
        const $wrap = $8("#cg-free-choices").empty();
        for (let i = 0; i < 3; i++) {
          const selId = `cg-free-choice-${i}`;
          const prev = state_default.selected[i] || "";
          const options = available.map((g) => {
            const sel = g.id == prev ? " selected" : "";
            return `<option value="${g.id}"${sel}>${g.name}</option>`;
          }).join("");
          console.log(`[FreeChoices] \u{1F3A8} Rendering dropdown ${i} \u2192 selected gift ID: ${prev}`);
          $wrap.append(`
          <select id="${selId}" data-index="${i}">
            <option value="">\u2014 Select Gift \u2014</option>
            ${options}
          </select>
        `);
        }
        console.log("[FreeChoices] \u{1F9F7} Binding change handlers for dropdowns...");
        $8(document).off("change", "#cg-free-choices select").on("change", "#cg-free-choices select", (e) => {
          const $sel = $8(e.currentTarget);
          const idx = $sel.data("index");
          const id = $sel.val();
          console.log(`[FreeChoices] \u{1F4DD} Dropdown change \u2014 index: ${idx}, selected ID: ${id}`);
          state_default.set(idx, id);
          const chosen = gifts.find((g) => String(g.id) === String(id));
          if (chosen) {
            console.log("[FreeChoices] \u{1F4E5} Merging selected gift into state:", chosen);
            state_default.setList([chosen]);
          } else {
            console.log("[FreeChoices] \u26A0\uFE0F Selected gift not found in list");
          }
          console.log("[FreeChoices] \u{1F501} Calling TraitsService.refreshAll() after selection");
          service_default.refreshAll();
        });
        console.groupEnd();
      });
    }
  };

  // assets/js/src/gifts/index.js
  console.log("\u{1F525} [Gifts] Module loaded");
  var gifts_default = {
    init() {
      console.group("[Gifts] \u{1F501} init() called");
      console.log("[Gifts] \u25B6 Initializing Local Knowledge");
      local_knowledge_default.init();
      console.log("[Gifts] \u2705 Local Knowledge initialized");
      console.log("[Gifts] \u25B6 Initializing Language");
      language_default.init();
      console.log("[Gifts] \u2705 Language initialized");
      console.log("[Gifts] \u25B6 Initializing Free Choices");
      free_choices_default.init();
      console.log("[Gifts] \u2705 Free Choices initialized");
      console.groupEnd();
    }
  };
  return __toCommonJS(gifts_exports);
})();
//# sourceMappingURL=gifts.bundle.js.map
