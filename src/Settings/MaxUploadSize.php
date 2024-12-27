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

	public function get_value( MaxUploadSizeFormat $format = MaxUploadSizeFormat::IN_MEGABYTES ): int|string {

		$value = $this->options->get_int_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_system_value( $format );
		}

		return match ( $format ) {
			MaxUploadSizeFormat::IN_BYTES => $value * MB_IN_BYTES,
			MaxUploadSizeFormat::IN_MEGABYTES => $value,
			MaxUploadSizeFormat::FORMATTED => (string) size_format( $value )
		};
	}

	public function get_system_value( MaxUploadSizeFormat $format = MaxUploadSizeFormat::IN_MEGABYTES ): int|string {

		$value = wp_max_upload_size();

		return match ( $format ) {
			MaxUploadSizeFormat::IN_BYTES => $value,
			MaxUploadSizeFormat::IN_MEGABYTES => $value / MB_IN_BYTES,
			MaxUploadSizeFormat::FORMATTED => (string) size_format( $value ),
		};
	}
}
