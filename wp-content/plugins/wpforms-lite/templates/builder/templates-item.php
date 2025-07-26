<?php

namespace CharacterGeneratorDev {

/**
 * Panel Setup (form templates).
 * Form templates list item template.
 *
 * @since 1.6.8
 * @since 1.8.4 Added subcategories attribute.
 * @since 1.8.6 Added fields attribute.
 * @since 1.8.8 Added edit button attributes.
 *
 * @var bool   $selected             Is template selected.
 * @var string $license_class        License class (in the case of higher license needed).
 * @var string $categories           Categories, coma separated.
 * @var string $subcategories        Subcategories, comma separated.
 * @var string $fields               Fields, comma separated.
 * @var string $badge_text           Badge text.
 * @var string $demo_url             Template demo URL.
 * @var string $template_id          Template ID (Slug or ID if available).
 * @var string $education_class      Education class (in the case of higher license needed).
 * @var string $education_attributes Education attributes.
 * @var string $addons_attributes    Required addons attributes.
 * @var array  $template             Template data.
 * @var string $action_text          Template action button text.
 * @var string $create_url           User template creation URL.
 * @var string $edit_url             User template edit URL.
 * @var string $edit_action_text     User template edit button text.
 * @var string $badge_class          Badge class in case if there is any badge text exists.
 * @var bool   $can_create           Capability to create forms.
 * @var bool   $can_edit             Capability to edit forms (more granular for template - own, others).
 * @var bool   $can_delete           Capability to delete forms (more granular for template - own, others).
 * @var bool   $is_open              Is user template currently open in the builder.
 * @var int    $post_id              Post ID.
 */

use WPForms\Admin\Education\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_template_class = $template['source'] === 'wpforms-user-template' ? ' wpforms-user-template' : '';

?>

	<div class="wpforms-template-thumbnail">
			<div class="wpforms-template-thumbnail-placeholder">
			</div>
	</div>

	<!-- As requirement for Lists.js library data attribute slug is used in classes list. -->
	<h3 class="wpforms-template-name categories has-access favorite slug subcategories fields"
	>
	</h3>

		</span>
		<span class="wpforms-template-favorite">
		</span>

	if ( ! empty( $badge_text ) && ! $selected ) {
		Helpers::print_badge( $badge_text, 'sm', 'corner', 'steel', 'rounded-bl' );
	}
	?>

	<p class='wpforms-template-desc'>
	</p>

		<div class="wpforms-template-buttons">
			</a>

				<a class="wpforms-template-demo wpforms-btn wpforms-btn-md wpforms-btn-light-grey"
					target="_blank" rel="noopener noreferrer">
				</a>

				<a class="wpforms-template-edit wpforms-btn wpforms-btn-md wpforms-btn-light-grey"
				</a>
		</div>

</div>

} // namespace CharacterGeneratorDev
