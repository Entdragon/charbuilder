<?php

namespace CharacterGeneratorDev {

/**
 * WordPress media templates.
 *
 * @package WordPress
 * @subpackage Media
 * @since 3.5.0
 */

/**
 * Outputs the markup for an audio tag to be used in an Underscore template
 * when data.model is passed.
 *
 * @since 3.9.0
 */
function wp_underscore_audio_template() {
	$audio_types = wp_get_audio_extensions();
	?>
<audio style="visibility: hidden"
	controls
	class="wp-audio-shortcode"
	width="{{ _.isUndefined( data.model.width ) ? 400 : data.model.width }}"
	preload="{{ _.isUndefined( data.model.preload ) ? 'none' : data.model.preload }}"
	<#
	foreach ( array( 'autoplay', 'loop' ) as $attr ) :
		?>
	}
>
	<# if ( ! _.isEmpty( data.model.src ) ) { #>
	<source src="{{ data.model.src }}" type="{{ wp.media.view.settings.embedMimes[ data.model.src.split('.').pop() ] }}" />
	<# } #>

	foreach ( $audio_types as $type ) :
		?>
	<# } #>
	endforeach;
	?>
</audio>
}

/**
 * Outputs the markup for a video tag to be used in an Underscore template
 * when data.model is passed.
 *
 * @since 3.9.0
 */
function wp_underscore_video_template() {
	$video_types = wp_get_video_extensions();
	?>
<#  var w_rule = '', classes = [],
		w, h, settings = wp.media.view.settings,
		isYouTube = isVimeo = false;

	if ( ! _.isEmpty( data.model.src ) ) {
		isYouTube = data.model.src.match(/youtube|youtu\.be/);
		isVimeo = -1 !== data.model.src.indexOf('vimeo');
	}

	if ( settings.contentWidth && data.model.width >= settings.contentWidth ) {
		w = settings.contentWidth;
	} else {
		w = data.model.width;
	}

	if ( w !== data.model.width ) {
		h = Math.ceil( ( data.model.height * w ) / data.model.width );
	} else {
		h = data.model.height;
	}

	if ( w ) {
		w_rule = 'width: ' + w + 'px; ';
	}

	if ( isYouTube ) {
		classes.push( 'youtube-video' );
	}

	if ( isVimeo ) {
		classes.push( 'vimeo-video' );
	}

#>
<div style="{{ w_rule }}" class="wp-video">
<video controls
	class="wp-video-shortcode {{ classes.join( ' ' ) }}"
	<# if ( w ) { #>width="{{ w }}"<# } #>
	<# if ( h ) { #>height="{{ h }}"<# } #>
	$props = array(
		'poster'  => '',
		'preload' => 'metadata',
	);
	foreach ( $props as $key => $value ) :
		if ( empty( $value ) ) {
			?>
		<#
		} #>
		} else {
			echo $key
			?>
		}
	endforeach;
	?>
	<#
	foreach ( array( 'autoplay', 'loop' ) as $attr ) :
		?>
	}
>
	<# if ( ! _.isEmpty( data.model.src ) ) {
		if ( isYouTube ) { #>
		<source src="{{ data.model.src }}" type="video/youtube" />
		<# } else if ( isVimeo ) { #>
		<source src="{{ data.model.src }}" type="video/vimeo" />
		<# } else { #>
		<source src="{{ data.model.src }}" type="{{ settings.embedMimes[ data.model.src.split('.').pop() ] }}" />
		<# }
	} #>

	foreach ( $video_types as $type ) :
		?>
	<# } #>
	{{{ data.model.content }}}
</video>
</div>
}

/**
 * Prints the templates used in the media manager.
 *
 * @since 3.5.0
 */
function wp_print_media_templates() {
	$class = 'media-modal wp-core-ui';

	$alt_text_description = sprintf(
		/* translators: 1: Link to tutorial, 2: Additional link attributes, 3: Accessibility text. */
		__( '<a href="%1$s" %2$s>Learn how to describe the purpose of the image%3$s</a>. Leave empty if the image is purely decorative.' ),
		/* translators: Localized tutorial, if one exists. W3C Web Accessibility Initiative link has list of existing translations. */
		esc_url( __( 'https://www.w3.org/WAI/tutorials/images/decision-tree/' ) ),
		'target="_blank"',
		sprintf(
			'<span class="screen-reader-text"> %s</span>',
			/* translators: Hidden accessibility text. */
			__( '(opens in a new tab)' )
		)
	);
	?>

	<script type="text/html" id="tmpl-media-frame">
		<div class="media-frame-title" id="media-frame-title"></div>
		<button type="button" class="button button-link media-frame-menu-toggle" aria-expanded="false">
			<span class="dashicons dashicons-arrow-down" aria-hidden="true"></span>
		</button>
		<div class="media-frame-menu"></div>
		<div class="media-frame-tab-panel">
			<div class="media-frame-router"></div>
			<div class="media-frame-content"></div>
		</div>
		<h2 class="media-frame-actions-heading screen-reader-text">
			/* translators: Hidden accessibility text. */
			_e( 'Selected media actions' );
		?>
		</h2>
		<div class="media-frame-toolbar"></div>
		<div class="media-frame-uploader"></div>
	</script>

	<script type="text/html" id="tmpl-media-modal">
			<# if ( data.hasCloseButton ) { #>
				<button type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">
					/* translators: Hidden accessibility text. */
					_e( 'Close dialog' );
					?>
				</span></span></button>
			<# } #>
			<div class="media-modal-content" role="document"></div>
		</div>
		<div class="media-modal-backdrop"></div>
	</script>

	<script type="text/html" id="tmpl-uploader-window">
		<div class="uploader-window-content">
		</div>
	</script>

	<script type="text/html" id="tmpl-uploader-editor">
		<div class="uploader-editor-content">
		</div>
	</script>

	<script type="text/html" id="tmpl-uploader-inline">
		<# var messageClass = data.message ? 'has-upload-message' : 'no-upload-message'; #>
		<# if ( data.canClose ) { #>
		<button class="close dashicons dashicons-no"><span class="screen-reader-text">
			/* translators: Hidden accessibility text. */
			_e( 'Close uploader' );
			?>
		</span></button>
		<# } #>
		<div class="uploader-inline-content {{ messageClass }}">
		<# if ( data.message ) { #>
			<h2 class="upload-message">{{ data.message }}</h2>
		<# } #>
			<div class="upload-ui">
				<p>
					printf(
						/* translators: %s: https://apps.wordpress.org/ */
						__( 'The web browser on your device cannot be used to upload files. You may be able to use the <a href="%s">native app for your device</a> instead.' ),
						'https://apps.wordpress.org/'
					);
				?>
				</p>
			</div>
			<div class="upload-ui">
				/** This action is documented in wp-admin/includes/media.php */
				do_action( 'upload_ui_over_quota' );
				?>
			</div>
			<div class="upload-ui">
			</div>

			<div class="upload-inline-status"></div>

			<div class="post-upload-ui" id="post-upload-info">
				/** This action is documented in wp-admin/includes/media.php */
				do_action( 'pre-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				/** This action is documented in wp-admin/includes/media.php */
				do_action( 'pre-plupload-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

				if ( 10 === remove_action( 'post-plupload-upload-ui', 'media_upload_flash_bypass' ) ) {
					/** This action is documented in wp-admin/includes/media.php */
					do_action( 'post-plupload-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
					add_action( 'post-plupload-upload-ui', 'media_upload_flash_bypass' );
				} else {
					/** This action is documented in wp-admin/includes/media.php */
					do_action( 'post-plupload-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				}

				$max_upload_size = wp_max_upload_size();
				if ( ! $max_upload_size ) {
					$max_upload_size = 0;
				}
				?>

				<p class="max-upload-size">
					printf(
						/* translators: %s: Maximum allowed file size. */
						__( 'Maximum upload file size: %s.' ),
						esc_html( size_format( $max_upload_size ) )
					);
				?>
				</p>

				<# if ( data.suggestedWidth && data.suggestedHeight ) { #>
					<p class="suggested-dimensions">
							/* translators: 1: Suggested width number, 2: Suggested height number. */
							printf( __( 'Suggested image dimensions: %1$s by %2$s pixels.' ), '{{data.suggestedWidth}}', '{{data.suggestedHeight}}' );
						?>
					</p>
				<# } #>

				/** This action is documented in wp-admin/includes/media.php */
				do_action( 'post-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				?>
			</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-media-library-view-switcher">
			<span class="screen-reader-text">
				/* translators: Hidden accessibility text. */
				_e( 'List view' );
				?>
			</span>
		</a>
			<span class="screen-reader-text">
				/* translators: Hidden accessibility text. */
				_e( 'Grid view' );
				?>
			</span>
		</a>
	</script>

	<script type="text/html" id="tmpl-uploader-status">

		<div class="media-progress-bar"><div></div></div>
		<div class="upload-details">
			<span class="upload-count">
				<span class="upload-index"></span> / <span class="upload-total"></span>
			</span>
			<span class="upload-detail-separator">&ndash;</span>
			<span class="upload-filename"></span>
		</div>
		<div class="upload-errors"></div>
	</script>

	<script type="text/html" id="tmpl-uploader-status-error">
		<span class="upload-error-filename word-wrap-break-word">{{{ data.filename }}}</span>
		<span class="upload-error-message">{{ data.message }}</span>
	</script>

	<script type="text/html" id="tmpl-edit-attachment-frame">
		<div class="edit-media-header">
		</div>
		<div class="media-frame-title"></div>
		<div class="media-frame-content"></div>
	</script>

	<script type="text/html" id="tmpl-attachment-details-two-column">
		<div class="attachment-media-view {{ data.orientation }}">
			if ( isset( $_GET['error'] ) && 'deprecated' === $_GET['error'] ) {
				wp_admin_notice(
					__( 'The Edit Media screen is deprecated as of WordPress 6.3. Please use the Media Library instead.' ),
					array(
						'id'                 => 'message',
						'additional_classes' => array( 'error' ),
					)
				);
			}
			?>
			<div class="thumbnail thumbnail-{{ data.type }}">
				<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div></div></div>
				<# } else if ( data.sizes && data.sizes.full ) { #>
					<img class="details-image" src="{{ data.sizes.full.url }}" draggable="false" alt="" />
				<# } else if ( data.sizes && data.sizes.large ) { #>
					<img class="details-image" src="{{ data.sizes.large.url }}" draggable="false" alt="" />
				<# } else if ( -1 === jQuery.inArray( data.type, [ 'audio', 'video' ] ) ) { #>
					<img class="details-image icon" src="{{ data.icon }}" draggable="false" alt="" />
				<# } #>

				<# if ( 'audio' === data.type ) { #>
				<div class="wp-media-wrapper wp-audio">
					<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
						<source type="{{ data.mime }}" src="{{ data.url }}" />
					</audio>
				</div>
				<# } else if ( 'video' === data.type ) {
					var w_rule = '';
					if ( data.width ) {
						w_rule = 'width: ' + data.width + 'px;';
					} else if ( wp.media.view.settings.contentWidth ) {
						w_rule = 'width: ' + wp.media.view.settings.contentWidth + 'px;';
					}
				#>
				<div style="{{ w_rule }}" class="wp-media-wrapper wp-video">
					<video controls="controls" class="wp-video-shortcode" preload="metadata"
						<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
						<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
						<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
						<source type="{{ data.mime }}" src="{{ data.url }}" />
					</video>
				</div>
				<# } #>

				<div class="attachment-actions">
					<# if ( 'image' === data.type && ! data.uploading && data.sizes && data.can.save ) { #>
					<# } else if ( 'pdf' === data.subtype && data.sizes ) { #>
					<# } #>
				</div>
			</div>
		</div>
		<div class="attachment-info">
			<span class="settings-save-status" role="status">
				<span class="spinner"></span>
			</span>
			<div class="details">
				<h2 class="screen-reader-text">
					/* translators: Hidden accessibility text. */
					_e( 'Details' );
					?>
				</h2>
				<div class="uploaded-by">
						<# if ( data.authorLink ) { #>
							<a href="{{ data.authorLink }}">{{ data.authorName }}</a>
						<# } else { #>
							{{ data.authorName }}
						<# } #>
				</div>
				<# if ( data.uploadedToTitle ) { #>
					<div class="uploaded-to">
						<# if ( data.uploadedToLink ) { #>
							<a href="{{ data.uploadedToLink }}">{{ data.uploadedToTitle }}</a>
						<# } else { #>
							{{ data.uploadedToTitle }}
						<# } #>
					</div>
				<# } #>
				<# if ( 'image' === data.type && ! data.uploading ) { #>
					<# if ( data.width && data.height ) { #>
							/* translators: 1: A number of pixels wide, 2: A number of pixels tall. */
							printf( __( '%1$s by %2$s pixels' ), '{{ data.width }}', '{{ data.height }}' );
							?>
						</div>
					<# } #>

					<# if ( data.originalImageURL && data.originalImageName ) { #>
						<div class="word-wrap-break-word">
							<a href="{{ data.originalImageURL }}">{{data.originalImageName}}</a>
						</div>
					<# } #>
				<# } #>

				<# if ( data.fileLength && data.fileLengthHumanReadable ) { #>
						<span aria-hidden="true">{{ data.fileLengthHumanReadable }}</span>
						<span class="screen-reader-text">{{ data.fileLengthHumanReadable }}</span>
					</div>
				<# } #>

				<# if ( 'audio' === data.type && data.meta.bitrate ) { #>
					<div class="bitrate">
						<# if ( data.meta.bitrate_mode ) { #>
						{{ ' ' + data.meta.bitrate_mode.toUpperCase() }}
						<# } #>
					</div>
				<# } #>

				<# if ( data.mediaStates ) { #>
				<# } #>

				<div class="compat-meta">
					<# if ( data.compat && data.compat.meta ) { #>
						{{{ data.compat.meta }}}
					<# } #>
				</div>
			</div>

			<div class="settings">
				<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
				<# if ( 'image' === data.type ) { #>
					<span class="setting alt-text has-description" data-setting="alt">
						<textarea id="attachment-details-two-column-alt-text" aria-describedby="alt-text-description" {{ maybeReadOnly }}>{{ data.alt }}</textarea>
					</span>
				<# } #>
				<span class="setting" data-setting="title">
					<input type="text" id="attachment-details-two-column-title" value="{{ data.title }}" {{ maybeReadOnly }} />
				</span>
				<# if ( 'audio' === data.type ) { #>
				foreach ( array(
					'artist' => __( 'Artist' ),
					'album'  => __( 'Album' ),
				) as $key => $label ) :
					?>
				</span>
				<# } #>
				<span class="setting" data-setting="caption">
					<textarea id="attachment-details-two-column-caption" {{ maybeReadOnly }}>{{ data.caption }}</textarea>
				</span>
				<span class="setting" data-setting="description">
					<textarea id="attachment-details-two-column-description" {{ maybeReadOnly }}>{{ data.description }}</textarea>
				</span>
				<span class="setting" data-setting="url">
					<input type="text" class="attachment-details-copy-link" id="attachment-details-two-column-copy-link" value="{{ data.url }}" readonly />
					<span class="copy-to-clipboard-container">
					</span>
				</span>
				<div class="attachment-compat"></div>
			</div>

			<div class="actions">
				<# if ( data.link ) { #>
					$view_media_text = ( '1' === get_option( 'wp_attachment_pages_enabled' ) ) ? __( 'View attachment page' ) : __( 'View media file' );
					?>
				<# } #>
				<# if ( data.can.save ) { #>
					<# if ( data.link ) { #>
						<span class="links-separator">|</span>
					<# } #>
				<# } #>
				<# if ( data.can.save && data.link ) { #>
					<span class="links-separator">|</span>
				<# } #>
				<# if ( ! data.uploading && data.can.remove ) { #>
					<# if ( data.link || data.can.save ) { #>
						<span class="links-separator">|</span>
					<# } #>
						<# if ( 'trash' === data.status ) { #>
						<# } else { #>
						<# } #>
				<# } #>
			</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-attachment">
		<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">
			<div class="thumbnail">
				<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div style="width: {{ data.percent }}%"></div></div>
				<# } else if ( 'image' === data.type && data.size && data.size.url ) { #>
					<div class="centered">
						<img src="{{ data.size.url }}" draggable="false" alt="" />
					</div>
				<# } else { #>
					<div class="centered">
						<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
							<img src="{{ data.image.src }}" class="thumbnail" draggable="false" alt="" />
						<# } else if ( data.sizes ) { 
								if ( data.sizes.medium ) { #>
									<img src="{{ data.sizes.medium.url }}" class="thumbnail" draggable="false" alt="" />
								<# } else { #>
									<img src="{{ data.sizes.full.url }}" class="thumbnail" draggable="false" alt="" />
								<# } #>
						<# } else { #>
							<img src="{{ data.icon }}" class="icon" draggable="false" alt="" />
						<# } #>
					</div>
					<div class="filename">
						<div>{{ data.filename }}</div>
					</div>
				<# } #>
			</div>
			<# if ( data.buttons.close ) { #>
				<button type="button" class="button-link attachment-close media-modal-icon"><span class="screen-reader-text">
					/* translators: Hidden accessibility text. */
					_e( 'Remove' );
					?>
				</span></button>
			<# } #>
		</div>
		<# if ( data.buttons.check ) { #>
			<button type="button" class="check" tabindex="-1"><span class="media-modal-icon"></span><span class="screen-reader-text">
				/* translators: Hidden accessibility text. */
				_e( 'Deselect' );
				?>
			</span></button>
		<# } #>
		<#
		var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly';
		if ( data.describe ) {
			if ( 'image' === data.type ) { #>
				<input type="text" value="{{ data.caption }}" class="describe" data-setting="caption"
			<# } else { #>
				<input type="text" value="{{ data.title }}" class="describe" data-setting="title"
					<# if ( 'video' === data.type ) { #>
					<# } else if ( 'audio' === data.type ) { #>
					<# } else { #>
					<# } #> {{ maybeReadOnly }} />
			<# }
		} #>
	</script>

	<script type="text/html" id="tmpl-attachment-details">
		<h2>
			<span class="settings-save-status" role="status">
				<span class="spinner"></span>
			</span>
		</h2>
		<div class="attachment-info">

			<# if ( 'audio' === data.type ) { #>
				<div class="wp-media-wrapper wp-audio">
					<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
						<source type="{{ data.mime }}" src="{{ data.url }}" />
					</audio>
				</div>
			<# } else if ( 'video' === data.type ) {
				var w_rule = '';
				if ( data.width ) {
					w_rule = 'width: ' + data.width + 'px;';
				} else if ( wp.media.view.settings.contentWidth ) {
					w_rule = 'width: ' + wp.media.view.settings.contentWidth + 'px;';
				}
			#>
				<div style="{{ w_rule }}" class="wp-media-wrapper wp-video">
					<video controls="controls" class="wp-video-shortcode" preload="metadata"
						<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
						<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
						<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
						<source type="{{ data.mime }}" src="{{ data.url }}" />
					</video>
				</div>
			<# } else { #>
				<div class="thumbnail thumbnail-{{ data.type }}">
					<# if ( data.uploading ) { #>
						<div class="media-progress-bar"><div></div></div>
					<# } else if ( 'image' === data.type && data.size && data.size.url ) { #>
						<img src="{{ data.size.url }}" draggable="false" alt="" />
					<# } else { #>
						<img src="{{ data.icon }}" class="icon" draggable="false" alt="" />
					<# } #>
				</div>
			<# } #>

			<div class="details">
				<div class="filename">{{ data.filename }}</div>
				<div class="uploaded">{{ data.dateFormatted }}</div>

				<div class="file-size">{{ data.filesizeHumanReadable }}</div>
				<# if ( 'image' === data.type && ! data.uploading ) { #>
					<# if ( data.width && data.height ) { #>
						<div class="dimensions">
							/* translators: 1: A number of pixels wide, 2: A number of pixels tall. */
							printf( __( '%1$s by %2$s pixels' ), '{{ data.width }}', '{{ data.height }}' );
							?>
						</div>
					<# } #>

					<# if ( data.originalImageURL && data.originalImageName ) { #>
						<div class="word-wrap-break-word">
							<a href="{{ data.originalImageURL }}">{{data.originalImageName}}</a>
						</div>
					<# } #>

					<# if ( data.can.save && data.sizes ) { #>
					<# } #>
				<# } #>

				<# if ( data.fileLength && data.fileLengthHumanReadable ) { #>
						<span aria-hidden="true">{{ data.fileLengthHumanReadable }}</span>
						<span class="screen-reader-text">{{ data.fileLengthHumanReadable }}</span>
					</div>
				<# } #>

				<# if ( data.mediaStates ) { #>
				<# } #>

				<# if ( ! data.uploading && data.can.remove ) { #>
					<# if ( 'trash' === data.status ) { #>
					<# } else { #>
					<# } #>
				<# } #>

				<div class="compat-meta">
					<# if ( data.compat && data.compat.meta ) { #>
						{{{ data.compat.meta }}}
					<# } #>
				</div>
			</div>
		</div>
		<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
		<# if ( 'image' === data.type ) { #>
			<span class="setting alt-text has-description" data-setting="alt">
				<textarea id="attachment-details-alt-text" aria-describedby="alt-text-description" {{ maybeReadOnly }}>{{ data.alt }}</textarea>
			</span>
		<# } #>
		<span class="setting" data-setting="title">
			<input type="text" id="attachment-details-title" value="{{ data.title }}" {{ maybeReadOnly }} />
		</span>
		<# if ( 'audio' === data.type ) { #>
		foreach ( array(
			'artist' => __( 'Artist' ),
			'album'  => __( 'Album' ),
		) as $key => $label ) :
			?>
		</span>
		<# } #>
		<span class="setting" data-setting="caption">
			<textarea id="attachment-details-caption" {{ maybeReadOnly }}>{{ data.caption }}</textarea>
		</span>
		<span class="setting" data-setting="description">
			<textarea id="attachment-details-description" {{ maybeReadOnly }}>{{ data.description }}</textarea>
		</span>
		<span class="setting" data-setting="url">
			<input type="text" class="attachment-details-copy-link" id="attachment-details-copy-link" value="{{ data.url }}" readonly />
			<div class="copy-to-clipboard-container">
			</div>
		</span>
	</script>

	<script type="text/html" id="tmpl-media-selection">
		<div class="selection-info">
			<span class="count"></span>
			<# if ( data.editable ) { #>
			<# } #>
			<# if ( data.clearable ) { #>
			<# } #>
		</div>
		<div class="selection-view"></div>
	</script>

	<script type="text/html" id="tmpl-attachment-display-settings">

		<# if ( 'image' === data.type ) { #>
			<span class="setting align">
				<select id="attachment-display-settings-alignment" class="alignment"
					data-setting="align"
					<# if ( data.userSettings ) { #>
						data-user-setting="align"
					<# } #>>

					<option value="left">
					</option>
					<option value="center">
					</option>
					<option value="right">
					</option>
					<option value="none" selected>
					</option>
				</select>
			</span>
		<# } #>

		<span class="setting">
			<label for="attachment-display-settings-link-to" class="name">
				<# if ( data.model.canEmbed ) { #>
				<# } else { #>
				<# } #>
			</label>
			<select id="attachment-display-settings-link-to" class="link-to"
				data-setting="link"
				<# if ( data.userSettings && ! data.model.canEmbed ) { #>
					data-user-setting="urlbutton"
				<# } #>>

			<# if ( data.model.canEmbed ) { #>
				<option value="embed" selected>
				</option>
				<option value="file">
			<# } else { #>
				<option value="none" selected>
				</option>
				<option value="file">
			<# } #>
				<# if ( data.model.canEmbed ) { #>
				<# } else { #>
				<# } #>
				</option>
				<option value="post">
				<# if ( data.model.canEmbed ) { #>
				<# } else { #>
				<# } #>
				</option>
			<# if ( 'image' === data.type ) { #>
				<option value="custom">
				</option>
			<# } #>
			</select>
		</span>
		<span class="setting">
			<input type="text" id="attachment-display-settings-link-to-custom" class="link-to-custom" data-setting="linkUrl" />
		</span>

		<# if ( 'undefined' !== typeof data.sizes ) { #>
			<span class="setting">
				<select id="attachment-display-settings-size" class="size" name="size"
					data-setting="size"
					<# if ( data.userSettings ) { #>
						data-user-setting="imgsize"
					<# } #>>
					/** This filter is documented in wp-admin/includes/media.php */
					$sizes = apply_filters(
						'image_size_names_choose',
						array(
							'thumbnail' => __( 'Thumbnail' ),
							'medium'    => __( 'Medium' ),
							'large'     => __( 'Large' ),
							'full'      => __( 'Full Size' ),
						)
					);

					foreach ( $sizes as $value => $name ) :
						?>
						<#
						if ( size ) { #>
							</option>
						<# } #>
				</select>
			</span>
		<# } #>
	</script>

	<script type="text/html" id="tmpl-gallery-settings">

		<span class="setting">
			<select id="gallery-settings-link-to" class="link-to"
				data-setting="link"
				<# if ( data.userSettings ) { #>
					data-user-setting="urlbutton"
				<# } #>>

				<option value="post" <# if ( ! wp.media.galleryDefaults.link || 'post' === wp.media.galleryDefaults.link ) {
					#>selected="selected"<# }
				#>>
				</option>
				<option value="file" <# if ( 'file' === wp.media.galleryDefaults.link ) { #>selected="selected"<# } #>>
				</option>
				<option value="none" <# if ( 'none' === wp.media.galleryDefaults.link ) { #>selected="selected"<# } #>>
				</option>
			</select>
		</span>

		<span class="setting">
			<select id="gallery-settings-columns" class="columns" name="columns"
				data-setting="columns">
					#>>
					</option>
			</select>
		</span>

		<span class="setting">
			<input type="checkbox" id="gallery-settings-random-order" data-setting="_orderbyRandom" />
		</span>

		<span class="setting size">
			<select id="gallery-settings-size" class="size" name="size"
				data-setting="size"
				<# if ( data.userSettings ) { #>
					data-user-setting="imgsize"
				<# } #>
				>
				/** This filter is documented in wp-admin/includes/media.php */
				$size_names = apply_filters(
					'image_size_names_choose',
					array(
						'thumbnail' => __( 'Thumbnail' ),
						'medium'    => __( 'Medium' ),
						'large'     => __( 'Large' ),
						'full'      => __( 'Full Size' ),
					)
				);

				foreach ( $size_names as $size => $label ) :
					?>
					</option>
			</select>
		</span>
	</script>

	<script type="text/html" id="tmpl-playlist-settings">

		<# var emptyModel = _.isEmpty( data.model ),
			isVideo = 'video' === data.controller.get('library').props.get('type'); #>

		<span class="setting">
			<input type="checkbox" id="playlist-settings-show-list" data-setting="tracklist" <# if ( emptyModel ) { #>
				checked="checked"
			<# } #> />
			<label for="playlist-settings-show-list" class="checkbox-label-inline">
				<# if ( isVideo ) { #>
				<# } else { #>
				<# } #>
			</label>
		</span>

		<# if ( ! isVideo ) { #>
		<span class="setting">
			<input type="checkbox" id="playlist-settings-show-artist" data-setting="artists" <# if ( emptyModel ) { #>
				checked="checked"
			<# } #> />
			<label for="playlist-settings-show-artist" class="checkbox-label-inline">
			</label>
		</span>
		<# } #>

		<span class="setting">
			<input type="checkbox" id="playlist-settings-show-images" data-setting="images" <# if ( emptyModel ) { #>
				checked="checked"
			<# } #> />
			<label for="playlist-settings-show-images" class="checkbox-label-inline">
			</label>
		</span>
	</script>

	<script type="text/html" id="tmpl-embed-link-settings">
		<span class="setting link-text">
			<input type="text" id="embed-link-settings-link-text" class="alignment" data-setting="linkText" />
		</span>
		<div class="embed-container" style="display: none;">
			<div class="embed-preview"></div>
		</div>
	</script>

	<script type="text/html" id="tmpl-embed-image-settings">
		<div class="wp-clearfix">
			<div class="thumbnail">
				<img src="{{ data.model.url }}" draggable="false" alt="" />
			</div>
		</div>

		<span class="setting alt-text has-description">
			<textarea id="embed-image-settings-alt-text" data-setting="alt" aria-describedby="alt-text-description"></textarea>
		</span>

		/** This filter is documented in wp-admin/includes/media.php */
		if ( ! apply_filters( 'disable_captions', '' ) ) :
			?>
			<span class="setting caption">
				<textarea id="embed-image-settings-caption" data-setting="caption"></textarea>
			</span>

		<fieldset class="setting-group">
			<span class="setting align">
				<span class="button-group button-large" data-setting="align">
					<button class="button" value="left">
					</button>
					<button class="button" value="center">
					</button>
					<button class="button" value="right">
					</button>
					<button class="button active" value="none">
					</button>
				</span>
			</span>
		</fieldset>

		<fieldset class="setting-group">
			<span class="setting link-to">
				<span class="button-group button-large" data-setting="link">
					<button class="button" value="file">
					</button>
					<button class="button" value="custom">
					</button>
					<button class="button active" value="none">
					</button>
				</span>
			</span>
			<span class="setting">
				<input type="text" id="embed-image-settings-link-to-custom" class="link-to-custom" data-setting="linkUrl" />
			</span>
		</fieldset>
	</script>

	<script type="text/html" id="tmpl-image-details">
		<div class="media-embed">
			<div class="embed-media-settings">
				<div class="column-settings">
					<span class="setting alt-text has-description">
						<textarea id="image-details-alt-text" data-setting="alt" aria-describedby="alt-text-description">{{ data.model.alt }}</textarea>
					</span>

					/** This filter is documented in wp-admin/includes/media.php */
					if ( ! apply_filters( 'disable_captions', '' ) ) :
						?>
						<span class="setting caption">
							<textarea id="image-details-caption" data-setting="caption">{{ data.model.caption }}</textarea>
						</span>

					<fieldset class="setting-group">
						<span class="setting align">
							<span class="button-group button-large" data-setting="align">
								<button class="button" value="left">
								</button>
								<button class="button" value="center">
								</button>
								<button class="button" value="right">
								</button>
								<button class="button active" value="none">
								</button>
							</span>
						</span>
					</fieldset>

					<# if ( data.attachment ) { #>
						<# if ( 'undefined' !== typeof data.attachment.sizes ) { #>
							<span class="setting size">
								<select id="image-details-size" class="size" name="size"
									data-setting="size"
									<# if ( data.userSettings ) { #>
										data-user-setting="imgsize"
									<# } #>>
									/** This filter is documented in wp-admin/includes/media.php */
									$sizes = apply_filters(
										'image_size_names_choose',
										array(
											'thumbnail' => __( 'Thumbnail' ),
											'medium'    => __( 'Medium' ),
											'large'     => __( 'Large' ),
											'full'      => __( 'Full Size' ),
										)
									);

									foreach ( $sizes as $value => $name ) :
										?>
										<#
										if ( size ) { #>
											</option>
										<# } #>
									</option>
								</select>
							</span>
						<# } #>
							<div class="custom-size wp-clearfix<# if ( data.model.size !== 'custom' ) { #> hidden<# } #>">
								<span class="custom-size-setting">
									<input type="number" id="image-details-size-width" aria-describedby="image-size-desc" data-setting="customWidth" step="1" value="{{ data.model.customWidth }}" />
								</span>
								<span class="sep" aria-hidden="true">&times;</span>
								<span class="custom-size-setting">
									<input type="number" id="image-details-size-height" aria-describedby="image-size-desc" data-setting="customHeight" step="1" value="{{ data.model.customHeight }}" />
								</span>
							</div>
					<# } #>

					<span class="setting link-to">
						<select id="image-details-link-to" data-setting="link">
						<# if ( data.attachment ) { #>
							<option value="file">
							</option>
							<option value="post">
							</option>
						<# } else { #>
							<option value="file">
							</option>
						<# } #>
							<option value="custom">
							</option>
							<option value="none">
							</option>
						</select>
					</span>
					<span class="setting">
						<input type="text" id="image-details-link-to-custom" class="link-to-custom" data-setting="linkUrl" />
					</span>

					<div class="advanced-section">
						<div class="advanced-settings hidden">
							<div class="advanced-image">
								<span class="setting title-text">
									<input type="text" id="image-details-title-attribute" data-setting="title" value="{{ data.model.title }}" />
								</span>
								<span class="setting extra-classes">
									<input type="text" id="image-details-css-class" data-setting="extraClasses" value="{{ data.model.extraClasses }}" />
								</span>
							</div>
							<div class="advanced-link">
								<span class="setting link-target">
									<input type="checkbox" id="image-details-link-target" data-setting="linkTargetBlank" value="_blank" <# if ( data.model.linkTargetBlank ) { #>checked="checked"<# } #>>
								</span>
								<span class="setting link-rel">
									<input type="text" id="image-details-link-rel" data-setting="linkRel" value="{{ data.model.linkRel }}" />
								</span>
								<span class="setting link-class-name">
									<input type="text" id="image-details-link-css-class" data-setting="linkClassName" value="{{ data.model.linkClassName }}" />
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="column-image">
					<div class="image">
						<img src="{{ data.model.url }}" draggable="false" alt="" />
						<# if ( data.attachment && window.imageEdit ) { #>
							<div class="actions">
							</div>
						<# } #>
					</div>
				</div>
			</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-image-editor">
		<div id="media-head-{{ data.id }}"></div>
		<div id="image-editor-{{ data.id }}"></div>
	</script>

	<script type="text/html" id="tmpl-audio-details">
		<# var ext, html5types = {
			mp3: wp.media.view.settings.embedMimes.mp3,
			ogg: wp.media.view.settings.embedMimes.ogg
		}; #>

		<div class="media-embed media-embed-details">
			<div class="embed-media-settings embed-audio-settings">

				<# if ( ! _.isEmpty( data.model.src ) ) {
					ext = data.model.src.split('.').pop();
					if ( html5types[ ext ] ) {
						delete html5types[ ext ];
					}
				#>
				<span class="setting">
					<input type="text" id="audio-details-source" readonly data-setting="src" value="{{ data.model.src }}" />
				</span>
				<# } #>

				foreach ( $audio_types as $type ) :
					?>
					}
				#>
				<span class="setting">
				</span>
				<# } #>

				<# if ( ! _.isEmpty( html5types ) ) { #>
				<fieldset class="setting-group">
					<span class="setting">
						<span class="button-large">
						<# _.each( html5types, function (mime, type) { #>
							<button class="button add-media-source" data-mime="{{ mime }}">{{ type }}</button>
						<# } ) #>
						</span>
					</span>
				</fieldset>
				<# } #>

				<fieldset class="setting-group">
					<span class="setting preload">
						<span class="button-group button-large" data-setting="preload">
						</span>
					</span>
				</fieldset>

				<span class="setting-group">
					<span class="setting checkbox-setting autoplay">
						<input type="checkbox" id="audio-details-autoplay" data-setting="autoplay" />
					</span>

					<span class="setting checkbox-setting">
						<input type="checkbox" id="audio-details-loop" data-setting="loop" />
					</span>
				</span>
			</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-video-details">
		<# var ext, html5types = {
			mp4: wp.media.view.settings.embedMimes.mp4,
			ogv: wp.media.view.settings.embedMimes.ogv,
			webm: wp.media.view.settings.embedMimes.webm
		}; #>

		<div class="media-embed media-embed-details">
			<div class="embed-media-settings embed-video-settings">
				<div class="wp-video-holder">
				<#
				var w = ! data.model.width || data.model.width > 640 ? 640 : data.model.width,
					h = ! data.model.height ? 360 : data.model.height;

				if ( data.model.width && w !== data.model.width ) {
					h = Math.ceil( ( h * w ) / data.model.width );
				}
				#>


				<# if ( ! _.isEmpty( data.model.src ) ) {
					ext = data.model.src.split('.').pop();
					if ( html5types[ ext ] ) {
						delete html5types[ ext ];
					}
				#>
				<span class="setting">
					<input type="text" id="video-details-source" readonly data-setting="src" value="{{ data.model.src }}" />
				</span>
				<# } #>
				foreach ( $video_types as $type ) :
					?>
					}
				#>
				<span class="setting">
				</span>
				<# } #>
				</div>

				<# if ( ! _.isEmpty( html5types ) ) { #>
				<fieldset class="setting-group">
					<span class="setting">
						<span class="button-large">
						<# _.each( html5types, function (mime, type) { #>
							<button class="button add-media-source" data-mime="{{ mime }}">{{ type }}</button>
						<# } ) #>
						</span>
					</span>
				</fieldset>
				<# } #>

				<# if ( ! _.isEmpty( data.model.poster ) ) { #>
				<span class="setting">
					<input type="text" id="video-details-poster-image" readonly data-setting="poster" value="{{ data.model.poster }}" />
				</span>
				<# } #>

				<fieldset class="setting-group">
					<span class="setting preload">
						<span class="button-group button-large" data-setting="preload">
						</span>
					</span>
				</fieldset>

				<span class="setting-group">
					<span class="setting checkbox-setting autoplay">
						<input type="checkbox" id="video-details-autoplay" data-setting="autoplay" />
					</span>

					<span class="setting checkbox-setting">
						<input type="checkbox" id="video-details-loop" data-setting="loop" />
					</span>
				</span>

				<span class="setting" data-setting="content">
					<#
					var content = '';
					if ( ! _.isEmpty( data.model.content ) ) {
						var tracks = jQuery( data.model.content ).filter( 'track' );
						_.each( tracks.toArray(), function( track, index ) {
							content += track.outerHTML; #>
						<input class="content-track" type="text" id="video-details-track-{{ index }}" aria-describedby="video-details-track-desc-{{ index }}" value="{{ track.outerHTML }}" />
						<span class="description" id="video-details-track-desc-{{ index }}">
							printf(
								/* translators: 1: "srclang" HTML attribute, 2: "label" HTML attribute, 3: "kind" HTML attribute. */
								__( 'The %1$s, %2$s, and %3$s values can be edited to set the video track language and kind.' ),
								'srclang',
								'label',
								'kind'
							);
						?>
						</span>
						<# } ); #>
					<# } else { #>
					<# } #>
					<textarea class="hidden content-setting">{{ content }}</textarea>
				</span>
			</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-editor-gallery">
		<# if ( data.attachments.length ) { #>
			<div class="gallery gallery-columns-{{ data.columns }}">
				<# _.each( data.attachments, function( attachment, index ) { #>
					<dl class="gallery-item">
						<dt class="gallery-icon">
							<# if ( attachment.thumbnail ) { #>
								<img src="{{ attachment.thumbnail.url }}" width="{{ attachment.thumbnail.width }}" height="{{ attachment.thumbnail.height }}" alt="{{ attachment.alt }}" />
							<# } else { #>
								<img src="{{ attachment.url }}" alt="{{ attachment.alt }}" />
							<# } #>
						</dt>
						<# if ( attachment.caption ) { #>
							<dd class="wp-caption-text gallery-caption">
								{{{ data.verifyHTML( attachment.caption ) }}}
							</dd>
						<# } #>
					</dl>
					<# if ( index % data.columns === data.columns - 1 ) { #>
						<br style="clear: both;" />
					<# } #>
				<# } ); #>
			</div>
		<# } else { #>
			<div class="wpview-error">
			</div>
		<# } #>
	</script>

	<script type="text/html" id="tmpl-crop-content">
		<div class="upload-errors"></div>
	</script>

	<script type="text/html" id="tmpl-site-icon-preview-crop">
		<style>
			:root{
				--site-icon-url: url( "{{ data.url }}" );
			}
		</style>
		<div class="site-icon-preview crop">
			<div class="image-preview-wrap app-icon-preview">
			</div>
			<div class="site-icon-preview-browser">
				<svg role="img" aria-hidden="true" fill="none" xmlns="http://www.w3.org/2000/svg" class="browser-buttons"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 20a6 6 0 1 1 12 0 6 6 0 0 1-12 0Zm18 0a6 6 0 1 1 12 0 6 6 0 0 1-12 0Zm24-6a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z" /></svg>
				<div class="site-icon-preview-tab">
					<div class="image-preview-wrap browser">
					</div>
						<svg role="img" aria-hidden="true" fill="none" xmlns="http://www.w3.org/2000/svg" class="close-button">
							<path d="M12 13.0607L15.7123 16.773L16.773 15.7123L13.0607 12L16.773 8.28772L15.7123 7.22706L12 10.9394L8.28771 7.22705L7.22705 8.28771L10.9394 12L7.22706 15.7123L8.28772 16.773L12 13.0607Z" />
						</svg>
					</div>
				</div>
			</div>
		</div>
	</script>


	/**
	 * Fires when the custom Backbone media templates are printed.
	 *
	 * @since 3.5.0
	 */
	do_action( 'print_media_templates' );
}

} // namespace CharacterGeneratorDev
