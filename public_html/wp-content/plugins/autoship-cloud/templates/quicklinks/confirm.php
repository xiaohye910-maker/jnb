<?php
/**
 * QuickLink Confirmation Template.
 *
 * Displays the two-step confirmation page before executing a QuickLink action.
 * This template can be overridden by copying it to:
 * yourtheme/autoship/quicklinks/confirm.php
 *
 * @package Autoship
 * @since   3.2.0
 *
 * @var \Autoship\Domain\QuickLinks\QuickLinkConfirmation $confirmation          The confirmation object.
 * @var string                                            $nonce                 The nonce for form submission.
 * @var string                                            $confirm_url           The confirmation URL.
 * @var string                                            $cancel_url            The cancellation URL.
 * @var string                                            $action_name           The human-readable action name (e.g., "Pause Subscription").
 * @var string|null                                       $action_verb           The action verb for sentences (e.g., "pause").
 * @var int                                               $order_id              The scheduled order ID.
 * @var string                                            $site_name             The site name.
 * @var string|null                                       $order_customized_name Custom order name.
 * @var string|null                                       $custom_logo_url       Custom logo URL.
 * @var string|null                                       $custom_stylesheet_url Custom stylesheet URL.
 * @var array|null                                        $order_summary_data    Order summary data array.
 * @var array                                             $custom_meta_tags      Custom meta tags array.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Use action_verb for sentences if available, fallback to action_name.
// action_verb is properly translated (e.g., "pause", "resume", "reactivate").
$sentence_action_verb = $action_verb ?? strtolower( $action_name );

// Display action name for the details section (e.g., "Pause Subscription").
$display_action_name = $action_name;

// Get custom branding - now passed directly from controller.
$has_custom_logo   = ! empty( $custom_logo_url );
$custom_stylesheet = $custom_stylesheet_url ?? '';
$order_name        = ! empty( $order_customized_name ) ? $order_customized_name : __( 'subscription', 'autoship' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<?php // translators: %s: site name. ?>
	<title><?php echo esc_html( sprintf( __( 'Confirm Action - %s', 'autoship' ), $site_name ) ); ?></title>

	<?php // Custom meta tags from API. ?>
	<?php if ( ! empty( $custom_meta_tags ) ) : ?>
		<?php foreach ( $custom_meta_tags as $meta_tag ) : ?>
			<?php if ( ! empty( $meta_tag['key'] ) ) : ?>
				<?php if ( 'property' === ( $meta_tag['type'] ?? '' ) ) : ?>
					<meta property="<?php echo esc_attr( $meta_tag['key'] ); ?>" content="<?php echo esc_attr( $meta_tag['content'] ?? '' ); ?>">
				<?php else : ?>
					<meta name="<?php echo esc_attr( $meta_tag['key'] ); ?>" content="<?php echo esc_attr( $meta_tag['content'] ?? '' ); ?>">
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php // Custom stylesheet from API. ?>
	<?php if ( ! empty( $custom_stylesheet ) ) : ?>
		<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- Standalone page without WordPress head. ?>
		<link rel="stylesheet" href="<?php echo esc_url( $custom_stylesheet ); ?>">
	<?php endif; ?>

	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		html, body {
			min-height: 100vh;
			background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
		}

		body {
			font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			color: #1f2937;
			line-height: 1.5;
			padding: 1.25rem;
			display: flex;
			align-items: center;
			justify-content: center;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
		}

		.confirmation-card {
			max-width: 600px;
			width: 100%;
			background: white;
			border-radius: 8px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
			overflow: hidden;
			padding-bottom: 2rem;
            text-align: center;
		}

		/* Content */
		.card-content {
			padding: 2rem 2rem 2rem;
		}

		.action-message {
			font-size: 1rem;
			color: #4b5563;
			margin-bottom: 1.5rem;
			text-align: center;
			line-height: 1.6;
		}

		.action-message strong {
			color: #1f2937;
			font-weight: 600;
		}

		/* Action Details Box */
		.details-box {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 6px;
			padding: 1.25rem 1.5rem;
			margin-bottom: 1.5rem;
		}

		.details-header {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			margin-bottom: 12px;
			font-size: 0.875rem;
			font-weight: 600;
			color: #374151;
			text-transform: uppercase;
			letter-spacing: 0.05em;
		}

		.details-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 1.25rem;
		}

		.detail-item {
			display: flex;
			flex-direction: column;
			gap: 0.25rem;
			align-items: center;
			text-align: center;
		}

		.detail-label {
			font-size: 0.75rem;
			font-weight: 600;
			color: #64748b;
			text-transform: uppercase;
			letter-spacing: 0.05em;
		}

		.detail-value {
			font-size: 0.9375rem;
			font-weight: 500;
			color: #1f2937;
		}

		/* Order Summary */
		.order-summary {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 6px;
			padding: 1.25rem 1.5rem;
			margin-bottom: 1.5rem;
		}

		.order-summary-header {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0.5rem;
			margin-bottom: 1rem;
			font-size: 0.875rem;
			font-weight: 600;
			color: #374151;
			text-transform: uppercase;
			letter-spacing: 0.05em;
		}

		.order-items {
			margin-bottom: 1rem;
			padding-bottom: 1rem;
			border-bottom: 1px solid #e2e8f0;
		}

		.order-item {
			display: flex;
			align-items: center;
			gap: 0.75rem;
			padding: 0.75rem 0;
			font-size: 0.9375rem;
		}

		.item-image {
			width: 50px;
			height: 50px;
			object-fit: cover;
			border-radius: 4px;
			border: 1px solid #e2e8f0;
			flex-shrink: 0;
		}

		.item-image-placeholder {
			display: flex;
			align-items: center;
			justify-content: center;
			background: #f1f5f9;
		}

		.item-image-placeholder svg {
			width: 24px;
			height: 24px;
			color: #94a3b8;
		}

		.item-name {
			color: #4b5563;
			flex: 1;
            text-align: left;
		}

		.item-quantity {
			color: #64748b;
			margin: 0 0.75rem;
			font-size: 0.875rem;
			flex-shrink: 0;
		}

		.item-price {
			color: #1f2937;
			font-weight: 500;
			flex-shrink: 0;
		}

		.order-totals {
			display: flex;
			flex-direction: column;
			gap: 0.5rem;
		}

		.total-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 0.9375rem;
		}

		.total-label {
			color: #64748b;
		}

		.total-value {
			color: #1f2937;
			font-weight: 500;
		}

		.total-row.grand-total {
			margin-top: 0.5rem;
			padding-top: 0.75rem;
			border-top: 1px solid #e2e8f0;
			font-size: 1rem;
			font-weight: 600;
		}

		.total-row.grand-total .total-label {
			color: #1f2937;
		}

		.total-row.grand-total .total-value {
			color: #2563eb;
		}

		.total-row.discount .total-value {
			color: #16a34a;
		}

		/* Info Notice */
		.info-notice {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 6px;
			padding: 0.75rem 1rem;
			text-align: center;
			margin-bottom: 1.5rem;
		}

		.info-text {
			font-size: 0.875rem;
			color: #4b5563;
			line-height: 1.5;
		}

		/* Logo Section */
		.merchant-logo-section {
			margin-bottom: 2rem;
		}

		.merchant-logo {
			max-width: 200px;
			height: auto;
            margin: 0 auto;
		}

		.merchant-name {
			font-size: 0.875rem;
			color: #64748b;
			font-weight: 500;
		}

		.merchant-name-large {
			font-size: 1.5rem;
			color: #1f2937;
			font-weight: 600;
		}

		/* Footer Actions */
		.footer-actions {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 1rem;
			margin-top: 1.5rem;
		}

		.footer-actions form {
			display: contents;
		}

		.footer-actions .btn {
			min-width: 160px;
		}

		.cancel-link {
			font-size: 0.875rem;
			color: #6b7280;
			text-decoration: none;
			transition: color 0.2s;
		}

		.cancel-link:hover {
			color: #4b5563;
			text-decoration: underline;
		}

		/* Buttons */
		.btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 0.95rem 1.8rem;
			font-size: 1.1rem;
			font-weight: 600;
			text-decoration: none;
			border-radius: 8px;
			cursor: pointer;
			transition: all 0.2s ease;
			border: 2px solid transparent;
			min-width: 140px;
			font-family: inherit;
		}

		.btn:focus {
			outline: none;
			box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
		}

		.btn-primary {
			background-color: #2563eb;
			color: #ffffff;
			border: 2px solid #2563eb;
		}

		.btn-primary:hover {
			background-color: #1d4ed8;
			border-color: #1d4ed8;
			color: #ffffff;
		}

		.btn-primary:active {
			background-color: #1e40af;
			border-color: #1e40af;
		}

		.btn-secondary {
			background-color: #ffffff;
			color: #4b5563;
			border-color: #d1d5db;
		}

		.btn-secondary:hover {
			background-color: #f9fafb;
			border-color: #9ca3af;
			color: #374151;
		}

		.btn-secondary:active {
			background-color: #f3f4f6;
		}

		/* Powered By Section */
		.powered-by {
			text-align: center;
			padding: 1.5rem 1rem;
			background: #ffffff;
		}

		.powered-by-text {
			font-size: 0.6875rem;
			color: #9ca3af;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			margin-bottom: 0.5rem;
		}

		.powered-by-logo {
			display: inline-block;
		}

		.powered-by-logo svg {
			width: 100px;
			height: auto;
		}

		/* Mobile Responsive */
		@media (max-width: 640px) {
			body {
				padding: 1rem;
			}

			.card-content {
				padding: 1.5rem 1rem;
			}

			.merchant-logo-section {
				margin-bottom: 1.5rem;
			}

			.merchant-logo {
				max-width: 150px;
			}

			.details-grid {
				grid-template-columns: 1fr;
				gap: 0.875rem;
			}

			.btn {
				width: 100%;
				justify-content: center;
				padding: 0.75rem 1.5rem;
			}

			.powered-by {
				padding: 1.25rem 1rem;
			}

			.powered-by-logo svg {
				width: 80px;
			}
		}
	</style>
	<?php wp_head(); ?>
</head>
<body class="autoship-quicklink-page">
	<div class="confirmation-card">
		<!-- Content -->
		<div class="card-content">
			<!-- Merchant Logo/Name -->
			<div class="merchant-logo-section">
				<?php if ( $has_custom_logo ) : ?>
					<!-- Store has custom logo: show logo with name below -->
					<img src="<?php echo esc_url( $custom_logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" class="merchant-logo">
					<div class="merchant-name"><?php echo esc_html( $site_name ); ?></div>
				<?php else : ?>
					<?php
					// Check for theme custom logo.
					$custom_logo_id = get_theme_mod( 'custom_logo' );
					if ( $custom_logo_id ) :
						$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'medium' );
						?>
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" class="merchant-logo">
						<div class="merchant-name"><?php echo esc_html( $site_name ); ?></div>
					<?php else : ?>
						<!-- No custom logo: show store name in larger font -->
						<div class="merchant-name-large"><?php echo esc_html( $site_name ); ?></div>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<!-- Action Message -->
			<p class="action-message">
				<?php
				printf(
					/* translators: 1: action verb (e.g., "pause"), 2: order name (e.g., "subscription") */
					esc_html__( 'You are about to %1$s your %2$s.', 'autoship' ),
					'<strong>' . esc_html( $sentence_action_verb ) . '</strong>',
					esc_html( strtolower( $order_name ) )
				);
				?>
				<br>
				<?php esc_html_e( 'Please confirm to proceed.', 'autoship' ); ?>
			</p>

			<!-- Action Details -->
			<div class="details-box">
				<div class="details-header">
					<?php esc_html_e( 'Action Details', 'autoship' ); ?>
				</div>
				<div class="details-grid">
					<div class="detail-item">
						<span class="detail-label">
							<?php
							echo esc_html(
								strtoupper(
									! empty( $order_customized_name )
										? $order_customized_name
										: __( 'Scheduled Order', 'autoship' )
								) . ' ID'
							);
							?>
						</span>
						<span class="detail-value">#<?php echo esc_html( $order_id ); ?></span>
					</div>
					<div class="detail-item">
						<span class="detail-label"><?php esc_html_e( 'Action', 'autoship' ); ?></span>
						<span class="detail-value"><?php echo esc_html( $display_action_name ); ?></span>
					</div>
				</div>
			</div>

			<?php if ( ! empty( $order_summary_data ) && ! empty( $order_summary_data['items'] ) ) : ?>
				<?php
				$symbol = $order_summary_data['currency_symbol'] ?? '$';
				?>
				<!-- Order Summary -->
				<div class="order-summary">
					<div class="order-summary-header">
						<?php esc_html_e( 'Order Summary', 'autoship' ); ?>
					</div>

					<div class="order-items">
						<?php foreach ( $order_summary_data['items'] as $item ) : ?>
							<div class="order-item">
								<?php
								$image_url = $item['image_url'] ?? '';
								if ( ! empty( $image_url ) ) :
									?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $item['title'] ?? '' ); ?>" class="item-image">
								<?php else : ?>
									<div class="item-image item-image-placeholder">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
											<path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
										</svg>
									</div>
								<?php endif; ?>
								<span class="item-name"><?php echo esc_html( $item['title'] ?? '' ); ?></span>
								<span class="item-quantity">&times; <?php echo esc_html( $item['quantity'] ?? 0 ); ?></span>
								<span class="item-price"><?php echo esc_html( $symbol . number_format( $item['total'] ?? 0, 2 ) ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="order-totals">
						<div class="total-row">
							<span class="total-label"><?php esc_html_e( 'Subtotal', 'autoship' ); ?></span>
							<span class="total-value"><?php echo esc_html( $symbol . number_format( $order_summary_data['subtotal'] ?? 0, 2 ) ); ?></span>
						</div>
						<?php if ( ! empty( $order_summary_data['discounts'] ) && $order_summary_data['discounts'] > 0 ) : ?>
							<div class="total-row discount">
								<span class="total-label"><?php esc_html_e( 'Discounts', 'autoship' ); ?></span>
								<span class="total-value">-<?php echo esc_html( $symbol . number_format( $order_summary_data['discounts'], 2 ) ); ?></span>
							</div>
						<?php endif; ?>
						<div class="total-row">
							<span class="total-label"><?php esc_html_e( 'Shipping', 'autoship' ); ?></span>
							<span class="total-value"><?php echo esc_html( $symbol . number_format( $order_summary_data['shipping'] ?? 0, 2 ) ); ?></span>
						</div>
						<?php if ( ! empty( $order_summary_data['tax'] ) && $order_summary_data['tax'] > 0 ) : ?>
							<div class="total-row">
								<span class="total-label"><?php esc_html_e( 'Tax', 'autoship' ); ?></span>
								<span class="total-value"><?php echo esc_html( $symbol . number_format( $order_summary_data['tax'], 2 ) ); ?></span>
							</div>
						<?php endif; ?>
						<div class="total-row grand-total">
							<span class="total-label"><?php esc_html_e( 'Total', 'autoship' ); ?></span>
							<span class="total-value"><?php echo esc_html( $symbol . number_format( $order_summary_data['total'] ?? 0, 2 ) ); ?></span>
						</div>
					</div>
				</div>
			<?php else : ?>
				<!-- Info Notice when no order summary -->
				<div class="info-notice">
					<p class="info-text"><?php esc_html_e( 'Review your subscription details before confirming.', 'autoship' ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Action Buttons -->
			<div class="footer-actions">
				<form method="post" action="<?php echo esc_url( $confirm_url ); ?>">
					<?php wp_nonce_field( \Autoship\Services\QuickLinks\Confirmation\QuickLinkConfirmationService::NONCE_ACTION_PREFIX . $confirmation->get_uuid() ); ?>
					<button type="submit" class="btn btn-primary">
						<?php esc_html_e( 'Confirm Action', 'autoship' ); ?>
					</button>
				</form>
				<a href="<?php echo esc_url( $cancel_url ); ?>" class="cancel-link">
					<?php esc_html_e( 'Cancel', 'autoship' ); ?>
				</a>
			</div>
		</div>

		<!-- Powered By -->
		<?php require __DIR__ . '/partials/powered-by.php'; ?>
	</div>

	<?php wp_footer(); ?>
</body>
</html>
