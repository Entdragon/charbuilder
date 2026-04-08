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
    var ALLOWED_DICE_JS = ["d4", "d6", "d8"];
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
      allySpeciesId: null,
      allyCareerId: null,
      giftChoices: {},
      experience: 0,
      purchasedGifts: [],
      currentStep: 0
    };
    var DICE_POOL = ["d4", "d6", "d6", "d6", "d6", "d8", "d8"];
    var STEP_LABELS = ["Species", "Type", "Career", "Traits", "Personality", "Gifts", "Summary"];
    var UJ_TRAITS = ["Body", "Speed", "Mind", "Will", "Type", "Species", "Career"];
    var DIE_STEPS = ["d4", "d6", "d8", "d10", "d12"];
    function stepDieUp(die, steps) {
      var idx = DIE_STEPS.indexOf(die);
      if (idx < 0)
        return die;
      return DIE_STEPS[Math.min(idx + steps, DIE_STEPS.length - 1)];
    }
    function effectiveDie(baseDie, traitLabel) {
      if (!state.purchasedGifts || !state.purchasedGifts.length)
        return baseDie;
      var slug = "improved-trait-" + traitLabel.toLowerCase();
      var count = state.purchasedGifts.filter(function(p) {
        return p.slug === slug;
      }).length;
      return count ? stepDieUp(baseDie, count) : baseDie;
    }
    var loadingEl = document.getElementById("uj-builder-loading");
    var listScreen = document.getElementById("uj-char-list-screen");
    var wizardScreen = document.getElementById("uj-wizard-screen");
    var developScreen = document.getElementById("uj-develop-screen");
    var ALLOWS_MULTIPLE = { "wealth": true };
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
      if (developScreen)
        developScreen.style.display = "none";
      if (wizardScreen)
        wizardScreen.style.display = "none";
      if (listScreen)
        listScreen.style.display = "none";
      ajaxPost("uj_load_characters", {}).then(function(res) {
        state.characters = res && res.success && Array.isArray(res.data) ? res.data : [];
        renderCharList();
        if (listScreen)
          listScreen.style.display = "block";
      });
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
          var xpTotal = parseInt(c.experience || 0, 10);
          var purchased;
          try {
            purchased = JSON.parse(c.purchased_gifts || "[]") || [];
          } catch (e) {
            purchased = [];
          }
          var xpSpent = purchased.reduce(function(s, p) {
            return s + (p.xp_cost || 10);
          }, 0);
          var xpAvail = xpTotal - xpSpent;
          html += '<div class="char-card" data-id="' + esc(c.id) + '"><div class="char-card-name">' + esc(c.name || "(Unnamed)") + '</div><div class="char-card-detail">' + esc(detail || "Incomplete") + "</div>" + (dice ? `<div class="char-card-detail" style="color:var(--uj-amber);font-family:'Cinzel',serif;font-size:0.8rem;margin-top:0.25rem;">` + esc(dice) + "</div>" : "") + (xpTotal > 0 ? '<div class="char-card-detail" style="color:var(--uj-teal);font-size:0.78rem;margin-top:0.2rem;">' + xpAvail + " XP available (" + xpTotal + " earned)</div>" : "") + '<div class="char-card-footer"><span class="char-card-date">' + esc(date) + '</span><div style="display:flex;gap:0.4rem;flex-wrap:wrap;"><button class="uj-btn uj-btn-ghost" style="font-size:0.7rem;padding:0.3rem 0.7rem;" data-load="' + esc(c.id) + '">Edit</button><button class="uj-btn uj-btn-teal" style="font-size:0.7rem;padding:0.3rem 0.7rem;" data-develop="' + esc(c.id) + '">Develop</button><button class="uj-btn uj-btn-amber" style="font-size:0.7rem;padding:0.3rem 0.7rem;" data-sheet="' + esc(c.id) + '">Sheet</button><button class="uj-btn uj-btn-danger" data-delete="' + esc(c.id) + '">Delete</button></div></div></div>';
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
      listScreen.querySelectorAll("[data-develop]").forEach(function(btn) {
        btn.addEventListener("click", function(e) {
          e.stopPropagation();
          loadAndDevelop(btn.dataset.develop);
        });
      });
      listScreen.querySelectorAll("[data-sheet]").forEach(function(btn) {
        btn.addEventListener("click", function(e) {
          e.stopPropagation();
          loadAndViewSheet(btn.dataset.sheet);
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
      state.bodyDie = "";
      state.speedDie = "";
      state.mindDie = "";
      state.willDie = "";
      state.speciesDie = "";
      state.typeDie = "";
      state.careerDie = "";
      state.personalityWord = "";
      state.charName = "";
      state.notes = "";
      state.allySpeciesId = null;
      state.allyCareerId = null;
      state.giftChoices = {};
      state.experience = 0;
      state.purchasedGifts = [];
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
        state.allySpeciesId = c.ally_species_id ? Number(c.ally_species_id) : null;
        state.allyCareerId = c.ally_career_id ? Number(c.ally_career_id) : null;
        state.giftChoices = function() {
          try {
            return JSON.parse(c.gift_choices || "{}") || {};
          } catch (e) {
            return {};
          }
        }();
        state.experience = parseInt(c.experience || 0, 10);
        state.purchasedGifts = function() {
          try {
            return JSON.parse(c.purchased_gifts || "[]") || [];
          } catch (e) {
            return [];
          }
        }();
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
    function loadAndDevelop(id) {
      ajaxPost("uj_get_character", { id }).then(function(res) {
        if (!res || !res.data)
          return;
        var c = res.data;
        state.currentChar = c.id;
        state.charName = c.name || "";
        state.speciesId = c.species_id ? Number(c.species_id) : null;
        state.typeId = c.type_id ? Number(c.type_id) : null;
        state.careerId = c.career_id ? Number(c.career_id) : null;
        state.experience = parseInt(c.experience || 0, 10);
        state.purchasedGifts = function() {
          try {
            return JSON.parse(c.purchased_gifts || "[]") || [];
          } catch (e) {
            return [];
          }
        }();
        showDevelop();
      });
    }
    function loadAndViewSheet(id) {
      ajaxPost("uj_get_character", { id }).then(function(res) {
        if (!res || !res.data)
          return;
        var c = res.data;
        state.currentChar = c.id;
        state.charName = c.name || "";
        state.speciesId = c.species_id ? Number(c.species_id) : null;
        state.typeId = c.type_id ? Number(c.type_id) : null;
        state.careerId = c.career_id ? Number(c.career_id) : null;
        state.bodyDie = c.body_die || "";
        state.speedDie = c.speed_die || "";
        state.mindDie = c.mind_die || "";
        state.willDie = c.will_die || "";
        state.speciesDie = c.species_die || "";
        state.typeDie = c.type_die || "";
        state.careerDie = c.career_die || "";
        state.personalityWord = c.personality_word || "";
        state.notes = c.notes || "";
        state.allySpeciesId = c.ally_species_id ? Number(c.ally_species_id) : null;
        state.allyCareerId = c.ally_career_id ? Number(c.ally_career_id) : null;
        state.giftChoices = function() {
          try {
            return JSON.parse(c.gift_choices || "{}") || {};
          } catch (e) {
            return {};
          }
        }();
        state.experience = parseInt(c.experience || 0, 10);
        state.purchasedGifts = function() {
          try {
            return JSON.parse(c.purchased_gifts || "[]") || [];
          } catch (e) {
            return [];
          }
        }();
        state.currentStep = 6;
        showWizard();
      });
    }
    function showDevelop() {
      if (listScreen)
        listScreen.style.display = "none";
      if (wizardScreen)
        wizardScreen.style.display = "none";
      if (developScreen)
        developScreen.style.display = "block";
      renderDevelop();
    }
    function renderDevelop() {
      if (!developScreen)
        return;
      var d = state.allData || {};
      var allGifts = d.gifts || [];
      var allSoaks = d.soaks || [];
      var xpTotal = state.experience;
      var xpSpent = state.purchasedGifts.reduce(function(s, p) {
        return s + (p.xp_cost || 10);
      }, 0);
      var xpAvail = xpTotal - xpSpent;
      var sp = (d.species || []).find(function(x) {
        return x.id == state.speciesId;
      });
      var ty = (d.types || []).find(function(x) {
        return x.id == state.typeId;
      });
      var ca = (d.careers || []).find(function(x) {
        return x.id == state.careerId;
      });
      var subtitle = [sp ? sp.name : "", ty ? ty.name : "", ca ? ca.name : ""].filter(Boolean).join(" / ");
      function countPurchased(slug) {
        return state.purchasedGifts.filter(function(p) {
          return p.slug === slug;
        }).length;
      }
      function cardBadges(item, kind) {
        var owned = countPurchased(item.slug);
        var out = '<span class="dev-badge dev-badge-type">' + esc(kind) + "</span> ";
        if (owned)
          out += '<span class="dev-badge dev-badge-owned">Owned \xD7' + owned + "</span> ";
        return out;
      }
      function buildShopCard(item, kind) {
        var owned = countPurchased(item.slug);
        var canMultiple = item.requires_text && /again/i.test(item.requires_text);
        var alreadyOwned = owned > 0 && !canMultiple;
        var canAfford = xpAvail >= 10;
        var btnDisabled = alreadyOwned || !canAfford ? " disabled" : "";
        var btnLabel = alreadyOwned ? "Already owned" : !canAfford ? "Need 10 XP" : "Buy \u2014 10 XP";
        return '<div class="dev-shop-card" style="' + (alreadyOwned ? "opacity:0.45;pointer-events:none;" : "") + '"><div class="dev-shop-card-header"><div class="dev-shop-card-name">' + esc(item.name) + "</div><div>" + cardBadges(item, kind) + "</div></div>" + (item.subtitle ? '<div style="font-size:0.78rem;color:var(--uj-amber-light);font-style:italic;margin-bottom:0.15rem;">' + esc(item.subtitle) + "</div>" : "") + (item.description ? '<p class="dev-shop-card-desc">' + esc(item.description) + "</p>" : "") + (item.side_effect ? '<p class="dev-shop-card-desc" style="color:var(--uj-text-dim);"><em>Side effect:</em> ' + esc(item.side_effect) + "</p>" : "") + (item.requires_text ? '<div class="dev-shop-requires"><strong>Requires/Notes:</strong> ' + esc(item.requires_text) + "</div>" : "") + '<div class="dev-shop-card-footer"><button class="uj-btn uj-btn-teal" style="font-size:0.75rem;padding:0.3rem 0.85rem;" data-buy-slug="' + esc(item.slug) + '" data-buy-name="' + esc(item.name) + '" data-buy-kind="' + esc(kind) + '"' + btnDisabled + ">" + btnLabel + "</button></div></div>";
      }
      var IMPROVED_TRAITS = ["Body", "Speed", "Mind", "Will", "Career", "Species", "Type"];
      var expandedGifts = [];
      allGifts.forEach(function(g) {
        if (g.slug === "improved-trait") {
          IMPROVED_TRAITS.forEach(function(trait) {
            expandedGifts.push(Object.assign({}, g, {
              name: "Improved " + trait,
              slug: "improved-trait-" + trait.toLowerCase(),
              subtitle: "Improved Trait [" + trait + "]",
              requires_text: null
            }));
          });
        } else {
          expandedGifts.push(g);
        }
      });
      var giftsHtml = expandedGifts.map(function(g) {
        return buildShopCard(g, "Gift");
      }).join("");
      var soaksHtml = allSoaks.map(function(s) {
        return buildShopCard(s, "Soak");
      }).join("");
      var historyHtml = "";
      if (state.purchasedGifts.length === 0) {
        historyHtml = '<p style="color:var(--uj-text-dim);font-style:italic;">No purchases yet.</p>';
      } else {
        state.purchasedGifts.forEach(function(p) {
          historyHtml += '<div class="dev-history-item"><div class="dev-history-item-name">' + esc(p.name) + '</div><div style="font-size:0.8rem;color:var(--uj-text-dim);"><span class="dev-badge dev-badge-type" style="margin-right:0.4rem;">' + esc(p.kind) + "</span>" + esc(p.xp_cost) + " XP spent" + (p.purchased_on ? " &mdash; " + esc(p.purchased_on) : "") + "</div></div>";
        });
      }
      developScreen.innerHTML = '<div class="dev-header"><div><div class="dev-char-name">' + esc(state.charName || "(Unnamed)") + "</div>" + (subtitle ? '<div class="dev-char-subtitle">' + esc(subtitle) + "</div>" : "") + '</div><button class="uj-btn uj-btn-ghost" id="dev-back-btn" style="font-size:0.8rem;">&#8592; Back to Characters</button></div><div class="dev-xp-panel"><div class="dev-xp-stats"><div class="dev-xp-stat"><div class="dev-xp-stat-val">' + xpTotal + '</div><div class="dev-xp-stat-label">XP Earned</div></div><div class="dev-xp-stat"><div class="dev-xp-stat-val" style="color:var(--uj-text-muted);">' + xpSpent + '</div><div class="dev-xp-stat-label">XP Spent</div></div><div class="dev-xp-stat"><div class="dev-xp-stat-val" style="color:var(--uj-teal);">' + xpAvail + '</div><div class="dev-xp-stat-label">XP Available</div></div></div><div class="dev-xp-award"><input id="dev-xp-input" type="number" min="1" max="999" class="uj-input" style="width:6rem;" placeholder="XP"><button class="uj-btn uj-btn-amber" id="dev-xp-award-btn">Award XP</button></div></div>' + function() {
        var hasDevAlly = state.purchasedGifts.some(function(p) {
          return p.slug === "ally";
        });
        if (!hasDevAlly)
          return "";
        var speciesList = d.species || [];
        var careerList = d.careers || [];
        var spOpts = '<option value="">\u2014 Choose Species \u2014</option>' + speciesList.map(function(s) {
          return '<option value="' + esc(s.id) + '"' + (state.allySpeciesId == s.id ? " selected" : "") + ">" + esc(s.name) + "</option>";
        }).join("");
        var caOpts = '<option value="">\u2014 Choose Career \u2014</option>' + careerList.map(function(c) {
          return '<option value="' + esc(c.id) + '"' + (state.allyCareerId == c.id ? " selected" : "") + ">" + esc(c.name) + "</option>";
        }).join("");
        return `<div class="dev-xp-panel" style="flex-direction:column;align-items:flex-start;gap:0.75rem;"><div style="font-family:'Cinzel',serif;font-size:0.8rem;font-weight:700;color:var(--uj-amber);letter-spacing:0.06em;text-transform:uppercase;">Ally Configuration</div><div style="display:flex;gap:1rem;flex-wrap:wrap;width:100%;"><div style="flex:1;min-width:160px;"><label style="font-size:0.72rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.08em;display:block;margin-bottom:0.35rem;">Ally Species</label><select class="field-select" id="dev-ally-species" style="width:100%;">` + spOpts + '</select></div><div style="flex:1;min-width:160px;"><label style="font-size:0.72rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.08em;display:block;margin-bottom:0.35rem;">Ally Career</label><select class="field-select" id="dev-ally-career" style="width:100%;">' + caOpts + '</select></div><div style="display:flex;align-items:flex-end;"><button class="uj-btn uj-btn-amber" id="dev-ally-save-btn" style="font-size:0.78rem;">Save Ally</button></div></div></div>';
      }() + '<div class="dev-rule-box"><strong>Development rules:</strong> Spend <strong>10 XP</strong> to buy a new Gift or Soak. Gifts may only be purchased multiple times if their description specifically says so.</div><div class="dev-tabs"><button class="dev-tab active" data-tab="gifts">Gifts</button><button class="dev-tab" data-tab="soaks">Soaks</button><button class="dev-tab" data-tab="history">Purchase History (' + state.purchasedGifts.length + ')</button></div><div class="dev-panel active" data-panel="gifts"><div class="dev-shop-grid">' + giftsHtml + '</div></div><div class="dev-panel" data-panel="soaks"><div class="dev-shop-grid">' + soaksHtml + '</div></div><div class="dev-panel" data-panel="history"><div class="dev-history-list">' + historyHtml + "</div></div>";
      document.getElementById("dev-back-btn").addEventListener("click", showCharList);
      document.getElementById("dev-xp-award-btn").addEventListener("click", function() {
        var input = document.getElementById("dev-xp-input");
        var amt = parseInt(input.value, 10);
        if (!amt || amt < 1) {
          alert("Enter a positive XP amount.");
          return;
        }
        state.experience += amt;
        saveDevelop(renderDevelop);
      });
      var allySpSel = document.getElementById("dev-ally-species");
      var allyCaSel = document.getElementById("dev-ally-career");
      var allySaveBtn = document.getElementById("dev-ally-save-btn");
      if (allySpSel)
        allySpSel.addEventListener("change", function() {
          state.allySpeciesId = allySpSel.value ? Number(allySpSel.value) : null;
        });
      if (allyCaSel)
        allyCaSel.addEventListener("change", function() {
          state.allyCareerId = allyCaSel.value ? Number(allyCaSel.value) : null;
        });
      if (allySaveBtn)
        allySaveBtn.addEventListener("click", function() {
          saveDevelop(function() {
            allySaveBtn.textContent = "Saved!";
            setTimeout(function() {
              allySaveBtn.textContent = "Save Ally";
            }, 2e3);
          });
        });
      developScreen.querySelectorAll(".dev-tab").forEach(function(tab) {
        tab.addEventListener("click", function() {
          developScreen.querySelectorAll(".dev-tab").forEach(function(t) {
            t.classList.remove("active");
          });
          developScreen.querySelectorAll(".dev-panel").forEach(function(p) {
            p.classList.remove("active");
          });
          tab.classList.add("active");
          developScreen.querySelector('[data-panel="' + tab.dataset.tab + '"]').classList.add("active");
        });
      });
      developScreen.querySelectorAll("[data-buy-slug]").forEach(function(btn) {
        btn.addEventListener("click", function() {
          var slug = btn.dataset.buySlug;
          var name = btn.dataset.buyName;
          var kind = btn.dataset.buyKind;
          if (!confirm('Spend 10 XP to purchase "' + name + '"?'))
            return;
          var today = (/* @__PURE__ */ new Date()).toISOString().substring(0, 10);
          state.purchasedGifts.push({ slug, name, kind, xp_cost: 10, purchased_on: today });
          saveDevelop(renderDevelop);
        });
      });
    }
    function saveDevelop(callback) {
      ajaxPost("uj_update_development", {
        id: state.currentChar,
        experience: state.experience,
        purchased_gifts: JSON.stringify(state.purchasedGifts),
        ally_species_id: state.allySpeciesId !== null ? state.allySpeciesId : "",
        ally_career_id: state.allyCareerId !== null ? state.allyCareerId : ""
      }).then(function() {
        if (callback)
          callback();
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
          container.innerHTML = buildGiftsStep();
          bindGiftsStep();
          break;
        case 6:
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
          html += '<li class="gift-item">' + esc(g.name);
          if (g.subtitle) {
            html += '<em style="display:block;font-style:italic;color:#4ade80;font-size:0.8rem;font-weight:400;margin-top:0.1rem;">' + esc(g.subtitle) + "</em>";
          }
          html += "</li>";
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
        html += '<div class="dice-trait"><div class="dice-trait-name">' + t.label + '</div><div class="dice-trait-sub">' + esc(t.sub) + '</div><select class="dice-select" data-trait="' + t.key + '"><option value=""' + (state[t.key] === "" ? " selected" : "") + ">\u2014 Choose \u2014</option>" + ALLOWED_DICE_JS.map(function(d2) {
          return '<option value="' + d2 + '"' + (state[t.key] === d2 ? " selected" : "") + ">" + d2.toUpperCase() + "</option>";
        }).join("") + "</select></div>";
      });
      html += "</div>";
      html += '<p class="dice-hint">Assigned: <span id="dice-pool-display"></span></p>';
      html += '<p class="dice-error" id="dice-pool-error">Pool must be exactly d8 + d8 + d6 + d6 + d6 + d6 + d4 (7 dice total).</p>';
      return html;
    }
    var DICE_SLOT_KEYS = ["bodyDie", "speedDie", "mindDie", "willDie", "speciesDie", "typeDie", "careerDie"];
    function getRemainingPoolFor(excludeKey) {
      var remaining = { d4: 1, d6: 4, d8: 2 };
      DICE_SLOT_KEYS.forEach(function(k) {
        if (k !== excludeKey && state[k]) {
          remaining[state[k]] = (remaining[state[k]] || 0) - 1;
        }
      });
      return remaining;
    }
    function updateDiceSelects() {
      document.querySelectorAll(".dice-select").forEach(function(sel) {
        var key = sel.dataset.trait;
        var current = state[key] || "";
        var remaining = getRemainingPoolFor(key);
        var html = '<option value=""' + (current === "" ? " selected" : "") + ">\u2014 Choose \u2014</option>";
        ALLOWED_DICE_JS.forEach(function(d) {
          var avail = (remaining[d] || 0) > 0;
          var chosen = current === d;
          if (avail || chosen) {
            html += '<option value="' + d + '"' + (chosen ? " selected" : "") + ">" + d.toUpperCase() + "</option>";
          }
        });
        sel.innerHTML = html;
      });
      updateDiceDisplay();
    }
    function bindDiceStep() {
      updateDiceSelects();
      document.querySelectorAll(".dice-select").forEach(function(sel) {
        sel.addEventListener("change", function() {
          state[sel.dataset.trait] = sel.value;
          updateDiceSelects();
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
      var filled = pool.filter(Boolean);
      if (disp)
        disp.textContent = filled.length ? filled.join(", ") : "(none assigned yet)";
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
    function collectAllGifts() {
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
      var result = [];
      var slugCount = {};
      function addGifts(arr, src) {
        (arr || []).forEach(function(g) {
          var slug = g.slug || String(g.id);
          if (!slugCount[slug])
            slugCount[slug] = 0;
          result.push({
            id: g.id,
            name: g.name,
            slug,
            subtitle: g.subtitle || "",
            description: g.description || "",
            requires_text: g.requires_text || "",
            source: src,
            occurrence: slugCount[slug]++
          });
        });
      }
      if (sp)
        addGifts(sp.gifts, sp.name);
      if (ty)
        addGifts(ty.gifts, ty.name);
      if (ca)
        addGifts(ca.gifts, ca.name);
      return result;
    }
    function buildGiftsStep() {
      var gifts = collectAllGifts();
      var d = state.allData || {};
      var html = '<div class="step-heading">Step 6 \u2014 Your Gifts</div>';
      html += '<p style="font-size:0.88rem;color:var(--uj-text-dim);margin:0 0 1.25rem;">These gifts come with your Species, Type, and Career. Gifts marked with a choice require your input below.</p>';
      if (gifts.length === 0) {
        html += '<p style="color:var(--uj-text-dim);font-style:italic;">No gifts from your current selections.</p>';
        return html;
      }
      gifts.forEach(function(g) {
        var choiceKey = g.slug + "_" + g.occurrence;
        var isImproved = g.slug === "improved-trait";
        var isAlly = g.slug === "ally";
        var needsChoice = isImproved || isAlly;
        var borderColor = needsChoice ? "var(--uj-amber-border,#7c5c1e)" : "var(--uj-border-cool)";
        html += '<div style="background:var(--uj-surface);border:1px solid ' + borderColor + ';border-radius:var(--uj-radius-lg,8px);padding:1rem 1.25rem;margin-bottom:0.75rem;">';
        html += '<div style="display:flex;align-items:baseline;gap:0.75rem;flex-wrap:wrap;margin-bottom:0.25rem;">';
        html += `<span style="font-family:'Cinzel',Georgia,serif;font-size:1rem;font-weight:700;color:var(--uj-amber-light);">` + esc(g.name) + "</span>";
        html += '<span style="font-size:0.73rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.06em;">' + esc(g.source) + "</span>";
        html += "</div>";
        if (g.subtitle) {
          html += '<div style="font-style:italic;color:#4ade80;font-size:0.9rem;margin-bottom:0.5rem;">' + esc(g.subtitle) + "</div>";
        }
        if (isAlly) {
          var allyDesc = "You have a friend! Your friend is a Minor Typical character, with a Species and a Career, a d6 in all six Traits, and the four gifts they get from those two choices. (Your Ally does not have a Type Trait.) Your Ally also has the Soak of Distress Soak \u22124. Your friend is normally controlled by the Game Host, but the Host may let you \u201Ctake control\u201D and use the Ally as if it were your own character. Your Ally always has your best interest in mind. They would never betray you, but they might be deceived by villains. Or they might be captured and held hostage. If your Ally is killed, or otherwise leaves the game, you will have to retrain this gift.";
          html += '<p style="font-size:0.88rem;color:var(--uj-text-muted);margin:0 0 0.9rem;line-height:1.5;">' + esc(allyDesc) + "</p>";
          var speciesList = d.species || [];
          var careerList = d.careers || [];
          html += '<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:0.5rem;">';
          html += '<div style="flex:1;min-width:160px;">';
          html += `<label style="font-family:'Cinzel',Georgia,serif;font-size:0.7rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.1em;display:block;margin-bottom:0.35rem;">Ally Species</label>`;
          html += '<select class="field-select" id="uj-ally-species-select" style="width:100%;">';
          html += '<option value="">\u2014 Choose Species \u2014</option>';
          speciesList.forEach(function(sp) {
            html += '<option value="' + esc(sp.id) + '"' + (state.allySpeciesId == sp.id ? " selected" : "") + ">" + esc(sp.name) + "</option>";
          });
          html += "</select></div>";
          html += '<div style="flex:1;min-width:160px;">';
          html += `<label style="font-family:'Cinzel',Georgia,serif;font-size:0.7rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.1em;display:block;margin-bottom:0.35rem;">Ally Career</label>`;
          html += '<select class="field-select" id="uj-ally-career-select" style="width:100%;">';
          html += '<option value="">\u2014 Choose Career \u2014</option>';
          careerList.forEach(function(ca) {
            html += '<option value="' + esc(ca.id) + '"' + (state.allyCareerId == ca.id ? " selected" : "") + ">" + esc(ca.name) + "</option>";
          });
          html += "</select></div>";
          html += "</div>";
        } else if (isImproved) {
          var currentChoice = state.giftChoices[choiceKey] || "";
          if (g.description) {
            var desc = g.description.length > 300 ? g.description.slice(0, 297) + "\u2026" : g.description;
            html += '<p style="font-size:0.88rem;color:var(--uj-text-muted);margin:0 0 0.75rem;line-height:1.5;">' + esc(desc) + "</p>";
          }
          html += '<div style="margin-top:0.25rem;">';
          html += '<div style="font-size:0.76rem;color:var(--uj-text-dim);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.08em;">Choose a trait to improve:</div>';
          html += '<div class="uj-trait-picker" data-choice-key="' + esc(choiceKey) + '" style="display:flex;flex-wrap:wrap;gap:0.4rem;">';
          UJ_TRAITS.forEach(function(trait) {
            var active = currentChoice === trait;
            html += '<label class="uj-trait-pill" data-trait="' + esc(trait) + '" style="cursor:pointer;padding:0.35rem 0.9rem;border-radius:999px;font-size:0.82rem;user-select:none;border:1px solid ' + (active ? "var(--uj-amber)" : "var(--uj-border-cool)") + ";color:" + (active ? "var(--uj-amber-light)" : "var(--uj-text-muted)") + ";background:" + (active ? "rgba(180,120,30,0.18)" : "transparent") + ';"><input type="radio" name="imptrait-' + esc(choiceKey) + '" value="' + esc(trait) + '"' + (active ? " checked" : "") + ' style="display:none;">' + esc(trait) + "</label>";
          });
          html += "</div></div>";
        } else {
          if (g.description) {
            var desc2 = g.description.length > 280 ? g.description.slice(0, 277) + "\u2026" : g.description;
            html += '<p style="font-size:0.88rem;color:var(--uj-text-muted);margin:0;line-height:1.5;">' + esc(desc2) + "</p>";
          }
        }
        html += "</div>";
      });
      return html;
    }
    function bindGiftsStep() {
      var container = document.getElementById("wiz-step-container");
      if (!container)
        return;
      container.querySelectorAll(".uj-trait-picker").forEach(function(picker) {
        var choiceKey = picker.dataset.choiceKey;
        picker.querySelectorAll(".uj-trait-pill").forEach(function(pill) {
          pill.addEventListener("click", function() {
            var radio = pill.querySelector('input[type="radio"]');
            if (!radio)
              return;
            var trait = radio.value;
            state.giftChoices[choiceKey] = trait;
            picker.querySelectorAll(".uj-trait-pill").forEach(function(p) {
              var active = p.dataset.trait === trait;
              p.style.borderColor = active ? "var(--uj-amber)" : "var(--uj-border-cool)";
              p.style.color = active ? "var(--uj-amber-light)" : "var(--uj-text-muted)";
              p.style.background = active ? "rgba(180,120,30,0.18)" : "transparent";
            });
          });
        });
      });
      var allySpSel = container.querySelector("#uj-ally-species-select");
      var allyCaSel = container.querySelector("#uj-ally-career-select");
      if (allySpSel)
        allySpSel.addEventListener("change", function() {
          state.allySpeciesId = allySpSel.value ? Number(allySpSel.value) : null;
        });
      if (allyCaSel)
        allyCaSel.addEventListener("change", function() {
          state.allyCareerId = allyCaSel.value ? Number(allyCaSel.value) : null;
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
      var summarySlugCount = {};
      function addGifts(arr, src) {
        (arr || []).forEach(function(g) {
          if (!giftMap[g.id]) {
            var slug = g.slug || String(g.id);
            if (!summarySlugCount[slug])
              summarySlugCount[slug] = 0;
            var occ = summarySlugCount[slug]++;
            giftMap[g.id] = { name: g.name, slug, occurrence: occ, sources: [], note: "" };
          }
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
        slug: "_personality",
        occurrence: 0,
        sources: ["All characters"],
        note: ""
      };
      Object.values(giftMap).forEach(function(g) {
        var choiceKey = g.slug + "_" + g.occurrence;
        if (g.slug === "improved-trait") {
          var choice = state.giftChoices[choiceKey] || "";
          g.note = choice ? "Trait: " + choice : "(no trait chosen)";
        } else if (g.slug === "ally") {
          var allySp2 = state.allySpeciesId ? (d.species || []).find(function(x) {
            return x.id == state.allySpeciesId;
          }) : null;
          var allyCa2 = state.allyCareerId ? (d.careers || []).find(function(x) {
            return x.id == state.allyCareerId;
          }) : null;
          var allyParts = [];
          if (allySp2)
            allyParts.push("Species: " + allySp2.name);
          if (allyCa2)
            allyParts.push("Career: " + allyCa2.name);
          g.note = allyParts.length ? allyParts.join(" \xB7 ") : "(ally choices not set)";
        }
      });
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
      html += '<div class="step-heading">Step 7 \u2014 Summary</div>';
      html += '<div class="summary-char-name">' + esc(state.charName || "(Unnamed)") + "</div>";
      var subtitle = [sp ? sp.name : "", ty ? ty.name : "", ca ? ca.name : ""].filter(Boolean).join(" \xB7 ");
      if (subtitle)
        html += '<div class="summary-subtitle">' + esc(subtitle) + "</div>";
      if (state.personalityWord) {
        html += '<div class="summary-personality"><span class="summary-personality-label">Personality</span><span class="summary-personality-word">' + esc(state.personalityWord) + "</span></div>";
      }
      var traitRows = [
        { label: "Body", die: effectiveDie(state.bodyDie, "Body") },
        { label: "Speed", die: effectiveDie(state.speedDie, "Speed") },
        { label: "Mind", die: effectiveDie(state.mindDie, "Mind") },
        { label: "Will", die: effectiveDie(state.willDie, "Will") }
      ];
      html += '<div class="summary-traits">';
      traitRows.forEach(function(t) {
        var base = state[t.label.toLowerCase() + "Die"];
        var improved = t.die !== base;
        html += '<div class="summary-trait"><div class="summary-trait-name">' + t.label + (improved ? ' <span style="color:var(--uj-teal);font-size:0.65rem;vertical-align:middle;" title="Improved via development">&#9650;</span>' : "") + '</div><div class="summary-trait-die" style="' + (improved ? "color:var(--uj-teal);" : "") + '">' + (t.die || "\u2014") + "</div></div>";
      });
      html += "</div>";
      var effSpeciesDie = effectiveDie(state.speciesDie, "Species");
      var effTypeDie = effectiveDie(state.typeDie, "Type");
      var effCareerDie = effectiveDie(state.careerDie, "Career");
      html += '<div class="summary-source-dice"><div class="summary-source-die-item"><span class="source-die-label">Species Die</span><span class="summary-trait-die" style="font-size:1.1rem;' + (effSpeciesDie !== state.speciesDie ? "color:var(--uj-teal);" : "") + '">' + (sp ? effSpeciesDie || "\u2014" : "\u2014") + "</span>" + (sp ? '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(sp.name) + "</span>" : "") + '</div><div class="summary-source-die-item"><span class="source-die-label">Type Die</span><span class="summary-trait-die" style="font-size:1.1rem;' + (effTypeDie !== state.typeDie ? "color:var(--uj-teal);" : "") + '">' + (ty ? effTypeDie || "\u2014" : "\u2014") + "</span>" + (ty ? '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(ty.name) + "</span>" : "") + '</div><div class="summary-source-die-item"><span class="source-die-label">Career Die</span><span class="summary-trait-die" style="font-size:1.1rem;' + (effCareerDie !== state.careerDie ? "color:var(--uj-teal);" : "") + '">' + (ca ? effCareerDie || "\u2014" : "\u2014") + "</span>" + (ca ? '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(ca.name) + "</span>" : "") + "</div></div>";
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
      var allGiftsData = d.gifts || [];
      function findGiftBySlug(slug) {
        return allGiftsData.find(function(x) {
          return x.slug === slug;
        }) || null;
      }
      html += '<div class="summary-section"><div class="summary-section-title">Gifts</div><ul class="summary-list">';
      Object.values(giftMap).forEach(function(g) {
        var gData = findGiftBySlug(g.slug);
        var subtitle2 = gData ? gData.subtitle || "" : "";
        html += '<li class="gift-item">' + esc(g.name) + (subtitle2 ? '<span style="display:block;font-size:0.78rem;color:#4ade80;font-style:italic;margin-top:0.1rem;">' + esc(subtitle2) + "</span>" : "") + "<small>" + esc((g.sources || []).join(", ")) + (g.note ? " \u2014 " + g.note : "") + "</small></li>";
      });
      html += "</ul></div>";
      if (state.purchasedGifts && state.purchasedGifts.length > 0) {
        var allSoaksData = d.soaks || [];
        html += '<div class="summary-section"><div class="summary-section-title" style="color:var(--uj-teal);">Developed Gifts &amp; Soaks</div><ul class="summary-list">';
        state.purchasedGifts.forEach(function(p) {
          var pData = p.kind === "Gift" ? findGiftBySlug(p.slug) : allSoaksData.find(function(x) {
            return x.slug === p.slug;
          }) || null;
          var pSubtitle = pData ? pData.subtitle || "" : "";
          html += '<li class="gift-item" style="border-left-color:var(--uj-teal);">' + esc(p.name) + (pSubtitle ? '<span style="display:block;font-size:0.78rem;color:#4ade80;font-style:italic;margin-top:0.1rem;">' + esc(pSubtitle) + "</span>" : "") + '<small style="color:var(--uj-teal);">Developed \xB7 ' + esc(p.xp_cost || 10) + " XP</small></li>";
        });
        html += "</ul></div>";
      }
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
        notes: state.notes,
        ally_species_id: state.allySpeciesId !== null ? state.allySpeciesId : "",
        ally_career_id: state.allyCareerId !== null ? state.allyCareerId : "",
        gift_choices: JSON.stringify(state.giftChoices || {}),
        experience: state.experience || 0,
        purchased_gifts: JSON.stringify(state.purchasedGifts || [])
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
