<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Settings\Enums\MaxUploadSizeFormat;
use DCO_CA\Settings\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class MaxUploadSize implements Setting {

	private const OPTION_NAME = 'max_upload_size';

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value( MaxUploadSizeFormat $format = MaxUploadSizeFormat::FROM_SETTINGS_IN_MEGABYTES ): int|string {

		$value = $this->options->get_int_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_default_value();
		}

		return match ( $format ) {
			MaxUploadSizeFormat::FROM_SYSTEM_IN_BYTES => wp_max_upload_size(),
			MaxUploadSizeFormat::FROM_SYSTEM_IN_MEGABYTES => wp_max_upload_size() / MB_IN_BYTES,
			MaxUploadSizeFormat::FROM_SETTINGS_IN_BYTES => $value * MB_IN_BYTES,
			MaxUploadSizeFormat::FROM_SETTINGS_IN_MEGABYTES => $value,
			MaxUploadSizeFormat::FROM_SETTINGS_FORMATTED => (string) size_format( $value * MB_IN_BYTES )
		};
	}

	private function get_default_value(): int {

		return $this->get_value( MaxUploadSizeFormat::FROM_SYSTEM_IN_MEGABYTES );
	}
}
