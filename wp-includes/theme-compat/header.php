<?php

namespace CharacterGeneratorDev {

/**
 * @package WordPress
 * @subpackage Theme_Compat
 * @deprecated 3.0.0
 *
 * This file is here for backward compatibility with old themes and will be removed in a future version.
 */
_deprecated_file(
	/* translators: %s: Template name. */
	sprintf( __( 'Theme without %s' ), basename( __FILE__ ) ),
	'3.0.0',
	null,
	/* translators: %s: Template name. */
	sprintf( __( 'Please include a %s template in your theme.' ), basename( __FILE__ ) )
);
?>
<!DOCTYPE html>
<head>
<link rel="profile" href="https://gmpg.org/xfn/11" />



<style type="text/css" media="screen">

	// Checks to see whether it needs a sidebar.
	if ( empty( $withcomments ) && ! is_single() ) {
		?>

</style>

if ( is_singular() ) {
	wp_enqueue_script( 'comment-reply' );
}
?>

</head>
<div id="page">

<div id="header" role="banner">
	<div id="headerimg">
	</div>
</div>
<hr />

} // namespace CharacterGeneratorDev
