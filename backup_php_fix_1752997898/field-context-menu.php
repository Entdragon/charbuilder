<?php

namespace CharacterGeneratorDev {

/**
 * WPForms Builder Field Context Menu (right click) Template.
 *
 * @since 1.8.6
 */

use WPForms\Admin\Education\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wpforms-context-menu" id="wpforms-field-context-menu">
	<ul class="wpforms-context-menu-list">
		<li class="wpforms-context-menu-list-item" data-action="edit">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-pencil-square-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
			</span>
		</li>

		<li class="wpforms-context-menu-list-item" data-action="duplicate">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-files-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
			</span>
		</li>

		<li class="wpforms-context-menu-list-item" data-action="delete">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-trash-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
			</span>
		</li>

		<li class="wpforms-context-menu-list-divider" data-visibility="required, label, field-size"></li>

		<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="required">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-asterisk"></i>
			</span>

			</span>
		</li>

		<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="label">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-tag"></i>
			</span>

			</span>
		</li>

		<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-has-child" data-action="field-size">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-arrows-h"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
			</span>

			<ul class="wpforms-context-menu-list wpforms-context-menu-list-selective">
				<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="field-size" data-value="small">
					<span class="wpforms-context-menu-list-item-icon">
						<i class="fa fa-check"></i>
					</span>

					<span class="wpforms-context-menu-list-item-text">
					</span>
				</li>

				<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="field-size" data-value="medium">
					<span class="wpforms-context-menu-list-item-icon">
						<i class="fa fa-check"></i>
					</span>

					<span class="wpforms-context-menu-list-item-text">
					</span>
				</li>

				<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="field-size" data-value="large">
					<span class="wpforms-context-menu-list-item-icon">
						<i class="fa fa-check"></i>
					</span>

					<span class="wpforms-context-menu-list-item-text">
					</span>
				</li>
			</ul>
		</li>

		<li class="wpforms-context-menu-list-divider" data-visibility="smart-logic"></li>

		<li class="wpforms-context-menu-list-item" data-action="smart-logic">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-random"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
			</span>

		</li>
	</ul>
</div>

} // namespace CharacterGeneratorDev
