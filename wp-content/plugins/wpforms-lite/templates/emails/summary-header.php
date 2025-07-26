<?php

namespace CharacterGeneratorDev {

/**
 * Summary header template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/summary-header.php.
 *
 * @since 1.8.8
 *
 * @var string $title        Email title.
 * @var array  $header_image Header image arguments.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<!DOCTYPE html>
<head>
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="color-scheme" content="light dark">
</head>
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" class="body" role="presentation" bgcolor="#f8f8f8">
	<tr>
		<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
		<td align="center" valign="top" class="body-inner" width="700">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="container" role="presentation">
						<tr class="header-wrapper dark-mode">
							<td align="center" valign="middle" class="header">
								<div class="header-image">
								</div>
							</td>
						</tr>
						<tr class="header-wrapper light-mode">
							<td align="center" valign="middle" class="header">
								<div class="header-image">
								</div>
							</td>
						</tr>
					<tr>
						<td class="wrapper-inner">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
								<tr>
									<td valign="top" class="content">

} // namespace CharacterGeneratorDev
