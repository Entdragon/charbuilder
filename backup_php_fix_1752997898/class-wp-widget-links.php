<?php

namespace CharacterGeneratorDev {

/**
 * Widget API: WP_Widget_Links class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

/**
 * Core class used to implement a Links widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class WP_Widget_Links extends WP_Widget {

	/**
	 * Sets up a new Links widget instance.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$widget_ops = array(
			'description'                 => __( 'Your blogroll' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'links', __( 'Links' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current Links widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Links widget instance.
	 */
	public function widget( $args, $instance ) {
		$show_description = isset( $instance['description'] ) ? $instance['description'] : false;
		$show_name        = isset( $instance['name'] ) ? $instance['name'] : false;
		$show_rating      = isset( $instance['rating'] ) ? $instance['rating'] : false;
		$show_images      = isset( $instance['images'] ) ? $instance['images'] : true;
		$category         = isset( $instance['category'] ) ? $instance['category'] : false;
		$orderby          = isset( $instance['orderby'] ) ? $instance['orderby'] : 'name';
		$order            = 'rating' === $orderby ? 'DESC' : 'ASC';
		$limit            = isset( $instance['limit'] ) ? $instance['limit'] : -1;

		$before_widget = preg_replace( '/ id="[^"]*"/', ' id="%id"', $args['before_widget'] );

		$widget_links_args = array(
			'title_before'     => $args['before_title'],
			'title_after'      => $args['after_title'],
			'category_before'  => $before_widget,
			'category_after'   => $args['after_widget'],
			'show_images'      => $show_images,
			'show_description' => $show_description,
			'show_name'        => $show_name,
			'show_rating'      => $show_rating,
			'category'         => $category,
			'class'            => 'linkcat widget',
			'orderby'          => $orderby,
			'order'            => $order,
			'limit'            => $limit,
		);

		/**
		 * Filters the arguments for the Links widget.
		 *
		 * @since 2.6.0
		 * @since 4.4.0 Added the `$instance` parameter.
		 *
		 * @see wp_list_bookmarks()
		 *
		 * @param array $widget_links_args An array of arguments to retrieve the links list.
		 * @param array $instance          The settings for the particular instance of the widget.
		 */
		wp_list_bookmarks( apply_filters( 'widget_links_args', $widget_links_args, $instance ) );
	}

	/**
	 * Handles updating settings for the current Links widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$new_instance = (array) $new_instance;
		$instance     = array(
			'images'      => 0,
			'name'        => 0,
			'description' => 0,
			'rating'      => 0,
		);
		foreach ( $instance as $field => $val ) {
			if ( isset( $new_instance[ $field ] ) ) {
				$instance[ $field ] = 1;
			}
		}

		$instance['orderby'] = 'name';
		if ( in_array( $new_instance['orderby'], array( 'name', 'rating', 'id', 'rand' ), true ) ) {
			$instance['orderby'] = $new_instance['orderby'];
		}

		$instance['category'] = (int) $new_instance['category'];
		$instance['limit']    = ! empty( $new_instance['limit'] ) ? (int) $new_instance['limit'] : -1;

		return $instance;
	}

	/**
	 * Outputs the settings form for the Links widget.
	 *
	 * @since 2.8.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		// Defaults.
		$instance  = wp_parse_args(
			(array) $instance,
			array(
				'images'      => true,
				'name'        => true,
				'description' => false,
				'rating'      => false,
				'category'    => false,
				'orderby'     => 'name',
				'limit'       => -1,
			)
		);
		$link_cats = get_terms( array( 'taxonomy' => 'link_category' ) );
		$limit     = (int) $instance['limit'];
		if ( ! $limit ) {
			$limit = -1;
		}
		?>
		<p>
					</option>
			</select>
			</select>
		</p>

		<p>
			<br />

			<br />

			<br />

		</p>

		<p>
		</p>
	}
}

} // namespace CharacterGeneratorDev
