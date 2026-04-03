<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Nextime Service Interface.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime;

use Autoship\Services\Nextime\Interfaces\CarriersManagementInterface;
use Autoship\Services\Nextime\Interfaces\SitesManagementInterface;

interface NextimeServiceInterface extends CarriersManagementInterface, SitesManagementInterface {
}
