<?php

namespace CharacterGeneratorDev {

/**
 * Admin paged used for the site scanner.
 *
 * @package WPConsent
 */

/**
 * Class WPConsent_Admin_Page_Cookies.
 */
class WPConsent_Admin_Page_Scanner extends WPConsent_Admin_Page {

	use WPConsent_Services_Upsell;
	use WPConsent_Scan_Pages;

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	public $page_slug = 'wpconsent-scanner';

	/**
	 * Default view.
	 *
	 * @var string
	 */
	public $view = 'scanner';

	/**
	 * Scan results.
	 *
	 * @var array
	 */
	protected $scan_results;

	/**
	 * Current action.
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * Call this just to set the page title translatable.
	 */
	public function __construct() {
		$this->page_title = __( 'Website Scanner', 'wpconsent-cookies-banner-privacy-suite' );
		$this->menu_title = __( 'Scanner', 'wpconsent-cookies-banner-privacy-suite' );
		parent::__construct();
	}

	/**
	 * Get the scan results in one place.
	 *
	 * @return array
	 */
	protected function get_scan_results() {
		if ( ! isset( $this->scan_results ) ) {
			$this->scan_results = wpconsent()->scanner->get_scan_data();
		}

		return $this->scan_results;
	}

	/**
	 * Output the page content.
	 *
	 * @return void
	 */
	public function output_content() {
		$this->metabox(
			esc_html__( 'Scan Overview', 'wpconsent-cookies-banner-privacy-suite' ),
			$this->get_scan_overview()
		);

		$scan_data = $this->get_scan_results();

		if ( empty( $scan_data['data']['scripts'] ) ) {
			return;
		}

		$this->metabox(
			__( 'Detailed Report', 'wpconsent-cookies-banner-privacy-suite' ),
			$this->get_scanner_input()
		);
	}

	/**
	 * Get the markup for the scanner.
	 *
	 * @return string
	 */
	public function get_scanner_input() {
		$previous_scan = $this->get_scan_results();
		$categories    = wpconsent()->cookies->get_categories();
		if ( ! empty( $previous_scan ) ) {
			$previous_data = $previous_scan['data'];
		}
		ob_start();
		?>
		<form action="" id="wpconsent-scanner-form">
			<p>
			</p>
			if ( ! empty( $previous_data['scripts'] ) ) {
				foreach ( $previous_data['scripts'] as $category => $services ) {
					?>
					<div class="wpconsent-onboarding-selectable-list">
						foreach ( $services as $service ) {
							$this->get_scan_service_template( $service );
						}
						?>
					</div>
				}
			}
			?>
				<label class="wpconsent-inline-styled-checkbox">
						</span>
					printf(
					// translators: %1$s is an opening link tag, %2$s is a closing link tag.
						esc_html__( 'Prevent known scripts from adding cookies before consent is given. %1$sLearn More%2$s', 'wpconsent-cookies-banner-privacy-suite' ),
						'<a target="_blank" rel="noopener noreferrer" href="' . esc_url( wpconsent_utm_url( 'https://wpconsent.com/docs/automatic-script-blocking', 'onboarding', 'scripb-blocking' ) ) . '">',
						'</a>'
					);
					?>
				</label>
				<div id="wpconsent-scanner-actions" class="wpconsent-metabox-form-row">
				</div>
			</div>
			<input type="hidden" name="action" value="wpconsent_auto_configure"/>
		</form>
		return ob_get_clean();
	}

	/**
	 * Get the markup for a scanner service.
	 *
	 * @param array $data Service data.
	 *
	 * @return void
	 */
	public function get_scan_service_template( $data ) {
		?>
		<div class="wpconsent-onboarding-selectable-item wpconsent-show-hidden-container">
			<div class="wpconsent-onboarding-service-logo">
			</div>
			<div class="wpconsent-onboarding-service-info">
				<div class="wpconsent-service-info-buttons">
				</div>
					<ul class="wpconsent-scanner-service-cookies-list wpconsent-hidden-preview">
					</ul>
			</div>
			<div class="wpconsent-onboarding-service-checkbox">
				<label>
					<span class="wpconsent-styled-checkbox checked">
					</span>
				</label>
			</div>
		</div>
	}

	/**
	 * Returns the scan overview markup.
	 *
	 * @return string
	 */
	public function get_scan_overview() {
		$scan_results = $this->get_scan_results();

		if ( ! empty( $scan_results ) ) {
			$scripts            = $scan_results['data']['scripts'];
			$last_ran_timestamp = $scan_results['date'];

			$scripts_count = 0;
			$cookies_count = 0;
			foreach ( $scripts as $category => $services ) {
				$scripts_count += count( $services );
				foreach ( $services as $service ) {
					$cookies_count += count( $service['cookies'] );
				}
			}
			$next_scheduled_scan = esc_html__( 'Not Scheduled', 'wpconsent-cookies-banner-privacy-suite' );
			if ( wpconsent()->settings->get_option( 'auto_scanner', 0 ) ) {
				$interval            = wpconsent()->settings->get_option( 'auto_scanner_interval', 1 );
				$last_ran            = wpconsent()->settings->get_option( 'auto_scanner_last_ran', 0 );
				$next_scheduled_scan = date_i18n( get_option( 'date_format' ), strtotime( "+{$interval} days", $last_ran ) );
			}

			$stats_to_show = array(
				array(
					'label' => __( 'Services Detected', 'wpconsent-cookies-banner-privacy-suite' ),
					'value' => $scripts_count,
				),
				array(
					'label' => __( 'Cookies In Use', 'wpconsent-cookies-banner-privacy-suite' ),
					'value' => $cookies_count,
				),
				array(
					'label' => __( 'Last Successful Scan', 'wpconsent-cookies-banner-privacy-suite' ),
					'value' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_ran_timestamp ) ),
				),
				array(
					'label' => __( 'Cookies Configured', 'wpconsent-cookies-banner-privacy-suite' ),
					'value' => isset( $scan_results['configured'] ),
				),
				array(
					'label' => __( 'Next Scheduled Scan', 'wpconsent-cookies-banner-privacy-suite' ),
					'value' => $next_scheduled_scan,
				),
			);
		}
		ob_start();
		if ( ! empty( $stats_to_show ) ) {
			?>
			<div class="wpconsent-scan-overview">
					<div class="wpconsent-scan-overview-stat">
						<p>
					</div>
			</div>

		$this->metabox_row_separator();
		echo $this->get_manual_scan_input();  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>

		<div class="wpconsent-metabox-form-row">
		</div>
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
		return ob_get_clean();
	}

	/**
	 * Get the markup for the manual scan input.
	 *
	 * @return string
	 */
	public function get_manual_scan_input() {
		// Auto-populate important pages.
		$this->auto_populate_important_pages();

		ob_start();
		$selected_content_ids = wpconsent()->settings->get_option( 'manual_scan_pages', array() );

		$pages_args = array(
			'number'  => 20,
			'orderby' => 'title',
			'order'   => 'ASC',
		);
		if ( ! empty( $selected_content_ids ) ) {
			$pages_args['exclude'] = $selected_content_ids;
		}
		// Let's pre-load 20 pages.
		$pages = get_pages( $pages_args );
		?>
		<div class="wpconsent-manual-scan-description">
		</div>
		<div class="wpconsent-manual-scan-row">
			<div class="wpconsent-inline-select-group">
					foreach ( $pages as $page ) {
						?>
						</option>
					}
					?>
				</select>
			</div>
			<div class="wpconsent-scanner-selected-items-container">
				<!-- Homepage always scanned -->
				<div class="wpconsent-scanner-selected-item homepage" id="scanner-item-home">
					<div class="wpconsent-scanner-selected-item-info">
					</div>
				</div>
				// Load saved selections.
				if ( ! empty( $selected_content_ids ) ) {
					foreach ( $selected_content_ids as $page_id ) {
						$page = get_post( $page_id );
						if ( $page ) {
							?>
								<div class="wpconsent-scanner-selected-item-info">
									</a>
								</div>
									<span class="dashicons dashicons-no-alt"></span>
								</button>
							</div>
						}
					}
				}
				?>
			</div>
		</div>

		return ob_get_clean();
	}
}

} // namespace CharacterGeneratorDev
