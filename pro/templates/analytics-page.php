<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><span style="color: #00d4aa;">⚡</span> <?php _e( 'Coupon Prompt Analytics', 'coupon-prompt' ); ?></h1>

	<div class="coupon-analytics-dashboard">
		<!-- Summary Cards -->
		<div class="analytics-summary">
			<div class="summary-card">
				<h3><?php _e( 'Total Views', 'coupon-prompt' ); ?></h3>
				<div class="stat-number"><?php echo number_format( $analytics['stats']->total_views ?? 0 ); ?></div>
				<div class="stat-label"><?php _e( 'Last 30 days', 'coupon-prompt' ); ?></div>
			</div>

			<div class="summary-card">
				<h3><?php _e( 'Total Clicks', 'coupon-prompt' ); ?></h3>
				<div class="stat-number"><?php echo number_format( $analytics['stats']->total_clicks ?? 0 ); ?></div>
				<div class="stat-label">
					<?php printf( __( '%s%% click rate', 'coupon-prompt' ), $analytics['conversion_rate'] ?? 0 ); ?>
				</div>
			</div>

			<div class="summary-card">
				<h3><?php _e( 'Applications', 'coupon-prompt' ); ?></h3>
				<div class="stat-number"><?php echo number_format( $analytics['stats']->total_applications ?? 0 ); ?></div>
				<div class="stat-label">
					<?php printf( __( '%s%% application rate', 'coupon-prompt' ), $analytics['application_rate'] ?? 0 ); ?>
				</div>
			</div>

			<div class="summary-card">
				<h3><?php _e( 'Conversions', 'coupon-prompt' ); ?></h3>
				<div class="stat-number"><?php echo number_format( $analytics['stats']->total_conversions ?? 0 ); ?></div>
				<div class="stat-label">
					<?php echo wc_price( $analytics['stats']->total_revenue ?? 0 ); ?> <?php _e( 'revenue', 'coupon-prompt' ); ?>
				</div>
			</div>
		</div>

		<!-- Pro Features Notice -->
		<div class="pro-features-notice">
			<h3><span style="color: #00d4aa;">⚡</span> <?php _e( 'Pro Analytics Features', 'coupon-prompt' ); ?></h3>
			<p><?php _e( 'Track detailed performance metrics, A/B test results, and ROI for your coupon prompts.', 'coupon-prompt' ); ?></p>
		</div>

		<!-- Charts Section -->
		<div class="analytics-charts">
			<div class="chart-container">
				<h3><?php _e( 'Performance Over Time', 'coupon-prompt' ); ?></h3>
				<?php if ( ! empty( $analytics['daily_data'] ) ) : ?>
					<canvas id="performanceChart" width="400" height="200"></canvas>
				<?php else : ?>
					<div class="no-data-message">
						<p><?php _e( 'No analytics data available yet. Data will appear once customers interact with your coupon prompts.', 'coupon-prompt' ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<div class="chart-container">
				<h3><?php _e( 'Conversion Funnel', 'coupon-prompt' ); ?></h3>
				<?php if ( $analytics['stats']->total_views > 0 ) : ?>
					<canvas id="funnelChart" width="400" height="200"></canvas>
				<?php else : ?>
					<div class="no-data-message">
						<p><?php _e( 'Conversion funnel will appear once you have analytics data.', 'coupon-prompt' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Coupon Performance Table -->
		<div class="coupon-performance">
			<h3><?php _e( 'Top Performing Coupons', 'coupon-prompt' ); ?></h3>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Coupon Code', 'coupon-prompt' ); ?></th>
						<th><?php _e( 'Views', 'coupon-prompt' ); ?></th>
						<th><?php _e( 'Clicks', 'coupon-prompt' ); ?></th>
						<th><?php _e( 'Applications', 'coupon-prompt' ); ?></th>
						<th><?php _e( 'Conversions', 'coupon-prompt' ); ?></th>
						<th><?php _e( 'Revenue', 'coupon-prompt' ); ?></th>
						<th><?php _e( 'CTR', 'coupon-prompt' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $analytics['coupon_performance'] ) ) : ?>
						<?php foreach ( $analytics['coupon_performance'] as $performance ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $performance->coupon_code ); ?></strong></td>
								<td><?php echo number_format( $performance->views ); ?></td>
								<td><?php echo number_format( $performance->clicks ); ?></td>
								<td><?php echo number_format( $performance->applications ); ?></td>
								<td><?php echo number_format( $performance->conversions ); ?></td>
								<td><?php echo wc_price( $performance->revenue ); ?></td>
								<td>
									<?php
									$ctr = $performance->views > 0 ? round( ( $performance->clicks / $performance->views ) * 100, 2 ) : 0;
									echo $ctr . '%';
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="7" style="text-align: center; padding: 30px;">
								<div class="no-analytics-data">
									<h4><?php _e( 'No analytics data yet', 'coupon-prompt' ); ?></h4>
									<p><?php _e( 'Enable coupon prompts on your coupons and start collecting valuable performance data.', 'coupon-prompt' ); ?></p>
									<a href="<?php echo admin_url( 'edit.php?post_type=shop_coupon' ); ?>" class="button button-primary">
										<?php _e( 'Manage Coupons', 'coupon-prompt' ); ?>
									</a>
								</div>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<style>
.analytics-summary {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}

.summary-card {
	background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
	border: 1px solid #00d4aa;
	border-radius: 8px;
	padding: 20px;
	text-align: center;
	box-shadow: 0 4px 6px rgba(0, 212, 170, 0.1);
}

.summary-card h3 {
	margin: 0 0 10px 0;
	color: #1e293b;
	font-size: 14px;
	font-weight: 600;
}

.stat-number {
	font-size: 32px;
	font-weight: bold;
	color: #00d4aa;
	line-height: 1;
	margin-bottom: 5px;
}

.stat-label {
	color: #64748b;
	font-size: 13px;
}

.pro-features-notice {
	background: linear-gradient(135deg, #f0fdf9 0%, #f0f9ff 100%);
	border: 1px solid #00d4aa;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 30px;
}

.pro-features-notice h3 {
	margin-top: 0;
	color: #065f46;
}

.analytics-charts {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
	margin-bottom: 30px;
}

.chart-container {
	background: #fff;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	padding: 20px;
	box-shadow: 0 1px 3px rgba(0,0,0,.1);
}

.chart-container h3 {
	margin: 0 0 15px 0;
	color: #1e293b;
	font-size: 16px;
}

.no-data-message {
	text-align: center;
	padding: 40px 20px;
	color: #64748b;
	background: #f8fafc;
	border-radius: 4px;
	border: 1px dashed #cbd5e1;
}

.coupon-performance {
	background: #fff;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	padding: 20px;
	box-shadow: 0 1px 3px rgba(0,0,0,.1);
}

.coupon-performance h3 {
	margin: 0 0 15px 0;
	color: #1e293b;
	font-size: 16px;
}

.no-analytics-data {
	color: #64748b;
}

.no-analytics-data h4 {
	margin-bottom: 10px;
	color: #1e293b;
}

@media (max-width: 768px) {
	.analytics-charts {
		grid-template-columns: 1fr;
	}

	.analytics-summary {
		grid-template-columns: 1fr;
	}
}
</style>

<?php if ( ! empty( $analytics['daily_data'] ) || $analytics['stats']->total_views > 0 ) : ?>
<script>
jQuery(document).ready(function($) {
	<?php if ( ! empty( $analytics['daily_data'] ) ) : ?>
	// Performance Chart
	const performanceCtx = document.getElementById('performanceChart').getContext('2d');
	const dailyData = <?php echo json_encode( $analytics['daily_data'] ); ?>;

	const dates = dailyData.map(item => item.date);
	const views = dailyData.map(item => parseInt(item.views));
	const clicks = dailyData.map(item => parseInt(item.clicks));
	const conversions = dailyData.map(item => parseInt(item.conversions));

	new Chart(performanceCtx, {
		type: 'line',
		data: {
			labels: dates,
			datasets: [{
				label: '<?php _e( 'Views', 'coupon-prompt' ); ?>',
				data: views,
				borderColor: '#00d4aa',
				backgroundColor: 'rgba(0, 212, 170, 0.1)',
				tension: 0.4
			}, {
				label: '<?php _e( 'Clicks', 'coupon-prompt' ); ?>',
				data: clicks,
				borderColor: '#0ea5e9',
				backgroundColor: 'rgba(14, 165, 233, 0.1)',
				tension: 0.4
			}, {
				label: '<?php _e( 'Conversions', 'coupon-prompt' ); ?>',
				data: conversions,
				borderColor: '#f59e0b',
				backgroundColor: 'rgba(245, 158, 11, 0.1)',
				tension: 0.4
			}]
		},
		options: {
			responsive: true,
			plugins: {
				legend: {
					position: 'top',
				}
			},
			scales: {
				y: {
					beginAtZero: true
				}
			}
		}
	});
	<?php endif; ?>

	<?php if ( $analytics['stats']->total_views > 0 ) : ?>
	// Funnel Chart
	const funnelCtx = document.getElementById('funnelChart').getContext('2d');
	const totalViews = <?php echo $analytics['stats']->total_views; ?>;
	const totalClicks = <?php echo $analytics['stats']->total_clicks; ?>;
	const totalApplications = <?php echo $analytics['stats']->total_applications; ?>;
	const totalConversions = <?php echo $analytics['stats']->total_conversions; ?>;

	new Chart(funnelCtx, {
		type: 'bar',
		data: {
			labels: ['Views', 'Clicks', 'Applications', 'Conversions'],
			datasets: [{
				label: 'Count',
				data: [totalViews, totalClicks, totalApplications, totalConversions],
				backgroundColor: [
					'#00d4aa',
					'#0ea5e9',
					'#f59e0b',
					'#ef4444'
				]
			}]
		},
		options: {
			responsive: true,
			plugins: {
				legend: {
					display: false
				}
			},
			scales: {
				y: {
					beginAtZero: true
				}
			}
		}
	});
	<?php endif; ?>
});
</script>
<?php endif; ?>
