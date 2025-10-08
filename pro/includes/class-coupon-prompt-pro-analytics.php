<?php
// Analytics for Coupon Prompt Pro
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coupon_Prompt_Pro_Analytics {

	public static function init() {
		add_action( 'wp_ajax_coupon_prompt_track_view', array( __CLASS__, 'track_view' ) );
		add_action( 'wp_ajax_nopriv_coupon_prompt_track_view', array( __CLASS__, 'track_view' ) );
		add_action( 'wp_ajax_coupon_prompt_track_click', array( __CLASS__, 'track_click' ) );
		add_action( 'wp_ajax_nopriv_coupon_prompt_track_click', array( __CLASS__, 'track_click' ) );

		// Clean up old analytics data weekly
		add_action( 'coupon_prompt_pro_cleanup_analytics', array( __CLASS__, 'cleanup_old_data' ) );

		// Track successful coupon applications
		add_action( 'woocommerce_applied_coupon', array( __CLASS__, 'track_coupon_application' ) );

		// Track order completion with coupons
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'track_order_completion' ) );
	}

	public static function create_analytics_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'coupon_prompt_analytics';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            coupon_id bigint(20) NOT NULL,
            coupon_code varchar(100) NOT NULL,
            event_type varchar(20) NOT NULL,
            user_id bigint(20) DEFAULT 0,
            session_id varchar(100) NOT NULL,
            ab_variant varchar(1) DEFAULT 'A',
            cart_value decimal(10,2) DEFAULT 0,
            user_agent text,
            ip_address varchar(45),
            page_url varchar(255),
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            order_id bigint(20) DEFAULT 0,
            revenue decimal(10,2) DEFAULT 0,
            PRIMARY KEY (id),
            KEY coupon_id (coupon_id),
            KEY event_type (event_type),
            KEY timestamp (timestamp),
            KEY session_id (session_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function track_view() {
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'coupon_prompt_track' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$coupon_id   = absint( $_POST['coupon_id'] ?? 0 );
		$coupon_code = sanitize_text_field( $_POST['coupon_code'] ?? '' );
		$ab_variant  = sanitize_text_field( $_POST['ab_variant'] ?? 'A' );

		self::record_event( 'view', $coupon_id, $coupon_code, $ab_variant );

		wp_send_json_success();
	}

	public static function track_click() {
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'coupon_prompt_track' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$coupon_id   = absint( $_POST['coupon_id'] ?? 0 );
		$coupon_code = sanitize_text_field( $_POST['coupon_code'] ?? '' );
		$ab_variant  = sanitize_text_field( $_POST['ab_variant'] ?? 'A' );

		self::record_event( 'click', $coupon_id, $coupon_code, $ab_variant );

		wp_send_json_success();
	}

	public static function track_coupon_application( $coupon_code ) {
		$coupon = new WC_Coupon( $coupon_code );
		if ( ! $coupon->get_id() ) {
			return;
		}

		// Check if this was from our prompt
		$session_id = self::get_session_id();
		global $wpdb;

		$recent_click = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}coupon_prompt_analytics
             WHERE coupon_code = %s AND session_id = %s AND event_type = 'click'
             AND timestamp > %s ORDER BY timestamp DESC LIMIT 1",
				$coupon_code,
				$session_id,
				date( 'Y-m-d H:i:s', strtotime( '-1 hour' ) )
			)
		);

		if ( $recent_click ) {
			self::record_event( 'applied', $coupon->get_id(), $coupon_code, $recent_click->ab_variant );
		}
	}

	public static function track_order_completion( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$coupons = $order->get_coupon_codes();
		if ( empty( $coupons ) ) {
			return;
		}

		$session_id = self::get_session_id();
		global $wpdb;

		foreach ( $coupons as $coupon_code ) {
			$recent_application = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}coupon_prompt_analytics
                 WHERE coupon_code = %s AND session_id = %s AND event_type = 'applied'
                 AND timestamp > %s ORDER BY timestamp DESC LIMIT 1",
					$coupon_code,
					$session_id,
					date( 'Y-m-d H:i:s', strtotime( '-24 hours' ) )
				)
			);

			if ( $recent_application ) {
				$coupon = new WC_Coupon( $coupon_code );

				self::record_event(
					'converted',
					$coupon->get_id(),
					$coupon_code,
					$recent_application->ab_variant,
					array(
						'order_id' => $order_id,
						'revenue'  => $order->get_total(),
					)
				);
			}
		}
	}

	private static function record_event( $event_type, $coupon_id, $coupon_code, $ab_variant = 'A', $additional_data = array() ) {
		global $wpdb;

		$cart_value = 0;
		if ( function_exists( 'WC' ) && WC()->cart ) {
			$cart_value = WC()->cart->get_subtotal();
		}

		$data = array(
			'coupon_id'   => $coupon_id,
			'coupon_code' => $coupon_code,
			'event_type'  => $event_type,
			'user_id'     => get_current_user_id(),
			'session_id'  => self::get_session_id(),
			'ab_variant'  => $ab_variant,
			'cart_value'  => $cart_value,
			'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'ip_address'  => self::get_client_ip(),
			'page_url'    => isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '',
			'timestamp'   => current_time( 'mysql' ),
		);

		if ( isset( $additional_data['order_id'] ) ) {
			$data['order_id'] = $additional_data['order_id'];
		}

		if ( isset( $additional_data['revenue'] ) ) {
			$data['revenue'] = $additional_data['revenue'];
		}

		$wpdb->insert( $wpdb->prefix . 'coupon_prompt_analytics', $data );
	}

	private static function get_session_id() {
		// Use a combination of user IP and user agent for session identification
		$user_ip    = self::get_client_ip();
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		// Get session token for logged-in users, or use a fallback for guests
		$session_token = '';
		if ( is_user_logged_in() ) {
			$session_token = wp_get_session_token();
		} else {
			// For guest users, use a combination of IP and user agent as session identifier
			$session_token = md5( $user_ip . $user_agent );
		}

		// Create a unique session identifier
		$session_key = 'coupon_prompt_session_' . md5( $user_ip . $user_agent . $session_token );

		// Check if we already have a session ID stored
		$session_id = get_transient( $session_key );

		if ( ! $session_id ) {
			$session_id = wp_generate_uuid4();
			// Store for 1 hour
			set_transient( $session_key, $session_id, HOUR_IN_SECONDS );
		}

		return $session_id;
	}

	private static function get_client_ip() {
		// Use WordPress approach to safely access server variables
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );

		foreach ( $ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) && ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );

				// Handle comma-separated IPs (first one is usually the client)
				if ( strpos( $ip, ',' ) !== false ) {
					$ip_list = explode( ',', $ip );
					$ip      = trim( $ip_list[0] );
				}

				$ip = trim( $ip );

				// Validate the IP and exclude private/reserved ranges for security
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return $ip;
				}
			}
		}

		// Fallback to REMOTE_ADDR if available, or default
		if ( isset( $_SERVER['REMOTE_ADDR'] ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return '0.0.0.0';
	}

	public static function get_analytics_data( $date_range = 30 ) {
		global $wpdb;

		$start_date = date( 'Y-m-d H:i:s', strtotime( "-{$date_range} days" ) );

		// Get overall stats
		$stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
                COUNT(CASE WHEN event_type = 'view' THEN 1 END) as total_views,
                COUNT(CASE WHEN event_type = 'click' THEN 1 END) as total_clicks,
                COUNT(CASE WHEN event_type = 'applied' THEN 1 END) as total_applications,
                COUNT(CASE WHEN event_type = 'converted' THEN 1 END) as total_conversions,
                SUM(CASE WHEN event_type = 'converted' THEN revenue ELSE 0 END) as total_revenue
             FROM {$wpdb->prefix}coupon_prompt_analytics
             WHERE timestamp >= %s",
				$start_date
			)
		);

		// Get daily data for charts
		$daily_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
                DATE(timestamp) as date,
                COUNT(CASE WHEN event_type = 'view' THEN 1 END) as views,
                COUNT(CASE WHEN event_type = 'click' THEN 1 END) as clicks,
                COUNT(CASE WHEN event_type = 'applied' THEN 1 END) as applications,
                COUNT(CASE WHEN event_type = 'converted' THEN 1 END) as conversions,
                SUM(CASE WHEN event_type = 'converted' THEN revenue ELSE 0 END) as revenue
             FROM {$wpdb->prefix}coupon_prompt_analytics
             WHERE timestamp >= %s
             GROUP BY DATE(timestamp)
             ORDER BY date",
				$start_date
			)
		);

		// Get coupon performance
		$coupon_performance = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
                coupon_code,
                COUNT(CASE WHEN event_type = 'view' THEN 1 END) as views,
                COUNT(CASE WHEN event_type = 'click' THEN 1 END) as clicks,
                COUNT(CASE WHEN event_type = 'applied' THEN 1 END) as applications,
                COUNT(CASE WHEN event_type = 'converted' THEN 1 END) as conversions,
                SUM(CASE WHEN event_type = 'converted' THEN revenue ELSE 0 END) as revenue
             FROM {$wpdb->prefix}coupon_prompt_analytics
             WHERE timestamp >= %s
             GROUP BY coupon_code
             ORDER BY conversions DESC",
				$start_date
			)
		);

		// Calculate conversion rates
		$conversion_rate  = ( $stats && $stats->total_views > 0 ) ? round( ( $stats->total_clicks / $stats->total_views ) * 100, 2 ) : 0;
		$application_rate = ( $stats && $stats->total_clicks > 0 ) ? round( ( $stats->total_applications / $stats->total_clicks ) * 100, 2 ) : 0;

		return array(
			'stats'              => $stats ?: (object) array(
				'total_views'        => 0,
				'total_clicks'       => 0,
				'total_applications' => 0,
				'total_conversions'  => 0,
				'total_revenue'      => 0,
			),
			'conversion_rate'    => $conversion_rate,
			'application_rate'   => $application_rate,
			'daily_data'         => $daily_data ?: array(),
			'coupon_performance' => $coupon_performance ?: array(),
		);
	}

	public static function get_dashboard_stats() {
		global $wpdb;

		$start_date = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );

		$stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
                COUNT(CASE WHEN event_type = 'view' THEN 1 END) as total_views,
                COUNT(CASE WHEN event_type = 'click' THEN 1 END) as total_clicks,
                COUNT(CASE WHEN event_type = 'converted' THEN 1 END) as total_conversions,
                SUM(CASE WHEN event_type = 'converted' THEN revenue ELSE 0 END) as revenue_generated
             FROM {$wpdb->prefix}coupon_prompt_analytics
             WHERE timestamp >= %s",
				$start_date
			)
		);

		$conversion_rate = ( $stats && $stats->total_views > 0 ) ? round( ( $stats->total_clicks / $stats->total_views ) * 100, 2 ) : 0;

		return array(
			'total_views'       => $stats ? $stats->total_views : 0,
			'total_clicks'      => $stats ? $stats->total_clicks : 0,
			'total_conversions' => $stats ? $stats->total_conversions : 0,
			'conversion_rate'   => $conversion_rate,
			'revenue_generated' => $stats ? $stats->revenue_generated : 0,
		);
	}

	public static function get_ab_test_results( $coupon_id, $date_range = 30 ) {
		global $wpdb;

		$start_date = date( 'Y-m-d H:i:s', strtotime( "-{$date_range} days" ) );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
                ab_variant,
                COUNT(CASE WHEN event_type = 'view' THEN 1 END) as views,
                COUNT(CASE WHEN event_type = 'click' THEN 1 END) as clicks,
                COUNT(CASE WHEN event_type = 'applied' THEN 1 END) as applications,
                COUNT(CASE WHEN event_type = 'converted' THEN 1 END) as conversions
             FROM {$wpdb->prefix}coupon_prompt_analytics
             WHERE coupon_id = %d AND timestamp >= %s
             GROUP BY ab_variant",
				$coupon_id,
				$start_date
			)
		);
	}

	public static function cleanup_old_data() {
		global $wpdb;

		// Keep data based on settings
		$retention_days = Coupon_Prompt_Pro_Settings::get_setting( 'cleanup_analytics_days', 365 );
		$cutoff_date    = date( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}coupon_prompt_analytics WHERE timestamp < %s",
				$cutoff_date
			)
		);
	}
}
