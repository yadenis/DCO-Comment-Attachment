<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class EnableMultipleUpload implements Setting {

	private const OPTION_NAME   = 'autoembed_links';
	private const DEFAULT_VALUE = false;

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
