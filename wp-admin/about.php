<?php

namespace CharacterGeneratorDev {

/**
 * About This Version administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';

// Used in the HTML title tag.
/* translators: Page title of the About WordPress page in the admin. */
$title = _x( 'About', 'page title' );

list( $display_version ) = explode( '-', wp_get_wp_version() );
$display_major_version   = '6.8';

$release_notes_url = sprintf(
	/* translators: %s: WordPress version number. */
	__( 'https://wordpress.org/documentation/wordpress-version/version-%s/' ),
	'6-8'
);

$field_guide_url = sprintf(
	/* translators: %s: WordPress version number. */
	__( 'https://make.wordpress.org/core/wordpress-%s-field-guide/' ),
	'6-8'
);

require_once ABSPATH . 'wp-admin/admin-header.php';
?>
	<div class="wrap about__container">

		<div class="about__header">
			<div class="about__header-title">
				<h1>
					printf(
						/* translators: %s: Version number. */
						__( 'WordPress %s' ),
						$display_version
					);
					?>
				</h1>
			</div>
		</div>

		</nav>

		<div class="about__section changelog has-subtle-background-color">
			<div class="column">
				<p>
					printf(
						/* translators: 1: WordPress version number, 2: Plural number of bugs. */
						_n(
							'<strong>Version %1$s</strong> addressed %2$s bug.',
							'<strong>Version %1$s</strong> addressed %2$s bugs.',
							35
						),
						'6.8.2',
						'35'
					);
					?>
					printf(
						/* translators: %s: HelpHub URL. */
						__( 'For more information, see <a href="%s">the release notes</a>.' ),
						sprintf(
							/* translators: %s: WordPress version. */
							esc_url( __( 'https://wordpress.org/support/wordpress-version/version-%s/' ) ),
							sanitize_title( '6.8.2' )
						)
					);
					?>
				</p>
				<p>
					printf(
						/* translators: 1: WordPress version number, 2: Plural number of bugs. */
						_n(
							'<strong>Version %1$s</strong> addressed %2$s bug.',
							'<strong>Version %1$s</strong> addressed %2$s bugs.',
							16
						),
						'6.8.1',
						'15'
					);
					?>
					printf(
						/* translators: %s: HelpHub URL. */
						__( 'For more information, see <a href="%s">the release notes</a>.' ),
						sprintf(
							/* translators: %s: WordPress version. */
							esc_url( __( 'https://wordpress.org/support/wordpress-version/version-%s/' ) ),
							sanitize_title( '6.8.1' )
						)
					);
					?>
				</p>
			</div>
		</div>

		<div class="about__section has-1-column">
			<div class="column">
			</div>
		</div>

		<div class="about__section has-2-columns">
			<div class="column is-vertically-aligned-center">
				<p>
				</p>
					<p>
						if ( current_user_can( 'edit_theme_options' ) && ( current_theme_supports( 'editor-styles' ) || wp_theme_has_theme_json() ) ) {
							printf(
								/* translators: %s is a direct link to the Style Book. */
								__( 'Plus, now you can see it in Classic themes that have editor-styles or a theme.json file. Find <a href="%s">the Style Book</a> under Appearance > Design and use it to preview your theme&#8217;s evolution, as you edit CSS or make changes in the Customizer.' ),
								add_query_arg( 'p', '/stylebook', admin_url( '/site-editor.php' ) )
							);
						} else {
							_e( 'Plus, now you can see it in Classic themes that have editor-styles or a theme.json file. Find the Style Book under Appearance > Design and use it to preview your theme&#8217;s evolution, as you edit CSS or make changes in the Customizer.' );
						}
						?>
					</p>
			</div>
			<div class="column is-vertically-aligned-center">
				<div class="about__image">
					<img src="https://s.w.org/images/core/6.8/feature-01.webp?v=23478" alt="" height="436" width="436" />
				</div>
			</div>
		</div>

		<div class="about__section has-2-columns">
			<div class="column is-vertically-aligned-center">
				<div class="about__image">
					<img src="https://s.w.org/images/core/6.8/feature-02.png?v=23478" alt="" height="436" width="436" />
				</div>
			</div>
			<div class="column is-vertically-aligned-center">
			</div>
		</div>

		<div class="about__section has-2-columns">
			<div class="column is-vertically-aligned-center">
			</div>
			<div class="column is-vertically-aligned-center">
				<div class="about__image">
					<img src="https://s.w.org/images/core/6.8/feature-03.webp?v=23478" alt="" height="436" width="436" />
				</div>
			</div>
		</div>

		<div class="about__section has-2-columns">
			<div class="column is-vertically-aligned-center">
				<div class="about__image">
					<img src="https://s.w.org/images/core/6.8/feature-04.png?v=23478" alt="" height="436" width="436" />
				</div>
			</div>
			<div class="column is-vertically-aligned-center">
			</div>
		</div>

		<hr class="is-invisible is-large" />

		<div class="about__section has-2-columns">
			<div class="column">
				<div class="about__image">
					<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path fill="#1e1e1e" d="M24 13.84c-.752 0-1.397-.287-1.936-.86a2.902 2.902 0 0 1-.809-2.06c0-.8.27-1.487.809-2.06S23.248 8 24 8c.753 0 1.398.287 1.937.86.54.573.809 1.26.809 2.06s-.27 1.487-.809 2.06-1.184.86-1.937.86ZM19.976 40V18.68a69.562 69.562 0 0 1-4.945-.56 45.877 45.877 0 0 1-4.57-.92l.565-2.4a46.79 46.79 0 0 0 6.356 1.14c2.106.227 4.312.34 6.618.34 2.307 0 4.513-.113 6.62-.34a46.786 46.786 0 0 0 6.355-1.14l.564 2.4c-1.454.373-2.977.68-4.57.92a69.55 69.55 0 0 1-4.945.56V40h-2.256V29.6h-3.535V40h-2.257Z"/>
					</svg>
				</div>
			</div>
			<div class="column">
				<div class="about__image">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path fill="#1e1e1e" d="M18.1823 11.6392C18.1823 13.0804 17.0139 14.2487 15.5727 14.2487C14.3579 14.2487 13.335 13.4179 13.0453 12.2922L13.0377 12.2625L13.0278 12.2335L12.3985 10.377L12.3942 10.3785C11.8571 8.64997 10.246 7.39405 8.33961 7.39405C5.99509 7.39405 4.09448 9.29465 4.09448 11.6392C4.09448 13.9837 5.99509 15.8843 8.33961 15.8843C8.88499 15.8843 9.40822 15.781 9.88943 15.5923L9.29212 14.0697C8.99812 14.185 8.67729 14.2487 8.33961 14.2487C6.89838 14.2487 5.73003 13.0804 5.73003 11.6392C5.73003 10.1979 6.89838 9.02959 8.33961 9.02959C9.55444 9.02959 10.5773 9.86046 10.867 10.9862L10.8772 10.9836L11.4695 12.7311C11.9515 14.546 13.6048 15.8843 15.5727 15.8843C17.9172 15.8843 19.8178 13.9837 19.8178 11.6392C19.8178 9.29465 17.9172 7.39404 15.5727 7.39404C15.0287 7.39404 14.5066 7.4968 14.0264 7.6847L14.6223 9.20781C14.9158 9.093 15.2358 9.02959 15.5727 9.02959C17.0139 9.02959 18.1823 10.1979 18.1823 11.6392Z"></path>
					</svg>
				</div>
			</div>
		</div>

		<div class="about__section has-1-column">
			<div class="column">
				<div class="about__image">
					<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path fill="#1e1e1e" d="M32.455 17.72a1.592 1.592 0 0 1 .599 2.195l-7.637 12.99a1.653 1.653 0 0 1-2.235.589 1.592 1.592 0 0 1-.599-2.195l7.637-12.99a1.653 1.653 0 0 1 2.235-.589ZM13.774 23.21a1.653 1.653 0 0 0-2.236.589 1.592 1.592 0 0 0 .6 2.195l.944.536c.783.444 1.783.18 2.235-.588a1.592 1.592 0 0 0-.599-2.196l-.944-.535ZM16.432 17.72a1.653 1.653 0 0 1 2.236.588l.545.928a1.592 1.592 0 0 1-.599 2.196 1.653 1.653 0 0 1-2.235-.588l-.546-.928a1.592 1.592 0 0 1 .6-2.196ZM25.637 16.5c0-.888-.733-1.607-1.637-1.607s-1.636.72-1.636 1.607v1.071c0 .888.732 1.608 1.636 1.608.904 0 1.637-.72 1.637-1.608V16.5Z"/>
						<path fill="#1e1e1e" fill-rule="evenodd" d="M4.91 27.75C4.91 17.395 13.455 9 24 9s19.091 8.395 19.091 18.75c0 3.909-1.22 7.542-3.305 10.548l-.488.702H8.702l-.488-.702A18.438 18.438 0 0 1 4.91 27.75ZM24 12.214c-8.736 0-15.818 6.956-15.818 15.536 0 2.943.832 5.692 2.277 8.036h27.082a15.25 15.25 0 0 0 2.277-8.036c0-8.58-7.082-15.536-15.818-15.536Z" clip-rule="evenodd"/>
					</svg>
				</div>
			</div>
		</div>

		<hr class="is-invisible is-large" style="margin-bottom:calc(2 * var(--gap));" />

		<div class="about__section has-2-columns is-wider-left is-feature" style="background-color:var(--background);border-radius:var(--border-radius);">
			<div class="column">
				<p>
					printf(
						/* translators: %s: Version number. */
						__( 'For a comprehensive overview of all the new features and enhancements in WordPress %s, please visit the feature-showcase website.' ),
						$display_major_version
					);
					?>
				</p>
			</div>
			<div class="column aligncenter">
				<div class="about__image">
				</div>
			</div>
		</div>

		<hr class="is-large" style="margin-top:calc(2 * var(--gap));" />

		<div class="about__section has-3-columns">
			<div class="column about__image is-vertically-aligned-top">
			</div>
			<div class="column is-vertically-aligned-center" style="grid-column-end:span 2">
				<h3>
					printf(
						/* translators: %s: Version number. */
						__( 'Learn more about WordPress %s' ),
						$display_major_version
					);
					?>
				</h3>
				<p>
					printf(
						/* translators: 1: Learn WordPress link, 2: Workshops link. */
						__( '<a href="%1$s">Learn WordPress</a> is a free resource for new and experienced WordPress users. Learn is stocked with how-to videos on using various features in WordPress, <a href="%2$s">interactive workshops</a> for exploring topics in-depth, and lesson plans for diving deep into specific areas of WordPress.' ),
						'https://learn.wordpress.org/',
						'https://learn.wordpress.org/online-workshops/'
					);
					?>
				</p>
			</div>
		</div>

		<div class="about__section has-2-columns">
			<div class="column">
				<div class="about__image">
					<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path fill="#1e1e1e" d="M32 15.5H16v3h16v-3ZM16 22h16v3H16v-3ZM28 28.5H16v3h12v-3Z"/>
						<path fill="#1e1e1e" fill-rule="evenodd" d="M34 8H14a4 4 0 0 0-4 4v24a4 4 0 0 0 4 4h20a4 4 0 0 0 4-4V12a4 4 0 0 0-4-4Zm-20 3h20a1 1 0 0 1 1 1v24a1 1 0 0 1-1 1H14a1 1 0 0 1-1-1V12a1 1 0 0 1 1-1Z" clip-rule="evenodd"/>
					</svg>
				</div>
				<h4 style="margin-top: calc(var(--gap) / 2); margin-bottom: calc(var(--gap) / 2);">
						printf(
							/* translators: %s: WordPress version number. */
							__( 'WordPress %s Release Notes' ),
							$display_major_version
						);
						?>
					</a>
				</h4>
				<p>
					printf(
						/* translators: %s: WordPress version number. */
						__( 'Read the WordPress %s Release Notes for information on installation, enhancements, fixed issues, release contributors, learning resources, and the list of file changes.' ),
						$display_major_version
					);
					?>
				</p>
			</div>
			<div class="column">
				<div class="about__image">
					<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path fill="#1e1e1e" stroke="#fff" stroke-width=".5" d="M26.5 24.25h13.75v11.5h-14v8h-3.5v-8H12.604L8.09 31.237a1.75 1.75 0 0 1 0-2.474l4.513-4.513H22.75v-4.5h-14V8.25h14v-4h3.5v4h10.146l4.513 4.513a1.75 1.75 0 0 1 0 2.474l-4.513 4.513H26.25v4.5h.25ZM12.25 16v.25h22.704l.073-.073 1.293-1.293a1.25 1.25 0 0 0 0-1.768l-1.293-1.293-.073-.073H12.25V16Zm1.723 16.177.073.073H36.75v-4.5H14.046l-.073.073-1.293 1.293a1.25 1.25 0 0 0 0 1.768l1.293 1.293Z"/>
					</svg>
				</div>
				<h4 style="margin-top: calc(var(--gap) / 2); margin-bottom: calc(var(--gap) / 2);">
						printf(
							/* translators: %s: WordPress version number. */
							__( 'WordPress %s Field Guide' ),
							$display_major_version
						);
						?>
					</a>
				</h4>
				<p>
					printf(
						/* translators: %s: WordPress version number. */
						__( 'Explore the WordPress %s Field Guide. Learn about the changes in this release with detailed developer notes to help you build with WordPress.' ),
						$display_major_version
					);
					?>
				</p>
			</div>
		</div>

		<hr class="is-large" />

		<div class="return-to-dashboard">
			if ( isset( $_GET['updated'] ) && current_user_can( 'update_core' ) ) {
				printf(
					'<a href="%1$s">%2$s</a> | ',
					esc_url( self_admin_url( 'update-core.php' ) ),
					is_multisite() ? __( 'Go to Updates' ) : __( 'Go to Dashboard &rarr; Updates' )
				);
			}

			printf(
				'<a href="%1$s">%2$s</a>',
				esc_url( self_admin_url() ),
				is_blog_admin() ? __( 'Go to Dashboard &rarr; Home' ) : __( 'Go to Dashboard' )
			);
			?>
		</div>
	</div>



// These are strings we may use to describe maintenance/security releases, where we aim for no new strings.
return;

__( 'Maintenance Release' );
__( 'Maintenance Releases' );

__( 'Security Release' );
__( 'Security Releases' );

__( 'Maintenance and Security Release' );
__( 'Maintenance and Security Releases' );

/* translators: %s: WordPress version number. */
__( '<strong>Version %s</strong> addressed one security issue.' );
/* translators: %s: WordPress version number. */
__( '<strong>Version %s</strong> addressed some security issues.' );

/* translators: 1: WordPress version number, 2: Plural number of bugs. */
_n_noop(
	'<strong>Version %1$s</strong> addressed %2$s bug.',
	'<strong>Version %1$s</strong> addressed %2$s bugs.'
);

/* translators: 1: WordPress version number, 2: Plural number of bugs. Singular security issue. */
_n_noop(
	'<strong>Version %1$s</strong> addressed a security issue and fixed %2$s bug.',
	'<strong>Version %1$s</strong> addressed a security issue and fixed %2$s bugs.'
);

/* translators: 1: WordPress version number, 2: Plural number of bugs. More than one security issue. */
_n_noop(
	'<strong>Version %1$s</strong> addressed some security issues and fixed %2$s bug.',
	'<strong>Version %1$s</strong> addressed some security issues and fixed %2$s bugs.'
);

/* translators: %s: Documentation URL. */
__( 'For more information, see <a href="%s">the release notes</a>.' );

/* translators: 1: WordPress version number, 2: Link to update WordPress */
__( 'Important! Your version of WordPress (%1$s) is no longer supported, you will not receive any security updates for your website. To keep your site secure, please <a href="%2$s">update to the latest version of WordPress</a>.' );

/* translators: 1: WordPress version number, 2: Link to update WordPress */
__( 'Important! Your version of WordPress (%1$s) will stop receiving security updates in the near future. To keep your site secure, please <a href="%2$s">update to the latest version of WordPress</a>.' );

/* translators: %s: The major version of WordPress for this branch. */
__( 'This is the final release of WordPress %s' );

/* translators: The localized WordPress download URL. */
__( 'https://wordpress.org/download/' );

} // namespace CharacterGeneratorDev
