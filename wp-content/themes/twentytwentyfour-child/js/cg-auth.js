;(function($) {
  $(function() {

    // Login form handler
    const loginForm = $('#cg-login-form');
    if (loginForm.length) {
      loginForm.on('submit', function(e) {
        e.preventDefault();
        const username = $('#cg-login-username').val();
        const password = $('#cg-login-password').val();
        const message  = $('#cg-login-message');
        message.text('Logging in…');

        $.post(CG_Auth.ajax_url, {
          action:   'cg_login_user',
          security: CG_Auth.nonce,
          username,
          password
        }).done(res => {
          if (res.success) {
            message.text('Login successful! Redirecting…');
            window.location.href = res.data.redirect || '/character-generator';
          } else {
            message.text(res.data || 'Login failed.');
          }
        }).fail(() => {
          message.text('Network error. Please try again.');
        });
      });
    }

    // Registration form handler
    const registerForm = $('#cg-register-form');
    if (registerForm.length) {
      registerForm.on('submit', function(e) {
        e.preventDefault();
        const username = $('#cg-register-username').val();
        const email    = $('#cg-register-email').val();
        const pass1    = $('#cg-register-password').val();
        const pass2    = $('#cg-register-confirm').val();
        const message  = $('#cg-register-message');

        if (pass1 !== pass2) {
          message.text('Passwords do not match.');
          return;
        }

        message.text('Registering...');

        $.post(CG_Auth.ajax_url, {
          action:   'cg_register_user',
          security: CG_Auth.nonce,
          username,
          email,
          password: pass1
        }).done(res => {
          if (res.success) {
            message.text('Registration successful! Redirecting...');
            window.location.href = res.data.redirect || '/character-generator';
          } else {
            message.text(res.data || 'Registration failed.');
          }
        }).fail(() => {
          message.text('Network error. Please try again.');
        });
      });
    }

  });
})(jQuery);
