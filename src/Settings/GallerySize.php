<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class GallerySize implements Setting {

	private const OPTION_NAME   = 'gallery_size';
	private const DEFAULT_VALUE = 'thumbnail';

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): string {

		return $this->options->get_string_option( self::OPTION_NAME ) ?? $this->get_default_value();
	}

	private function get_default_value(): string {

		return self::DEFAULT_VALUE;
	}
}
