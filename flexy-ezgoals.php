<?php
/**
 * Plugin Name:       EZ Goals
 * Plugin URI:        https://flexperception.com/ezgoals
 * Description:       Add goals to WordPress posts with deadline tracking and color-coded urgency indicators
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tested up to:      7.0
 * Author:            seth@flexperception.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flexy-ezgoals
 * Domain Path:       /languages
 *
 * @package FlexyEzgoals
 */

defined( 'ABSPATH' ) || exit;

define( 'FLEXY_EZGOALS_VERSION', '0.1.0' );
define( 'FLEXY_EZGOALS_PATH', plugin_dir_path( __FILE__ ) );
define( 'FLEXY_EZGOALS_URL', plugin_dir_url( __FILE__ ) );

// Require plugin includes.
require_once FLEXY_EZGOALS_PATH . 'includes/post-meta.php';
require_once FLEXY_EZGOALS_PATH . 'includes/display.php';
require_once FLEXY_EZGOALS_PATH . 'includes/caching.php';
require_once FLEXY_EZGOALS_PATH . 'includes/rest-api.php';
require_once FLEXY_EZGOALS_PATH . 'includes/settings.php';
require_once FLEXY_EZGOALS_PATH . 'includes/enqueue.php';

/**
 * Activation hook: Set up default settings and schedule cron.
 *
 * Idempotent - safe to run multiple times.
 */
function flexy_ezgoals_activate() {
	// Add default settings if they don't exist.
	if ( false === get_option( 'flexy_ezgoals_colors' ) ) {
		add_option(
			'flexy_ezgoals_colors',
			array(
				'plenty'      => '#9e9e9e',
				'moderate'    => '#2196f3',
				'approaching' => '#ffeb3b',
				'due_soon'    => '#ff9800',
			)
		);
	}

	// Schedule daily cache rebuild.
	if ( ! wp_next_scheduled( 'flexy_ezgoals_daily_cache_rebuild' ) ) {
		wp_schedule_event( time(), 'daily', 'flexy_ezgoals_daily_cache_rebuild' );
	}

	// Flush rewrite rules for REST API.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'flexy_ezgoals_activate' );

/**
 * Deactivation hook: Clean up scheduled tasks.
 * Do NOT delete user data.
 */
function flexy_ezgoals_deactivate() {
	$timestamp = wp_next_scheduled( 'flexy_ezgoals_daily_cache_rebuild' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'flexy_ezgoals_daily_cache_rebuild' );
	}
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'flexy_ezgoals_deactivate' );
