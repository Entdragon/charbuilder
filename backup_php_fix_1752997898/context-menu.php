<?php

namespace CharacterGeneratorDev {

/**
 * WPForms Builder Context Menu (top) Template, Lite version.
 *
 * @since 1.8.8
 *
 * @var int  $form_id          The form ID.
 * @var bool $is_form_template Whether it's a form template (`wpforms-template`), or form (`wpforms`).
 * @var bool $has_payments     Whether the form has payments.
 * @var bool $show_whats_new   Whether to show the What's New menu item.
 */

use WPForms\Admin\Education\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
?>

<div class="wpforms-context-menu wpforms-context-menu-dropdown" id="wpforms-context-menu">
	<ul class="wpforms-context-menu-list">


			<li class="wpforms-context-menu-list-item"
				data-action="duplicate-template"
			>
				<span class="wpforms-context-menu-list-item-icon">
					<i class="fa fa-copy"></i>
				</span>

				<span class="wpforms-context-menu-list-item-text">
				</span>
			</li>


			<li class="wpforms-context-menu-list-item"
				data-action="duplicate-form"
			>
				<span class="wpforms-context-menu-list-item-icon">
					<i class='fa fa-copy'></i>
				</span>

				<span class="wpforms-context-menu-list-item-text">
				</span>
			</li>

			<li class="wpforms-context-menu-list-item"
				data-action="save-as-template"
			>
				<span class="wpforms-context-menu-list-item-icon">
					<i class="fa fa-file-text-o"></i>
				</span>

				<span class="wpforms-context-menu-list-item-text">
				</span>
			</li>


		<li class='wpforms-context-menu-list-divider'></li>

		<li class="wpforms-context-menu-list-item education-modal"
			data-action="upgrade"
			data-license="pro"
			data-name="Entries"
			data-utm-content="Upgrade to Pro - Entries Context Menu Item"
		>
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-envelope-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
			</span>

		</li>

			data-action="view-payments"
		>
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-money"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
			</span>
		</li>

		<li class="wpforms-context-menu-list-divider"></li>

		<li class="wpforms-context-menu-list-item"
			data-action="keyboard-shortcuts"
		>
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-keyboard-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
			</span>
		</li>


			<li class="wpforms-context-menu-list-item"
				data-action="whats-new"
			>
				<span class="wpforms-context-menu-list-item-icon">
					<i class="fa fa-bullhorn"></i>
				</span>

				<span class="wpforms-context-menu-list-item-text">
				</span>
			</li>


	</ul>
</div>

} // namespace CharacterGeneratorDev
