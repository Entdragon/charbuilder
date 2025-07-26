<?php

namespace CharacterGeneratorDev {

/**
 * Email Footer.
 *
 * @since 7.10.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$background_color = '#e9eaec';
?>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
	<td valign="top" id="templateFooter"
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock"
			   style="min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
			<tbody class="mcnTextBlockOuter">
			<tr>
				<td valign="top" class="mcnTextBlockInner"
					style="mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
					<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%"
						   style="min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"
						   class="mcnTextContentContainer">
						<tbody>
						<tr>
							<td valign="top" class="mcnTextContent"
								style="padding-top: 9px;padding-right: 18px;padding-bottom: 9px;padding-left: 18px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;word-break: break-word;color: #aaa;font-family: Helvetica;font-size: 12px;line-height: 150%;text-align: center;">

								<!-- Footer content -->
								/* translators: %s - link to a site. */
								$footer = sprintf( esc_html__( 'Sent from %s', 'google-analytics-for-wordpress' ), '<a href="' . esc_url( home_url() ) . '" style="color:#bbbbbb;">' . wp_specialchars_decode( get_bloginfo( 'name' ) ) . '</a>' );
								echo apply_filters( 'monsterinsights_email_footer_text', $footer ); // phpcs:ignore
								?>

							</td>
						</tr>
						</tbody>
					</table>
				</td>
			</tr>
			</tbody>
		</table>
	</td>
</tr>
</table>
<!--[if gte mso 9]>
</td>
</tr>
</table>
<![endif]-->
<!-- // END TEMPLATE -->
</td>
</tr>
</table>
</center>
</body>
</html>

} // namespace CharacterGeneratorDev
