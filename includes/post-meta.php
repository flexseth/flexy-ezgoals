<?php
/**
 * Post Meta Registration and Helpers
 *
 * @package FlexyEzgoals
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register custom post meta for goals.
 *
 * Goals are stored as a serialized array in _ezgoals_goals meta key.
 *
 * @since 0.1.0
 */
function flexy_ezgoals_register_post_meta() {
	register_post_meta(
		'',
		'_ezgoals_goals',
		array(
			'type'          => 'array',
			'description'   => __( 'Goals attached to this post', 'flexy-ezgoals' ),
			'single'        => true,
			'show_in_rest'  => false, // Custom REST endpoint instead.
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}

/**
 * Fires when registering custom post meta.
 *
 * @since 0.1.0
 */
add_action( 'init', 'flexy_ezgoals_register_post_meta' );

/**
 * Get goals for a post.
 *
 * @since 0.1.0
 *
 * @param int $post_id Post ID.
 * @return array Array of goal objects.
 */
function flexy_ezgoals_get_goals( $post_id ) {
	$goals = get_post_meta( $post_id, '_ezgoals_goals', true );
	return is_array( $goals ) ? $goals : array();
}

/**
 * Save goals for a post.
 *
 * @since 0.1.0
 *
 * @param int   $post_id Post ID.
 * @param array $goals   Array of goal objects.
 * @return bool|int Meta ID on success, false on failure.
 */
function flexy_ezgoals_save_goals( $post_id, $goals ) {
	if ( ! is_array( $goals ) ) {
		return false;
	}

	// Sanitize each goal.
	$sanitized_goals = array();
	foreach ( $goals as $goal ) {
		if ( ! is_array( $goal ) ) {
			continue;
		}

		$sanitized_goals[] = array(
			'type'     => isset( $goal['type'] ) ? sanitize_text_field( $goal['type'] ) : '',
			'deadline' => isset( $goal['deadline'] ) ? sanitize_text_field( $goal['deadline'] ) : '',
			'status'   => isset( $goal['status'] ) ? sanitize_text_field( $goal['status'] ) : 'active',
			'label'    => isset( $goal['label'] ) ? sanitize_text_field( $goal['label'] ) : '',
		);
	}

	// Update post meta.
	$result = update_post_meta( $post_id, '_ezgoals_goals', $sanitized_goals );

	// Clear cache when goals are updated.
	flexy_ezgoals_clear_cache();

	return $result;
}

/**
 * Check if a goal deadline is approaching and return urgency level.
 *
 * @since 0.1.0
 *
 * @param string $deadline ISO 8601 date string.
 * @return string Urgency class: 'plenty-time', 'moderate', 'approaching', 'due-soon'.
 */
function flexy_ezgoals_is_due_soon( $deadline ) {
	if ( empty( $deadline ) ) {
		return 'plenty-time';
	}

	$deadline_timestamp = strtotime( $deadline );
	if ( false === $deadline_timestamp ) {
		return 'plenty-time';
	}

	$now      = current_time( 'timestamp' );
	$diff     = $deadline_timestamp - $now;
	$days_until = floor( $diff / DAY_IN_SECONDS );

	if ( $days_until < 0 ) {
		return 'due-soon'; // Overdue.
	} elseif ( $days_until < 1 ) {
		return 'due-soon'; // Due today or tomorrow.
	} elseif ( $days_until < 3 ) {
		return 'approaching'; // Due in 1-3 days.
	} elseif ( $days_until < 7 ) {
		return 'moderate'; // Due in 3-7 days.
	} else {
		return 'plenty-time'; // 7+ days.
	}
}

/**
 * Get active (non-completed) goals for a post.
 *
 * @since 0.1.0
 *
 * @param int $post_id Post ID.
 * @return array Array of active goal objects.
 */
function flexy_ezgoals_get_active_goals( $post_id ) {
	$goals        = flexy_ezgoals_get_goals( $post_id );
	$active_goals = array();

	foreach ( $goals as $goal ) {
		if ( isset( $goal['status'] ) && 'completed' === $goal['status'] ) {
			continue;
		}
		$active_goals[] = $goal;
	}

	return $active_goals;
}
