<?php
/**
 * Caching and Cron Functions
 *
 * @package FlexyEzgoals
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daily cron callback: Rebuild due goals cache.
 *
 * Scans all posts and builds a transient of post IDs with due goals.
 *
 * @since 0.1.0
 */
function flexy_ezgoals_rebuild_due_cache() {
	$today    = current_time( 'Y-m-d' );
	$tomorrow = gmdate( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );
	$week_out = gmdate( 'Y-m-d', strtotime( '+7 days', current_time( 'timestamp' ) ) );

	// Query posts that have goals meta (optimize scan surface).
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required to find posts with goals; results are cached daily.
	$posts_with_goals = get_posts(
		array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_ezgoals_goals',
			'meta_compare'   => 'EXISTS',
		)
	);

	$due_today    = array();
	$due_soon     = array();
	$due_this_week = array();

	foreach ( $posts_with_goals as $post_id ) {
		$goals = flexy_ezgoals_get_active_goals( $post_id );

		foreach ( $goals as $goal ) {
			if ( empty( $goal['deadline'] ) ) {
				continue;
			}

			$deadline_date = gmdate( 'Y-m-d', strtotime( $goal['deadline'] ) );

			if ( $deadline_date === $today ) {
				$due_today[] = $post_id;
				break; // Post counted, move to next post.
			} elseif ( $deadline_date <= $tomorrow ) {
				$due_soon[] = $post_id;
				break;
			} elseif ( $deadline_date <= $week_out ) {
				$due_this_week[] = $post_id;
				break;
			}
		}
	}

	// Store transients (expire in 1 day).
	set_transient( 'ezgoals_due_posts_today', array_unique( $due_today ), DAY_IN_SECONDS );
	set_transient( 'ezgoals_due_posts_soon', array_unique( $due_soon ), DAY_IN_SECONDS );
	set_transient( 'ezgoals_due_posts_week', array_unique( $due_this_week ), DAY_IN_SECONDS );
}

/**
 * Fires on the daily cron event to rebuild goal cache.
 *
 * @since 0.1.0
 */
add_action( 'flexy_ezgoals_daily_cache_rebuild', 'flexy_ezgoals_rebuild_due_cache' );

/**
 * Clear goal caches.
 *
 * Called when goals are updated to ensure fresh data.
 *
 * @since 0.1.0
 */
function flexy_ezgoals_clear_cache() {
	delete_transient( 'ezgoals_due_posts_today' );
	delete_transient( 'ezgoals_due_posts_soon' );
	delete_transient( 'ezgoals_due_posts_week' );
}

/**
 * Get cached list of posts with goals due today.
 *
 * @since 0.1.0
 *
 * @return array Array of post IDs.
 */
function flexy_ezgoals_get_due_posts_today() {
	$due_posts = get_transient( 'ezgoals_due_posts_today' );

	// If cache doesn't exist, build it now.
	if ( false === $due_posts ) {
		flexy_ezgoals_rebuild_due_cache();
		$due_posts = get_transient( 'ezgoals_due_posts_today' );
	}

	return is_array( $due_posts ) ? $due_posts : array();
}

/**
 * Get cached list of posts with goals due soon (next 1-2 days).
 *
 * @since 0.1.0
 *
 * @return array Array of post IDs.
 */
function flexy_ezgoals_get_due_posts_soon() {
	$due_posts = get_transient( 'ezgoals_due_posts_soon' );

	if ( false === $due_posts ) {
		flexy_ezgoals_rebuild_due_cache();
		$due_posts = get_transient( 'ezgoals_due_posts_soon' );
	}

	return is_array( $due_posts ) ? $due_posts : array();
}

/**
 * Get cached list of posts with goals due this week.
 *
 * @since 0.1.0
 *
 * @return array Array of post IDs.
 */
function flexy_ezgoals_get_due_posts_week() {
	$due_posts = get_transient( 'ezgoals_due_posts_week' );

	if ( false === $due_posts ) {
		flexy_ezgoals_rebuild_due_cache();
		$due_posts = get_transient( 'ezgoals_due_posts_week' );
	}

	return is_array( $due_posts ) ? $due_posts : array();
}
