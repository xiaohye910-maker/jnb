<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Scanner Patterns for Email Scanner Detection.
 *
 * Contains regex patterns to detect known email security scanners
 * and allow-list patterns for legitimate browsers.
 *
 * @package Autoship\Services\QuickLinks\EmailScanner
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\EmailScanner;

/**
 * Scanner Patterns.
 *
 * Static class containing all scanner detection patterns.
 * Patterns are used to identify email security scanners that
 * automatically prefetch links in emails.
 */
class ScannerPatterns {

	/**
	 * Get scanner User-Agent patterns.
	 *
	 * These patterns match known email security scanners
	 * that automatically fetch links to check for phishing/malware.
	 *
	 * @return array Array of regex patterns.
	 */
	public static function get_scanner_patterns(): array {
		return array(
			// Microsoft/Outlook SafeLinks.
			'/SafeLinks/i',
			'/Microsoft Office Protocol Discovery/i',
			'/Microsoft-CryptoAPI/i',
			'/Outlook-iOS/i',
			'/Outlook-Android/i',
			'/Microsoft Outlook/i',

			// Google.
			'/GoogleImageProxy/i',
			'/Google-Safety/i',
			'/Google Web Preview/i',
			'/Google-Read-Aloud/i',

			// Proofpoint.
			'/Proofpoint/i',
			'/PFWL/i',

			// Mimecast.
			'/Mimecast/i',

			// Barracuda.
			'/Barracuda/i',
			'/BESS/i',

			// Cisco/IronPort.
			'/Cisco IronPort/i',
			'/IronPort/i',

			// Symantec/Broadcom.
			'/Symantec/i',
			'/MessageLabs/i',

			// Trend Micro.
			'/TMUFE/i',
			'/Trend Micro/i',

			// FireEye.
			'/FireEye/i',

			// Sophos.
			'/Sophos/i',

			// Forcepoint/Websense.
			'/Websense/i',
			'/Forcepoint/i',

			// McAfee.
			'/McAfee/i',

			// Cloudmark.
			'/Cloudmark/i',

			// SpamTitan.
			'/SpamTitan/i',

			// Zscaler.
			'/Zscaler/i',

			// Fortinet.
			'/FortiGate/i',
			'/Fortinet/i',

			// Generic scanner/bot patterns.
			'/URLScan/i',
			'/LinkChecker/i',
			'/url_verifier/i',
			'/link_preview/i',
			'/HeadlessChrome/i',
			'/PhantomJS/i',
			'/Headless/i',
			'/bot/i',
			'/spider/i',
			'/crawler/i',
			'/wget/i',
			'/curl/i',
		);
	}

	/**
	 * Get allow-list patterns.
	 *
	 * These patterns match legitimate browser User-Agents
	 * that should never be blocked, even if they trigger
	 * other detection heuristics.
	 *
	 * @return array Array of regex patterns.
	 */
	public static function get_allow_list(): array {
		return array(
			// Real Chrome browser (not HeadlessChrome).
			// Uses negative lookbehind to ensure it's not HeadlessChrome.
			'/^Mozilla\/5\.0(?!.*HeadlessChrome).*Chrome\/\d+/i',

			// Real Firefox browser.
			'/^Mozilla\/5\.0.*Firefox\/\d+/i',

			// Real Safari browser (macOS/iOS).
			// Safari includes Safari in UA but NOT Chrome (Chrome browsers include both).
			'/^Mozilla\/5\.0(?!.*Chrome).*Safari\/\d+/i',

			// Real Edge browser.
			'/^Mozilla\/5\.0.*Edg\/\d+/i',

			// Real Internet Explorer.
			'/^Mozilla\/5\.0.*Trident/i',

			// Real Opera browser.
			'/^Mozilla\/5\.0.*OPR\/\d+/i',
		);
	}
}
