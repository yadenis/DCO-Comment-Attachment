<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class ManuallyModeration implements Setting {

	private const OPTION_NAME   = 'manually_moderation';
	private const DEFAULT_VALUE = false;

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): bool {

		$value = $this->options->get_bool_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_default_value();
		}

		return $value;
	}

	private function get_default_value(): bool {

		return self::DEFAULT_VALUE;
	}
}
