<?php

namespace CharacterGeneratorDev {


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Welcome page class.
 *
 * This page is shown when the plugin is activated.
 *
 * @since 1.0.0
 */
class WPForms_Welcome {

	/**
	 * Hidden welcome page slug.
	 *
	 * @since 1.5.6
	 */
	const SLUG = 'wpforms-getting-started';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'plugins_loaded', [ $this, 'hooks' ] );
	}

	/**
	 * Register all WP hooks.
	 *
	 * @since 1.5.6
	 */
	public function hooks() {

		// If user is in admin ajax or doing cron, return.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// If user cannot manage_options, return.
		if ( ! wpforms_current_user_can() ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'register' ] );
		add_action( 'admin_head', [ $this, 'hide_menu' ] );
		add_action( 'admin_init', [ $this, 'redirect' ], 9999 );
	}

	/**
	 * Register the pages to be used for the Welcome screen (and tabs).
	 *
	 * These pages will be removed from the Dashboard menu, so they will
	 * not actually show. Sneaky, sneaky.
	 *
	 * @since 1.0.0
	 */
	public function register() {

		// Getting started - shows after installation.
		add_dashboard_page(
			esc_html__( 'Welcome to WPForms', 'wpforms-lite' ),
			esc_html__( 'Welcome to WPForms', 'wpforms-lite' ),
			apply_filters( 'wpforms_welcome_cap', wpforms_get_capability_manage_options() ),
			self::SLUG,
			[ $this, 'output' ]
		);
	}

	/**
	 * Removed the dashboard pages from the admin menu.
	 *
	 * This means the pages are still available to us, but hidden.
	 *
	 * @since 1.0.0
	 */
	public function hide_menu() {

		remove_submenu_page( 'index.php', self::SLUG );
	}

	/**
	 * Welcome screen redirect.
	 *
	 * This function checks if a new install or update has just occurred. If so,
	 * then we redirect the user to the appropriate page.
	 *
	 * @since 1.0.0
	 */
	public function redirect() {

		// Check if we should consider redirection.
		if ( ! get_transient( 'wpforms_activation_redirect' ) ) {
			return;
		}

		// If we are redirecting, clear the transient so it only happens once.
		delete_transient( 'wpforms_activation_redirect' );

		// Check option to disable welcome redirect.
		if ( get_option( 'wpforms_activation_redirect', false ) ) {
			return;
		}

		// Only do this for single site installs.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) {
			return;
		}

		// Check if this is an update or first install.
		$upgrade = get_option( 'wpforms_version_upgraded_from' );

		if ( ! $upgrade ) {
			// Initial install.
			wp_safe_redirect( admin_url( 'index.php?page=' . self::SLUG ) );
			exit;
		}
	}

	/**
	 * Getting Started screen. Shows after first install.
	 *
	 * @since 1.0.0
	 */
	public function output() {

		$class = wpforms()->is_pro() ? 'pro' : 'lite';
		?>


			<div class="container">

				<div class="intro">

					<div class="sullie">
					</div>

					<div class="block">
					</div>

					</a>

					<div class="block">


						<div class="button-wrap wpforms-clear">
							<div class="left">
								</a>
							</div>
							<div class="right">
									class="wpforms-btn wpforms-btn-block wpforms-btn-lg wpforms-btn-grey" target="_blank" rel="noopener noreferrer">
								</a>
							</div>
						</div>

					</div>

				</div><!-- /.intro -->


				<div class="features">

					<div class="block">


						<div class="feature-list wpforms-clear">

							<div class="feature-block first">
							</div>

							<div class="feature-block last">
							</div>

							<div class="feature-block first">
							</div>

							<div class="feature-block last">
							</div>

							<div class="feature-block first">
							</div>

							<div class="feature-block last">
							</div>

							<div class="feature-block first">
							</div>

							<div class="feature-block last">
							</div>

							<div class="feature-block first">
							</div>

							<div class="feature-block last">
							</div>

						</div>

						<div class="button-wrap">
								class="wpforms-btn wpforms-btn-lg wpforms-btn-grey" rel="noopener noreferrer" target="_blank">
							</a>
						</div>

					</div>

				</div><!-- /.features -->

				<div class="upgrade-cta upgrade">

					<div class="block wpforms-clear">

						<div class="left">
							<ul>
							</ul>
						</div>

						<div class="right">
							<h2><span>PRO</span></h2>
							<div class="price">
								<span class="amount">199</span><br>
							</div>
								class="wpforms-btn wpforms-btn-block wpforms-btn-lg wpforms-btn-orange wpforms-upgrade-modal">
							</a>
						</div>

					</div>

				</div>

				<div class="testimonials upgrade">

					<div class="block">


						<div class="testimonial-block wpforms-clear">
							<p>
							<p><strong>Bill Erickson</strong>, Erickson Web Consulting</p>
						</div>

						<div class="testimonial-block wpforms-clear">
							<p>
							<p><strong>David Henzel</strong>, MaxCDN</p>
						</div>

					</div>

				</div><!-- /.testimonials -->

				<div class="footer">

					<div class="block wpforms-clear">

						<div class="button-wrap wpforms-clear">
							<div class="left">
									class="wpforms-btn wpforms-btn-block wpforms-btn-lg wpforms-btn-orange">
								</a>
							</div>
							<div class="right">
									class="wpforms-btn wpforms-btn-block wpforms-btn-lg wpforms-btn-trans-green wpforms-upgrade-modal">
									<span class="underline">
									</span>
								</a>
							</div>
						</div>

					</div>

				</div><!-- /.footer -->

			</div><!-- /.container -->

		</div><!-- /#wpforms-welcome -->
	}
}

new WPForms_Welcome();

} // namespace CharacterGeneratorDev
