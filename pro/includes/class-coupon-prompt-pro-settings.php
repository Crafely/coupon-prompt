<?php
// Settings for Coupon Prompt Pro
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coupon_Prompt_Pro_Settings {

	private static $option_name = 'coupon_prompt_pro_settings';

	public static function init() {
		// Settings are handled in the admin class
	}

	public static function set_default_settings() {
		$defaults = array(
			'analytics_enabled'      => true,
			'ab_testing_enabled'     => true,
			'guest_prompts_enabled'  => false,
			'max_prompts_per_page'   => 3,
			'prompt_position'        => 'before_cart',
			'enable_scheduling'      => true,
			'enable_targeting'       => true,
			'cleanup_analytics_days' => 365,
			'email_notifications'    => false,
			'notification_email'     => get_option( 'admin_email' ),
			'custom_css'             => '',
			'enable_shortcodes'      => true,
			'enable_widgets'         => true,
			'priority_sorting'       => true,
			'best_deal_logic'        => false,
			'debug_mode'             => false,
			'cache_duration'         => 0,
		);

		if ( ! get_option( self::$option_name ) ) {
			update_option( self::$option_name, $defaults );
		}
	}

	public static function get_settings() {
		$defaults = array(
			'analytics_enabled'      => true,
			'ab_testing_enabled'     => true,
			'guest_prompts_enabled'  => false,
			'max_prompts_per_page'   => 3,
			'prompt_position'        => 'before_cart',
			'enable_scheduling'      => true,
			'enable_targeting'       => true,
			'cleanup_analytics_days' => 365,
			'email_notifications'    => false,
			'notification_email'     => get_option( 'admin_email' ),
			'custom_css'             => '',
			'enable_shortcodes'      => true,
			'enable_widgets'         => true,
			'priority_sorting'       => true,
			'best_deal_logic'        => false,
			'debug_mode'             => false,
			'cache_duration'         => 0,
		);

		return wp_parse_args( get_option( self::$option_name, array() ), $defaults );
	}

	public static function get_setting( $key, $default = null ) {
		$settings = self::get_settings();
		return $settings[ $key ] ?? $default;
	}

	public static function update_setting( $key, $value ) {
		$settings         = self::get_settings();
		$settings[ $key ] = $value;
		update_option( self::$option_name, $settings );
	}

	public static function save_settings( $post_data ) {
		$settings = array();

		// Sanitize and save each setting
		$settings['analytics_enabled']      = isset( $post_data['analytics_enabled'] );
		$settings['ab_testing_enabled']     = isset( $post_data['ab_testing_enabled'] );
		$settings['guest_prompts_enabled']  = isset( $post_data['guest_prompts_enabled'] );
		$settings['max_prompts_per_page']   = absint( $post_data['max_prompts_per_page'] ?? 3 );
		$settings['prompt_position']        = sanitize_text_field( $post_data['prompt_position'] ?? 'before_cart' );
		$settings['enable_scheduling']      = isset( $post_data['enable_scheduling'] );
		$settings['enable_targeting']       = isset( $post_data['enable_targeting'] );
		$settings['cleanup_analytics_days'] = absint( $post_data['cleanup_analytics_days'] ?? 365 );
		$settings['email_notifications']    = isset( $post_data['email_notifications'] );
		$settings['notification_email']     = sanitize_email( $post_data['notification_email'] ?? get_option( 'admin_email' ) );
		$settings['custom_css']             = wp_strip_all_tags( $post_data['custom_css'] ?? '' );
		$settings['enable_shortcodes']      = isset( $post_data['enable_shortcodes'] );
		$settings['enable_widgets']         = isset( $post_data['enable_widgets'] );
		$settings['priority_sorting']       = isset( $post_data['priority_sorting'] );
		$settings['best_deal_logic']        = isset( $post_data['best_deal_logic'] );
		$settings['debug_mode']             = isset( $post_data['debug_mode'] );
		$settings['cache_duration']         = absint( $post_data['cache_duration'] ?? 0 );

		update_option( self::$option_name, $settings );

		return true;
	}

	public static function is_analytics_enabled() {
		return self::get_setting( 'analytics_enabled', true );
	}

	public static function is_ab_testing_enabled() {
		return self::get_setting( 'ab_testing_enabled', true );
	}

	public static function are_guest_prompts_enabled() {
		return self::get_setting( 'guest_prompts_enabled', false );
	}

	public static function get_max_prompts_per_page() {
		return self::get_setting( 'max_prompts_per_page', 3 );
	}

	public static function get_prompt_position() {
		return self::get_setting( 'prompt_position', 'before_cart' );
	}

	public static function is_scheduling_enabled() {
		return self::get_setting( 'enable_scheduling', true );
	}

	public static function is_targeting_enabled() {
		return self::get_setting( 'enable_targeting', true );
	}

	public static function is_debug_mode() {
		return self::get_setting( 'debug_mode', false ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG );
	}
}
