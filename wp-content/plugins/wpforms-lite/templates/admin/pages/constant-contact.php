<?php

namespace CharacterGeneratorDev {

/**
 * Constant Contact page template.
 *
 * @var string $sign_up_link           Sign up link.
 * @var string $wpbeginners_guide_link Link to a WPBeginners blog post.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap wpforms-admin-wrap wpforms-constant-contact-wrap">
	<div class="wpforms-admin-content">
		<p>
			echo wp_kses(
				__( 'Email is hands-down the most effective way to nurture leads and turn them into customers, with a return on investment (ROI) of <strong>$44 back for every $1 spent</strong> according to DMA.', 'wpforms-lite' ),
				[ 'strong' => [] ]
			);
			?>
		</p>
			printf(
				'<img src="%s" srcset="%s 2x" alt="" class="logo">',
				esc_url( WPFORMS_PLUGIN_URL . 'assets/images/constant-contact/cc-about-logo.png' ),
				esc_url( WPFORMS_PLUGIN_URL . 'assets/images/constant-contact/cc-about-logo@2x.png' )
			);
			?>
		</a>
		<ol class="reasons">
			<li>
				echo wp_kses(
					__( '<strong>Email is still #1</strong> - At least 91% of consumers check their email on a daily basis. You get direct access to your subscribers, without having to play by social media\'s rules and algorithms.', 'wpforms-lite' ),
					[ 'strong' => [] ]
				);
				?>
			</li>
			<li>
				echo wp_kses(
					__( '<strong>You own your email list</strong> - Unlike with social media, your list is your property and no one can revoke your access to it.', 'wpforms-lite' ),
					[ 'strong' => [] ]
				);
				?>
			</li>
			<li>
				echo wp_kses(
					__( '<strong>Email converts</strong> - People who buy products marketed through email spend 138% more than those who don\'t receive email offers.', 'wpforms-lite' ),
					[ 'strong' => [] ]
				);
				?>
			</li>
		</ol>
		<p>
			echo sprintf(
				wp_kses( /* translators: %s - WPBeginners.com Guide to Email Lists URL. */
					__( 'For more details, see this guide on <a href="%s" target="_blank" rel="noopener noreferrer">why building your email list is so important</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( $wpbeginners_guide_link )
			);
			?>
		</p>
		<hr/>
		<ol>
		</ol>
		<p>
			</a>
		</p>
		<ul>
		</ul>
		<p>
			</a>
		</p>
		<hr/>
		<div class="steps">
			foreach (
				[
					esc_html__( 'Select from our pre-built templates, or create a form from scratch.', 'wpforms-lite' ),
					esc_html__( 'Drag and drop any field you want onto your signup form.', 'wpforms-lite' ),
					esc_html__( 'Connect your Constant Contact email list.', 'wpforms-lite' ),
					esc_html__( 'Add your new form to any post, page, or sidebar.', 'wpforms-lite' ),
				] as $index => $item
			) {
				++ $index;
				?>
				<figure class="step">
					printf(
						'<div class="step-image-wrapper"><img src="%1$s" alt=""><a href="%1$s" class="hover" data-lity></a></div><figcaption>%2$d. %3$s</figcaption>',
						esc_url( WPFORMS_PLUGIN_URL . 'assets/images/constant-contact/cc-about-step' . $index . '.png' ),
						absint( $index ),
						esc_html( $item )
					);
					?>
				</figure>
		</div>
	</div>
</div>

} // namespace CharacterGeneratorDev
