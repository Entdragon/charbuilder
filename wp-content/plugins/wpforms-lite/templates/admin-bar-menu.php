<?php

namespace CharacterGeneratorDev {

/**
 * Forms selector for admin bar menu.
 *
 * @since 1.6.5
 *
 * @var array $forms_data Forms data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_notifications = $forms_data['has_notifications'] ? ' wpforms-menu-form-notifications' : '';

end( $forms_data['forms'] );
$last_key = key( $forms_data['forms'] );
?>

<script type="text/html" id="tmpl-wpforms-admin-menubar-data">
		<div class="ab-sub-wrapper">
				</li>
				</li>
				</li>
				</li>
			</ul>
		</div>
	</li>
</script>

} // namespace CharacterGeneratorDev
