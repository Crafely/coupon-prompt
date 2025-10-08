<?php
// Pro Admin UI for Coupon Prompt Pro
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coupon_Prompt_Pro_Admin {

	public static function init() {
		// Add Pro indicator to admin bar
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_pro_indicator' ), 999 );

		// Add Pro fields to coupon edit page
		add_action( 'woocommerce_coupon_options', array( __CLASS__, 'add_pro_coupon_fields' ), 20 );
		add_action( 'woocommerce_process_shop_coupon_meta', array( __CLASS__, 'save_pro_coupon_fields' ), 20, 2 );

		// Add Pro admin menu
		add_action( 'admin_menu', array( __CLASS__, 'add_pro_admin_menu' ) );

		// Enqueue admin assets
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );

		// Add Pro column to coupons list
		add_filter( 'manage_shop_coupon_posts_columns', array( __CLASS__, 'add_coupon_columns' ) );
		add_action( 'manage_shop_coupon_posts_custom_column', array( __CLASS__, 'coupon_column_content' ), 10, 2 );
	}

	public static function add_admin_bar_pro_indicator( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$license_info = Coupon_Prompt_Pro_License::get_license_info();

		$wp_admin_bar->add_node(
			array(
				'id'    => 'coupon-prompt-pro',
				'title' => '<span style="color: #00d4aa;">⚡ Coupon Prompt Pro</span>',
				'href'  => admin_url( 'admin.php?page=coupon-prompt-pro-license' ),
				'meta'  => array(
					'title' => $license_info['valid'] ? 'Pro features active' : 'Pro features available',
				),
			)
		);
	}

	public static function add_pro_coupon_fields( $coupon_id ) {
		echo '<div class="coupon-prompt-pro-fields" style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 20px;">';
		echo '<h3 style="color: #00d4aa;"><span style="font-size: 16px;">⚡</span> ' . __( 'Coupon Prompt Pro Settings', 'coupon-prompt' ) . '</h3>';

		// Targeting Rules
		echo '<div class="options_group">';
		echo '<h4>' . __( 'Advanced Targeting', 'coupon-prompt' ) . '</h4>';

		// Priority
		woocommerce_wp_text_input(
			array(
				'id'                => 'coupon_prompt_pro_priority',
				'label'             => __( 'Display Priority', 'coupon-prompt' ),
				'description'       => __( 'Higher numbers show first. Default: 10', 'coupon-prompt' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '0' ),
				'value'             => get_post_meta( $coupon_id, 'coupon_prompt_pro_priority', true ) ?: '10',
			)
		);

		// Minimum cart value
		woocommerce_wp_text_input(
			array(
				'id'                => 'coupon_prompt_pro_min_cart_value',
				'label'             => __( 'Minimum Cart Value', 'coupon-prompt' ),
				'description'       => __( 'Show this prompt only when cart value is above this amount.', 'coupon-prompt' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '0.01',
					'min'  => '0',
				),
				'value'             => get_post_meta( $coupon_id, 'coupon_prompt_pro_min_cart_value', true ),
			)
		);

		// Maximum cart value
		woocommerce_wp_text_input(
			array(
				'id'                => 'coupon_prompt_pro_max_cart_value',
				'label'             => __( 'Maximum Cart Value', 'coupon-prompt' ),
				'description'       => __( 'Show this prompt only when cart value is below this amount.', 'coupon-prompt' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '0.01',
					'min'  => '0',
				),
				'value'             => get_post_meta( $coupon_id, 'coupon_prompt_pro_max_cart_value', true ),
			)
		);

		// First time customers only
		woocommerce_wp_checkbox(
			array(
				'id'          => 'coupon_prompt_pro_first_time_only',
				'label'       => __( 'First-time Customers Only', 'coupon-prompt' ),
				'description' => __( 'Show this prompt only to customers who have never made a purchase.', 'coupon-prompt' ),
				'value'       => get_post_meta( $coupon_id, 'coupon_prompt_pro_first_time_only', true ) ? 'yes' : '',
			)
		);

		echo '</div>';

		// A/B Testing
		echo '<div class="options_group">';
		echo '<h4>' . __( 'A/B Testing', 'coupon-prompt' ) . '</h4>';

		// Enable A/B testing
		woocommerce_wp_checkbox(
			array(
				'id'          => 'coupon_prompt_pro_ab_testing',
				'label'       => __( 'Enable A/B Testing', 'coupon-prompt' ),
				'description' => __( 'Test different versions of this prompt.', 'coupon-prompt' ),
				'value'       => get_post_meta( $coupon_id, 'coupon_prompt_pro_ab_testing', true ) ? 'yes' : '',
			)
		);

		// Variant B message
		woocommerce_wp_text_input(
			array(
				'id'          => 'coupon_prompt_pro_ab_message_b',
				'label'       => __( 'A/B Test Message B', 'coupon-prompt' ),
				'description' => __( 'Alternative message for A/B testing.', 'coupon-prompt' ),
				'desc_tip'    => true,
				'value'       => get_post_meta( $coupon_id, 'coupon_prompt_pro_ab_message_b', true ),
			)
		);

		// Variant B button text
		woocommerce_wp_text_input(
			array(
				'id'          => 'coupon_prompt_pro_ab_button_b',
				'label'       => __( 'A/B Test Button B', 'coupon-prompt' ),
				'description' => __( 'Alternative button text for A/B testing.', 'coupon-prompt' ),
				'desc_tip'    => true,
				'value'       => get_post_meta( $coupon_id, 'coupon_prompt_pro_ab_button_b', true ),
			)
		);

		echo '</div>';

		// Scheduling
		echo '<div class="options_group">';
		echo '<h4>' . __( 'Scheduling', 'coupon-prompt' ) . '</h4>';

		// Schedule start date
		woocommerce_wp_text_input(
			array(
				'id'          => 'coupon_prompt_pro_schedule_start',
				'label'       => __( 'Schedule Start Date', 'coupon-prompt' ),
				'description' => __( 'Start showing this prompt from this date.', 'coupon-prompt' ),
				'desc_tip'    => true,
				'type'        => 'datetime-local',
				'value'       => get_post_meta( $coupon_id, 'coupon_prompt_pro_schedule_start', true ),
			)
		);

		// Schedule end date
		woocommerce_wp_text_input(
			array(
				'id'          => 'coupon_prompt_pro_schedule_end',
				'label'       => __( 'Schedule End Date', 'coupon-prompt' ),
				'description' => __( 'Stop showing this prompt after this date.', 'coupon-prompt' ),
				'desc_tip'    => true,
				'type'        => 'datetime-local',
				'value'       => get_post_meta( $coupon_id, 'coupon_prompt_pro_schedule_end', true ),
			)
		);

		echo '</div>';

		echo '</div>';

		// Add some Pro styling
		?>
		<style>
		.coupon-prompt-pro-fields {
			background: linear-gradient(135deg, #f0fdf9 0%, #f0f9ff 100%);
			border: 1px solid #00d4aa;
			border-radius: 8px;
			padding: 20px;
			margin: 20px 0;
		}

		.coupon-prompt-pro-fields h3 {
			margin-top: 0;
			border-bottom: 2px solid #00d4aa;
			padding-bottom: 10px;
		}

		.coupon-prompt-pro-fields .options_group {
			background: rgba(255, 255, 255, 0.7);
			border-radius: 4px;
			padding: 15px;
			margin-bottom: 15px;
		}

		.coupon-prompt-pro-fields h4 {
			color: #0369a1;
			margin-top: 0;
		}
		</style>
		<?php
	}

	public static function save_pro_coupon_fields( $post_id, $coupon ) {
		// Save Pro fields
		$pro_fields = array(
			'coupon_prompt_pro_priority'        => 'absint',
			'coupon_prompt_pro_min_cart_value'  => 'sanitize_text_field',
			'coupon_prompt_pro_max_cart_value'  => 'sanitize_text_field',
			'coupon_prompt_pro_first_time_only' => 'checkbox',
			'coupon_prompt_pro_ab_testing'      => 'checkbox',
			'coupon_prompt_pro_ab_message_b'    => 'sanitize_text_field',
			'coupon_prompt_pro_ab_button_b'     => 'sanitize_text_field',
			'coupon_prompt_pro_schedule_start'  => 'sanitize_text_field',
			'coupon_prompt_pro_schedule_end'    => 'sanitize_text_field',
		);

		foreach ( $pro_fields as $field => $sanitize_function ) {
			if ( $sanitize_function === 'checkbox' ) {
				$value = isset( $_POST[ $field ] ) ? 'yes' : '';
			} elseif ( $sanitize_function === 'absint' ) {
				$value = isset( $_POST[ $field ] ) ? absint( $_POST[ $field ] ) : '';
			} else {
				$value = isset( $_POST[ $field ] ) ? $sanitize_function( $_POST[ $field ] ) : '';
			}

			update_post_meta( $post_id, $field, $value );
		}
	}

	public static function add_pro_admin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Coupon Analytics', 'coupon-prompt' ),
			__( 'Coupon Analytics', 'coupon-prompt' ),
			'manage_woocommerce',
			'coupon-prompt-analytics',
			array( __CLASS__, 'analytics_page' )
		);

		add_submenu_page(
			'woocommerce',
			__( 'Coupon Prompt Settings', 'coupon-prompt' ),
			__( 'Coupon Settings', 'coupon-prompt' ),
			'manage_woocommerce',
			'coupon-prompt-settings',
			array( __CLASS__, 'settings_page' )
		);
	}

	public static function analytics_page() {
		$analytics = Coupon_Prompt_Pro_Analytics::get_analytics_data();
		include COUPON_PROMPT_PRO_DIR . 'templates/analytics-page.php';
	}

	public static function settings_page() {
		if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['settings_nonce'], 'coupon_prompt_pro_settings' ) ) {
			Coupon_Prompt_Pro_Settings::save_settings( $_POST );
			echo '<div class="notice notice-success"><p>' . __( 'Settings saved!', 'coupon-prompt' ) . '</p></div>';
		}

		$settings = Coupon_Prompt_Pro_Settings::get_settings();
		include COUPON_PROMPT_PRO_DIR . 'templates/settings-page.php';
	}

	public static function enqueue_admin_scripts( $hook ) {
		$pro_pages   = array( 'coupon-prompt', 'post.php', 'post-new.php' );
		$is_pro_page = false;

		foreach ( $pro_pages as $page ) {
			if ( strpos( $hook, $page ) !== false ) {
				$is_pro_page = true;
				break;
			}
		}

		if ( ! $is_pro_page ) {
			return;
		}

		wp_enqueue_script( 'coupon-prompt-pro-admin', COUPON_PROMPT_PRO_URL . 'assets/admin.js', array( 'jquery' ), COUPON_PROMPT_VERSION );
		wp_enqueue_style( 'coupon-prompt-pro-admin', COUPON_PROMPT_PRO_URL . 'assets/admin.css', array(), COUPON_PROMPT_VERSION );

		// Chart.js for analytics
		if ( strpos( $hook, 'coupon-prompt-analytics' ) !== false ) {
			wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1' );
		}
	}

	public static function add_coupon_columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;

			if ( $key === 'coupon_code' ) {
				$new_columns['coupon_prompt_pro'] = '<span style="color: #00d4aa;">⚡ Pro</span>';
			}
		}

		return $new_columns;
	}

	public static function coupon_column_content( $column, $post_id ) {
		if ( $column === 'coupon_prompt_pro' ) {
			$has_pro_features = false;

			$pro_fields = array(
				'coupon_prompt_pro_priority',
				'coupon_prompt_pro_min_cart_value',
				'coupon_prompt_pro_max_cart_value',
				'coupon_prompt_pro_first_time_only',
				'coupon_prompt_pro_ab_testing',
				'coupon_prompt_pro_schedule_start',
				'coupon_prompt_pro_schedule_end',
			);

			foreach ( $pro_fields as $field ) {
				if ( get_post_meta( $post_id, $field, true ) ) {
					$has_pro_features = true;
					break;
				}
			}

			if ( $has_pro_features ) {
				echo '<span style="color: #00d4aa; font-weight: bold;">✓ Active</span>';
			} else {
				echo '<span style="color: #666;">—</span>';
			}
		}
	}
}
