<?php

namespace CharacterGeneratorDev {

/* Template Name: Custom Register */
get_header();

// Redirect if logged in
if ( is_user_logged_in() ) {
  wp_redirect( home_url( '/character-generator/' ) );
  exit;
}
?>

<div class="cg-register-wrapper" style="max-width: 500px; margin: auto; padding: 2em;">
  <h2>Create a New Account</h2>

  <form id="cg-register-form">
    <label>Username</label>
    <input type="text" id="cg-register-username" required />

    <label>Email</label>
    <input type="email" id="cg-register-email" required />

    <label>Password</label>
    <input type="password" id="cg-register-password" required />

    <label>Confirm Password</label>
    <input type="password" id="cg-register-confirm" required />

    <button type="submit" id="cg-register-submit">Register</button>
    <div id="cg-register-message" style="margin-top: 1em;"></div>
  </form>

  <p style="margin-top: 2em; font-size: 0.9em;">
    Already have an account?
  </p>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cg-register-form');
    const messageBox = document.getElementById('cg-register-message');

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const username = document.getElementById('cg-register-username').value;
      const email    = document.getElementById('cg-register-email').value;
      const password = document.getElementById('cg-register-password').value;
      const confirm  = document.getElementById('cg-register-confirm').value;

      if (password !== confirm) {
        messageBox.textContent = 'Passwords do not match.';
        return;
      }

      messageBox.textContent = 'Registering...';

      fetch(CG_Auth.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'cg_register_user',
          security: CG_Auth.nonce,
          username: username,
          email: email,
          password: password
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          messageBox.textContent = 'Registration successful! Redirecting...';
          window.location.href = data.redirect || '/character-generator';
        } else {
          messageBox.textContent = data.data || 'Registration failed.';
        }
      })
      .catch(err => {
        messageBox.textContent = 'An error occurred. Please try again.';
      });
    });
  });
</script>


} // namespace CharacterGeneratorDev
