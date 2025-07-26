<?php

namespace CharacterGeneratorDev {

/**
 * Admin paged used to Manage Geolocation.
 *
 * @package WPConsent
 */

/**
 * Class WPConsent_Admin_Page_Geolocation
 */
class WPConsent_Admin_Page_Geolocation extends WPConsent_Admin_Page {
	/**
	 * Page slug.
	 *
	 * @var string
	 */
	public $page_slug = 'wpconsent-geolocation';

	/**
	 * Call this just to set the page title translatable.
	 */
	public function __construct() {
		$this->page_title = __( 'Geolocation', 'wpconsent-cookies-banner-privacy-suite' );
		parent::__construct();
	}

	/**
	 * Override the output method so we can add our form markup for this page.
	 *
	 * @return void
	 */
	public function output() {
		$this->output_header();
		?>
		<div class="wpconsent-content">
			<div class="wpconsent-blur-area">
				$this->output_content();
				do_action( "wpconsent_admin_page_content_{$this->page_slug}", $this );
				?>
			</div>
			echo WPConsent_Admin_page::get_upsell_box( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html__( 'Geolocation is a PRO feature', 'wpconsent-cookies-banner-privacy-suite' ),
				'<p>' . esc_html__( 'Upgrade to WPConsent PRO today and personalize the display of your cookie banner to show only in the specific countries or regions you choose.', 'wpconsent-cookies-banner-privacy-suite' ) . '</p>',
				array(
					'text' => esc_html__( 'Upgrade to PRO and Unlock "Geolocation"', 'wpconsent-cookies-banner-privacy-suite' ),
					'url'  => esc_url( wpconsent_utm_url( 'https://wpconsent.com/lite/', 'geolocation-page', 'main' ) ),
				),
				array(
					'text' => esc_html__( 'Learn more about all the features', 'wpconsent-cookies-banner-privacy-suite' ),
					'url'  => esc_url( wpconsent_utm_url( 'https://wpconsent.com/lite/', 'geolocation-page', 'features' ) ),
				)
			);
			?>
		</div>
	}

	/**
	 * Output the content
	 */
	public function output_content() {
		?>
		<div class="wpconsent-geolocation-container">
		</div>
	}

	/**
	 * Output location groups management interface.
	 *
	 * @return void
	 */
	public function output_location_groups_management() {
		$location_groups = $this->get_location_groups();
		?>
		<div class="wpconsent-location-groups-section">
		</div>
	}

	/**
	 * Output the list of existing location groups.
	 *
	 * @param array $location_groups Array of location groups.
	 *
	 * @return void
	 */
	public function output_location_groups_list( $location_groups ) {
		echo $this->get_location_groups_list_content( $location_groups );
	}

	/**
	 * Get the content for the location groups list.
	 *
	 * @param array $location_groups Array of location groups.
	 *
	 * @return string
	 */
	public function get_location_groups_list_content( $location_groups ) {
		if ( empty( $location_groups ) ) {
			return '<p class="wpconsent-empty-state">' . __( 'No location groups have been created yet. Choose one of the pre-configured templates or create a custom rule.', 'wpconsent-cookies-banner-privacy-suite' ) . '</p>';
		}

		ob_start();
		?>
		<table class="wp-list-table widefat fixed striped wpconsent-location-groups-table">
			<thead>
			<tr>
				<th scope="col" class="manage-column column-checkbox"><input type="checkbox"/></th>
			</tr>
			</thead>
			<tbody>
					<td class="column-checkbox">
						<input type="checkbox"/>
					</td>
					<td class="column-name">
					</td>
					<td class="column-locations">
						echo wp_kses( $this->format_locations_display( $group['locations'] ), array( 'span' => array( 'class' => array() ) ) );
						?>
					</td>
					<td class="column-type">
					</td>
					<td class="column-consent-settings">
						$consent_settings = array(
							'enable_script_blocking'  => __( 'Block Script', 'wpconsent-cookies-banner-privacy-suite' ),
							'show_banner'             => __( 'Show Banner', 'wpconsent-cookies-banner-privacy-suite' ),
							'enable_consent_floating' => __( 'Show Settings Button', 'wpconsent-cookies-banner-privacy-suite' ),
						);

						foreach ( $consent_settings as $key => $label ) {
							$is_enabled      = ! empty( $group[ $key ] );
							$checkmark_class = $is_enabled ? 'consent-setting-checkmark-enabled' : 'consent-setting-checkmark-disabled';
							$checkmark       = $is_enabled ? '✓' : '✗';
							?>
							<div class="consent-setting-item">
							</div>
					</td>
					<td class="column-mode">
					</td>
					<td class="column-action">
						</button>
						</button>
					</td>
				</tr>
			</tbody>
			<tfoot>
			<tr>
				<th scope="col" class="manage-column column-checkbox"><input type="checkbox"/></th>
			</tr>
			</tfoot>
		</table>
		return ob_get_clean();
	}

	/**
	 * Output predefined rules metabox.
	 *
	 * @return void
	 */
	public function output_predefined_rules_metabox() {
		$content = $this->get_predefined_rules_content();
		$this->metabox(
			__( 'Location-based Rules', 'wpconsent-cookies-banner-privacy-suite' ),
			$content,
			__( 'Quickly add predefined rules for common privacy regulations. Each rule will automatically configure the relevant countries and settings for the location.', 'wpconsent-cookies-banner-privacy-suite' )
		);
	}


	/**
	 * Get predefined rules content.
	 *
	 * @return string
	 */
	public function get_predefined_rules_content() {
		ob_start();
		?>
		<div class="">
		</div>
		<div class="wpconsent-predefined-rules">
			<div class="wpconsent-predefined-rule">
				<button type="button" class="wpconsent-button wpconsent-add-location-group">
				</button>
			</div>

			<div class="wpconsent-predefined-rule">
				<button type="button" class="wpconsent-button wpconsent-add-predefined-rule" data-rule="gdpr">
				</button>
			</div>

			<div class="wpconsent-predefined-rule">
				<button type="button" class="wpconsent-button wpconsent-add-predefined-rule" data-rule="ccpa">
				</button>
			</div>

			<div class="wpconsent-predefined-rule">
				<button type="button" class="wpconsent-button wpconsent-add-predefined-rule" data-rule="lgpd">
				</button>
			</div>
		</div>
		return ob_get_clean();
	}


	/**
	 * Format locations for display.
	 *
	 * @param array $locations Array of location data.
	 *
	 * @return string
	 */
	public function format_locations_display( $locations ) {
		return $locations;
	}

	/**
	 * Get dummy location groups for the initial setup.
	 *
	 * @return array An array of location groups, where each group contains details such as
	 *               name, associated locations, and type of consent.
	 */
	public function get_location_groups() {
		return array(
			array(
				'name'                    => 'GDPR Compliance',
				'locations'               => 'Europe',
				'type_of_consent'         => 'GDPR',
				'enable_script_blocking'  => true,
				'show_banner'             => true,
				'enable_consent_floating' => true,
			),
			array(
				'name'                    => 'CCPA',
				'locations'               => 'California, USA',
				'type_of_consent'         => 'CCPA',
				'enable_script_blocking'  => true,
				'show_banner'             => true,
				'enable_consent_floating' => true,
				'consent_mode'            => 'optout',
			),
		);
	}
}

} // namespace CharacterGeneratorDev
