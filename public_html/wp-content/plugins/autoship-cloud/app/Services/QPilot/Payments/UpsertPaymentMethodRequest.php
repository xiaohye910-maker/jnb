<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Upsert payment method request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Payments;

/**
 * Request object for creating or updating a payment method.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class UpsertPaymentMethodRequest extends CreatePaymentMethodRequest {
	/**
	 * The payment method ID.
	 *
	 * @var int|null
	 */
	private ?int $id = null;

	/**
	 * Whether to apply this payment method to all scheduled orders.
	 *
	 * @var bool|null
	 */
	private ?bool $apply_to_scheduled_orders = null;

	/**
	 * Set the payment method ID.
	 *
	 * @param int $id The payment method ID.
	 *
	 * @return self
	 */
	public function set_id( int $id ): self {
		$this->id = $id;

		return $this;
	}

	/**
	 * Set whether to apply this payment method to all scheduled orders.
	 *
	 * @param bool $apply_to_scheduled_orders Whether to apply this payment method to all scheduled orders.
	 *
	 * @return self
	 */
	public function set_apply_to_scheduled_orders( bool $apply_to_scheduled_orders ): self {
		$this->apply_to_scheduled_orders = $apply_to_scheduled_orders;

		return $this;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = parent::to_array();

		if ( null !== $this->id ) {
			$data['id'] = $this->id;
		}

		if ( null !== $this->apply_to_scheduled_orders ) {
			$data['applyToScheduledOrders'] = $this->apply_to_scheduled_orders;
		}

		return $data;
	}
}
