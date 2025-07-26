<?php

namespace CharacterGeneratorDev {


if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\Icons;

$iconPath = CUSTOMTABLES_MEDIA_WEBPATH . 'images/icons/';

?>

<table class="form-table" role="presentation">
	<tr class="form-field form-required">
		<th scope="row">
			<label for="toolbaricons">
			</label>
		</th>
		<td>
			$vlu = get_option('customtables-toolbaricons', ''); // Default is empty

			$types = [
				'' => esc_html__('Legacy Image Icons', 'customtables'),
				'ultimate-member' => esc_html__('Ultimate Member (Font Awesome)', 'customtables'), // Added UM icon set
				'font-awesome-4' => esc_html__('Font Awesome 4', 'customtables'),
				'font-awesome-6' => esc_html__('Font Awesome 6', 'customtables'),
				'bootstrap' => esc_html__('Bootstrap', 'customtables')
			];
			?>

			<select name="toolbaricons" id="toolbaricons">
					</option>
			</select>
			<br/>
			<p>Choose the icon style for your toolbar interface.</p>
			<ul class="description">
			</ul>

			<!-- Icon Preview -->
			<h4>Preview:</h4>
			<iframe id="icon-preview-frame" src="" width="300" height="400" style="border: #cccccc;border-radius: 10px;"
					aria-live="polite"></iframe>
		</td>
	</tr>
</table>


<script>
	document.addEventListener("DOMContentLoaded", function () {
		const toolbarIcons = document.getElementById("toolbaricons");
		const frame = document.getElementById("icon-preview-frame");

		function updatePreview() {
			const selected = toolbarIcons.value;
			let content = "";

			const style = `
            <style>
                body {
                    font-family: Arial, sans-serif; /* Change this to any preferred font */
                    font-size: 14px;

                    color: #333;
                }
                ul {
                    list-style: none;

                }
                li {

                }
                img {
                    vertical-align: middle;
                    
                }
            </style>
        `;

			if (selected === "font-awesome-4") {
				content = `
    <!DOCTYPE html>
    <html>
    <head>
        ${style}
        <!-- Link to Font Awesome 4 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css?v=1.0">
    </head>
    <body>${document.getElementById('ui-font-awesome-4').innerHTML}</body>
    </html>`;
			} else if (selected === "font-awesome-5") {
				content = `
    <!DOCTYPE html>
    <html>
    <head>
        ${style}
        <!-- Link to Font Awesome 5 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css?v=1.0">
    </head>
    <body>${document.getElementById('ui-font-awesome-5').innerHTML}</body>
    </html>`;
			} else if (selected === "font-awesome-6") {
				content = `
    <!DOCTYPE html>
    <html>
    <head>
        ${style}
        <!-- Link to Font Awesome 6 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css?v=1.0">
    </head>
    <body>${document.getElementById('ui-font-awesome-6').innerHTML}</body>
    </html>`;
			} else if (selected === "bootstrap") {
				content = `
    <!DOCTYPE html>
    <html>
    <head>
        ${style}
        <!-- Link to Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css?v=1.0">
    </head>
    <body>${document.getElementById('ui-bootstrap').innerHTML}</body>
    </html>`;
			} else if (selected === "ultimate-member") {
				content = `
    <!DOCTYPE html>
    <html>
    <head>
        ${style}
    </head>
    <body>${document.getElementById('ui-ultimate-member').innerHTML}</body>
    </html>`;
			} else {
				content = `
    <!DOCTYPE html>
    <html>
    <head>${style}</head>
    <body>${document.getElementById('ui-images').innerHTML}</body>
    </html>`;
			}


			const doc = frame.contentDocument || frame.contentWindow.document;
			doc.open();
			doc.write(content);
			doc.close();
		}

		toolbarIcons.addEventListener("change", updatePreview);
		updatePreview(); // Trigger on page load
	});

</script>

} // namespace CharacterGeneratorDev
