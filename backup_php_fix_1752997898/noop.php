<?php

namespace CharacterGeneratorDev {

<?php
/**
 * Noop functions for load-scripts.php and load-styles.php.
 *
 * @package WordPress
 * @subpackage Administration
 * @since 4.4.0
 */

/**
 * @ignore
 */
function CG_Dev\\__() {}

/**
 * @ignore
 */
function CG_Dev\\_x() {}

/**
 * @ignore
 */
function CG_Dev\\add_filter() {}

/**
 * @ignore
 */
function CG_Dev\\has_filter() {
	return false;
}

/**
 * @ignore
 */
function CG_Dev\\esc_attr() {}

/**
 * @ignore
 */
function CG_Dev\\apply_filters() {}

/**
 * @ignore
 */
function CG_Dev\\get_option() {}

/**
 * @ignore
 */
function CG_Dev\\is_lighttpd_before_150() {}

/**
 * @ignore
 */
function CG_Dev\\add_action() {}

/**
 * @ignore
 */
function CG_Dev\\did_action() {}

/**
 * @ignore
 */
function CG_Dev\\do_action_ref_array() {}

/**
 * @ignore
 */
function CG_Dev\\get_bloginfo() {}

/**
 * @ignore
 */
function CG_Dev\\is_admin() {
	return true;
}

/**
 * @ignore
 */
function CG_Dev\\site_url() {}

/**
 * @ignore
 */
function CG_Dev\\admin_url() {}

/**
 * @ignore
 */
function CG_Dev\\home_url() {}

/**
 * @ignore
 */
function CG_Dev\\includes_url() {}

/**
 * @ignore
 */
function CG_Dev\\wp_guess_url() {}

function CG_Dev\\get_file( $path ) {

	$path = realpath( $path );

	if ( ! $path || ! @is_file( $path ) ) {
		return '';
	}

	return @file_get_contents( $path );
}

} // namespace CharacterGeneratorDev
