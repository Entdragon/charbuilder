<?php

namespace CharacterGeneratorDev {

/**
 * Edit user administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';

/** WordPress Translation Installation API */
require_once ABSPATH . 'wp-admin/includes/translation-install.php';

$action          = ! empty( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';
$user_id         = ! empty( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : 0;
$wp_http_referer = ! empty( $_REQUEST['wp_http_referer'] ) ? sanitize_url( $_REQUEST['wp_http_referer'] ) : '';

$current_user = wp_get_current_user();

if ( ! defined( 'IS_PROFILE_PAGE' ) ) {
	define( 'IS_PROFILE_PAGE', ( $user_id === $current_user->ID ) );
}

if ( ! $user_id && IS_PROFILE_PAGE ) {
	$user_id = $current_user->ID;
} elseif ( ! $user_id && ! IS_PROFILE_PAGE ) {
	wp_die( __( 'Invalid user ID.' ) );
} elseif ( ! get_userdata( $user_id ) ) {
	wp_die( __( 'Invalid user ID.' ) );
}

wp_enqueue_script( 'user-profile' );

if ( wp_is_application_passwords_available_for_user( $user_id ) ) {
	wp_enqueue_script( 'application-passwords' );
}

if ( IS_PROFILE_PAGE ) {
	// Used in the HTML title tag.
	$title = __( 'Profile' );
} else {
	// Used in the HTML title tag.
	/* translators: %s: User's display name. */
	$title = __( 'Edit User %s' );
}

if ( current_user_can( 'edit_users' ) && ! IS_PROFILE_PAGE ) {
	$submenu_file = 'users.php';
} else {
	$submenu_file = 'profile.php';
}

if ( current_user_can( 'edit_users' ) && ! is_user_admin() ) {
	$parent_file = 'users.php';
} else {
	$parent_file = 'profile.php';
}

$profile_help = '<p>' . __( 'Your profile contains information about you (your &#8220;account&#8221;) as well as some personal options related to using WordPress.' ) . '</p>' .
	'<p>' . __( 'You can change your password, turn on keyboard shortcuts, change the color scheme of your WordPress administration screens, and turn off the WYSIWYG (Visual) editor, among other things. You can hide the Toolbar (formerly called the Admin Bar) from the front end of your site, however it cannot be disabled on the admin screens.' ) . '</p>' .
	'<p>' . __( 'You can select the language you wish to use while using the WordPress administration screen without affecting the language site visitors see.' ) . '</p>' .
	'<p>' . __( 'Your username cannot be changed, but you can use other fields to enter your real name or a nickname, and change which name to display on your posts.' ) . '</p>' .
	'<p>' . __( 'You can log out of other devices, such as your phone or a public computer, by clicking the Log Out Everywhere Else button.' ) . '</p>' .
	'<p>' . __( 'Required fields are indicated; the rest are optional. Profile information will only be displayed if your theme is set up to do so.' ) . '</p>' .
	'<p>' . __( 'Remember to click the Update Profile button when you are finished.' ) . '</p>';

get_current_screen()->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview' ),
		'content' => $profile_help,
	)
);

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://wordpress.org/documentation/article/users-your-profile-screen/">Documentation on User Profiles</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/forums/">Support forums</a>' ) . '</p>'
);

$wp_http_referer = remove_query_arg( array( 'update', 'delete_count', 'user_id' ), $wp_http_referer );

$user_can_edit = current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' );

/**
 * Filters whether to allow administrators on Multisite to edit every user.
 *
 * Enabling the user editing form via this filter also hinges on the user holding
 * the 'manage_network_users' cap, and the logged-in user not matching the user
 * profile open for editing.
 *
 * The filter was introduced to replace the EDIT_ANY_USER constant.
 *
 * @since 3.0.0
 *
 * @param bool $allow Whether to allow editing of any user. Default true.
 */
if ( is_multisite()
	&& ! current_user_can( 'manage_network_users' )
	&& $user_id !== $current_user->ID
	&& ! apply_filters( 'enable_edit_any_user_configuration', true )
) {
	wp_die( __( 'Sorry, you are not allowed to edit this user.' ) );
}

// Execute confirmed email change. See send_confirmation_on_profile_email().
if ( IS_PROFILE_PAGE && isset( $_GET['newuseremail'] ) && $current_user->ID ) {
	$new_email = get_user_meta( $current_user->ID, '_new_email', true );
	if ( $new_email && hash_equals( $new_email['hash'], $_GET['newuseremail'] ) ) {
		$user             = new stdClass();
		$user->ID         = $current_user->ID;
		$user->user_email = esc_html( trim( $new_email['newemail'] ) );
		if ( is_multisite() && $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $current_user->user_login ) ) ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $user->user_email, $current_user->user_login ) );
		}
		wp_update_user( $user );
		delete_user_meta( $current_user->ID, '_new_email' );
		wp_redirect( add_query_arg( array( 'updated' => 'true' ), self_admin_url( 'profile.php' ) ) );
		die();
	} else {
		wp_redirect( add_query_arg( array( 'error' => 'new-email' ), self_admin_url( 'profile.php' ) ) );
	}
} elseif ( IS_PROFILE_PAGE && ! empty( $_GET['dismiss'] ) && $current_user->ID . '_new_email' === $_GET['dismiss'] ) {
	check_admin_referer( 'dismiss-' . $current_user->ID . '_new_email' );
	delete_user_meta( $current_user->ID, '_new_email' );
	wp_redirect( add_query_arg( array( 'updated' => 'true' ), self_admin_url( 'profile.php' ) ) );
	die();
}

switch ( $action ) {
	case 'update':
		check_admin_referer( 'update-user_' . $user_id );

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_die( __( 'Sorry, you are not allowed to edit this user.' ) );
		}

		if ( IS_PROFILE_PAGE ) {
			/**
			 * Fires before the page loads on the 'Profile' editing screen.
			 *
			 * The action only fires if the current user is editing their own profile.
			 *
			 * @since 2.0.0
			 *
			 * @param int $user_id The user ID.
			 */
			do_action( 'personal_options_update', $user_id );
		} else {
			/**
			 * Fires before the page loads on the 'Edit User' screen.
			 *
			 * @since 2.7.0
			 *
			 * @param int $user_id The user ID.
			 */
			do_action( 'edit_user_profile_update', $user_id );
		}

		// Update the email address in signups, if present.
		if ( is_multisite() ) {
			$user = get_userdata( $user_id );

			if ( $user->user_login && isset( $_POST['email'] ) && is_email( $_POST['email'] ) && $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $user->user_login ) ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $_POST['email'], $user_login ) );
			}
		}

		// Update the user.
		$errors = edit_user( $user_id );

		// Grant or revoke super admin status if requested.
		if ( is_multisite() && is_network_admin()
			&& ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' )
			&& ! isset( $super_admins ) && empty( $_POST['super_admin'] ) === is_super_admin( $user_id )
		) {
			empty( $_POST['super_admin'] ) ? revoke_super_admin( $user_id ) : grant_super_admin( $user_id );
		}

		if ( ! is_wp_error( $errors ) ) {
			$redirect = add_query_arg( 'updated', true, get_edit_user_link( $user_id ) );
			if ( $wp_http_referer ) {
				$redirect = add_query_arg( 'wp_http_referer', urlencode( $wp_http_referer ), $redirect );
			}
			wp_redirect( $redirect );
			exit;
		}

		// Intentional fall-through to display $errors.
	default:
		$profile_user = get_user_to_edit( $user_id );

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_die( __( 'Sorry, you are not allowed to edit this user.' ) );
		}

		$title    = sprintf( $title, $profile_user->display_name );
		$sessions = WP_Session_Tokens::get_instance( $profile_user->ID );

		require_once ABSPATH . 'wp-admin/admin-header.php';
		?>

		if ( ! IS_PROFILE_PAGE && is_super_admin( $profile_user->ID ) && current_user_can( 'manage_network_options' ) ) :
			$message = '<strong>' . __( 'Important:' ) . '</strong> ' . __( 'This user has super admin privileges.' );
			wp_admin_notice(
				$message,
				array(
					'type' => 'info',
				)
			);
		endif;

		if ( isset( $_GET['updated'] ) ) :
			if ( IS_PROFILE_PAGE ) :
				$message = '<p><strong>' . __( 'Profile updated.' ) . '</strong></p>';
			else :
				$message = '<p><strong>' . __( 'User updated.' ) . '</strong></p>';
			endif;
			if ( $wp_http_referer && ! str_contains( $wp_http_referer, 'user-new.php' ) && ! IS_PROFILE_PAGE ) :
				$message .= sprintf(
					'<p><a href="%1$s">%2$s</a></p>',
					esc_url( wp_validate_redirect( sanitize_url( $wp_http_referer ), self_admin_url( 'users.php' ) ) ),
					__( '&larr; Go to Users' )
				);
			endif;
			wp_admin_notice(
				$message,
				array(
					'id'                 => 'message',
					'dismissible'        => true,
					'additional_classes' => array( 'updated' ),
					'paragraph_wrap'     => false,
				)
			);
		endif;

		if ( isset( $_GET['error'] ) ) :
			$message = '';
			if ( 'new-email' === $_GET['error'] ) :
				$message = __( 'Error while saving the new email address. Please try again.' );
			endif;
			wp_admin_notice(
				$message,
				array(
					'type' => 'error',
				)
			);
		endif;

		if ( isset( $errors ) && is_wp_error( $errors ) ) {
			wp_admin_notice(
				implode( "</p>\n<p>", $errors->get_error_messages() ),
				array(
					'additional_classes' => array( 'error' ),
				)
			);
		}
		?>

		<div class="wrap" id="profile-page">
			<h1 class="wp-heading-inline">
			</h1>


			<hr class="wp-header-end">

				/**
				 * Fires inside the your-profile form tag on the user editing screen.
				 *
				 * @since 3.0.0
				 */
				do_action( 'user_edit_form_tag' );
				?>
				>
				<p>
					<input type="hidden" name="from" value="profile" />
				</p>


				<table class="form-table" role="presentation">
						<tr class="user-rich-editing-wrap">
							<td>
								</label>
							</td>
						</tr>

					$show_syntax_highlighting_preference = (
					// For Custom HTML widget and Additional CSS in Customizer.
					user_can( $profile_user, 'edit_theme_options' )
					||
					// Edit plugins.
					user_can( $profile_user, 'edit_plugins' )
					||
					// Edit themes.
					user_can( $profile_user, 'edit_themes' )
					);
					?>

					<tr class="user-syntax-highlighting-wrap">
						<td>
							</label>
						</td>
					</tr>

					<tr class="user-admin-color-wrap">
						<td>
							/**
							 * Fires in the 'Admin Color Scheme' section of the user editing screen.
							 *
							 * The section is only enabled if a callback is hooked to the action,
							 * and if there is more than one defined color scheme for the admin.
							 *
							 * @since 3.0.0
							 * @since 3.8.1 Added `$user_id` parameter.
							 *
							 * @param int $user_id The user ID.
							 */
							do_action( 'admin_color_scheme_picker', $user_id );
							?>
						</td>
					</tr>

					<tr class="user-comment-shortcuts-wrap">
						<td>
							<label for="comment_shortcuts">
							</label>
						</td>
					</tr>

					<tr class="show-admin-bar user-admin-bar-front-wrap">
						<td>
							<label for="admin_bar_front">
							</label><br />
						</td>
					</tr>

					$languages                = get_available_languages();
					$can_install_translations = current_user_can( 'install_languages' ) && wp_can_install_language_pack();
					?>
					<tr class="user-language-wrap">
						<th scope="row">
						</th>
						<td>
								$user_locale = $profile_user->locale;

							if ( 'en_US' === $user_locale ) {
								$user_locale = '';
							} elseif ( '' === $user_locale || ! in_array( $user_locale, $languages, true ) ) {
								$user_locale = 'site-default';
							}

							wp_dropdown_languages(
								array(
									'name'      => 'locale',
									'id'        => 'locale',
									'selected'  => $user_locale,
									'languages' => $languages,
									'show_available_translations' => $can_install_translations,
									'show_option_site_default' => true,
								)
							);
							?>
						</td>
					</tr>

					/**
					 * Fires at the end of the 'Personal Options' settings table on the user editing screen.
					 *
					 * @since 2.7.0
					 *
					 * @param WP_User $profile_user The current WP_User object.
					 */
					do_action( 'personal_options', $profile_user );
					?>

				</table>
				if ( IS_PROFILE_PAGE ) {
					/**
					 * Fires after the 'Personal Options' settings table on the 'Profile' editing screen.
					 *
					 * The action only fires if the current user is editing their own profile.
					 *
					 * @since 2.0.0
					 *
					 * @param WP_User $profile_user The current WP_User object.
					 */
					do_action( 'profile_personal_options', $profile_user );
				}
				?>


				<table class="form-table" role="presentation">
					<tr class="user-user-login-wrap">
					</tr>

						<tr class="user-role-wrap">
							<td>
								<select name="role" id="role">
									// Compare user role against currently editable roles.
									$user_roles = array_intersect( array_values( $profile_user->roles ), array_keys( get_editable_roles() ) );
									$user_role  = reset( $user_roles );

									// Print the full list of roles with the primary one selected.
									wp_dropdown_roles( $user_role );

									// Print the 'no role' option. Make it selected if the user has no role yet.
									if ( $user_role ) {
										echo '<option value="">' . __( '&mdash; No role for this site &mdash;' ) . '</option>';
									} else {
										echo '<option value="" selected="selected">' . __( '&mdash; No role for this site &mdash;' ) . '</option>';
									}
									?>
							</select>
							</td>
						</tr>

						<tr class="user-super-admin-wrap">
							<td>
							</td>
						</tr>

					<tr class="user-first-name-wrap">
					</tr>

					<tr class="user-last-name-wrap">
					</tr>

					<tr class="user-nickname-wrap">
					</tr>

					<tr class="user-display-name-wrap">
						<th>
						</th>
						<td>
							<select name="display_name" id="display_name">
									$public_display                     = array();
									$public_display['display_nickname'] = $profile_user->nickname;
									$public_display['display_username'] = $profile_user->user_login;

								if ( ! empty( $profile_user->first_name ) ) {
									$public_display['display_firstname'] = $profile_user->first_name;
								}

								if ( ! empty( $profile_user->last_name ) ) {
									$public_display['display_lastname'] = $profile_user->last_name;
								}

								if ( ! empty( $profile_user->first_name ) && ! empty( $profile_user->last_name ) ) {
									$public_display['display_firstlast'] = $profile_user->first_name . ' ' . $profile_user->last_name;
									$public_display['display_lastfirst'] = $profile_user->last_name . ' ' . $profile_user->first_name;
								}

								if ( ! in_array( $profile_user->display_name, $public_display, true ) ) { // Only add this if it isn't duplicated elsewhere.
									$public_display = array( 'display_displayname' => $profile_user->display_name ) + $public_display;
								}

								$public_display = array_map( 'trim', $public_display );
								$public_display = array_unique( $public_display );

								?>
							</select>
						</td>
					</tr>
				</table>


				<table class="form-table" role="presentation">
					<tr class="user-email-wrap">
						<td>
								<p class="description" id="email-description">
								</p>

							$new_email = get_user_meta( $current_user->ID, '_new_email', true );
							if ( $new_email && $new_email['newemail'] !== $current_user->user_email && $profile_user->ID === $current_user->ID ) :

								$pending_change_message = sprintf(
									/* translators: %s: New email. */
									__( 'There is a pending change of your email to %s.' ),
									'<code>' . esc_html( $new_email['newemail'] ) . '</code>'
								);
								$pending_change_message .= sprintf(
									' <a href="%1$s">%2$s</a>',
									esc_url( wp_nonce_url( self_admin_url( 'profile.php?dismiss=' . $current_user->ID . '_new_email' ), 'dismiss-' . $current_user->ID . '_new_email' ) ),
									__( 'Cancel' )
								);
								wp_admin_notice(
									$pending_change_message,
									array(
										'additional_classes' => array( 'updated', 'inline' ),
									)
								);
							endif;
							?>
						</td>
					</tr>

					<tr class="user-url-wrap">
					</tr>

						<th>
							/**
							 * Filters a user contactmethod label.
							 *
							 * The dynamic portion of the hook name, `$name`, refers to
							 * each of the keys in the contact methods array.
							 *
							 * @since 2.9.0
							 *
							 * @param string $desc The translatable label for the contact method.
							 */
							echo apply_filters( "user_{$name}_label", $desc );
							?>
							</label>
						</th>
						<td>
						</td>
					</tr>
				</table>


				<table class="form-table" role="presentation">
					<tr class="user-description-wrap">
					</tr>

						<tr class="user-profile-picture">
							<td>
								<p class="description">
									if ( IS_PROFILE_PAGE ) {
										$description = sprintf(
											/* translators: %s: Gravatar URL. */
											__( '<a href="%s">You can change your profile picture on Gravatar</a>.' ),
											/* translators: The localized Gravatar URL. */
											__( 'https://gravatar.com/' )
										);
									} else {
										$description = '';
									}

									/**
									 * Filters the user profile picture description displayed under the Gravatar.
									 *
									 * @since 4.4.0
									 * @since 4.7.0 Added the `$profile_user` parameter.
									 *
									 * @param string  $description  The description that will be printed.
									 * @param WP_User $profile_user The current WP_User object.
									 */
									echo apply_filters( 'user_profile_picture_description', $description, $profile_user );
									?>
								</p>
							</td>
						</tr>
					/**
					 * Filters the display of the password fields.
					 *
					 * @since 1.5.1
					 * @since 2.8.0 Added the `$profile_user` parameter.
					 * @since 4.4.0 Now evaluated only in user-edit.php.
					 *
					 * @param bool    $show         Whether to show the password fields. Default true.
					 * @param WP_User $profile_user User object for the current user to edit.
					 */
					$show_password_fields = apply_filters( 'show_password_fields', true, $profile_user );
					?>
						</table>


						<table class="form-table" role="presentation">
							<tr id="password" class="user-pass1-wrap">
								<td>
									<input type="hidden" value=" " /><!-- #24364 workaround -->
									<div class="wp-pwd hide-if-js">
										<div class="password-input-wrapper">
											<div style="display:none" id="pass-strength-result" aria-live="polite"></div>
										</div>
											<span class="dashicons dashicons-hidden" aria-hidden="true"></span>
										</button>
											<span class="dashicons dashicons-no" aria-hidden="true"></span>
										</button>
									</div>
								</td>
							</tr>
							<tr class="user-pass2-wrap hide-if-js">
								<td>
								<input type="password" name="pass2" id="pass2" class="regular-text" value="" autocomplete="new-password" spellcheck="false" aria-describedby="pass2-desc" />
								</td>
							</tr>
							<tr class="pw-weak">
								<td>
									<label>
										<input type="checkbox" name="pw_weak" class="pw-checkbox" />
									</label>
								</td>
							</tr>

								<tr class="user-generate-reset-link-wrap hide-if-no-js">
									<td>
										<div class="generate-reset-link">
											<button type="button" class="button button-secondary" id="generate-reset-link">
											</button>
										</div>
										<p class="description">
											printf(
												/* translators: %s: User's display name. */
												__( 'Send %s a link to reset their password. This will not change their password, nor will it force a change.' ),
												esc_html( $profile_user->display_name )
											);
											?>
										</p>
									</td>
								</tr>

								<tr class="user-sessions-wrap hide-if-no-js">
									<td aria-live="assertive">
										<p class="description">
										</p>
									</td>
								</tr>
								<tr class="user-sessions-wrap hide-if-no-js">
									<td aria-live="assertive">
										<p class="description">
										</p>
									</td>
								</tr>
								<tr class="user-sessions-wrap hide-if-no-js">
									<td>
										<p class="description">
											/* translators: %s: User's display name. */
											printf( __( 'Log %s out of all locations.' ), $profile_user->display_name );
											?>
										</p>
									</td>
								</tr>
						</table>

						<div class="application-passwords hide-if-no-js" id="application-passwords-section">
								if ( is_multisite() ) :
									$blogs       = get_blogs_of_user( $user_id, true );
									$blogs_count = count( $blogs );

									if ( $blogs_count > 1 ) :
										?>
										<p>
											/* translators: 1: URL to my-sites.php, 2: Number of sites the user has. */
											$message = _n(
												'Application passwords grant access to <a href="%1$s">the %2$s site in this installation that you have permissions on</a>.',
												'Application passwords grant access to <a href="%1$s">all %2$s sites in this installation that you have permissions on</a>.',
												$blogs_count
											);

											if ( is_super_admin( $user_id ) ) {
												/* translators: 1: URL to my-sites.php, 2: Number of sites the user has. */
												$message = _n(
													'Application passwords grant access to <a href="%1$s">the %2$s site on the network as you have Super Admin rights</a>.',
													'Application passwords grant access to <a href="%1$s">all %2$s sites on the network as you have Super Admin rights</a>.',
													$blogs_count
												);
											}

											printf(
												$message,
												admin_url( 'my-sites.php' ),
												number_format_i18n( $blogs_count )
											);
											?>
										</p>
									endif;
								endif;
								?>

									<div class="create-application-password form-wrap">
										<div class="form-field">
											<input type="text" size="30" id="new_application_password_name" name="new_application_password_name" class="input" aria-required="true" aria-describedby="new_application_password_name_desc" spellcheck="false" />
										</div>

										/**
										 * Fires in the create Application Passwords form.
										 *
										 * @since 5.6.0
										 *
										 * @param WP_User $profile_user The current WP_User object.
										 */
										do_action( 'wp_create_application_password_form', $profile_user );
										?>

									</div>
								else :
									wp_admin_notice(
										__( 'Your website appears to use Basic Authentication, which is not currently compatible with Application Passwords.' ),
										array(
											'type' => 'error',
											'additional_classes' => array( 'inline' ),
										)
									);
								endif;
								?>

								<div class="application-passwords-list-table-wrapper">
									$application_passwords_list_table = _get_list_table( 'WP_Application_Passwords_List_Table', array( 'screen' => 'application-passwords-user' ) );
									$application_passwords_list_table->prepare_items();
									$application_passwords_list_table->display();
									?>
								</div>
								<p>
									printf(
										/* translators: %s: Documentation URL. */
										__( 'If this is a development website, you can <a href="%s">set the environment type accordingly</a> to enable application passwords.' ),
										__( 'https://developer.wordpress.org/apis/wp-config-php/#wp-environment-type' )
									);
									?>
								</p>
						</div>

					if ( IS_PROFILE_PAGE ) {
						/**
						 * Fires after the 'About Yourself' settings table on the 'Profile' editing screen.
						 *
						 * The action only fires if the current user is editing their own profile.
						 *
						 * @since 2.0.0
						 *
						 * @param WP_User $profile_user The current WP_User object.
						 */
						do_action( 'show_user_profile', $profile_user );
					} else {
						/**
						 * Fires after the 'About the User' settings table on the 'Edit User' screen.
						 *
						 * @since 2.0.0
						 *
						 * @param WP_User $profile_user The current WP_User object.
						 */
						do_action( 'edit_user_profile', $profile_user );
					}
					?>

					/**
					 * Filters whether to display additional capabilities for the user.
					 *
					 * The 'Additional Capabilities' section will only be enabled if
					 * the number of the user's capabilities exceeds their number of
					 * roles.
					 *
					 * @since 2.8.0
					 *
					 * @param bool    $enable      Whether to display the capabilities. Default true.
					 * @param WP_User $profile_user The current WP_User object.
					 */
					$display_additional_caps = apply_filters( 'additional_capabilities_display', true, $profile_user );
					?>


					<table class="form-table" role="presentation">
						<tr class="user-capabilities-wrap">
							<td>
								$output = '';
								foreach ( $profile_user->caps as $cap => $value ) {
									if ( ! $wp_roles->is_role( $cap ) ) {
										if ( '' !== $output ) {
											$output .= ', ';
										}

										if ( $value ) {
											$output .= $cap;
										} else {
											/* translators: %s: Capability name. */
											$output .= sprintf( __( 'Denied: %s' ), $cap );
										}
									}
								}
								echo $output;
								?>
							</td>
						</tr>
					</table>

				<input type="hidden" name="action" value="update" />


			</form>
		</div>
		break;
}
?>
<script type="text/javascript">
	if (window.location.hash == '#password') {
		document.getElementById('pass1').focus();
	}
</script>

<script type="text/javascript">
	jQuery( function( $ ) {
		var languageSelect = $( '#locale' );
		$( 'form' ).on( 'submit', function() {
			/*
			 * Don't show a spinner for English and installed languages,
			 * as there is nothing to download.
			 */
			if ( ! languageSelect.find( 'option:selected' ).data( 'installed' ) ) {
				$( '#submit', this ).after( '<span class="spinner language-install-spinner is-active" />' );
			}
		});
	} );
</script>

	<script type="text/html" id="tmpl-new-application-password">
		<div class="notice notice-success is-dismissible new-application-password-notice" role="alert">
			<p class="application-password-display">
				<label for="new-application-password-value">
					printf(
						/* translators: %s: Application name. */
						__( 'Your new password for %s is:' ),
						'<strong>{{ data.name }}</strong>'
					);
					?>
				</label>
				<input id="new-application-password-value" type="text" class="code" readonly="readonly" value="{{ data.password }}" />
			</p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text">
					/* translators: Hidden accessibility text. */
					_e( 'Dismiss this notice.' );
					?>
				</span>
			</button>
		</div>
	</script>

	<script type="text/html" id="tmpl-application-password-row">
	</script>
require_once ABSPATH . 'wp-admin/admin-footer.php';

} // namespace CharacterGeneratorDev
