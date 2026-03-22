>
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
            // Load builder CSS lazily so it doesn't bleed into other WP pages
            var link = document.createElement('link');
            link.rel  = 'stylesheet';
            link.href = appUrl + '/assets/css/dist/core.css';
            document.head.appendChild(link);
            var s = document.createElement('script');
            s.src = appUrl + '/assets/js/dist/core.bundle.js';
            document.body.appendChild(s);
        }

        function init() {
            // If WordPress gave us an SSO token, use it first so the WordPress
            // identity always wins over any stale character-generator session.
            if (window.CG_SSO_TOKEN) {
                cgAjax('cg_sso_login', { token: window.CG_SSO_TOKEN })
                    .then(function(sso) {
                        if (sso.success) { showApp(sso.data.username); return; }
                        // SSO failed — fall back to existing session check
                        checkExistingSession();
                    });
            } else {
                checkExistingSession();
            }
        }

        function checkExistingSession() {
            var ctrl  = new AbortController();
            var timer = setTimeout(function() { ctrl.abort(); }, 4000);
            fetch(appUrl + '/api/auth/me', { signal: ctrl.signal, credentials: 'include' })
                .then(function(r) { clearTimeout(timer); return r.json(); })
                .then(function(d) { if (d.success) showApp(d.data.username); })
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

        // Load jQuery if WordPress hasn't provided it, then run init
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
