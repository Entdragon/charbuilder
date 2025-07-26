<?php

namespace CharacterGeneratorDev {

/**
 * Multisite network settings administration panel.
 *
 * @package WordPress
 * @subpackage Multisite
 * @since 3.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';

/** WordPress Translation Installation API */
require_once ABSPATH . 'wp-admin/includes/translation-install.php';

if ( ! current_user_can( 'manage_network_options' ) ) {
	wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
}

// Used in the HTML title tag.
$title       = __( 'Network Settings' );
$parent_file = 'settings.php';

// Handle network admin email change requests.
if ( ! empty( $_GET['network_admin_hash'] ) ) {
	$new_admin_details = get_site_option( 'network_admin_hash' );
	$redirect          = 'settings.php?updated=false';
	if ( is_array( $new_admin_details ) && hash_equals( $new_admin_details['hash'], $_GET['network_admin_hash'] ) && ! empty( $new_admin_details['newemail'] ) ) {
		update_site_option( 'admin_email', $new_admin_details['newemail'] );
		delete_site_option( 'network_admin_hash' );
		delete_site_option( 'new_admin_email' );
		$redirect = 'settings.php?updated=true';
	}
	wp_redirect( network_admin_url( $redirect ) );
	exit;
} elseif ( ! empty( $_GET['dismiss'] ) && 'new_network_admin_email' === $_GET['dismiss'] ) {
	check_admin_referer( 'dismiss_new_network_admin_email' );
	delete_site_option( 'network_admin_hash' );
	delete_site_option( 'new_admin_email' );
	wp_redirect( network_admin_url( 'settings.php?updated=true' ) );
	exit;
}

add_action( 'admin_head', 'network_settings_add_js' );

get_current_screen()->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview' ),
		'content' =>
			'<p>' . __( 'This screen sets and changes options for the network as a whole. The first site is the main site in the network and network options are pulled from that original site&#8217;s options.' ) . '</p>' .
			'<p>' . __( 'Operational settings has fields for the network&#8217;s name and admin email.' ) . '</p>' .
			'<p>' . __( 'Registration settings can disable/enable public signups. If you let others sign up for a site, install spam plugins. Spaces, not commas, should separate names banned as sites for this network.' ) . '</p>' .
			'<p>' . __( 'New site settings are defaults applied when a new site is created in the network. These include welcome email for when a new site or user account is registered, and what&#8127;s put in the first post, page, comment, comment author, and comment URL.' ) . '</p>' .
			'<p>' . __( 'Upload settings control the size of the uploaded files and the amount of available upload space for each site. You can change the default value for specific sites when you edit a particular site. Allowed file types are also listed (space separated only).' ) . '</p>' .
			'<p>' . __( 'You can set the language, and WordPress will automatically download and install the translation files (available if your filesystem is writable).' ) . '</p>' .
			'<p>' . __( 'Menu setting enables/disables the plugin menus from appearing for non super admins, so that only super admins, not site admins, have access to activate plugins.' ) . '</p>' .
			'<p>' . __( 'Super admins can no longer be added on the Options screen. You must now go to the list of existing users on Network Admin > Users and click on Username or the Edit action link below that name. This goes to an Edit User page where you can check a box to grant super admin privileges.' ) . '</p>',
	)
);

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://developer.wordpress.org/advanced-administration/multisite/admin/settings/">Documentation on Network Settings</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/forums/">Support forums</a>' ) . '</p>'
);

if ( $_POST ) {
	/** This action is documented in wp-admin/network/edit.php */
	do_action( 'wpmuadminedit' );

	check_admin_referer( 'siteoptions' );

	$checked_options = array(
		'menu_items'                  => array(),
		'registrationnotification'    => 'no',
		'upload_space_check_disabled' => 1,
		'add_new_users'               => 0,
	);
	foreach ( $checked_options as $option_name => $option_unchecked_value ) {
		if ( ! isset( $_POST[ $option_name ] ) ) {
			$_POST[ $option_name ] = $option_unchecked_value;
		}
	}

	$options = array(
		'registrationnotification',
		'registration',
		'add_new_users',
		'menu_items',
		'upload_space_check_disabled',
		'blog_upload_space',
		'upload_filetypes',
		'site_name',
		'first_post',
		'first_page',
		'first_comment',
		'first_comment_url',
		'first_comment_author',
		'welcome_email',
		'welcome_user_email',
		'fileupload_maxk',
		'illegal_names',
		'limited_email_domains',
		'banned_email_domains',
		'WPLANG',
		'new_admin_email',
		'first_comment_email',
	);

	// Handle translation installation.
	if ( ! empty( $_POST['WPLANG'] ) && current_user_can( 'install_languages' ) && wp_can_install_language_pack() ) {
		$language = wp_download_language_pack( $_POST['WPLANG'] );
		if ( $language ) {
			$_POST['WPLANG'] = $language;
		}
	}

	foreach ( $options as $option_name ) {
		if ( ! isset( $_POST[ $option_name ] ) ) {
			continue;
		}
		$value = wp_unslash( $_POST[ $option_name ] );
		update_site_option( $option_name, $value );
	}

	/**
	 * Fires after the network options are updated.
	 *
	 * @since MU (3.0.0)
	 */
	do_action( 'update_wpmu_options' );

	wp_redirect( add_query_arg( 'updated', 'true', network_admin_url( 'settings.php' ) ) );
	exit;
}

require_once ABSPATH . 'wp-admin/admin-header.php';

if ( isset( $_GET['updated'] ) ) {
	wp_admin_notice(
		__( 'Settings saved.' ),
		array(
			'type'        => 'success',
			'dismissible' => true,
			'id'          => 'message',
		)
	);
}
?>

<div class="wrap">
	<form method="post" action="settings.php" novalidate="novalidate">
		<table class="form-table" role="presentation">
			<tr>
				<td>
				</td>
			</tr>

			<tr>
				<td>
					<p class="description" id="admin-email-desc">
					</p>
					$new_admin_email = get_site_option( 'new_admin_email' );
					if ( $new_admin_email && get_site_option( 'admin_email' ) !== $new_admin_email ) :
						$notice_message = sprintf(
							/* translators: %s: New network admin email. */
							__( 'There is a pending change of the network admin email to %s.' ),
							'<code>' . esc_html( $new_admin_email ) . '</code>'
						);

						$notice_message .= sprintf(
							' <a href="%1$s">%2$s</a>',
							esc_url( wp_nonce_url( network_admin_url( 'settings.php?dismiss=new_network_admin_email' ), 'dismiss_new_network_admin_email' ) ),
							__( 'Cancel' )
						);

						wp_admin_notice(
							$notice_message,
							array(
								'type'               => 'warning',
								'dismissible'        => true,
								'additional_classes' => array( 'inline' ),
							)
						);
					endif;
					?>
				</td>
			</tr>
		</table>
		<table class="form-table" role="presentation">
			<tr>
				if ( ! get_site_option( 'registration' ) ) {
					update_site_option( 'registration', 'none' );
				}
				$reg = get_site_option( 'registration' );
				?>
				<td>
					<fieldset>
					<legend class="screen-reader-text">
						/* translators: Hidden accessibility text. */
						_e( 'New registrations settings' );
						?>
					</legend>
					if ( is_subdomain_install() ) {
						echo '<p class="description">';
						printf(
							/* translators: 1: NOBLOGREDIRECT, 2: wp-config.php */
							__( 'If registration is disabled, please set %1$s in %2$s to a URL you will redirect visitors to if they visit a non-existent site.' ),
							'<code>NOBLOGREDIRECT</code>',
							'<code>wp-config.php</code>'
						);
						echo '</p>';
					}
					?>
					</fieldset>
				</td>
			</tr>

			<tr>
				if ( ! get_site_option( 'registrationnotification' ) ) {
					update_site_option( 'registrationnotification', 'yes' );
				}
				?>
				<td>
				</td>
			</tr>

			<tr id="addnewusers">
				<td>
				</td>
			</tr>

			<tr>
				<td>
					$illegal_names = get_site_option( 'illegal_names' );

					if ( empty( $illegal_names ) ) {
						$illegal_names = '';
					} elseif ( is_array( $illegal_names ) ) {
						$illegal_names = implode( ' ', $illegal_names );
					}
					?>
					<p class="description" id="illegal-names-desc">
					</p>
				</td>
			</tr>

			<tr>
				<td>
					$limited_email_domains = get_site_option( 'limited_email_domains' );

					if ( empty( $limited_email_domains ) ) {
						$limited_email_domains = '';
					} else {
						// Convert from an input field. Back-compat for WPMU < 1.0.
						$limited_email_domains = str_replace( ' ', "\n", $limited_email_domains );

						if ( is_array( $limited_email_domains ) ) {
							$limited_email_domains = implode( "\n", $limited_email_domains );
						}
					}
					?>
					<textarea name="limited_email_domains" id="limited_email_domains" aria-describedby="limited-email-domains-desc" cols="45" rows="5">
					<p class="description" id="limited-email-domains-desc">
					</p>
				</td>
			</tr>

			<tr>
				<td>
					$banned_email_domains = get_site_option( 'banned_email_domains' );

					if ( empty( $banned_email_domains ) ) {
						$banned_email_domains = '';
					} elseif ( is_array( $banned_email_domains ) ) {
						$banned_email_domains = implode( "\n", $banned_email_domains );
					}
					?>
					<textarea name="banned_email_domains" id="banned_email_domains" aria-describedby="banned-email-domains-desc" cols="45" rows="5">
					<p class="description" id="banned-email-domains-desc">
					</p>
				</td>
			</tr>

		</table>
		<table class="form-table" role="presentation">

			<tr>
				<td>
					<textarea name="welcome_email" id="welcome_email" aria-describedby="welcome-email-desc" rows="5" cols="45" class="large-text">
					<p class="description" id="welcome-email-desc">
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<textarea name="welcome_user_email" id="welcome_user_email" aria-describedby="welcome-user-email-desc" rows="5" cols="45" class="large-text">
					<p class="description" id="welcome-user-email-desc">
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<textarea name="first_post" id="first_post" aria-describedby="first-post-desc" rows="5" cols="45" class="large-text">
					<p class="description" id="first-post-desc">
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<textarea name="first_page" id="first_page" aria-describedby="first-page-desc" rows="5" cols="45" class="large-text">
					<p class="description" id="first-page-desc">
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<textarea name="first_comment" id="first_comment" aria-describedby="first-comment-desc" rows="5" cols="45" class="large-text">
					<p class="description" id="first-comment-desc">
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="description" id="first-comment-author-desc">
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="description" id="first-comment-email-desc">
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="description" id="first-comment-url-desc">
					</p>
				</td>
			</tr>
		</table>
		<table class="form-table" role="presentation">
			<tr>
				<td>
						printf(
							/* translators: %s: Number of megabytes to limit uploads to. */
							__( 'Limit total size of files uploaded to %s MB' ),
							'</label><label><input name="blog_upload_space" type="number" min="0" style="width: 100px" id="blog_upload_space" aria-describedby="blog-upload-space-desc" value="' . esc_attr( get_site_option( 'blog_upload_space', 100 ) ) . '" />'
						);
						?>
					</label><br />
					<p class="screen-reader-text" id="blog-upload-space-desc">
						/* translators: Hidden accessibility text. */
						_e( 'Size in megabytes' );
						?>
					</p>
				</td>
			</tr>

			<tr>
				<td>
					<p class="description" id="upload-filetypes-desc">
					</p>
				</td>
			</tr>

			<tr>
				<td>
						printf(
							/* translators: %s: File size in kilobytes. */
							__( '%s KB' ),
							'<input name="fileupload_maxk" type="number" min="0" style="width: 100px" id="fileupload_maxk" aria-describedby="fileupload-maxk-desc" value="' . esc_attr( get_site_option( 'fileupload_maxk', 300 ) ) . '" />'
						);
						?>
					<p class="screen-reader-text" id="fileupload-maxk-desc">
						/* translators: Hidden accessibility text. */
						_e( 'Size in kilobytes' );
						?>
					</p>
				</td>
			</tr>
		</table>

		$languages    = get_available_languages();
		$translations = wp_get_available_translations();
		if ( ! empty( $languages ) || ! empty( $translations ) ) {
			?>
			<table class="form-table" role="presentation">
				<tr>
					<td>
						$lang = get_site_option( 'WPLANG' );
						if ( ! in_array( $lang, $languages, true ) ) {
							$lang = '';
						}

						wp_dropdown_languages(
							array(
								'name'         => 'WPLANG',
								'id'           => 'WPLANG',
								'selected'     => $lang,
								'languages'    => $languages,
								'translations' => $translations,
								'show_available_translations' => current_user_can( 'install_languages' ) && wp_can_install_language_pack(),
							)
						);
						?>
					</td>
				</tr>
			</table>
		}
		?>

		$menu_perms = get_site_option( 'menu_items' );
		/**
		 * Filters available network-wide administration menu options.
		 *
		 * Options returned to this filter are output as individual checkboxes that, when selected,
		 * enable site administrator access to the specified administration menu in certain contexts.
		 *
		 * Adding options for specific menus here hinges on the appropriate checks and capabilities
		 * being in place in the site dashboard on the other side. For instance, when the single
		 * default option, 'plugins' is enabled, site administrators are granted access to the Plugins
		 * screen in their individual sites' dashboards.
		 *
		 * @since MU (3.0.0)
		 *
		 * @param string[] $admin_menus Associative array of the menu items available.
		 */
		$menu_items = apply_filters( 'mu_menu_items', array( 'plugins' => __( 'Plugins' ) ) );

		if ( $menu_items ) :
			?>
			<table id="menu" class="form-table">
				<tr>
					<td>
						echo '<fieldset><legend class="screen-reader-text">' .
							/* translators: Hidden accessibility text. */
							__( 'Enable menus' ) .
						'</legend>';

						foreach ( (array) $menu_items as $key => $val ) {
							echo "<label><input type='checkbox' name='menu_items[" . $key . "]' value='1'" . ( isset( $menu_perms[ $key ] ) ? checked( $menu_perms[ $key ], '1', false ) : '' ) . ' /> ' . esc_html( $val ) . '</label><br/>';
						}

						echo '</fieldset>';
						?>
					</td>
				</tr>
			</table>
		endif;
		?>

		/**
		 * Fires at the end of the Network Settings form, before the submit button.
		 *
		 * @since MU (3.0.0)
		 */
		do_action( 'wpmu_options' );
		?>
	</form>
</div>


} // namespace CharacterGeneratorDev
