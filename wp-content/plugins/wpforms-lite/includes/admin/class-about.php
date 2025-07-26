<?php

namespace CharacterGeneratorDev {


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * About WPForms admin page class.
 *
 * @since 1.5.0
 */
class WPForms_About {

	/**
	 * Admin menu page slug.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	const SLUG = 'wpforms-about';

	/**
	 * Default view for a page.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	const DEFAULT_TAB = 'about';

	/**
	 * Array of license types, that are considered being top level and has no features difference.
	 *
	 * @since 1.5.0
	 *
	 * @var array
	 */
	public static $licenses_top = [ 'pro', 'agency', 'ultimate', 'elite' ];

	/**
	 * List of features that licenses are different with.
	 *
	 * @since 1.5.0
	 *
	 * @var array
	 */
	public static $licenses_features = [];

	/**
	 * The current active tab.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	public $view;

	/**
	 * The core views.
	 *
	 * @since 1.5.0
	 *
	 * @var array
	 */
	public $views = [];

	/**
	 * Primary class constructor.
	 *
	 * @since 1.5.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.2.3
	 */
	private function hooks() {

		// Maybe load tools page.
		add_action( 'admin_init', [ $this, 'init' ] );
	}

	/**
	 * Determining if the user is viewing our page, if so, party on.
	 *
	 * @since 1.5.0
	 */
	public function init() {

		// Check what page we are on.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		// Only load if we are actually on the settings page.
		if ( $page !== self::SLUG ) {
			return;
		}

		/*
		 * Define the core views for our tab.
		 */
		$this->views = apply_filters(
			'wpforms_admin_about_views',
			[
				esc_html__( 'About Us', 'wpforms-lite' )        => [ 'about' ],
				esc_html__( 'Getting Started', 'wpforms-lite' ) => [ 'getting-started' ],
			]
		);

		$license = $this->get_license_type();

		if (
			(
				$license === 'pro' ||
				! in_array( $license, self::$licenses_top, true )
			) ||
			wpforms_debug()
		) {
			$vs_tab_name = sprintf( /* translators: %1$s - current license type, %2$s - suggested license type. */
				esc_html__( '%1$s vs %2$s', 'wpforms-lite' ),
				ucfirst( $license ),
				$this->get_next_license( $license )
			);

			$this->views[ $vs_tab_name ] = [ 'versus' ];
		}

		// Determine the current active settings tab.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->view = ! empty( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : self::DEFAULT_TAB;

		// If the user tries to load an invalid view - fallback to About Us.
		if (
			! in_array( $this->view, call_user_func_array( 'array_merge', array_values( $this->views ) ), true ) &&
			! has_action( 'wpforms_admin_about_display_tab_' . sanitize_key( $this->view ) )
		) {
			$this->view = self::DEFAULT_TAB;
		}

		add_action( 'wpforms_admin_page', [ $this, 'output' ] );

		// Hook for addons.
		do_action( 'wpforms_admin_about_init' );
	}

	/**
	 * Output the basic page structure.
	 *
	 * @since 1.5.0
	 */
	public function output() {

		$show_nav = false;
		foreach ( $this->views as $view ) {
			if ( in_array( $this->view, (array) $view, true ) ) {
				$show_nav = true;
				break;
			}
		}
		?>

		<div id="wpforms-admin-about" class="wrap wpforms-admin-wrap">

			if ( $show_nav ) {
				$license      = $this->get_license_type();
				$next_license = $this->get_next_license( $license );
				echo '<ul class="wpforms-admin-tabs">';
				foreach ( $this->views as $label => $view ) {
					$class = in_array( $this->view, $view, true ) ? 'active' : '';
					echo '<li>';
					printf(
						'<a href="%s" class="%s">%s</a>',
						esc_url( admin_url( 'admin.php?page=' . self::SLUG . '&view=' . sanitize_key( $view[0] ) ) ),
						esc_attr( $class ),
						esc_html( $label )
					);
					echo '</li>';
				}
				echo '</ul>';
			}
			?>

			<h1 class="wpforms-h1-placeholder"></h1>


			switch ( $this->view ) {
				case 'about':
					$this->output_about();
					break;

				case 'getting-started':
					$this->output_getting_started();
					break;

				case 'versus':
					$this->output_versus();
					break;

				default:
					do_action( 'wpforms_admin_about_display_tab_' . sanitize_key( $this->view ) );
					break;
			}

			?>

		</div>

	}

	/**
	 * Display the About tab content.
	 *
	 * @since 1.5.0
	 */
	protected function output_about() {

		$this->output_about_info();
		$this->output_about_addons();
	}

	/**
	 * Display the General Info section of About tab.
	 *
	 * @since 1.5.8
	 */
	protected function output_about_info() {

		?>

		<div class="wpforms-admin-about-section wpforms-admin-columns">

			<div class="wpforms-admin-column-60">
				<h3>
				</h3>
				<p>
				</p>
				<p>
				</p>
				<p>
					printf(
						wp_kses( /* translators: %1$s - WPBeginner URL, %2$s - OptinMonster URL, %3$s - MonsterInsights URL. */
							__( 'WPForms is brought to you by the same team that’s behind the largest WordPress resource site, <a href="%1$s" target="_blank" rel="noopener noreferrer">WPBeginner</a>, the most popular lead-generation software, <a href="%2$s" target="_blank" rel="noopener noreferrer">OptinMonster</a>, the best WordPress analytics plugin, <a href="%3$s" target="_blank" rel="noopener noreferrer">MonsterInsights</a>, and more!', 'wpforms-lite' ),
							[
								'a' => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						'https://www.wpbeginner.com/?utm_source=wpformsplugin&utm_medium=pluginaboutpage&utm_campaign=aboutwpforms',
						'https://optinmonster.com/?utm_source=wpformsplugin&utm_medium=pluginaboutpage&utm_campaign=aboutwpforms',
						'https://www.monsterinsights.com/?utm_source=wpformsplugin&utm_medium=pluginaboutpage&utm_campaign=aboutwpforms'
					);
					?>
				</p>
				<p>
				</p>
			</div>

			<div class="wpforms-admin-column-40 wpforms-admin-column-last">
				<figure>
					<figcaption>
					</figcaption>
				</figure>
			</div>

		</div>
	}

	/**
	 * Display the Addons section of About tab.
	 *
	 * @since 1.5.8
	 */
	protected function output_about_addons() {

		if ( ! wpforms_current_user_can() ) {
			return;
		}

		$all_plugins          = get_plugins();
		$am_plugins           = $this->get_am_plugins();
		$can_install_plugins  = wpforms_can_install( 'plugin' );
		$can_activate_plugins = wpforms_can_activate( 'plugin' );

		?>
		<div id="wpforms-admin-addons">
			<div class="addons-container">
				foreach ( $am_plugins as $plugin => $details ) :

					$plugin_data              = $this->get_plugin_data( $plugin, $details, $all_plugins );
					$plugin_ready_to_activate = $can_activate_plugins
						&& isset( $plugin_data['status_class'] )
						&& $plugin_data['status_class'] === 'status-installed';
					$plugin_not_activated     = ! isset( $plugin_data['status_class'] )
						|| $plugin_data['status_class'] !== 'status-active';

					?>
					<div class="addon-container">
						<div class="addon-item">
							<div class="details wpforms-clear">
								<h5 class="addon-name">
								</h5>
								<p class="addon-desc">
								</p>
							</div>
							<div class="actions wpforms-clear">
								<div class="status">
									<strong>
										printf( /* translators: %s - status label. */
											esc_html__( 'Status: %s', 'wpforms-lite' ),
											'<span class="status-label ' . esc_attr( $plugin_data['status_class'] ) . '">' . wp_kses_post( $plugin_data['status_text'] ) . '</span>'
										);
										?>
									</strong>
								</div>
								<div class="action-button">
										</button>
											<span aria-hidden="true" class="dashicons dashicons-external"></span>
										</a>
								</div>
							</div>
						</div>
					</div>
			</div>
		</div>
	}

	/**
	 * Get AM plugin data to display in the Addons section of About tab.
	 *
	 * @since 1.5.8
	 *
	 * @param string $plugin      Plugin slug.
	 * @param array  $details     Plugin details.
	 * @param array  $all_plugins List of all plugins.
	 *
	 * @return array
	 */
	protected function get_plugin_data( $plugin, $details, $all_plugins ) {

		$have_pro = ( ! empty( $details['pro'] ) && ! empty( $details['pro']['plug'] ) );
		$show_pro = false;

		$plugin_data = [];

		if ( $have_pro ) {
			if ( array_key_exists( $plugin, $all_plugins ) ) {
				if ( is_plugin_active( $plugin ) ) {
					$show_pro = true;
				}
			}
			if ( array_key_exists( $details['pro']['plug'], $all_plugins ) ) {
				$show_pro = true;
			}
			if ( $show_pro ) {
				$plugin  = $details['pro']['plug'];
				$details = $details['pro'];
			}
		}

		if ( array_key_exists( $plugin, $all_plugins ) ) {
			if ( is_plugin_active( $plugin ) ) {
				// Status text/status.
				$plugin_data['status_class'] = 'status-active';
				$plugin_data['status_text']  = esc_html__( 'Active', 'wpforms-lite' );
				// Button text/status.
				$plugin_data['action_class'] = $plugin_data['status_class'] . ' button button-secondary disabled';
				$plugin_data['action_text']  = esc_html__( 'Activated', 'wpforms-lite' );
				$plugin_data['plugin_src']   = esc_attr( $plugin );
			} else {
				// Status text/status.
				$plugin_data['status_class'] = 'status-installed';
				$plugin_data['status_text']  = esc_html__( 'Inactive', 'wpforms-lite' );
				// Button text/status.
				$plugin_data['action_class'] = $plugin_data['status_class'] . ' button button-secondary';
				$plugin_data['action_text']  = esc_html__( 'Activate', 'wpforms-lite' );
				$plugin_data['plugin_src']   = esc_attr( $plugin );
			}
		} else {
			// Doesn't exist, install.
			// Status text/status.
			$plugin_data['status_class'] = 'status-missing';

			if ( isset( $details['act'] ) && 'go-to-url' === $details['act'] ) {
				$plugin_data['status_class'] = 'status-go-to-url';
			}
			$plugin_data['status_text'] = esc_html__( 'Not Installed', 'wpforms-lite' );
			// Button text/status.
			$plugin_data['action_class'] = $plugin_data['status_class'] . ' button button-primary';
			$plugin_data['action_text']  = esc_html__( 'Install Plugin', 'wpforms-lite' );
			$plugin_data['plugin_src']   = esc_url( $details['url'] );
		}

		$plugin_data['details'] = $details;

		return $plugin_data;
	}

	/**
	 * Display the Getting Started tab content.
	 *
	 * @since 1.5.0
	 */
	protected function output_getting_started() {

		$license      = $this->get_license_type();
		$utm_campaign = $license === 'lite' ? 'liteplugin' : 'plugin';

		$links = [
			'add-new'                 => "https://wpforms.com/docs/creating-first-form/?utm_source=WordPress&utm_medium=wpforms-about-page&utm_campaign={$utm_campaign}&utm_content=How to Add a New Form#add-new",
			'customize-fields'        => "https://wpforms.com/docs/creating-first-form/?utm_source=WordPress&utm_medium=wpforms-about-page&utm_campaign={$utm_campaign}&utm_content=How to Customize Form Fields#customize-fields",
			'display-form'            => "https://wpforms.com/docs/creating-first-form/?utm_source=WordPress&utm_medium=wpforms-about-page&utm_campaign={$utm_campaign}&utm_content=How to Display Forms on Your Site#display-form",
			'right-form-field'        => "https://wpforms.com/docs/how-to-choose-the-right-form-field-for-your-forms/?utm_source=WordPress&utm_medium=wpforms-about-page&utm_campaign={$utm_campaign}&utm_content=How to Choose the Right Form Field",
			'complete-guide'          => "https://wpforms.com/docs/a-complete-guide-to-wpforms-settings/?utm_source=WordPress&utm_medium=wpforms-about-page&utm_campaign={$utm_campaign}&utm_content=A Complete Guide to WPForms Settings",
			'gdpr-compliant'          => "https://wpforms.com/docs/how-to-create-gdpr-compliant-forms/?utm_source=WordPress&utm_medium=wpforms-about-page&utm_campaign={$utm_campaign}&utm_content=How to Create GDPR Complaint Forms",
			'install-activate-addons' => "https://wpforms.com/docs/install-activate-wpforms-addons/?utm_source=WordPress&utm_medium=wpforms-about-page&utm_campaign={$utm_campaign}&utm_content=How to Install and Activate WPForms Addons",
		];
		?>

		<div class="wpforms-admin-about-section wpforms-admin-about-section-first-form" style="display:flex;">

			<div class="wpforms-admin-about-section-first-form-text">

				<h2>
				</h2>

				<p>
				</p>

				<p>
				</p>

				<p>
				</p>

				<ul class="list-plain">
					<li>
						</a>
					</li>
					<li>
						</a>
					</li>
					<li>
						</a>
					</li>
				</ul>

			</div>

			<div class="wpforms-admin-about-section-first-form-video">
				<iframe src="https://www.youtube-nocookie.com/embed/SQ9kV9SKz5k?rel=0" width="540" height="304" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
			</div>

		</div>

			<div class="wpforms-admin-about-section wpforms-admin-about-section-hero">

				<div class="wpforms-admin-about-section-hero-main">
					<h2>
					</h2>

					<p class="bigger">
						echo wp_kses(
							__( 'Thanks for being a loyal WPForms Lite user. <strong>Upgrade to WPForms Pro</strong> to unlock all the awesome features and experience<br>why WPForms is consistently rated the best WordPress form builder.', 'wpforms-lite' ),
							[
								'br'     => [],
								'strong' => [],
							]
						);
						?>
					</p>

					<p>
						printf(
							wp_kses( /* translators: %s - stars. */
								__( 'We know that you will truly love WPForms. It has over <strong>13,000+ five star ratings</strong> (%s) and is active on over 6 million websites.', 'wpforms-lite' ),
								[
									'strong' => [],
								]
							),
							'<i class="fa fa-star" aria-hidden="true"></i>' .
							'<i class="fa fa-star" aria-hidden="true"></i>' .
							'<i class="fa fa-star" aria-hidden="true"></i>' .
							'<i class="fa fa-star" aria-hidden="true"></i>' .
							'<i class="fa fa-star" aria-hidden="true"></i>'
						);
						?>
					</p>
				</div>

				<div class="wpforms-admin-about-section-hero-extra">
					<div class="wpforms-admin-columns">
						<div class="wpforms-admin-column-50">
							<ul class="list-features list-plain">
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
									printf( /* translators: %s - number of templates. */
										esc_html__( '%s customizable form templates', 'wpforms-lite' ),
										'2000+'
									);
									?>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
							</ul>
						</div>
						<div class="wpforms-admin-column-50 wpforms-admin-column-last">
							<ul class="list-features list-plain">
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
								<li>
									<i class="fa fa-check" aria-hidden="true"></i>
								</li>
							</ul>
						</div>
					</div>

					<hr />

					<h3 class="call-to-action">
						printf(
							'<a href="%s" target="_blank" rel="noopener noreferrer">',
							esc_url( wpforms_admin_upgrade_link( 'wpforms-about-page', 'Get WPForms Pro Today' ) )
						);

						esc_html_e( 'Get WPForms Pro Today and Unlock all the Powerful Features', 'wpforms-lite' );
						?>
						</a>
					</h3>

						<p>
							echo wp_kses(
								__( 'Bonus: WPForms Lite users get <span class="price-20-off">50% off regular price</span>, automatically applied at checkout.', 'wpforms-lite' ),
								[
									'span' => [
										'class' => [],
									],
								]
							);
							?>
						</p>
				</div>

			</div>

		<div class="wpforms-admin-about-section wpforms-admin-about-section-squashed wpforms-admin-about-section-post wpforms-admin-columns">
			<div class="wpforms-admin-column-20">
			</div>
			<div class="wpforms-admin-column-80">
				<h2>
				</h2>

				<p>
				</p>

				</a>
			</div>
		</div>

		<div class="wpforms-admin-about-section wpforms-admin-about-section-squashed wpforms-admin-about-section-post wpforms-admin-columns">
			<div class="wpforms-admin-column-20">
			</div>
			<div class="wpforms-admin-column-80">
				<h2>
				</h2>

				<p>
				</p>

				</a>
			</div>
		</div>

		<div class="wpforms-admin-about-section wpforms-admin-about-section-squashed wpforms-admin-about-section-post wpforms-admin-columns">
			<div class="wpforms-admin-column-20">
			</div>
			<div class="wpforms-admin-column-80">
				<h2>
				</h2>

				<p>
				</p>

				</a>
			</div>
		</div>

		<div class="wpforms-admin-about-section wpforms-admin-about-section-squashed wpforms-admin-about-section-post wpforms-admin-columns">
			<div class="wpforms-admin-column-20">
			</div>
			<div class="wpforms-admin-column-80">
				<h2>
				</h2>

				<p>
				</p>

				</a>
			</div>
		</div>

	}

	/**
	 * Get the next license type. Helper for Versus tab content.
	 *
	 * @since 1.5.5
	 *
	 * @param string $current Current license type slug.
	 *
	 * @return string Next license type slug.
	 */
	protected function get_next_license( $current ) {

		$current       = ucfirst( $current );
		$license_pairs = [
			'Lite'  => 'Pro',
			'Basic' => 'Pro',
			'Plus'  => 'Pro',
			'Pro'   => 'Elite',
		];

		return ! empty( $license_pairs[ $current ] ) ? $license_pairs[ $current ] : 'Elite';
	}

	/**
	 * Display the Versus tab content.
	 *
	 * @since 1.5.0
	 */
	protected function output_versus() {

		$license      = $this->get_license_type();
		$next_license = $this->get_next_license( $license );
		?>

		<div class="wpforms-admin-about-section wpforms-admin-about-section-squashed">
			<h1 class="centered">
			</h1>

			<p class="centered">
			</p>
		</div>

		<div class="wpforms-admin-about-section wpforms-admin-about-section-squashed wpforms-admin-about-section-hero wpforms-admin-about-section-table">

			<div class="wpforms-admin-about-section-hero-main no-border wpforms-admin-columns">
				<div class="wpforms-admin-column-33">
					<h3 class="no-margin">
					</h3>
				</div>
				<div class="wpforms-admin-column-33">
					<h3 class="no-margin">
					</h3>
				</div>
				<div class="wpforms-admin-column-33">
					<h3 class="no-margin">
					</h3>
				</div>
			</div>
			<div class="wpforms-admin-about-section-hero-extra no-padding wpforms-admin-columns">

				<table>
					foreach ( $this->get_licenses_features_list() as $slug => $name ) {
						$current = $this->get_license_data( $slug, $license );
						$next    = $this->get_license_data( $slug, strtolower( $next_license ) );

						if ( empty( $current ) || empty( $next ) ) {
							continue;
						}

						$current_status = $current['status'];

						if ( $current['text'] !== $next['text'] && $current_status === 'full' ) {
							$current_status = 'partial';
						}
						?>
						<tr class="wpforms-admin-columns">
							<td class="wpforms-admin-column-33">
							</td>
							<td class="wpforms-admin-column-33">
									</p>
							</td>
							<td class="wpforms-admin-column-33">
									<p class="features-full">
									</p>
							</td>
						</tr>
					}
					?>
				</table>

			</div>

		</div>

		<div class="wpforms-admin-about-section wpforms-admin-about-section-hero">
			<div class="wpforms-admin-about-section-hero-main no-border">
				<h3 class="call-to-action centered">
					printf(
						'<a href="%s" target="_blank" rel="noopener noreferrer">',
						esc_url( wpforms_admin_upgrade_link( 'wpforms-about-page', 'Get WPForms Pro Today' ) )
					);
					printf( /* translators: %s - next license level. */
						esc_html__( 'Get WPForms %s Today and Unlock all the Powerful Features', 'wpforms-lite' ),
						esc_html( $next_license )
					);
					?>
					</a>
				</h3>

					<p class="centered">
						echo wp_kses(
							__( 'Bonus: WPForms Lite users get <span class="price-20-off">50% off regular price</span>, automatically applied at checkout.', 'wpforms-lite' ),
							[
								'span' => [
									'class' => [],
								],
							]
						);
						?>
					</p>
			</div>
		</div>

	}

	/**
	 * List of AM plugins that we propose to install.
	 *
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function get_am_plugins() {

		$images_url = WPFORMS_PLUGIN_URL . 'assets/images/about/';

		return [

			'optinmonster/optin-monster-wp-api.php'        => [
				'icon'  => $images_url . 'plugin-om.png',
				'name'  => esc_html__( 'OptinMonster', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Instantly get more subscribers, leads, and sales with the #1 conversion optimization toolkit. Create high converting popups, announcement bars, spin a wheel, and more with smart targeting and personalization.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/optinmonster/',
				'url'   => 'https://downloads.wordpress.org/plugin/optinmonster.zip',
			],

			'google-analytics-for-wordpress/googleanalytics.php' => [
				'icon'  => $images_url . 'plugin-mi.png',
				'name'  => esc_html__( 'MonsterInsights', 'wpforms-lite' ),
				'desc'  => esc_html__( 'The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/google-analytics-for-wordpress/',
				'url'   => 'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
				'pro'   => [
					'plug' => 'google-analytics-premium/googleanalytics-premium.php',
					'icon' => $images_url . 'plugin-mi.png',
					'name' => esc_html__( 'MonsterInsights Pro', 'wpforms-lite' ),
					'desc' => esc_html__( 'The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', 'wpforms-lite' ),
					'url'  => 'https://www.monsterinsights.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'wp-mail-smtp/wp_mail_smtp.php'                => [
				'icon'  => $images_url . 'plugin-smtp.png',
				'name'  => esc_html__( 'WP Mail SMTP', 'wpforms-lite' ),
				'desc'  => esc_html__( "Improve your WordPress email deliverability and make sure that your website emails reach user's inbox with the #1 SMTP plugin for WordPress. Over 3 million websites use it to fix WordPress email issues.", 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/wp-mail-smtp/',
				'url'   => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
				'pro'   => [
					'plug' => 'wp-mail-smtp-pro/wp_mail_smtp.php',
					'icon' => $images_url . 'plugin-smtp.png',
					'name' => esc_html__( 'WP Mail SMTP Pro', 'wpforms-lite' ),
					'desc' => esc_html__( "Improve your WordPress email deliverability and make sure that your website emails reach user's inbox with the #1 SMTP plugin for WordPress. Over 3 million websites use it to fix WordPress email issues.", 'wpforms-lite' ),
					'url'  => 'https://wpmailsmtp.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'all-in-one-seo-pack/all_in_one_seo_pack.php'  => [
				'icon'  => $images_url . 'plugin-aioseo.png',
				'name'  => esc_html__( 'AIOSEO', 'wpforms-lite' ),
				'desc'  => esc_html__( "The original WordPress SEO plugin and toolkit that improves your website's search rankings. Comes with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.", 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/all-in-one-seo-pack/',
				'url'   => 'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
				'pro'   => [
					'plug' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
					'icon' => $images_url . 'plugin-aioseo.png',
					'name' => esc_html__( 'AIOSEO Pro', 'wpforms-lite' ),
					'desc' => esc_html__( "The original WordPress SEO plugin and toolkit that improves your website's search rankings. Comes with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.", 'wpforms-lite' ),
					'url'  => 'https://aioseo.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'coming-soon/coming-soon.php'                  => [
				'icon'  => $images_url . 'plugin-seedprod.png',
				'name'  => esc_html__( 'SeedProd', 'wpforms-lite' ),
				'desc'  => esc_html__( 'The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/coming-soon/',
				'url'   => 'https://downloads.wordpress.org/plugin/coming-soon.zip',
				'pro'   => [
					'plug' => 'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php',
					'icon' => $images_url . 'plugin-seedprod.png',
					'name' => esc_html__( 'SeedProd Pro', 'wpforms-lite' ),
					'desc' => esc_html__( 'The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', 'wpforms-lite' ),
					'url'  => 'https://www.seedprod.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'rafflepress/rafflepress.php'                  => [
				'icon'  => $images_url . 'plugin-rp.png',
				'name'  => esc_html__( 'RafflePress', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/rafflepress/',
				'url'   => 'https://downloads.wordpress.org/plugin/rafflepress.zip',
				'pro'   => [
					'plug' => 'rafflepress-pro/rafflepress-pro.php',
					'icon' => $images_url . 'plugin-rp.png',
					'name' => esc_html__( 'RafflePress Pro', 'wpforms-lite' ),
					'desc' => esc_html__( 'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'wpforms-lite' ),
					'url'  => 'https://rafflepress.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'pushengage/main.php'                          => [
				'icon'  => $images_url . 'plugin-pushengage.png',
				'name'  => esc_html__( 'PushEngage', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Connect with your visitors after they leave your website with the leading web push notification software. Over 10,000+ businesses worldwide use PushEngage to send 15 billion notifications each month.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/pushengage/',
				'url'   => 'https://downloads.wordpress.org/plugin/pushengage.zip',
			],

			'instagram-feed/instagram-feed.php'            => [
				'icon'  => $images_url . 'plugin-sb-instagram.png',
				'name'  => esc_html__( 'Smash Balloon Instagram Feeds', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Easily display Instagram content on your WordPress site without writing any code. Comes with multiple templates, ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/instagram-feed/',
				'url'   => 'https://downloads.wordpress.org/plugin/instagram-feed.zip',
				'pro'   => [
					'plug' => 'instagram-feed-pro/instagram-feed.php',
					'icon' => $images_url . 'plugin-sb-instagram.png',
					'name' => esc_html__( 'Smash Balloon Instagram Feeds Pro', 'wpforms-lite' ),
					'desc' => esc_html__( 'Easily display Instagram content on your WordPress site without writing any code. Comes with multiple templates, ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.', 'wpforms-lite' ),
					'url'  => 'https://smashballoon.com/instagram-feed/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'custom-facebook-feed/custom-facebook-feed.php' => [
				'icon'  => $images_url . 'plugin-sb-fb.png',
				'name'  => esc_html__( 'Smash Balloon Facebook Feeds', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/custom-facebook-feed/',
				'url'   => 'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
				'pro'   => [
					'plug' => 'custom-facebook-feed-pro/custom-facebook-feed.php',
					'icon' => $images_url . 'plugin-sb-fb.png',
					'name' => esc_html__( 'Smash Balloon Facebook Feeds Pro', 'wpforms-lite' ),
					'desc' => esc_html__( 'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'wpforms-lite' ),
					'url'  => 'https://smashballoon.com/custom-facebook-feed/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'feeds-for-youtube/youtube-feed.php'           => [
				'icon'  => $images_url . 'plugin-sb-youtube.png',
				'name'  => esc_html__( 'Smash Balloon YouTube Feeds', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/feeds-for-youtube/',
				'url'   => 'https://downloads.wordpress.org/plugin/feeds-for-youtube.zip',
				'pro'   => [
					'plug' => 'youtube-feed-pro/youtube-feed.php',
					'icon' => $images_url . 'plugin-sb-youtube.png',
					'name' => esc_html__( 'Smash Balloon YouTube Feeds Pro', 'wpforms-lite' ),
					'desc' => esc_html__( 'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', 'wpforms-lite' ),
					'url'  => 'https://smashballoon.com/youtube-feed/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'custom-twitter-feeds/custom-twitter-feed.php' => [
				'icon'  => $images_url . 'plugin-sb-twitter.png',
				'name'  => esc_html__( 'Smash Balloon Twitter Feeds', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ability to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/custom-twitter-feeds/',
				'url'   => 'https://downloads.wordpress.org/plugin/custom-twitter-feeds.zip',
				'pro'   => [
					'plug' => 'custom-twitter-feeds-pro/custom-twitter-feed.php',
					'icon' => $images_url . 'plugin-sb-twitter.png',
					'name' => esc_html__( 'Smash Balloon Twitter Feeds Pro', 'wpforms-lite' ),
					'desc' => esc_html__( 'Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ability to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.', 'wpforms-lite' ),
					'url'  => 'https://smashballoon.com/custom-twitter-feeds/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'trustpulse-api/trustpulse.php'                => [
				'icon'  => $images_url . 'plugin-trustpulse.png',
				'name'  => esc_html__( 'TrustPulse', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Boost your sales and conversions by up to 15% with real-time social proof notifications. TrustPulse helps you show live user activity and purchases to help convince other users to purchase.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/trustpulse-api/',
				'url'   => 'https://downloads.wordpress.org/plugin/trustpulse-api.zip',
			],

			'searchwp/index.php'                           => [
				'icon'  => $images_url . 'plugin-searchwp.png',
				'name'  => esc_html__( 'SearchWP', 'wpforms-lite' ),
				'desc'  => esc_html__( 'The most advanced WordPress search plugin. Customize your WordPress search algorithm, reorder search results, track search metrics, and everything you need to leverage search to grow your business.', 'wpforms-lite' ),
				'wporg' => false,
				'url'   => 'https://searchwp.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
				'act'   => 'go-to-url',
			],

			'affiliate-wp/affiliate-wp.php'                => [
				'icon'  => $images_url . 'plugin-affwp.png',
				'name'  => esc_html__( 'AffiliateWP', 'wpforms-lite' ),
				'desc'  => esc_html__( 'The #1 affiliate management plugin for WordPress. Easily create an affiliate program for your eCommerce store or membership site within minutes and start growing your sales with the power of referral marketing.', 'wpforms-lite' ),
				'wporg' => false,
				'url'   => 'https://affiliatewp.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
				'act'   => 'go-to-url',
			],

			'stripe/stripe-checkout.php'                   => [
				'icon'  => $images_url . 'plugin-wp-simple-pay.png',
				'name'  => esc_html__( 'WP Simple Pay', 'wpforms-lite' ),
				'desc'  => esc_html__( 'The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your WordPress site without setting up a shopping cart. No code required.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/stripe/',
				'url'   => 'https://downloads.wordpress.org/plugin/stripe.zip',
				'pro'   => [
					'plug' => 'wp-simple-pay-pro-3/simple-pay.php',
					'icon' => $images_url . 'plugin-wp-simple-pay.png',
					'name' => esc_html__( 'WP Simple Pay Pro', 'wpforms-lite' ),
					'desc' => esc_html__( 'The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your WordPress site without setting up a shopping cart. No code required.', 'wpforms-lite' ),
					'url'  => 'https://wpsimplepay.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],

			'easy-digital-downloads/easy-digital-downloads.php' => [
				'icon'  => $images_url . 'plugin-edd.png',
				'name'  => esc_html__( 'Easy Digital Downloads', 'wpforms-lite' ),
				'desc'  => esc_html__( 'The best WordPress eCommerce plugin for selling digital downloads. Start selling eBooks, software, music, digital art, and more within minutes. Accept payments, manage subscriptions, advanced access control, and more.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/easy-digital-downloads/',
				'url'   => 'https://downloads.wordpress.org/plugin/easy-digital-downloads.zip',
			],

			'sugar-calendar-lite/sugar-calendar-lite.php'  => [
				'icon'  => $images_url . 'plugin-sugarcalendar.png',
				'name'  => esc_html__( 'Sugar Calendar', 'wpforms-lite' ),
				'desc'  => esc_html__( 'A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/sugar-calendar-lite/',
				'url'   => 'https://downloads.wordpress.org/plugin/sugar-calendar-lite.zip',
				'pro'   => [
					'plug' => 'sugar-calendar/sugar-calendar.php',
					'icon' => $images_url . 'plugin-sugarcalendar.png',
					'name' => esc_html__( 'Sugar Calendar Pro', 'wpforms-lite' ),
					'desc' => esc_html__( 'A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.', 'wpforms-lite' ),
					'url'  => 'https://sugarcalendar.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=About%20WPForms',
					'act'  => 'go-to-url',
				],
			],
			'charitable/charitable.php'                    => [
				'icon'  => $images_url . 'plugin-charitable.png',
				'name'  => esc_html__( 'Charitable', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Top-rated WordPress donation and fundraising plugin. Over 10,000+ non-profit organizations and website owners use Charitable to create fundraising campaigns and raise more money online.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/charitable/',
				'url'   => 'https://downloads.wordpress.org/plugin/charitable.zip',
			],
			'insert-headers-and-footers/ihaf.php'          => [
				'icon'  => $images_url . 'plugin-wpcode.png',
				'name'  => esc_html__( 'WPCode', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Future proof your WordPress customizations with the most popular code snippet management plugin for WordPress. Trusted by over 1,500,000+ websites for easily adding code to WordPress right from the admin area.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/insert-headers-and-footers/',
				'url'   => 'https://downloads.wordpress.org/plugin/insert-headers-and-footers.zip',
			],
			'duplicator/duplicator.php'                    => [
				'icon'  => $images_url . 'plugin-duplicator.png',
				'name'  => esc_html__( 'Duplicator', 'wpforms-lite' ),
				'desc'  => esc_html__( 'Leading WordPress backup & site migration plugin. Over 1,500,000+ smart website owners use Duplicator to make reliable and secure WordPress backups to protect their websites. It also makes website migration really easy.', 'wpforms-lite' ),
				'wporg' => 'https://wordpress.org/plugins/duplicator/',
				'url'   => 'https://downloads.wordpress.org/plugin/duplicator.zip',
			],
		];
	}

	/**
	 * Get the array of data that compared the license data.
	 *
	 * @since 1.5.0
	 *
	 * @param string $feature Feature name.
	 * @param string $license License type to get data for.
	 *
	 * @return array|false
	 */
	protected function get_license_data( $feature, $license ) {

		$data = [
			'entries'      => [
				'lite'  => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Entries via Email Only', 'wpforms-lite' ) . '</strong>',
					],
				],
				'basic' => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Complete Entry Management inside WordPress', 'wpforms-lite' ) . '</strong>',
					],
				],
				'plus'  => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Complete Entry Management inside WordPress', 'wpforms-lite' ) . '</strong>',
					],
				],
				'pro'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Complete Entry Management inside WordPress', 'wpforms-lite' ) . '</strong>',
					],
				],
			],
			'fields'       => [
				'lite'  => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Standard and Payment Fields', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Name, Email, Single Line Text, Paragraph Text, Dropdown, Multiple Choice, Checkboxes, Numbers, Number Slider, and Payment Fields (Single Item, Total, etc.)', 'wpforms-lite' ),
					],
				],
				'basic' => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Access to all Standard, Fancy, and Payment Fields', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Address, Phone, Website / URL, Date / Time, Password, File Upload, Layout, Rich Text, Content, HTML, Pagebreaks, Entry Preview, Section Dividers, Ratings, and Hidden Field', 'wpforms-lite' ),
					],
				],
				'plus'  => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Access to all Standard, Fancy, and Payment Fields', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Address, Phone, Website / URL, Date / Time, Password, File Upload, Layout, Rich Text, Content, HTML, Pagebreaks, Entry Preview, Section Dividers, Ratings, and Hidden Field', 'wpforms-lite' ),
					],
				],
				'pro'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Access to all Standard, Fancy, and Payment Fields', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Address, Phone, Website / URL, Date / Time, Password, File Upload, Layout, Rich Text, Content, HTML, Pagebreaks, Entry Preview, Section Dividers, Ratings, and Hidden Field', 'wpforms-lite' ),
					],
				],
			],
			'conditionals' => [
				'lite'  => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'Not available', 'wpforms-lite' ) . '</strong>',
					],
				],
				'basic' => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Powerful Form Logic for Building Smart Forms', 'wpforms-lite' ) . '</strong>',
					],
				],
				'plus'  => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Powerful Form Logic for Building Smart Forms', 'wpforms-lite' ) . '</strong>',
					],
				],
				'pro'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Powerful Form Logic for Building Smart Forms', 'wpforms-lite' ) . '</strong>',
					],
				],
			],
			'templates'    => [
				'lite'  => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Basic Form Templates', 'wpforms-lite' ) . '</strong>',
					],
				],
				'basic' => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Basic Form Templates', 'wpforms-lite' ) . '</strong>',
					],
				],
				'plus'  => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Basic Form Templates', 'wpforms-lite' ) . '</strong>',
					],
				],
				'pro'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' .
						sprintf( /* translators: %s - number of templates. */
							esc_html__( 'All Form Templates including Bonus %s pre-made form templates', 'wpforms-lite' ),
							'2000+'
						) .
						'</strong>',
					],
				],
			],
			'antispam'     => [
				'lite'  => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Basic Anti-Spam Settings', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Basic Protection, reCAPTCHA, hCaptcha, Cloudflare Turnstile and Akismet', 'wpforms-lite' ),
					],
				],
				'basic' => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Additional Anti-Spam Settings', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Basic Protection, reCAPTCHA, hCaptcha, Cloudflare Turnstile, Akismet, Country Filter, Keyword Filter, and Custom Captcha', 'wpforms-lite' ),
					],
				],
				'plus'  => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Additional Anti-Spam Settings', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Basic Protection, reCAPTCHA, hCaptcha, Cloudflare Turnstile, Akismet, Country Filter, Keyword Filter, and Custom Captcha', 'wpforms-lite' ),
					],
				],
				'pro'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Additional Anti-Spam Settings', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Basic Protection, reCAPTCHA, hCaptcha, Cloudflare Turnstile, Akismet, Country Filter, Keyword Filter, and Custom Captcha', 'wpforms-lite' ),
					],
				],
			],
			'marketing'    => [
				'lite'     => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Marketing Integration', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Constant Contact only', 'wpforms-lite' ),
					],
				],
				'basic'    => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Marketing Integration', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Constant Contact only', 'wpforms-lite' ),
					],
				],
				'plus'     => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Additional Marketing Integrations', 'wpforms-lite' ) . '</strong>',
						wpforms_list_array(
							[
								'Constant Contact',
								'Mailchimp',
								'AWeber',
								'GetResponse',
								'Campaign Monitor',
								'Brevo',
								'Drip',
								'MailerLite',
								'MailPoet',
								'Kit',
								'Slack',
								'Twilio',
							]
						),
					],
				],
				'pro'      => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Additional Marketing Integrations', 'wpforms-lite' ) . '</strong>',
						wpforms_list_array(
							[
								'Constant Contact',
								'Mailchimp',
								'AWeber',
								'GetResponse',
								'Campaign Monitor',
								'Brevo',
								'Drip',
								'MailerLite',
								'MailPoet',
								'Kit',
								'Slack',
								'Twilio',
								'Make',
							]
						),
						'',
						wp_kses(
							__( '<strong>Bonus:</strong> 7000+ integrations with Zapier.', 'wpforms-lite' ),
							[
								'strong' => [],
							]
						),
					],
				],
				'elite'    => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'All Marketing Integrations', 'wpforms-lite' ) . '</strong>',
						wpforms_list_array(
							[
								'ActiveCampaign',
								'Constant Contact',
								'Mailchimp',
								'AWeber',
								'GetResponse',
								'Campaign Monitor',
								'Salesforce',
								'Brevo',
								'HubSpot',
								'Drip',
								'MailerLite',
								'MailPoet',
								'Kit',
								'Slack',
								'Twilio',
								'Pipedrive',
								'Make',
								'Zoho CRM',
							]
						),
						'',
						wp_kses(
							__( '<strong>Bonus:</strong> 7000+ integrations with Zapier.', 'wpforms-lite' ),
							[
								'strong' => [],
							]
						),
					],
				],
				'ultimate' => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'All Marketing Integrations', 'wpforms-lite' ) . '</strong>',
						wpforms_list_array(
							[
								'ActiveCampaign',
								'Constant Contact',
								'Mailchimp',
								'AWeber',
								'GetResponse',
								'Campaign Monitor',
								'Salesforce',
								'Brevo',
								'HubSpot',
								'Drip',
								'MailerLite',
								'MailPoet',
								'Kit',
								'Slack',
								'Twilio',
								'Pipedrive',
								'Make',
								'Zoho CRM',
							]
						),
						'',
						wp_kses(
							__( '<strong>Bonus:</strong> 7000+ integrations with Zapier.', 'wpforms-lite' ),
							[
								'strong' => [],
							]
						),
					],
				],
				'agency'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'All Marketing Integrations', 'wpforms-lite' ) . '</strong>',
						wpforms_list_array(
							[
								'ActiveCampaign',
								'Constant Contact',
								'Mailchimp',
								'AWeber',
								'GetResponse',
								'Campaign Monitor',
								'Salesforce',
								'Brevo',
								'HubSpot',
								'Drip',
								'MailerLite',
								'MailPoet',
								'Kit',
								'Slack',
								'Twilio',
								'Pipedrive',
								'Make',
								'Zoho CRM',
							]
						),
						'',
						wp_kses(
							__( '<strong>Bonus:</strong> 7000+ integrations with Zapier.', 'wpforms-lite' ),
							[
								'strong' => [],
							]
						),
					],
				],
			],
			'payments'     => [
				'lite'     => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Payment Forms', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Accept payments using Stripe and Square only', 'wpforms-lite' ),
					],
				],
				'basic'    => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Payment Forms', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Accept payments using Stripe and Square only', 'wpforms-lite' ),
					],
				],
				'plus'     => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Payment Forms', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Accept payments using Stripe and Square only', 'wpforms-lite' ),
					],
				],
				'pro'      => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Create Payment Forms', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Accept payments using PayPal Commerce, Stripe, Square, and PayPal Standard', 'wpforms-lite' ),
					],
				],
				'elite'    => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Create Payment Forms', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Accept payments using PayPal Commerce, Stripe, Square, PayPal Standard, and Authorize.Net', 'wpforms-lite' ),
					],
				],
				'agency'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Create Payment Forms', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Accept payments using PayPal Commerce, Stripe, Square, PayPal Standard, and Authorize.Net', 'wpforms-lite' ),
					],
				],
				'ultimate' => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Create Payment Forms', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Accept payments using PayPal Commerce, Stripe, Square, PayPal Standard, and Authorize.Net', 'wpforms-lite' ),
					],
				],
			],
			'surveys'      => [
				'lite'  => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'Not Available', 'wpforms-lite' ) . '</strong>',
					],
				],
				'basic' => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'Not Available', 'wpforms-lite' ) . '</strong>',
					],
				],
				'plus'  => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'Not Available', 'wpforms-lite' ) . '</strong>',
					],
				],
				'pro'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Create interactive Surveys and Polls with beautiful reports', 'wpforms-lite' ) . '</strong>',
					],
				],
			],
			'advanced'     => [
				'lite'  => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'No Advanced Features', 'wpforms-lite' ) . '</strong>',
					],
				],
				'basic' => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Advanced Features', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Multi-page Forms, File Upload Forms, Multiple Form Notifications, File Upload and CSV Attachments, Conditional Form Confirmation', 'wpforms-lite' ),
					],
				],
				'plus'  => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Advanced Features', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Multi-page Forms, File Upload Forms, Multiple Form Notifications, File Upload and CSV Attachments, Conditional Form Confirmation, Save and Resume Form', 'wpforms-lite' ),
					],
				],
				'pro'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'All Advanced Features', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Multi-page Forms, File Upload Forms, Multiple Form Notifications, File Upload and CSV Attachments, Conditional Form Confirmation, Custom CAPTCHA, Offline Forms, Signature Forms, Save and Resume Form, Coupons', 'wpforms-lite' ),
					],
				],
			],
			'addons'       => [
				'lite'     => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'No Addons Included', 'wpforms-lite' ) . '</strong>',
					],
				],
				'basic'    => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'No Addons Included', 'wpforms-lite' ) . '</strong>',
					],
				],
				'plus'     => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Email Marketing Addons included', 'wpforms-lite' ) . '</strong>',
					],
				],
				'pro'      => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Pro Addons Included', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Calculations, Form Abandonment, Conversational Forms, Lead Forms, Frontend Post Submission, User Registration, Geolocation, Google Sheets, Coupons, Dropbox, Google Drive, and more (30+ total)', 'wpforms-lite' ),
					],
				],
				'elite'    => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'All Addons Included', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Calculations, Form Abandonment, Conversational Forms, Lead Forms, Frontend Post Submission, User Registration, Geolocation, Webhooks, Google Sheets, Coupons, Dropbox, Google Drive, Entry Automation, and more (35+ total)', 'wpforms-lite' ),
					],
				],
				'ultimate' => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'All Addons Included', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Calculations, Form Abandonment, Conversational Forms, Lead Forms, Frontend Post Submission, User Registration, Geolocation, Webhooks, Google Sheets, Coupons, Dropbox, Google Drive, Entry Automation, and more (35+ total)', 'wpforms-lite' ),
					],
				],
				'agency'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'All Addons Included', 'wpforms-lite' ) . '</strong>',
						esc_html__( 'Calculations, Form Abandonment, Conversational Forms, Lead Forms, Frontend Post Submission, User Registration, Geolocation, Webhooks, Google Sheets, Coupons, Dropbox, Google Drive, Entry Automation, and more (35+ total)', 'wpforms-lite' ),
					],
				],
			],
			'support'      => [
				'lite'     => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Support', 'wpforms-lite' ) . '</strong>',
					],
				],
				'basic'    => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Standard Support', 'wpforms-lite' ) . '</strong>',
					],
				],
				'plus'     => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( 'Standard Support', 'wpforms-lite' ) . '</strong>',
					],
				],
				'pro'      => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Priority Support', 'wpforms-lite' ) . '</strong>',
					],
				],
				'elite'    => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Premium Support', 'wpforms-lite' ) . '</strong>',
					],
				],
				'ultimate' => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Premium Support', 'wpforms-lite' ) . '</strong>',
					],
				],
				'agency'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Premium Support', 'wpforms-lite' ) . '</strong>',
					],
				],
			],
			'sites'        => [
				'basic'    => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( '1 Site', 'wpforms-lite' ) . '</strong>',
					],
				],
				'plus'     => [
					'status' => 'partial',
					'text'   => [
						'<strong>' . esc_html__( '3 Sites', 'wpforms-lite' ) . '</strong>',
					],
				],
				'pro'      => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( '5 Sites', 'wpforms-lite' ) . '</strong>',
					],
				],
				'elite'    => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Unlimited Sites', 'wpforms-lite' ) . '</strong>',
					],
				],
				'ultimate' => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Unlimited Sites', 'wpforms-lite' ) . '</strong>',
					],
				],
				'agency'   => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Unlimited Sites', 'wpforms-lite' ) . '</strong>',
					],
				],
			],
		];

		// Wrong feature?
		if ( ! isset( $data[ $feature ] ) ) {
			return false;
		}

		// Is a top level license?
		$is_licenses_top = in_array( $license, self::$licenses_top, true );

		// Wrong license type?
		if ( ! isset( $data[ $feature ][ $license ] ) && ! $is_licenses_top ) {
			return false;
		}

		// Some licenses have partial data.
		if ( isset( $data[ $feature ][ $license ] ) ) {
			return $data[ $feature ][ $license ];
		}

		// Top level plans has no feature difference with `pro` plan in most cases.
		return $is_licenses_top ? $data[ $feature ]['pro'] : $data[ $feature ][ $license ];
	}

	/**
	 * Get the current installation license type (always lowercase).
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	protected function get_license_type() {

		$type = wpforms_get_license_type();

		if ( empty( $type ) ) {
			$type = 'lite';
		}

		return $type;
	}

	/**
	 * Get the list of features for the licenses.
	 *
	 * @since 1.8.2.3
	 *
	 * @return array
	 */
	private function get_licenses_features_list() {

		self::$licenses_features = [
			'entries'      => esc_html__( 'Form Entries', 'wpforms-lite' ),
			'fields'       => esc_html__( 'Form Fields', 'wpforms-lite' ),
			'templates'    => esc_html__( 'Form Templates', 'wpforms-lite' ),
			'antispam'     => esc_html__( 'Spam Protection and Security', 'wpforms-lite' ),
			'conditionals' => esc_html__( 'Smart Conditional Logic', 'wpforms-lite' ),
			'marketing'    => esc_html__( 'Marketing Integrations', 'wpforms-lite' ),
			'payments'     => esc_html__( 'Payment Forms', 'wpforms-lite' ),
			'surveys'      => esc_html__( 'Surveys & Polls', 'wpforms-lite' ),
			'advanced'     => esc_html__( 'Advanced Form Features', 'wpforms-lite' ),
			'addons'       => esc_html__( 'WPForms Addons', 'wpforms-lite' ),
			'support'      => esc_html__( 'Customer Support', 'wpforms-lite' ),
			'sites'        => esc_html__( 'Number of Sites', 'wpforms-lite' ),
		];

		return self::$licenses_features;
	}
}

new WPForms_About();

} // namespace CharacterGeneratorDev
