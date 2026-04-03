<?php
/**
 * QuickLink Shared Styles Partial.
 *
 * Contains all CSS for QuickLink templates.
 * This template can be overridden by copying it to:
 * yourtheme/autoship/quicklinks/partials/styles.php
 *
 * @package Autoship
 * @since   3.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
/* Reset and Base */
.autoship-ql-page {
	font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	line-height: 1.5;
	color: #1f2937;
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;
}

.autoship-ql-page *,
.autoship-ql-page *::before,
.autoship-ql-page *::after {
	box-sizing: border-box;
}

/* Container */
.autoship-ql-container {
	max-width: 480px;
	margin: 0 auto;
	padding: 40px 20px 60px;
}

@media (max-width: 640px) {
	.autoship-ql-container {
		padding: 24px 16px 40px;
	}
}

/* Card */
.autoship-ql-card {
	background: #ffffff;
	border-radius: 16px;
	padding: 40px 32px;
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
	text-align: center;
}

@media (max-width: 640px) {
	.autoship-ql-card {
		padding: 32px 20px;
		border-radius: 12px;
	}
}

/* Header */
.autoship-ql-header {
	margin-bottom: 24px;
	padding-bottom: 20px;
	border-bottom: 1px solid #e5e7eb;
}

.autoship-ql-logo {
	max-height: 48px;
	max-width: 200px;
	height: auto;
	width: auto;
}

.autoship-ql-site-name {
	font-size: 20px;
	font-weight: 600;
	color: #1f2937;
	margin: 0;
}

/* Icon */
.autoship-ql-icon {
	margin-bottom: 20px;
}

.autoship-ql-icon svg {
	display: inline-block;
}

/* Title */
.autoship-ql-title {
	font-size: 24px;
	font-weight: 700;
	color: #1f2937;
	margin: 0 0 12px 0;
}

@media (max-width: 640px) {
	.autoship-ql-title {
		font-size: 20px;
	}
}

/* Description */
.autoship-ql-description {
	font-size: 16px;
	color: #4b5563;
	margin: 0 0 24px 0;
}

.autoship-ql-description strong {
	color: #1f2937;
}

/* Message */
.autoship-ql-message {
	font-size: 16px;
	line-height: 1.6;
	color: #4b5563;
	margin: 0 0 28px 0;
}

/* Info Box */
.autoship-ql-info-box {
	background: #f9fafb;
	border: 1px solid #e5e7eb;
	border-radius: 12px;
	padding: 20px;
	margin-bottom: 20px;
	text-align: left;
}

.autoship-ql-info-box h3 {
	font-size: 14px;
	font-weight: 600;
	color: #374151;
	margin: 0 0 12px 0;
	text-transform: uppercase;
	letter-spacing: 0.05em;
}

.autoship-ql-info-box ul {
	list-style: none;
	margin: 0;
	padding: 0;
}

.autoship-ql-info-box li {
	font-size: 15px;
	color: #4b5563;
	padding: 6px 0;
	border-bottom: 1px solid #e5e7eb;
}

.autoship-ql-info-box li:last-child {
	border-bottom: none;
	padding-bottom: 0;
}

.autoship-ql-info-box li strong {
	color: #1f2937;
}

/* Help Box */
.autoship-ql-help-box {
	background: #eff6ff;
	border: 1px solid #bfdbfe;
	border-radius: 12px;
	padding: 20px;
	margin-bottom: 28px;
	text-align: left;
}

.autoship-ql-help-box h4 {
	font-size: 14px;
	font-weight: 600;
	color: #1e40af;
	margin: 0 0 12px 0;
}

.autoship-ql-help-box ul {
	list-style: none;
	margin: 0;
	padding: 0;
}

.autoship-ql-help-box li {
	font-size: 14px;
	color: #1e40af;
	padding: 4px 0 4px 20px;
	position: relative;
}

.autoship-ql-help-box li::before {
	content: "";
	position: absolute;
	left: 0;
	top: 11px;
	width: 6px;
	height: 6px;
	background: #2563eb;
	border-radius: 50%;
}

/* Warning Box (for errors, expired, etc.) */
.autoship-ql-warning-box {
	background: #fef2f2;
	border: 1px solid #fecaca;
	border-radius: 12px;
	padding: 20px;
	margin-bottom: 28px;
	text-align: left;
}

.autoship-ql-warning-box h4 {
	font-size: 14px;
	font-weight: 600;
	color: #991b1b;
	margin: 0 0 12px 0;
}

.autoship-ql-warning-box ul {
	list-style: none;
	margin: 0;
	padding: 0;
}

.autoship-ql-warning-box li {
	font-size: 14px;
	color: #991b1b;
	padding: 4px 0 4px 20px;
	position: relative;
}

.autoship-ql-warning-box li::before {
	content: "";
	position: absolute;
	left: 0;
	top: 11px;
	width: 6px;
	height: 6px;
	background: #dc2626;
	border-radius: 50%;
}

/* Success Box */
.autoship-ql-success-box {
	background: #f0fdf4;
	border: 1px solid #bbf7d0;
	border-radius: 12px;
	padding: 20px;
	margin-bottom: 28px;
	text-align: left;
}

.autoship-ql-success-box h4 {
	font-size: 14px;
	font-weight: 600;
	color: #166534;
	margin: 0 0 12px 0;
}

.autoship-ql-success-box ul {
	list-style: none;
	margin: 0;
	padding: 0;
}

.autoship-ql-success-box li {
	font-size: 14px;
	color: #166534;
	padding: 4px 0 4px 20px;
	position: relative;
}

.autoship-ql-success-box li::before {
	content: "";
	position: absolute;
	left: 0;
	top: 11px;
	width: 6px;
	height: 6px;
	background: #16a34a;
	border-radius: 50%;
}

/* Form */
.autoship-ql-form {
	margin: 0;
}

/* Actions */
.autoship-ql-actions {
	display: flex;
	flex-direction: column;
	gap: 12px;
	margin-bottom: 24px;
}

@media (min-width: 400px) {
	.autoship-ql-actions {
		flex-direction: row;
		justify-content: center;
	}
}

/* Buttons */
.autoship-ql-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 14px 28px;
	font-size: 16px;
	font-weight: 600;
	text-decoration: none;
	border-radius: 8px;
	cursor: pointer;
	transition: all 0.2s ease;
	border: 2px solid transparent;
	min-width: 140px;
}

.autoship-ql-btn:focus {
	outline: none;
	box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
}

/* Primary Button */
.autoship-ql-btn-primary {
	background-color: #2563eb;
	color: #ffffff;
	border-color: #2563eb;
}

.autoship-ql-btn-primary:hover {
	background-color: #1d4ed8;
	border-color: #1d4ed8;
	color: #ffffff;
}

.autoship-ql-btn-primary:active {
	background-color: #1e40af;
	border-color: #1e40af;
}

/* Secondary Button */
.autoship-ql-btn-secondary {
	background-color: #ffffff;
	color: #4b5563;
	border-color: #d1d5db;
}

.autoship-ql-btn-secondary:hover {
	background-color: #f9fafb;
	border-color: #9ca3af;
	color: #374151;
}

.autoship-ql-btn-secondary:active {
	background-color: #f3f4f6;
}

/* Success Button */
.autoship-ql-btn-success {
	background-color: #16a34a;
	color: #ffffff;
	border-color: #16a34a;
}

.autoship-ql-btn-success:hover {
	background-color: #15803d;
	border-color: #15803d;
	color: #ffffff;
}

/* Help Link */
.autoship-ql-help-link {
	font-size: 13px;
	color: #6b7280;
	margin: 0 0 24px 0;
}

.autoship-ql-help-link a {
	color: #2563eb;
	text-decoration: underline;
}

.autoship-ql-help-link a:hover {
	color: #1d4ed8;
}

/* Powered By */
.autoship-ql-powered-by {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
	padding-top: 20px;
	border-top: 1px solid #e5e7eb;
	text-align: center;
}

.autoship-ql-powered-by-text {
	font-size: 12px;
	color: #9ca3af;
	display: block;
}

.autoship-ql-powered-by-logo {
	opacity: 0.7;
}

/* Status Badge */
.autoship-ql-status-badge {
	display: inline-flex;
	align-items: center;
	padding: 6px 14px;
	font-size: 13px;
	font-weight: 600;
	border-radius: 20px;
	margin-bottom: 20px;
}

.autoship-ql-status-badge-success {
	background: #dcfce7;
	color: #166534;
}

.autoship-ql-status-badge-error {
	background: #fee2e2;
	color: #991b1b;
}

.autoship-ql-status-badge-warning {
	background: #fef3c7;
	color: #92400e;
}

.autoship-ql-status-badge-info {
	background: #dbeafe;
	color: #1e40af;
}

/* Icon Colors */
.autoship-ql-icon-success svg circle {
	fill: #dcfce7;
	stroke: #16a34a;
}

.autoship-ql-icon-success svg path {
	stroke: #16a34a;
}

.autoship-ql-icon-error svg circle {
	fill: #fee2e2;
	stroke: #dc2626;
}

.autoship-ql-icon-error svg path {
	stroke: #dc2626;
}

.autoship-ql-icon-warning svg circle {
	fill: #fef3c7;
	stroke: #d97706;
}

.autoship-ql-icon-warning svg path {
	stroke: #d97706;
}

/* Countdown Timer (for rate limiting) */
.autoship-ql-countdown {
	font-size: 48px;
	font-weight: 700;
	color: #1f2937;
	margin: 20px 0;
}

.autoship-ql-countdown-label {
	font-size: 14px;
	color: #6b7280;
	margin-bottom: 28px;
}
</style>
