<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'character_generator', 'cg_render_ui' );
function cg_render_ui() {
  if ( ! is_user_logged_in() ) {
    return '<p>Please <a href="' 
      . esc_url( wp_login_url() ) 
      . '">log in</a> to create or load a character.</p>';
  }

  ob_start();
  ?>

  <!-- Open builder button -->
  <button id="cg-open-builder" class="button">Open Character Builder</button>

  <!-- NEW / LOAD Splash (initially hidden) -->
  <div id="cg-modal-splash" class="cg-modal-splash cg-hidden">
    <div class="cg-modal-splash__content">
      <h2>Character Builder</h2>

      <!-- New Character -->
      <button id="cg-new-splash" class="button primary">
        New Character
      </button>

      <!-- Load Existing -->
      <div class="cg-splash-load">
        <label for="cg-splash-load-select">Or load existing:</label>
        <select id="cg-splash-load-select">
          <option value="">— Loading… —</option>
        </select>
        <button id="cg-load-splash" class="button">
          Load Character
        </button>
      </div>
    </div>
  </div>

  <!-- Single overlay for builder + unsaved prompt -->
  <div id="cg-modal-overlay" class="cg-hidden">
    <div id="cg-modal">
      <button id="cg-modal-close" class="cg-modal-close">✕</button>

      <!-- Where JS injects the form panels -->
      <div id="cg-form-container"></div>

      <!-- Unsaved‐changes prompt (initially hidden) -->
      <div id="cg-unsaved-confirm" class="cg-hidden">
        <div class="unsaved-dialog">
          <p>You have unsaved changes. What would you like to do?</p>
          <div class="unsaved-buttons">
            <button id="unsaved-save"   class="button primary">
              Save & Exit
            </button>
            <button id="unsaved-exit"   class="button secondary">
              Exit Without Saving
            </button>
            <button id="unsaved-cancel" class="button">
              Cancel
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php
  return ob_get_clean();
}
