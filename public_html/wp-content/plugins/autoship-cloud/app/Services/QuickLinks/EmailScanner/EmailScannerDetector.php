<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Email Scanner Detector Service.
 *
 * H-2: Detects and blocks email security scanners from auto-triggering
 * financial actions (Process Now).
 *
 * Email scanners automatically fetch links in emails to check for phishing/malware.
 * This can cause QuickLinks to execute before the customer opens the email,
 * resulting in unauthorized charges.
 *
 * This service:
 * - Detects known email scanners via User-Agent patterns
 * - Uses behavioral analysis to detect unknown scanners
 * - Only blocks financial actions (Process Now)
 * - Allows low-risk actions (Resume, Pause) for link validation
 *
 * @package Autoship\Services\QuickLinks\EmailScanner
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\EmailScanner;

/**
 * Email Scanner Detector.
 *
 * Detects email security scanners using multiple detection layers:
 * - Layer 1: Allow-list (never block real browsers)
 * - Layer 2: User-Agent pattern matching
 * - Layer 3: Behavioral analysis
 */
class EmailScannerDetector {

	/**
	 * Whether detection is enabled.
	 *
	 * @var bool
	 */
	private bool $enabled;

	/**
	 * Action types to block for scanners.
	 *
	 * @var array
	 */
	private array $block_actions;

	/**
	 * Behavioral detection configuration.
	 *
	 * @var array
	 */
	private array $behavioral_config;

	/**
	 * Constructor.
	 *
	 * @param array $config Configuration array.
	 */
	public function __construct( array $config = array() ) {
		$this->enabled       = $config['enabled'] ?? true;
		$this->block_actions = $config['block_actions'] ?? array( 2 ); // Process Now.

		$this->behavioral_config = $config['behavioral_detection'] ?? array(
			'enabled'                 => true,
			'require_accept_language' => true,
			'require_html_accept'     => true,
			'min_suspicious_count'    => 2,
		);
	}

	/**
	 * Check if request appears to be from an email scanner.
	 *
	 * Uses multiple detection layers:
	 * 1. Allow-list check (never block real browsers)
	 * 2. Known scanner pattern matching
	 * 3. Behavioral analysis
	 *
	 * @param string|null $user_agent The User-Agent header.
	 * @param array       $headers    Request headers.
	 *
	 * @return bool True if scanner detected.
	 */
	public function is_scanner( ?string $user_agent, array $headers = array() ): bool {
		if ( ! $this->enabled ) {
			return false;
		}

		// Layer 1: Allow-list check (never block real browsers).
		if ( $this->is_allow_listed( $user_agent ) ) {
			return false;
		}

		// Layer 2: Known scanner pattern matching.
		if ( $this->matches_known_scanner( $user_agent ) ) {
			return true;
		}

		// Layer 3: Behavioral analysis.
		if ( $this->has_scanner_like_behavior( $headers ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if action type should be blocked for scanners.
	 *
	 * Only financial actions (Process Now) are blocked.
	 * Low-risk actions (Resume, Pause) are allowed for link validation.
	 *
	 * @param int|null $action_type The action type.
	 *
	 * @return bool True if action should be blocked.
	 */
	public function should_block_action( ?int $action_type ): bool {
		if ( null === $action_type ) {
			return false;
		}

		return in_array( $action_type, $this->block_actions, true );
	}

	/**
	 * Check if User-Agent is in the allow-list.
	 *
	 * Allow-listed User-Agents are never blocked, even if they
	 * trigger other detection heuristics.
	 *
	 * @param string|null $user_agent The User-Agent header.
	 *
	 * @return bool True if allow-listed.
	 */
	private function is_allow_listed( ?string $user_agent ): bool {
		if ( empty( $user_agent ) ) {
			return false;
		}

		foreach ( ScannerPatterns::get_allow_list() as $pattern ) {
			if ( preg_match( $pattern, $user_agent ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if User-Agent matches known scanner patterns.
	 *
	 * @param string|null $user_agent The User-Agent header.
	 *
	 * @return bool True if matches known scanner.
	 */
	private function matches_known_scanner( ?string $user_agent ): bool {
		if ( empty( $user_agent ) ) {
			return false;
		}

		foreach ( ScannerPatterns::get_scanner_patterns() as $pattern ) {
			if ( preg_match( $pattern, $user_agent ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Detect scanner-like behavior using request heuristics.
	 *
	 * Analyzes request headers and characteristics that are
	 * uncommon for real browsers but common for bots/scanners.
	 *
	 * @param array $headers Request headers.
	 *
	 * @return bool True if scanner-like behavior detected.
	 */
	private function has_scanner_like_behavior( array $headers ): bool {
		if ( ! ( $this->behavioral_config['enabled'] ?? true ) ) {
			return false;
		}

		$suspicious_count = 0;

		// Check 1: Missing Accept-Language header.
		// Real browsers always send this, bots often don't.
		if ( $this->behavioral_config['require_accept_language'] ?? true ) {
			if ( empty( $headers['accept_language'] ) ) {
				++$suspicious_count;
			}
		}

		// Check 2: Accept header doesn't include text/html.
		// Real browsers accept text/html, scanners may not.
		if ( $this->behavioral_config['require_html_accept'] ?? true ) {
			$accept = $headers['accept'] ?? '';
			if ( ! empty( $accept ) && false === strpos( $accept, 'text/html' ) ) {
				++$suspicious_count;
			}
		}

		// Check 3: Missing Referer header.
		// Links clicked from emails often have no referer,
		// but this is suspicious when combined with other factors.
		if ( empty( $headers['referer'] ) ) {
			++$suspicious_count;
		}

		// Require at least N suspicious indicators.
		$min_count = $this->behavioral_config['min_suspicious_count'] ?? 2;

		return $suspicious_count >= $min_count;
	}

	/**
	 * Check if scanner detection is enabled.
	 *
	 * @return bool True if enabled.
	 */
	public function is_enabled(): bool {
		return $this->enabled;
	}

	/**
	 * Get blocked action types.
	 *
	 * @return array Array of action type IDs.
	 */
	public function get_block_actions(): array {
		return $this->block_actions;
	}
}
