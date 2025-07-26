<?php

namespace CharacterGeneratorDev {

/**
 * XSL Breadcrumb partial for the sitemap.
 *
 * @since 4.1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
if ( empty( $data['datetime'] ) || empty( $data['node'] ) ) {
	return;
}

?>
<div class="date">
	<xsl:choose>
	</xsl:choose>
</div>
<div class="time">
	<xsl:choose>
	</xsl:choose>
</div>
} // namespace CharacterGeneratorDev
