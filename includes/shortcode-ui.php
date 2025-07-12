<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'character_generator', 'cg_render_ui' );
function cg_render_ui() {
    if ( ! is_user_logged_in() ) {
        return '<p>Please <a href="' 
            . esc_url( wp_login_url() ) 
            . '">log in</a> to create or load a character.</p>';
    }

    ob_start(); ?>

    <!-- trigger to open modal -->
    <button id="cg-open-builder">Open Character Builder</button>

    <!-- pop-out modal -->
    <div class="cg-modal-overlay" style="display:none;">
      <div class="cg-modal">
        <button class="cg-modal-close">✕</button>
        <div id="cg-app">

          <!-- step controls -->
          <button id="cg-new">New Character</button>
          <button id="cg-load">Load Character</button>

          <!-- unsaved-changes prompt (hidden by default) -->
          <div id="cg-unsaved-confirm" class="cg-modal-overlay cg-hidden">
            <div class="cg-modal unsaved-dialog">
              <p>You have unsaved changes. What would you like to do?</p>
              <div class="unsaved-buttons">
                <button type="button" id="unsaved-save"   class="button primary">Save & Exit</button>
                <button type="button" id="unsaved-exit"   class="button secondary">Exit Without Saving</button>
                <button type="button" id="unsaved-cancel" class="button">Cancel</button>
              </div>
            </div>
          </div>
          <!-- /unsaved-changes prompt -->

          <!-- JS will build and inject the form here -->
          <div id="cg-form-container"></div>

        </div><!-- /#cg-app -->
      </div><!-- /.cg-modal -->
    </div><!-- /.cg-modal-overlay -->

    <?php
    return ob_get_clean();
}
