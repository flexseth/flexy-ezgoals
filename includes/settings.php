<?php
/**
 * Settings Page
 *
 * @package FlexyEzgoals
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register settings via Settings API.
 */
function flexy_ezgoals_register_settings() {
	register_setting(
		'flexy_ezgoals_settings_group',
		'flexy_ezgoals_colors',
		array(
			'sanitize_callback' => 'flexy_ezgoals_sanitize_colors',
		)
	);

	add_settings_section(
		'flexy_ezgoals_colors_section',
		__( 'Urgency Color Scheme', 'flexy-ezgoals' ),
		'flexy_ezgoals_render_colors_section_description',
		'flexy-ezgoals'
	);

	add_settings_field(
		'plenty_color',
		__( 'Plenty of Time (7+ days)', 'flexy-ezgoals' ),
		'flexy_ezgoals_render_color_field',
		'flexy-ezgoals',
		'flexy_ezgoals_colors_section',
		array( 'field' => 'plenty' )
	);

	add_settings_field(
		'moderate_color',
		__( 'Moderate (3-7 days)', 'flexy-ezgoals' ),
		'flexy_ezgoals_render_color_field',
		'flexy-ezgoals',
		'flexy_ezgoals_colors_section',
		array( 'field' => 'moderate' )
	);

	add_settings_field(
		'approaching_color',
		__( 'Approaching (1-3 days)', 'flexy-ezgoals' ),
		'flexy_ezgoals_render_color_field',
		'flexy-ezgoals',
		'flexy_ezgoals_colors_section',
		array( 'field' => 'approaching' )
	);

	add_settings_field(
		'due_soon_color',
		__( 'Due Soon (< 1 day)', 'flexy-ezgoals' ),
		'flexy_ezgoals_render_color_field',
		'flexy-ezgoals',
		'flexy_ezgoals_colors_section',
		array( 'field' => 'due_soon' )
	);
}
add_action( 'admin_init', 'flexy_ezgoals_register_settings' );

/**
 * Sanitize color settings.
 *
 * @param array $input Raw input from form.
 * @return array Sanitized colors.
 */
function flexy_ezgoals_sanitize_colors( $input ) {
	$output = array();

	$fields = array( 'plenty', 'moderate', 'approaching', 'due_soon' );

	foreach ( $fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			$color = sanitize_hex_color( $input[ $field ] );
			$output[ $field ] = $color ? $color : '#9e9e9e';
		}
	}

	return $output;
}

/**
 * Render section description.
 */
function flexy_ezgoals_render_colors_section_description() {
	echo '<p>' . esc_html__( 'Customize the background colors for goal urgency levels. Colors should have good contrast for readability.', 'flexy-ezgoals' ) . '</p>';
}

/**
 * Render color picker field.
 *
 * @param array $args Field arguments.
 */
function flexy_ezgoals_render_color_field( $args ) {
	$field   = $args['field'];
	$options = get_option(
		'flexy_ezgoals_colors',
		array(
			'plenty'      => '#9e9e9e',
			'moderate'    => '#2196f3',
			'approaching' => '#ffeb3b',
			'due_soon'    => '#ff9800',
		)
	);
	$value   = isset( $options[ $field ] ) ? $options[ $field ] : '#9e9e9e';

	printf(
		'<input type="text" name="flexy_ezgoals_colors[%s]" value="%s" class="flexy-ezgoals-color-picker" />',
		esc_attr( $field ),
		esc_attr( $value )
	);
}

/**
 * Add settings page to admin menu.
 */
function flexy_ezgoals_add_settings_page() {
	add_options_page(
		__( 'EZ Goals Settings', 'flexy-ezgoals' ),
		__( 'EZ Goals', 'flexy-ezgoals' ),
		'manage_options',
		'flexy-ezgoals',
		'flexy_ezgoals_render_settings_page'
	);
}
add_action( 'admin_menu', 'flexy_ezgoals_add_settings_page' );

/**
 * Render settings page.
 */
function flexy_ezgoals_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Show update message.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Settings API handles nonce verification.
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error(
			'flexy_ezgoals_messages',
			'flexy_ezgoals_message',
			__( 'Settings saved.', 'flexy-ezgoals' ),
			'updated'
		);
	}

	settings_errors( 'flexy_ezgoals_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'flexy_ezgoals_settings_group' );
			do_settings_sections( 'flexy-ezgoals' );
			submit_button( __( 'Save Settings', 'flexy-ezgoals' ) );
			?>
		</form>
	</div>
	<?php
}

/**
 * Enqueue color picker assets on settings page.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function flexy_ezgoals_enqueue_settings_assets( $hook_suffix ) {
	if ( 'settings_page_flexy-ezgoals' !== $hook_suffix ) {
		return;
	}

	// Enqueue WordPress color picker.
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	// Initialize color picker.
	wp_add_inline_script(
		'wp-color-picker',
		'jQuery(document).ready(function($) { $(".flexy-ezgoals-color-picker").wpColorPicker(); });'
	);
}
add_action( 'admin_enqueue_scripts', 'flexy_ezgoals_enqueue_settings_assets' );
