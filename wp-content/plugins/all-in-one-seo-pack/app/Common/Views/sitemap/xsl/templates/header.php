<?php

namespace CharacterGeneratorDev {

/**
 * XSL Header template for the sitemap.
 *
 * @since 4.1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable
?>
<xsl:template name="Header">
	<xsl:param name="title"/>
	<xsl:param name="amountOfURLs"/>
	<xsl:param name="fileType"/>

	<div id="content-head">
		<h1><xsl:value-of select="$title"/></h1>
		<xsl:choose>
			<xsl:when test="$fileType='RSS'">
				<p>
						// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
						printf( __( 'You can find more information about RSS Sitemaps at %1$ssitemaps.org%2$s.', 'all-in-one-seo-pack' ), '<a href="https://www.sitemaps.org/" target="_blank" rel="noreferrer noopener">', '</a>');
					?>
				</p>
			</xsl:when>
			<xsl:otherwise>
				<p>
						// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
						printf( __( 'You can find more information about XML Sitemaps at %1$ssitemaps.org%2$s.', 'all-in-one-seo-pack' ), '<a href="https://www.sitemaps.org/" target="_blank" rel="noreferrer noopener">', '</a>');
					?>
				</p>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="$amountOfURLs &gt; 0">
			<p>
				<xsl:choose>
					<xsl:when test="$fileType='Sitemap' or $fileType='RSS'">
						<xsl:value-of select="$amountOfURLs"/>
						<xsl:choose>
							<xsl:when test="$amountOfURLs = 1">
							</xsl:when>
							<xsl:otherwise>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$amountOfURLs"/>
						<xsl:choose>
							<xsl:when test="$amountOfURLs = 1">
							</xsl:when>
							<xsl:otherwise>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
					echo sprintf(
						// Translators: 1 - The generated date, 2 - The generated time.
						__( 'and was generated on %1$s at %2$s', 'all-in-one-seo-pack' ),
						date_i18n( get_option( 'date_format' ) ),
						date_i18n( get_option( 'time_format' ) )
					); 
				?>
			</p>
		</xsl:if>
	</div>
</xsl:template>

} // namespace CharacterGeneratorDev
