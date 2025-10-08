<?php
// Scheduler for Coupon Prompt Pro
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coupon_Prompt_Pro_Scheduler {

	public static function init() {
		add_filter( 'coupon_prompt_eligible_coupons', array( __CLASS__, 'apply_scheduling' ), 5, 2 );

		// Schedule hourly check for scheduled coupons
		if ( ! wp_next_scheduled( 'coupon_prompt_pro_check_scheduled_coupons' ) ) {
			wp_schedule_event( time(), 'hourly', 'coupon_prompt_pro_check_scheduled_coupons' );
		}
	}

	public static function apply_scheduling( $coupons, $context = array() ) {
		if ( ! Coupon_Prompt_Pro_Settings::is_scheduling_enabled() ) {
			return $coupons;
		}

		$filtered_coupons = array();
		$current_time     = current_time( 'mysql' );

		foreach ( $coupons as $coupon ) {
			if ( self::is_coupon_scheduled_for_now( $coupon->get_id(), $current_time ) ) {
				$filtered_coupons[] = $coupon;
			}
		}

		return $filtered_coupons;
	}

	public static function is_coupon_scheduled_for_now( $coupon_id, $current_time = null ) {
		if ( $current_time === null ) {
			$current_time = current_time( 'mysql' );
		}

		$schedule_start = get_post_meta( $coupon_id, 'coupon_prompt_pro_schedule_start', true );
		$schedule_end   = get_post_meta( $coupon_id, 'coupon_prompt_pro_schedule_end', true );

		// If no scheduling is set, coupon is always available
		if ( empty( $schedule_start ) && empty( $schedule_end ) ) {
			return true;
		}

		// Check start date
		if ( ! empty( $schedule_start ) && $current_time < $schedule_start ) {
			return false;
		}

		// Check end date
		if ( ! empty( $schedule_end ) && $current_time > $schedule_end ) {
			return false;
		}

		return true;
	}
}
