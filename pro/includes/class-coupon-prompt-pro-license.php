<?php
// License management for Coupon Prompt Pro
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coupon_Prompt_Pro_License {

	private static $license_server_url = 'https://api.crafely.com/';
	private static $product_id         = 'coupon-prompt-pro';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_license_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_license_actions' ) );
		add_action( 'coupon_prompt_pro_check_license', array( __CLASS__, 'check_license_status' ) );

		// Schedule daily license check
		if ( ! wp_next_scheduled( 'coupon_prompt_pro_check_license' ) ) {
			wp_schedule_event( time(), 'daily', 'coupon_prompt_pro_check_license' );
		}

		// Add Pro indicator to admin
		add_action( 'admin_notices', array( __CLASS__, 'show_pro_status' ) );
	}

	public static function add_license_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Coupon Prompt Pro License', 'coupon-prompt' ),
			__( 'Coupon Prompt Pro', 'coupon-prompt' ),
			'manage_woocommerce',
			'coupon-prompt-pro-license',
			array( __CLASS__, 'license_page' )
		);
	}

	public static function license_page() {
		$license_key    = get_option( 'coupon_prompt_pro_license_key', '' );
		$license_status = get_option( 'coupon_prompt_pro_license_status', 'inactive' );

		?>
		<div class="wrap">
			<h1><?php _e( 'Coupon Prompt Pro License', 'coupon-prompt' ); ?></h1>

			<?php if ( isset( $_GET['message'] ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( $_GET['type'] ?? 'info' ); ?>">
					<p><?php echo esc_html( urldecode( $_GET['message'] ) ); ?></p>
				</div>
			<?php endif; ?>

			<div class="coupon-prompt-pro-info">
				<h2><?php _e( 'Pro Features Available', 'coupon-prompt' ); ?></h2>
				<ul>
					<li>✅ <?php _e( 'Advanced Targeting (User roles, cart value, purchase history)', 'coupon-prompt' ); ?></li>
					<li>✅ <?php _e( 'Analytics & Conversion Tracking', 'coupon-prompt' ); ?></li>
					<li>✅ <?php _e( 'A/B Testing for Coupon Messages', 'coupon-prompt' ); ?></li>
					<li>✅ <?php _e( 'Scheduling & Automation', 'coupon-prompt' ); ?></li>
					<li>✅ <?php _e( 'Custom Placement & Shortcodes', 'coupon-prompt' ); ?></li>
					<li>✅ <?php _e( 'Priority Support', 'coupon-prompt' ); ?></li>
				</ul>
			</div>

			<form method="post" action="">
				<?php wp_nonce_field( 'coupon_prompt_pro_license', 'license_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'License Key', 'coupon-prompt' ); ?></th>
						<td>
							<input type="text" name="license_key" value="<?php echo esc_attr( $license_key ); ?>"
									class="regular-text" placeholder="XXXX-XXXX-XXXX-XXXX" />
							<p class="description">
								<?php _e( 'Enter your license key to activate Coupon Prompt Pro features.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'License Status', 'coupon-prompt' ); ?></th>
						<td>
							<span class="license-status license-<?php echo esc_attr( $license_status ); ?>">
								<?php echo esc_html( ucfirst( $license_status ) ); ?>
							</span>
							<?php if ( $license_status === 'active' ) : ?>
								<span style="color: green; font-size: 16px;">✓</span>
							<?php else : ?>
								<span style="color: red; font-size: 16px;">✗</span>
							<?php endif; ?>
						</td>
					</tr>
				</table>

				<?php if ( $license_status === 'active' ) : ?>
					<p class="submit">
						<input type="submit" name="deactivate_license" class="button-secondary"
								value="<?php _e( 'Deactivate License', 'coupon-prompt' ); ?>" />
					</p>
				<?php else : ?>
					<p class="submit">
						<input type="submit" name="activate_license" class="button-primary"
								value="<?php _e( 'Activate License', 'coupon-prompt' ); ?>" />
					</p>
				<?php endif; ?>
			</form>

			<?php if ( $license_status !== 'active' ) : ?>
				<div class="coupon-prompt-demo-notice">
					<h3><?php _e( 'Demo Mode Active', 'coupon-prompt' ); ?></h3>
					<p><?php _e( 'Pro features are available in demo mode for testing. Enter a valid license key for full functionality.', 'coupon-prompt' ); ?></p>
				</div>
			<?php endif; ?>

			<style>
				.coupon-prompt-pro-info {
					background: #f0f9ff;
					border: 1px solid #0ea5e9;
					border-radius: 8px;
					padding: 20px;
					margin-bottom: 20px;
				}

				.coupon-prompt-pro-info h2 {
					color: #0369a1;
					margin-top: 0;
				}

				.coupon-prompt-pro-info ul {
					margin: 10px 0;
					padding-left: 20px;
				}

				.coupon-prompt-pro-info li {
					margin-bottom: 5px;
					color: #1e40af;
				}

				.license-status {
					font-weight: bold;
					padding: 8px 12px;
					border-radius: 4px;
					display: inline-block;
				}

				.license-active {
					background: #d1fae5;
					color: #065f46;
					border: 1px solid #10b981;
				}

				.license-inactive, .license-invalid {
					background: #fee2e2;
					color: #991b1b;
					border: 1px solid #ef4444;
				}

				.coupon-prompt-demo-notice {
					background: #fef3c7;
					border: 1px solid #f59e0b;
					border-radius: 4px;
					padding: 15px;
					margin-top: 20px;
				}

				.coupon-prompt-demo-notice h3 {
					color: #92400e;
					margin-top: 0;
				}
			</style>
		</div>
		<?php
	}

	public static function handle_license_actions() {
		if ( ! isset( $_POST['license_nonce'] ) || ! wp_verify_nonce( $_POST['license_nonce'], 'coupon_prompt_pro_license' ) ) {
			return;
		}

		if ( isset( $_POST['activate_license'] ) ) {
			$license_key = sanitize_text_field( $_POST['license_key'] );
			$result      = self::activate_license( $license_key );

			if ( $result['success'] ) {
				update_option( 'coupon_prompt_pro_license_key', $license_key );
				update_option( 'coupon_prompt_pro_license_status', 'active' );
				$message = __( 'License activated successfully!', 'coupon-prompt' );
				$type    = 'success';
			} else {
				$message = $result['message'] ?? __( 'License activation failed.', 'coupon-prompt' );
				$type    = 'error';
			}

			wp_redirect(
				add_query_arg(
					array(
						'message' => urlencode( $message ),
						'type'    => $type,
					),
					admin_url( 'admin.php?page=coupon-prompt-pro-license' )
				)
			);
			exit;
		}

		if ( isset( $_POST['deactivate_license'] ) ) {
			$license_key = get_option( 'coupon_prompt_pro_license_key', '' );
			self::deactivate_license( $license_key );
			update_option( 'coupon_prompt_pro_license_status', 'inactive' );

			wp_redirect(
				add_query_arg(
					array(
						'message' => urlencode( __( 'License deactivated.', 'coupon-prompt' ) ),
						'type'    => 'info',
					),
					admin_url( 'admin.php?page=coupon-prompt-pro-license' )
				)
			);
			exit;
		}
	}

	public static function activate_license( $license_key ) {
		if ( empty( $license_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'Please enter a license key.', 'coupon-prompt' ),
			);
		}

		// For demo purposes, accept any key with format XXXX-XXXX-XXXX-XXXX or length >= 10
		if ( preg_match( '/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key ) || strlen( $license_key ) >= 10 ) {
			return array( 'success' => true );
		}

		return array(
			'success' => false,
			'message' => __( 'Invalid license key format. Please check your key and try again.', 'coupon-prompt' ),
		);
	}

	public static function deactivate_license( $license_key ) {
		// In production, this would make an API call to deactivate the license
		return true;
	}

	public static function check_license_status() {
		$license_key = get_option( 'coupon_prompt_pro_license_key', '' );
		if ( empty( $license_key ) ) {
			update_option( 'coupon_prompt_pro_license_status', 'inactive' );
			return false;
		}

		// For demo purposes, keep active if key exists and has proper format
		if ( preg_match( '/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key ) || strlen( $license_key ) >= 10 ) {
			update_option( 'coupon_prompt_pro_license_status', 'active' );
			return true;
		}

		update_option( 'coupon_prompt_pro_license_status', 'invalid' );
		return false;
	}

	public static function is_license_valid() {
		// In demo/development mode, always return true if Pro folder exists
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		$status = get_option( 'coupon_prompt_pro_license_status', 'inactive' );
		return $status === 'active';
	}

	public static function show_pro_status() {
		$screen = get_current_screen();
		if ( strpos( $screen->id, 'coupon' ) === false && strpos( $screen->id, 'woocommerce' ) === false ) {
			return;
		}

		if ( ! self::is_license_valid() && ! defined( 'WP_DEBUG' ) ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php _e( 'Coupon Prompt Pro', 'coupon-prompt' ); ?></strong>:
					<?php _e( 'Pro features detected but not licensed.', 'coupon-prompt' ); ?>
					<a href="<?php echo admin_url( 'admin.php?page=coupon-prompt-pro-license' ); ?>"><?php _e( 'Enter License Key', 'coupon-prompt' ); ?></a>
				</p>
			</div>
			<?php
		}
	}

	public static function get_license_info() {
		return array(
			'key'       => get_option( 'coupon_prompt_pro_license_key', '' ),
			'status'    => get_option( 'coupon_prompt_pro_license_status', 'inactive' ),
			'valid'     => self::is_license_valid(),
			'demo_mode' => defined( 'WP_DEBUG' ) && WP_DEBUG,
		);
	}
}
