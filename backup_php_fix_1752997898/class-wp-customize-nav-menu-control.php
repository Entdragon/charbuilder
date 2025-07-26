<?php

namespace CharacterGeneratorDev {

/**
 * Customize API: WP_Customize_Nav_Menu_Control class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize Nav Menu Control Class.
 *
 * @since 4.3.0
 *
 * @see WP_Customize_Control
 */
class WP_Customize_Nav_Menu_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @since 4.3.0
	 * @var string
	 */
	public $type = 'nav_menu';

	/**
	 * Don't render the control's content - it uses a JS template instead.
	 *
	 * @since 4.3.0
	 */
	public function render_content() {}

	/**
	 * JS/Underscore template for the control UI.
	 *
	 * @since 4.3.0
	 */
	public function content_template() {
		$add_items = __( 'Add Items' );
		?>
		<p class="new-menu-item-invitation">
			printf(
				/* translators: %s: "Add Items" button text. */
				__( 'Time to add some links! Click &#8220;%s&#8221; to start putting pages, categories, and custom links in your menu. Add as many things as you would like.' ),
				$add_items
			);
			?>
		</p>
		<div class="customize-control-nav_menu-buttons">
			</button>
			</button>
		</div>
		<p class="screen-reader-text" id="reorder-items-desc-{{ data.menu_id }}">
			/* translators: Hidden accessibility text. */
			_e( 'When in reorder mode, additional controls to reorder menu items will be available in the items list above.' );
			?>
		</p>
	}

	/**
	 * Return parameters for this control.
	 *
	 * @since 4.3.0
	 *
	 * @return array Exported parameters.
	 */
	public function json() {
		$exported            = parent::json();
		$exported['menu_id'] = $this->setting->term_id;

		return $exported;
	}
}

} // namespace CharacterGeneratorDev
