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

  // assets/js/src/core/formBuilder/form-builder.js
  var require_form_builder = __commonJS({
    "assets/js/src/core/formBuilder/form-builder.js"() {
      (function($2) {
        window.CG_FormBuilder = {
          capitalize: (str) => str.charAt(0).toUpperCase() + str.slice(1),
          safe: (str) => (str || "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;"),
          buildForm(data = {}) {
            const cap = this.capitalize;
            const TRAITS = window.CG_Traits.TRAITS;
            const DICE = CG_Traits.DICE_TYPES;
            return `
<form id="cg-form">

  <ul class="cg-tabs">
    <li data-tab="tab-traits" class="active">Details & Traits</li>
    <li data-tab="tab-profile">Profile<br>(Species/Career/Gifts)</li>
    <li data-tab="tab-skills">Skills</li>
    <li data-tab="tab-summary">Summary</li>
  </ul>

  <div class="cg-tab-wrap">

    <!-- DETAILS & TRAITS -->
    <div id="tab-traits" class="tab-panel active">
      <div class="cg-nav-buttons"><button type="button" class="cg-nav-next">Next \u2192</button></div>
      <div class="cg-details-panel">

        <!-- DETAILS BOX -->
        <div class="cg-details-box">
          <h3>Details</h3>
          <label>Name</label><input type="text" id="cg-name" value="${this.safe(data.name)}" required />
          <label>Age</label><input type="text" id="cg-age" value="${this.safe(data.age)}" required />
          <label>Gender</label>
          <select id="cg-gender">
            <option value="">&mdash; Select &mdash;</option>
            <option value="Male" ${data.gender === "Male" ? "selected" : ""}>Male</option>
            <option value="Female" ${data.gender === "Female" ? "selected" : ""}>Female</option>
            <option value="Nonbinary" ${data.gender === "Nonbinary" ? "selected" : ""}>Nonbinary</option>
          </select>
          <label>Motto</label><input type="text" id="cg-motto" value="${this.safe(data.motto)}" />
          <label>Goal 1</label><input type="text" id="cg-goal1" value="${this.safe(data.goal1)}" />
          <label>Goal 2</label><input type="text" id="cg-goal2" value="${this.safe(data.goal2)}" />
          <label>Goal 3</label><input type="text" id="cg-goal3" value="${this.safe(data.goal3)}" />
        </div>

        <!-- TRAITS BOX -->
        <div class="cg-traits-box">
          <h3>Traits</h3>
          <div class="cg-traits">
            ${TRAITS.map((trait) => {
              const val = data[trait] || "";
              const opts = DICE.map((d) => `<option value="${d}"${val === d ? " selected" : ""}>${d}</option>`).join("");
              let label = cap(trait);
              if (trait === "trait_species")
                label = "Species";
              if (trait === "trait_career")
                label = "Career";
              return `
                <div class="cg-trait">
                  <label>${label} <small>(choose one)</small></label>
                  <select id="cg-${trait}" class="cg-trait-select">
                    <option value="">&mdash; Select &mdash;</option>
                    ${opts}
                  </select>
                  <div class="trait-adjusted" id="cg-${trait}-adjusted"></div>
                </div>`;
            }).join("")}
          </div>
        </div>

        <!-- DESCRIPTION BOX -->
        <div class="cg-text-box">
          <h3>Description & Backstory</h3>
          <label>Description</label>
          <textarea id="cg-description">${this.safe(data.description)}</textarea>
          <label>Backstory</label>
          <textarea id="cg-backstory">${this.safe(data.backstory)}</textarea>
        </div>
      </div>
      <div class="cg-nav-buttons"><button type="button" class="cg-nav-next">Next \u2192</button></div>
    </div>

    <!-- PROFILE TAB -->
    <div id="tab-profile" class="tab-panel">
      <div class="cg-nav-buttons">
        <button type="button" class="cg-nav-prev">\u2190 Previous</button>
        <button type="button" class="cg-nav-next">Next \u2192</button>
      </div>

      <!-- BOX: Species and Careers -->
      <div class="cg-profile-box">
        <h3>Species and Careers</h3>
        <label for="cg-species">Species</label>
        <select id="cg-species" class="cg-profile-select"></select>
        <ul id="species-gifts" class="cg-gift-item"></ul>

        <label for="cg-career">Career</label>
        <select id="cg-career" class="cg-profile-select"></select>
        <div class="trait-adjusted" id="cg-trait_career-adjusted"></div>

        <div id="cg-extra-careers" class="cg-profile-grid"></div>
      </div>

      <!-- BOX: Gifts -->
      <div class="cg-profile-box">
        <h3>Gifts</h3>

        <div class="cg-gift-label">Local Knowledge</div>
        <div id="cg-local-knowledge" class="cg-gift-item"></div>

        <div class="cg-gift-label">Language</div>
        <div id="cg-language" class="cg-gift-item"></div>

        <div class="cg-gift-label">Species Gifts</div>
        <ul id="species-gift-block" class="cg-gift-item"></ul>

        <div class="cg-gift-label">Career</div>
        <ul id="career-gifts" class="cg-gift-item"></ul>

        <div class="cg-gift-label">Chosen</div>
        <div id="cg-free-choices" class="cg-gift-item"></div>
      </div>

      <div class="cg-nav-buttons">
        <button type="button" class="cg-nav-prev">\u2190 Previous</button>
        <button type="button" class="cg-nav-next">Next \u2192</button>
      </div>
    </div>

    <!-- SKILLS TAB -->
    <div id="tab-skills" class="tab-panel">
      <div class="cg-nav-buttons">
        <button type="button" class="cg-nav-prev">\u2190 Previous</button>
        <button type="button" class="cg-nav-next">Next \u2192</button>
      </div>
      <table id="skills-table" class="cg-skills-table">
        <thead><tr></tr></thead>
        <tbody></tbody>
      </table>
    </div>

    <!-- SUMMARY TAB -->
    <div id="tab-summary" class="tab-panel">
      <div class="cg-nav-buttons">
        <button type="button" class="cg-nav-prev">\u2190 Previous</button>
      </div>
      <div id="cg-summary-sheet">Summary will display here.</div>
      <button id="cg-export-pdf" type="button">Export to PDF</button>
    </div>

  </div>

  <input type="hidden" id="cg-id" value="${this.safe(data.id)}" />
  <div class="cg-form-buttons">
    <button type="button" class="cg-save-button">\u{1F4BE} Save</button>
    <button type="button" class="cg-save-button cg-close-after-save">\u{1F4BE} Save & Close</button>
  </div>
</form>`;
          }
        };
      })(jQuery);
    }
  });

  // assets/js/src/core/traits/traits.js
  var require_traits = __commonJS({
    "assets/js/src/core/traits/traits.js"() {
      (function($2) {
        window.CG_Traits = {
          TRAITS: ["will", "speed", "body", "mind", "trait_species", "trait_career"],
          DICE_TYPES: ["d8", "d6", "d4"],
          MAX_COUNT: { d8: 2, d6: 3, d4: 1 },
          BOOSTS: {
            78: "will",
            89: "speed",
            85: "body",
            100: "mind",
            224: "trait_species",
            223: "trait_career"
          },
          enforceCounts: function() {
            const freq = { d8: 0, d6: 0, d4: 0 };
            $2(".cg-traits select").each(function() {
              const val = $2(this).val();
              if (val && freq.hasOwnProperty(val))
                freq[val]++;
            });
            $2(".cg-traits select").each(function() {
              const $sel = $2(this);
              const current = $sel.val();
              let html = '<option value="">\u2014 Select \u2014</option>';
              CG_Traits.DICE_TYPES.forEach((die) => {
                if (freq[die] < CG_Traits.MAX_COUNT[die] || current === die) {
                  html += `<option value="${die}"${current === die ? " selected" : ""}>${die}</option>`;
                }
              });
              $sel.html(html);
            });
            CG_Traits.updateAdjustedTraitDisplays();
          },
          updateAdjustedTraitDisplays: function() {
            var _a, _b, _c;
            const boosts = {};
            if ((_a = CG_Gifts == null ? void 0 : CG_Gifts.selected) == null ? void 0 : _a.length) {
              CG_Gifts.selected.forEach((id) => {
                const trait = CG_Traits.BOOSTS[id];
                if (trait)
                  boosts[trait] = (boosts[trait] || 0) + 1;
              });
            }
            const s = (_b = window == null ? void 0 : window.CG_Species) == null ? void 0 : _b.currentProfile;
            if (s) {
              ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((key) => {
                const id = s[key];
                const trait = CG_Traits.BOOSTS[id];
                if (trait)
                  boosts[trait] = (boosts[trait] || 0) + 1;
              });
            }
            const c = (_c = window == null ? void 0 : window.CG_Career) == null ? void 0 : _c.currentProfile;
            if (c) {
              ["gift_id_1", "gift_id_2", "gift_id_3"].forEach((key) => {
                const id = c[key];
                const trait = CG_Traits.BOOSTS[id];
                if (trait)
                  boosts[trait] = (boosts[trait] || 0) + 1;
              });
            }
            const order = ["d4", "d6", "d8", "d10", "d12"];
            CG_Traits.TRAITS.forEach((trait) => {
              const $sel = $2(`#cg-${trait}`);
              const base = $sel.val() || "d4";
              const index = order.indexOf(base);
              const upgrade = boosts[trait] || 0;
              const boostedIndex = Math.min(index + upgrade, order.length - 1);
              const boosted = order[boostedIndex];
              const label = upgrade > 0 ? `Increased by gift \xD7${upgrade}: ${boosted}` : "";
              $2(`#cg-${trait}-adjusted`).text(label);
            });
          }
        };
        $2(function() {
          CG_Traits.enforceCounts();
          $2(document).on("change", ".cg-traits select", function() {
            CG_Traits.enforceCounts();
          });
        });
      })(jQuery);
    }
  });

  // assets/js/src/core/species/species.js
  var require_species = __commonJS({
    "assets/js/src/core/species/species.js"() {
      (function($2) {
        window.CG_Species = {
          currentProfile: null,
          loadSpeciesList(callback) {
            $2.post(CG_Ajax.ajax_url, {
              action: "cg_get_species_list",
              security: CG_Ajax.nonce
            }, (res) => {
              const $sel = $2("#cg-species");
              $sel.html('<option value="">\u2014 Select Species \u2014</option>');
              if (res.success) {
                res.data.forEach((row) => {
                  $sel.append(`<option value="${row.id}">${row.ct_species_name}</option>`);
                });
              }
              if (typeof callback === "function")
                callback();
            });
          },
          bindSpeciesHandler() {
            $2("#cg-app").on("change", "#cg-species", function() {
              const id = $2(this).val();
              if (!id) {
                $2("#species-gifts").empty();
                $2("#species-gift-block").empty();
                return;
              }
              $2.post(CG_Ajax.ajax_url, {
                action: "cg_get_species_profile",
                id,
                security: CG_Ajax.nonce
              }, (res) => {
                if (!res.success)
                  return;
                const s = res.data;
                CG_Species.currentProfile = s;
                const traits = [];
                if (s.habitat)
                  traits.push(`<li><strong>Habitat:</strong> ${s.habitat}</li>`);
                if (s.diet)
                  traits.push(`<li><strong>Diet:</strong> ${s.diet}</li>`);
                if (s.cycle)
                  traits.push(`<li><strong>Life Cycle:</strong> ${s.cycle}</li>`);
                ["sense_1", "sense_2", "sense_3"].forEach((key, i) => {
                  if (s[key])
                    traits.push(`<li><strong>Sense ${i + 1}:</strong> ${s[key]}</li>`);
                });
                ["weapon_1", "weapon_2", "weapon_3"].forEach((key, i) => {
                  if (s[key])
                    traits.push(`<li><strong>Weapon ${i + 1}:</strong> ${s[key]}</li>`);
                });
                $2("#species-gifts").html(`<ul>${traits.join("")}</ul>`);
                const giftDropdowns = [
                  [s.gift_1, s.gift_id_1],
                  [s.gift_2, s.gift_id_2],
                  [s.gift_3, s.gift_id_3]
                ].filter(([name]) => !!name).map(([name], i) => CG_GiftUtils.renderDropdown(`Species Gift ${i + 1}`, name)).join("");
                $2("#species-gift-block").html(giftDropdowns);
                if (window.CG_Traits)
                  CG_Traits.updateAdjustedTraitDisplays();
                setTimeout(() => {
                  if (window.CG_Skills) {
                    const skillIds = [s.skill_one, s.skill_two, s.skill_three].map(Number).filter((n) => !isNaN(n));
                    CG_Skills.populateSkillDice("species", null, skillIds);
                  }
                }, 100);
              });
            });
          }
        };
      })(jQuery);
    }
  });

  // assets/js/src/core/career/career.js
  var require_career = __commonJS({
    "assets/js/src/core/career/career.js"() {
      (function($2) {
        window.CG_Career = {
          CAREERS: {},
          currentProfile: null,
          loadCareerList(callback) {
            const $sel = $2("#cg-career");
            if (!$sel.length) {
              console.warn("[CG_Career] Missing #cg-career select");
              if (typeof callback === "function")
                callback();
              return;
            }
            $sel.html('<option value="">\u2014 Select Career \u2014</option>');
            $2.post(CG_Ajax.ajax_url, {
              action: "cg_get_career_list",
              security: CG_Ajax.nonce
            }).done((res) => {
              if (res.success) {
                res.data.forEach((row) => {
                  this.CAREERS[row.id] = row.ct_career_name;
                  $sel.append(`<option value="${row.id}">${row.ct_career_name}</option>`);
                });
              }
              if (typeof callback === "function")
                callback();
            });
          },
          bindCareerHandler() {
            $2(document).on("change", "#cg-career", () => {
              const careerId = $2("#cg-career").val();
              const $gifts = $2("#career-gifts");
              if (!$gifts.length) {
                console.warn("[CG_Career] Missing #career-gifts container");
              } else {
                $gifts.empty();
              }
              if (!careerId) {
                CG_Skills.populateSkillDice("career", "", []);
                if (window.CG_Gifts)
                  CG_Gifts.renderExtraCareerUI();
                return;
              }
              $2.post(CG_Ajax.ajax_url, {
                action: "cg_get_career_gifts",
                id: careerId,
                security: CG_Ajax.nonce
              }).done((res) => {
                if (!res.success) {
                  console.warn("[CG_Career] cg_get_career_gifts failed");
                  return;
                }
                const c = res.data;
                CG_Career.currentProfile = c;
                const dice = $2("#cg-trait_career").val() || "";
                const skillIds = [c.skill_one, c.skill_two, c.skill_three].map(Number).filter((n) => !isNaN(n));
                CG_Skills.populateSkillDice("career", dice, skillIds);
                const giftDropdowns = [
                  [c.gift_1, c.gift_id_1],
                  [c.gift_2, c.gift_id_2],
                  [c.gift_3, c.gift_id_3]
                ].filter(([name]) => !!name).map(([name], i) => CG_GiftUtils.renderDropdown(`Career Gift ${i + 1}`, name)).join("");
                $gifts.html(giftDropdowns);
                if (window.CG_Traits)
                  CG_Traits.updateAdjustedTraitDisplays();
                if (window.CG_Gifts)
                  CG_Gifts.renderExtraCareerUI();
              }).fail(() => {
                console.error("[CG_Career] AJAX cg_get_career_gifts error");
              });
            });
          }
        };
        $2(function() {
          CG_Career.bindCareerHandler();
        });
      })(jQuery);
    }
  });

  // assets/js/src/core/skills/skills.js
  var require_skills = __commonJS({
    "assets/js/src/core/skills/skills.js"() {
      (function($2) {
        window.CG_Skills = {
          remainingMarks: 13,
          skillMarks: {},
          speciesSkillIds: [],
          careerSkillIds: [],
          extraCareerSkills: {},
          init: function() {
            this.bindMarkHandlers();
            $2("#cg-app").on("click", '[data-tab="tab-skills"]', () => {
              this.loadSkillsList();
            });
            $2("#cg-app").on("change", ".cg-trait-career-select", () => {
              this.refreshAll();
            });
          },
          loadSkillsList: function(callback) {
            const self = this;
            const $tbl = $2("#skills-table");
            if (!$tbl.length) {
              if (callback)
                callback();
              return;
            }
            $2.post(CG_Ajax.ajax_url, {
              action: "cg_get_skills_list",
              security: CG_Ajax.nonce
            }).done((res) => {
              if (!res.success) {
                if (callback)
                  callback();
                return;
              }
              const $thead = $tbl.find("thead tr").empty();
              const $tbody = $tbl.find("tbody").empty();
              let sp = $2("#cg-species option:selected").text().trim();
              if (/select/i.test(sp))
                sp = "";
              let cr = $2("#cg-career option:selected").text().trim();
              if (/select/i.test(cr))
                cr = "";
              $thead.append("<th>Skill</th>");
              $thead.append(`<th>Species${sp ? ": " + sp : ""}</th>`);
              $thead.append(`<th>Career${cr ? ": " + cr : ""}</th>`);
              const extraCount = $2(".cg-extra-career-block").length;
              for (let i = 1; i <= extraCount; i++) {
                let nm = $2(`#cg-extra-career-${i} option:selected`).text().trim();
                if (/select/i.test(nm))
                  nm = "";
                const lbl = nm ? `Career: ${nm}` : `Career ${i + 1}`;
                $thead.append(`<th>${lbl}</th>`);
              }
              $thead.append("<th>Marks</th><th>Dice Pool</th>");
              res.data.forEach((skill) => {
                const id = skill.id;
                const cnt = self.skillMarks[id] || 0;
                const dice = self.diceFromMarks(cnt);
                const btns = [1, 2, 3].map(
                  (n) => `<button data-mark="${n}" class="${n <= cnt ? "active" : ""}">\u2022</button>`
                ).join("");
                let cols = `
            <td>${skill.ct_skill_name}</td>
            <td class="skill-species"></td>
            <td class="skill-career"></td>
          `;
                for (let i = 1; i <= extraCount; i++) {
                  cols += `<td class="skill-extra skill-extra-${i}"></td>`;
                }
                cols += `
            <td class="skill-marks">
              <div class="mark-buttons" data-skill-id="${id}">
                ${btns}
              </div>
              <div class="mark-dice">${dice || "\u2014"}</div>
            </td>
            <td class="skill-total"></td>
          `;
                $tbody.append(`<tr data-skill-id="${id}">${cols}</tr>`);
              });
              if (!$2("#remaining-marks").length) {
                $tbl.after('<p id="remaining-marks">Marks Left: 13</p>');
              }
              setTimeout(() => {
                self.scanExtraCareers(() => {
                  self.updateMarkButtons();
                  self.updateRemainingMarks();
                  self.refreshAll();
                });
              }, 50);
              if (callback)
                callback();
            });
          },
          diceFromMarks(n) {
            return n === 1 ? "d4" : n === 2 ? "d6" : n === 3 ? "d8" : "";
          },
          totalMarksExcept(ex) {
            return Object.entries(this.skillMarks).filter(([id]) => +id !== +ex).reduce((sum, [, v]) => sum + v, 0);
          },
          updateRemainingMarks() {
            this.remainingMarks = 13 - Object.values(this.skillMarks).reduce((a, b) => a + b, 0);
            $2("#remaining-marks").text(`Marks Left: ${this.remainingMarks}`);
          },
          calculateDicePool($row, markDice) {
            const parts = [];
            $row.find("td.skill-species, td.skill-career, td.skill-extra").each(function() {
              const t = $2(this).text().trim();
              if (t)
                parts.push(t);
            });
            if (markDice)
              parts.push(markDice);
            return parts.join(" + ");
          },
          /**
           * Restored so species.js / career.js can feed in skill IDs.
           */
          populateSkillDice(source, _die, skillIds = []) {
            if (source === "species")
              this.speciesSkillIds = skillIds;
            if (source === "career")
              this.careerSkillIds = skillIds;
            this.refreshAll();
          },
          scanExtraCareers(callback) {
            this.extraCareerSkills = {};
            const self = this;
            let pending = 0;
            $2(".cg-extra-career-block").each((i) => {
              const idx = i + 1;
              const cid = $2(`#cg-extra-career-${idx}`).val();
              if (!cid)
                return;
              pending++;
              $2.post(CG_Ajax.ajax_url, {
                action: "cg_get_career_gifts",
                id: cid,
                security: CG_Ajax.nonce
              }).done((res) => {
                if (res.success) {
                  const ids = [res.data.skill_one, res.data.skill_two, res.data.skill_three].map(Number).filter((n) => !isNaN(n));
                  self.extraCareerSkills[idx] = ids;
                }
              }).always(() => {
                if (--pending === 0 && typeof callback === "function")
                  callback();
              });
            });
            if (pending === 0 && typeof callback === "function")
              callback();
          },
          refreshAll() {
            $2("#skills-table tbody tr").each((_, row) => {
              const $row = $2(row);
              const sid = +$row.data("skill-id");
              const marks = this.skillMarks[sid] || 0;
              const markD = this.diceFromMarks(marks);
              const sp = this.speciesSkillIds.includes(sid) ? CG_Traits.getBoostedDie("trait_species") || $2("#cg-trait_species").val() || "" : "";
              $row.find(".skill-species").text(sp);
              const cr = this.careerSkillIds.includes(sid) ? CG_Traits.getBoostedDie("trait_career") || $2("#cg-trait_career").val() || "" : "";
              $row.find(".skill-career").text(cr);
              Object.entries(this.extraCareerSkills).forEach(([i, ids]) => {
                let out = "";
                if (ids.includes(sid)) {
                  const key = `trait_career_${i}`;
                  const boost = CG_Traits.getBoostedDie(key);
                  const base = $2(`#cg-${key}`).val() || "";
                  out = boost || base || "";
                }
                $row.find(`.skill-extra-${i}`).text(out);
              });
              $row.find(".mark-dice").text(markD || "\u2014");
              $row.find(".skill-total").text(this.calculateDicePool($row, markD));
            });
          },
          bindMarkHandlers() {
            $2("#cg-app").on("click", ".mark-buttons button", (e) => {
              const $b = $2(e.currentTarget);
              const sid = +$b.closest(".mark-buttons").data("skill-id");
              const sel = +$b.data("mark");
              const cur = this.skillMarks[sid] || 0;
              const nxt = cur === sel ? 0 : sel;
              if (this.totalMarksExcept(sid) + nxt > 13)
                return;
              this.skillMarks[sid] = nxt;
              $b.siblings().addBack().each((_, btn) => {
                const m = +$2(btn).data("mark");
                $2(btn).toggleClass("active", m <= nxt);
              });
              const d = this.diceFromMarks(nxt);
              const $row = $b.closest("tr");
              $row.find(".mark-dice").text(d || "\u2014");
              $row.find(".skill-total").text(this.calculateDicePool($row, d));
              this.updateRemainingMarks();
            });
          },
          updateMarkButtons() {
            $2("#skills-table tbody tr").each((_, row) => {
              const $r = $2(row);
              const sid = +$r.data("skill-id");
              const m = this.skillMarks[sid] || 0;
              $r.find(".mark-buttons button").each((_2, btn) => {
                const v = +$2(btn).data("mark");
                $2(btn).toggleClass("active", v <= m);
              });
            });
          },
          collectMarkData() {
            return this.skillMarks;
          },
          loadMarkData(saved) {
            this.skillMarks = {};
            Object.entries(saved || {}).forEach(([id, v]) => {
              this.skillMarks[+id] = +v;
            });
            this.updateMarkButtons();
            this.updateRemainingMarks();
          }
        };
        $2(function() {
          CG_Skills.init();
        });
      })(jQuery);
    }
  });

  // assets/js/src/core/summary/summary.js
  var require_summary = __commonJS({
    "assets/js/src/core/summary/summary.js"() {
      (function($2) {
        window.CG_Summary = {
          init: function() {
            $2("#cg-app").on("click", '[data-tab="tab-summary"]', () => {
              this.renderSummary();
            });
            $2("#cg-app").on("click", "#cg-export-pdf", () => {
              alert("PDF export coming soon.");
            });
          },
          renderSummary: function() {
            const $sheet = $2("#cg-summary-sheet").empty();
            const cap = CG_FormBuilder.capitalize;
            const traits = CG_Traits.TRAITS;
            const name = $2("#cg-name").val() || "\u2014";
            const age = $2("#cg-age").val() || "\u2014";
            const gender = $2("#cg-gender").val() || "\u2014";
            const motto = $2("#cg-motto").val() || "\u2014";
            const goal1 = $2("#cg-goal1").val() || "\u2014";
            const goal2 = $2("#cg-goal2").val() || "\u2014";
            const goal3 = $2("#cg-goal3").val() || "\u2014";
            const description = $2("#cg-description").val() || "\u2014";
            const backstory = $2("#cg-backstory").val() || "\u2014";
            const traitBlock = traits.map((t) => {
              const base = $2(`#cg-${t}`).val() || "";
              const boosted = CG_Traits.getBoostedDie(t);
              let label = cap(t);
              if (t === "trait_species")
                label = "Species";
              if (t === "trait_career")
                label = "Career";
              const text = boosted ? `${base} \u2794 ${boosted}` : base || "\u2014";
              return `<li><strong>${label}:</strong> ${text}</li>`;
            }).join("");
            const species = $2("#cg-species option:selected").text().trim() || "\u2014";
            const speciesGifts = $2("#species-gifts li").length ? $2("#species-gifts").html() : "<li>\u2014</li>";
            const career = $2("#cg-career option:selected").text().trim() || "\u2014";
            const careerGifts = $2("#career-gifts li").length ? $2("#career-gifts").html() : "<li>\u2014</li>";
            let extraHTML = "";
            $2(".cg-extra-career-block").each((i) => {
              const idx = i + 1;
              const txt = $2(`#cg-extra-career-${idx} option:selected`).text().trim() || "\u2014";
              const key = `trait_career_${idx}`;
              const boosted = CG_Traits.getBoostedDie(key);
              const raw = $2(`#cg-${key}`).val() || "";
              const val = boosted || raw || "\u2014";
              extraHTML += `<li><strong>Extra Career ${idx}:</strong> ${txt} (Trait: ${val})</li>`;
            });
            const localTitle = $2("#cg-local-knowledge label strong").text() || "Local Knowledge";
            const localVal = $2("#cg-local-knowledge-area").val() || "\u2014";
            const langTitle = $2("#cg-language label strong").text() || "Language";
            const langVal = $2("#cg-language-area").val() || "\u2014";
            const freeChoices = $2("#cg-free-choices select").map((i, el) => {
              const txt = $2(el).find("option:selected").text().trim() || "\u2014";
              return `<li><strong>Free Choice ${i + 1}:</strong> ${txt}</li>`;
            }).get().join("");
            const bonusGiftHtml = `
        <h4>Bonus Gift</h4>
        <ul class="cg-summary-list"><li>Combat Save</li></ul>
      `;
            const speedDie = CG_Traits.getBoostedDie("speed") || $2("#cg-speed").val() || "";
            const mindDie = CG_Traits.getBoostedDie("mind") || $2("#cg-mind").val() || "";
            const bodyDie = CG_Traits.getBoostedDie("body") || $2("#cg-body").val() || "";
            let dodgeDie = "";
            $2("#skills-table tbody tr").each(function() {
              if ($2(this).find("td:first").text().trim().toLowerCase() === "dodge") {
                dodgeDie = $2(this).find(".skill-total").text().trim();
              }
            });
            const initiativePool = speedDie && mindDie ? `${speedDie} + ${mindDie}` : "\u2014";
            const dodgePool = speedDie ? `${speedDie}${dodgeDie ? " + " + dodgeDie : ""}` : "\u2014";
            const soakPool = bodyDie || "\u2014";
            const dieMax = { d4: 4, d6: 6, d8: 8, d10: 10, d12: 12 };
            function halfDie(d) {
              const n = parseInt(d.slice(1), 10);
              return `d${Math.ceil(n / 2)}`;
            }
            const stridePool = "1";
            const dashDice = speedDie ? [halfDie(speedDie)] : [];
            if (bodyDie && dieMax[bodyDie] > dieMax[speedDie]) {
              dashDice.push(bodyDie);
            }
            const dashPool = dashDice.length ? dashDice.join(" + ") : "\u2014";
            const sprintPool = speedDie || "\u2014";
            const runDice = bodyDie || speedDie || dashDice.length ? [bodyDie, speedDie, ...dashDice].filter(Boolean) : [];
            const runPool = runDice.length ? runDice.join(" + ") : "\u2014";
            let skillsHTML = "";
            $2("#skills-table tbody tr").each(function() {
              const name2 = $2(this).find("td:first").text().trim();
              const pool = $2(this).find(".skill-total").text().trim() || "\u2014";
              skillsHTML += `<tr><td>${name2}</td><td>${pool}</td></tr>`;
            });
            const html = `
        <h3>Character Summary</h3>
        <p><strong>Name:</strong> ${name}</p>
        <p><strong>Age:</strong> ${age}</p>
        <p><strong>Gender:</strong> ${gender}</p>

        <h4>Motto</h4>
        <p><em>\u201C${motto}\u201D</em></p>

        <h4>Goals</h4>
        <ul class="cg-summary-list">
          <li>${goal1}</li>
          <li>${goal2}</li>
          <li>${goal3}</li>
        </ul>

        <h4>Traits</h4>
        <ul class="cg-summary-list">${traitBlock}</ul>
        <hr>

        <h4>Species: ${species}</h4>
        <ul class="cg-summary-list">${speciesGifts}</ul>

        <h4>Career: ${career}</h4>
        <ul class="cg-summary-list">${careerGifts}</ul>

        ${extraHTML ? `
          <h4>Additional Careers</h4>
          <ul class="cg-summary-list">${extraHTML}</ul>
        ` : ""}

        <h4>${localTitle}</h4>
        <p><em>Area:</em> ${localVal}</p>

        <h4>${langTitle}</h4>
        <p><em>Choice:</em> ${langVal}</p>

        <h4>Free Choice Gifts</h4>
        <ul class="cg-summary-list">${freeChoices}</ul>

        ${bonusGiftHtml}

        <h3>Battle Array</h3>
        <ul class="cg-summary-list">
          <li><strong>Initiative:</strong> ${initiativePool}</li>
          <li><strong>Dodge:</strong> ${dodgePool}</li>
          <li><strong>Soak:</strong> ${soakPool}</li>
        </ul>

        <h3>Movement</h3>
        <ul class="cg-summary-list">
          <li><strong>Stride:</strong> ${stridePool}</li>
          <li><strong>Dash:</strong> ${dashPool}</li>
          <li><strong>Sprint:</strong> ${sprintPool}</li>
          <li><strong>Run:</strong> ${runPool}</li>
        </ul>
        <hr>

        <h4>Skills & Dice Pools</h4>
        <table class="cg-summary-skills" style="width:100%; border-collapse: collapse;">
          <thead>
            <tr>
              <th style="text-align:left; border-bottom:1px solid #aaa;">Skill</th>
              <th style="text-align:left; border-bottom:1px solid #aaa;">Dice Pool</th>
            </tr>
          </thead>
          <tbody>
            ${skillsHTML}
          </tbody>
        </table>

        <hr>

                <h4>Description</h4>
        <p>${description}</p>

        <h4>Backstory</h4>
        <p>${backstory}</p>
      `;
            $sheet.html(html);
          },
          bindExportButton: function() {
            $2("#cg-export-pdf").on("click", function() {
              alert("PDF export not implemented yet.");
            });
          }
        };
        $2(function() {
          CG_Summary.init();
        });
      })(jQuery);
    }
  });

  // assets/js/src/core/main/main.js
  var require_main = __commonJS({
    "assets/js/src/core/main/main.js"() {
      (function($2) {
        let isDirty = false;
        CG_Species._loaded = false;
        CG_Career._loaded = false;
        function showModal() {
          $2(".cg-modal-overlay").fadeIn(200);
          const $tabs = $2(".cg-modal .cg-tabs li");
          const $panels = $2(".cg-modal .tab-panel");
          $tabs.removeClass("active").first().addClass("active");
          $panels.removeClass("active").first().addClass("active");
          refreshTab($tabs.first().data("tab"));
        }
        function closeModal() {
          $2(".cg-modal-overlay").fadeOut(200);
        }
        function goToTabByIndex(idx) {
          const $tabs = $2(".cg-modal .cg-tabs li");
          const $panels = $2(".cg-modal .tab-panel");
          const $t = $tabs.eq(idx);
          if (!$t.length)
            return;
          $tabs.removeClass("active");
          $t.addClass("active");
          $panels.removeClass("active");
          $2("#" + $t.data("tab")).addClass("active");
          refreshTab($t.data("tab"));
        }
        function refreshTab(tabId) {
          switch (tabId) {
            case "tab-traits":
              CG_Traits.enforceCounts();
              break;
            case "tab-profile":
              if (!CG_Species._loaded) {
                CG_Species.loadSpeciesList();
                CG_Species._loaded = true;
              }
              if (!CG_Career._loaded) {
                CG_Career.loadCareerList();
                CG_Career._loaded = true;
              }
              CG_Gifts.loadFreeChoices();
              CG_Gifts.loadLocalKnowledge();
              CG_Gifts.loadLanguage();
              CG_Gifts.renderExtraCareerUI();
              break;
            case "tab-skills":
              CG_Skills.refreshAll();
              break;
            case "tab-summary":
              CG_Summary.renderSummary();
              break;
          }
        }
        function showUnsavedModal() {
          $2("#cg-unsaved-confirm").removeClass("cg-hidden");
        }
        function hideUnsavedModal() {
          $2("#cg-unsaved-confirm").addClass("cg-hidden");
        }
        $2(function() {
          $2("body").on("input change", "#cg-form input, #cg-form select, #cg-form textarea", () => {
            isDirty = true;
          });
          $2("body").on("click", "#cg-open-builder", (e) => {
            e.preventDefault();
            isDirty = false;
            showModal();
          });
          $2("body").on("click", ".cg-modal-close, .cg-modal-overlay", function(e) {
            if (e.target !== this && !$2(this).hasClass("cg-modal-close"))
              return;
            e.preventDefault();
            if (isDirty) {
              showUnsavedModal();
              return;
            }
            closeModal();
            $2("#cg-form-container").empty();
          });
          $2("body").on("click", "#unsaved-save", () => {
            hideUnsavedModal();
            $2("#cg-form .cg-close-after-save").click();
          });
          $2("body").on("click", "#unsaved-exit", () => {
            hideUnsavedModal();
            isDirty = false;
            closeModal();
            $2("#cg-form-container").empty();
          });
          $2("body").on("click", "#unsaved-cancel", () => {
            hideUnsavedModal();
          });
          $2("body").on("click", "#cg-new", () => {
            CG_Gifts.selected = [];
            CG_Gifts.freeLoaded = false;
            CG_Skills.skillMarks = {};
            CG_Species._loaded = false;
            CG_Career._loaded = false;
            isDirty = false;
            $2("#cg-form-container").html(
              `<form id="cg-form">${CG_FormBuilder.buildForm()}</form>`
            );
            setTimeout(() => {
              CG_Traits.enforceCounts();
              CG_Traits.updateAdjustedTraitDisplays();
              CG_Species.loadSpeciesList();
              CG_Career.loadCareerList();
              CG_Skills.loadSkillsList();
              goToTabByIndex(0);
            }, 50);
          });
          $2("body").on("click", "#cg-load", () => {
            isDirty = false;
            $2.post(CG_Ajax.ajax_url, {
              action: "cg_load_characters",
              security: CG_Ajax.nonce
            }).done((res) => {
              if (!res.success)
                return;
              let opts = '<option value="">\u2014 Select Character \u2014</option>';
              res.data.forEach((c) => opts += `<option value="${c.id}">${c.name}</option>`);
              $2("#cg-form-container").html(`
          <label for="cg-select">Pick a character</label>
          <select id="cg-select">${opts}</select>
        `);
            });
          });
          $2("body").on("change", "#cg-select", function() {
            const id = $2(this).val();
            if (!id)
              return;
            $2.post(CG_Ajax.ajax_url, {
              action: "cg_get_character",
              id,
              security: CG_Ajax.nonce
            }).done((res) => {
              if (!res.success)
                return;
              $2("#cg-form-container").html(
                `<form id="cg-form">${CG_FormBuilder.buildForm(res.data)}</form>`
              );
              isDirty = false;
              setTimeout(() => {
                CG_Traits.enforceCounts();
                CG_Traits.updateAdjustedTraitDisplays();
                CG_Species.loadSpeciesList(() => {
                  $2("#cg-species").val(res.data.species).trigger("change");
                });
                CG_Career.loadCareerList(() => {
                  $2("#cg-career").val(res.data.career).trigger("change");
                });
                CG_Skills.loadSkillsList(() => {
                  CG_Skills.loadMarkData(res.data.skill_marks || {});
                  CG_Skills.refreshAll();
                });
                if (res.data.extra_career_1) {
                  $2("#cg-extra-career-1").val(res.data.extra_career_1).trigger("change");
                }
                if (res.data.extra_career_2) {
                  $2("#cg-extra-career-2").val(res.data.extra_career_2).trigger("change");
                }
                goToTabByIndex(0);
              }, 100);
            });
          });
          $2("body").on("click", ".cg-nav-next, .cg-nav-prev", function() {
            const $tabs = $2(".cg-modal .cg-tabs li");
            const idx = $tabs.index($tabs.filter(".active")) + ($2(this).hasClass("cg-nav-next") ? 1 : -1);
            goToTabByIndex(idx);
          });
          CG_Species.bindSpeciesHandler();
          CG_Career.bindCareerHandler();
          CG_Gifts.init();
          CG_Summary.bindExportButton();
        });
      })(jQuery);
    }
  });

  // assets/js/src/core/index.js
  var import_jquery = __toESM(__require("jquery"));
  var import_form_builder = __toESM(require_form_builder());
  var import_traits = __toESM(require_traits());
  var import_species = __toESM(require_species());
  var import_career = __toESM(require_career());
  var import_skills = __toESM(require_skills());
  var import_summary = __toESM(require_summary());
  var import_main = __toESM(require_main());
  (function($2) {
    $2(function() {
      import_form_builder.default.init();
      import_traits.default.init();
      import_species.default.init();
      import_career.default.init();
      import_skills.default.init();
      import_summary.default.init();
      import_main.default.init();
    });
  })(jQuery);
})();
//# sourceMappingURL=core.bundle.js.map
