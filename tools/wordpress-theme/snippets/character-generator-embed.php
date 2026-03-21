<?php
/**
 * Character Generator — WordPress Embed Snippet
 *
 * Place this file in your theme (or child theme) and add this line to functions.php:
 *   require_once get_stylesheet_directory() . '/snippets/character-generator-embed.php';
 *
 * Then add the shortcode [character_generator] to your /character-generator/ page.
 *
 * SSO: when a WordPress user is logged in, they are automatically logged into the
 * character generator without needing to enter their credentials again.
 *
 * ──────────────────────────────────────────────────────────────────────────────
 * CONFIGURATION
 * ──────────────────────────────────────────────────────────────────────────────
 */

if ( ! defined( 'CG_APP_URL' ) ) {
    define( 'CG_APP_URL', 'https://characters.libraryofcalbria.com' );
}

// ──────────────────────────────────────────────────────────────────────────────
// Shortcode: [character_generator]
// ──────────────────────────────────────────────────────────────────────────────

add_shortcode( 'character_generator', 'loc_character_generator_shortcode' );

function loc_character_generator_shortcode() {
    $app_url = rtrim( CG_APP_URL, '/' );

    // ── SSO token (one-time, 2 min TTL) ──────────────────────────────────────
    $sso_token = '';
    if ( is_user_logged_in() ) {
        $user_id   = get_current_user_id();
        $sso_token = bin2hex( random_bytes( 16 ) );
        set_transient( 'cg_sso_' . $sso_token, $user_id, 120 );
    }

    $sso_js = $sso_token
        ? "window.CG_SSO_TOKEN = " . json_encode( $sso_token ) . ";"
        : '';

    ob_start();
    ?>
    <!-- ── Character Generator: assets ─────────────────────────────────── -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap">
    <link rel="stylesheet" href="<?php echo esc_url( $app_url ); ?>/assets/css/dist/core.css">

    <style>
    /* ── Design tokens ─────────────────────────────────────────────────── */
    #cg-embed-root {
        --bg:          #1a1714;
        --surface:     #242019;
        --surface-2:   #2d2820;
        --gold:        #c9a84c;
        --gold-light:  #e5c97a;
        --gold-dark:   #a8822a;
        --gold-border: rgba(201,168,76,0.28);
        --gold-glow:   rgba(201,168,76,0.12);
        --text:        #e8dcc4;
        --text-muted:  #9a8a6a;
        --text-dim:    #5a4f3a;
        --error:       #d9534f;
        font-family:   'Crimson Pro', Georgia, serif;
        color:         var(--text);
    }

    /* ── Shared buttons ─────────────────────────────────────────────────── */
    #cg-embed-root .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 0.5rem; padding: 0.6rem 1.4rem;
        font-family: 'Cinzel', Georgia, serif; font-size: 0.82rem;
        font-weight: 600; letter-spacing: 0.06em; border-radius: 5px;
        cursor: pointer; transition: all 0.2s; text-transform: uppercase;
    }
    #cg-embed-root .btn-gold {
        background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 50%, var(--gold-dark) 100%);
        color: #1a1410; border: 1px solid var(--gold-dark);
        box-shadow: 0 2px 8px rgba(201,168,76,0.25);
    }
    #cg-embed-root .btn-gold:hover:not(:disabled) {
        background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%);
        box-shadow: 0 4px 16px rgba(201,168,76,0.35); transform: translateY(-1px);
    }
    #cg-embed-root .btn-gold:disabled { opacity: 0.5; cursor: not-allowed; }
    #cg-embed-root .btn-ghost {
        background: transparent; color: var(--text-muted);
        border: 1px solid rgba(201,168,76,0.2);
        font-family: 'Cinzel', Georgia, serif; font-size: 0.75rem;
        letter-spacing: 0.05em; padding: 0.4rem 0.9rem; border-radius: 4px;
        cursor: pointer; text-transform: uppercase; transition: all 0.2s;
    }
    #cg-embed-root .btn-ghost:hover { color: var(--gold-light); border-color: var(--gold-border); }

    /* ── Button aliases used by bundle ──────────────────────────────────── */
    #cg-embed-root .button,
    #cg-embed-root .button.primary,
    #cg-embed-root .button.secondary {
        display: inline-flex; align-items: center; justify-content: center;
        font-family: 'Cinzel', Georgia, serif; font-size: 0.78rem;
        font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
        border-radius: 5px; padding: 0.55rem 1.2rem; cursor: pointer;
        transition: all 0.18s; border: 1px solid var(--gold-border);
        background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 50%, var(--gold-dark) 100%);
        color: #1a1410; box-shadow: 0 2px 6px rgba(201,168,76,0.2);
    }
    #cg-embed-root .button:hover,
    #cg-embed-root .button.primary:hover {
        background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%);
        box-shadow: 0 4px 12px rgba(201,168,76,0.3); transform: translateY(-1px);
    }
    #cg-embed-root .button.secondary {
        background: transparent; color: var(--gold);
        border-color: var(--gold-border); box-shadow: none;
    }
    #cg-embed-root .button.secondary:hover {
        background: rgba(201,168,76,0.08); color: var(--gold-light); border-color: var(--gold);
    }

    /* ── Auth screen ────────────────────────────────────────────────────── */
    #cg-embed-root #cg-auth-screen {
        display: flex; align-items: center; justify-content: center; padding: 3rem 1rem;
    }
    #cg-embed-root .auth-card {
        background: var(--surface); border: 1px solid var(--gold-border);
        border-radius: 12px; padding: 0 2rem 2.5rem; width: 100%; max-width: 400px;
        box-shadow: 0 24px 64px rgba(0,0,0,0.5), 0 0 0 1px rgba(201,168,76,0.1); overflow: hidden;
    }
    #cg-embed-root .auth-card-accent {
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--gold), transparent);
        margin: 0 -2rem 2.5rem;
    }
    #cg-embed-root .auth-emblem {
        text-align: center; font-family: 'Cinzel', Georgia, serif;
        font-size: 1.5rem; color: var(--gold); letter-spacing: 0.12em;
        margin-bottom: 0.35rem; line-height: 1;
    }
    #cg-embed-root .auth-card h1 {
        margin: 0 0 0.2rem; font-family: 'Cinzel', Georgia, serif;
        font-size: 1.5rem; font-weight: 700; color: var(--gold-light);
        text-align: center; letter-spacing: 0.06em;
    }
    #cg-embed-root .auth-card .subtitle {
        text-align: center; color: var(--text-muted); font-size: 0.9rem;
        font-family: 'Cinzel', Georgia, serif; letter-spacing: 0.1em;
        text-transform: uppercase; margin-bottom: 2rem;
    }
    #cg-embed-root .auth-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--gold-border), transparent);
        margin: 0 -2rem 1.75rem;
    }
    #cg-embed-root .auth-tabs {
        display: flex; border-bottom: 1px solid rgba(201,168,76,0.15); margin-bottom: 1.5rem;
    }
    #cg-embed-root .auth-tab {
        flex: 1; background: none; border: none; border-bottom: 2px solid transparent;
        margin-bottom: -1px; color: var(--text-dim); padding: 0.5rem;
        font-family: 'Cinzel', Georgia, serif; font-size: 0.8rem; font-weight: 600;
        letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; transition: all 0.2s;
    }
    #cg-embed-root .auth-tab.active { color: var(--gold); border-bottom-color: var(--gold); }
    #cg-embed-root .auth-tab:hover:not(.active) { color: var(--text-muted); }
    #cg-embed-root .auth-form { display: none; flex-direction: column; gap: 1rem; }
    #cg-embed-root .auth-form.active { display: flex; }
    #cg-embed-root .auth-field label {
        font-family: 'Cinzel', Georgia, serif; font-size: 0.72rem; font-weight: 600;
        letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted);
        display: block; margin-bottom: 0.4rem;
    }
    #cg-embed-root .auth-field input {
        width: 100%; padding: 0.65rem 0.9rem; background: rgba(10,8,5,0.5);
        border: 1px solid rgba(201,168,76,0.18); border-radius: 5px; color: var(--text);
        font-family: 'Crimson Pro', Georgia, serif; font-size: 1rem; outline: none;
        transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box;
    }
    #cg-embed-root .auth-field input:focus {
        border-color: var(--gold); box-shadow: 0 0 0 2px rgba(201,168,76,0.12);
    }
    #cg-embed-root .auth-field input::placeholder { color: var(--text-dim); }
    #cg-embed-root .auth-submit { width: 100%; padding: 0.8rem; margin-top: 0.25rem; }
    #cg-embed-root .auth-error {
        color: var(--error); font-size: 0.9rem; text-align: center;
        min-height: 1.2em; font-style: italic;
    }

    /* ── App topbar ─────────────────────────────────────────────────────── */
    #cg-embed-root .app-topbar {
        display: flex; align-items: center; justify-content: flex-end;
        padding: 0.5rem 1.5rem; background: var(--surface);
        border-bottom: 1px solid var(--gold-border); gap: 1rem;
    }
    #cg-embed-root .app-topbar__username { font-size: 0.9rem; color: var(--text-muted); }
    #cg-embed-root .app-topbar__username strong { color: var(--text); font-weight: 600; }

    /* ── App hero ───────────────────────────────────────────────────────── */
    #cg-embed-root .app-main {
        flex: 1; display: flex; align-items: center; justify-content: center;
        padding: 3rem 2rem;
    }
    #cg-embed-root .app-hero { text-align: center; max-width: 480px; }
    #cg-embed-root .app-hero__ornament {
        font-size: 2rem; color: var(--gold); display: block; margin-bottom: 1rem; opacity: 0.7;
    }
    #cg-embed-root .app-hero__title {
        font-family: 'Cinzel', Georgia, serif; font-size: 2rem; font-weight: 700;
        color: var(--gold-light); letter-spacing: 0.05em; margin: 0 0 0.4rem; line-height: 1.2;
    }
    #cg-embed-root .app-hero__subtitle {
        font-family: 'Cinzel', Georgia, serif; font-size: 0.8rem; color: var(--text-muted);
        letter-spacing: 0.15em; text-transform: uppercase; margin: 0 0 2rem;
    }
    #cg-embed-root .app-hero__rule {
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--gold-border), transparent);
        margin: 0 auto 2.5rem; width: 80%;
    }
    #cg-embed-root .app-hero__cta { padding: 0.9rem 2.5rem; font-size: 0.9rem; }
    #cg-embed-root #cg-open-builder-trigger { display: none; }

    /* ── Unsaved dialog ─────────────────────────────────────────────────── */
    #cg-embed-root .unsaved-dialog { font-family: 'Crimson Pro', Georgia, serif; }
    #cg-embed-root .unsaved-buttons {
        display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;
    }
    </style>

    <!-- ── Character Generator: HTML ────────────────────────────────────── -->
    <div id="cg-embed-root">

      <!-- Auth Screen -->
      <div id="cg-auth-screen" style="display:flex">
        <div class="auth-card">
          <div class="auth-card-accent"></div>
          <div class="auth-emblem">◆</div>
          <h1>Library of Calabria</h1>
          <p class="subtitle">Character Generator</p>
          <div class="auth-divider"></div>
          <div class="auth-tabs">
            <button class="auth-tab active" data-tab="login">Sign In</button>
            <button class="auth-tab" data-tab="register">Register</button>
          </div>
          <form class="auth-form active" id="login-form" onsubmit="return false;">
            <div class="auth-field">
              <label for="login-username">Username or Email</label>
              <input type="text" id="login-username" name="username" autocomplete="username" placeholder="Enter your username">
            </div>
            <div class="auth-field">
              <label for="login-password">Password</label>
              <input type="password" id="login-password" name="password" autocomplete="current-password" placeholder="Enter your password">
            </div>
            <div class="auth-error" id="login-error"></div>
            <button type="button" id="login-btn" class="btn btn-gold auth-submit">Sign In</button>
          </form>
          <form class="auth-form" id="register-form" onsubmit="return false;">
            <div class="auth-field">
              <label for="reg-username">Username</label>
              <input type="text" id="reg-username" name="username" autocomplete="username" placeholder="Choose a username">
            </div>
            <div class="auth-field">
              <label for="reg-email">Email</label>
              <input type="email" id="reg-email" name="email" autocomplete="email" placeholder="your@email.com">
            </div>
            <div class="auth-field">
              <label for="reg-password">Password</label>
              <input type="password" id="reg-password" name="password" autocomplete="new-password" placeholder="Choose a password">
            </div>
            <div class="auth-error" id="register-error"></div>
            <button type="button" id="register-btn" class="btn btn-gold auth-submit">Create Account</button>
          </form>
        </div>
      </div><!-- /#cg-auth-screen -->

      <!-- App Screen -->
      <div id="cg-app-screen" style="display:none; flex-direction:column; flex:1">
        <div class="app-topbar">
          <span class="app-topbar__username">Signed in as <strong id="cg-username-display"></strong></span>
          <button class="btn-ghost" id="cg-logout-btn">Sign Out</button>
        </div>
        <main class="app-main">
          <div class="app-hero">
            <span class="app-hero__ornament">✦ ◆ ✦</span>
            <h2 class="app-hero__title">Your Characters</h2>
            <p class="app-hero__subtitle">Library of Calabria &mdash; Character Records</p>
            <div class="app-hero__rule"></div>
            <button id="cg-open-builder" class="btn btn-gold app-hero__cta">✦ &nbsp; Open Character Builder</button>
          </div>
        </main>
        <div id="cg-modal-splash" class="cg-modal-splash cg-hidden">
          <div class="cg-modal-splash__content">
            <h2>Character Builder</h2>
            <p style="color:var(--text-muted);font-family:'Crimson Pro',Georgia,serif;font-size:0.95rem;margin:0 0 1.5rem">Begin a new tale or continue an existing one.</p>
            <button id="cg-new-splash" class="button primary">✦ &nbsp; New Character</button>
            <div class="cg-splash-load">
              <label for="cg-splash-load-select">Load an existing character</label>
              <select id="cg-splash-load-select"><option value="">— Loading… —</option></select>
              <button id="cg-load-splash" class="button">Load Character</button>
            </div>
          </div>
        </div>
        <div id="cg-modal-overlay" class="cg-hidden">
          <div id="cg-modal">
            <button id="cg-modal-close" class="cg-modal-close">✕</button>
            <div id="cg-form-container"></div>
            <div id="cg-unsaved-confirm" class="cg-hidden">
              <div class="unsaved-dialog">
                <p>You have unsaved changes. What would you like to do?</p>
                <div class="unsaved-buttons">
                  <button id="unsaved-save"   class="button primary">Save &amp; Exit</button>
                  <button id="unsaved-exit"   class="button secondary">Exit Without Saving</button>
                  <button id="unsaved-cancel" class="button">Cancel</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /#cg-app-screen -->

    </div><!-- /#cg-embed-root -->

    <!-- ── Character Generator: config + auth ───────────────────────────── -->
    <script>
    (function () {
        var appUrl = <?php echo json_encode( $app_url ); ?>;
        <?php echo $sso_js; ?>

        window.CG_API_BASE = appUrl;
        window.CG_EMBED    = true;
        window.CG_AJAX = {
            ajax_url:    appUrl + '/api/ajax',
            ajaxurl:     appUrl + '/api/ajax',
            nonce:       '1',
            security:    '1',
            _ajax_nonce: '1',
        };
        window.CG_NONCES = new Proxy({}, { get: function() { return '1'; } });

        // Use WordPress's jQuery if available
        if (typeof jQuery !== 'undefined' && !window.jQuery) {
            window.jQuery = jQuery;
        }

        var authScreen = document.getElementById('cg-auth-screen');
        var appScreen  = document.getElementById('cg-app-screen');

        function cgAjax(action, data) {
            var params = Object.assign({ action: action, nonce: '1', security: '1' }, data || {});
            var body   = new URLSearchParams(params);
            var ctrl   = new AbortController();
            var timer  = setTimeout(function() { ctrl.abort(); }, 10000);
            return fetch(appUrl + '/api/ajax', {
                method:      'POST',
                body:        body,
                signal:      ctrl.signal,
                credentials: 'include',
            }).then(function(r) {
                clearTimeout(timer);
                return r.json();
            }).catch(function(e) {
                clearTimeout(timer);
                return { success: false, data: e.name === 'AbortError' ? 'Request timed out.' : 'Network error.' };
            });
        }

        function showAuth() {
            authScreen.style.display = 'flex';
            appScreen.style.display  = 'none';
        }

        function showApp(username) {
            authScreen.style.display = 'none';
            appScreen.style.display  = 'flex';
            document.getElementById('cg-username-display').textContent = username;
            loadBundle();
        }

        function loadBundle() {
            if (window.__CG_BUNDLE_LOADED__) return;
            window.__CG_BUNDLE_LOADED__ = true;
            var s = document.createElement('script');
            s.src = appUrl + '/assets/js/dist/core.bundle.js';
            document.body.appendChild(s);
        }

        function init() {
            var ctrl  = new AbortController();
            var timer = setTimeout(function() { ctrl.abort(); }, 4000);
            fetch(appUrl + '/api/auth/me', { signal: ctrl.signal, credentials: 'include' })
                .then(function(r) { clearTimeout(timer); return r.json(); })
                .then(function(d) {
                    if (d.success) { showApp(d.data.username); return; }
                    // Try SSO auto-login if WordPress user is logged in
                    if (window.CG_SSO_TOKEN) {
                        return cgAjax('cg_sso_login', { token: window.CG_SSO_TOKEN })
                            .then(function(sso) {
                                if (sso.success) showApp(sso.data.username);
                            });
                    }
                })
                .catch(function() {});
        }

        // Tab switching
        document.querySelectorAll('.auth-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.auth-tab').forEach(function(t) { t.classList.remove('active'); });
                document.querySelectorAll('.auth-form').forEach(function(f) { f.classList.remove('active'); });
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab + '-form').classList.add('active');
            });
        });

        function doLogin() {
            var btn  = document.getElementById('login-btn');
            var err  = document.getElementById('login-error');
            var user = document.getElementById('login-username').value.trim();
            var pass = document.getElementById('login-password').value;
            if (!user || !pass) { err.textContent = 'Please enter your username and password.'; return; }
            btn.disabled = true;
            err.textContent = 'Signing in\u2026';
            cgAjax('cg_login_user', { username: user, password: pass }).then(function(res) {
                btn.disabled = false;
                if (res.success) { err.textContent = ''; showApp(user); }
                else { err.textContent = res.data || 'Login failed.'; }
            });
        }

        function doRegister() {
            var btn   = document.getElementById('register-btn');
            var err   = document.getElementById('register-error');
            var user  = document.getElementById('reg-username').value.trim();
            var email = document.getElementById('reg-email').value.trim();
            var pass  = document.getElementById('reg-password').value;
            if (!user || !email || !pass) { err.textContent = 'All fields are required.'; return; }
            btn.disabled = true;
            err.textContent = 'Creating account\u2026';
            cgAjax('cg_register_user', { username: user, email: email, password: pass }).then(function(res) {
                btn.disabled = false;
                if (res.success) { err.textContent = ''; showApp(user); }
                else { err.textContent = res.data || 'Registration failed.'; }
            });
        }

        document.getElementById('login-btn').addEventListener('click', doLogin);
        document.getElementById('login-username').addEventListener('keydown', function(e) { if (e.key === 'Enter') doLogin(); });
        document.getElementById('login-password').addEventListener('keydown', function(e) { if (e.key === 'Enter') doLogin(); });
        document.getElementById('register-btn').addEventListener('click', doRegister);
        document.getElementById('cg-logout-btn').addEventListener('click', function() {
            cgAjax('cg_logout_user', {}).then(function() { showAuth(); });
        });

        // jQuery shim (bundle requires window.jQuery)
        if (typeof window.jQuery === 'undefined' && typeof jQuery !== 'undefined') {
            window.jQuery = jQuery;
        }
        if (typeof window.jQuery === 'undefined') {
            var jq = document.createElement('script');
            jq.src = appUrl + '/vendor/jquery/dist/jquery.js';
            jq.onload = function() { init(); };
            document.head.appendChild(jq);
        } else {
            init();
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}
