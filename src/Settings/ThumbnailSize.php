<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Settings\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class ThumbnailSize implements Setting {

	private const OPTION_NAME = 'thumbnail_size';

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): string {

		$value = $this->options->get_string_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_default_value();
		}

		return $value;
	}

	private function get_default_value(): string {

		return 'medium';
	}
}
