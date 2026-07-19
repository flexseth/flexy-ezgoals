<?php
/**
 * Asset Enqueue Functions
 *
 * @package FlexyEzgoals
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register goal block type.
 *
 * Runs after npm run build has created build/goal-block/.
 *
 * @since 0.1.0
 */
function flexy_ezgoals_register_block() {
	$block_path = FLEXY_EZGOALS_PATH . 'build/goal-block';

	// Only register if build directory exists.
	if ( file_exists( $block_path ) ) {
		register_block_type( $block_path );
	}
}

/**
 * Fires during WordPress initialization.
 *
 * @since 0.1.0
 */
add_action( 'init', 'flexy_ezgoals_register_block' );

/**
 * Enqueue front-end styles.
 *
 * Only loads when post has goals.
 *
 * @since 0.1.0
 */
function flexy_ezgoals_enqueue_frontend_styles() {
	if ( ! is_singular() ) {
		return;
	}

	$post_id = get_the_ID();
	$goals   = flexy_ezgoals_get_goals( $post_id );

	if ( empty( $goals ) ) {
		return;
	}

	// Enqueue block styles (if block style exists).
	$block_style_path = FLEXY_EZGOALS_URL . 'build/goal-block/style-index.css';
	$block_style_file = FLEXY_EZGOALS_PATH . 'build/goal-block/style-index.css';

	if ( file_exists( $block_style_file ) ) {
		wp_enqueue_style(
			'flexy-ezgoals-block-style',
			$block_style_path,
			array(),
			FLEXY_EZGOALS_VERSION
		);
	}

	// Add inline CSS for custom colors.
	flexy_ezgoals_add_inline_color_styles();
}

/**
 * Fires when scripts and styles are enqueued for the front-end.
 *
 * @since 0.1.0
 */
add_action( 'wp_enqueue_scripts', 'flexy_ezgoals_enqueue_frontend_styles' );

/**
 * Add inline CSS for custom color scheme.
 *
 * @since 0.1.0
 */
function flexy_ezgoals_add_inline_color_styles() {
	$colors = get_option(
		'flexy_ezgoals_colors',
		array(
			'plenty'      => '#9e9e9e',
			'moderate'    => '#2196f3',
			'approaching' => '#ffeb3b',
			'due_soon'    => '#ff9800',
		)
	);

	$custom_css = sprintf(
		'.ezgoals-callout.ezgoals-callout-plenty-time { background-color: %s; }
		.ezgoals-callout.ezgoals-callout-moderate { background-color: %s; }
		.ezgoals-callout.ezgoals-callout-approaching { background-color: %s; }
		.ezgoals-callout.ezgoals-callout-due-soon { background-color: %s; }',
		esc_attr( $colors['plenty'] ),
		esc_attr( $colors['moderate'] ),
		esc_attr( $colors['approaching'] ),
		esc_attr( $colors['due_soon'] )
	);

	wp_add_inline_style( 'flexy-ezgoals-block-style', $custom_css );
}
