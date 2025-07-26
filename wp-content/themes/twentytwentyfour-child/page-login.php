<?php

namespace CharacterGeneratorDev {

/* Template Name: Custom Login */
get_header();

// Redirect logged-in users
if ( is_user_logged_in() ) {
  wp_redirect( home_url( '/character-generator/' ) );
  exit;
}
?>

<div class="cg-login-wrapper" style="max-width: 500px; margin: auto; padding: 2em;">
  <h2>Log in to access the character generator</h2>

  <form id="cg-login-form">
    <label>Username or Email</label>
    <input type="text" id="cg-login-username" required />

    <label>Password</label>
    <input type="password" id="cg-login-password" required />

    <button type="submit" id="cg-login-submit">Log In</button>
    <div id="cg-login-message" style="margin-top: 1em;"></div>
  </form>

  <p style="margin-top: 2em; font-size: 0.9em;">
    Don’t have an account?
  </p>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cg-login-form');
    const messageBox = document.getElementById('cg-login-message');

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      messageBox.textContent = 'Logging in…';

      fetch(CG_Auth.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'cg_login_user',
          security: CG_Auth.nonce,
          username: document.getElementById('cg-login-username').value,
          password: document.getElementById('cg-login-password').value
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          messageBox.textContent = 'Login successful! Redirecting…';
          window.location.href = data.redirect || '/character-generator';
        } else {
          messageBox.textContent = data.data || 'Login failed. Please try again.';
        }
      })
      .catch(err => {
        messageBox.textContent = 'An error occurred. Please try again.';
      });
    });
  });
</script>


} // namespace CharacterGeneratorDev
