<?php
/**
 * REST API Endpoints
 *
 * @package FlexyEzgoals
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register custom REST API endpoints for goals.
 */
function flexy_ezgoals_register_rest_routes() {
	register_rest_route(
		'flexy-ezgoals/v1',
		'/goals/(?P<post_id>\d+)',
		array(
			'methods'             => 'GET',
			'callback'            => 'flexy_ezgoals_rest_get_goals',
			'permission_callback' => 'flexy_ezgoals_rest_permission_check',
			'args'                => array(
				'post_id' => array(
					'required'          => true,
					'validate_callback' => function( $param ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'flexy-ezgoals/v1',
		'/goals/(?P<post_id>\d+)',
		array(
			'methods'             => 'POST',
			'callback'            => 'flexy_ezgoals_rest_save_goals',
			'permission_callback' => 'flexy_ezgoals_rest_permission_check',
			'args'                => array(
				'post_id' => array(
					'required'          => true,
					'validate_callback' => function( $param ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => 'absint',
				),
				'goals'   => array(
					'required'          => true,
					'validate_callback' => function( $param ) {
						return is_array( $param );
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'flexy_ezgoals_register_rest_routes' );

/**
 * Permission callback for REST endpoints.
 * User must be able to edit the post.
 *
 * @param WP_REST_Request $request Request object.
 * @return bool True if user has permission.
 */
function flexy_ezgoals_rest_permission_check( $request ) {
	$post_id = $request->get_param( 'post_id' );

	if ( ! $post_id ) {
		return false;
	}

	return current_user_can( 'edit_post', $post_id );
}

/**
 * REST callback: Get goals for a post.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object.
 */
function flexy_ezgoals_rest_get_goals( $request ) {
	$post_id = $request->get_param( 'post_id' );

	$post = get_post( $post_id );
	if ( ! $post ) {
		return new WP_Error(
			'post_not_found',
			__( 'Post not found', 'flexy-ezgoals' ),
			array( 'status' => 404 )
		);
	}

	$goals = flexy_ezgoals_get_goals( $post_id );

	return rest_ensure_response(
		array(
			'post_id' => $post_id,
			'goals'   => $goals,
		)
	);
}

/**
 * REST callback: Save goals for a post.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object.
 */
function flexy_ezgoals_rest_save_goals( $request ) {
	$post_id = $request->get_param( 'post_id' );
	$goals   = $request->get_param( 'goals' );

	$post = get_post( $post_id );
	if ( ! $post ) {
		return new WP_Error(
			'post_not_found',
			__( 'Post not found', 'flexy-ezgoals' ),
			array( 'status' => 404 )
		);
	}

	if ( ! is_array( $goals ) ) {
		return new WP_Error(
			'invalid_goals',
			__( 'Goals must be an array', 'flexy-ezgoals' ),
			array( 'status' => 400 )
		);
	}

	$result = flexy_ezgoals_save_goals( $post_id, $goals );

	if ( false === $result ) {
		return new WP_Error(
			'save_failed',
			__( 'Failed to save goals', 'flexy-ezgoals' ),
			array( 'status' => 500 )
		);
	}

	return rest_ensure_response(
		array(
			'success' => true,
			'post_id' => $post_id,
			'goals'   => flexy_ezgoals_get_goals( $post_id ),
		)
	);
}
