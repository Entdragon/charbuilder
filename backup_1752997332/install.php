<?php

namespace CharacterGeneratorDev {

/**
 * WordPress Installer
 *
 * @package WordPress
 * @subpackage Administration
 */

// Confidence check.
if ( false ) {
	?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Error: PHP is not running</title>
</head>
<body class="wp-core-ui">
	<p id="logo"><a href="https://wordpress.org/">WordPress</a></p>
	<h1>Error: PHP is not running</h1>
	<p>WordPress requires that your web server is running PHP. Your server does not have PHP installed, or PHP is turned off.</p>
</body>
</html>
}

/**
 * We are installing WordPress.
 *
 * @since 1.5.1
 * @var bool
 */
define( 'WP_INSTALLING', true );

/** Load WordPress Bootstrap */
require_once dirname( __DIR__ ) . '/wp-load.php';

/** Load WordPress Administration Upgrade API */
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/** Load WordPress Translation Install API */
require_once ABSPATH . 'wp-admin/includes/translation-install.php';

/** Load wpdb */
require_once ABSPATH . WPINC . '/class-wpdb.php';

nocache_headers();

$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : 0;

/**
 * Display installation header.
 *
 * @since 2.5.0
 *
 * @param string $body_classes
 */
function CG_Dev\\display_header( $body_classes = '' ) {
	header( 'Content-Type: text/html; charset=utf-8' );
	if ( is_rtl() ) {
		$body_classes .= 'rtl';
	}
	if ( $body_classes ) {
		$body_classes = ' ' . $body_classes;
	}
	?>
<!DOCTYPE html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow" />
</head>

} // End display_header().

/**
 * Displays installer setup form.
 *
 * @since 2.8.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string|null $error
 */
function CG_Dev\\display_setup_form( $error = null ) {
	global $wpdb;

	$user_table = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->users ) ) ) !== null );

	// Ensure that sites appear in search engines by default.
	$blog_public = 1;
	if ( isset( $_POST['weblog_title'] ) ) {
		$blog_public = isset( $_POST['blog_public'] ) ? (int) $_POST['blog_public'] : $blog_public;
	}

	$weblog_title = isset( $_POST['weblog_title'] ) ? trim( wp_unslash( $_POST['weblog_title'] ) ) : '';
	$user_name    = isset( $_POST['user_name'] ) ? trim( wp_unslash( $_POST['user_name'] ) ) : '';
	$admin_email  = isset( $_POST['admin_email'] ) ? trim( wp_unslash( $_POST['admin_email'] ) ) : '';

	if ( ! is_null( $error ) ) {
		?>
<form id="setup" method="post" action="install.php?step=2" novalidate="novalidate">
	<table class="form-table" role="presentation">
		<tr>
		</tr>
		<tr>
			<td>
			if ( $user_table ) {
				_e( 'User(s) already exists.' );
				echo '<input name="user_name" type="hidden" value="admin" />';
			} else {
				?>
			}
			?>
			</td>
		</tr>
		<tr class="form-field form-required user-pass1-wrap">
			<th scope="row">
				<label for="pass1">
				</label>
			</th>
			<td>
				<div class="wp-pwd">
					<div class="password-input-wrapper">
						<div id="pass-strength-result" aria-live="polite"></div>
					</div>
						<span class="dashicons dashicons-hidden"></span>
					</button>
				</div>
				<p id="admin-password-desc"><span class="description important hide-if-no-js">
			</td>
		</tr>
		<tr class="form-field form-required user-pass2-wrap hide-if-js">
			<th scope="row">
				</label>
			</th>
			<td>
				<input type="password" name="admin_password2" id="pass2" autocomplete="new-password" spellcheck="false" />
			</td>
		</tr>
		<tr class="pw-weak">
			<td>
				<label>
					<input type="checkbox" name="pw_weak" class="pw-checkbox" />
				</label>
			</td>
		</tr>
		<tr>
		</tr>
		<tr>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span>
						has_action( 'blog_privacy_selector' )
							/* translators: Hidden accessibility text. */
							? _e( 'Site visibility' )
							/* translators: Hidden accessibility text. */
							: _e( 'Search engine visibility' );
						?>
					</span></legend>
					if ( has_action( 'blog_privacy_selector' ) ) {
						?>
						/** This action is documented in wp-admin/options-reading.php */
						do_action( 'blog_privacy_selector' );
					} else {
						?>
				</fieldset>
			</td>
		</tr>
	</table>
</form>
} // End display_setup_form().

// Let's check to make sure WP isn't already installed.
if ( is_blog_installed() ) {
	display_header();
	die(
		'<h1>' . __( 'Already Installed' ) . '</h1>' .
		'<p>' . __( 'You appear to have already installed WordPress. To reinstall please clear your old database tables first.' ) . '</p>' .
		'<p class="step"><a href="' . esc_url( wp_login_url() ) . '">' . __( 'Log In' ) . '</a></p>' .
		'</body></html>'
	);
}

/**
 * @global string   $wp_version              The WordPress version string.
 * @global string   $required_php_version    The required PHP version string.
 * @global string[] $required_php_extensions The names of required PHP extensions.
 * @global string   $required_mysql_version  The required MySQL version string.
 * @global wpdb     $wpdb                    WordPress database abstraction object.
 */
global $wp_version, $required_php_version, $required_php_extensions, $required_mysql_version, $wpdb;

$php_version   = PHP_VERSION;
$mysql_version = $wpdb->db_version();
$php_compat    = version_compare( $php_version, $required_php_version, '>=' );
$mysql_compat  = version_compare( $mysql_version, $required_mysql_version, '>=' ) || file_exists( WP_CONTENT_DIR . '/db.php' );

$version_url = sprintf(
	/* translators: %s: WordPress version. */
	esc_url( __( 'https://wordpress.org/documentation/wordpress-version/version-%s/' ) ),
	sanitize_title( $wp_version )
);

$php_update_message = '</p><p>' . sprintf(
	/* translators: %s: URL to Update PHP page. */
	__( '<a href="%s">Learn more about updating PHP</a>.' ),
	esc_url( wp_get_update_php_url() )
);

$annotation = wp_get_update_php_annotation();

if ( $annotation ) {
	$php_update_message .= '</p><p><em>' . $annotation . '</em>';
}

if ( ! $mysql_compat && ! $php_compat ) {
	$compat = sprintf(
		/* translators: 1: URL to WordPress release notes, 2: WordPress version number, 3: Minimum required PHP version number, 4: Minimum required MySQL version number, 5: Current PHP version number, 6: Current MySQL version number. */
		__( 'You cannot install because <a href="%1$s">WordPress %2$s</a> requires PHP version %3$s or higher and MySQL version %4$s or higher. You are running PHP version %5$s and MySQL version %6$s.' ),
		$version_url,
		$wp_version,
		$required_php_version,
		$required_mysql_version,
		$php_version,
		$mysql_version
	) . $php_update_message;
} elseif ( ! $php_compat ) {
	$compat = sprintf(
		/* translators: 1: URL to WordPress release notes, 2: WordPress version number, 3: Minimum required PHP version number, 4: Current PHP version number. */
		__( 'You cannot install because <a href="%1$s">WordPress %2$s</a> requires PHP version %3$s or higher. You are running version %4$s.' ),
		$version_url,
		$wp_version,
		$required_php_version,
		$php_version
	) . $php_update_message;
} elseif ( ! $mysql_compat ) {
	$compat = sprintf(
		/* translators: 1: URL to WordPress release notes, 2: WordPress version number, 3: Minimum required MySQL version number, 4: Current MySQL version number. */
		__( 'You cannot install because <a href="%1$s">WordPress %2$s</a> requires MySQL version %3$s or higher. You are running version %4$s.' ),
		$version_url,
		$wp_version,
		$required_mysql_version,
		$mysql_version
	);
}

if ( ! $mysql_compat || ! $php_compat ) {
	display_header();
	die( '<h1>' . __( 'Requirements Not Met' ) . '</h1><p>' . $compat . '</p></body></html>' );
}

if ( isset( $required_php_extensions ) && is_array( $required_php_extensions ) ) {
	$missing_extensions = array();

	foreach ( $required_php_extensions as $extension ) {
		if ( extension_loaded( $extension ) ) {
			continue;
		}

		$missing_extensions[] = sprintf(
			/* translators: 1: URL to WordPress release notes, 2: WordPress version number, 3: The PHP extension name needed. */
			__( 'You cannot install because <a href="%1$s">WordPress %2$s</a> requires the %3$s PHP extension.' ),
			$version_url,
			$wp_version,
			$extension
		);
	}

	if ( count( $missing_extensions ) > 0 ) {
		display_header();
		die( '<h1>' . __( 'Requirements Not Met' ) . '</h1><p>' . implode( '</p><p>', $missing_extensions ) . '</p></body></html>' );
	}
}

if ( ! is_string( $wpdb->base_prefix ) || '' === $wpdb->base_prefix ) {
	display_header();
	die(
		'<h1>' . __( 'Configuration Error' ) . '</h1>' .
		'<p>' . sprintf(
			/* translators: %s: wp-config.php */
			__( 'Your %s file has an empty database table prefix, which is not supported.' ),
			'<code>wp-config.php</code>'
		) . '</p></body></html>'
	);
}

// Set error message if DO_NOT_UPGRADE_GLOBAL_TABLES isn't set as it will break install.
if ( defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) {
	display_header();
	die(
		'<h1>' . __( 'Configuration Error' ) . '</h1>' .
		'<p>' . sprintf(
			/* translators: %s: DO_NOT_UPGRADE_GLOBAL_TABLES */
			__( 'The constant %s cannot be defined when installing WordPress.' ),
			'<code>DO_NOT_UPGRADE_GLOBAL_TABLES</code>'
		) . '</p></body></html>'
	);
}

/**
 * @global string    $wp_local_package Locale code of the package.
 * @global WP_Locale $wp_locale        WordPress date and time locale object.
 */
$language = '';
if ( ! empty( $_REQUEST['language'] ) ) {
	$language = sanitize_locale_name( $_REQUEST['language'] );
} elseif ( isset( $GLOBALS['wp_local_package'] ) ) {
	$language = $GLOBALS['wp_local_package'];
}

$scripts_to_print = array( 'jquery' );

switch ( $step ) {
	case 0: // Step 0.
		if ( wp_can_install_language_pack() && empty( $language ) ) {
			$languages = wp_get_available_translations();
			if ( $languages ) {
				$scripts_to_print[] = 'language-chooser';
				display_header( 'language-chooser' );
				echo '<form id="setup" method="post" action="?step=1">';
				wp_install_language_form( $languages );
				echo '</form>';
				break;
			}
		}

		// Deliberately fall through if we can't reach the translations API.

	case 1: // Step 1, direct link or from language chooser.
		if ( ! empty( $language ) ) {
			$loaded_language = wp_download_language_pack( $language );
			if ( $loaded_language ) {
				load_default_textdomain( $loaded_language );
				$GLOBALS['wp_locale'] = new WP_Locale();
			}
		}

		$scripts_to_print[] = 'user-profile';

		display_header();
		?>


		display_setup_form();
		break;
	case 2:
		if ( ! empty( $language ) && load_default_textdomain( $language ) ) {
			$loaded_language      = $language;
			$GLOBALS['wp_locale'] = new WP_Locale();
		} else {
			$loaded_language = 'en_US';
		}

		if ( ! empty( $wpdb->error ) ) {
			wp_die( $wpdb->error->get_error_message() );
		}

		$scripts_to_print[] = 'user-profile';

		display_header();
		// Fill in the data we gathered.
		$weblog_title         = isset( $_POST['weblog_title'] ) ? trim( wp_unslash( $_POST['weblog_title'] ) ) : '';
		$user_name            = isset( $_POST['user_name'] ) ? trim( wp_unslash( $_POST['user_name'] ) ) : '';
		$admin_password       = isset( $_POST['admin_password'] ) ? wp_unslash( $_POST['admin_password'] ) : '';
		$admin_password_check = isset( $_POST['admin_password2'] ) ? wp_unslash( $_POST['admin_password2'] ) : '';
		$admin_email          = isset( $_POST['admin_email'] ) ? trim( wp_unslash( $_POST['admin_email'] ) ) : '';
		$public               = isset( $_POST['blog_public'] ) ? (int) $_POST['blog_public'] : 1;

		// Check email address.
		$error = false;
		if ( empty( $user_name ) ) {
			// TODO: Poka-yoke.
			display_setup_form( __( 'Please provide a valid username.' ) );
			$error = true;
		} elseif ( sanitize_user( $user_name, true ) !== $user_name ) {
			display_setup_form( __( 'The username you provided has invalid characters.' ) );
			$error = true;
		} elseif ( $admin_password !== $admin_password_check ) {
			// TODO: Poka-yoke.
			display_setup_form( __( 'Your passwords do not match. Please try again.' ) );
			$error = true;
		} elseif ( empty( $admin_email ) ) {
			// TODO: Poka-yoke.
			display_setup_form( __( 'You must provide an email address.' ) );
			$error = true;
		} elseif ( ! is_email( $admin_email ) ) {
			// TODO: Poka-yoke.
			display_setup_form( __( 'Sorry, that is not a valid email address. Email addresses look like <code>username@example.com</code>.' ) );
			$error = true;
		}

		if ( false === $error ) {
			$wpdb->show_errors();
			$result = wp_install( $weblog_title, $user_name, $admin_email, $public, '', wp_slash( $admin_password ), $loaded_language );
			?>



<table class="form-table install-success">
	<tr>
	</tr>
	<tr>
		<td>
		</td>
	</tr>
</table>


		}
		break;
}

if ( ! wp_is_mobile() ) {
	?>
<script type="text/javascript">var t = document.getElementById('weblog_title'); if (t){ t.focus(); }</script>
}

wp_print_scripts( $scripts_to_print );
?>
<script type="text/javascript">
jQuery( function( $ ) {
	$( '.hide-if-no-js' ).removeClass( 'hide-if-no-js' );
} );
</script>
</body>
</html>

} // namespace CharacterGeneratorDev
