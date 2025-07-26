<?php

namespace CharacterGeneratorDev {

/**
 * WPConsent Onboarding Wizard
 *
 * @package WPConsent
 */

/**
 * Onboarding wizard page class.
 */
class WPConsent_Admin_Page_Onboarding extends WPConsent_Admin_Page {

	use WPConsent_Input_Image_Radio;
	use WPConsent_Banner_Preview;
	use WPConsent_Services_Upsell;
	use WPConsent_Scan_Pages;

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	public $page_slug = 'wpconsent-onboarding';

	/**
	 * Current step in the onboarding process.
	 *
	 * @var int
	 */
	public $current_step = 1;

	/**
	 * Steps in the onboarding process.
	 *
	 * @var array
	 */
	public $steps = array(
		'welcome',
		'scan',
		'configure',
		'banner',
	);

	/**
	 * Hide the page from the menu.
	 *
	 * @var bool
	 */
	public $hide_menu = true;


	/**
	 * Call this just to set the page title translatable.
	 */
	public function __construct() {
		$this->page_title = __( 'WPConsent Onboarding Wizard', 'wpconsent-cookies-banner-privacy-suite' );
		parent::__construct();
	}

	/**
	 * Page-specific hooks.
	 *
	 * @return void
	 */
	public function page_hooks() {
		add_action( 'admin_body_class', array( $this, 'add_body_class' ) );
		add_filter( 'wpconsent_admin_js_data', array( $this, 'localize_script' ) );
		add_filter( 'wpconsent_admin_js_data', array( $this, 'banner_preview_scripts' ), 15 );
	}

	/**
	 * Add strings that are specific to this page.
	 *
	 * @param array $data The translated data.
	 *
	 * @return array
	 */
	public function localize_script( $data ) {
		$data['configuring_title'] = esc_html__( 'Configuring Selected Services', 'wpconsent-cookies-banner-privacy-suite' );
		$data['banner_title']      = esc_html__( 'Saving Banner Preferences', 'wpconsent-cookies-banner-privacy-suite' );
		$data['max_steps']         = count( $this->steps );
		$data['icons']             = array(
			'checkmark' => wpconsent_get_icon( 'checkmark', 88, 88, '0 0 130.2 130.2' ),
		);

		return $data;
	}

	/**
	 * Add a body class to the onboarding page.
	 *
	 * @param string $classes Body classes.
	 *
	 * @return string
	 */
	public function add_body_class( $classes ) {
		$classes .= ' wpconsent-onboarding-step-' . $this->current_step;

		return $classes;
	}

	/**
	 * Get the dashboard URL.
	 *
	 * @return string
	 */
	public function get_dashboard_url() {
		return admin_url( 'admin.php?page=wpconsent' );
	}

	/**
	 * Don't load the regular header on this page.
	 *
	 * @return void
	 */
	public function output_header() {
		?>
		<div class="wpconsent-onboarding-progress-bar">
			<div class="wpconsent-onboarding-progress-bar-inner"></div>
		</div>
		<div class="wpconsent-onboarding-header">
			<div class="wpconsent-column">
			</div>
			<div class="wpconsent-column">
			</div>
		</div>
	}

	/**
	 * Page content.
	 *
	 * @return void
	 */
	public function output_content() {
		?>
		<div class="wpconsent-onboarding-content">
			<div class="wpconsent-onboarding-content-inner">
				foreach ( $this->steps as $step ) {
					$this->step_markup( $step );
				}
				?>
			</div>
		</div>
	}

	/**
	 * Step markup.
	 *
	 * @param string $step The step.
	 *
	 * @return void
	 */
	public function step_markup( $step ) {
		$method = 'step_' . $step;
		if ( method_exists( $this, $method ) ) {
			$this->$method();
			++ $this->current_step;
		}
	}

	/**
	 * Step 1 content.
	 *
	 * @return void
	 */
	public function step_welcome() {
		?>
			<div class="wpconsent-onboarding-image">
			</div>
			<div class="wpconsent-onboarding-text">
			</div>
			<div class="wpconsent-onboarding-buttons">
			</div>
		</div>
	}

	/**
	 * Scan step content.
	 *
	 * @return void
	 */
	public function step_scan() {
		$this->auto_populate_important_pages();
		?>
			<div class="wpconsent-onboarding-text">
			</div>
			<div class="wpconsent-onboarding-buttons">
				<p class="wpconsent-disclaimer">
					printf(
					// translators: %1$s is an opening link tag, %2$s is a closing link tag, %3$s is an opening link tag.
						esc_html__( 'Please Note: By continuing with the website scan, you agree to send website data to our API for processing. This data is utilized to improve scanning accuracy and provide updated service and cookie descriptions. For details, please review our %1$sPrivacy Policy%2$s and %3$sTerms of Service%2$s.', 'wpconsent-cookies-banner-privacy-suite' ),
						'<a href="' . esc_url( wpconsent_utm_url( 'https://wpconsent.com/privacy-policy/', 'onboarding', 'privacy-policy' ) ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>',
						'<a href="' . esc_url( wpconsent_utm_url( 'https://wpconsent.com/terms/', 'onboarding', 'terms-of-service' ) ) . '" target="_blank" rel="noopener noreferrer">'
					);
					?>
				</p>
			</div>

			<div class="wpconsent-footer-buttons">
			</div>
		</div>
		$selected_content_ids = wpconsent()->settings->get_option( 'manual_scan_pages', array() );
		if ( ! empty( $selected_content_ids ) ) {
			foreach ( $selected_content_ids as $page_id ) {
				$page = get_post( $page_id );
				if ( $page ) {
					?>
				}
			}
		}
	}

	/**
	 * Scan form.
	 *
	 * @return void
	 */
	public function scan_form() {
		// Get admin email.
		$current_user = wp_get_current_user();
		$email        = $current_user->user_email;
		?>
		<div class="wpconsent-onboarding-license-key">
			echo $this->get_input_email(
				'scanner-email',
				esc_attr( $email ),
				esc_html__( 'We\'ll send you recommendations based on the scan results. You can unsubscribe at any time.', 'wpconsent-cookies-banner-privacy-suite' )
			);
			?>
		</div>
	}

	/**
	 * Configure scan step.
	 *
	 * @return void
	 */
	public function step_configure() {
		?>
			<div class="wpconsent-onboarding-text">
			</div>
			<form id="wpconsent-onboarding-services">
				<div class="wpconsent-onboarding-selectable-list">

				</div>
				<div class="wpconsent-onboarding-buttons">
					<label class="wpconsent-inline-styled-checkbox">
						<span class="wpconsent-styled-checkbox checked">
							<input type="checkbox" name="script_blocking" checked/>
						</span>
						printf(
						// translators: %1$s is an opening link tag, %2$s is a closing link tag.
							esc_html__( 'Prevent known scripts from adding cookies before consent is given. %1$sLearn More%2$s', 'wpconsent-cookies-banner-privacy-suite' ),
							'<a target="_blank" rel="noopener noreferrer" href="' . esc_url( wpconsent_utm_url( 'https://wpconsent.com/docs/automatic-script-blocking', 'onboarding', 'scripb-blocking' ) ) . '">',
							'</a>'
						);
						?>
					</label>
				</div>
				<input type="hidden" name="action" value="wpconsent_auto_configure"/>
			</form>
		</div>
	}

	/**
	 * Banner step content.
	 *
	 * @return void
	 */
	public function step_banner() {
		$banner_design_url = admin_url( 'admin.php?page=wpconsent-banner' );
		?>
			<div class="wpconsent-onboarding-text">
			</div>
			<form id="wpconsent-onboarding-banner-layout">
				<div class="wpconsent-onboarding-banner">
					echo $this->image_radio( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'banner_layout',
						$this->get_banner_layouts(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						esc_html( wpconsent()->settings->get_option( 'banner_layout', 'long' ) ),
						'large'
					);
					?>
					<div data-show-if-id='[name="banner_layout"]' data-show-if-value="long">
						echo $this->image_radio( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'banner_long_position',
							array(
								'top'    => array(
									'label' => esc_html__( 'Top', 'wpconsent-cookies-banner-privacy-suite' ),
									'img'   => esc_url( WPCONSENT_PLUGIN_URL ) . 'admin/images/banner-long-top.png',
									'width' => 105,
								),
								'bottom' => array(
									'label' => esc_html__( 'Bottom', 'wpconsent-cookies-banner-privacy-suite' ),
									'img'   => esc_url( WPCONSENT_PLUGIN_URL ) . 'admin/images/banner-long-bottom.png',
								),
							),
							esc_html( wpconsent()->settings->get_option( 'banner_position', 'top' ) )
						);
						?>
					</div>
					<div data-show-if-id='[name="banner_layout"]' data-show-if-value="floating">
						echo $this->image_radio( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'banner_floating_position',
							array(
								'left-top'     => array(
									'label' => esc_html__( 'Left Top', 'wpconsent-cookies-banner-privacy-suite' ),
									'img'   => esc_url( WPCONSENT_PLUGIN_URL . 'admin/images/banner-floating-left-top.png' ),
									'width' => 105,
								),
								'right-top'    => array(
									'label' => esc_html__( 'Right Top', 'wpconsent-cookies-banner-privacy-suite' ),
									'img'   => esc_url( WPCONSENT_PLUGIN_URL . 'admin/images/banner-floating-right-top.png' ),
								),
								'left-bottom'  => array(
									'label' => esc_html__( 'Left Bottom', 'wpconsent-cookies-banner-privacy-suite' ),
									'img'   => esc_url( WPCONSENT_PLUGIN_URL . 'admin/images/banner-floating-left-bottom.png' ),
								),
								'right-bottom' => array(
									'label' => esc_html__( 'Right Bottom', 'wpconsent-cookies-banner-privacy-suite' ),
									'img'   => esc_url( WPCONSENT_PLUGIN_URL . 'admin/images/banner-floating-right-bottom.png' ),
								),
							),
							esc_attr( wpconsent()->settings->get_option( 'banner_position', 'left-top' ) )
						);
						?>
					</div>
					<hr/>
					<div class="wpconsent-banner-preview-wrapper">
					</div>
				</div>
				<div class="wpconsent-onboarding-buttons">
					<input type="hidden" name="action" value="wpconsent_save_banner_layout"/>
				</div>
			</form>
		</div>
	}

	/**
	 * Output the footer.
	 *
	 * @return void
	 */
	public function output_footer() {
		parent::output_footer();
		?>
		<script type="text/template" id="wpconsent-onboarding-selectable-item">
			<div class="wpconsent-onboarding-selectable-item">
				<div class="wpconsent-onboarding-service-logo">
					<img src="{{logo}}" alt="{{service}}"/>
				</div>
				<div class="wpconsent-onboarding-service-info">
					<h3>{{service}}</h3>
					<p>{{description}}</p>
				</div>
				<div class="wpconsent-onboarding-service-checkbox">
					<label>
						<span class="wpconsent-styled-checkbox checked">
						<input type="checkbox" name="scanner_service[]" value="{{name}}"/>
						</span>
					</label>
				</div>
			</div>
		</script>
	}


	/**
	 * Get the banner layouts in a method we can override in child classes.
	 *
	 * @return array
	 */
	public function get_banner_layouts() {
		return array(
			'long'     => array(
				'label' => esc_html__( 'Long Banner', 'wpconsent-cookies-banner-privacy-suite' ),
				'img'   => WPCONSENT_PLUGIN_URL . 'admin/images/banner-layout-long.png',
				'width' => 98,
			),
			'floating' => array(
				'label' => esc_html__( 'Floating Banner', 'wpconsent-cookies-banner-privacy-suite' ),
				'img'   => WPCONSENT_PLUGIN_URL . 'admin/images/banner-layout-floating.png',
			),
			'modal'    => array(
				'label'           => esc_html__( 'Modal Banner', 'wpconsent-cookies-banner-privacy-suite' ),
				'img'             => WPCONSENT_PLUGIN_URL . 'admin/images/banner-layout-modal.png',
				'is_pro'          => true,
				'pro_title'       => esc_html__( 'Modal Layout is a Pro Feature', 'wpconsent-cookies-banner-privacy-suite' ),
				'pro_description' => esc_html__( 'Upgrade to WPConsent Pro to unlock the modal banner and improve the consent rate for your website.', 'wpconsent-cookies-banner-privacy-suite' ),
				'pro_link'        => wpconsent_utm_url(
					'https://wpconsent.com/lite/',
					'banner-layout',
					'modal'
				),
			),
		);
	}
}

} // namespace CharacterGeneratorDev
