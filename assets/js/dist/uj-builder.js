(() => {
  // assets/js/src/uj-builder/index.js
  (function() {
    "use strict";
    var cfg = window.UJBuilder || {};
    var LOGGED_IN = !!cfg.loggedIn;
    function esc(str) {
      if (str === null || str === void 0)
        return "";
      return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }
    var COLLECTION_KEY = { species: "species", type: "types", career: "careers" };
    var SOURCE_DIE_KEY = { species: "speciesDie", type: "typeDie", career: "careerDie" };
    var CORE_SKILLS = [
      "Academics",
      "Athletics",
      "Craft",
      "Deceit",
      "Endurance",
      "Evasion",
      "Fighting",
      "Negotiation",
      "Observation",
      "Presence",
      "Questioning",
      "Shooting",
      "Tactics",
      "Transport"
    ];
    var ALLOWED_DICE_JS = ["d4", "d6", "d8", "d10", "d12"];
    function ajaxPost(action, data) {
      return new Promise(function(resolve, reject) {
        var fd = new FormData();
        fd.append("action", action);
        for (var k in data) {
          if (data[k] !== null && typeof data[k] === "object") {
            fd.append(k, JSON.stringify(data[k]));
          } else {
            fd.append(k, data[k] === null ? "" : data[k]);
          }
        }
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "/ajax.php");
        xhr.timeout = 2e4;
        xhr.onload = function() {
          try {
            resolve(JSON.parse(xhr.responseText));
          } catch (e) {
            reject(new Error("Bad response from server for action: " + action));
          }
        };
        xhr.onerror = function() {
          reject(new Error("Network error for action: " + action));
        };
        xhr.ontimeout = function() {
          reject(new Error("Timeout for action: " + action));
        };
        xhr.send(fd);
      });
    }
    document.querySelectorAll(".uj-auth-tab").forEach(function(tab) {
      tab.addEventListener("click", function() {
        document.querySelectorAll(".uj-auth-tab").forEach(function(t) {
          t.classList.remove("active");
        });
        document.querySelectorAll(".uj-auth-form").forEach(function(f) {
          f.classList.remove("active");
        });
        tab.classList.add("active");
        var target = document.getElementById("uj-" + tab.dataset.tab + "-form");
        if (target)
          target.classList.add("active");
      });
    });
    var loginForm = document.getElementById("uj-login-form");
    if (loginForm) {
      loginForm.addEventListener("submit", function(e) {
        e.preventDefault();
        var err = document.getElementById("uj-login-error");
        err.textContent = "";
        var fd = new FormData(loginForm);
        ajaxPost("cg_login_user", {
          username: fd.get("username"),
          password: fd.get("password")
        }).then(function(res) {
          if (res.success) {
            window.location.reload();
          } else {
            err.textContent = res.data || "Login failed.";
          }
        }).catch(function() {
          err.textContent = "Network error.";
        });
      });
    }
    var registerForm = document.getElementById("uj-register-form");
    if (registerForm) {
      registerForm.addEventListener("submit", function(e) {
        e.preventDefault();
        var err = document.getElementById("uj-register-error");
        err.textContent = "";
        var fd = new FormData(registerForm);
        ajaxPost("cg_register_user", {
          username: fd.get("username"),
          email: fd.get("email"),
          password: fd.get("password")
        }).then(function(res) {
          if (res.success) {
            window.location.reload();
          } else {
            err.textContent = res.data || "Registration failed.";
          }
        }).catch(function() {
          err.textContent = "Network error.";
        });
      });
    }
    if (!LOGGED_IN)
      return;
    var logoutBtn = document.getElementById("uj-logout-btn");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", function() {
        ajaxPost("cg_logout_user", {}).then(function() {
          window.location.reload();
        });
      });
    }
    var state = {
      allData: null,
      personalities: [],
      characters: [],
      currentChar: null,
      speciesId: null,
      typeId: null,
      careerId: null,
      bodyDie: "d6",
      speedDie: "d6",
      mindDie: "d6",
      willDie: "d4",
      speciesDie: "d6",
      typeDie: "d6",
      careerDie: "d6",
      personalityWord: "",
      charName: "",
      notes: "",
      currentStep: 0
    };
    var DICE_POOL = ["d4", "d6", "d6", "d6", "d6", "d8", "d8"];
    var STEP_LABELS = ["Species", "Type", "Career", "Traits", "Personality", "Summary"];
    var loadingEl = document.getElementById("uj-builder-loading");
    var listScreen = document.getElementById("uj-char-list-screen");
    var wizardScreen = document.getElementById("uj-wizard-screen");
    Promise.all([
      ajaxPost("uj_get_all_full", {}),
      ajaxPost("cg_get_personality_list", {}),
      ajaxPost("uj_load_characters", {})
    ]).then(function(results) {
      if (results[0].success)
        state.allData = results[0].data;
      if (results[1].success)
        state.personalities = results[1].data || [];
      if (results[2].success)
        state.characters = results[2].data || [];
      if (loadingEl)
        loadingEl.style.display = "none";
      showCharList();
    }).catch(function(err) {
      if (loadingEl) {
        loadingEl.textContent = "Error loading data \u2014 " + (err && err.message ? err.message : "please refresh.");
      }
      console.error("[UJ Builder]", err);
    });
    function showCharList() {
      if (wizardScreen)
        wizardScreen.style.display = "none";
      if (listScreen)
        listScreen.style.display = "block";
      renderCharList();
    }
    function renderCharList() {
      if (!listScreen)
        return;
      var html = '<div class="char-list-header"><h2>My Characters</h2><button class="uj-btn uj-btn-amber" id="uj-new-char-btn">+ New Character</button></div>';
      if (state.characters.length === 0) {
        html += '<div class="char-empty">No characters yet. Create your first one!</div>';
      } else {
        html += '<div class="char-cards">';
        state.characters.forEach(function(c) {
          var d = state.allData || {};
          var sp = (d.species || []).find(function(x) {
            return x.id == c.species_id;
          });
          var ty = (d.types || []).find(function(x) {
            return x.id == c.type_id;
          });
          var ca = (d.careers || []).find(function(x) {
            return x.id == c.career_id;
          });
          var detail = [sp ? sp.name : null, ty ? ty.name : null, ca ? ca.name : null].filter(Boolean).join(" / ");
          var dice = [c.body_die, c.speed_die, c.mind_die, c.will_die].filter(Boolean).join(" ");
          var date = c.updated_at ? c.updated_at.substring(0, 10) : "";
          html += '<div class="char-card" data-id="' + esc(c.id) + '"><div class="char-card-name">' + esc(c.name || "(Unnamed)") + '</div><div class="char-card-detail">' + esc(detail || "Incomplete") + "</div>" + (dice ? `<div class="char-card-detail" style="color:var(--uj-amber);font-family:'Cinzel',serif;font-size:0.8rem;margin-top:0.25rem;">` + esc(dice) + "</div>" : "") + '<div class="char-card-footer"><span class="char-card-date">' + esc(date) + '</span><div style="display:flex;gap:0.4rem;"><button class="uj-btn uj-btn-ghost" style="font-size:0.7rem;padding:0.3rem 0.7rem;" data-load="' + esc(c.id) + '">Edit</button><button class="uj-btn uj-btn-danger" data-delete="' + esc(c.id) + '">Delete</button></div></div></div>';
        });
        html += "</div>";
      }
      listScreen.innerHTML = html;
      var newBtn = document.getElementById("uj-new-char-btn");
      if (newBtn)
        newBtn.addEventListener("click", startNewChar);
      listScreen.querySelectorAll("[data-load]").forEach(function(btn) {
        btn.addEventListener("click", function(e) {
          e.stopPropagation();
          loadChar(btn.dataset.load);
        });
      });
      listScreen.querySelectorAll("[data-delete]").forEach(function(btn) {
        btn.addEventListener("click", function(e) {
          e.stopPropagation();
          if (!confirm("Delete this character?"))
            return;
          deleteChar(btn.dataset.delete);
        });
      });
      listScreen.querySelectorAll(".char-card").forEach(function(card) {
        card.addEventListener("click", function() {
          loadChar(card.dataset.id);
        });
      });
    }
    function startNewChar() {
      state.currentChar = null;
      state.speciesId = null;
      state.typeId = null;
      state.careerId = null;
      state.bodyDie = "d8";
      state.speedDie = "d8";
      state.mindDie = "d6";
      state.willDie = "d4";
      state.speciesDie = "d6";
      state.typeDie = "d6";
      state.careerDie = "d6";
      state.personalityWord = "";
      state.charName = "";
      state.notes = "";
      state.currentStep = 0;
      showWizard();
    }
    function loadChar(id) {
      ajaxPost("uj_get_character", { id }).then(function(res) {
        if (!res.success) {
          alert(res.data || "Error loading character.");
          return;
        }
        var c = res.data;
        state.currentChar = String(c.id);
        state.speciesId = c.species_id ? Number(c.species_id) : null;
        state.typeId = c.type_id ? Number(c.type_id) : null;
        state.careerId = c.career_id ? Number(c.career_id) : null;
        state.bodyDie = c.body_die || "d8";
        state.speedDie = c.speed_die || "d8";
        state.mindDie = c.mind_die || "d6";
        state.willDie = c.will_die || "d4";
        state.speciesDie = c.species_die || "d8";
        state.typeDie = c.type_die || "d8";
        state.careerDie = c.career_die || "d8";
        state.personalityWord = c.personality_word || "";
        state.charName = c.name || "";
        state.notes = c.notes || "";
        state.currentStep = 0;
        showWizard();
      });
    }
    function deleteChar(id) {
      ajaxPost("uj_delete_character", { id }).then(function() {
        state.characters = state.characters.filter(function(c) {
          return c.id != id;
        });
        renderCharList();
      });
    }
    function showWizard() {
      if (listScreen)
        listScreen.style.display = "none";
      if (wizardScreen)
        wizardScreen.style.display = "block";
      renderWizard();
    }
    function renderWizard() {
      if (!wizardScreen)
        return;
      wizardScreen.innerHTML = buildWizardShell();
      renderStep(state.currentStep);
      bindWizardNav();
    }
    function buildWizardShell() {
      var prog = '<div class="wizard-progress">';
      STEP_LABELS.forEach(function(label, i) {
        if (i > 0) {
          prog += '<div class="wp-line' + (i <= state.currentStep ? " done" : "") + '"></div>';
        }
        var cls = i < state.currentStep ? "done" : i === state.currentStep ? "active" : "";
        prog += '<div class="wp-step ' + cls + '" title="' + label + '">' + (i + 1) + "</div>";
      });
      prog += "</div>";
      var isLast = state.currentStep === STEP_LABELS.length - 1;
      return '<div class="wizard-header"><h2 class="wizard-title">' + esc(state.charName || "New Character") + "</h2>" + prog + '<button class="uj-btn uj-btn-ghost" id="wiz-back-list-btn">\u2190 Characters</button></div><div id="wiz-step-container"></div><div class="wizard-nav"><button class="uj-btn uj-btn-ghost" id="wiz-prev-btn"' + (state.currentStep === 0 ? " disabled" : "") + '>\u2190 Back</button><div class="wizard-nav-right"><span class="save-status" id="wiz-save-status"></span><button class="uj-btn uj-btn-amber" id="wiz-save-btn">Save</button>' + (isLast ? '<button class="uj-btn uj-btn-teal" id="wiz-print-btn">Print</button>' : '<button class="uj-btn uj-btn-teal" id="wiz-next-btn">Next \u2192</button>') + "</div></div>";
    }
    function renderStep(step) {
      var container = document.getElementById("wiz-step-container");
      if (!container)
        return;
      switch (step) {
        case 0:
          container.innerHTML = buildSelectionStep("species", "Step 1 \u2014 Choose Your Species", "speciesId");
          bindSelectionCards("species", "speciesId");
          break;
        case 1:
          container.innerHTML = buildSelectionStep("type", "Step 2 \u2014 Choose Your Type", "typeId");
          bindSelectionCards("type", "typeId");
          break;
        case 2:
          container.innerHTML = buildSelectionStep("career", "Step 3 \u2014 Choose Your Career", "careerId");
          bindSelectionCards("career", "careerId");
          break;
        case 3:
          container.innerHTML = buildDiceStep();
          bindDiceStep();
          break;
        case 4:
          container.innerHTML = buildPersonalityStep();
          bindPersonalityStep();
          break;
        case 5:
          container.innerHTML = buildSummaryStep();
          break;
      }
    }
    function bindWizardNav() {
      var backBtn = document.getElementById("wiz-back-list-btn");
      var prevBtn = document.getElementById("wiz-prev-btn");
      var nextBtn = document.getElementById("wiz-next-btn");
      var saveBtn = document.getElementById("wiz-save-btn");
      var printBtn = document.getElementById("wiz-print-btn");
      if (backBtn)
        backBtn.addEventListener("click", function() {
          ajaxPost("uj_load_characters", {}).then(function(res) {
            if (res.success)
              state.characters = res.data || [];
            showCharList();
          });
        });
      if (prevBtn)
        prevBtn.addEventListener("click", function() {
          if (state.currentStep > 0) {
            state.currentStep--;
            renderWizard();
          }
        });
      if (nextBtn)
        nextBtn.addEventListener("click", function() {
          if (validateStep(state.currentStep)) {
            state.currentStep++;
            renderWizard();
          }
        });
      if (saveBtn)
        saveBtn.addEventListener("click", saveCharacter);
      if (printBtn)
        printBtn.addEventListener("click", function() {
          window.print();
        });
    }
    function validateStep(step) {
      if (step === 0 && !state.speciesId) {
        alert("Please select a Species.");
        return false;
      }
      if (step === 1 && !state.typeId) {
        alert("Please select a Type.");
        return false;
      }
      if (step === 2 && !state.careerId) {
        alert("Please select a Career.");
        return false;
      }
      if (step === 3 && !validateDice()) {
        alert("Dice must be exactly d8 + d8 + d6 + d4 \u2014 one d4 for the worst trait, two d8s for the best.");
        return false;
      }
      return true;
    }
    function validateDice() {
      var pool = [
        state.bodyDie,
        state.speedDie,
        state.mindDie,
        state.willDie,
        state.speciesDie,
        state.typeDie,
        state.careerDie
      ].sort().join(",");
      return pool === DICE_POOL.slice().sort().join(",");
    }
    function buildSourceDiePicker(entityType) {
      var dieKey = SOURCE_DIE_KEY[entityType];
      var current = state[dieKey] || "d8";
      var label = entityType.charAt(0).toUpperCase() + entityType.slice(1) + " Die";
      var html = '<div class="source-die-row" id="source-die-row-' + entityType + '"><span class="source-die-label">' + label + '</span><div class="source-die-options">';
      ALLOWED_DICE_JS.forEach(function(d) {
        html += '<label class="die-pill' + (current === d ? " die-pill-active" : "") + '"><input type="radio" name="source-die-' + entityType + '" value="' + d + '"' + (current === d ? " checked" : "") + ' style="display:none;">' + d + "</label>";
      });
      html += "</div></div>";
      return html;
    }
    function buildSelectionStep(entityType, heading, stateKey) {
      var collKey = COLLECTION_KEY[entityType];
      var items = state.allData && state.allData[collKey] || [];
      var html = '<div class="step-heading">' + heading + "</div>";
      html += buildDetailPanel(entityType, state[stateKey]);
      html += '<div class="select-grid">';
      items.forEach(function(item) {
        var sel = state[stateKey] == item.id ? " selected" : "";
        var tags = (item.skills || []).slice(0, 2).map(function(s) {
          return '<span class="tag tag-skill">' + esc(s.name) + "</span>";
        }).join("");
        html += '<div class="select-card' + sel + '" data-id="' + item.id + '"><div class="select-card-name">' + esc(item.name) + '</div><div class="select-card-tags">' + tags + "</div></div>";
      });
      html += "</div>";
      return html;
    }
    function buildDetailPanel(entityType, selectedId) {
      var item = null;
      if (selectedId && state.allData) {
        var collKey = COLLECTION_KEY[entityType];
        var list = state.allData[collKey] || [];
        item = list.find(function(x) {
          return x.id == selectedId;
        }) || null;
      }
      if (!item) {
        return '<div class="detail-panel"><span class="detail-panel-empty">Select a ' + entityType + " to see its details.</span></div>";
      }
      var html = '<div class="detail-panel" id="uj-detail-panel"><div class="detail-panel-name">' + esc(item.name) + '</div><div class="detail-panel-desc">' + esc(item.description || "") + '</div><div class="detail-grants">';
      if (item.skills && item.skills.length) {
        html += '<div class="detail-grant-group"><div class="detail-grant-label">Skills</div><ul class="detail-grant-list">';
        item.skills.forEach(function(s) {
          html += "<li>" + esc(s.name) + "</li>";
        });
        html += "</ul></div>";
      }
      if (item.gifts && item.gifts.length) {
        html += '<div class="detail-grant-group"><div class="detail-grant-label">Gifts</div><ul class="detail-grant-list">';
        item.gifts.forEach(function(g) {
          html += '<li class="gift-item">' + esc(g.name) + "</li>";
        });
        html += "</ul></div>";
      }
      if (item.soaks && item.soaks.length) {
        html += '<div class="detail-grant-group"><div class="detail-grant-label">Soaks</div><ul class="detail-grant-list">';
        item.soaks.forEach(function(s) {
          html += '<li class="soak-item">' + esc(s.name) + "</li>";
        });
        html += "</ul></div>";
      }
      if (item.gear) {
        html += '<div class="detail-grant-group"><div class="detail-grant-label">Starting Gear</div><ul class="detail-grant-list">';
        item.gear.split(/\n/).forEach(function(line) {
          line = line.trim();
          if (line)
            html += '<li class="gear-item">' + esc(line) + "</li>";
        });
        html += "</ul></div>";
      }
      html += "</div></div>";
      return html;
    }
    function bindDiePills(entityType) {
      var container = document.getElementById("wiz-step-container");
      if (!container)
        return;
      var dieKey = SOURCE_DIE_KEY[entityType];
      container.querySelectorAll('input[name="source-die-' + entityType + '"]').forEach(function(radio) {
        radio.addEventListener("change", function() {
          state[dieKey] = radio.value;
          container.querySelectorAll(".die-pill").forEach(function(lbl) {
            var inp = lbl.querySelector("input");
            if (inp && inp.name === "source-die-" + entityType) {
              lbl.classList.toggle("die-pill-active", inp.value === radio.value);
            }
          });
        });
      });
    }
    function bindSelectionCards(entityType, stateKey) {
      var container = document.getElementById("wiz-step-container");
      if (!container)
        return;
      bindDiePills(entityType);
      container.querySelectorAll(".select-card").forEach(function(card) {
        card.addEventListener("click", function() {
          state[stateKey] = Number(card.dataset.id);
          container.querySelectorAll(".select-card").forEach(function(c) {
            c.classList.remove("selected");
          });
          card.classList.add("selected");
          var oldPanel = document.getElementById("uj-detail-panel");
          if (!oldPanel) {
            oldPanel = container.querySelector(".detail-panel");
          }
          if (oldPanel) {
            var tmp = document.createElement("div");
            tmp.innerHTML = buildDetailPanel(entityType, state[stateKey]);
            oldPanel.parentNode.replaceChild(tmp.firstChild, oldPanel);
          }
        });
      });
    }
    function buildDiceStep() {
      var d = state.allData || {};
      var sp = (d.species || []).find(function(x) {
        return x.id == state.speciesId;
      });
      var ty = (d.types || []).find(function(x) {
        return x.id == state.typeId;
      });
      var ca = (d.careers || []).find(function(x) {
        return x.id == state.careerId;
      });
      var slots = [
        { key: "bodyDie", label: "Body", sub: "Trait" },
        { key: "speedDie", label: "Speed", sub: "Trait" },
        { key: "mindDie", label: "Mind", sub: "Trait" },
        { key: "willDie", label: "Will", sub: "Trait" },
        { key: "speciesDie", label: "Species", sub: sp ? sp.name : "Species" },
        { key: "typeDie", label: "Type", sub: ty ? ty.name : "Type" },
        { key: "careerDie", label: "Career", sub: ca ? ca.name : "Career" }
      ];
      var html = '<div class="step-heading">Step 4 \u2014 Assign Dice</div><p style="color:var(--uj-text-muted);font-size:0.9rem;margin:0 0 1rem;">Distribute the pool <strong style="color:var(--uj-amber);">d8, d8, d6, d6, d6, d6, d4</strong> across all seven slots \u2014 your four traits and your three source dice.</p><div class="dice-grid dice-grid-7">';
      slots.forEach(function(t) {
        html += '<div class="dice-trait"><div class="dice-trait-name">' + t.label + '</div><div class="dice-trait-sub">' + esc(t.sub) + '</div><select class="dice-select" data-trait="' + t.key + '">' + ALLOWED_DICE_JS.map(function(d2) {
          return '<option value="' + d2 + '"' + (state[t.key] === d2 ? " selected" : "") + ">" + d2.toUpperCase() + "</option>";
        }).join("") + "</select></div>";
      });
      html += "</div>";
      html += '<p class="dice-hint">Assigned: <span id="dice-pool-display"></span></p>';
      html += '<p class="dice-error" id="dice-pool-error">Pool must be exactly d8 + d8 + d6 + d6 + d6 + d6 + d4 (7 dice total).</p>';
      return html;
    }
    function bindDiceStep() {
      updateDiceDisplay();
      document.querySelectorAll(".dice-select").forEach(function(sel) {
        sel.addEventListener("change", function() {
          state[sel.dataset.trait] = sel.value;
          updateDiceDisplay();
        });
      });
    }
    function updateDiceDisplay() {
      var pool = [
        state.bodyDie,
        state.speedDie,
        state.mindDie,
        state.willDie,
        state.speciesDie,
        state.typeDie,
        state.careerDie
      ];
      var disp = document.getElementById("dice-pool-display");
      var errEl = document.getElementById("dice-pool-error");
      if (disp)
        disp.textContent = pool.join(", ");
      if (errEl)
        errEl.style.display = validateDice() ? "none" : "block";
    }
    function buildPersonalityStep() {
      var opts = state.personalities.map(function(p) {
        return '<option value="' + esc(p) + '"' + (state.personalityWord === p ? " selected" : "") + ">" + esc(p) + "</option>";
      }).join("");
      return '<div class="step-heading">Step 5 \u2014 Personality &amp; Name</div><div class="personality-row"><div class="personality-field"><label class="field-label">Character Name</label><input type="text" class="field-input" id="char-name-input" value="' + esc(state.charName) + '" placeholder="What are they called?"></div><div class="personality-field"><label class="field-label">Personality Trait</label><select class="field-select" id="personality-select"><option value="">\u2014 Choose a trait \u2014</option>' + opts + '</select></div></div><div style="margin-bottom:1rem;"><label class="field-label">Notes (optional)</label><textarea class="field-textarea" id="char-notes-input" placeholder="Backstory, goals, appearance\u2026">' + esc(state.notes) + '</textarea></div><p style="font-size:0.88rem;color:var(--uj-text-dim);">The <strong style="color:var(--uj-text);">Personality</strong> gift is free for all characters. It grants a bonus d12 when you act according to your personality trait.</p>';
    }
    function bindPersonalityStep() {
      var nameEl = document.getElementById("char-name-input");
      var selEl = document.getElementById("personality-select");
      var notesEl = document.getElementById("char-notes-input");
      if (nameEl)
        nameEl.addEventListener("input", function() {
          state.charName = nameEl.value;
        });
      if (selEl)
        selEl.addEventListener("change", function() {
          state.personalityWord = selEl.value;
        });
      if (notesEl)
        notesEl.addEventListener("input", function() {
          state.notes = notesEl.value;
        });
    }
    function buildSummaryStep() {
      var d = state.allData || {};
      var sp = (d.species || []).find(function(x) {
        return x.id == state.speciesId;
      }) || null;
      var ty = (d.types || []).find(function(x) {
        return x.id == state.typeId;
      }) || null;
      var ca = (d.careers || []).find(function(x) {
        return x.id == state.careerId;
      }) || null;
      function itemGrantsSkill(item, skillName) {
        if (!item || !item.skills)
          return false;
        return item.skills.some(function(s) {
          return s.name.toLowerCase() === skillName.toLowerCase();
        });
      }
      function skillDiceFor(skillName) {
        var dice = [];
        if (itemGrantsSkill(sp, skillName) && state.speciesDie)
          dice.push({ die: state.speciesDie, src: "Species" });
        if (itemGrantsSkill(ty, skillName) && state.typeDie)
          dice.push({ die: state.typeDie, src: "Type" });
        if (itemGrantsSkill(ca, skillName) && state.careerDie)
          dice.push({ die: state.careerDie, src: "Career" });
        return dice;
      }
      var giftMap = {};
      function addGifts(arr, src) {
        (arr || []).forEach(function(g) {
          if (!giftMap[g.id])
            giftMap[g.id] = { name: g.name, sources: [] };
          giftMap[g.id].sources.push(src);
        });
      }
      if (sp)
        addGifts(sp.gifts, sp.name);
      if (ty)
        addGifts(ty.gifts, ty.name);
      if (ca)
        addGifts(ca.gifts, ca.name);
      giftMap["_personality"] = {
        name: "Personality [" + (state.personalityWord || "of choice") + "]",
        sources: ["All characters"]
      };
      var soakMap = {};
      if (ty && ty.soaks) {
        ty.soaks.forEach(function(s) {
          if (!soakMap[s.id])
            soakMap[s.id] = { name: s.name, detail: s.damage_negated || "" };
        });
      }
      var gearSeen = {};
      var gearLines = [];
      function addGear(obj, src) {
        if (!obj || !obj.gear)
          return;
        obj.gear.split(/\n/).forEach(function(line) {
          line = line.trim();
          if (!line)
            return;
          var key = line.toLowerCase();
          if (!gearSeen[key]) {
            gearSeen[key] = true;
            gearLines.push({ from: src, text: line });
          }
        });
      }
      addGear(ty, ty ? ty.name : "");
      addGear(ca, ca ? ca.name : "");
      var html = "";
      html += '<div class="step-heading">Step 6 \u2014 Summary</div>';
      html += '<div class="summary-char-name">' + esc(state.charName || "(Unnamed)") + "</div>";
      var subtitle = [sp ? sp.name : "", ty ? ty.name : "", ca ? ca.name : ""].filter(Boolean).join(" \xB7 ");
      if (subtitle)
        html += '<div class="summary-subtitle">' + esc(subtitle) + "</div>";
      if (state.personalityWord) {
        html += '<div class="summary-personality"><span class="summary-personality-label">Personality</span><span class="summary-personality-word">' + esc(state.personalityWord) + "</span></div>";
      }
      var traitRows = [
        { label: "Body", die: state.bodyDie },
        { label: "Speed", die: state.speedDie },
        { label: "Mind", die: state.mindDie },
        { label: "Will", die: state.willDie }
      ];
      html += '<div class="summary-traits">';
      traitRows.forEach(function(t) {
        html += '<div class="summary-trait"><div class="summary-trait-name">' + t.label + '</div><div class="summary-trait-die">' + (t.die || "\u2014") + "</div></div>";
      });
      html += "</div>";
      html += '<div class="summary-source-dice"><div class="summary-source-die-item"><span class="source-die-label">Species Die</span><span class="summary-trait-die" style="font-size:1.1rem;">' + (sp ? state.speciesDie || "\u2014" : "\u2014") + "</span>" + (sp ? '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(sp.name) + "</span>" : "") + '</div><div class="summary-source-die-item"><span class="source-die-label">Type Die</span><span class="summary-trait-die" style="font-size:1.1rem;">' + (ty ? state.typeDie || "\u2014" : "\u2014") + "</span>" + (ty ? '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(ty.name) + "</span>" : "") + '</div><div class="summary-source-die-item"><span class="source-die-label">Career Die</span><span class="summary-trait-die" style="font-size:1.1rem;">' + (ca ? state.careerDie || "\u2014" : "\u2014") + "</span>" + (ca ? '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(ca.name) + "</span>" : "") + "</div></div>";
      html += '<div class="summary-section summary-skills-section"><div class="summary-section-title">Skills</div><table class="skills-table"><thead><tr><th class="skill-name-col">Skill</th><th class="skill-die-col" title="Species die">Species</th><th class="skill-die-col" title="Type die">Type</th><th class="skill-die-col" title="Career die">Career</th><th class="skill-total-col">Dice Pool</th></tr></thead><tbody>';
      CORE_SKILLS.forEach(function(skillName) {
        var spDie = itemGrantsSkill(sp, skillName) ? state.speciesDie : "";
        var tyDie = itemGrantsSkill(ty, skillName) ? state.typeDie : "";
        var caDie = itemGrantsSkill(ca, skillName) ? state.careerDie : "";
        var pool = [spDie, tyDie, caDie].filter(Boolean);
        var hasAny = pool.length > 0;
        html += '<tr class="' + (hasAny ? "skill-row-active" : "skill-row-empty") + '"><td class="skill-name-col">' + esc(skillName) + '</td><td class="skill-die-col">' + (spDie ? '<span class="skill-die-badge">' + spDie + "</span>" : '<span class="skill-die-empty">\u2014</span>') + '</td><td class="skill-die-col">' + (tyDie ? '<span class="skill-die-badge">' + tyDie + "</span>" : '<span class="skill-die-empty">\u2014</span>') + '</td><td class="skill-die-col">' + (caDie ? '<span class="skill-die-badge">' + caDie + "</span>" : '<span class="skill-die-empty">\u2014</span>') + '</td><td class="skill-total-col">' + (pool.length ? '<span style="color:var(--uj-teal);font-weight:600;">' + pool.join(" + ") + "</span>" : '<span class="skill-die-empty">\u2014</span>') + "</td></tr>";
      });
      html += "</tbody></table></div>";
      var initDice = [state.mindDie].concat(skillDiceFor("Observation").map(function(x) {
        return x.die;
      })).filter(Boolean);
      var dodgeDice = [state.speedDie].concat(skillDiceFor("Evasion").map(function(x) {
        return x.die;
      })).filter(Boolean);
      var rallyDice = [state.willDie].concat(skillDiceFor("Tactics").map(function(x) {
        return x.die;
      })).filter(Boolean);
      html += '<div class="summary-battle-array"><div class="summary-section-title">Battle Array</div><div class="battle-array-grid"><div class="battle-stat"><div class="battle-stat-name">Initiative</div><div class="battle-stat-sub">Mind + Observation</div><div class="battle-stat-dice">' + initDice.join(" + ") + '</div></div><div class="battle-stat"><div class="battle-stat-name">Dodge</div><div class="battle-stat-sub">Speed + Evasion</div><div class="battle-stat-dice">' + dodgeDice.join(" + ") + '</div></div><div class="battle-stat"><div class="battle-stat-name">Rally</div><div class="battle-stat-sub">Will + Tactics</div><div class="battle-stat-dice">' + rallyDice.join(" + ") + "</div></div></div></div>";
      html += '<div class="summary-grid">';
      html += '<div class="summary-section"><div class="summary-section-title">Gifts</div><ul class="summary-list">';
      Object.values(giftMap).forEach(function(g) {
        html += '<li class="gift-item">' + esc(g.name) + "<small>" + esc(g.sources.join(", ")) + "</small></li>";
      });
      html += "</ul></div>";
      html += '<div class="summary-section"><div class="summary-section-title">Soaks</div><ul class="summary-list">';
      var soaks = Object.values(soakMap);
      if (soaks.length) {
        soaks.forEach(function(s) {
          html += '<li class="soak-item">' + esc(s.name) + (s.detail ? "<small>" + esc(s.detail) + "</small>" : "") + "</li>";
        });
      } else {
        html += '<li style="color:var(--uj-text-dim);border-left-color:var(--uj-text-dim);">None granted</li>';
      }
      html += "</ul></div>";
      html += '<div class="summary-section"><div class="summary-section-title">Starting Gear</div><ul class="summary-list">';
      if (gearLines.length) {
        gearLines.forEach(function(g) {
          html += '<li style="border-left-color:#fbbf24;">' + esc(g.text) + "<small>" + esc(g.from) + "</small></li>";
        });
      } else {
        html += '<li style="color:var(--uj-text-dim);border-left-color:var(--uj-text-dim);">No starting gear</li>';
      }
      html += "</ul></div>";
      html += "</div>";
      if (state.notes) {
        html += '<div class="summary-section" style="margin-top:1.25rem;"><div class="summary-section-title">Notes</div><p style="font-size:0.92rem;color:var(--uj-text-muted);white-space:pre-wrap;margin:0;">' + esc(state.notes) + "</p></div>";
      }
      return html;
    }
    function saveCharacter() {
      var statusEl = document.getElementById("wiz-save-status");
      if (statusEl) {
        statusEl.textContent = "Saving\u2026";
        statusEl.className = "save-status saving";
      }
      var payload = {
        id: state.currentChar || "",
        name: state.charName,
        species_id: state.speciesId !== null ? state.speciesId : "",
        type_id: state.typeId !== null ? state.typeId : "",
        career_id: state.careerId !== null ? state.careerId : "",
        body_die: state.bodyDie,
        speed_die: state.speedDie,
        mind_die: state.mindDie,
        will_die: state.willDie,
        species_die: state.speciesDie,
        type_die: state.typeDie,
        career_die: state.careerDie,
        personality_word: state.personalityWord,
        notes: state.notes
      };
      ajaxPost("uj_save_character", { character: payload }).then(function(res) {
        if (res.success) {
          state.currentChar = String(res.data.id);
          if (statusEl) {
            statusEl.textContent = "Saved";
            statusEl.className = "save-status saved";
          }
          setTimeout(function() {
            if (statusEl)
              statusEl.textContent = "";
          }, 3e3);
          var titleEl = wizardScreen ? wizardScreen.querySelector(".wizard-title") : null;
          if (titleEl)
            titleEl.textContent = state.charName || "New Character";
        } else {
          if (statusEl) {
            statusEl.textContent = "Save failed";
            statusEl.className = "save-status error";
          }
        }
      }).catch(function() {
        if (statusEl) {
          statusEl.textContent = "Network error";
          statusEl.className = "save-status error";
        }
      });
    }
  })();
})();
//# sourceMappingURL=uj-builder.js.map
