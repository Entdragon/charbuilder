<?php

namespace CharacterGeneratorDev {

/**
 * Edit tag form for inclusion in administration panels.
 *
 * @package WordPress
 * @subpackage Administration
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Back compat hooks.
if ( 'category' === $taxonomy ) {
	/**
	 * Fires before the Edit Category form.
	 *
	 * @since 2.1.0
	 * @deprecated 3.0.0 Use {@see '{$taxonomy}_pre_edit_form'} instead.
	 *
	 * @param WP_Term $tag Current category term object.
	 */
	do_action_deprecated( 'edit_category_form_pre', array( $tag ), '3.0.0', '{$taxonomy}_pre_edit_form' );
} elseif ( 'link_category' === $taxonomy ) {
	/**
	 * Fires before the Edit Link Category form.
	 *
	 * @since 2.3.0
	 * @deprecated 3.0.0 Use {@see '{$taxonomy}_pre_edit_form'} instead.
	 *
	 * @param WP_Term $tag Current link category term object.
	 */
	do_action_deprecated( 'edit_link_category_form_pre', array( $tag ), '3.0.0', '{$taxonomy}_pre_edit_form' );
} else {
	/**
	 * Fires before the Edit Tag form.
	 *
	 * @since 2.5.0
	 * @deprecated 3.0.0 Use {@see '{$taxonomy}_pre_edit_form'} instead.
	 *
	 * @param WP_Term $tag Current tag term object.
	 */
	do_action_deprecated( 'edit_tag_form_pre', array( $tag ), '3.0.0', '{$taxonomy}_pre_edit_form' );
}

$wp_http_referer = ! empty( $_REQUEST['wp_http_referer'] ) ? sanitize_url( $_REQUEST['wp_http_referer'] ) : '';
$wp_http_referer = remove_query_arg( array( 'action', 'message', 'tag_ID' ), $wp_http_referer );

// Also used by Edit Tags.
require_once ABSPATH . 'wp-admin/includes/edit-tag-messages.php';

/**
 * Fires before the Edit Term form for all taxonomies.
 *
 * The dynamic portion of the hook name, `$taxonomy`, refers to
 * the taxonomy slug.
 *
 * Possible hook names include:
 *
 *  - `category_pre_edit_form`
 *  - `post_tag_pre_edit_form`
 *
 * @since 3.0.0
 *
 * @param WP_Term $tag      Current taxonomy term object.
 * @param string  $taxonomy Current $taxonomy slug.
 */
do_action( "{$taxonomy}_pre_edit_form", $tag, $taxonomy ); ?>

<div class="wrap">

$class = ( isset( $_REQUEST['error'] ) ) ? 'error' : 'success';

if ( $message ) {
	$message = '<p><strong>' . $message . '</strong></p>';
	if ( $wp_http_referer ) {
		$message .= sprintf(
			'<p><a href="%1$s">%2$s</a></p>',
			esc_url( wp_validate_redirect( sanitize_url( $wp_http_referer ), admin_url( 'term.php?taxonomy=' . $taxonomy ) ) ),
			esc_html( $tax->labels->back_to_items )
		);
	}

	wp_admin_notice(
		$message,
		array(
			'type'           => $class,
			'id'             => 'message',
			'paragraph_wrap' => false,
		)
	);
}
?>

<div id="ajax-response"></div>

<form name="edittag" id="edittag" method="post" action="edit-tags.php" class="validate"
/**
 * Fires inside the Edit Term form tag.
 *
 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
 *
 * Possible hook names include:
 *
 *  - `category_term_edit_form_tag`
 *  - `post_tag_term_edit_form_tag`
 *
 * @since 3.7.0
 */
do_action( "{$taxonomy}_term_edit_form_tag" );
?>
>
<input type="hidden" name="action" value="editedtag" />
wp_original_referer_field( true, 'previous' );
wp_nonce_field( 'update-tag_' . $tag_ID );

/**
 * Fires at the beginning of the Edit Term form.
 *
 * At this point, the required hidden fields and nonces have already been output.
 *
 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
 *
 * Possible hook names include:
 *
 *  - `category_term_edit_form_top`
 *  - `post_tag_term_edit_form_top`
 *
 * @since 4.5.0
 *
 * @param WP_Term $tag      Current taxonomy term object.
 * @param string  $taxonomy Current $taxonomy slug.
 */
do_action( "{$taxonomy}_term_edit_form_top", $tag, $taxonomy );

$tag_name_value = '';
if ( isset( $tag->name ) ) {
	$tag_name_value = esc_attr( $tag->name );
}
?>
	<table class="form-table" role="presentation">
		<tr class="form-field form-required term-name-wrap">
		</tr>
		<tr class="form-field term-slug-wrap">
			/**
			 * Filters the editable slug for a post or term.
			 *
			 * Note: This is a multi-use hook in that it is leveraged both for editable
			 * post URIs and term slugs.
			 *
			 * @since 2.6.0
			 * @since 4.4.0 The `$tag` parameter was added.
			 *
			 * @param string          $slug The editable slug. Will be either a term slug or post URI depending
			 *                              upon the context in which it is evaluated.
			 * @param WP_Term|WP_Post $tag  Term or post object.
			 */
			$slug = isset( $tag->slug ) ? apply_filters( 'editable_slug', $tag->slug, $tag ) : '';
			?>
		</tr>
		<tr class="form-field term-parent-wrap">
			<td>
				$dropdown_args = array(
					'hide_empty'       => 0,
					'hide_if_empty'    => false,
					'taxonomy'         => $taxonomy,
					'name'             => 'parent',
					'orderby'          => 'name',
					'selected'         => $tag->parent,
					'exclude_tree'     => $tag->term_id,
					'hierarchical'     => true,
					'show_option_none' => __( 'None' ),
					'aria_describedby' => 'parent-description',
				);

				/** This filter is documented in wp-admin/edit-tags.php */
				$dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, $taxonomy, 'edit' );
				wp_dropdown_categories( $dropdown_args );
				?>
			</td>
		</tr>
		<tr class="form-field term-description-wrap">
		</tr>
		// Back compat hooks.
		if ( 'category' === $taxonomy ) {
			/**
			 * Fires after the Edit Category form fields are displayed.
			 *
			 * @since 2.9.0
			 * @deprecated 3.0.0 Use {@see '{$taxonomy}_edit_form_fields'} instead.
			 *
			 * @param WP_Term $tag Current category term object.
			 */
			do_action_deprecated( 'edit_category_form_fields', array( $tag ), '3.0.0', '{$taxonomy}_edit_form_fields' );
		} elseif ( 'link_category' === $taxonomy ) {
			/**
			 * Fires after the Edit Link Category form fields are displayed.
			 *
			 * @since 2.9.0
			 * @deprecated 3.0.0 Use {@see '{$taxonomy}_edit_form_fields'} instead.
			 *
			 * @param WP_Term $tag Current link category term object.
			 */
			do_action_deprecated( 'edit_link_category_form_fields', array( $tag ), '3.0.0', '{$taxonomy}_edit_form_fields' );
		} else {
			/**
			 * Fires after the Edit Tag form fields are displayed.
			 *
			 * @since 2.9.0
			 * @deprecated 3.0.0 Use {@see '{$taxonomy}_edit_form_fields'} instead.
			 *
			 * @param WP_Term $tag Current tag term object.
			 */
			do_action_deprecated( 'edit_tag_form_fields', array( $tag ), '3.0.0', '{$taxonomy}_edit_form_fields' );
		}
		/**
		 * Fires after the Edit Term form fields are displayed.
		 *
		 * The dynamic portion of the hook name, `$taxonomy`, refers to
		 * the taxonomy slug.
		 *
		 * Possible hook names include:
		 *
		 *  - `category_edit_form_fields`
		 *  - `post_tag_edit_form_fields`
		 *
		 * @since 3.0.0
		 *
		 * @param WP_Term $tag      Current taxonomy term object.
		 * @param string  $taxonomy Current taxonomy slug.
		 */
		do_action( "{$taxonomy}_edit_form_fields", $tag, $taxonomy );
		?>
	</table>
// Back compat hooks.
if ( 'category' === $taxonomy ) {
	/** This action is documented in wp-admin/edit-tags.php */
	do_action_deprecated( 'edit_category_form', array( $tag ), '3.0.0', '{$taxonomy}_add_form' );
} elseif ( 'link_category' === $taxonomy ) {
	/** This action is documented in wp-admin/edit-tags.php */
	do_action_deprecated( 'edit_link_category_form', array( $tag ), '3.0.0', '{$taxonomy}_add_form' );
} else {
	/**
	 * Fires at the end of the Edit Term form.
	 *
	 * @since 2.5.0
	 * @deprecated 3.0.0 Use {@see '{$taxonomy}_edit_form'} instead.
	 *
	 * @param WP_Term $tag Current taxonomy term object.
	 */
	do_action_deprecated( 'edit_tag_form', array( $tag ), '3.0.0', '{$taxonomy}_edit_form' );
}
/**
 * Fires at the end of the Edit Term form for all taxonomies.
 *
 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
 *
 * Possible hook names include:
 *
 *  - `category_edit_form`
 *  - `post_tag_edit_form`
 *
 * @since 3.0.0
 *
 * @param WP_Term $tag      Current taxonomy term object.
 * @param string  $taxonomy Current taxonomy slug.
 */
do_action( "{$taxonomy}_edit_form", $tag, $taxonomy );
?>

<div class="edit-tag-actions">


		<span id="delete-link">
		</span>

</div>

</form>
</div>

<script type="text/javascript">
try{document.forms.edittag.name.focus();}catch(e){}
</script>
endif;

} // namespace CharacterGeneratorDev
