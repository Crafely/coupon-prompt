<?php
// Pro Frontend logic for Coupon Prompt Pro
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coupon_Prompt_Pro_Frontend {

	public static function init() {
		// Enhance the original frontend functionality
		add_filter( 'coupon_prompt_eligible_coupons', array( __CLASS__, 'apply_pro_filtering' ), 15, 2 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );

		// Add Pro badge to frontend
		add_action( 'wp_footer', array( __CLASS__, 'add_pro_badge' ) );
	}

	public static function apply_pro_filtering( $coupons, $context = array() ) {
		// Apply priority sorting
		if ( Coupon_Prompt_Pro_Settings::get_setting( 'priority_sorting', true ) ) {
			usort( $coupons, array( __CLASS__, 'sort_by_priority' ) );
		}

		// Limit number of prompts
		$max_prompts = Coupon_Prompt_Pro_Settings::get_max_prompts_per_page();
		if ( count( $coupons ) > $max_prompts ) {
			$coupons = array_slice( $coupons, 0, $max_prompts );
		}

		return $coupons;
	}

	private static function sort_by_priority( $a, $b ) {
		$priority_a = get_post_meta( $a->get_id(), 'coupon_prompt_pro_priority', true ) ?: 10;
		$priority_b = get_post_meta( $b->get_id(), 'coupon_prompt_pro_priority', true ) ?: 10;

		// Higher priority first
		return intval( $priority_b ) - intval( $priority_a );
	}

	public static function enqueue_frontend_assets() {
		if ( is_cart() || is_checkout() ) {
			wp_enqueue_script(
				'coupon-prompt-pro-frontend',
				COUPON_PROMPT_PRO_URL . 'assets/frontend.js',
				array( 'jquery' ),
				COUPON_PROMPT_VERSION,
				true
			);

			wp_enqueue_style(
				'coupon-prompt-pro-frontend',
				COUPON_PROMPT_PRO_URL . 'assets/frontend.css',
				array(),
				COUPON_PROMPT_VERSION
			);

			// Localize script with settings
			wp_localize_script(
				'coupon-prompt-pro-frontend',
				'couponPromptPro',
				array(
					'ajax_url'          => admin_url( 'admin-ajax.php' ),
					'nonce'             => wp_create_nonce( 'coupon_prompt_track' ),
					'analytics_enabled' => Coupon_Prompt_Pro_Settings::is_analytics_enabled(),
				)
			);
		}
	}

	public static function add_pro_badge() {
		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}

		if ( Coupon_Prompt_Pro_Settings::is_debug_mode() ) {
			?>
			<div style="position: fixed; bottom: 10px; right: 10px; background: linear-gradient(135deg, #00d4aa, #0ea5e9); color: white; padding: 8px 12px; border-radius: 20px; font-size: 12px; z-index: 9999; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
				⚡ Coupon Prompt Pro Active
			</div>
			<?php
		}
	}
}
