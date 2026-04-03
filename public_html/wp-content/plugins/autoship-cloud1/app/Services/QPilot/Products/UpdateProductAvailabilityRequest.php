<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * UpdateProductAvailabilityRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Products;

/**
 * Request object for updating product availability.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class UpdateProductAvailabilityRequest {
	/**
	 * Whether the product can be added to scheduled orders.
	 *
	 * @var bool
	 */
	private bool $add_to_scheduled_order;

	/**
	 * Whether the product can be processed in scheduled orders.
	 *
	 * @var bool
	 */
	private bool $process_scheduled_order;

	/**
	 * Constructor.
	 *
	 * @param bool $add_to_scheduled_order Whether the product can be added to scheduled orders.
	 * @param bool $process_scheduled_order Whether the product can be processed in scheduled orders.
	 */
	public function __construct( bool $add_to_scheduled_order = false, bool $process_scheduled_order = false ) {
		$this->add_to_scheduled_order  = $add_to_scheduled_order;
		$this->process_scheduled_order = $process_scheduled_order;
	}

	/**
	 * Set whether the product can be added to scheduled orders.
	 *
	 * @param bool $add_to_scheduled_order Whether the product can be added to scheduled orders.
	 * @return self
	 */
	public function set_add_to_scheduled_order( bool $add_to_scheduled_order ): self {
		$this->add_to_scheduled_order = $add_to_scheduled_order;
		return $this;
	}

	/**
	 * Get whether the product can be added to scheduled orders.
	 *
	 * @return bool Whether the product can be added to scheduled orders.
	 */
	public function get_add_to_scheduled_order(): bool {
		return $this->add_to_scheduled_order;
	}

	/**
	 * Set whether the product can be processed in scheduled orders.
	 *
	 * @param bool $process_scheduled_order Whether the product can be processed in scheduled orders.
	 * @return self
	 */
	public function set_process_scheduled_order( bool $process_scheduled_order ): self {
		$this->process_scheduled_order = $process_scheduled_order;
		return $this;
	}

	/**
	 * Get whether the product can be processed in scheduled orders.
	 *
	 * @return bool Whether the product can be processed in scheduled orders.
	 */
	public function get_process_scheduled_order(): bool {
		return $this->process_scheduled_order;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		return array(
			'AddToScheduledOrder'   => $this->add_to_scheduled_order,
			'ProcessScheduledOrder' => $this->process_scheduled_order,
		);
	}
}
