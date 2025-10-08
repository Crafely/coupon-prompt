<?php
// Advanced targeting for Coupon Prompt Pro
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coupon_Prompt_Pro_Targeting {

	public static function init() {
		// Hook into the coupon filtering process
		add_filter( 'coupon_prompt_eligible_coupons', array( __CLASS__, 'apply_targeting_rules' ), 10, 2 );
	}

	public static function apply_targeting_rules( $coupons, $context = array() ) {
		if ( ! Coupon_Prompt_Pro_Settings::is_targeting_enabled() ) {
			return $coupons;
		}

		$filtered_coupons = array();

		foreach ( $coupons as $coupon ) {
			if ( self::is_coupon_targeted_for_user( $coupon ) ) {
				$filtered_coupons[] = $coupon;
			}
		}

		return $filtered_coupons;
	}

	public static function is_coupon_targeted_for_user( $coupon ) {
		$coupon_id = $coupon->get_id();

		// Check cart value targeting
		if ( ! self::check_cart_value_targeting( $coupon_id ) ) {
			return false;
		}

		// Check first-time customer targeting
		if ( ! self::check_first_time_customer_targeting( $coupon_id ) ) {
			return false;
		}

		return true;
	}

	private static function check_cart_value_targeting( $coupon_id ) {
		$min_cart_value = get_post_meta( $coupon_id, 'coupon_prompt_pro_min_cart_value', true );
		$max_cart_value = get_post_meta( $coupon_id, 'coupon_prompt_pro_max_cart_value', true );

		if ( empty( $min_cart_value ) && empty( $max_cart_value ) ) {
			return true; // No cart value targeting
		}

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return false;
		}

		$cart_total = WC()->cart->get_subtotal();

		if ( ! empty( $min_cart_value ) && $cart_total < floatval( $min_cart_value ) ) {
			return false;
		}

		if ( ! empty( $max_cart_value ) && $cart_total > floatval( $max_cart_value ) ) {
			return false;
		}

		return true;
	}

	private static function check_first_time_customer_targeting( $coupon_id ) {
		$first_time_only = get_post_meta( $coupon_id, 'coupon_prompt_pro_first_time_only', true );

		if ( $first_time_only !== 'yes' ) {
			return true; // No first-time targeting
		}

		if ( ! is_user_logged_in() ) {
			return true; // Assume guest is first-time
		}

		$user_id         = get_current_user_id();
		$customer_orders = wc_get_orders(
			array(
				'customer' => $user_id,
				'status'   => array( 'completed', 'processing' ),
				'limit'    => 1,
			)
		);

		return empty( $customer_orders );
	}
}
