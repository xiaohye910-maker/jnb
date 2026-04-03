<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Product Query Service for the Utilities module.
 *
 * @package Autoship
 * @since 2.12.1
 */

namespace Autoship\Modules\Utilities\Services;

use Autoship\Core\AutoshipSettingsInterface;
use Autoship\Domain\AutoshipProduct;
use Autoship\Repositories\ProductRepositoryInterface;

/**
 * Service for querying products eligible for frequency updates.
 *
 * Provides a single source of truth for both the template rendering
 * (product count) and the AJAX batch handler (product retrieval),
 * ensuring the count always matches the number of products processed.
 *
 * @package Autoship\Modules\Utilities\Services
 * @since 2.12.1
 */
class ProductQueryService {

	/**
	 * The product repository.
	 *
	 * @var ProductRepositoryInterface
	 */
	private ProductRepositoryInterface $repository;

	/**
	 * The Autoship settings.
	 *
	 * @var AutoshipSettingsInterface
	 */
	private AutoshipSettingsInterface $settings;

	/**
	 * Constructor.
	 *
	 * @param ProductRepositoryInterface $repository The product repository.
	 * @param AutoshipSettingsInterface  $settings   The Autoship settings.
	 */
	public function __construct( ProductRepositoryInterface $repository, AutoshipSettingsInterface $settings ) {
		$this->repository = $repository;
		$this->settings   = $settings;
	}

	/**
	 * Get all product IDs eligible for frequency updates.
	 *
	 * When global sync is disabled, only active products are returned.
	 * When enabled, all products are returned. This fixes the count
	 * mismatch bug where the template counted active products. But the
	 * handler queried all products.
	 *
	 * @return int[]
	 */
	public function get_eligible_product_ids(): array {
		$use_active = ! $this->settings->is_global_sync_active_enabled();

		if ( $use_active ) {
			$identifiers = array_merge(
				$this->repository->get_active_product_ids( 'simple' ),
				$this->repository->get_active_product_ids( 'variation' ),
				$this->repository->get_active_product_ids( 'variable' )
			);
		} else {
			$identifiers = array_merge(
				$this->repository->get_product_ids( 'simple' ),
				$this->repository->get_product_ids( 'variation' ),
				$this->repository->get_product_ids( 'variable' )
			);
		}

		sort( $identifiers );

		return $identifiers;
	}

	/**
	 * Get the count of products eligible for frequency updates.
	 *
	 * @return int
	 */
	public function get_frequency_updatable_product_count(): int {
		return count( $this->get_eligible_product_ids() );
	}

	/**
	 * Get a batch of products eligible for frequency updates.
	 *
	 * @param int $page       The page number (1-based).
	 * @param int $batch_size The number of products per batch.
	 *
	 * @return AutoshipProduct[]
	 */
	public function get_frequency_updatable_products( int $page, int $batch_size ): array {
		if ( $page < 1 || $batch_size < 1 ) {
			return array();
		}

		$identifiers = $this->get_eligible_product_ids();

		if ( empty( $identifiers ) ) {
			return array();
		}

		$chunks = array_chunk( $identifiers, $batch_size );
		$chunk  = $chunks[ $page - 1 ] ?? array();

		if ( empty( $chunk ) ) {
			return array();
		}

		$wc_products = array_filter( array_map( 'wc_get_product', $chunk ) );

		return array_map(
			function ( $wc_product ) {
				return new AutoshipProduct( $wc_product );
			},
			$wc_products
		);
	}

	/**
	 * Get the product repository.
	 *
	 * @return ProductRepositoryInterface
	 */
	public function get_repository(): ProductRepositoryInterface {
		return $this->repository;
	}
}
