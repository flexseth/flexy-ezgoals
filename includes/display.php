<?php
/**
 * Front-end Display Functions
 *
 * @package FlexyEzgoals
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inject goal callout above post content on front-end.
 *
 * @since 0.1.0
 *
 * @param string $content Post content.
 * @return string Modified content with goal callout prepended.
 */
function flexy_ezgoals_inject_goal_callout( $content ) {
	if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post_id = get_the_ID();
	$goals   = flexy_ezgoals_get_active_goals( $post_id );

	if ( empty( $goals ) ) {
		return $content;
	}

	$callout_html = flexy_ezgoals_render_callout( $goals );

	return $callout_html . $content;
}
/**
 * Filters the content to inject goal callout.
 *
 * @since 0.1.0
 *
 * @param string $content Post content.
 */
add_filter( 'the_content', 'flexy_ezgoals_inject_goal_callout', 10 );

/**
 * Render goal callout HTML.
 *
 * @since 0.1.0
 *
 * @param array $goals Array of goal objects.
 * @return string HTML output.
 */
function flexy_ezgoals_render_callout( $goals ) {
	if ( empty( $goals ) ) {
		return '';
	}

	$colors = get_option(
		'flexy_ezgoals_colors',
		array(
			'plenty'      => '#9e9e9e',
			'moderate'    => '#2196f3',
			'approaching' => '#ffeb3b',
			'due_soon'    => '#ff9800',
		)
	);

	$html = '<div class="ezgoals-callout-container">';

	foreach ( $goals as $goal ) {
		$urgency_class = flexy_ezgoals_is_due_soon( $goal['deadline'] );
		$urgency_label = flexy_ezgoals_get_urgency_label( $urgency_class );
		$type_label    = flexy_ezgoals_get_type_label( $goal['type'] );
		$deadline_formatted = ! empty( $goal['deadline'] ) ? date_i18n( get_option( 'date_format' ), strtotime( $goal['deadline'] ) ) : '';

		// Get color for this urgency level.
		$color_map = array(
			'plenty-time' => $colors['plenty'],
			'moderate'    => $colors['moderate'],
			'approaching' => $colors['approaching'],
			'due-soon'    => $colors['due_soon'],
		);
		$bg_color  = isset( $color_map[ $urgency_class ] ) ? $color_map[ $urgency_class ] : '#9e9e9e';

		// Calculate text color for contrast.
		$text_color = flexy_ezgoals_get_contrast_color( $bg_color );

		$html .= sprintf(
			'<div class="ezgoals-callout ezgoals-callout-%s" style="background-color: %s; color: %s;">
				<div class="ezgoals-callout-header">
					<span class="ezgoals-type">%s</span>
					<span class="ezgoals-urgency">%s</span>
				</div>
				<div class="ezgoals-callout-body">
					<p class="ezgoals-label">%s</p>
					<p class="ezgoals-deadline"><strong>%s:</strong> %s</p>
				</div>
			</div>',
			esc_attr( $urgency_class ),
			esc_attr( $bg_color ),
			esc_attr( $text_color ),
			esc_html( $type_label ),
			esc_html( $urgency_label ),
			esc_html( $goal['label'] ),
			esc_html__( 'Deadline', 'flexy-ezgoals' ),
			esc_html( $deadline_formatted )
		);
	}

	$html .= '</div>';

	return $html;
}

/**
 * Get human-readable urgency label.
 *
 * @since 0.1.0
 *
 * @param string $urgency_class Urgency class.
 * @return string Translated label.
 */
function flexy_ezgoals_get_urgency_label( $urgency_class ) {
	$labels = array(
		'plenty-time' => __( 'Plenty of time', 'flexy-ezgoals' ),
		'moderate'    => __( 'Upcoming', 'flexy-ezgoals' ),
		'approaching' => __( 'Approaching', 'flexy-ezgoals' ),
		'due-soon'    => __( 'Due Soon', 'flexy-ezgoals' ),
	);

	return isset( $labels[ $urgency_class ] ) ? $labels[ $urgency_class ] : $labels['plenty-time'];
}

/**
 * Get human-readable goal type label.
 *
 * @since 0.1.0
 *
 * @param string $type Goal type.
 * @return string Translated label.
 */
function flexy_ezgoals_get_type_label( $type ) {
	$labels = array(
		'near-term'   => __( 'Near Term Goal', 'flexy-ezgoals' ),
		'long-term'   => __( 'Long Term Goal', 'flexy-ezgoals' ),
		'stretch'     => __( 'Stretch Goal', 'flexy-ezgoals' ),
		'daily'       => __( 'Daily Goal', 'flexy-ezgoals' ),
	);

	return isset( $labels[ $type ] ) ? $labels[ $type ] : ucfirst( str_replace( '-', ' ', $type ) );
}

/**
 * Calculate contrasting text color (black or white) for a given background color.
 *
 * @since 0.1.0
 *
 * @param string $hex_color Background color in hex format.
 * @return string '#000000' or '#ffffff'.
 */
function flexy_ezgoals_get_contrast_color( $hex_color ) {
	// Remove # if present.
	$hex_color = ltrim( $hex_color, '#' );

	// Convert to RGB.
	$r = hexdec( substr( $hex_color, 0, 2 ) );
	$g = hexdec( substr( $hex_color, 2, 2 ) );
	$b = hexdec( substr( $hex_color, 4, 2 ) );

	// Calculate luminance.
	$luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;

	// Return black for light backgrounds, white for dark backgrounds.
	return $luminance > 0.5 ? '#000000' : '#ffffff';
}
