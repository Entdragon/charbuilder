<?php

namespace CharacterGeneratorDev {

/**
 * Do Not Track admin page.
 *
 * @package WPConsent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPConsent_Admin_Page_Do_Not_Track
 */
class WPConsent_Admin_Page_Do_Not_Track extends WPConsent_Admin_Page {

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	public $page_slug = 'wpconsent-do-not-track';

	/**
	 * Default view.
	 *
	 * @var string
	 */
	public $view = 'requests';

	/**
	 * Call this just to set the page title translatable.
	 */
	public function __construct() {
		$this->page_title = __( 'Do Not Track', 'wpconsent-cookies-banner-privacy-suite' );
		$this->menu_title = __( 'Do Not Track', 'wpconsent-cookies-banner-privacy-suite' );
		parent::__construct();
	}

	/**
	 * Page specific Hooks.
	 *
	 * @return void
	 */
	public function page_hooks() {
		$this->views = array(
			'requests'      => __( 'Requests', 'wpconsent-cookies-banner-privacy-suite' ),
			'configuration' => __( 'Configuration', 'wpconsent-cookies-banner-privacy-suite' ),
			'export'        => __( 'Export', 'wpconsent-cookies-banner-privacy-suite' ),
		);
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
				esc_html__( 'Do Not Track Addon is a premium feature', 'wpconsent-cookies-banner-privacy-suite' ),
				'<p>' . esc_html__( 'Upgrade to WPConsent Plus or higher plans today and improve the way you manage "Do Not Track or Sell My Information" requests.', 'wpconsent-cookies-banner-privacy-suite' ) . '</p>',
				array(
					'text' => esc_html__( 'Upgrade to PRO and Unlock "Do Not Track"', 'wpconsent-cookies-banner-privacy-suite' ),
					'url'  => esc_url( wpconsent_utm_url( 'https://wpconsent.com/lite/', 'do-not-track-page', 'main' ) ),
				),
				array(
					'text' => esc_html__( 'Learn more about all the features', 'wpconsent-cookies-banner-privacy-suite' ),
					'url'  => esc_url( wpconsent_utm_url( 'https://wpconsent.com/lite/', 'do-not-track-page', 'features' ) ),
				),
				array(
					esc_html__( 'Customizable requests form', 'wpconsent-cookies-banner-privacy-suite' ),
					esc_html__( 'Easily export to CSV', 'wpconsent-cookies-banner-privacy-suite' ),
					esc_html__( 'Mark requests as processed', 'wpconsent-cookies-banner-privacy-suite' ),
					esc_html__( 'Avoid Spam with an easy configuration', 'wpconsent-cookies-banner-privacy-suite' ),
					esc_html__( '1-click Do Not Track page creation', 'wpconsent-cookies-banner-privacy-suite' ),
					esc_html__( 'Self-Hosted records for compliance proof', 'wpconsent-cookies-banner-privacy-suite' ),
				)
			);
			?>
		</div>
	}

	/**
	 * The page output based on the view.
	 *
	 * @return void
	 */
	public function output_content() {
		if ( method_exists( $this, 'output_view_' . $this->view ) ) {
			call_user_func( array( $this, 'output_view_' . $this->view ) );
		}
	}

	/**
	 * For this page we output a menu.
	 *
	 * @return void
	 */
	public function output_header_bottom() {
		?>
		<ul class="wpconsent-admin-tabs">
			foreach ( $this->views as $slug => $label ) {
				$class = $this->view === $slug ? 'active' : '';
				?>
				<li>
				</li>
		</ul>
	}

	/**
	 * Output the requests view.
	 *
	 * @return void
	 */
	protected function output_view_requests() {
		// Output a dummy table that looks exactly like the one in the addon.
		?>
		<div class="wpconsent-admin-content-section">


			<div class="wpconsent-dnt-requests-table">
				<form method="post">
					<input type="hidden" name="view" value="requests"/>

					<div class="tablenav top">
						<div class="alignleft actions bulkactions">
							<select name="action" id="bulk-action-selector-top">
							</select>
						</div>
						<div class="tablenav-pages">
						</div>
						<br class="clear">
					</div>

					<table class="wp-list-table widefat fixed striped">
						<thead>
						<tr>
							<td id="cb" class="manage-column column-cb check-column">
								<input id="cb-select-all-1" type="checkbox">
							</td>
						</tr>
						</thead>
						<tbody>
						// Dummy data for the table.
						$dummy_data = array(
							array(
								'request_id'     => 1,
								'first_name'     => 'John',
								'last_name'      => 'Doe',
								'email'          => 'john.doe@example.com',
								'city'           => 'New York',
								'state'          => 'NY',
								'country'        => 'USA',
								'request_status' => 'received',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-2 days' ) ),
							),
							array(
								'request_id'     => 2,
								'first_name'     => 'Jane',
								'last_name'      => 'Smith',
								'email'          => 'jane.smith@example.com',
								'city'           => 'London',
								'state'          => '',
								'country'        => 'UK',
								'request_status' => 'confirmed',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-5 days' ) ),
							),
							array(
								'request_id'     => 3,
								'first_name'     => 'Robert',
								'last_name'      => 'Johnson',
								'email'          => 'robert.johnson@example.com',
								'city'           => 'Paris',
								'state'          => '',
								'country'        => 'France',
								'request_status' => 'processed',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-10 days' ) ),
								'processed_at'   => date( 'Y-m-d H:i:s', strtotime( '-8 days' ) ),
							),
							array(
								'request_id'     => 4,
								'first_name'     => 'Maria',
								'last_name'      => 'Garcia',
								'email'          => 'maria.garcia@example.com',
								'city'           => 'Madrid',
								'state'          => '',
								'country'        => 'Spain',
								'request_status' => 'received',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
							),
							array(
								'request_id'     => 5,
								'first_name'     => 'Michael',
								'last_name'      => 'Brown',
								'email'          => 'michael.brown@example.com',
								'city'           => 'Sydney',
								'state'          => 'NSW',
								'country'        => 'Australia',
								'request_status' => 'processed',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-15 days' ) ),
								'processed_at'   => date( 'Y-m-d H:i:s', strtotime( '-12 days' ) ),
							),
							array(
								'request_id'     => 6,
								'first_name'     => 'Emma',
								'last_name'      => 'Wilson',
								'email'          => 'emma.wilson@example.com',
								'city'           => 'Toronto',
								'state'          => 'ON',
								'country'        => 'Canada',
								'request_status' => 'received',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-3 days' ) ),
							),
							array(
								'request_id'     => 7,
								'first_name'     => 'David',
								'last_name'      => 'Lee',
								'email'          => 'david.lee@example.com',
								'city'           => 'Tokyo',
								'state'          => '',
								'country'        => 'Japan',
								'request_status' => 'confirmed',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-7 days' ) ),
							),
							array(
								'request_id'     => 8,
								'first_name'     => 'Sophia',
								'last_name'      => 'Martinez',
								'email'          => 'sophia.martinez@example.com',
								'city'           => 'Berlin',
								'state'          => '',
								'country'        => 'Germany',
								'request_status' => 'processed',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-20 days' ) ),
								'processed_at'   => date( 'Y-m-d H:i:s', strtotime( '-18 days' ) ),
							),
							array(
								'request_id'     => 9,
								'first_name'     => 'James',
								'last_name'      => 'Taylor',
								'email'          => 'james.taylor@example.com',
								'city'           => 'Dublin',
								'state'          => '',
								'country'        => 'Ireland',
								'request_status' => 'received',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
							),
							array(
								'request_id'     => 10,
								'first_name'     => 'Olivia',
								'last_name'      => 'Anderson',
								'email'          => 'olivia.anderson@example.com',
								'city'           => 'Stockholm',
								'state'          => '',
								'country'        => 'Sweden',
								'request_status' => 'confirmed',
								'created_at'     => date( 'Y-m-d H:i:s', strtotime( '-4 days' ) ),
							),
						);

						foreach ( $dummy_data as $item ) :
							$status_labels = array(
								'received'  => __( 'Received', 'wpconsent-cookies-banner-privacy-suite' ),
								'confirmed' => __( 'Confirmed', 'wpconsent-cookies-banner-privacy-suite' ),
								'processed' => __( 'Processed', 'wpconsent-cookies-banner-privacy-suite' ),
							);

							$status_text = isset( $status_labels[ $item['request_status'] ] ) ? $status_labels[ $item['request_status'] ] : $item['request_status'];

							// Add processed date if available.
							if ( 'processed' === $item['request_status'] && isset( $item['processed_at'] ) ) {
								$processed_at = date_i18n( get_option( 'date_format' ), strtotime( $item['processed_at'] ) );
								$status_text  .= ' <span class="description">' . sprintf(
									/* translators: %s: date */
										__( 'on %s', 'wpconsent-cookies-banner-privacy-suite' ),
										$processed_at
									) . '</span>';
							}

							// Build location string.
							$location = array();
							if ( ! empty( $item['city'] ) ) {
								$location[] = $item['city'];
							}
							if ( ! empty( $item['state'] ) ) {
								$location[] = $item['state'];
							}
							if ( ! empty( $item['country'] ) ) {
								$location[] = $item['country'];
							}
							$location_text = implode( ', ', $location );

							// Row actions for non-processed items.
							$row_actions = '';
							if ( 'processed' !== $item['request_status'] ) {
								$row_actions = '<div class="row-actions"><span class="mark_processed"><a href="#">' . __( 'Mark as Processed', 'wpconsent-cookies-banner-privacy-suite' ) . '</a></span></div>';
							}
							?>
							<tr>
								<th scope="row" class="check-column">
								</th>
								<td class="name column-name">
								</td>
							</tr>
						</tbody>
						<tfoot>
						<tr>
							<td class="manage-column column-cb check-column">
								<input id="cb-select-all-2" type="checkbox">
							</td>
						</tr>
						</tfoot>
					</table>

					<div class="tablenav bottom">
						<div class="alignleft actions bulkactions">
							<select name="action2" id="bulk-action-selector-bottom">
							</select>
						</div>
						<div class="tablenav-pages">
						</div>
						<br class="clear">
					</div>
				</form>
			</div>
		</div>
	}

	/**
	 * Output the configuration view.
	 *
	 * @return void
	 */
	protected function output_view_configuration() {
		// Display a preview of the configuration form from the addon

		// Available fields that would be in the form
		$available_fields = array(
			'first_name' => __( 'First Name', 'wpconsent-cookies-banner-privacy-suite' ),
			'last_name'  => __( 'Last Name', 'wpconsent-cookies-banner-privacy-suite' ),
			'email'      => __( 'Email', 'wpconsent-cookies-banner-privacy-suite' ),
			'address'    => __( 'Address', 'wpconsent-cookies-banner-privacy-suite' ),
		);

		// Required fields that cannot be .
		$required_fields = array( 'first_name', 'last_name', 'email' );

		?>
		<div class="wpconsent-admin-content-section">

			<form method="post" action="">
				<div class="wpconsent-admin-settings-section">
					<table class="form-table">
						<tr>
							<th scope="row">
							</th>
							<td>
								<div class="wpconsent-inline-select-group">
									<select id="dnt_page_id" name="dnt_page_id" class="regular-text">
										$pages = get_pages(
											array(
												'number'  => 20,
												'orderby' => 'title',
												'order'   => 'ASC',
											)
										);

										foreach ( $pages as $page ) {
											echo '<option value="' . esc_attr( $page->ID ) . '">' . esc_html( $page->post_title ) . '</option>';
										}
										?>
									</select>
								</div>
								<p>
									<button type="button" class="button button-secondary">
									</button>
								</p>
								<p class="description">
								</p>
							</td>
						</tr>
					</table>
				</div>

				<div class="wpconsent-admin-settings-section">

					<table class="form-table">
						<tr>
							<th scope="row">
							</th>
							<td>
							</td>
						</tr>
						foreach ( $available_fields as $field_key => $default_label ) {
							$is_core_field = in_array( $field_key, $required_fields, true );
							?>
							<tr>
								<th scope="row">
								</th>
								<td>
									<fieldset>
										<label>
										</label>
										<br>
										<label>
										</label>
										<br>
										<label>
										</label>
									</fieldset>
								</td>
							</tr>
					</table>
				</div>

				<div class="wpconsent-admin-settings-section">
				</div>

				<p class="submit">
				</p>
			</form>
		</div>
	}


	/**
	 * Output the export view.
	 *
	 * @return void
	 */
	public function output_view_export() {
		?>
		<div class="wpconsent-admin-content-section wpconsent-admin-content-section-dnt-export">


			<form method="post" action="" id="wpconsent-dnt-export-form">

				<div class="wpconsent-export-section">
					<table class="form-table">
						<tr>
							<th scope="row">
							</th>
							<td>
								<input type="date" id="export-date-from" name="date_from" class="wpconsent-date-input">
							</td>
						</tr>
						<tr>
							<th scope="row">
							</th>
							<td>
								<input type="date" id="export-date-to" name="date_to" class="wpconsent-date-input">
							</td>
						</tr>
						<tr>
							<th scope="row">
							</th>
							<td>
								<fieldset>
									<label for="export-only-not-processed">
										<input type="checkbox" id="export-only-not-processed" name="export_only_not_processed" value="1">
									</label>

									<label for="mark-as-processed">
										<input type="checkbox" id="mark-as-processed" name="mark_as_processed" value="1">
									</label>
								</fieldset>
							</td>
						</tr>
					</table>

					<div class="wpconsent-metabox-form-row">
						<button id="wpconsent-dnt-export" class="button button-primary" type="button">
						</button>
						<div id="wpconsent-dnt-export-progress" style="display: none;">
							<div class="wpconsent-progress-bar">
								<div class="wpconsent-progress-bar-inner"></div>
							</div>
							<div class="wpconsent-progress-status">
								<span class="wpconsent-progress-percentage">0%</span>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	}
}

} // namespace CharacterGeneratorDev
