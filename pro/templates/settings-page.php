<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><span style="color: #00d4aa;">⚡</span> <?php _e( 'Coupon Prompt Pro Settings', 'coupon-prompt' ); ?></h1>

	<form method="post" action="">
		<?php wp_nonce_field( 'coupon_prompt_pro_settings', 'settings_nonce' ); ?>

		<div class="coupon-settings-tabs">
			<nav class="nav-tab-wrapper">
				<a href="#general" class="nav-tab nav-tab-active"><?php _e( 'General', 'coupon-prompt' ); ?></a>
				<a href="#display" class="nav-tab"><?php _e( 'Display', 'coupon-prompt' ); ?></a>
				<a href="#analytics" class="nav-tab"><?php _e( 'Analytics', 'coupon-prompt' ); ?></a>
				<a href="#advanced" class="nav-tab"><?php _e( 'Advanced', 'coupon-prompt' ); ?></a>
			</nav>

			<!-- General Tab -->
			<div id="general" class="tab-content">
				<h2><?php _e( 'General Settings', 'coupon-prompt' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Enable Guest Prompts', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="guest_prompts_enabled" value="1"
										<?php checked( $settings['guest_prompts_enabled'] ?? false ); ?> />
								<?php _e( 'Show coupon prompts to guest users', 'coupon-prompt' ); ?>
							</label>
							<p class="description">
								<?php _e( 'By default, only logged-in users see coupon prompts.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Max Prompts Per Page', 'coupon-prompt' ); ?></th>
						<td>
							<input type="number" name="max_prompts_per_page"
									value="<?php echo esc_attr( $settings['max_prompts_per_page'] ?? 3 ); ?>"
									min="1" max="10" class="small-text" />
							<p class="description">
								<?php _e( 'Maximum number of coupon prompts to show on a single page.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Enable Scheduling', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="enable_scheduling" value="1"
										<?php checked( $settings['enable_scheduling'] ?? true ); ?> />
								<?php _e( 'Allow scheduling of coupon prompts', 'coupon-prompt' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Enable Targeting', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="enable_targeting" value="1"
										<?php checked( $settings['enable_targeting'] ?? true ); ?> />
								<?php _e( 'Allow advanced targeting rules', 'coupon-prompt' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Best Deal Logic', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="best_deal_logic" value="1"
										<?php checked( $settings['best_deal_logic'] ?? false ); ?> />
								<?php _e( 'Only show the best available coupon', 'coupon-prompt' ); ?>
							</label>
							<p class="description">
								<?php _e( 'When enabled, only the coupon with the highest savings will be displayed.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Display Tab -->
			<div id="display" class="tab-content" style="display: none;">
				<h2><?php _e( 'Display Settings', 'coupon-prompt' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Priority Sorting', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="priority_sorting" value="1"
										<?php checked( $settings['priority_sorting'] ?? true ); ?> />
								<?php _e( 'Sort prompts by priority', 'coupon-prompt' ); ?>
							</label>
							<p class="description">
								<?php _e( 'Higher priority coupons will be shown first.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Enable Shortcodes', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="enable_shortcodes" value="1"
										<?php checked( $settings['enable_shortcodes'] ?? true ); ?> />
								<?php _e( 'Enable [coupon_prompt] shortcode', 'coupon-prompt' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Enable Widgets', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="enable_widgets" value="1"
										<?php checked( $settings['enable_widgets'] ?? true ); ?> />
								<?php _e( 'Enable Coupon Prompt widget', 'coupon-prompt' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Custom CSS', 'coupon-prompt' ); ?></th>
						<td>
							<textarea name="custom_css" rows="8" cols="80" class="large-text code"><?php echo esc_textarea( $settings['custom_css'] ?? '' ); ?></textarea>
							<p class="description">
								<?php _e( 'Add custom CSS for all coupon prompts.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Analytics Tab -->
			<div id="analytics" class="tab-content" style="display: none;">
				<h2><?php _e( 'Analytics Settings', 'coupon-prompt' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Enable Analytics', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="analytics_enabled" value="1"
										<?php checked( $settings['analytics_enabled'] ?? true ); ?> />
								<?php _e( 'Track coupon prompt performance', 'coupon-prompt' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Enable A/B Testing', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="ab_testing_enabled" value="1"
										<?php checked( $settings['ab_testing_enabled'] ?? true ); ?> />
								<?php _e( 'Allow A/B testing of coupon prompts', 'coupon-prompt' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Data Retention', 'coupon-prompt' ); ?></th>
						<td>
							<select name="cleanup_analytics_days">
								<?php
								$retention_options = array(
									30  => __( '30 days', 'coupon-prompt' ),
									90  => __( '90 days', 'coupon-prompt' ),
									180 => __( '6 months', 'coupon-prompt' ),
									365 => __( '1 year', 'coupon-prompt' ),
									730 => __( '2 years', 'coupon-prompt' ),
								);

								$current_retention = $settings['cleanup_analytics_days'] ?? 365;

								foreach ( $retention_options as $days => $label ) {
									echo '<option value="' . esc_attr( $days ) . '" ' . selected( $current_retention, $days, false ) . '>' . esc_html( $label ) . '</option>';
								}
								?>
							</select>
							<p class="description">
								<?php _e( 'How long to keep analytics data before automatic cleanup.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Email Notifications', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="email_notifications" value="1"
										<?php checked( $settings['email_notifications'] ?? false ); ?> />
								<?php _e( 'Send email notifications for scheduling events', 'coupon-prompt' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Notification Email', 'coupon-prompt' ); ?></th>
						<td>
							<input type="email" name="notification_email"
									value="<?php echo esc_attr( $settings['notification_email'] ?? get_option( 'admin_email' ) ); ?>"
									class="regular-text" />
							<p class="description">
								<?php _e( 'Email address for notifications.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Advanced Tab -->
			<div id="advanced" class="tab-content" style="display: none;">
				<h2><?php _e( 'Advanced Settings', 'coupon-prompt' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Debug Mode', 'coupon-prompt' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="debug_mode" value="1"
										<?php checked( $settings['debug_mode'] ?? false ); ?> />
								<?php _e( 'Enable debug logging and indicators', 'coupon-prompt' ); ?>
							</label>
							<p class="description">
								<?php _e( 'Shows Pro badge on frontend and enables detailed logging.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Cache Duration', 'coupon-prompt' ); ?></th>
						<td>
							<select name="cache_duration">
								<?php
								$cache_options = array(
									0    => __( 'No caching', 'coupon-prompt' ),
									300  => __( '5 minutes', 'coupon-prompt' ),
									600  => __( '10 minutes', 'coupon-prompt' ),
									1800 => __( '30 minutes', 'coupon-prompt' ),
									3600 => __( '1 hour', 'coupon-prompt' ),
								);

								$current_cache = $settings['cache_duration'] ?? 0;

								foreach ( $cache_options as $seconds => $label ) {
									echo '<option value="' . esc_attr( $seconds ) . '" ' . selected( $current_cache, $seconds, false ) . '>' . esc_html( $label ) . '</option>';
								}
								?>
							</select>
							<p class="description">
								<?php _e( 'Cache eligibility checks to improve performance.', 'coupon-prompt' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<h3><?php _e( 'Pro Features Status', 'coupon-prompt' ); ?></h3>
				<div class="pro-status-display">
					<?php
					$pro_status = coupon_prompt_get_pro_status();
					?>
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e( 'Pro Version', 'coupon-prompt' ); ?></th>
							<td>
								<span class="status-indicator <?php echo $pro_status['available'] ? 'active' : 'inactive'; ?>">
									<?php echo $pro_status['available'] ? '✅ Available' : '❌ Not Available'; ?>
								</span>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Pro Status', 'coupon-prompt' ); ?></th>
							<td>
								<span class="status-indicator <?php echo $pro_status['active'] ? 'active' : 'inactive'; ?>">
									<?php echo $pro_status['active'] ? '✅ Active' : '❌ Inactive'; ?>
								</span>
								<?php if ( ! $pro_status['active'] ) : ?>
									<br><small><?php _e( 'Pro features are automatically active when the pro folder exists in your installation.', 'coupon-prompt' ); ?></small>
									</a>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<?php submit_button(); ?>
	</form>
</div>

<style>
.coupon-settings-tabs .nav-tab-wrapper {
	border-bottom: 1px solid #c3c4c7;
	margin-bottom: 20px;
}

.tab-content {
	background: #fff;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	padding: 20px;
	box-shadow: 0 1px 3px rgba(0,0,0,.1);
}

.pro-status-display {
	background: linear-gradient(135deg, #f0fdf9 0%, #f0f9ff 100%);
	border: 1px solid #00d4aa;
	border-radius: 8px;
	padding: 20px;
}

.status-indicator {
	font-weight: bold;
	padding: 5px 10px;
	border-radius: 4px;
	display: inline-block;
}

.status-indicator.active {
	background: #d1fae5;
	color: #065f46;
}

.status-indicator.inactive {
	background: #fee2e2;
	color: #991b1b;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Tab functionality
	$('.nav-tab').click(function(e) {
		e.preventDefault();

		// Update active tab
		$('.nav-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');

		// Show corresponding content
		$('.tab-content').hide();
		var target = $(this).attr('href');
		$(target).show();
	});
});
</script>
