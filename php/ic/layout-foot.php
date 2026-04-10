
    </main>
  </div><!-- #ic-body -->
</div><!-- #ic-wrapper -->

<?php if (cg_is_logged_in()): ?>
<div id="ic-cpw-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#1a1714;border:1px solid rgba(201,168,76,0.3);border-radius:10px;padding:2rem;width:100%;max-width:380px;box-shadow:0 8px 40px rgba(0,0,0,0.7);">
    <h3 style="font-family:'Cinzel',Georgia,serif;color:#e5c97a;margin:0 0 1.25rem;font-size:1rem;letter-spacing:0.06em;text-transform:uppercase;">Change Password</h3>
    <div style="display:flex;flex-direction:column;gap:0.75rem;">
      <input type="text" autocomplete="username" aria-hidden="true" tabindex="-1"
        style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;">
      <input type="password" id="ic-cpw-current" placeholder="Current password" autocomplete="current-password"
        style="width:100%;background:#242019;border:1px solid rgba(154,138,106,0.3);border-radius:5px;color:#e8dcc4;font-family:'Crimson Pro',Georgia,serif;font-size:1rem;padding:0.5rem 0.75rem;outline:none;box-sizing:border-box;">
      <input type="password" id="ic-cpw-new" placeholder="New password (min 8 chars)" autocomplete="new-password"
        style="width:100%;background:#242019;border:1px solid rgba(154,138,106,0.3);border-radius:5px;color:#e8dcc4;font-family:'Crimson Pro',Georgia,serif;font-size:1rem;padding:0.5rem 0.75rem;outline:none;box-sizing:border-box;">
      <input type="password" id="ic-cpw-confirm" placeholder="Confirm new password" autocomplete="new-password"
        style="width:100%;background:#242019;border:1px solid rgba(154,138,106,0.3);border-radius:5px;color:#e8dcc4;font-family:'Crimson Pro',Georgia,serif;font-size:1rem;padding:0.5rem 0.75rem;outline:none;box-sizing:border-box;">
      <div id="ic-cpw-error" style="color:#d9534f;font-size:0.82rem;min-height:1.2em;"></div>
      <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:0.25rem;">
        <button id="ic-cpw-cancel" style="background:transparent;border:1px solid rgba(154,138,106,0.3);border-radius:5px;color:#9a8a6a;font-family:'Cinzel',Georgia,serif;font-size:0.75rem;padding:0.45rem 1rem;cursor:pointer;">Cancel</button>
        <button id="ic-cpw-submit" style="background:#c9a84c;border:none;border-radius:5px;color:#1a1714;font-family:'Cinzel',Georgia,serif;font-size:0.75rem;font-weight:700;padding:0.45rem 1rem;cursor:pointer;">Update Password</button>
      </div>
    </div>
  </div>
</div>
<script>
(function() {
  var overlay = document.getElementById('ic-cpw-overlay');
  var openBtn = document.getElementById('ic-change-pw-btn');
  var cancelBtn = document.getElementById('ic-cpw-cancel');
  var submitBtn = document.getElementById('ic-cpw-submit');
  var errEl = document.getElementById('ic-cpw-error');

  function openModal() {
    document.getElementById('ic-cpw-current').value = '';
    document.getElementById('ic-cpw-new').value = '';
    document.getElementById('ic-cpw-confirm').value = '';
    errEl.textContent = '';
    overlay.style.display = 'flex';
  }
  function closeModal() { overlay.style.display = 'none'; }

  if (openBtn)   openBtn.addEventListener('click', openModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', function(e) { if (e.target === overlay) closeModal(); });

  submitBtn.addEventListener('click', function() {
    var cur = document.getElementById('ic-cpw-current').value;
    var nw  = document.getElementById('ic-cpw-new').value;
    var con = document.getElementById('ic-cpw-confirm').value;
    errEl.textContent = '';
    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating…';

    var fd = new FormData();
    fd.append('action', 'cg_change_password');
    fd.append('current_password', cur);
    fd.append('new_password', nw);
    fd.append('confirm_password', con);

    fetch('/ajax.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Password';
        if (res.success) {
          closeModal();
          alert('Password updated successfully.');
        } else {
          errEl.textContent = res.data || 'Something went wrong.';
        }
      })
      .catch(function() {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Password';
        errEl.textContent = 'Request failed — please try again.';
      });
  });
}());
</script>
<?php endif; ?>

<script>
(function () {
  const searchInput = document.getElementById('ic-live-search');
  if (!searchInput) return;
  const target = searchInput.dataset.target || '.card';
  searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll(target).forEach(el => {
      const text = (el.dataset.name || el.textContent).toLowerCase();
      el.classList.toggle('hidden', q.length > 0 && !text.includes(q));
    });
  });
}());
</script>
</body>
</html>
