<?php

namespace CharacterGeneratorDev {

/**
 * XSL XSLSort partial for the sitemap.
 *
 * @since 4.1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
if ( empty( $data['node'] ) ) {
	return;
}

$orderBy = '';
if ( ! empty( $data['parameters']['sitemap-orderby'] ) && in_array( $data['parameters']['sitemap-orderby'], [ 'ascending', 'descending' ], true ) ) {
	$orderBy = $data['parameters']['sitemap-orderby'];
}

if ( empty( $orderBy ) ) {
	return;
}
?>

} // namespace CharacterGeneratorDev
