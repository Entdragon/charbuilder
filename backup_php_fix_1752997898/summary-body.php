<?php

namespace CharacterGeneratorDev {

/**
 * Email Summary body template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/summary-body.php.
 *
 * @since 1.5.4
 * @since 1.8.8 Added `$overview`, `$has_trends`, `$notification_block`, and `$icons` parameters.
 *
 * @var array $overview           Form entries overview data.
 * @var array $entries            Form entries data to loop through.
 * @var bool  $has_trends         Whether trends data is available.
 * @var array $notification_block Notification block shown before the Info block.
 * @var array $info_block         Info block shown at the end of the email.
 * @var array $icons              Icons used for the design purposes.
 */

use WPForms\Integrations\LiteConnect\LiteConnect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<table class="summary-container" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
	<tbody>
		<tr>
			<td class="summary-content" bgcolor="#ffffff">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
					<tbody>
						<tr>
							<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
							<td class="summary-content-inner" align="center" valign="top" width="600">
								<div class="summary-header" width="100%">
										<p class="lite-disclaimer">
										</p>

											<p class="lite-disclaimer">
												printf(
													wp_kses( /* translators: %1$s - WPForms.com Upgrade page URL. */
														__( 'Your entries are being backed up securely in the cloud. When you’re ready to manage your entries inside WordPress, just <a href="%1$s" target="_blank" rel="noopener noreferrer">upgrade to Pro</a> and we’ll automatically import them in seconds!', 'wpforms-lite' ),
														[
															'a' => [
																'href'   => [],
																'rel'    => [],
																'target' => [],
															],
														]
													),
													esc_url( wpforms_utm_link( 'https://wpforms.com/lite-upgrade/', 'Weekly Summary Email', 'Upgrade' ) )
												);
												?>
											</p>
											<p class="lite-disclaimer">
												printf(
													'<a href="%1$s" target="_blank" rel="noopener noreferrer"><strong>%2$s</strong></a>',
													esc_url( wpforms_utm_link( 'https://wpforms.com/lite-upgrade/', 'Weekly Summary Email', 'Upgrade' ) ),
													esc_html__( 'Check out what else you’ll get with your Pro license.', 'wpforms-lite' )
												);
												?>
											</p>
											<p class="lite-disclaimer">
											</p>
											<p class="lite-disclaimer">
												printf(
													wp_kses( /* translators: %1$s - WPForms.com Documentation page URL. */
														__( 'Backups are completely free, 100%% secure, and you can turn them on in a few clicks! <a href="%1$s" target="_blank" rel="noopener noreferrer">Enable entry backups now.</a>', 'wpforms-lite' ),
														[
															'a' => [
																'href'   => [],
																'rel'    => [],
																'target' => [],
															],
														]
													),
													esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-use-lite-connect-for-wpforms/', 'Weekly Summary Email', 'Documentation#backup-with-lite-connect' ) )
												);
												?>
											</p>
											<p class="lite-disclaimer">
												printf(
													wp_kses( /* translators: %1$s - WPForms.com Upgrade page URL. */
														__( 'When you’re ready to manage your entries inside WordPress, <a href="%1$s" target="_blank" rel="noopener noreferrer">upgrade to Pro</a> to import your entries.', 'wpforms-lite' ),
														[
															'a' => [
																'href'   => [],
																'rel'    => [],
																'target' => [],
															],
														]
													),
													esc_url( wpforms_utm_link( 'https://wpforms.com/lite-upgrade/', 'Weekly Summary Email', 'Upgrade' ) )
												);
												?>
											</p>
								</div>
								<div class="email-summaries-overview-wrapper" width="100%">
										<table class="email-summaries-overview" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" bgcolor="#f8f8f8">
											<tbody>
											<tr>
												<td class="overview-icon" valign="top">
												</td>
												<td class="overview-stats" valign="top">
													<h5>
														printf(
														/* translators: %1$d - number of entries. */
															esc_html__( '%1$d Total', 'wpforms-lite' ),
															absint( $overview['total'] )
														);
														?>
													</h5>
													<p>
													</p>
												</td>
													<td class="summary-trend">
															<tr valign="middle">
																<td valign="middle">
																</td>
																<td dir="ltr" valign="middle">
																</td>
															</tr>
														</table>
													</td>
											</tr>
											</tbody>
										</table>
								</div>
								<div class="email-summaries-wrapper" width="100%">
									<table class="email-summaries" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
										<thead>
										<tr>
										</tr>
										</thead>
										<tbody>
												<td class="entry-count" align="center" valign="middle">
														<span>
															</span>
														</a>
												</td>
													<td class="summary-trend" align="center" valign="middle">
																<tr valign="middle">
																	<td valign="middle">
																	</td>
																	<td dir="ltr" valign="middle">
																	</td>
																</tr>
															</table>
															&mdash;
													</td>
											</tr>

											<tr>
												<td colspan="3">
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</td>
							<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
			<tr class="summary-notice" align="center">
				<td class="summary-notification-block" bgcolor="#edf3f7">
					<table class="summary-notification-table summary-notification-table" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
						<tbody>
							<tr>
								<td class="summary-notice-icon" align="center" valign="middle">
								</td>
							</tr>
							<tr>
								<td class="summary-notice-content" align="center" valign="middle">
								</td>
							</tr>

							<tr>
								<td class="button-container" align="center" valign="middle">
									<table class="button-wrapper" cellspacing="24">
										<tr>
												<td class="button button-blue" align="center" border="1" valign="middle">
													</a>
												</td>
												<td class="button button-blue-outline" align="center" border="1" valign="middle">
													</a>
												</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
				<tr><td class="summary-notice-divider" height="1">&nbsp;</td></tr>
			<tr class="summary-notice" align="center">
				<td class="summary-info-block" bgcolor="#f7f0ed">
					<table class="summary-info-table summary-notice-table" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
						<tbody>
								<tr>
									<td class="summary-notice-icon" align="center" valign="middle">
									</td>
								</tr>
								<tr>
									<td class="summary-notice-content" align="center" valign="middle">
									</td>
								</tr>

								<tr>
									<td class="button-container" align="center" valign="middle">
										<table class="button-wrapper" cellspacing="24">
											<tr>
												<td class="button button-orange" align="center" border="1" valign="middle">
													</a>
												</td>
											</tr>
										</table>
									</td>
								</tr>
						</tbody>
					</table>
				</td>
			</tr>
	</tbody>
</table>

} // namespace CharacterGeneratorDev
