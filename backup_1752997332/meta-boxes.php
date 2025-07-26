<?php

namespace CharacterGeneratorDev {

/**
 * WordPress Administration Meta Boxes API.
 *
 * @package WordPress
 * @subpackage Administration
 */

//
// Post-related Meta Boxes.
//

/**
 * Displays post submit form fields.
 *
 * @since 2.7.0
 *
 * @global string $action
 *
 * @param WP_Post $post Current post object.
 * @param array   $args {
 *     Array of arguments for building the post submit meta box.
 *
 *     @type string   $id       Meta box 'id' attribute.
 *     @type string   $title    Meta box title.
 *     @type callable $callback Meta box display callback.
 *     @type array    $args     Extra meta box arguments.
 * }
 */
function CG_Dev\\post_submit_meta_box( $post, $args = array() ) {
	global $action;

	$post_id          = (int) $post->ID;
	$post_type        = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
	$can_publish      = current_user_can( $post_type_object->cap->publish_posts );
	?>
<div class="submitbox" id="submitpost">

<div id="minor-publishing">

	<div style="display:none;">
	</div>

	<div id="minor-publishing-actions">
		<div id="save-action">
			if ( ! in_array( $post->post_status, array( 'publish', 'future', 'pending' ), true ) ) {
				$private_style = '';
				if ( 'private' === $post->post_status ) {
					$private_style = 'style="display:none"';
				}
				?>
				<span class="spinner"></span>
				<span class="spinner"></span>
		</div>

		if ( is_post_type_viewable( $post_type_object ) ) :
			?>
			<div id="preview-action">
				$preview_link = esc_url( get_preview_post_link( $post ) );
				if ( 'publish' === $post->post_status ) {
					$preview_button_text = __( 'Preview Changes' );
				} else {
					$preview_button_text = __( 'Preview' );
				}

				$preview_button = sprintf(
					'%1$s<span class="screen-reader-text"> %2$s</span>',
					$preview_button_text,
					/* translators: Hidden accessibility text. */
					__( '(opens in a new tab)' )
				);
				?>
				<input type="hidden" name="wp-preview" id="wp-preview" value="" />
			</div>
		endif;

		/**
		 * Fires after the Save Draft (or Save as Pending) and Preview (or Preview Changes) buttons
		 * in the Publish meta box.
		 *
		 * @since 4.4.0
		 *
		 * @param WP_Post $post WP_Post object for the current post.
		 */
		do_action( 'post_submitbox_minor_actions', $post );
		?>
		<div class="clear"></div>
	</div>

	<div id="misc-publishing-actions">
		<div class="misc-pub-section misc-pub-post-status">
			<span id="post-status-display">
				switch ( $post->post_status ) {
					case 'private':
						_e( 'Privately Published' );
						break;
					case 'publish':
						_e( 'Published' );
						break;
					case 'future':
						_e( 'Scheduled' );
						break;
					case 'pending':
						_e( 'Pending Review' );
						break;
					case 'draft':
					case 'auto-draft':
						_e( 'Draft' );
						break;
				}
				?>
			</span>

			if ( 'publish' === $post->post_status || 'private' === $post->post_status || $can_publish ) {
				$private_style = '';
				if ( 'private' === $post->post_status ) {
					$private_style = 'style="display:none"';
				}
				?>
					/* translators: Hidden accessibility text. */
					_e( 'Edit status' );
					?>
				</span></a>

				<div id="post-status-select" class="hide-if-js">
					<label for="post_status" class="screen-reader-text">
						/* translators: Hidden accessibility text. */
						_e( 'Set status' );
						?>
					</label>
					<select name="post_status" id="post_status">
					</select>
				</div>
			}
			?>
		</div>

		<div class="misc-pub-section misc-pub-visibility" id="visibility">
			<span id="post-visibility-display">
				if ( 'private' === $post->post_status ) {
					$post->post_password = '';
					$visibility          = 'private';
					$visibility_trans    = __( 'Private' );
				} elseif ( ! empty( $post->post_password ) ) {
					$visibility       = 'password';
					$visibility_trans = __( 'Password protected' );
				} elseif ( 'post' === $post_type && is_sticky( $post_id ) ) {
					$visibility       = 'public';
					$visibility_trans = __( 'Public, Sticky' );
				} else {
					$visibility       = 'public';
					$visibility_trans = __( 'Public' );
				}

				echo esc_html( $visibility_trans );
				?>
			</span>

					/* translators: Hidden accessibility text. */
					_e( 'Edit visibility' );
					?>
				</span></a>

				<div id="post-visibility-select" class="hide-if-js">





					<p>
					</p>
				</div>
		</div>

		/* translators: Publish box date string. 1: Date, 2: Time. See https://www.php.net/manual/datetime.format.php */
		$date_string = __( '%1$s at %2$s' );
		/* translators: Publish box date format, see https://www.php.net/manual/datetime.format.php */
		$date_format = _x( 'M j, Y', 'publish box date format' );
		/* translators: Publish box time format, see https://www.php.net/manual/datetime.format.php */
		$time_format = _x( 'H:i', 'publish box time format' );

		if ( 0 !== $post_id ) {
			if ( 'future' === $post->post_status ) { // Scheduled for publishing at a future date.
				/* translators: Post date information. %s: Date on which the post is currently scheduled to be published. */
				$stamp = __( 'Scheduled for: %s' );
			} elseif ( 'publish' === $post->post_status || 'private' === $post->post_status ) { // Already published.
				/* translators: Post date information. %s: Date on which the post was published. */
				$stamp = __( 'Published on: %s' );
			} elseif ( '0000-00-00 00:00:00' === $post->post_date_gmt ) { // Draft, 1 or more saves, no date specified.
				$stamp = __( 'Publish <b>immediately</b>' );
			} elseif ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // Draft, 1 or more saves, future date specified.
				/* translators: Post date information. %s: Date on which the post is to be published. */
				$stamp = __( 'Schedule for: %s' );
			} else { // Draft, 1 or more saves, date specified.
				/* translators: Post date information. %s: Date on which the post is to be published. */
				$stamp = __( 'Publish on: %s' );
			}
			$date = sprintf(
				$date_string,
				date_i18n( $date_format, strtotime( $post->post_date ) ),
				date_i18n( $time_format, strtotime( $post->post_date ) )
			);
		} else { // Draft (no saves, and thus no date specified).
			$stamp = __( 'Publish <b>immediately</b>' );
			$date  = sprintf(
				$date_string,
				date_i18n( $date_format, strtotime( current_time( 'mysql' ) ) ),
				date_i18n( $time_format, strtotime( current_time( 'mysql' ) ) )
			);
		}

		if ( ! empty( $args['args']['revisions_count'] ) ) :
			?>
			<div class="misc-pub-section misc-pub-revisions">
				/* translators: Post revisions heading. %s: The number of available revisions. */
				printf( __( 'Revisions: %s' ), '<b>' . number_format_i18n( $args['args']['revisions_count'] ) . '</b>' );
				?>
					/* translators: Hidden accessibility text. */
					_e( 'Browse revisions' );
					?>
				</span></a>
			</div>
		endif;

		if ( $can_publish ) : // Contributors don't get to choose the date of publish.
			?>
			<div class="misc-pub-section curtime misc-pub-curtime">
				<span id="timestamp">
				</span>
				<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" role="button">
					<span class="screen-reader-text">
						/* translators: Hidden accessibility text. */
						_e( 'Edit date and time' );
						?>
					</span>
				</a>
				<fieldset id="timestampdiv" class="hide-if-js">
					<legend class="screen-reader-text">
						/* translators: Hidden accessibility text. */
						_e( 'Date and time' );
						?>
					</legend>
				</fieldset>
			</div>
		endif;

		if ( 'draft' === $post->post_status && get_post_meta( $post_id, '_customize_changeset_uuid', true ) ) :
			$message = sprintf(
				/* translators: %s: URL to the Customizer. */
				__( 'This draft comes from your <a href="%s">unpublished customization changes</a>. You can edit, but there is no need to publish now. It will be published automatically with those changes.' ),
				esc_url(
					add_query_arg(
						'changeset_uuid',
						rawurlencode( get_post_meta( $post_id, '_customize_changeset_uuid', true ) ),
						admin_url( 'customize.php' )
					)
				)
			);
			wp_admin_notice(
				$message,
				array(
					'type'               => 'info',
					'additional_classes' => array( 'notice-alt', 'inline' ),
				)
			);
		endif;

		/**
		 * Fires after the post time/date setting in the Publish meta box.
		 *
		 * @since 2.9.0
		 * @since 4.4.0 Added the `$post` parameter.
		 *
		 * @param WP_Post $post WP_Post object for the current post.
		 */
		do_action( 'post_submitbox_misc_actions', $post );
		?>
	</div>
	<div class="clear"></div>
</div>

<div id="major-publishing-actions">
	/**
	 * Fires at the beginning of the publishing actions section of the Publish meta box.
	 *
	 * @since 2.7.0
	 * @since 4.9.0 Added the `$post` parameter.
	 *
	 * @param WP_Post|null $post WP_Post object for the current post on Edit Post screen,
	 *                           null on Edit Link screen.
	 */
	do_action( 'post_submitbox_start', $post );
	?>
	<div id="delete-action">
		if ( current_user_can( 'delete_post', $post_id ) ) {
			if ( ! EMPTY_TRASH_DAYS ) {
				$delete_text = __( 'Delete permanently' );
			} else {
				$delete_text = __( 'Move to Trash' );
			}
			?>
		}
		?>
	</div>

	<div id="publishing-action">
		<span class="spinner"></span>
		if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ), true ) || 0 === $post_id ) {
			if ( $can_publish ) :
				if ( ! empty( $post->post_date_gmt ) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) :
					?>
				else :
					?>
				endif;
			else :
				?>
			endif;
		} else {
			?>
		}
		?>
	</div>
	<div class="clear"></div>
</div>

</div>
}

/**
 * Displays attachment submit form fields.
 *
 * @since 3.5.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\attachment_submit_meta_box( $post ) {
	?>
<div class="submitbox" id="submitpost">

<div id="minor-publishing">

<div style="display:none;">
</div>


<div id="misc-publishing-actions">
	<div class="misc-pub-section curtime misc-pub-curtime">
		<span id="timestamp">
			$uploaded_on = sprintf(
				/* translators: Publish box date string. 1: Date, 2: Time. */
				__( '%1$s at %2$s' ),
				/* translators: Publish box date format, see https://www.php.net/manual/datetime.format.php */
				date_i18n( _x( 'M j, Y', 'publish box date format' ), strtotime( $post->post_date ) ),
				/* translators: Publish box time format, see https://www.php.net/manual/datetime.format.php */
				date_i18n( _x( 'H:i', 'publish box time format' ), strtotime( $post->post_date ) )
			);
			/* translators: Attachment information. %s: Date the attachment was uploaded. */
			printf( __( 'Uploaded on: %s' ), '<b>' . $uploaded_on . '</b>' );
			?>
		</span>
	</div><!-- .misc-pub-section -->

	/**
	 * Fires after the 'Uploaded on' section of the Save meta box
	 * in the attachment editing screen.
	 *
	 * @since 3.5.0
	 * @since 4.9.0 Added the `$post` parameter.
	 *
	 * @param WP_Post $post WP_Post object for the current attachment.
	 */
	do_action( 'attachment_submitbox_misc_actions', $post );
	?>
</div><!-- #misc-publishing-actions -->
<div class="clear"></div>
</div><!-- #minor-publishing -->

<div id="major-publishing-actions">
	<div id="delete-action">
	if ( current_user_can( 'delete_post', $post->ID ) ) {
		if ( EMPTY_TRASH_DAYS && MEDIA_TRASH ) {
			printf(
				'<a class="submitdelete deletion" href="%1$s">%2$s</a>',
				get_delete_post_link( $post->ID ),
				__( 'Move to Trash' )
			);
		} else {
			$show_confirmation = ! MEDIA_TRASH ? " onclick='return showNotice.warn();'" : '';

			printf(
				'<a class="submitdelete deletion"%1$s href="%2$s">%3$s</a>',
				$show_confirmation,
				get_delete_post_link( $post->ID, '', true ),
				__( 'Delete permanently' )
			);
		}
	}
	?>
	</div>

	<div id="publishing-action">
		<span class="spinner"></span>
	</div>
	<div class="clear"></div>
</div><!-- #major-publishing-actions -->

</div>

}

/**
 * Displays post format form elements.
 *
 * @since 3.1.0
 *
 * @param WP_Post $post Current post object.
 * @param array   $box {
 *     Post formats meta box arguments.
 *
 *     @type string   $id       Meta box 'id' attribute.
 *     @type string   $title    Meta box title.
 *     @type callable $callback Meta box display callback.
 *     @type array    $args     Extra meta box arguments.
 * }
 */
function CG_Dev\\post_format_meta_box( $post, $box ) {
	if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post->post_type, 'post-formats' ) ) :
		$post_formats = get_theme_support( 'post-formats' );

		if ( is_array( $post_formats[0] ) ) :
			$post_format = get_post_format( $post->ID );
			if ( ! $post_format ) {
				$post_format = '0';
			}
			// Add in the current one if it isn't there yet, in case the active theme doesn't support it.
			if ( $post_format && ! in_array( $post_format, $post_formats[0], true ) ) {
				$post_formats[0][] = $post_format;
			}
			?>
		<div id="post-formats-select">
		<fieldset>
			<legend class="screen-reader-text">
				/* translators: Hidden accessibility text. */
				_e( 'Post Formats' );
				?>
			</legend>
		</fieldset>
	</div>
	endif;
endif;
}

/**
 * Displays post tags form fields.
 *
 * @since 2.6.0
 *
 * @todo Create taxonomy-agnostic wrapper for this.
 *
 * @param WP_Post $post Current post object.
 * @param array   $box {
 *     Tags meta box arguments.
 *
 *     @type string   $id       Meta box 'id' attribute.
 *     @type string   $title    Meta box title.
 *     @type callable $callback Meta box display callback.
 *     @type array    $args {
 *         Extra meta box arguments.
 *
 *         @type string $taxonomy Taxonomy. Default 'post_tag'.
 *     }
 * }
 */
function CG_Dev\\post_tags_meta_box( $post, $box ) {
	$defaults = array( 'taxonomy' => 'post_tag' );
	if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
		$args = array();
	} else {
		$args = $box['args'];
	}
	$parsed_args           = wp_parse_args( $args, $defaults );
	$tax_name              = esc_attr( $parsed_args['taxonomy'] );
	$taxonomy              = get_taxonomy( $parsed_args['taxonomy'] );
	$user_can_assign_terms = current_user_can( $taxonomy->cap->assign_terms );
	$comma                 = _x( ',', 'tag delimiter' );
	$terms_to_edit         = get_terms_to_edit( $post->ID, $tax_name );
	if ( ! is_string( $terms_to_edit ) ) {
		$terms_to_edit = '';
	}
	?>
	<div class="jaxtag">
	<div class="nojs-tags hide-if-js">
	</div>
	<div class="ajaxtag hide-if-no-js">
	</div>
	</div>
	<ul class="tagchecklist" role="list"></ul>
</div>
}

/**
 * Displays post categories form fields.
 *
 * @since 2.6.0
 *
 * @todo Create taxonomy-agnostic wrapper for this.
 *
 * @param WP_Post $post Current post object.
 * @param array   $box {
 *     Categories meta box arguments.
 *
 *     @type string   $id       Meta box 'id' attribute.
 *     @type string   $title    Meta box title.
 *     @type callable $callback Meta box display callback.
 *     @type array    $args {
 *         Extra meta box arguments.
 *
 *         @type string $taxonomy Taxonomy. Default 'category'.
 *     }
 * }
 */
function CG_Dev\\post_categories_meta_box( $post, $box ) {
	$defaults = array( 'taxonomy' => 'category' );
	if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
		$args = array();
	} else {
		$args = $box['args'];
	}
	$parsed_args = wp_parse_args( $args, $defaults );
	$tax_name    = esc_attr( $parsed_args['taxonomy'] );
	$taxonomy    = get_taxonomy( $parsed_args['taxonomy'] );
	?>
		</ul>

			</ul>
		</div>

			$name = ( 'category' === $tax_name ) ? 'post_category' : 'tax_input[' . $tax_name . ']';
			// Allows for an empty term set to be sent. 0 is an invalid term ID and will be ignored by empty() checks.
			echo "<input type='hidden' name='{$name}[]' value='0' />";
			?>
				wp_terms_checklist(
					$post->ID,
					array(
						'taxonomy'     => $tax_name,
						'popular_cats' => $popular_ids,
					)
				);
				?>
			</ul>
		</div>
						/* translators: %s: Add New taxonomy label. */
						printf( __( '+ %s' ), $taxonomy->labels->add_new_item );
					?>
				</a>
					</label>
					$parent_dropdown_args = array(
						'taxonomy'         => $tax_name,
						'hide_empty'       => 0,
						'name'             => 'new' . $tax_name . '_parent',
						'orderby'          => 'name',
						'hierarchical'     => 1,
						'show_option_none' => '&mdash; ' . $taxonomy->labels->parent_item . ' &mdash;',
					);

					/**
					 * Filters the arguments for the taxonomy parent dropdown on the Post Edit page.
					 *
					 * @since 4.4.0
					 *
					 * @param array $parent_dropdown_args {
					 *     Optional. Array of arguments to generate parent dropdown.
					 *
					 *     @type string   $taxonomy         Name of the taxonomy to retrieve.
					 *     @type bool     $hide_if_empty    True to skip generating markup if no
					 *                                      categories are found. Default 0.
					 *     @type string   $name             Value for the 'name' attribute
					 *                                      of the select element.
					 *                                      Default "new{$tax_name}_parent".
					 *     @type string   $orderby          Which column to use for ordering
					 *                                      terms. Default 'name'.
					 *     @type bool|int $hierarchical     Whether to traverse the taxonomy
					 *                                      hierarchy. Default 1.
					 *     @type string   $show_option_none Text to display for the "none" option.
					 *                                      Default "&mdash; {$parent} &mdash;",
					 *                                      where `$parent` is 'parent_item'
					 *                                      taxonomy label.
					 * }
					 */
					$parent_dropdown_args = apply_filters( 'post_edit_category_parent_dropdown_args', $parent_dropdown_args );

					wp_dropdown_categories( $parent_dropdown_args );
					?>
				</p>
			</div>
	</div>
}

/**
 * Displays post excerpt form fields.
 *
 * @since 2.6.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\post_excerpt_meta_box( $post ) {
	?>
<label class="screen-reader-text" for="excerpt">
	/* translators: Hidden accessibility text. */
	_e( 'Excerpt' );
	?>
<p>
	printf(
		/* translators: %s: Documentation URL. */
		__( 'Excerpts are optional hand-crafted summaries of your content that can be used in your theme. <a href="%s">Learn more about manual excerpts</a>.' ),
		__( 'https://wordpress.org/documentation/article/what-is-an-excerpt-classic-editor/' )
	);
	?>
</p>
}

/**
 * Displays trackback links form fields.
 *
 * @since 2.6.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\post_trackback_meta_box( $post ) {
	$form_trackback = '<input type="text" name="trackback_url" id="trackback_url" class="code" value="' .
		esc_attr( str_replace( "\n", ' ', $post->to_ping ) ) . '" aria-describedby="trackback-url-desc" />';

	if ( '' !== $post->pinged ) {
		$pings          = '<p>' . __( 'Already pinged:' ) . '</p><ul>';
		$already_pinged = explode( "\n", trim( $post->pinged ) );
		foreach ( $already_pinged as $pinged_url ) {
			$pings .= "\n\t<li>" . esc_html( $pinged_url ) . '</li>';
		}
		$pings .= '</ul>';
	}

	?>
<p>
</p>
<p>
	printf(
		/* translators: %s: Documentation URL. */
		__( 'Trackbacks are a way to notify legacy blog systems that you&#8217;ve linked to them. If you link other WordPress sites, they&#8217;ll be notified automatically using <a href="%s">pingbacks</a>, no other action necessary.' ),
		__( 'https://wordpress.org/documentation/article/introduction-to-blogging/#comments' )
	);
	?>
</p>
	if ( ! empty( $pings ) ) {
		echo $pings;
	}
}

/**
 * Displays custom fields form fields.
 *
 * @since 2.6.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\post_custom_meta_box( $post ) {
	?>
<div id="postcustomstuff">
<div id="ajax-response"></div>
	$metadata = has_meta( $post->ID );
	foreach ( $metadata as $key => $value ) {
		if ( is_protected_meta( $metadata[ $key ]['meta_key'], 'post' ) || ! current_user_can( 'edit_post_meta', $post->ID, $metadata[ $key ]['meta_key'] ) ) {
			unset( $metadata[ $key ] );
		}
	}
	list_meta( $metadata );
	meta_form( $post );
	?>
</div>
<p>
	printf(
		/* translators: %s: Documentation URL. */
		__( 'Custom fields can be used to add extra metadata to a post that you can <a href="%s">use in your theme</a>.' ),
		__( 'https://wordpress.org/documentation/article/assign-custom-fields/' )
	);
	?>
</p>
}

/**
 * Displays comments status form fields.
 *
 * @since 2.6.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\post_comment_status_meta_box( $post ) {
	?>
<input name="advanced_view" type="hidden" value="1" />
<p class="meta-options">
		printf(
			/* translators: %s: Documentation URL. */
			__( 'Allow <a href="%s">trackbacks and pingbacks</a>' ),
			__( 'https://wordpress.org/documentation/article/introduction-to-blogging/#managing-comments' )
		);
		?>
	</label>
	/**
	 * Fires at the end of the Discussion meta box on the post editing screen.
	 *
	 * @since 3.1.0
	 *
	 * @param WP_Post $post WP_Post object for the current post.
	 */
	do_action( 'post_comment_status_meta_box-options', $post ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	?>
</p>
}

/**
 * Displays comments for post table header
 *
 * @since 3.0.0
 *
 * @param array $result Table header rows.
 * @return array
 */
function CG_Dev\\post_comment_meta_box_thead( $result ) {
	unset( $result['cb'], $result['response'] );
	return $result;
}

/**
 * Displays comments for post.
 *
 * @since 2.8.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\post_comment_meta_box( $post ) {
	wp_nonce_field( 'get-comments', 'add_comment_nonce', false );
	?>

	$total         = get_comments(
		array(
			'post_id' => $post->ID,
			'count'   => true,
			'orderby' => 'none',
		)
	);
	$wp_list_table = _get_list_table( 'WP_Post_Comments_List_Table' );
	$wp_list_table->display( true );

	if ( 1 > $total ) {
		echo '<p id="no-comments">' . __( 'No comments yet.' ) . '</p>';
	} else {
		$hidden = get_hidden_meta_boxes( get_current_screen() );
		if ( ! in_array( 'commentsdiv', $hidden, true ) ) {
			?>
		}

		?>
	}

	wp_comment_trashnotice();
}

/**
 * Displays slug form fields.
 *
 * @since 2.6.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\post_slug_meta_box( $post ) {
	/** This filter is documented in wp-admin/edit-tag-form.php */
	$editable_slug = apply_filters( 'editable_slug', $post->post_name, $post );
	?>
<label class="screen-reader-text" for="post_name">
	/* translators: Hidden accessibility text. */
	_e( 'Slug' );
	?>
}

/**
 * Displays form field with list of authors.
 *
 * @since 2.6.0
 *
 * @global int $user_ID
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\post_author_meta_box( $post ) {
	global $user_ID;

	$post_type_object = get_post_type_object( $post->post_type );
	?>
<label class="screen-reader-text" for="post_author_override">
	/* translators: Hidden accessibility text. */
	_e( 'Author' );
	?>
</label>
	wp_dropdown_users(
		array(
			'capability'       => array( $post_type_object->cap->edit_posts ),
			'name'             => 'post_author_override',
			'selected'         => empty( $post->ID ) ? $user_ID : $post->post_author,
			'include_selected' => true,
			'show'             => 'display_name_with_login',
		)
	);
}

/**
 * Displays list of revisions.
 *
 * @since 2.6.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\post_revisions_meta_box( $post ) {
	wp_list_post_revisions( $post );
}

//
// Page-related Meta Boxes.
//

/**
 * Displays page attributes form fields.
 *
 * @since 2.7.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\page_attributes_meta_box( $post ) {
	if ( is_post_type_hierarchical( $post->post_type ) ) :
		$dropdown_args = array(
			'post_type'        => $post->post_type,
			'exclude_tree'     => $post->ID,
			'selected'         => $post->post_parent,
			'name'             => 'parent_id',
			'show_option_none' => __( '(no parent)' ),
			'sort_column'      => 'menu_order, post_title',
			'echo'             => 0,
		);

		/**
		 * Filters the arguments used to generate a Pages drop-down element.
		 *
		 * @since 3.3.0
		 *
		 * @see wp_dropdown_pages()
		 *
		 * @param array   $dropdown_args Array of arguments used to generate the pages drop-down.
		 * @param WP_Post $post          The current post.
		 */
		$dropdown_args = apply_filters( 'page_attributes_dropdown_pages_args', $dropdown_args, $post );
		$pages         = wp_dropdown_pages( $dropdown_args );
		if ( ! empty( $pages ) ) :
			?>
		endif; // End empty pages check.
	endif;  // End hierarchical check.

	if ( count( get_page_templates( $post ) ) > 0 && (int) get_option( 'page_for_posts' ) !== $post->ID ) :
		$template = ! empty( $post->page_template ) ? $post->page_template : false;
		?>
		/**
		 * Fires immediately after the label inside the 'Template' section
		 * of the 'Page Attributes' meta box.
		 *
		 * @since 4.4.0
		 *
		 * @param string|false $template The template used for the current post.
		 * @param WP_Post      $post     The current post.
		 */
		do_action( 'page_attributes_meta_box_template', $template, $post );
		?>
</p>
<select name="page_template" id="page_template">
		/**
		 * Filters the title of the default page template displayed in the drop-down.
		 *
		 * @since 4.1.0
		 *
		 * @param string $label   The display value for the default page template title.
		 * @param string $context Where the option label is displayed. Possible values
		 *                        include 'meta-box' or 'quick-edit'.
		 */
		$default_title = apply_filters( 'default_page_template_title', __( 'Default template' ), 'meta-box' );
		?>
</select>
		/**
		 * Fires before the help hint text in the 'Page Attributes' meta box.
		 *
		 * @since 4.9.0
		 *
		 * @param WP_Post $post The current post.
		 */
		do_action( 'page_attributes_misc_attributes', $post );
		?>
	endif;
	endif;
}

//
// Link-related Meta Boxes.
//

/**
 * Displays link create form fields.
 *
 * @since 2.7.0
 *
 * @param object $link Current link object.
 */
function CG_Dev\\link_submit_meta_box( $link ) {
	?>
<div class="submitbox" id="submitlink">

<div id="minor-publishing">

<div style="display:none;">
</div>

<div id="minor-publishing-actions">
<div id="preview-action">
</div>
<div class="clear"></div>
</div>

<div id="misc-publishing-actions">
<div class="misc-pub-section misc-pub-private">
</div>
</div>

</div>

<div id="major-publishing-actions">
	/** This action is documented in wp-admin/includes/meta-boxes.php */
	do_action( 'post_submitbox_start', null );
	?>
<div id="delete-action">
	if ( ! empty( $_GET['action'] ) && 'edit' === $_GET['action'] && current_user_can( 'manage_links' ) ) {
		printf(
			'<a class="submitdelete deletion" href="%s" onclick="return confirm( \'%s\' );">%s</a>',
			wp_nonce_url( "link.php?action=delete&amp;link_id=$link->link_id", 'delete-bookmark_' . $link->link_id ),
			/* translators: %s: Link name. */
			esc_js( sprintf( __( "You are about to delete this link '%s'\n  'Cancel' to stop, 'OK' to delete." ), $link->link_name ) ),
			__( 'Delete' )
		);
	}
	?>
</div>

<div id="publishing-action">
</div>
<div class="clear"></div>
</div>
	/**
	 * Fires at the end of the Publish box in the Link editing screen.
	 *
	 * @since 2.5.0
	 */
	do_action( 'submitlink_box' );
	?>
<div class="clear"></div>
</div>
}

/**
 * Displays link categories form fields.
 *
 * @since 2.6.0
 *
 * @param object $link Current link object.
 */
function CG_Dev\\link_categories_meta_box( $link ) {
	?>
<div id="taxonomy-linkcategory" class="categorydiv">
	<ul id="category-tabs" class="category-tabs">
	</ul>

	<div id="categories-all" class="tabs-panel">
		<ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">
			if ( isset( $link->link_id ) ) {
				wp_link_category_checklist( $link->link_id );
			} else {
				wp_link_category_checklist();
			}
			?>
		</ul>
	</div>

	<div id="categories-pop" class="tabs-panel" style="display: none;">
		<ul id="categorychecklist-pop" class="categorychecklist form-no-clear">
		</ul>
	</div>

	<div id="category-adder" class="wp-hidden-children">
		<p id="link-category-add" class="wp-hidden-child">
			<label class="screen-reader-text" for="newcat">
				/* translators: Hidden accessibility text. */
				_e( '+ Add Category' );
				?>
			</label>
			<span id="category-ajax-response"></span>
		</p>
	</div>
</div>
}

/**
 * Displays form fields for changing link target.
 *
 * @since 2.6.0
 *
 * @param object $link Current link object.
 */
function CG_Dev\\link_target_meta_box( $link ) {

	?>
<fieldset><legend class="screen-reader-text"><span>
	/* translators: Hidden accessibility text. */
	_e( 'Target' );
	?>
</span></legend>
<p><label for="link_target_blank" class="selectit">
<p><label for="link_target_top" class="selectit">
<p><label for="link_target_none" class="selectit">
</fieldset>
}

/**
 * Displays 'checked' checkboxes attribute for XFN microformat options.
 *
 * @since 1.0.1
 *
 * @global object $link Current link object.
 *
 * @param string $xfn_relationship XFN relationship category. Possible values are:
 *                                 'friendship', 'physical', 'professional',
 *                                 'geographical', 'family', 'romantic', 'identity'.
 * @param string $xfn_value        Optional. The XFN value to mark as checked
 *                                 if it matches the current link's relationship.
 *                                 Default empty string.
 * @param mixed  $deprecated       Deprecated. Not used.
 */
function CG_Dev\\xfn_check( $xfn_relationship, $xfn_value = '', $deprecated = '' ) {
	global $link;

	if ( ! empty( $deprecated ) ) {
		_deprecated_argument( __FUNCTION__, '2.5.0' ); // Never implemented.
	}

	$link_rel  = isset( $link->link_rel ) ? $link->link_rel : '';
	$link_rels = preg_split( '/\s+/', $link_rel );

	// Mark the specified value as checked if it matches the current link's relationship.
	if ( '' !== $xfn_value && in_array( $xfn_value, $link_rels, true ) ) {
		echo ' checked="checked"';
	}

	if ( '' === $xfn_value ) {
		// Mark the 'none' value as checked if the current link does not match the specified relationship.
		if ( 'family' === $xfn_relationship
			&& ! array_intersect( $link_rels, array( 'child', 'parent', 'sibling', 'spouse', 'kin' ) )
		) {
			echo ' checked="checked"';
		}

		if ( 'friendship' === $xfn_relationship
			&& ! array_intersect( $link_rels, array( 'friend', 'acquaintance', 'contact' ) )
		) {
			echo ' checked="checked"';
		}

		if ( 'geographical' === $xfn_relationship
			&& ! array_intersect( $link_rels, array( 'co-resident', 'neighbor' ) )
		) {
			echo ' checked="checked"';
		}

		// Mark the 'me' value as checked if it matches the current link's relationship.
		if ( 'identity' === $xfn_relationship
			&& in_array( 'me', $link_rels, true )
		) {
			echo ' checked="checked"';
		}
	}
}

/**
 * Displays XFN form fields.
 *
 * @since 2.6.0
 *
 * @param object $link Current link object.
 */
function CG_Dev\\link_xfn_meta_box( $link ) {
	?>
<table class="links-table">
	<tr>
	</tr>
	<tr>
		<td><fieldset>
			<legend class="screen-reader-text"><span>
				/* translators: Hidden accessibility text. xfn: https://gmpg.org/xfn/ */
				_e( 'identity' );
				?>
			</span></legend>
			<label for="me">
		</fieldset></td>
	</tr>
	<tr>
		<td><fieldset>
			<legend class="screen-reader-text"><span>
				/* translators: Hidden accessibility text. xfn: https://gmpg.org/xfn/ */
				_e( 'friendship' );
				?>
			</span></legend>
			<label for="contact">
			</label>
			<label for="acquaintance">
			</label>
			<label for="friend">
			</label>
			<label for="friendship">
			</label>
		</fieldset></td>
	</tr>
	<tr>
		<td><fieldset>
			<legend class="screen-reader-text"><span>
				/* translators: Hidden accessibility text. xfn: https://gmpg.org/xfn/ */
				_e( 'physical' );
				?>
			</span></legend>
			<label for="met">
			</label>
		</fieldset></td>
	</tr>
	<tr>
		<td><fieldset>
			<legend class="screen-reader-text"><span>
				/* translators: Hidden accessibility text. xfn: https://gmpg.org/xfn/ */
				_e( 'professional' );
				?>
			</span></legend>
			<label for="co-worker">
			</label>
			<label for="colleague">
			</label>
		</fieldset></td>
	</tr>
	<tr>
		<td><fieldset>
			<legend class="screen-reader-text"><span>
				/* translators: Hidden accessibility text. xfn: https://gmpg.org/xfn/ */
				_e( 'geographical' );
				?>
			</span></legend>
			<label for="co-resident">
			</label>
			<label for="neighbor">
			</label>
			<label for="geographical">
			</label>
		</fieldset></td>
	</tr>
	<tr>
		<td><fieldset>
			<legend class="screen-reader-text"><span>
				/* translators: Hidden accessibility text. xfn: https://gmpg.org/xfn/ */
				_e( 'family' );
				?>
			</span></legend>
			<label for="child">
			</label>
			<label for="kin">
			</label>
			<label for="parent">
			</label>
			<label for="sibling">
			</label>
			<label for="spouse">
			</label>
			<label for="family">
			</label>
		</fieldset></td>
	</tr>
	<tr>
		<td><fieldset>
			<legend class="screen-reader-text"><span>
				/* translators: Hidden accessibility text. xfn: https://gmpg.org/xfn/ */
				_e( 'romantic' );
				?>
			</span></legend>
			<label for="muse">
			</label>
			<label for="crush">
			</label>
			<label for="date">
			</label>
			<label for="romantic">
			</label>
		</fieldset></td>
	</tr>

</table>
}

/**
 * Displays advanced link options form fields.
 *
 * @since 2.6.0
 *
 * @param object $link Current link object.
 */
function CG_Dev\\link_advanced_meta_box( $link ) {
	?>
<table class="links-table" cellpadding="0">
	<tr>
	</tr>
	<tr>
	</tr>
	<tr>
	</tr>
	<tr>
		<td><select name="link_rating" id="link_rating" size="1">
		for ( $rating = 0; $rating <= 10; $rating++ ) {
			echo '<option value="' . $rating . '"';
			if ( isset( $link->link_rating ) && $link->link_rating === $rating ) {
				echo ' selected="selected"';
			}
			echo '>' . $rating . '</option>';
		}
		?>
		</td>
	</tr>
</table>
}

/**
 * Displays post thumbnail meta box.
 *
 * @since 2.9.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\post_thumbnail_meta_box( $post ) {
	$thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true );
	echo _wp_post_thumbnail_html( $thumbnail_id, $post->ID );
}

/**
 * Displays fields for ID3 data.
 *
 * @since 3.9.0
 *
 * @param WP_Post $post Current post object.
 */
function CG_Dev\\attachment_id3_data_meta_box( $post ) {
	$meta = array();
	if ( ! empty( $post->ID ) ) {
		$meta = wp_get_attachment_metadata( $post->ID );
	}

	foreach ( wp_get_attachment_id3_keys( $post, 'edit' ) as $key => $label ) :
		$value = '';
		if ( ! empty( $meta[ $key ] ) ) {
			$value = $meta[ $key ];
		}
		?>
	<p>
	</p>
	endforeach;
}

/**
 * Registers the default post meta boxes, and runs the `do_meta_boxes` actions.
 *
 * @since 5.0.0
 *
 * @param WP_Post $post The post object that these meta boxes are being generated for.
 */
function CG_Dev\\register_and_do_post_meta_boxes( $post ) {
	$post_type        = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );

	$thumbnail_support = current_theme_supports( 'post-thumbnails', $post_type ) && post_type_supports( $post_type, 'thumbnail' );
	if ( ! $thumbnail_support && 'attachment' === $post_type && $post->post_mime_type ) {
		if ( wp_attachment_is( 'audio', $post ) ) {
			$thumbnail_support = post_type_supports( 'attachment:audio', 'thumbnail' ) || current_theme_supports( 'post-thumbnails', 'attachment:audio' );
		} elseif ( wp_attachment_is( 'video', $post ) ) {
			$thumbnail_support = post_type_supports( 'attachment:video', 'thumbnail' ) || current_theme_supports( 'post-thumbnails', 'attachment:video' );
		}
	}

	$publish_callback_args = array( '__back_compat_meta_box' => true );

	if ( post_type_supports( $post_type, 'revisions' ) && 'auto-draft' !== $post->post_status ) {
		$revisions = wp_get_latest_revision_id_and_total_count( $post->ID );

		// We should aim to show the revisions meta box only when there are revisions.
		if ( ! is_wp_error( $revisions ) && $revisions['count'] > 1 ) {
			$publish_callback_args = array(
				'revisions_count'        => $revisions['count'],
				'revision_id'            => $revisions['latest_id'],
				'__back_compat_meta_box' => true,
			);

			add_meta_box( 'revisionsdiv', __( 'Revisions' ), 'post_revisions_meta_box', null, 'normal', 'core', array( '__back_compat_meta_box' => true ) );
		}
	}

	if ( 'attachment' === $post_type ) {
		wp_enqueue_script( 'image-edit' );
		wp_enqueue_style( 'imgareaselect' );
		add_meta_box( 'submitdiv', __( 'Save' ), 'attachment_submit_meta_box', null, 'side', 'core', array( '__back_compat_meta_box' => true ) );
		add_action( 'edit_form_after_title', 'edit_form_image_editor' );

		if ( wp_attachment_is( 'audio', $post ) ) {
			add_meta_box( 'attachment-id3', __( 'Metadata' ), 'attachment_id3_data_meta_box', null, 'normal', 'core', array( '__back_compat_meta_box' => true ) );
		}
	} else {
		add_meta_box( 'submitdiv', __( 'Publish' ), 'post_submit_meta_box', null, 'side', 'core', $publish_callback_args );
	}

	if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post_type, 'post-formats' ) ) {
		add_meta_box( 'formatdiv', _x( 'Format', 'post format' ), 'post_format_meta_box', null, 'side', 'core', array( '__back_compat_meta_box' => true ) );
	}

	// All taxonomies.
	foreach ( get_object_taxonomies( $post ) as $tax_name ) {
		$taxonomy = get_taxonomy( $tax_name );
		if ( ! $taxonomy->show_ui || false === $taxonomy->meta_box_cb ) {
			continue;
		}

		$label = $taxonomy->labels->name;

		if ( ! is_taxonomy_hierarchical( $tax_name ) ) {
			$tax_meta_box_id = 'tagsdiv-' . $tax_name;
		} else {
			$tax_meta_box_id = $tax_name . 'div';
		}

		add_meta_box(
			$tax_meta_box_id,
			$label,
			$taxonomy->meta_box_cb,
			null,
			'side',
			'core',
			array(
				'taxonomy'               => $tax_name,
				'__back_compat_meta_box' => true,
			)
		);
	}

	if ( post_type_supports( $post_type, 'page-attributes' ) || count( get_page_templates( $post ) ) > 0 ) {
		add_meta_box( 'pageparentdiv', $post_type_object->labels->attributes, 'page_attributes_meta_box', null, 'side', 'core', array( '__back_compat_meta_box' => true ) );
	}

	if ( $thumbnail_support && current_user_can( 'upload_files' ) ) {
		add_meta_box( 'postimagediv', esc_html( $post_type_object->labels->featured_image ), 'post_thumbnail_meta_box', null, 'side', 'low', array( '__back_compat_meta_box' => true ) );
	}

	if ( post_type_supports( $post_type, 'excerpt' ) ) {
		add_meta_box( 'postexcerpt', __( 'Excerpt' ), 'post_excerpt_meta_box', null, 'normal', 'core', array( '__back_compat_meta_box' => true ) );
	}

	if ( post_type_supports( $post_type, 'trackbacks' ) ) {
		add_meta_box( 'trackbacksdiv', __( 'Send Trackbacks' ), 'post_trackback_meta_box', null, 'normal', 'core', array( '__back_compat_meta_box' => true ) );
	}

	if ( post_type_supports( $post_type, 'custom-fields' ) ) {
		add_meta_box(
			'postcustom',
			__( 'Custom Fields' ),
			'post_custom_meta_box',
			null,
			'normal',
			'core',
			array(
				'__back_compat_meta_box'             => ! (bool) get_user_meta( get_current_user_id(), 'enable_custom_fields', true ),
				'__block_editor_compatible_meta_box' => true,
			)
		);
	}

	/**
	 * Fires in the middle of built-in meta box registration.
	 *
	 * @since 2.1.0
	 * @deprecated 3.7.0 Use {@see 'add_meta_boxes'} instead.
	 *
	 * @param WP_Post $post Post object.
	 */
	do_action_deprecated( 'dbx_post_advanced', array( $post ), '3.7.0', 'add_meta_boxes' );

	/*
	 * Allow the Discussion meta box to show up if the post type supports comments,
	 * or if comments or pings are open.
	 */
	if ( comments_open( $post ) || pings_open( $post ) || post_type_supports( $post_type, 'comments' ) ) {
		add_meta_box( 'commentstatusdiv', __( 'Discussion' ), 'post_comment_status_meta_box', null, 'normal', 'core', array( '__back_compat_meta_box' => true ) );
	}

	$statuses = get_post_stati( array( 'public' => true ) );

	if ( empty( $statuses ) ) {
		$statuses = array( 'publish' );
	}

	$statuses[] = 'private';

	if ( in_array( get_post_status( $post ), $statuses, true ) ) {
		/*
		 * If the post type support comments, or the post has comments,
		 * allow the Comments meta box.
		 */
		if ( comments_open( $post ) || pings_open( $post ) || $post->comment_count > 0 || post_type_supports( $post_type, 'comments' ) ) {
			add_meta_box( 'commentsdiv', __( 'Comments' ), 'post_comment_meta_box', null, 'normal', 'core', array( '__back_compat_meta_box' => true ) );
		}
	}

	if ( ! ( 'pending' === get_post_status( $post ) && ! current_user_can( $post_type_object->cap->publish_posts ) ) ) {
		add_meta_box( 'slugdiv', __( 'Slug' ), 'post_slug_meta_box', null, 'normal', 'core', array( '__back_compat_meta_box' => true ) );
	}

	if ( post_type_supports( $post_type, 'author' ) && current_user_can( $post_type_object->cap->edit_others_posts ) ) {
		add_meta_box( 'authordiv', __( 'Author' ), 'post_author_meta_box', null, 'normal', 'core', array( '__back_compat_meta_box' => true ) );
	}

	/**
	 * Fires after all built-in meta boxes have been added.
	 *
	 * @since 3.0.0
	 *
	 * @param string  $post_type Post type.
	 * @param WP_Post $post      Post object.
	 */
	do_action( 'add_meta_boxes', $post_type, $post );

	/**
	 * Fires after all built-in meta boxes have been added, contextually for the given post type.
	 *
	 * The dynamic portion of the hook name, `$post_type`, refers to the post type of the post.
	 *
	 * Possible hook names include:
	 *
	 *  - `add_meta_boxes_post`
	 *  - `add_meta_boxes_page`
	 *  - `add_meta_boxes_attachment`
	 *
	 * @since 3.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	do_action( "add_meta_boxes_{$post_type}", $post );

	/**
	 * Fires after meta boxes have been added.
	 *
	 * Fires once for each of the default meta box contexts: normal, advanced, and side.
	 *
	 * @since 3.0.0
	 *
	 * @param string                $post_type Post type of the post on Edit Post screen, 'link' on Edit Link screen,
	 *                                         'dashboard' on Dashboard screen.
	 * @param string                $context   Meta box context. Possible values include 'normal', 'advanced', 'side'.
	 * @param WP_Post|object|string $post      Post object on Edit Post screen, link object on Edit Link screen,
	 *                                         an empty string on Dashboard screen.
	 */
	do_action( 'do_meta_boxes', $post_type, 'normal', $post );
	/** This action is documented in wp-admin/includes/meta-boxes.php */
	do_action( 'do_meta_boxes', $post_type, 'advanced', $post );
	/** This action is documented in wp-admin/includes/meta-boxes.php */
	do_action( 'do_meta_boxes', $post_type, 'side', $post );
}

} // namespace CharacterGeneratorDev
