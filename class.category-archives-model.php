<?php
/**
 * Model of Category Archives Widget.
 *
 * This Widget displays Category Archives links on category page.
 * Only displays links on category page.
 */
class CategoryArchivesModel {

	/**
	 * Get archives markup.
	 *
	 * Customize wp-includes/general-template.php -> wp_get_archives
	 *
	 * @param mixed(string|array) $args Optional. Override defaults.
	 *
	 * @return mixed(string|null) String when retrieving, null when displaying.
	 */
	public function get_archives( array $args ) {
		global $wpdb;

		// Get category id.
		$cat_id = (int) $args['cat_id'];

		// Set default args.
		$defaults = array(
			'type'            => 'month',
			'limit'           => '',
			'format'          => 'html',
			'before'          => '',
			'after'           => '',
			'show_post_count' => false,
			'echo'            => 1,
			'order'           => 'DESC',
		);

		$r = wp_parse_args( $args, $defaults );

		// Only year or month
		$type = ( '' === $r['type'] ) ? 'month' : $r['type'];

		$limit = '';
		if ( '' !== $r['limit'] ) {
			$limit = absint( $r['limit'] );
			$limit = ' LIMIT ' . $limit;
		}

		$order = strtoupper( $r['order'] );
		if ( 'ASC' !== $order ) {
			$order = 'DESC';
		}

		// Make query.
		$where = apply_filters(
			'getarchives_where',
			"WHERE post_type = 'post' AND post_status = 'publish' AND t.term_taxonomy_id = " . (int) $cat_id,
			$r
		);
		$join  = "LEFT JOIN $wpdb->term_relationships AS r "
		         . "ON $wpdb->posts.ID = r.object_ID LEFT JOIN $wpdb->term_taxonomy AS t "
		         . "ON r.term_taxonomy_id = t.term_taxonomy_id LEFT JOIN $wpdb->terms as terms "
		         . "ON t.term_id = terms.term_id";
		$join  = apply_filters( 'getarchives_join', $join, $r );

		// Control cache.
		$last_changed = wp_cache_get( 'last_changed', 'posts' );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, 'posts' );
		}

		// Get month archives.
		$output = '';
		if ( 'month' === $type ) {
			$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) AS posts "
			         . "FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) "
			         . "ORDER BY post_date $order $limit";
			// Get cache.
			$key = md5( $query );
			$key = "wp_get_archives:$key:$last_changed";
			// Exec query when cache is old. 
			if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
				$results = $wpdb->get_results( $query );
				wp_cache_set( $key, $results, 'posts' );
			}
			// Make month list.
			$format = preg_replace( '/[dDj,]/', '', get_option( 'date_format' ) );
			$format = preg_replace( '/-$/', '', $format );
			if ( ! empty( $results ) ) {
				$afterafter = $r['after'];
				foreach ( (array) $results as $result ) {
					$url  = $this->get_month_link( $result->year, $result->month, $cat_id );
					$date = sprintf( '%d-%s', $result->year, $result->month );
					$text = mysql2date( $format, $date );
					$after = '';
					if ( $r['show_post_count'] ) {
						$after = '&nbsp;( ' . $result->posts . ')' . $afterafter;
					}
					$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $after );
				}
			}
		}

		// Get year archives.
		if ( 'year' === $type ) {
			$query = "SELECT YEAR(post_date) AS `year`, count(ID) AS posts "
			         . "FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) "
			         . "ORDER BY post_date $order $limit";
			// Get cache.
			$key = md5( $query );
			$key = "wp_get_archives:$key:$last_changed";
			// Exec query when cache is old. 
			if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
				$results = $wpdb->get_results( $query );
				wp_cache_set( $key, $results, 'posts' );
			}
			// Make yearly list.
			$format = preg_replace( '/[dDj,]/', '', get_option( 'date_format' ) );
			$format = preg_replace( '/-$/', '', $format );
			if ( ! empty( $results ) ) {
				$afterafter = $r['after'];
				foreach ( (array) $results as $result ) {
					$url   = $this->get_year_link( $result->year, $cat_id, 'yealy' );
					$date  = sprintf( '%d', $result->year );
//					$text  = mysql2date( $format, $date );
					$text = $date;
					$after = '';
					if ( $r['show_post_count'] ) {
						$after = '&nbsp;( ' . $result->posts . ')' . $afterafter;
					}
					$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $after );
				}
			}
		}

		if ( ! empty( $r['echo'] ) ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Get link.
	 *
	 * Refer wp-includes/link-template.php->get_month_link
	 *
	 * @param mixed(bool|int) $year   False for current year. Integer of year.
	 * @param mixed(bool|int) $month  False for current month. Integer of month.
	 * @param int             $cat_id Category ID.
	 *
	 * @link    http://wpdocs.sourceforge.jp/%E9%96%A2%E6%95%B0%E3%83%AA%E3%83%95%E3%82%A1%E3%83%AC%E3%83%B3%E3%82%B9/WP_Rewrite
	 *
	 * @return string
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	private function get_month_link( $year, $month, $cat_id ) {
		global $wp_rewrite;

		if ( empty( $year ) ) {
			$year = gmdate( 'Y', current_time( 'timestamp' ) );
		}
		if ( empty( $month ) ) {
			$month = gmdate( 'm', current_time( 'timestamp' ) );
		}
		// Get link.
		$month_link = $wp_rewrite->get_month_permastruct();
		if ( ! empty( $month_link ) ) {
			$month_link = str_replace( '%year%', $year, $month_link );
			$month_link = str_replace( '%monthnum%', zeroise( intval( $month ), 2 ), $month_link );

			// Add category id to query parameter.
			return apply_filters(
				'month_link',
				home_url( user_trailingslashit( $month_link, 'month' ) ) . '?cat=' . (int) $cat_id,
				$year,
				$month
			);
		} else {
			return apply_filters(
				'month_link',
				home_url( '?m=' . $year . zeroise( $month, 2 ) . '&cat=' . (int) $cat_id ),
				$year,
				$month
			);
		}
	}

	/**
	 * Get year link.
	 *
	 * Refer wp-includes/link-template.php->get_month_link
	 *
	 * @param mixed(bool|int) $year   False for current year. Integer of year.
	 * @param int             $cat_id Category ID.
	 *
	 * @link    http://wpdocs.sourceforge.jp/%E9%96%A2%E6%95%B0%E3%83%AA%E3%83%95%E3%82%A1%E3%83%AC%E3%83%B3%E3%82%B9/WP_Rewrite
	 *
	 * @return string
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	private function get_year_link( $year, $cat_id ) {
		global $wp_rewrite;

		if ( empty( $year ) ) {
			$year = gmdate( 'Y', current_time( 'timestamp' ) );
		}
		$year_link = $wp_rewrite->get_year_permastruct();
		if ( ! empty( $year_link ) ) {
			$year_link = str_replace( '%year%', $year, $year_link );

			// Add category id to query parameter.
			return apply_filters(
				'year_link',
				home_url( user_trailingslashit( $year_link, 'year' ) ) . '?cat=' . (int) $cat_id,
				$year
			);
		} else {
			return apply_filters(
				'year_link',
				home_url( '?y=' . $year . '&cat=' . (int) $cat_id ),
				$year
			);
		}
	}
}
