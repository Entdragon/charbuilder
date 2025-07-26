<?php

namespace CharacterGeneratorDev {

/**
 * Display a multiselect field for filtering the payments overview table.
 *
 * @since 1.8.4
 *
 * @var string $name          Name of the select field.
 * @var array  $options       Select field options.
 * @var array  $selected      Array of selected options.
 * @var array  $data_settings Data settings for the multiselect JS instance.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Leave early if no filters are provided.
if ( empty( $options ) ) {
	return '';
}

?>
<select
	multiple
	class="wpforms-multiselect wpforms-hide"
>
	</option>
</select>

} // namespace CharacterGeneratorDev
