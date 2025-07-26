<?php

namespace CharacterGeneratorDev {

/**
 * Customize API: WP_Customize_Nav_Menu_Locations_Control class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.9.0
 */

/**
 * Customize Nav Menu Locations Control Class.
 *
 * @since 4.9.0
 *
 * @see WP_Customize_Control
 */
class WP_Customize_Nav_Menu_Locations_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @since 4.9.0
	 * @var string
	 */
	public $type = 'nav_menu_locations';

	/**
	 * Don't render the control's content - it uses a JS template instead.
	 *
	 * @since 4.9.0
	 */
	public function render_content() {}

	/**
	 * JS/Underscore template for the control UI.
	 *
	 * @since 4.9.0
	 */
	public function content_template() {
		if ( current_theme_supports( 'menus' ) ) :
			?>
			<# var elementId; #>
			<ul class="menu-location-settings">
				<li class="customize-control assigned-menu-locations-title">
					<span class="customize-control-title">{{ wp.customize.Menus.data.l10n.locationsTitle }}</span>
					<# if ( data.isCreating ) { #>
						<p>
							printf(
								/* translators: 1: Documentation URL, 2: Additional link attributes, 3: Accessibility text. */
								_x( '(If you plan to use a menu <a href="%1$s" %2$s>widget%3$s</a>, skip this step.)', 'menu locations' ),
								__( 'https://wordpress.org/documentation/article/manage-wordpress-widgets/' ),
								' class="external-link" target="_blank"',
								sprintf(
									'<span class="screen-reader-text"> %s</span>',
									/* translators: Hidden accessibility text. */
									__( '(opens in a new tab)' )
								)
							);
							?>
						</p>
					<# } else { #>
					<# } #>
				</li>

					<# elementId = _.uniqueId( 'customize-nav-menu-control-location-' ); #>
					<li class="customize-control customize-control-checkbox assigned-menu-location">
						<span class="customize-inside-control-row">
							<label for="{{ elementId }}">
								<span class="theme-location-set">
									printf(
										/* translators: %s: Menu name. */
										_x( '(Current: %s)', 'menu location' ),
										'<span class="current-menu-location-name-' . esc_attr( $location ) . '"></span>'
									);
									?>
								</span>
							</label>
						</span>
					</li>
			</ul>
		endif;
	}
}

} // namespace CharacterGeneratorDev
