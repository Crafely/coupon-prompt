<?php
// A/B Testing for Coupon Prompt Pro
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coupon_Prompt_Pro_AB_Testing {

	public static function init() {
		add_filter( 'coupon_prompt_message_data', array( __CLASS__, 'apply_ab_testing' ), 10, 2 );
	}

	public static function apply_ab_testing( $message_data, $coupon ) {
		if ( ! Coupon_Prompt_Pro_Settings::is_ab_testing_enabled() ) {
			return $message_data;
		}

		$coupon_id          = $coupon->get_id();
		$ab_testing_enabled = get_post_meta( $coupon_id, 'coupon_prompt_pro_ab_testing', true );

		if ( $ab_testing_enabled !== 'yes' ) {
			return $message_data;
		}

		$variant = self::get_user_variant( $coupon_id );

		if ( $variant === 'B' ) {
			$message_b = get_post_meta( $coupon_id, 'coupon_prompt_pro_ab_message_b', true );
			$button_b  = get_post_meta( $coupon_id, 'coupon_prompt_pro_ab_button_b', true );

			if ( ! empty( $message_b ) ) {
				$message_data['message'] = $message_b;
			}

			if ( ! empty( $button_b ) ) {
				$message_data['button_text'] = $button_b;
			}
		}

		$message_data['ab_variant'] = $variant;

		return $message_data;
	}

	public static function get_user_variant( $coupon_id ) {
		// Use a consistent method to determine user variant
		$user_identifier = self::get_user_identifier();
		$hash            = md5( $coupon_id . $user_identifier );
		$numeric_hash    = hexdec( substr( $hash, 0, 8 ) );

		// 50/50 split between A and B
		return ( $numeric_hash % 2 === 0 ) ? 'A' : 'B';
	}

	private static function get_user_identifier() {
		if ( is_user_logged_in() ) {
			return 'user_' . get_current_user_id();
		}

		// For guests, use IP and user agent as identifier
		$ip         = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		return 'guest_' . md5( $ip . $user_agent . date( 'Y-m-d' ) );
	}
}
