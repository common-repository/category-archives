<?php
/**
 * Admin widget form view.
 */
?>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
		<?php esc_html_e( 'Title:', 'categoryarchives' ); ?>
	</label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
	       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
	       type="text" value="<?php echo esc_attr( $title ); ?>">
</p>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>">
		<?php esc_html_e( 'Select archive type', 'categoryarchives' ); ?>
	</label><br>
	<select class="select" type="select" <?php echo esc_attr( $type ); ?>
	        id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"
	        name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>">
		<?php $selected = ( 'month' === $type ) ? ' selected' : ''; ?>
		<option value="month"<?php echo $selected; ?>>Month</option>
		<?php $selected = ( 'year' === $type ) ? ' selected' : ''; ?>
		<option value="year"<?php echo $selected; ?>>Year</option>
	</select>
</p>
<p>
	<input class="checkbox" type="checkbox" <?php echo esc_attr( $count ); ?>
	       id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"
	       name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>">
	<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>">
		<?php esc_html_e( 'Show post counts', 'categoryarchives' ); ?>
	</label>
</p>
