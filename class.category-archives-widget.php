<?php
/**
 * Category Archives Widget.
 *
 * This Widget displays year or month category archives links on category page.  
 * Only displays links on category page.
 */
class CategoryArchivesWidget extends WP_Widget {
	/**
	 * Setting widget
	 */
	function __construct() {
		$widget_ops = array(
				'description' => __(
						'Displays year or month archives link on category page.',
						'categoryarchives'
				),
		);
		parent::__construct(
				'CategoryArchivesWidget',
				__( 'Category Archives', 'mcarchives' ),
				$widget_ops
		);
	}

	/**
	 * Render view of admin form.
	 *
	 * @param array  $data     Associated array that is used by view.
	 * @param string $template Name of view file.
	 */
	private function render( $data, $template = 'category-archives-form-view.php' ) {
		extract( $data );
		include( dirname( __FILE__ ) . '/' . $template );
	}

	/**
	 * Display admin form of widget.
	 *
	 * @param object $instance Received from WordPress.
	 */
	function form( $instance ) {
		$instance = wp_parse_args(
				(array) $instance,
				array( 'title' => '', 'count' => 0, 'type' => 'month' )
		);
		$data     = array(
				'title' => sanitize_text_field( $instance['title'] ),
				'count' => $instance['count'] ? 'checked="checked"' : '',
				'type'  => $instance['type'],
		);

		$this->render( $data, 'category-archives-form-view.php' );
	}

	/**
	 * Update admin form of widget.
	 *
	 * @param object $new_instance Received from WordPress.
	 * @param object $old_instance Received from WordPress.
	 */
	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$new_instance      = wp_parse_args(
				(array) $new_instance,
				array( 'title' => '', 'count' => 0, 'type' => 'month' )
		);
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['type']  = sanitize_text_field( $new_instance['type'] );
		$instance['count'] = $new_instance['count'] ? 1 : 0;

		return $instance;
	}

	/**
	 * Display Category Archives links on front end.
	 *
	 * @param array  $args     Received from WordPress.
	 * @param object $instance Received from WordPress.
	 */
	function widget( $args, $instance ) {
		require_once( dirname( __FILE__ ) . '/class.category-archives-model.php' );
		global $wp_query;

		if ( ! is_category() ) {
			return;
		}

		$count = ! empty( $instance['count'] ) ? '1' : '0';
		$type  = $instance['type'];
		$title = apply_filters(
				'widget_title',
				empty( $instance['title'] ) ? __( 'Archives',
						'categoryarchives' ) : sanitize_text_field( $instance['title'] ),
				$instance,
				$this->id_base
		);

		// Get category id.
		$cat_id = $wp_query->query_vars['cat'];

		// Display year or month category archives link.
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<ul>
			<?php
			$model = new CategoryArchivesModel();
			$model->get_archives(
					apply_filters(
							'widget_archives_args',
							array(
									'type'            => $type,
									'show_post_count' => $count,
									'cat_id'          => $cat_id,
							)
					)
			);
			?>
		</ul>
		<?php
		echo $args['after_widget'];
	}

}
