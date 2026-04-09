/* ============================================================
   UJ Character Builder — entry point
   Reads config from window.UJBuilder (set by builder.php)
   ============================================================ */
(function() {
  'use strict';

  var cfg        = window.UJBuilder || {};
  var LOGGED_IN  = !!cfg.loggedIn;

  /* ── Utility ─────────────────────────────────────────────── */
  function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // Map singular type name → key in allData response
  var COLLECTION_KEY = { species: 'species', type: 'types', career: 'careers' };

  // Map entity type → state die key
  var SOURCE_DIE_KEY = { species: 'speciesDie', type: 'typeDie', career: 'careerDie' };

  // The 14 canonical UJ core skills (character sheet order)
  var CORE_SKILLS = [
    'Academics','Athletics','Craft','Deceit','Endurance','Evasion',
    'Fighting','Negotiation','Observation','Presence','Questioning',
    'Shooting','Tactics','Transport'
  ];

  var ALLOWED_DICE_JS = ['d4','d6','d8'];

  function ajaxPost(action, data) {
    return new Promise(function(resolve, reject) {
      var fd = new FormData();
      fd.append('action', action);
      for (var k in data) {
        if (data[k] !== null && typeof data[k] === 'object') {
          fd.append(k, JSON.stringify(data[k]));
        } else {
          fd.append(k, data[k] === null ? '' : data[k]);
        }
      }
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '/ajax.php');
      xhr.timeout = 20000;
      xhr.onload = function() {
        try { resolve(JSON.parse(xhr.responseText)); }
        catch (e) { reject(new Error('Bad response from server for action: ' + action)); }
      };
      xhr.onerror   = function() { reject(new Error('Network error for action: ' + action)); };
      xhr.ontimeout = function() { reject(new Error('Timeout for action: ' + action)); };
      xhr.send(fd);
    });
  }

  /* ── Auth tabs (not-logged-in view) ───────────────────────── */
  document.querySelectorAll('.uj-auth-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
      document.querySelectorAll('.uj-auth-tab').forEach(function(t) { t.classList.remove('active'); });
      document.querySelectorAll('.uj-auth-form').forEach(function(f) { f.classList.remove('active'); });
      tab.classList.add('active');
      var target = document.getElementById('uj-' + tab.dataset.tab + '-form');
      if (target) target.classList.add('active');
    });
  });

  /* ── Login form ─────────────────────────────────────────── */
  var loginForm = document.getElementById('uj-login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var err = document.getElementById('uj-login-error');
      err.textContent = '';
      var fd = new FormData(loginForm);
      ajaxPost('cg_login_user', {
        username: fd.get('username'),
        password: fd.get('password'),
      }).then(function(res) {
        if (res.success) { window.location.reload(); }
        else { err.textContent = res.data || 'Login failed.'; }
      }).catch(function() { err.textContent = 'Network error.'; });
    });
  }

  /* ── Register form ──────────────────────────────────────── */
  var registerForm = document.getElementById('uj-register-form');
  if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var err = document.getElementById('uj-register-error');
      err.textContent = '';
      var fd = new FormData(registerForm);
      ajaxPost('cg_register_user', {
        username: fd.get('username'),
        email:    fd.get('email'),
        password: fd.get('password'),
      }).then(function(res) {
        if (res.success) { window.location.reload(); }
        else { err.textContent = res.data || 'Registration failed.'; }
      }).catch(function() { err.textContent = 'Network error.'; });
    });
  }

  if (!LOGGED_IN) return;

  /* ── Logout ─────────────────────────────────────────────── */
  var logoutBtn = document.getElementById('uj-logout-btn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
      ajaxPost('cg_logout_user', {}).then(function() { window.location.reload(); });
    });
  }

  // Change Password modal
  var changePwBtn    = document.getElementById('uj-change-pw-btn');
  var changePwOverlay= document.getElementById('uj-change-pw-overlay');
  var cpwCancel      = document.getElementById('cpw-cancel-btn');
  var cpwSubmit      = document.getElementById('cpw-submit-btn');
  var cpwError       = document.getElementById('cpw-error');
  function openChangePw() {
    document.getElementById('cpw-current').value = '';
    document.getElementById('cpw-new').value     = '';
    document.getElementById('cpw-confirm').value = '';
    if (cpwError) cpwError.textContent = '';
    if (changePwOverlay) { changePwOverlay.style.display = 'flex'; }
  }
  function closeChangePw() {
    if (changePwOverlay) { changePwOverlay.style.display = 'none'; }
  }
  if (changePwBtn)    changePwBtn.addEventListener('click', openChangePw);
  if (cpwCancel)      cpwCancel.addEventListener('click', closeChangePw);
  if (changePwOverlay) changePwOverlay.addEventListener('click', function(e) {
    if (e.target === changePwOverlay) closeChangePw();
  });
  if (cpwSubmit) cpwSubmit.addEventListener('click', function() {
    var cur     = document.getElementById('cpw-current').value;
    var nw      = document.getElementById('cpw-new').value;
    var confirm = document.getElementById('cpw-confirm').value;
    if (cpwError) cpwError.textContent = '';
    cpwSubmit.disabled = true;
    cpwSubmit.textContent = 'Updating…';
    ajaxPost('cg_change_password', {
      current_password: cur,
      new_password:     nw,
      confirm_password: confirm,
    }).then(function(res) {
      cpwSubmit.disabled = false;
      cpwSubmit.textContent = 'Update Password';
      if (res.success) {
        closeChangePw();
        alert('Password updated successfully. Please use your new password next time you sign in.');
      } else {
        if (cpwError) cpwError.textContent = res.data || 'Something went wrong.';
      }
    }).catch(function() {
      cpwSubmit.disabled = false;
      cpwSubmit.textContent = 'Update Password';
      if (cpwError) cpwError.textContent = 'Request failed — please try again.';
    });
  });

  /* ════════════════════════════════════════════════════════════
     UJ CHARACTER BUILDER STATE
  ════════════════════════════════════════════════════════════ */
  var state = {
    allData:         null,
    personalities:   [],
    characters:      [],
    currentChar:     null,
    speciesId:       null,
    typeId:          null,
    careerId:        null,
    bodyDie:         'd6',
    speedDie:        'd6',
    mindDie:         'd6',
    willDie:         'd4',
    speciesDie:      'd6',
    typeDie:         'd6',
    careerDie:       'd6',
    personalityWord: '',
    charName:        '',
    notes:           '',
    allySpeciesId:   null,
    allyCareerId:    null,
    allyName:        '',
    allyGender:      '',
    allyBodyDie:     'd6',
    allySpeedDie:    'd6',
    allyMindDie:     'd6',
    allyWillDie:     'd6',
    giftChoices:     {},
    experience:      0,
    purchasedGifts:  [],
    currentStep:     0,
    extraCareerId:   null,
    extraTypeId:     null,
    extraCareerDie:  null,
    extraTypeDie:    null,
  };

  var DICE_POOL   = ['d4', 'd6', 'd6', 'd6', 'd6', 'd8', 'd8'];
  var STEP_LABELS = ['Species', 'Type', 'Career', 'Traits', 'Personality', 'Gifts', 'Summary'];
  var UJ_TRAITS   = ['Body', 'Speed', 'Mind', 'Will', 'Type', 'Species', 'Career'];
  var DIE_STEPS   = ['d4', 'd6', 'd8', 'd10', 'd12'];

  function stepDieUp(die, steps) {
    var idx = DIE_STEPS.indexOf(die);
    if (idx < 0) return die;
    return DIE_STEPS[Math.min(idx + steps, DIE_STEPS.length - 1)];
  }

  function effectiveDie(baseDie, traitLabel) {
    if (!state.purchasedGifts || !state.purchasedGifts.length) return baseDie;
    var slug  = 'improved-trait-' + traitLabel.toLowerCase().replace(/\s+/g, '-');
    var count = state.purchasedGifts.filter(function(p) { return p.slug === slug; }).length;
    return count ? stepDieUp(baseDie, count) : baseDie;
  }

  function effectiveExtraCareerDie() {
    if (!state.extraCareerDie) return null;
    return effectiveDie(state.extraCareerDie, 'Extra Career');
  }

  function effectiveExtraTypeDie() {
    if (!state.extraTypeDie) return null;
    return effectiveDie(state.extraTypeDie, 'Extra Type');
  }

  /* ── DOM refs ───────────────────────────────────────────── */
  var loadingEl     = document.getElementById('uj-builder-loading');
  var listScreen    = document.getElementById('uj-char-list-screen');
  var wizardScreen  = document.getElementById('uj-wizard-screen');
  var developScreen = document.getElementById('uj-develop-screen');

  /* ── Gifts that may be purchased more than once ─────────── */
  var ALLOWS_MULTIPLE = { 'wealth': true };

  /* ── Boot ──────────────────────────────────────────────── */
  Promise.all([
    ajaxPost('uj_get_all_full', {}),
    ajaxPost('cg_get_personality_list', {}),
    ajaxPost('uj_load_characters', {}),
  ]).then(function(results) {
    if (results[0].success) state.allData       = results[0].data;
    if (results[1].success) state.personalities = results[1].data || [];
    if (results[2].success) state.characters    = results[2].data || [];
    if (loadingEl) loadingEl.style.display = 'none';
    showCharList();
  }).catch(function(err) {
    if (loadingEl) {
      loadingEl.textContent = 'Error loading data — ' + (err && err.message ? err.message : 'please refresh.');
    }
    console.error('[UJ Builder]', err);
  });

  /* ════════════════════════════════════════════════════════════
     CHARACTER LIST
  ════════════════════════════════════════════════════════════ */
  function showCharList() {
    if (developScreen) developScreen.style.display = 'none';
    if (wizardScreen)  wizardScreen.style.display  = 'none';
    if (listScreen)    listScreen.style.display    = 'none';
    ajaxPost('uj_load_characters', {}).then(function(res) {
      state.characters = (res && res.success && Array.isArray(res.data)) ? res.data : [];
      renderCharList();
      if (listScreen) listScreen.style.display = 'block';
    });
  }

  function renderCharList() {
    if (!listScreen) return;
    var html = '<div class="char-list-header">' +
      '<h2>My Characters</h2>' +
      '<button class="uj-btn uj-btn-amber" id="uj-new-char-btn">+ New Character</button>' +
      '</div>';

    if (state.characters.length === 0) {
      html += '<div class="char-empty">No characters yet. Create your first one!</div>';
    } else {
      html += '<div class="char-cards">';
      state.characters.forEach(function(c) {
        var d  = state.allData || {};
        var sp = (d.species  || []).find(function(x) { return x.id == c.species_id; });
        var ty = (d.types    || []).find(function(x) { return x.id == c.type_id;    });
        var ca = (d.careers  || []).find(function(x) { return x.id == c.career_id;  });
        var detail  = [sp ? sp.name : null, ty ? ty.name : null, ca ? ca.name : null].filter(Boolean).join(' / ');
        var dice    = [c.body_die, c.speed_die, c.mind_die, c.will_die].filter(Boolean).join(' ');
        var date    = c.updated_at ? c.updated_at.substring(0, 10) : '';
        var xpTotal = parseInt(c.experience || 0, 10);
        var purchased;
        try { purchased = JSON.parse(c.purchased_gifts || '[]') || []; } catch(e) { purchased = []; }
        var xpSpent = purchased.reduce(function(s, p) { return s + (p.xp_cost || 10); }, 0);
        var xpAvail = xpTotal - xpSpent;
        html += '<div class="char-card" data-id="' + esc(c.id) + '">' +
          '<div class="char-card-name">' + esc(c.name || '(Unnamed)') + '</div>' +
          '<div class="char-card-detail">' + esc(detail || 'Incomplete') + '</div>' +
          (dice ? '<div class="char-card-detail" style="color:var(--uj-amber);font-family:\'Cinzel\',serif;font-size:0.8rem;margin-top:0.25rem;">' + esc(dice) + '</div>' : '') +
          (xpTotal > 0 ? '<div class="char-card-detail" style="color:var(--uj-teal);font-size:0.78rem;margin-top:0.2rem;">' + xpAvail + ' XP available (' + xpTotal + ' earned)</div>' : '') +
          '<div class="char-card-footer">' +
            '<span class="char-card-date">' + esc(date) + '</span>' +
            '<div style="display:flex;gap:0.4rem;flex-wrap:wrap;">' +
              '<button class="uj-btn uj-btn-ghost" style="font-size:0.7rem;padding:0.3rem 0.7rem;" data-load="' + esc(c.id) + '">Edit</button>' +
              '<button class="uj-btn uj-btn-teal" style="font-size:0.7rem;padding:0.3rem 0.7rem;" data-develop="' + esc(c.id) + '">Develop</button>' +
              '<button class="uj-btn uj-btn-amber" style="font-size:0.7rem;padding:0.3rem 0.7rem;" data-sheet="' + esc(c.id) + '">Sheet</button>' +
              '<button class="uj-btn uj-btn-danger" data-delete="' + esc(c.id) + '">Delete</button>' +
            '</div>' +
          '</div>' +
        '</div>';
      });
      html += '</div>';
    }
    listScreen.innerHTML = html;

    var newBtn = document.getElementById('uj-new-char-btn');
    if (newBtn) newBtn.addEventListener('click', startNewChar);

    listScreen.querySelectorAll('[data-load]').forEach(function(btn) {
      btn.addEventListener('click', function(e) { e.stopPropagation(); loadChar(btn.dataset.load); });
    });
    listScreen.querySelectorAll('[data-develop]').forEach(function(btn) {
      btn.addEventListener('click', function(e) { e.stopPropagation(); loadAndDevelop(btn.dataset.develop); });
    });
    listScreen.querySelectorAll('[data-sheet]').forEach(function(btn) {
      btn.addEventListener('click', function(e) { e.stopPropagation(); loadAndViewSheet(btn.dataset.sheet); });
    });
    listScreen.querySelectorAll('[data-delete]').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (!confirm('Delete this character?')) return;
        deleteChar(btn.dataset.delete);
      });
    });
    listScreen.querySelectorAll('.char-card').forEach(function(card) {
      card.addEventListener('click', function() { loadChar(card.dataset.id); });
    });
  }

  function startNewChar() {
    state.currentChar    = null;
    state.speciesId      = null;
    state.typeId         = null;
    state.careerId       = null;
    state.bodyDie        = '';
    state.speedDie       = '';
    state.mindDie        = '';
    state.willDie        = '';
    state.speciesDie     = '';
    state.typeDie        = '';
    state.careerDie      = '';
    state.personalityWord= '';
    state.charName       = '';
    state.notes          = '';
    state.allySpeciesId  = null;
    state.allyCareerId   = null;
    state.giftChoices    = {};
    state.experience     = 0;
    state.purchasedGifts = [];
    state.currentStep    = 0;
    state.extraCareerId  = null;
    state.extraTypeId    = null;
    state.extraCareerDie = null;
    state.extraTypeDie   = null;
    showWizard();
  }

  function loadChar(id) {
    ajaxPost('uj_get_character', { id: id }).then(function(res) {
      if (!res.success) { alert(res.data || 'Error loading character.'); return; }
      var c = res.data;
      state.currentChar    = String(c.id);
      state.speciesId      = c.species_id ? Number(c.species_id) : null;
      state.typeId         = c.type_id    ? Number(c.type_id)    : null;
      state.careerId       = c.career_id  ? Number(c.career_id)  : null;
      state.bodyDie        = c.body_die   || 'd8';
      state.speedDie       = c.speed_die  || 'd8';
      state.mindDie        = c.mind_die   || 'd6';
      state.willDie        = c.will_die   || 'd4';
      state.speciesDie     = c.species_die || 'd8';
      state.typeDie        = c.type_die   || 'd8';
      state.careerDie      = c.career_die || 'd8';
      state.personalityWord= c.personality_word || '';
      state.charName       = c.name       || '';
      state.notes          = c.notes      || '';
      state.allySpeciesId  = c.ally_species_id ? Number(c.ally_species_id) : null;
      state.allyCareerId   = c.ally_career_id  ? Number(c.ally_career_id)  : null;
      state.giftChoices    = (function() {
        try { return JSON.parse(c.gift_choices || '{}') || {}; } catch(e) { return {}; }
      })();
      state.experience     = parseInt(c.experience || 0, 10);
      state.purchasedGifts = (function() {
        try { return JSON.parse(c.purchased_gifts || '[]') || []; } catch(e) { return []; }
      })();
      state.extraCareerId  = c.extra_career_id ? Number(c.extra_career_id) : null;
      state.extraTypeId    = c.extra_type_id   ? Number(c.extra_type_id)   : null;
      state.extraCareerDie = c.extra_career_die || null;
      state.extraTypeDie   = c.extra_type_die   || null;
      state.currentStep    = 0;
      showWizard();
    });
  }

  function deleteChar(id) {
    ajaxPost('uj_delete_character', { id: id }).then(function() {
      state.characters = state.characters.filter(function(c) { return c.id != id; });
      renderCharList();
    });
  }

  /* ════════════════════════════════════════════════════════════
     CHARACTER DEVELOPMENT
  ════════════════════════════════════════════════════════════ */
  function loadAndDevelop(id) {
    ajaxPost('uj_get_character', { id: id }).then(function(res) {
      if (!res || !res.data) return;
      var c = res.data;
      state.currentChar    = c.id;
      state.charName       = c.name || '';
      state.speciesId      = c.species_id ? Number(c.species_id) : null;
      state.typeId         = c.type_id    ? Number(c.type_id)    : null;
      state.careerId       = c.career_id  ? Number(c.career_id)  : null;
      state.allySpeciesId  = c.ally_species_id ? Number(c.ally_species_id) : null;
      state.allyCareerId   = c.ally_career_id  ? Number(c.ally_career_id)  : null;
      state.allyName       = c.ally_name   || '';
      state.allyGender     = c.ally_gender || '';
      state.allyBodyDie    = c.ally_body_die  || 'd6';
      state.allySpeedDie   = c.ally_speed_die || 'd6';
      state.allyMindDie    = c.ally_mind_die  || 'd6';
      state.allyWillDie    = c.ally_will_die  || 'd6';
      state.experience     = parseInt(c.experience || 0, 10);
      state.purchasedGifts = (function() {
        try { return JSON.parse(c.purchased_gifts || '[]') || []; } catch(e) { return []; }
      })();
      state.extraCareerId  = c.extra_career_id ? Number(c.extra_career_id) : null;
      state.extraTypeId    = c.extra_type_id   ? Number(c.extra_type_id)   : null;
      state.extraCareerDie = c.extra_career_die || null;
      state.extraTypeDie   = c.extra_type_die   || null;
      showDevelop();
    });
  }

  function loadAndViewSheet(id) {
    ajaxPost('uj_get_character', { id: id }).then(function(res) {
      if (!res || !res.data) return;
      var c = res.data;
      state.currentChar     = c.id;
      state.charName        = c.name || '';
      state.speciesId       = c.species_id ? Number(c.species_id) : null;
      state.typeId          = c.type_id    ? Number(c.type_id)    : null;
      state.careerId        = c.career_id  ? Number(c.career_id)  : null;
      state.bodyDie         = c.body_die   || '';
      state.speedDie        = c.speed_die  || '';
      state.mindDie         = c.mind_die   || '';
      state.willDie         = c.will_die   || '';
      state.speciesDie      = c.species_die || '';
      state.typeDie         = c.type_die   || '';
      state.careerDie       = c.career_die || '';
      state.personalityWord = c.personality_word || '';
      state.notes           = c.notes      || '';
      state.allySpeciesId   = c.ally_species_id ? Number(c.ally_species_id) : null;
      state.allyCareerId    = c.ally_career_id  ? Number(c.ally_career_id)  : null;
      state.allyName        = c.ally_name   || '';
      state.allyGender      = c.ally_gender || '';
      state.allyBodyDie     = c.ally_body_die  || 'd6';
      state.allySpeedDie    = c.ally_speed_die || 'd6';
      state.allyMindDie     = c.ally_mind_die  || 'd6';
      state.allyWillDie     = c.ally_will_die  || 'd6';
      state.giftChoices     = (function() {
        try { return JSON.parse(c.gift_choices || '{}') || {}; } catch(e) { return {}; }
      })();
      state.experience      = parseInt(c.experience || 0, 10);
      state.purchasedGifts  = (function() {
        try { return JSON.parse(c.purchased_gifts || '[]') || []; } catch(e) { return []; }
      })();
      state.extraCareerId   = c.extra_career_id ? Number(c.extra_career_id) : null;
      state.extraTypeId     = c.extra_type_id   ? Number(c.extra_type_id)   : null;
      state.extraCareerDie  = c.extra_career_die || null;
      state.extraTypeDie    = c.extra_type_die   || null;
      state.currentStep = 6;
      showWizard();
    });
  }

  function showDevelop() {
    if (listScreen)    listScreen.style.display    = 'none';
    if (wizardScreen)  wizardScreen.style.display  = 'none';
    if (developScreen) developScreen.style.display = 'block';
    renderDevelop();
  }

  function renderDevelop() {
    if (!developScreen) return;
    var d        = state.allData || {};
    var allGifts = d.gifts  || [];
    var allSoaks = d.soaks  || [];

    var xpTotal  = state.experience;
    var xpSpent  = state.purchasedGifts.reduce(function(s, p) { return s + (p.xp_cost || 10); }, 0);
    var xpAvail  = xpTotal - xpSpent;

    var sp      = (d.species  || []).find(function(x) { return x.id == state.speciesId;      });
    var ty      = (d.types    || []).find(function(x) { return x.id == state.typeId;         });
    var ca      = (d.careers  || []).find(function(x) { return x.id == state.careerId;       });
    var extraCa = state.extraCareerId ? ((d.careers || []).find(function(x) { return x.id == state.extraCareerId; }) || null) : null;
    var extraTy = state.extraTypeId   ? ((d.types   || []).find(function(x) { return x.id == state.extraTypeId;   }) || null) : null;
    var subtitle = [sp ? sp.name : '', ty ? ty.name : '', ca ? ca.name : ''].filter(Boolean).join(' / ');

    // Build set of slugs already granted through species / type / career at creation
    var creationGrantedSlugs = {};
    function addCreationSlugs(arr) {
      (arr || []).forEach(function(g) { creationGrantedSlugs[g.slug || String(g.id)] = true; });
    }
    if (sp) addCreationSlugs(sp.gifts);
    if (ty) addCreationSlugs(ty.gifts);
    if (ca) addCreationSlugs(ca.gifts);
    if (ty) addCreationSlugs(ty.soaks);

    function countPurchased(slug) {
      return state.purchasedGifts.filter(function(p) { return p.slug === slug; }).length;
    }

    function cardBadges(item, kind) {
      var owned = countPurchased(item.slug);
      var out = '<span class="dev-badge dev-badge-type">' + esc(kind) + '</span> ';
      if (owned) out += '<span class="dev-badge dev-badge-owned">Owned ×' + owned + '</span> ';
      return out;
    }

    // Helper: get current base die for a named trait
    function getDieForTrait(traitLabel) {
      var map = {
        'Body': state.bodyDie, 'Speed': state.speedDie,
        'Mind': state.mindDie, 'Will': state.willDie,
        'Species': state.speciesDie, 'Type': state.typeDie, 'Career': state.careerDie,
        'Extra Career': state.extraCareerDie || 'd4',
        'Extra Type':   state.extraTypeDie   || 'd4',
      };
      return map[traitLabel] || 'd6';
    }

    function buildShopCard(item, kind) {
      var owned        = countPurchased(item.slug);
      var canMultiple  = !!item._allowsMultiple || !!(item.requires_text && /again/i.test(item.requires_text));
      var fromCreation = !!creationGrantedSlugs[item.slug];
      var alreadyOwned = (owned > 0 || fromCreation) && !canMultiple;
      var atMaxDie     = false;
      if (item._traitLabel && canMultiple) {
        var baseDie = getDieForTrait(item._traitLabel);
        atMaxDie = effectiveDie(baseDie, item._traitLabel) === 'd12';
        if (atMaxDie) alreadyOwned = true;
      }
      var canAfford    = xpAvail >= 10;
      var btnDisabled  = alreadyOwned || !canAfford ? ' disabled' : '';
      var btnLabel     = fromCreation  ? 'Granted at creation'
                       : atMaxDie     ? 'At maximum (d12)'
                       : alreadyOwned ? 'Already owned'
                       : !canAfford   ? 'Need 10 XP'
                       :               'Buy — 10 XP';

      return '<div class="dev-shop-card" style="' + (alreadyOwned ? 'opacity:0.45;pointer-events:none;' : '') + '">' +
        '<div class="dev-shop-card-header">' +
          '<div class="dev-shop-card-name">' + esc(item.name) + '</div>' +
          '<div>' + cardBadges(item, kind) + '</div>' +
        '</div>' +
        (item.subtitle ? '<div style="font-size:0.78rem;color:var(--uj-amber-light);font-style:italic;margin-bottom:0.15rem;">' + esc(item.subtitle) + '</div>' : '') +
        (item.description ? '<p class="dev-shop-card-desc">' + esc(item.description) + '</p>' : '') +
        (item.side_effect ? '<p class="dev-shop-card-desc" style="color:var(--uj-text-dim);"><em>Side effect:</em> ' + esc(item.side_effect) + '</p>' : '') +
        (item.requires_text ? '<div class="dev-shop-requires"><strong>Requires/Notes:</strong> ' + esc(item.requires_text) + '</div>' : '') +
        '<div class="dev-shop-card-footer">' +
          '<button class="uj-btn uj-btn-teal" style="font-size:0.75rem;padding:0.3rem 0.85rem;" data-buy-slug="' + esc(item.slug) + '" data-buy-name="' + esc(item.name) + '" data-buy-kind="' + esc(kind) + '"' + btnDisabled + '>' + btnLabel + '</button>' +
        '</div>' +
      '</div>';
    }

    // Build set of all owned slugs (creation + purchased) — used for Extra trait eligibility
    var ownedSlugSet = {};
    Object.keys(creationGrantedSlugs).forEach(function(s) { ownedSlugSet[s] = true; });
    state.purchasedGifts.forEach(function(p) { ownedSlugSet[p.slug] = true; });

    function buildExtraTraitCard(g) {
      var isCareer         = g.slug === 'extra-career';
      var alreadyPurchased = isCareer ? (state.extraCareerId !== null) : (state.extraTypeId !== null);
      var canAfford        = xpAvail >= 10;
      var candidateList    = isCareer ? (d.careers || []) : (d.types || []);
      var kindLabel        = isCareer ? 'Career' : 'Type';

      // Eligible: must own every creation gift (and for type: soaks too) from any source
      // (granted at creation or purchased through development).
      // Exclude the character's own main career/type — you can't pick what you already are.
      var mainExcludeId = isCareer ? state.careerId : state.typeId;
      var eligibleItems = candidateList.filter(function(item) {
        if (item.id == mainExcludeId) return false;
        var required = (item.gifts || []).concat(isCareer ? [] : (item.soaks || []));
        return required.every(function(gift) {
          return ownedSlugSet[gift.slug || String(gift.id)];
        });
      });

      var selectedId   = isCareer ? state.extraCareerId : state.extraTypeId;
      var selectedName = '';
      var selectedDie  = isCareer ? state.extraCareerDie : state.extraTypeDie;
      if (selectedId) {
        var found = candidateList.find(function(x) { return x.id == selectedId; });
        if (found) selectedName = found.name;
      }

      var html = '<div class="dev-shop-card' + (alreadyPurchased ? '" style="opacity:0.65"' : '"') + '>';
      html += '<div class="dev-shop-card-header">';
      html += '<div class="dev-shop-card-name">' + esc(g.name) + '</div>';
      html += '<div><span class="dev-badge dev-badge-type">Gift</span>';
      if (alreadyPurchased) html += ' <span class="dev-badge dev-badge-owned">Purchased</span>';
      html += '</div></div>';
      if (g.subtitle) html += '<div style="font-size:0.78rem;color:var(--uj-amber-light);font-style:italic;margin-bottom:0.15rem;">' + esc(g.subtitle) + '</div>';
      if (g.description) html += '<p class="dev-shop-card-desc">' + esc(g.description) + '</p>';

      if (alreadyPurchased) {
        var effDie = effectiveDie(selectedDie, isCareer ? 'Extra Career' : 'Extra Type');
        html += '<div style="padding:0.4rem 0;font-size:0.85rem;color:var(--uj-teal);">' +
          'Chosen: <strong>' + esc(selectedName) + '</strong> — die: <strong>' + esc(effDie) + '</strong>' +
          (effDie !== selectedDie ? ' <span style="font-size:0.75rem;color:var(--uj-text-dim);">(base ' + esc(selectedDie) + ')</span>' : '') +
          '</div>';
      } else {
        html += '<div style="margin:0.6rem 0;">';
        if (eligibleItems.length === 0) {
          html += '<div style="font-size:0.82rem;color:var(--uj-text-dim);font-style:italic;">No eligible ' + kindLabel + 's yet — you must first purchase all creation gifts' +
            (isCareer ? '' : ' and soaks') + ' from a ' + kindLabel + ' through development.</div>';
        } else {
          html += '<label style="font-size:0.76rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.08em;display:block;margin-bottom:0.35rem;">Choose an eligible ' + kindLabel + ':</label>';
          html += '<select class="field-select extra-trait-select" style="width:100%;margin-bottom:0.5rem;">';
          html += '<option value="">— Select ' + kindLabel + ' —</option>';
          eligibleItems.forEach(function(item) {
            html += '<option value="' + esc(item.id) + '">' + esc(item.name) + '</option>';
          });
          html += '</select>';
        }
        html += '</div>';
        var btnDisabled2 = (!canAfford || eligibleItems.length === 0) ? ' disabled' : '';
        var btnLabel2    = !canAfford ? 'Need 10 XP' : (eligibleItems.length === 0 ? 'Not eligible yet' : 'Buy — 10 XP');
        html += '<div class="dev-shop-card-footer">' +
          '<button class="uj-btn uj-btn-teal" style="font-size:0.75rem;padding:0.3rem 0.85rem;" ' +
          'data-buy-slug="' + esc(g.slug) + '" data-buy-name="' + esc(g.name) + '" data-buy-kind="Gift"' + btnDisabled2 + '>' + btnLabel2 + '</button>' +
          '</div>';
      }
      html += '</div>';
      return html;
    }

    var IMPROVED_TRAITS = ['Body', 'Speed', 'Mind', 'Will', 'Career', 'Species', 'Type'];
    if (state.extraCareerId !== null) IMPROVED_TRAITS.push('Extra Career');
    if (state.extraTypeId   !== null) IMPROVED_TRAITS.push('Extra Type');

    function improvedTraitDisplayName(trait) {
      if (trait === 'Species'       && sp)      return 'Improved Species: '       + sp.name;
      if (trait === 'Type'          && ty)      return 'Improved Type: '          + ty.name;
      if (trait === 'Career'        && ca)      return 'Improved Career: '        + ca.name;
      if (trait === 'Extra Career'  && extraCa) return 'Improved Career: '        + extraCa.name;
      if (trait === 'Extra Type'    && extraTy) return 'Improved Type: '          + extraTy.name;
      return 'Improved ' + trait;
    }

    var expandedGifts = [];
    allGifts.forEach(function(g) {
      if (g.slug === 'improved-trait') {
        IMPROVED_TRAITS.forEach(function(trait) {
          expandedGifts.push(Object.assign({}, g, {
            name:            improvedTraitDisplayName(trait),
            slug:            'improved-trait-' + trait.toLowerCase().replace(/\s+/g, '-'),
            subtitle:        'Improved Trait [' + trait + ']',
            requires_text:   null,
            _allowsMultiple: true,
            _traitLabel:     trait,
          }));
        });
      } else if (g.slug === 'extra-career' || g.slug === 'extra-type') {
        expandedGifts.push(Object.assign({}, g, { _isExtraTrait: true }));
      } else {
        expandedGifts.push(g);
      }
    });
    var giftsHtml = expandedGifts.map(function(g) {
      return g._isExtraTrait ? buildExtraTraitCard(g) : buildShopCard(g, 'Gift');
    }).join('');
    var soaksHtml = allSoaks.map(function(s) { return buildShopCard(s, 'Soak'); }).join('');

    var historyHtml = '';
    if (state.purchasedGifts.length === 0) {
      historyHtml = '<p style="color:var(--uj-text-dim);font-style:italic;">No purchases yet.</p>';
    } else {
      state.purchasedGifts.forEach(function(p) {
        historyHtml += '<div class="dev-history-item">' +
          '<div class="dev-history-item-name">' + esc(p.name) + '</div>' +
          '<div style="font-size:0.8rem;color:var(--uj-text-dim);">' +
            '<span class="dev-badge dev-badge-type" style="margin-right:0.4rem;">' + esc(p.kind) + '</span>' +
            esc(p.xp_cost) + ' XP spent' +
            (p.purchased_on ? ' &mdash; ' + esc(p.purchased_on) : '') +
          '</div>' +
        '</div>';
      });
    }

    developScreen.innerHTML =
      '<div class="dev-header">' +
        '<div>' +
          '<div class="dev-char-name">' + esc(state.charName || '(Unnamed)') + '</div>' +
          (subtitle ? '<div class="dev-char-subtitle">' + esc(subtitle) + '</div>' : '') +
        '</div>' +
        '<button class="uj-btn uj-btn-ghost" id="dev-back-btn" style="font-size:0.8rem;">&#8592; Back to Characters</button>' +
      '</div>' +

      '<div class="dev-xp-panel">' +
        '<div class="dev-xp-stats">' +
          '<div class="dev-xp-stat"><div class="dev-xp-stat-val">' + xpTotal + '</div><div class="dev-xp-stat-label">XP Earned</div></div>' +
          '<div class="dev-xp-stat"><div class="dev-xp-stat-val" style="color:var(--uj-text-muted);">' + xpSpent + '</div><div class="dev-xp-stat-label">XP Spent</div></div>' +
          '<div class="dev-xp-stat"><div class="dev-xp-stat-val" style="color:var(--uj-teal);">' + xpAvail + '</div><div class="dev-xp-stat-label">XP Available</div></div>' +
        '</div>' +
        '<div class="dev-xp-award">' +
          '<input id="dev-xp-input" type="number" min="1" max="999" class="uj-input" style="width:6rem;" placeholder="XP">' +
          '<button class="uj-btn uj-btn-amber" id="dev-xp-award-btn">Award XP</button>' +
        '</div>' +
      '</div>' +

      (function() {
        var hasDevAlly = state.purchasedGifts.some(function(p) { return p.slug === 'ally'; });
        if (!hasDevAlly) return '';
        var speciesList = d.species || [];
        var careerList  = d.careers || [];
        var spOpts = '<option value="">— Choose Species —</option>' +
          speciesList.map(function(s) { return '<option value="' + esc(s.id) + '"' + (state.allySpeciesId == s.id ? ' selected' : '') + '>' + esc(s.name) + '</option>'; }).join('');
        var caOpts = '<option value="">— Choose Career —</option>' +
          careerList.map(function(c) { return '<option value="' + esc(c.id) + '"' + (state.allyCareerId == c.id ? ' selected' : '') + '>' + esc(c.name) + '</option>'; }).join('');
        var diceOpts = ['d4','d6','d8','d10','d12'].map(function(d) {
          return '<option value="' + d + '"' + (d === 'd6' ? ' selected' : '') + '>' + d + '</option>';
        }).join('');
        function diceOptsFor(current) {
          return ['d4','d6','d8','d10','d12'].map(function(d) {
            return '<option value="' + d + '"' + (d === current ? ' selected' : '') + '>' + d + '</option>';
          }).join('');
        }
        var genderOpts = ['','Male','Female','Non-binary','Unknown'].map(function(g) {
          return '<option value="' + g + '"' + (g === state.allyGender ? ' selected' : '') + '>' + (g || '— Gender —') + '</option>';
        }).join('');
        function labelDiv(lbl, content) {
          return '<div style="flex:1;min-width:120px;"><label style="font-size:0.7rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.08em;display:block;margin-bottom:0.35rem;">' + lbl + '</label>' + content + '</div>';
        }
        return '<div class="dev-xp-panel" style="flex-direction:column;align-items:flex-start;gap:0.9rem;">' +
          '<div style="font-family:\'Cinzel\',serif;font-size:0.8rem;font-weight:700;color:var(--uj-amber);letter-spacing:0.06em;text-transform:uppercase;">Ally Configuration</div>' +
          '<div style="display:flex;gap:0.75rem;flex-wrap:wrap;width:100%;">' +
            labelDiv('Ally Name', '<input type="text" id="dev-ally-name" class="field-input" placeholder="Name…" value="' + esc(state.allyName) + '" style="width:100%;">') +
            labelDiv('Gender', '<select class="field-select" id="dev-ally-gender" style="width:100%;">' + genderOpts + '</select>') +
            labelDiv('Species', '<select class="field-select" id="dev-ally-species" style="width:100%;">' + spOpts + '</select>') +
            labelDiv('Career', '<select class="field-select" id="dev-ally-career" style="width:100%;">' + caOpts + '</select>') +
          '</div>' +
          '<div style="display:flex;gap:0.75rem;flex-wrap:wrap;width:100%;align-items:flex-end;">' +
            labelDiv('Body Die', '<select class="field-select" id="dev-ally-body-die" style="width:100%;">' + diceOptsFor(state.allyBodyDie) + '</select>') +
            labelDiv('Speed Die', '<select class="field-select" id="dev-ally-speed-die" style="width:100%;">' + diceOptsFor(state.allySpeedDie) + '</select>') +
            labelDiv('Mind Die', '<select class="field-select" id="dev-ally-mind-die" style="width:100%;">' + diceOptsFor(state.allyMindDie) + '</select>') +
            labelDiv('Will Die', '<select class="field-select" id="dev-ally-will-die" style="width:100%;">' + diceOptsFor(state.allyWillDie) + '</select>') +
            '<div style="display:flex;align-items:flex-end;padding-bottom:0.05rem;"><button class="uj-btn uj-btn-amber" id="dev-ally-save-btn" style="font-size:0.78rem;white-space:nowrap;">Save Ally</button></div>' +
          '</div>' +
        '</div>';
      })() +

      '<div class="dev-rule-box">' +
        '<strong>Development rules:</strong> Spend <strong>10 XP</strong> to buy a new Gift or Soak. ' +
        'Gifts may only be purchased multiple times if their description specifically says so.' +
      '</div>' +

      '<div class="dev-tabs">' +
        '<button class="dev-tab active" data-tab="gifts">Gifts</button>' +
        '<button class="dev-tab" data-tab="soaks">Soaks</button>' +
        '<button class="dev-tab" data-tab="history">Purchase History (' + state.purchasedGifts.length + ')</button>' +
      '</div>' +

      '<div class="dev-panel active" data-panel="gifts">' +
        '<div class="dev-shop-grid">' + giftsHtml + '</div>' +
      '</div>' +
      '<div class="dev-panel" data-panel="soaks">' +
        '<div class="dev-shop-grid">' + soaksHtml + '</div>' +
      '</div>' +
      '<div class="dev-panel" data-panel="history">' +
        '<div class="dev-history-list">' + historyHtml + '</div>' +
      '</div>';

    document.getElementById('dev-back-btn').addEventListener('click', showCharList);

    document.getElementById('dev-xp-award-btn').addEventListener('click', function() {
      var input = document.getElementById('dev-xp-input');
      var amt   = parseInt(input.value, 10);
      if (!amt || amt < 1) { alert('Enter a positive XP amount.'); return; }
      state.experience += amt;
      saveDevelop(renderDevelop);
    });

    var allyNameIn    = document.getElementById('dev-ally-name');
    var allyGenderSel = document.getElementById('dev-ally-gender');
    var allySpSel     = document.getElementById('dev-ally-species');
    var allyCaSel     = document.getElementById('dev-ally-career');
    var allyBodySel   = document.getElementById('dev-ally-body-die');
    var allySpeedSel  = document.getElementById('dev-ally-speed-die');
    var allyMindSel   = document.getElementById('dev-ally-mind-die');
    var allyWillSel   = document.getElementById('dev-ally-will-die');
    var allySaveBtn   = document.getElementById('dev-ally-save-btn');
    if (allyNameIn)    allyNameIn.addEventListener('input',   function() { state.allyName      = allyNameIn.value; });
    if (allyGenderSel) allyGenderSel.addEventListener('change', function() { state.allyGender  = allyGenderSel.value; });
    if (allySpSel)     allySpSel.addEventListener('change',   function() { state.allySpeciesId = allySpSel.value ? Number(allySpSel.value) : null; });
    if (allyCaSel)     allyCaSel.addEventListener('change',   function() { state.allyCareerId  = allyCaSel.value ? Number(allyCaSel.value) : null; });
    if (allyBodySel)   allyBodySel.addEventListener('change', function() { state.allyBodyDie   = allyBodySel.value; });
    if (allySpeedSel)  allySpeedSel.addEventListener('change',function() { state.allySpeedDie  = allySpeedSel.value; });
    if (allyMindSel)   allyMindSel.addEventListener('change', function() { state.allyMindDie   = allyMindSel.value; });
    if (allyWillSel)   allyWillSel.addEventListener('change', function() { state.allyWillDie   = allyWillSel.value; });
    if (allySaveBtn)   allySaveBtn.addEventListener('click',  function() { saveDevelop(function() {
      allySaveBtn.textContent = 'Saved!';
      setTimeout(function() { allySaveBtn.textContent = 'Save Ally'; }, 2000);
    }); });

    developScreen.querySelectorAll('.dev-tab').forEach(function(tab) {
      tab.addEventListener('click', function() {
        developScreen.querySelectorAll('.dev-tab').forEach(function(t) { t.classList.remove('active'); });
        developScreen.querySelectorAll('.dev-panel').forEach(function(p) { p.classList.remove('active'); });
        tab.classList.add('active');
        developScreen.querySelector('[data-panel="' + tab.dataset.tab + '"]').classList.add('active');
      });
    });

    developScreen.querySelectorAll('[data-buy-slug]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var slug = btn.dataset.buySlug;
        var name = btn.dataset.buyName;
        var kind = btn.dataset.buyKind;
        var today = new Date().toISOString().substring(0, 10);

        // Extra Career / Extra Type — need a selection from the dropdown first
        if (slug === 'extra-career' || slug === 'extra-type') {
          var sel = btn.closest('.dev-shop-card').querySelector('.extra-trait-select');
          var selectedId = sel && sel.value ? Number(sel.value) : null;
          if (!selectedId) {
            alert('Please select a ' + (slug === 'extra-career' ? 'Career' : 'Type') + ' from the list first.');
            return;
          }
          var selectedName = sel.options[sel.selectedIndex].text;
          if (!confirm('Spend 10 XP to purchase "' + name + ': ' + selectedName + '"?')) return;
          if (slug === 'extra-career') {
            state.extraCareerId  = selectedId;
            state.extraCareerDie = 'd4';
          } else {
            state.extraTypeId  = selectedId;
            state.extraTypeDie = 'd4';
          }
          state.purchasedGifts.push({ slug: slug, name: name + ': ' + selectedName, kind: kind, xp_cost: 10, purchased_on: today });
          saveDevelop(renderDevelop);
          return;
        }

        if (!confirm('Spend 10 XP to purchase "' + name + '"?')) return;
        state.purchasedGifts.push({ slug: slug, name: name, kind: kind, xp_cost: 10, purchased_on: today });
        saveDevelop(renderDevelop);
      });
    });
  }

  function saveDevelop(callback) {
    ajaxPost('uj_update_development', {
      id:               state.currentChar,
      experience:       state.experience,
      purchased_gifts:  JSON.stringify(state.purchasedGifts),
      ally_species_id:  state.allySpeciesId !== null ? state.allySpeciesId : '',
      ally_career_id:   state.allyCareerId  !== null ? state.allyCareerId  : '',
      ally_name:        state.allyName    || '',
      ally_gender:      state.allyGender  || '',
      ally_body_die:    state.allyBodyDie  || 'd6',
      ally_speed_die:   state.allySpeedDie || 'd6',
      ally_mind_die:    state.allyMindDie  || 'd6',
      ally_will_die:    state.allyWillDie  || 'd6',
      extra_career_id:  state.extraCareerId  !== null ? state.extraCareerId  : '',
      extra_type_id:    state.extraTypeId    !== null ? state.extraTypeId    : '',
      extra_career_die: state.extraCareerDie || '',
      extra_type_die:   state.extraTypeDie   || '',
    }).then(function() {
      if (callback) callback();
    });
  }

  /* ════════════════════════════════════════════════════════════
     WIZARD
  ════════════════════════════════════════════════════════════ */
  function showWizard() {
    if (listScreen)   listScreen.style.display   = 'none';
    if (wizardScreen) wizardScreen.style.display = 'block';
    renderWizard();
  }

  function renderWizard() {
    if (!wizardScreen) return;
    wizardScreen.innerHTML = buildWizardShell();
    renderStep(state.currentStep);
    bindWizardNav();
  }

  function buildWizardShell() {
    var prog = '<div class="wizard-progress">';
    STEP_LABELS.forEach(function(label, i) {
      if (i > 0) {
        prog += '<div class="wp-line' + (i <= state.currentStep ? ' done' : '') + '"></div>';
      }
      var cls = i < state.currentStep ? 'done' : (i === state.currentStep ? 'active' : '');
      prog += '<div class="wp-step ' + cls + '" title="' + label + '">' + (i + 1) + '</div>';
    });
    prog += '</div>';

    var isLast = state.currentStep === STEP_LABELS.length - 1;

    return '<div class="wizard-header">' +
        '<h2 class="wizard-title">' + esc(state.charName || 'New Character') + '</h2>' +
        prog +
        '<button class="uj-btn uj-btn-ghost" id="wiz-back-list-btn">← Characters</button>' +
      '</div>' +
      '<div id="wiz-step-container"></div>' +
      '<div class="wizard-nav">' +
        '<button class="uj-btn uj-btn-ghost" id="wiz-prev-btn"' + (state.currentStep === 0 ? ' disabled' : '') + '>← Back</button>' +
        '<div class="wizard-nav-right">' +
          '<span class="save-status" id="wiz-save-status"></span>' +
          '<button class="uj-btn uj-btn-amber" id="wiz-save-btn">Save</button>' +
          (isLast
            ? '<button class="uj-btn uj-btn-teal" id="wiz-print-btn">Print</button>'
            : '<button class="uj-btn uj-btn-teal" id="wiz-next-btn">Next →</button>') +
        '</div>' +
      '</div>';
  }

  function renderStep(step) {
    var container = document.getElementById('wiz-step-container');
    if (!container) return;
    switch (step) {
      case 0: container.innerHTML = buildSelectionStep('species', 'Step 1 — Choose Your Species', 'speciesId'); bindSelectionCards('species', 'speciesId'); break;
      case 1: container.innerHTML = buildSelectionStep('type',    'Step 2 — Choose Your Type',    'typeId');    bindSelectionCards('type',    'typeId');    break;
      case 2: container.innerHTML = buildSelectionStep('career',  'Step 3 — Choose Your Career',  'careerId');  bindSelectionCards('career',  'careerId');  break;
      case 3: container.innerHTML = buildDiceStep();        bindDiceStep();        break;
      case 4: container.innerHTML = buildPersonalityStep(); bindPersonalityStep(); break;
      case 5: container.innerHTML = buildGiftsStep();       bindGiftsStep();       break;
      case 6: container.innerHTML = buildSummaryStep(); break;
    }
  }

  function bindWizardNav() {
    var backBtn  = document.getElementById('wiz-back-list-btn');
    var prevBtn  = document.getElementById('wiz-prev-btn');
    var nextBtn  = document.getElementById('wiz-next-btn');
    var saveBtn  = document.getElementById('wiz-save-btn');
    var printBtn = document.getElementById('wiz-print-btn');

    if (backBtn) backBtn.addEventListener('click', function() {
      ajaxPost('uj_load_characters', {}).then(function(res) {
        if (res.success) state.characters = res.data || [];
        showCharList();
      });
    });
    if (prevBtn) prevBtn.addEventListener('click', function() {
      if (state.currentStep > 0) { state.currentStep--; renderWizard(); }
    });
    if (nextBtn) nextBtn.addEventListener('click', function() {
      if (validateStep(state.currentStep)) { state.currentStep++; renderWizard(); }
    });
    if (saveBtn)  saveBtn.addEventListener('click',  saveCharacter);
    if (printBtn) printBtn.addEventListener('click', function() { window.print(); });
  }

  function validateStep(step) {
    if (step === 0 && !state.speciesId) { alert('Please select a Species.'); return false; }
    if (step === 1 && !state.typeId)    { alert('Please select a Type.');    return false; }
    if (step === 2 && !state.careerId)  { alert('Please select a Career.');  return false; }
    if (step === 3 && !validateDice())  {
      alert('Dice must be exactly d8 + d8 + d6 + d4 — one d4 for the worst trait, two d8s for the best.');
      return false;
    }
    return true;
  }

  function validateDice() {
    var pool = [state.bodyDie, state.speedDie, state.mindDie, state.willDie,
                state.speciesDie, state.typeDie, state.careerDie].sort().join(',');
    return pool === DICE_POOL.slice().sort().join(',');
  }

  /* ════════════════════════════════════════════════════════════
     STEP BUILDERS
  ════════════════════════════════════════════════════════════ */

  /* ── Die picker for source die (Species / Type / Career) ── */
  function buildSourceDiePicker(entityType) {
    var dieKey   = SOURCE_DIE_KEY[entityType];
    var current  = state[dieKey] || 'd8';
    var label    = entityType.charAt(0).toUpperCase() + entityType.slice(1) + ' Die';
    var html = '<div class="source-die-row" id="source-die-row-' + entityType + '">' +
      '<span class="source-die-label">' + label + '</span>' +
      '<div class="source-die-options">';
    ALLOWED_DICE_JS.forEach(function(d) {
      html += '<label class="die-pill' + (current === d ? ' die-pill-active' : '') + '">' +
        '<input type="radio" name="source-die-' + entityType + '" value="' + d + '"' +
        (current === d ? ' checked' : '') + ' style="display:none;">' +
        d + '</label>';
    });
    html += '</div></div>';
    return html;
  }

  /* ── Generic selection step (Species / Type / Career) ──── */
  function buildSelectionStep(entityType, heading, stateKey) {
    var collKey = COLLECTION_KEY[entityType];
    var items   = (state.allData && state.allData[collKey]) || [];
    var html    = '<div class="step-heading">' + heading + '</div>';
    html += buildDetailPanel(entityType, state[stateKey]);
    html += '<div class="select-grid">';
    items.forEach(function(item) {
      var sel  = state[stateKey] == item.id ? ' selected' : '';
      var tags = (item.skills || []).slice(0, 2).map(function(s) {
        return '<span class="tag tag-skill">' + esc(s.name) + '</span>';
      }).join('');
      html += '<div class="select-card' + sel + '" data-id="' + item.id + '">' +
        '<div class="select-card-name">' + esc(item.name) + '</div>' +
        '<div class="select-card-tags">' + tags + '</div>' +
      '</div>';
    });
    html += '</div>';
    return html;
  }

  /* ── Detail panel ────────────────────────────────────────── */
  function buildDetailPanel(entityType, selectedId) {
    var item = null;
    if (selectedId && state.allData) {
      var collKey = COLLECTION_KEY[entityType];
      var list    = state.allData[collKey] || [];
      item        = list.find(function(x) { return x.id == selectedId; }) || null;
    }
    if (!item) {
      return '<div class="detail-panel"><span class="detail-panel-empty">Select a ' + entityType + ' to see its details.</span></div>';
    }

    var html = '<div class="detail-panel" id="uj-detail-panel">' +
      '<div class="detail-panel-name">' + esc(item.name) + '</div>' +
      '<div class="detail-panel-desc">' + esc(item.description || '') + '</div>' +
      '<div class="detail-grants">';

    if (item.skills && item.skills.length) {
      html += '<div class="detail-grant-group"><div class="detail-grant-label">Skills</div><ul class="detail-grant-list">';
      item.skills.forEach(function(s) { html += '<li>' + esc(s.name) + '</li>'; });
      html += '</ul></div>';
    }
    if (item.gifts && item.gifts.length) {
      html += '<div class="detail-grant-group"><div class="detail-grant-label">Gifts</div><ul class="detail-grant-list">';
      item.gifts.forEach(function(g) {
        html += '<li class="gift-item">' + esc(g.name);
        if (g.subtitle) {
          html += '<em style="display:block;font-style:italic;color:#4ade80;font-size:0.8rem;font-weight:400;margin-top:0.1rem;">' + esc(g.subtitle) + '</em>';
        }
        html += '</li>';
      });
      html += '</ul></div>';
    }
    if (item.soaks && item.soaks.length) {
      html += '<div class="detail-grant-group"><div class="detail-grant-label">Soaks</div><ul class="detail-grant-list">';
      item.soaks.forEach(function(s) { html += '<li class="soak-item">' + esc(s.name) + '</li>'; });
      html += '</ul></div>';
    }
    if (item.gear) {
      html += '<div class="detail-grant-group"><div class="detail-grant-label">Starting Gear</div><ul class="detail-grant-list">';
      item.gear.split(/\n/).forEach(function(line) {
        line = line.trim();
        if (line) html += '<li class="gear-item">' + esc(line) + '</li>';
      });
      html += '</ul></div>';
    }

    html += '</div></div>';
    return html;
  }

  /* ── Bind die-pill radio buttons ────────────────────────── */
  function bindDiePills(entityType) {
    var container = document.getElementById('wiz-step-container');
    if (!container) return;
    var dieKey = SOURCE_DIE_KEY[entityType];
    container.querySelectorAll('input[name="source-die-' + entityType + '"]').forEach(function(radio) {
      radio.addEventListener('change', function() {
        state[dieKey] = radio.value;
        container.querySelectorAll('.die-pill').forEach(function(lbl) {
          var inp = lbl.querySelector('input');
          if (inp && inp.name === 'source-die-' + entityType) {
            lbl.classList.toggle('die-pill-active', inp.value === radio.value);
          }
        });
      });
    });
  }

  /* ── Bind selection cards ────────────────────────────────── */
  function bindSelectionCards(entityType, stateKey) {
    var container = document.getElementById('wiz-step-container');
    if (!container) return;
    bindDiePills(entityType);
    container.querySelectorAll('.select-card').forEach(function(card) {
      card.addEventListener('click', function() {
        state[stateKey] = Number(card.dataset.id);
        container.querySelectorAll('.select-card').forEach(function(c) { c.classList.remove('selected'); });
        card.classList.add('selected');
        // Replace detail panel in place
        var oldPanel = document.getElementById('uj-detail-panel');
        if (!oldPanel) { oldPanel = container.querySelector('.detail-panel'); }
        if (oldPanel) {
          var tmp = document.createElement('div');
          tmp.innerHTML = buildDetailPanel(entityType, state[stateKey]);
          oldPanel.parentNode.replaceChild(tmp.firstChild, oldPanel);
        }
      });
    });
  }

  /* ── Step 3: Dice ────────────────────────────────────────── */
  function buildDiceStep() {
    var d  = state.allData || {};
    var sp = (d.species || []).find(function(x) { return x.id == state.speciesId; });
    var ty = (d.types   || []).find(function(x) { return x.id == state.typeId;   });
    var ca = (d.careers || []).find(function(x) { return x.id == state.careerId; });

    var slots = [
      { key: 'bodyDie',    label: 'Body',    sub: 'Trait' },
      { key: 'speedDie',   label: 'Speed',   sub: 'Trait' },
      { key: 'mindDie',    label: 'Mind',    sub: 'Trait' },
      { key: 'willDie',    label: 'Will',    sub: 'Trait' },
      { key: 'speciesDie', label: 'Species', sub: sp ? sp.name : 'Species' },
      { key: 'typeDie',    label: 'Type',    sub: ty ? ty.name : 'Type'    },
      { key: 'careerDie',  label: 'Career',  sub: ca ? ca.name : 'Career'  },
    ];

    var html = '<div class="step-heading">Step 4 — Assign Dice</div>' +
      '<p style="color:var(--uj-text-muted);font-size:0.9rem;margin:0 0 1rem;">Distribute the pool ' +
      '<strong style="color:var(--uj-amber);">d8, d8, d6, d6, d6, d6, d4</strong> ' +
      'across all seven slots — your four traits and your three source dice.</p>' +
      '<div class="dice-grid dice-grid-7">';

    slots.forEach(function(t) {
      html += '<div class="dice-trait">' +
        '<div class="dice-trait-name">' + t.label + '</div>' +
        '<div class="dice-trait-sub">' + esc(t.sub) + '</div>' +
        '<select class="dice-select" data-trait="' + t.key + '">' +
        '<option value=""' + (state[t.key] === '' ? ' selected' : '') + '>— Choose —</option>' +
        ALLOWED_DICE_JS.map(function(d) {
          return '<option value="' + d + '"' + (state[t.key] === d ? ' selected' : '') + '>' + d.toUpperCase() + '</option>';
        }).join('') +
        '</select>' +
      '</div>';
    });

    html += '</div>';
    html += '<p class="dice-hint">Assigned: <span id="dice-pool-display"></span></p>';
    html += '<p class="dice-error" id="dice-pool-error">Pool must be exactly d8 + d8 + d6 + d6 + d6 + d6 + d4 (7 dice total).</p>';
    return html;
  }

  var DICE_SLOT_KEYS = ['bodyDie','speedDie','mindDie','willDie','speciesDie','typeDie','careerDie'];

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
    document.querySelectorAll('.dice-select').forEach(function(sel) {
      var key       = sel.dataset.trait;
      var current   = state[key] || '';
      var remaining = getRemainingPoolFor(key);
      var html      = '<option value=""' + (current === '' ? ' selected' : '') + '>— Choose —</option>';
      ALLOWED_DICE_JS.forEach(function(d) {
        var avail   = (remaining[d] || 0) > 0;
        var chosen  = current === d;
        if (avail || chosen) {
          html += '<option value="' + d + '"' + (chosen ? ' selected' : '') + '>' + d.toUpperCase() + '</option>';
        }
      });
      sel.innerHTML = html;
    });
    updateDiceDisplay();
  }

  function bindDiceStep() {
    updateDiceSelects();
    document.querySelectorAll('.dice-select').forEach(function(sel) {
      sel.addEventListener('change', function() {
        state[sel.dataset.trait] = sel.value;
        updateDiceSelects();
      });
    });
  }

  function updateDiceDisplay() {
    var pool  = [state.bodyDie, state.speedDie, state.mindDie, state.willDie,
                 state.speciesDie, state.typeDie, state.careerDie];
    var disp  = document.getElementById('dice-pool-display');
    var errEl = document.getElementById('dice-pool-error');
    var filled = pool.filter(Boolean);
    if (disp)  disp.textContent = filled.length ? filled.join(', ') : '(none assigned yet)';
    if (errEl) errEl.style.display = validateDice() ? 'none' : 'block';
  }

  /* ── Step 4: Personality ─────────────────────────────────── */
  function buildPersonalityStep() {
    var opts = state.personalities.map(function(p) {
      return '<option value="' + esc(p) + '"' + (state.personalityWord === p ? ' selected' : '') + '>' + esc(p) + '</option>';
    }).join('');
    return '<div class="step-heading">Step 5 — Personality &amp; Name</div>' +
      '<div class="personality-row">' +
        '<div class="personality-field">' +
          '<label class="field-label">Character Name</label>' +
          '<input type="text" class="field-input" id="char-name-input" value="' + esc(state.charName) + '" placeholder="What are they called?">' +
        '</div>' +
        '<div class="personality-field">' +
          '<label class="field-label">Personality Trait</label>' +
          '<select class="field-select" id="personality-select">' +
          '<option value="">— Choose a trait —</option>' +
          opts +
          '</select>' +
        '</div>' +
      '</div>' +
      '<div style="margin-bottom:1rem;">' +
        '<label class="field-label">Notes (optional)</label>' +
        '<textarea class="field-textarea" id="char-notes-input" placeholder="Backstory, goals, appearance…">' + esc(state.notes) + '</textarea>' +
      '</div>' +
      '<p style="font-size:0.88rem;color:var(--uj-text-dim);">' +
        'The <strong style="color:var(--uj-text);">Personality</strong> gift is free for all characters. ' +
        'It grants a bonus d12 when you act according to your personality trait.' +
      '</p>';
  }

  function bindPersonalityStep() {
    var nameEl  = document.getElementById('char-name-input');
    var selEl   = document.getElementById('personality-select');
    var notesEl = document.getElementById('char-notes-input');
    if (nameEl)  nameEl.addEventListener('input',  function() { state.charName       = nameEl.value;  });
    if (selEl)   selEl.addEventListener('change',  function() { state.personalityWord= selEl.value;   });
    if (notesEl) notesEl.addEventListener('input', function() { state.notes          = notesEl.value; });
  }

  /* ── Step 5: Gifts ───────────────────────────────────────── */
  function collectAllGifts() {
    var d  = state.allData || {};
    var sp = (d.species || []).find(function(x) { return x.id == state.speciesId; }) || null;
    var ty = (d.types   || []).find(function(x) { return x.id == state.typeId;   }) || null;
    var ca = (d.careers || []).find(function(x) { return x.id == state.careerId; }) || null;
    var result = [];
    var slugCount = {};
    function addGifts(arr, src) {
      (arr || []).forEach(function(g) {
        var slug = g.slug || String(g.id);
        if (!slugCount[slug]) slugCount[slug] = 0;
        result.push({
          id:           g.id,
          name:         g.name,
          slug:         slug,
          subtitle:     g.subtitle     || '',
          description:  g.description  || '',
          requires_text: g.requires_text || '',
          source:       src,
          occurrence:   slugCount[slug]++,
        });
      });
    }
    if (sp) addGifts(sp.gifts, sp.name);
    if (ty) addGifts(ty.gifts, ty.name);
    if (ca) addGifts(ca.gifts, ca.name);
    return result;
  }

  function buildGiftsStep() {
    var gifts = collectAllGifts();
    var d = state.allData || {};
    var html = '<div class="step-heading">Step 6 — Your Gifts</div>';
    html += '<p style="font-size:0.88rem;color:var(--uj-text-dim);margin:0 0 1.25rem;">These gifts come with your Species, Type, and Career. Gifts marked with a choice require your input below.</p>';

    if (gifts.length === 0) {
      html += '<p style="color:var(--uj-text-dim);font-style:italic;">No gifts from your current selections.</p>';
      return html;
    }

    gifts.forEach(function(g) {
      var choiceKey   = g.slug + '_' + g.occurrence;
      var isImproved  = (g.slug === 'improved-trait');
      var isAlly      = (g.slug === 'ally');
      var needsChoice = isImproved || isAlly;
      var borderColor = needsChoice ? 'var(--uj-amber-border,#7c5c1e)' : 'var(--uj-border-cool)';

      html += '<div style="background:var(--uj-surface);border:1px solid ' + borderColor + ';border-radius:var(--uj-radius-lg,8px);padding:1rem 1.25rem;margin-bottom:0.75rem;">';

      html += '<div style="display:flex;align-items:baseline;gap:0.75rem;flex-wrap:wrap;margin-bottom:0.25rem;">';
      html += '<span style="font-family:\'Cinzel\',Georgia,serif;font-size:1rem;font-weight:700;color:var(--uj-amber-light);">' + esc(g.name) + '</span>';
      html += '<span style="font-size:0.73rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.06em;">' + esc(g.source) + '</span>';
      html += '</div>';

      if (g.subtitle) {
        html += '<div style="font-style:italic;color:#4ade80;font-size:0.9rem;margin-bottom:0.5rem;">' + esc(g.subtitle) + '</div>';
      }

      if (isAlly) {
        var allyDesc = 'You have a friend! Your friend is a Minor Typical character, with a Species and a Career, a d6 in all six Traits, and the four gifts they get from those two choices. (Your Ally does not have a Type Trait.) Your Ally also has the Soak of Distress Soak \u22124. Your friend is normally controlled by the Game Host, but the Host may let you \u201ctake control\u201d and use the Ally as if it were your own character. Your Ally always has your best interest in mind. They would never betray you, but they might be deceived by villains. Or they might be captured and held hostage. If your Ally is killed, or otherwise leaves the game, you will have to retrain this gift.';
        html += '<p style="font-size:0.88rem;color:var(--uj-text-muted);margin:0 0 0.9rem;line-height:1.5;">' + esc(allyDesc) + '</p>';

        var speciesList = d.species || [];
        var careerList  = d.careers || [];
        html += '<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:0.5rem;">';

        html += '<div style="flex:1;min-width:160px;">';
        html += '<label style="font-family:\'Cinzel\',Georgia,serif;font-size:0.7rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.1em;display:block;margin-bottom:0.35rem;">Ally Species</label>';
        html += '<select class="field-select" id="uj-ally-species-select" style="width:100%;">';
        html += '<option value="">— Choose Species —</option>';
        speciesList.forEach(function(sp) {
          html += '<option value="' + esc(sp.id) + '"' + (state.allySpeciesId == sp.id ? ' selected' : '') + '>' + esc(sp.name) + '</option>';
        });
        html += '</select></div>';

        html += '<div style="flex:1;min-width:160px;">';
        html += '<label style="font-family:\'Cinzel\',Georgia,serif;font-size:0.7rem;color:var(--uj-text-dim);text-transform:uppercase;letter-spacing:0.1em;display:block;margin-bottom:0.35rem;">Ally Career</label>';
        html += '<select class="field-select" id="uj-ally-career-select" style="width:100%;">';
        html += '<option value="">— Choose Career —</option>';
        careerList.forEach(function(ca) {
          html += '<option value="' + esc(ca.id) + '"' + (state.allyCareerId == ca.id ? ' selected' : '') + '>' + esc(ca.name) + '</option>';
        });
        html += '</select></div>';

        html += '</div>';

      } else if (isImproved) {
        var currentChoice = state.giftChoices[choiceKey] || '';
        if (g.description) {
          var desc = g.description.length > 300 ? g.description.slice(0, 297) + '\u2026' : g.description;
          html += '<p style="font-size:0.88rem;color:var(--uj-text-muted);margin:0 0 0.75rem;line-height:1.5;">' + esc(desc) + '</p>';
        }
        html += '<div style="margin-top:0.25rem;">';
        html += '<div style="font-size:0.76rem;color:var(--uj-text-dim);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.08em;">Choose a trait to improve:</div>';
        html += '<div class="uj-trait-picker" data-choice-key="' + esc(choiceKey) + '" style="display:flex;flex-wrap:wrap;gap:0.4rem;">';
        UJ_TRAITS.forEach(function(trait) {
          var active = (currentChoice === trait);
          html += '<label class="uj-trait-pill" data-trait="' + esc(trait) + '" style="cursor:pointer;padding:0.35rem 0.9rem;border-radius:999px;font-size:0.82rem;user-select:none;' +
            'border:1px solid ' + (active ? 'var(--uj-amber)' : 'var(--uj-border-cool)') + ';' +
            'color:' + (active ? 'var(--uj-amber-light)' : 'var(--uj-text-muted)') + ';' +
            'background:' + (active ? 'rgba(180,120,30,0.18)' : 'transparent') + ';">' +
            '<input type="radio" name="imptrait-' + esc(choiceKey) + '" value="' + esc(trait) + '"' + (active ? ' checked' : '') + ' style="display:none;">' +
            esc(trait) + '</label>';
        });
        html += '</div></div>';

      } else {
        if (g.description) {
          var desc2 = g.description.length > 280 ? g.description.slice(0, 277) + '\u2026' : g.description;
          html += '<p style="font-size:0.88rem;color:var(--uj-text-muted);margin:0;line-height:1.5;">' + esc(desc2) + '</p>';
        }
      }

      html += '</div>';
    });

    return html;
  }

  function bindGiftsStep() {
    var container = document.getElementById('wiz-step-container');
    if (!container) return;

    container.querySelectorAll('.uj-trait-picker').forEach(function(picker) {
      var choiceKey = picker.dataset.choiceKey;
      picker.querySelectorAll('.uj-trait-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
          var radio = pill.querySelector('input[type="radio"]');
          if (!radio) return;
          var trait = radio.value;
          state.giftChoices[choiceKey] = trait;
          picker.querySelectorAll('.uj-trait-pill').forEach(function(p) {
            var active = (p.dataset.trait === trait);
            p.style.borderColor  = active ? 'var(--uj-amber)'       : 'var(--uj-border-cool)';
            p.style.color        = active ? 'var(--uj-amber-light)'  : 'var(--uj-text-muted)';
            p.style.background   = active ? 'rgba(180,120,30,0.18)'  : 'transparent';
          });
        });
      });
    });

    var allySpSel = container.querySelector('#uj-ally-species-select');
    var allyCaSel = container.querySelector('#uj-ally-career-select');
    if (allySpSel) allySpSel.addEventListener('change', function() {
      state.allySpeciesId = allySpSel.value ? Number(allySpSel.value) : null;
    });
    if (allyCaSel) allyCaSel.addEventListener('change', function() {
      state.allyCareerId = allyCaSel.value ? Number(allyCaSel.value) : null;
    });
  }

  /* ── Step 6: Summary ─────────────────────────────────────── */
  function buildSummaryStep() {
    var d      = state.allData || {};
    var sp     = (d.species  || []).find(function(x) { return x.id == state.speciesId;     }) || null;
    var ty     = (d.types    || []).find(function(x) { return x.id == state.typeId;        }) || null;
    var ca     = (d.careers  || []).find(function(x) { return x.id == state.careerId;      }) || null;
    var extraCa = state.extraCareerId ? ((d.careers || []).find(function(x) { return x.id == state.extraCareerId; }) || null) : null;
    var extraTy = state.extraTypeId   ? ((d.types   || []).find(function(x) { return x.id == state.extraTypeId;   }) || null) : null;
    var effExtraCaDie = effectiveExtraCareerDie();
    var effExtraTyDie = effectiveExtraTypeDie();

    /* Helper: does item grant a skill by name? */
    function itemGrantsSkill(item, skillName) {
      if (!item || !item.skills) return false;
      return item.skills.some(function(s) {
        return s.name.toLowerCase() === skillName.toLowerCase();
      });
    }

    /* Helper: collect all dice granted for a skill name */
    function skillDiceFor(skillName) {
      var dice = [];
      if (itemGrantsSkill(sp,     skillName) && state.speciesDie) dice.push({ die: state.speciesDie, src: 'Species'      });
      if (itemGrantsSkill(ty,     skillName) && state.typeDie)    dice.push({ die: state.typeDie,    src: 'Type'         });
      if (itemGrantsSkill(ca,     skillName) && state.careerDie)  dice.push({ die: state.careerDie,  src: 'Career'       });
      if (itemGrantsSkill(extraCa,skillName) && effExtraCaDie)    dice.push({ die: effExtraCaDie,    src: 'Extra Career' });
      if (itemGrantsSkill(extraTy,skillName) && effExtraTyDie)    dice.push({ die: effExtraTyDie,    src: 'Extra Type'   });
      return dice;
    }

    // Merge & deduplicate gifts by id; track occurrence per slug for choice keys
    var giftMap = {};
    var summarySlugCount = {};
    function addGifts(arr, src) {
      (arr || []).forEach(function(g) {
        if (!giftMap[g.id]) {
          var slug = g.slug || String(g.id);
          if (!summarySlugCount[slug]) summarySlugCount[slug] = 0;
          var occ = summarySlugCount[slug]++;
          giftMap[g.id] = { name: g.name, slug: slug, occurrence: occ, sources: [], note: '' };
        }
        giftMap[g.id].sources.push(src);
      });
    }
    if (sp) addGifts(sp.gifts, sp.name);
    if (ty) addGifts(ty.gifts, ty.name);
    if (ca) addGifts(ca.gifts, ca.name);
    giftMap['_personality'] = {
      name:    'Personality [' + (state.personalityWord || 'of choice') + ']',
      slug:    '_personality',
      occurrence: 0,
      sources: ['All characters'],
      note:    '',
    };

    // Annotate with chosen trait / ally info
    Object.values(giftMap).forEach(function(g) {
      var choiceKey = g.slug + '_' + g.occurrence;
      if (g.slug === 'improved-trait') {
        var choice = state.giftChoices[choiceKey] || '';
        g.note = choice ? 'Trait: ' + choice : '(no trait chosen)';
      } else if (g.slug === 'ally') {
        var allySp2 = state.allySpeciesId ? (d.species || []).find(function(x) { return x.id == state.allySpeciesId; }) : null;
        var allyCa2 = state.allyCareerId  ? (d.careers || []).find(function(x) { return x.id == state.allyCareerId;  }) : null;
        var allyParts = [];
        if (allySp2) allyParts.push('Species: ' + allySp2.name);
        if (allyCa2) allyParts.push('Career: '  + allyCa2.name);
        g.note = allyParts.length ? allyParts.join(' · ') : '(ally choices not set)';
      }
    });

    // Merge & deduplicate soaks
    var soakMap = {};
    if (ty && ty.soaks) {
      ty.soaks.forEach(function(s) {
        if (!soakMap[s.id]) soakMap[s.id] = { name: s.name, detail: s.damage_negated || '' };
      });
    }

    // Starting gear
    var gearSeen = {};
    var gearLines = [];
    function addGear(obj, src) {
      if (!obj || !obj.gear) return;
      obj.gear.split(/\n/).forEach(function(line) {
        line = line.trim();
        if (!line) return;
        var key = line.toLowerCase();
        if (!gearSeen[key]) { gearSeen[key] = true; gearLines.push({ from: src, text: line }); }
      });
    }
    addGear(ty, ty ? ty.name : '');
    addGear(ca, ca ? ca.name : '');

    var html = '';

    // Header
    html += '<div class="step-heading">Step 7 — Summary</div>';
    html += '<div class="summary-char-name">' + esc(state.charName || '(Unnamed)') + '</div>';
    var subtitle = [sp ? sp.name : '', ty ? ty.name : '', ca ? ca.name : ''].filter(Boolean).join(' · ');
    if (subtitle) html += '<div class="summary-subtitle">' + esc(subtitle) + '</div>';

    if (state.personalityWord) {
      html += '<div class="summary-personality">' +
        '<span class="summary-personality-label">Personality</span>' +
        '<span class="summary-personality-word">' + esc(state.personalityWord) + '</span>' +
      '</div>';
    }

    // ── Trait dice ───────────────────────────────────────────
    var traitRows = [
      { label: 'Body',  die: effectiveDie(state.bodyDie,  'Body')  },
      { label: 'Speed', die: effectiveDie(state.speedDie, 'Speed') },
      { label: 'Mind',  die: effectiveDie(state.mindDie,  'Mind')  },
      { label: 'Will',  die: effectiveDie(state.willDie,  'Will')  },
    ];
    html += '<div class="summary-traits">';
    traitRows.forEach(function(t) {
      var base     = state[t.label.toLowerCase() + 'Die'];
      var improved = t.die !== base;
      html += '<div class="summary-trait">' +
        '<div class="summary-trait-name">' + t.label + (improved ? ' <span style="color:var(--uj-teal);font-size:0.65rem;vertical-align:middle;" title="Improved via development">&#9650;</span>' : '') + '</div>' +
        '<div class="summary-trait-die" style="' + (improved ? 'color:var(--uj-teal);' : '') + '">' + (t.die || '—') + '</div>' +
      '</div>';
    });
    html += '</div>';

    // ── Source dice (Species / Type / Career die) ────────────
    var effSpeciesDie = effectiveDie(state.speciesDie, 'Species');
    var effTypeDie    = effectiveDie(state.typeDie,    'Type');
    var effCareerDie  = effectiveDie(state.careerDie,  'Career');
    html += '<div class="summary-source-dice">' +
      '<div class="summary-source-die-item">' +
        '<span class="source-die-label">Species Die</span>' +
        '<span class="summary-trait-die" style="font-size:1.1rem;' + (effSpeciesDie !== state.speciesDie ? 'color:var(--uj-teal);' : '') + '">' + (sp ? (effSpeciesDie || '—') : '—') + '</span>' +
        (sp ? '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(sp.name) + '</span>' : '') +
      '</div>' +
      '<div class="summary-source-die-item">' +
        '<span class="source-die-label">Type Die</span>' +
        '<span class="summary-trait-die" style="font-size:1.1rem;' + (effTypeDie !== state.typeDie ? 'color:var(--uj-teal);' : '') + '">' + (ty ? (effTypeDie || '—') : '—') + '</span>' +
        (ty ? '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(ty.name) + '</span>' : '') +
      '</div>' +
      '<div class="summary-source-die-item">' +
        '<span class="source-die-label">Career Die</span>' +
        '<span class="summary-trait-die" style="font-size:1.1rem;' + (effCareerDie !== state.careerDie ? 'color:var(--uj-teal);' : '') + '">' + (ca ? (effCareerDie || '—') : '—') + '</span>' +
        (ca ? '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(ca.name) + '</span>' : '') +
      '</div>' +
      (extraCa && effExtraCaDie ? (
        '<div class="summary-source-die-item" style="border-color:var(--uj-teal);">' +
          '<span class="source-die-label" style="color:var(--uj-teal);">Extra Career Die</span>' +
          '<span class="summary-trait-die" style="font-size:1.1rem;color:var(--uj-teal);">' + esc(effExtraCaDie) + '</span>' +
          '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(extraCa.name) + '</span>' +
        '</div>'
      ) : '') +
      (extraTy && effExtraTyDie ? (
        '<div class="summary-source-die-item" style="border-color:var(--uj-teal);">' +
          '<span class="source-die-label" style="color:var(--uj-teal);">Extra Type Die</span>' +
          '<span class="summary-trait-die" style="font-size:1.1rem;color:var(--uj-teal);">' + esc(effExtraTyDie) + '</span>' +
          '<span style="font-size:0.78rem;color:var(--uj-text-dim);">' + esc(extraTy.name) + '</span>' +
        '</div>'
      ) : '') +
    '</div>';

    // ── Complete Skills Table ────────────────────────────────
    html += '<div class="summary-section summary-skills-section">' +
      '<div class="summary-section-title">Skills</div>' +
      '<table class="skills-table">' +
        '<thead><tr>' +
          '<th class="skill-name-col">Skill</th>' +
          '<th class="skill-die-col" title="Species die">Species' + (sp ? '<br><span style="font-size:0.68rem;font-weight:400;">' + esc(sp.name) + '</span>' : '') + '</th>' +
          '<th class="skill-die-col" title="Type die">Type' + (ty ? '<br><span style="font-size:0.68rem;font-weight:400;">' + esc(ty.name) + '</span>' : '') + '</th>' +
          '<th class="skill-die-col" title="Career die">Career' + (ca ? '<br><span style="font-size:0.68rem;font-weight:400;">' + esc(ca.name) + '</span>' : '') + '</th>' +
          (extraCa ? '<th class="skill-die-col" title="Extra Career die" style="color:var(--uj-teal);">+Career<br><span style="font-size:0.68rem;font-weight:400;">' + esc(extraCa.name) + '</span></th>' : '') +
          (extraTy ? '<th class="skill-die-col" title="Extra Type die" style="color:var(--uj-teal);">+Type<br><span style="font-size:0.68rem;font-weight:400;">' + esc(extraTy.name) + '</span></th>' : '') +
          '<th class="skill-total-col">Dice Pool</th>' +
        '</tr></thead><tbody>';
    CORE_SKILLS.forEach(function(skillName) {
      var spDie    = itemGrantsSkill(sp,     skillName) ? effSpeciesDie               : '';
      var tyDie    = itemGrantsSkill(ty,     skillName) ? effTypeDie                  : '';
      var caDie    = itemGrantsSkill(ca,     skillName) ? effCareerDie                : '';
      var exCaDie  = itemGrantsSkill(extraCa,skillName) ? (effExtraCaDie || '')       : '';
      var exTyDie  = itemGrantsSkill(extraTy,skillName) ? (effExtraTyDie || '')       : '';
      var pool     = [spDie, tyDie, caDie, exCaDie, exTyDie].filter(Boolean);
      var hasAny   = pool.length > 0;
      var dieBadge = function(d) { return d ? '<span class="skill-die-badge">' + d + '</span>' : '<span class="skill-die-empty">—</span>'; };
      html += '<tr class="' + (hasAny ? 'skill-row-active' : 'skill-row-empty') + '">' +
        '<td class="skill-name-col">' + esc(skillName) + '</td>' +
        '<td class="skill-die-col">' + dieBadge(spDie)   + '</td>' +
        '<td class="skill-die-col">' + dieBadge(tyDie)   + '</td>' +
        '<td class="skill-die-col">' + dieBadge(caDie)   + '</td>' +
        (extraCa ? '<td class="skill-die-col">' + (exCaDie ? '<span class="skill-die-badge" style="color:var(--uj-teal);border-color:var(--uj-teal);">' + exCaDie + '</span>' : '<span class="skill-die-empty">—</span>') + '</td>' : '') +
        (extraTy ? '<td class="skill-die-col">' + (exTyDie ? '<span class="skill-die-badge" style="color:var(--uj-teal);border-color:var(--uj-teal);">' + exTyDie + '</span>' : '<span class="skill-die-empty">—</span>') + '</td>' : '') +
        '<td class="skill-total-col">' + (pool.length ? '<span style="color:var(--uj-teal);font-weight:600;">' + pool.join(' + ') + '</span>' : '<span class="skill-die-empty">—</span>') + '</td>' +
      '</tr>';
    });
    html += '</tbody></table></div>';

    // ── Battle Array ─────────────────────────────────────────
    var initDice  = [state.mindDie].concat(skillDiceFor('Observation').map(function(x) { return x.die; })).filter(Boolean);
    var dodgeDice = [state.speedDie].concat(skillDiceFor('Evasion').map(function(x) { return x.die; })).filter(Boolean);
    var rallyDice = [state.willDie].concat(skillDiceFor('Tactics').map(function(x) { return x.die; })).filter(Boolean);

    html += '<div class="summary-battle-array">' +
      '<div class="summary-section-title">Battle Array</div>' +
      '<div class="battle-array-grid">' +
        '<div class="battle-stat">' +
          '<div class="battle-stat-name">Initiative</div>' +
          '<div class="battle-stat-sub">Mind + Observation</div>' +
          '<div class="battle-stat-dice">' + initDice.join(' + ') + '</div>' +
        '</div>' +
        '<div class="battle-stat">' +
          '<div class="battle-stat-name">Dodge</div>' +
          '<div class="battle-stat-sub">Speed + Evasion</div>' +
          '<div class="battle-stat-dice">' + dodgeDice.join(' + ') + '</div>' +
        '</div>' +
        '<div class="battle-stat">' +
          '<div class="battle-stat-name">Rally</div>' +
          '<div class="battle-stat-sub">Will + Tactics</div>' +
          '<div class="battle-stat-dice">' + rallyDice.join(' + ') + '</div>' +
        '</div>' +
      '</div>' +
    '</div>';

    // ── Attacks ──────────────────────────────────────────────
    // Gift slugs that exactly match an attack category unlock those attacks.
    // Unarmed attacks are always available.
    var GIFT_ATTACK_CATS = ['boxing','brawling','contortionist','jumping','quills','running','sleight-of-hand','spray','wrestling'];
    var charGiftSlugs = {};
    [sp, ty, ca, extraCa, extraTy].forEach(function(entity) {
      if (!entity || !entity.gifts) return;
      entity.gifts.forEach(function(g) { charGiftSlugs[g.slug || String(g.id)] = true; });
    });
    state.purchasedGifts.forEach(function(p) { charGiftSlugs[p.slug] = true; });

    var charAccessible = { 'unarmed': true };
    GIFT_ATTACK_CATS.forEach(function(cat) { if (charGiftSlugs[cat]) charAccessible[cat] = true; });

    // Trait dice map for the character
    var charTraitMap = {
      'Body':  effectiveDie(state.bodyDie,  'Body'),
      'Speed': effectiveDie(state.speedDie, 'Speed'),
      'Mind':  effectiveDie(state.mindDie,  'Mind'),
      'Will':  effectiveDie(state.willDie,  'Will'),
    };

    // Parse an attack_dice string into an array of resolved dice
    function parseAttackPool(diceStr, traitMap, skillFn) {
      var str = diceStr.replace(/^[^:]+:\s*/, '');
      var pool = [];
      str.split(',').forEach(function(part) {
        part = part.trim();
        if (!part) return;
        if (/^d\d+$/i.test(part))           { pool.push(part.toLowerCase()); return; }
        if (/^ammo\s+d\d+$/i.test(part))    { pool.push(part.replace(/^ammo\s+/i, '').toLowerCase()); return; }
        if (traitMap[part])                  { pool.push(traitMap[part]); return; }
        var sd = skillFn(part);
        sd.forEach(function(x) { pool.push(x.die || x); });
      });
      return pool.filter(Boolean);
    }

    var allAttacks = d.attacks || [];
    var charAttacks = allAttacks.filter(function(a) { return charAccessible[a.category]; });
    if (charAttacks.length) {
      html += '<div class="summary-section">';
      html += '<div class="summary-section-title">Attacks</div>';
      html += '<table class="skills-table" style="font-size:0.82rem;">';
      html += '<thead><tr>' +
        '<th class="skill-name-col">Attack</th>' +
        '<th style="width:5rem;text-align:left;padding-left:0.4rem;">Range</th>' +
        '<th style="text-align:left;padding-left:0.4rem;">Dice Pool</th>' +
        '<th style="text-align:left;padding-left:0.4rem;">Effect</th>' +
      '</tr></thead><tbody>';
      var lastCat = '';
      charAttacks.forEach(function(a) {
        if (a.category !== lastCat) {
          lastCat = a.category;
          html += '<tr><td colspan="4" style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--uj-amber-light);padding:0.55rem 0.4rem 0.2rem;border-top:1px solid var(--uj-border-cool);">' +
            esc(lastCat.charAt(0).toUpperCase() + lastCat.slice(1).replace(/-/g,' ')) +
          '</td></tr>';
        }
        var pool = parseAttackPool(a.attack_dice, charTraitMap, skillDiceFor);
        html += '<tr class="skill-row-active">' +
          '<td class="skill-name-col" style="font-weight:500;">' + esc(a.name) + '</td>' +
          '<td style="font-size:0.78rem;color:var(--uj-text-dim);padding-left:0.4rem;">' + esc(a.attack_range) + '</td>' +
          '<td style="padding-left:0.4rem;">' + (pool.length ? '<span style="color:var(--uj-teal);font-weight:600;">' + pool.join(' + ') + '</span>' : '<span class="skill-die-empty">—</span>') + '</td>' +
          '<td style="font-size:0.78rem;color:var(--uj-text-dim);padding-left:0.4rem;">' + esc(a.effect) + '</td>' +
        '</tr>';
      });
      html += '</tbody></table></div>';
    }

    // ── Gifts / Soaks / Gear ─────────────────────────────────
    html += '<div class="summary-grid">';

    var allGiftsData = d.gifts || [];
    function findGiftBySlug(slug) {
      return allGiftsData.find(function(x) { return x.slug === slug; }) || null;
    }

    html += '<div class="summary-section"><div class="summary-section-title">Gifts</div><ul class="summary-list">';
    Object.values(giftMap).forEach(function(g) {
      var gData    = findGiftBySlug(g.slug);
      var subtitle = gData ? (gData.subtitle || '') : '';
      html += '<li class="gift-item">' + esc(g.name) +
        (subtitle ? '<span style="display:block;font-size:0.78rem;color:#4ade80;font-style:italic;margin-top:0.1rem;">' + esc(subtitle) + '</span>' : '') +
        '<small>' + esc((g.sources || []).join(', ')) + (g.note ? ' — ' + g.note : '') + '</small>' +
      '</li>';
    });
    html += '</ul></div>';

    if (state.purchasedGifts && state.purchasedGifts.length > 0) {
      var allSoaksData = d.soaks || [];
      html += '<div class="summary-section"><div class="summary-section-title" style="color:var(--uj-teal);">Developed Gifts &amp; Soaks</div><ul class="summary-list">';
      state.purchasedGifts.forEach(function(p) {
        var pData     = p.kind === 'Gift' ? findGiftBySlug(p.slug) : (allSoaksData.find(function(x) { return x.slug === p.slug; }) || null);
        var pSubtitle = pData ? (pData.subtitle || '') : '';
        html += '<li class="gift-item" style="border-left-color:var(--uj-teal);">' + esc(p.name) +
          (pSubtitle ? '<span style="display:block;font-size:0.78rem;color:#4ade80;font-style:italic;margin-top:0.1rem;">' + esc(pSubtitle) + '</span>' : '') +
          '<small style="color:var(--uj-teal);">Developed · ' + esc(p.xp_cost || 10) + ' XP</small>' +
        '</li>';
      });
      html += '</ul></div>';
    }

    html += '<div class="summary-section"><div class="summary-section-title">Soaks</div><ul class="summary-list">';
    var soaks = Object.values(soakMap);
    if (soaks.length) {
      soaks.forEach(function(s) {
        html += '<li class="soak-item">' + esc(s.name) + (s.detail ? '<small>' + esc(s.detail) + '</small>' : '') + '</li>';
      });
    } else {
      html += '<li style="color:var(--uj-text-dim);border-left-color:var(--uj-text-dim);">None granted</li>';
    }
    html += '</ul></div>';

    html += '<div class="summary-section"><div class="summary-section-title">Starting Gear</div><ul class="summary-list">';
    if (gearLines.length) {
      gearLines.forEach(function(g) {
        html += '<li style="border-left-color:#fbbf24;">' + esc(g.text) + '<small>' + esc(g.from) + '</small></li>';
      });
    } else {
      html += '<li style="color:var(--uj-text-dim);border-left-color:var(--uj-text-dim);">No starting gear</li>';
    }
    html += '</ul></div>';

    html += '</div>'; // end summary-grid

    if (state.notes) {
      html += '<div class="summary-section" style="margin-top:1.25rem;">' +
        '<div class="summary-section-title">Notes</div>' +
        '<p style="font-size:0.92rem;color:var(--uj-text-muted);white-space:pre-wrap;margin:0;">' + esc(state.notes) + '</p>' +
      '</div>';
    }

    // ── Ally Sheet ──────────────────────────────────────────────
    var hasAllyFromCreation = !!(giftMap['_ally'] || Object.values(giftMap).some(function(g) { return g.slug === 'ally'; }));
    var hasDevAlly = state.purchasedGifts && state.purchasedGifts.some(function(p) { return p.slug === 'ally'; });
    if ((hasAllyFromCreation || hasDevAlly) && (state.allySpeciesId || state.allyCareerId || state.allyName)) {
      var allySp = state.allySpeciesId ? (d.species || []).find(function(x) { return x.id == state.allySpeciesId; }) : null;
      var allyCa = state.allyCareerId  ? (d.careers || []).find(function(x) { return x.id == state.allyCareerId;  }) : null;

      html += '<div class="summary-ally-sheet" style="page-break-before:always;margin-top:2.5rem;border:1px solid var(--uj-border);border-radius:var(--uj-radius-lg);overflow:hidden;">';

      // Header
      html += '<div style="background:rgba(0,0,0,0.4);border-bottom:1px solid var(--uj-border);padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:flex-end;">' +
        '<div>' +
          '<div style="font-family:\'Cinzel\',serif;font-size:0.65rem;letter-spacing:0.15em;text-transform:uppercase;color:var(--uj-teal);margin-bottom:0.2rem;">Ally Character Sheet</div>' +
          '<div style="font-family:\'Cinzel\',serif;font-size:1.5rem;font-weight:700;color:var(--uj-amber-light);">' + esc(state.allyName || 'Unnamed Ally') + (state.allyGender ? '<span style="font-size:0.9rem;font-weight:400;color:var(--uj-text-dim);margin-left:0.6rem;">(' + esc(state.allyGender) + ')</span>' : '') + '</div>' +
          '<div style="font-size:0.8rem;color:var(--uj-text-dim);margin-top:0.15rem;">' +
            [allySp ? esc(allySp.name) : null, allyCa ? esc(allyCa.name) : null].filter(Boolean).join(' &mdash; ') +
          '</div>' +
        '</div>' +
        '<div style="font-size:0.7rem;color:var(--uj-text-dim);text-align:right;">Ally of ' + esc(state.charName || 'unknown') + '</div>' +
      '</div>';

      var aBodyDie  = state.allyBodyDie  || 'd6';
      var aSpeedDie = state.allySpeedDie || 'd6';
      var aMindDie  = state.allyMindDie  || 'd6';
      var aWillDie  = state.allyWillDie  || 'd6';
      var aSpDie    = allySp ? 'd6' : '';
      var aCaDie    = allyCa ? 'd6' : '';

      html += '<div style="padding:1.25rem 1.5rem;">';

      // Trait + Source dice
      function allyDiceBlock(label, die, improved) {
        return '<div style="text-align:center;background:rgba(0,0,0,0.25);border:1px solid var(--uj-border);border-radius:8px;padding:0.5rem 0.8rem;">' +
          '<div style="font-size:0.58rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--uj-text-dim);margin-bottom:0.15rem;">' + label + '</div>' +
          '<div style="font-family:\'Cinzel\',serif;font-size:1.2rem;color:' + (improved ? 'var(--uj-teal)' : 'var(--uj-amber)') + ';">' + esc(die || '—') + '</div>' +
        '</div>';
      }
      html += '<div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1.25rem;">' +
        allyDiceBlock('Body',  aBodyDie,  false) +
        allyDiceBlock('Speed', aSpeedDie, false) +
        allyDiceBlock('Mind',  aMindDie,  false) +
        allyDiceBlock('Will',  aWillDie,  false) +
        (allySp ? allyDiceBlock('Species · ' + esc(allySp.name), aSpDie, false) : '') +
        (allyCa ? allyDiceBlock('Career · '  + esc(allyCa.name), aCaDie, false) : '') +
      '</div>';

      // Battle Array
      function allyGrantsSkill(entity, skillName) {
        if (!entity || !entity.skills) return false;
        return entity.skills.some(function(sk) {
          return (sk.name || sk).toLowerCase() === skillName.toLowerCase();
        });
      }
      var aInitDice  = [aMindDie].concat(allyGrantsSkill(allySp, 'Observation') ? [aSpDie] : []).concat(allyGrantsSkill(allyCa, 'Observation') ? [aCaDie] : []).filter(Boolean);
      var aDodgeDice = [aSpeedDie].concat(allyGrantsSkill(allySp, 'Evasion') ? [aSpDie] : []).concat(allyGrantsSkill(allyCa, 'Evasion') ? [aCaDie] : []).filter(Boolean);
      var aRallyDice = [aWillDie].concat(allyGrantsSkill(allySp, 'Tactics') ? [aSpDie] : []).concat(allyGrantsSkill(allyCa, 'Tactics') ? [aCaDie] : []).filter(Boolean);
      html += '<div class="summary-battle-array" style="margin-bottom:1.25rem;">' +
        '<div class="summary-section-title" style="font-size:0.72rem;">Battle Array</div>' +
        '<div class="battle-array-grid">' +
          '<div class="battle-stat"><div class="battle-stat-name">Initiative</div><div class="battle-stat-sub">Mind + Observation</div><div class="battle-stat-dice">' + aInitDice.join(' + ') + '</div></div>' +
          '<div class="battle-stat"><div class="battle-stat-name">Dodge</div><div class="battle-stat-sub">Speed + Evasion</div><div class="battle-stat-dice">' + aDodgeDice.join(' + ') + '</div></div>' +
          '<div class="battle-stat"><div class="battle-stat-name">Rally</div><div class="battle-stat-sub">Will + Tactics</div><div class="battle-stat-dice">' + aRallyDice.join(' + ') + '</div></div>' +
        '</div>' +
      '</div>';

      // Ally Attacks
      var allyGiftSlugsForAtk = {};
      function addAllyGiftSlugs(entity) {
        if (!entity || !entity.gifts) return;
        entity.gifts.forEach(function(g) { allyGiftSlugsForAtk[g.slug || String(g.id)] = true; });
      }
      addAllyGiftSlugs(allySp);
      addAllyGiftSlugs(allyCa);

      var allyAccessible = { 'unarmed': true };
      GIFT_ATTACK_CATS.forEach(function(cat) { if (allyGiftSlugsForAtk[cat]) allyAccessible[cat] = true; });

      var allyTraitMap = { 'Body': aBodyDie, 'Speed': aSpeedDie, 'Mind': aMindDie, 'Will': aWillDie };
      function allySkillFn(skillName) {
        var dice = [];
        if (allyGrantsSkill(allySp, skillName) && aSpDie) dice.push({ die: aSpDie });
        if (allyGrantsSkill(allyCa, skillName) && aCaDie) dice.push({ die: aCaDie });
        return dice;
      }

      var allyAttackList = allAttacks.filter(function(a) { return allyAccessible[a.category]; });
      if (allyAttackList.length) {
        html += '<div class="summary-section summary-skills-section" style="margin-bottom:1.25rem;">';
        html += '<div class="summary-section-title" style="font-size:0.72rem;">Attacks</div>';
        html += '<table class="skills-table" style="font-size:0.82rem;"><thead><tr>' +
          '<th class="skill-name-col">Attack</th>' +
          '<th style="width:5rem;text-align:left;padding-left:0.4rem;">Range</th>' +
          '<th style="text-align:left;padding-left:0.4rem;">Dice Pool</th>' +
          '<th style="text-align:left;padding-left:0.4rem;">Effect</th>' +
        '</tr></thead><tbody>';
        var aLastCat = '';
        allyAttackList.forEach(function(a) {
          if (a.category !== aLastCat) {
            aLastCat = a.category;
            html += '<tr><td colspan="4" style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--uj-amber-light);padding:0.55rem 0.4rem 0.2rem;border-top:1px solid var(--uj-border-cool);">' +
              esc(aLastCat.charAt(0).toUpperCase() + aLastCat.slice(1).replace(/-/g,' ')) + '</td></tr>';
          }
          var aPool = parseAttackPool(a.attack_dice, allyTraitMap, allySkillFn);
          html += '<tr class="skill-row-active">' +
            '<td class="skill-name-col" style="font-weight:500;">' + esc(a.name) + '</td>' +
            '<td style="font-size:0.78rem;color:var(--uj-text-dim);padding-left:0.4rem;">' + esc(a.attack_range) + '</td>' +
            '<td style="padding-left:0.4rem;">' + (aPool.length ? '<span style="color:var(--uj-teal);font-weight:600;">' + aPool.join(' + ') + '</span>' : '<span class="skill-die-empty">—</span>') + '</td>' +
            '<td style="font-size:0.78rem;color:var(--uj-text-dim);padding-left:0.4rem;">' + esc(a.effect) + '</td>' +
          '</tr>';
        });
        html += '</tbody></table></div>';
      }

      // Full Skills Table
      html += '<div class="summary-section summary-skills-section" style="margin-bottom:1.25rem;">' +
        '<div class="summary-section-title" style="font-size:0.72rem;">Skills</div>' +
        '<table class="skills-table"><thead><tr>' +
          '<th class="skill-name-col">Skill</th>' +
          '<th class="skill-die-col">Species</th>' +
          '<th class="skill-die-col">Career</th>' +
          '<th class="skill-total-col">Pool</th>' +
        '</tr></thead><tbody>';
      CORE_SKILLS.forEach(function(skillName) {
        var spDie2 = (allySp && allyGrantsSkill(allySp, skillName)) ? aSpDie : '';
        var caDie2 = (allyCa && allyGrantsSkill(allyCa, skillName)) ? aCaDie : '';
        var pool2  = [spDie2, caDie2].filter(Boolean);
        var hasAny = pool2.length > 0;
        html += '<tr class="' + (hasAny ? 'skill-row-active' : 'skill-row-empty') + '">' +
          '<td class="skill-name-col">' + esc(skillName) + '</td>' +
          '<td class="skill-die-col">' + (spDie2 ? '<span class="skill-die-badge">' + spDie2 + '</span>' : '<span class="skill-die-empty">—</span>') + '</td>' +
          '<td class="skill-die-col">' + (caDie2 ? '<span class="skill-die-badge">' + caDie2 + '</span>' : '<span class="skill-die-empty">—</span>') + '</td>' +
          '<td class="skill-total-col">' + (pool2.length ? '<span style="color:var(--uj-teal);font-weight:600;">' + pool2.join(' + ') + '</span>' : '<span class="skill-die-empty">—</span>') + '</td>' +
        '</tr>';
      });
      html += '</tbody></table></div>';

      // Gifts
      var allyGifts = [];
      var allyGiftSeen = {};
      function addAllyGifts(entity) {
        if (!entity || !entity.gifts) return;
        entity.gifts.forEach(function(g) {
          var key = g.slug || String(g.id);
          if (!allyGiftSeen[key]) { allyGiftSeen[key] = true; allyGifts.push(g); }
        });
      }
      addAllyGifts(allySp);
      addAllyGifts(allyCa);
      if (allyGifts.length) {
        html += '<div style="margin-bottom:1.25rem;"><div class="summary-section-title" style="font-size:0.72rem;">Gifts</div>';
        html += '<ul class="summary-list">';
        allyGifts.forEach(function(g) {
          html += '<li class="gift-item">' + esc(g.name) +
            (g.subtitle ? '<span style="display:block;font-size:0.78rem;color:#4ade80;font-style:italic;margin-top:0.1rem;">' + esc(g.subtitle) + '</span>' : '') +
          '</li>';
        });
        html += '</ul></div>';
      }

      // Soaks
      var allySoaks = [];
      var allySoakSeen = {};
      function addAllySoaks(entity) {
        if (!entity || !entity.soaks) return;
        entity.soaks.forEach(function(s) {
          var key = s.slug || String(s.id);
          if (!allySoakSeen[key]) { allySoakSeen[key] = true; allySoaks.push(s); }
        });
      }
      addAllySoaks(allySp);
      addAllySoaks(allyCa);
      if (allySoaks.length) {
        html += '<div><div class="summary-section-title" style="font-size:0.72rem;">Soaks</div>';
        html += '<ul class="summary-list">';
        allySoaks.forEach(function(s) {
          html += '<li class="soak-item">' + esc(s.name) + (s.damage_negated ? '<small>' + esc(s.damage_negated) + '</small>' : '') + '</li>';
        });
        html += '</ul></div>';
      }

      html += '</div></div>'; // end ally-sheet inner + wrapper
    }

    return html;
  }

  /* ════════════════════════════════════════════════════════════
     SAVE
  ════════════════════════════════════════════════════════════ */
  function saveCharacter() {
    var statusEl = document.getElementById('wiz-save-status');
    if (statusEl) { statusEl.textContent = 'Saving…'; statusEl.className = 'save-status saving'; }

    var payload = {
      id:               state.currentChar || '',
      name:             state.charName,
      species_id:       state.speciesId  !== null ? state.speciesId  : '',
      type_id:          state.typeId     !== null ? state.typeId     : '',
      career_id:        state.careerId   !== null ? state.careerId   : '',
      body_die:         state.bodyDie,
      speed_die:        state.speedDie,
      mind_die:         state.mindDie,
      will_die:         state.willDie,
      species_die:      state.speciesDie,
      type_die:         state.typeDie,
      career_die:       state.careerDie,
      personality_word: state.personalityWord,
      notes:            state.notes,
      ally_species_id:  state.allySpeciesId !== null ? state.allySpeciesId : '',
      ally_career_id:   state.allyCareerId  !== null ? state.allyCareerId  : '',
      gift_choices:     JSON.stringify(state.giftChoices || {}),
      experience:       state.experience     || 0,
      purchased_gifts:  JSON.stringify(state.purchasedGifts || []),
    };

    ajaxPost('uj_save_character', { character: payload }).then(function(res) {
      if (res.success) {
        state.currentChar = String(res.data.id);
        if (statusEl) { statusEl.textContent = 'Saved'; statusEl.className = 'save-status saved'; }
        setTimeout(function() { if (statusEl) statusEl.textContent = ''; }, 3000);
        var titleEl = wizardScreen ? wizardScreen.querySelector('.wizard-title') : null;
        if (titleEl) titleEl.textContent = state.charName || 'New Character';
      } else {
        if (statusEl) { statusEl.textContent = 'Save failed'; statusEl.className = 'save-status error'; }
      }
    }).catch(function() {
      if (statusEl) { statusEl.textContent = 'Network error'; statusEl.className = 'save-status error'; }
    });
  }

})();
