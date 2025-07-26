<?php

namespace CharacterGeneratorDev {

/**
 * Navigation Menu API: Walker_Nav_Menu_Edit class
 *
 * @package WordPress
 * @subpackage Administration
 * @since 4.4.0
 */

/**
 * Create HTML list of nav menu input items.
 *
 * @since 3.0.0
 *
 * @see Walker_Nav_Menu
 */
class Walker_Nav_Menu_Edit extends Walker_Nav_Menu {
	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker_Nav_Menu::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string   $output Passed by reference.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   Not used.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker_Nav_Menu::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string   $output Passed by reference.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   Not used.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Start the element output.
	 *
	 * @see Walker_Nav_Menu::start_el()
	 * @since 3.0.0
	 * @since 5.9.0 Renamed `$item` to `$data_object` and `$id` to `$current_object_id`
	 *              to match parent class for PHP 8 named parameter support.
	 *
	 * @global int $_wp_nav_menu_max_depth
	 *
	 * @param string   $output            Used to append additional content (passed by reference).
	 * @param WP_Post  $data_object       Menu item data object.
	 * @param int      $depth             Depth of menu item. Used for padding.
	 * @param stdClass $args              Not used.
	 * @param int      $current_object_id Optional. ID of the current menu item. Default 0.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		global $_wp_nav_menu_max_depth;

		// Restores the more descriptive, specific name for use within this method.
		$menu_item = $data_object;

		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		ob_start();
		$item_id      = esc_attr( $menu_item->ID );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = false;

		if ( 'taxonomy' === $menu_item->type ) {
			$original_object = get_term( (int) $menu_item->object_id, $menu_item->object );
			if ( $original_object && ! is_wp_error( $original_object ) ) {
				$original_title = $original_object->name;
			}
		} elseif ( 'post_type' === $menu_item->type ) {
			$original_object = get_post( $menu_item->object_id );
			if ( $original_object ) {
				$original_title = get_the_title( $original_object->ID );
			}
		} elseif ( 'post_type_archive' === $menu_item->type ) {
			$original_object = get_post_type_object( $menu_item->object );
			if ( $original_object ) {
				$original_title = $original_object->labels->archives;
			}
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $menu_item->object ),
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id === $_GET['edit-menu-item'] ) ? 'active' : 'inactive' ),
		);

		$title = $menu_item->title;

		if ( ! empty( $menu_item->_invalid ) ) {
			$classes[] = 'menu-item-invalid';
			/* translators: %s: Title of an invalid menu item. */
			$title = sprintf( __( '%s (Invalid)' ), $menu_item->title );
		} elseif ( isset( $menu_item->post_status ) && 'draft' === $menu_item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: Title of a menu item in draft status. */
			$title = sprintf( __( '%s (Pending)' ), $menu_item->title );
		}

		$title = ( ! isset( $menu_item->label ) || '' === $menu_item->label ) ? $title : $menu_item->label;

		$submenu_text = '';
		if ( 0 === $depth ) {
			$submenu_text = 'style="display: none;"';
		}

		?>
			<div class="menu-item-bar">
				<div class="menu-item-handle">
					</label>
					<span class="item-controls">
						<span class="item-order hide-if-js">
							printf(
								'<a href="%s" class="item-move-up" aria-label="%s">&#8593;</a>',
								wp_nonce_url(
									add_query_arg(
										array(
											'action'    => 'move-up-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								),
								esc_attr__( 'Move up' )
							);
							?>
							|
							printf(
								'<a href="%s" class="item-move-down" aria-label="%s">&#8595;</a>',
								wp_nonce_url(
									add_query_arg(
										array(
											'action'    => 'move-down-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								),
								esc_attr__( 'Move down' )
							);
							?>
						</span>
						if ( isset( $_GET['edit-menu-item'] ) && $item_id === $_GET['edit-menu-item'] ) {
							$edit_url = admin_url( 'nav-menus.php' );
						} else {
							$edit_url = add_query_arg(
								array(
									'edit-menu-item' => $item_id,
								),
								remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) )
							);
						}

						printf(
							'<a class="item-edit" id="edit-%s" href="%s" aria-label="%s"><span class="screen-reader-text">%s</span></a>',
							$item_id,
							esc_url( $edit_url ),
							esc_attr__( 'Edit menu item' ),
							/* translators: Hidden accessibility text. */
							__( 'Edit' )
						);
						?>
					</span>
				</div>
			</div>

					<p class="field-url description description-wide">
						</label>
					</p>
				<p class="description description-wide">
					</label>
				</p>
				<p class="field-title-attribute field-attr-title description description-wide">
					</label>
				</p>
				<p class="field-link-target description">
					</label>
				</p>
				<div class="description-group">
					<p class="field-css-classes description description-thin">
						</label>
					</p>
					<p class="field-xfn description description-thin">
						</label>
					</p>
				</div>
				<p class="field-description description description-wide">
					</label>
				</p>

				/**
				 * Update parent and order of menu item using select inputs.
				 *
				 * @since 6.7.0
				 */
				?>
	
				<div class="field-move-combo description-group">
					<p class="description description-wide">
						</label>
						</select>
					</p>
					<p class="description description-wide">
						</label>
						</select>
					</p>
				</div>

				/**
				 * Fires just before the move buttons of a nav menu item in the menu editor.
				 *
				 * @since 5.4.0
				 *
				 * @param string        $item_id           Menu item ID as a numeric string.
				 * @param WP_Post       $menu_item         Menu item data object.
				 * @param int           $depth             Depth of menu item. Used for padding.
				 * @param stdClass|null $args              An object of menu item arguments.
				 * @param int           $current_object_id Nav menu ID.
				 */
				do_action( 'wp_nav_menu_item_custom_fields', $item_id, $menu_item, $depth, $args, $current_object_id );
				?>

				<fieldset class="field-move hide-if-no-js description description-wide">
					<button type="button" class="button-link menus-move menus-move-left" data-dir="left"></button>
					<button type="button" class="button-link menus-move menus-move-right" data-dir="right"></button>
				</fieldset>

				<div class="menu-item-actions description-wide submitbox">
						<p class="link-to-original">
							/* translators: %s: Link to menu item's original object. */
							printf( __( 'Original: %s' ), '<a href="' . esc_url( $menu_item->url ) . '">' . esc_html( $original_title ) . '</a>' );
							?>
						</p>

					printf(
						'<a class="item-delete submitdelete deletion" id="delete-%s" href="%s">%s</a>',
						$item_id,
						wp_nonce_url(
							add_query_arg(
								array(
									'action'    => 'delete-menu-item',
									'menu-item' => $item_id,
								),
								admin_url( 'nav-menus.php' )
							),
							'delete-menu_item_' . $item_id
						),
						__( 'Remove' )
					);
					?>
					<span class="meta-sep hide-if-no-js"> | </span>
					printf(
						'<a class="item-cancel submitcancel hide-if-no-js" id="cancel-%s" href="%s#menu-item-settings-%s">%s</a>',
						$item_id,
						esc_url(
							add_query_arg(
								array(
									'edit-menu-item' => $item_id,
									'cancel'         => time(),
								),
								admin_url( 'nav-menus.php' )
							)
						),
						$item_id,
						__( 'Cancel' )
					);
					?>
				</div>

			</div><!-- .menu-item-settings-->
			<ul class="menu-item-transport"></ul>
		$output .= ob_get_clean();
	}
}
} // namespace CharacterGeneratorDev
