<?php

/**
 * Plugin Name: Coupon Prompt – Smart WooCommerce Coupon Notices
 * Description: Display smart coupon notices on cart/checkout if a valid WooCommerce coupon is available but not applied. Pro features available if pro folder exists.
 * Plugin URI: https://wordpress.org/plugins/coupon-prompt/
 * Version: 1.0.1
 * Author: Crafely
 * Author URI: https://profiles.wordpress.org/crafely
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: coupon-prompt
 * Requires at least: 5.0
 * Tested up to: 6.8.2
 * Requires PHP: 7.2
 * Tags: woocommerce, coupons, cart, checkout, prompt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Define constants for easier management and potential future use
if ( ! defined( 'COUPON_PROMPT_VERSION' ) ) {
	define( 'COUPON_PROMPT_VERSION', '1.0.1' );
}
if ( ! defined( 'COUPON_PROMPT_DIR' ) ) {
	define( 'COUPON_PROMPT_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'COUPON_PROMPT_URL' ) ) {
	define( 'COUPON_PROMPT_URL', plugin_dir_url( __FILE__ ) );
}

// Check if Pro version is available
if ( ! defined( 'COUPON_PROMPT_IS_PRO' ) ) {
	define( 'COUPON_PROMPT_IS_PRO', is_dir( COUPON_PROMPT_DIR . 'pro' ) );
}
if ( ! defined( 'COUPON_PROMPT_PRO_DIR' ) ) {
	define( 'COUPON_PROMPT_PRO_DIR', COUPON_PROMPT_DIR . 'pro/' );
}
if ( ! defined( 'COUPON_PROMPT_PRO_URL' ) ) {
	define( 'COUPON_PROMPT_PRO_URL', COUPON_PROMPT_URL . 'pro/' );
}

// Include plugin classes
require_once COUPON_PROMPT_DIR . 'includes/class-coupon-prompt-admin.php';
require_once COUPON_PROMPT_DIR . 'includes/class-coupon-prompt-frontend.php';
require_once COUPON_PROMPT_DIR . 'includes/class-coupon-prompt-utils.php';

// Include Pro classes if Pro version is available
if ( COUPON_PROMPT_IS_PRO ) {
	require_once COUPON_PROMPT_PRO_DIR . 'includes/class-coupon-prompt-pro-admin.php';
	require_once COUPON_PROMPT_PRO_DIR . 'includes/class-coupon-prompt-pro-frontend.php';
	require_once COUPON_PROMPT_PRO_DIR . 'includes/class-coupon-prompt-pro-analytics.php';
	require_once COUPON_PROMPT_PRO_DIR . 'includes/class-coupon-prompt-pro-targeting.php';
	require_once COUPON_PROMPT_PRO_DIR . 'includes/class-coupon-prompt-pro-ab-testing.php';
	require_once COUPON_PROMPT_PRO_DIR . 'includes/class-coupon-prompt-pro-license.php';
	require_once COUPON_PROMPT_PRO_DIR . 'includes/class-coupon-prompt-pro-settings.php';
	require_once COUPON_PROMPT_PRO_DIR . 'includes/class-coupon-prompt-pro-scheduler.php';
}

/**
 * Initialize the plugin, checking for WooCommerce.
 */
add_action( 'plugins_loaded', 'coupon_prompt_init' );
function coupon_prompt_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		// Show admin notice if WooCommerce is not active
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Coupon Prompt requires WooCommerce to be installed and active.', 'coupon-prompt' ) . '</p></div>';
			}
		);
		return;
	}

	// Initialize admin and frontend logic
	Coupon_Prompt_Admin::init();
	Coupon_Prompt_Frontend::init();

	// Initialize Pro features if available
	if ( COUPON_PROMPT_IS_PRO ) {
		coupon_prompt_init_pro();
	}
}

/**
 * Initialize Pro features
 */
function coupon_prompt_init_pro() {
	// Initialize Pro license system
	Coupon_Prompt_Pro_License::init();

	// Only initialize other Pro features if license is valid or in development
	if ( Coupon_Prompt_Pro_License::is_license_valid() || defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		Coupon_Prompt_Pro_Admin::init();
		Coupon_Prompt_Pro_Frontend::init();
		Coupon_Prompt_Pro_Analytics::init();
		Coupon_Prompt_Pro_Targeting::init();
		Coupon_Prompt_Pro_AB_Testing::init();
		Coupon_Prompt_Pro_Settings::init();
		Coupon_Prompt_Pro_Scheduler::init();
	}
}

/**
 * Activation hook
 */
register_activation_hook( __FILE__, 'coupon_prompt_activate' );
function coupon_prompt_activate() {
	// Create Pro analytics table if Pro version is available
	if ( COUPON_PROMPT_IS_PRO ) {
		Coupon_Prompt_Pro_Analytics::create_analytics_table();
		Coupon_Prompt_Pro_Settings::set_default_settings();

		// Schedule Pro cleanup events
		if ( ! wp_next_scheduled( 'coupon_prompt_pro_cleanup_analytics' ) ) {
			wp_schedule_event( time(), 'weekly', 'coupon_prompt_pro_cleanup_analytics' );
		}
	}
}

/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, 'coupon_prompt_deactivate' );
function coupon_prompt_deactivate() {
	// Clear Pro scheduled events if Pro version is available
	if ( COUPON_PROMPT_IS_PRO ) {
		wp_clear_scheduled_hook( 'coupon_prompt_pro_cleanup_analytics' );
		wp_clear_scheduled_hook( 'coupon_prompt_pro_check_scheduled_coupons' );
		wp_clear_scheduled_hook( 'coupon_prompt_pro_check_license' );
	}
}

/**
 * Check if Pro features are available
 */
function coupon_prompt_is_pro() {
	return COUPON_PROMPT_IS_PRO;
}

/**
 * Get Pro version status
 */
function coupon_prompt_get_pro_status() {
	if ( ! COUPON_PROMPT_IS_PRO ) {
		return array(
			'available' => false,
			'licensed'  => false,
			'message'   => __( 'Pro features not available. Contact support for Pro version.', 'coupon-prompt' ),
		);
	}

	$licensed = Coupon_Prompt_Pro_License::is_license_valid();

	return array(
		'available' => true,
		'licensed'  => $licensed,
		'message'   => $licensed
			? __( 'Pro features active and licensed.', 'coupon-prompt' )
			: __( 'Pro features available but not licensed. Please enter your license key.', 'coupon-prompt' ),
	);
}
