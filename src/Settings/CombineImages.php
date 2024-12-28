<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class CombineImages implements Setting {

	private const OPTION_NAME = 'combine_images';
	private const DEFAULT_VALUE = true;

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): bool {

		return $this->options->get_bool_option( self::OPTION_NAME ) ?? $this->get_default_value();
	}

	private function get_default_value(): bool {

		return self::DEFAULT_VALUE;
	}
}
