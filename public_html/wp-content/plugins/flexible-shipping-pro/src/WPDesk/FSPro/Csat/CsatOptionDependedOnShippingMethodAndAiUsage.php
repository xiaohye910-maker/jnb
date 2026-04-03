<?php

namespace WPDesk\FSPro\Csat;

use FSProVendor\Octolize\Csat\CsatOptionDependedOnShippingMethod;

class CsatOptionDependedOnShippingMethodAndAiUsage extends CsatOptionDependedOnShippingMethod {

	/**
	 * @param array $settings
	 *
	 * @return array
	 */
	public function update_settings( $settings ) {
		if ( is_array( $settings ) && isset( $settings['used_ai_chat'], $settings['used_rules_from_ai'] ) ) {
			$this->increase();
		}

		return $settings;
	}
}
