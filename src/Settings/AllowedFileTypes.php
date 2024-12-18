<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Settings\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class AllowedFileTypes implements Setting {

	private const OPTION_NAME = 'allowed_file_types';

	private array $extensions;

	public function __construct(
		private Options $options,
	) {

		$this->extensions = $this->get_extensions();
	}

	public function get_value(): array {

		$value = $this->options->get_array_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_default_value();
		}

		return $value;
	}

	private function get_default_value(): array {

		return $this->get_extensions();
	}

	private function get_extensions(): array {

		if ( isset( $this->extensions ) ) {
			return $this->extensions;
		}

		$raw_extensions = array_keys( get_allowed_mime_types() );
		$extensions     = [];

		foreach ( $raw_extensions as $extension ) {

			$exts = explode( '|', $extension );

			foreach ( $exts as $ext ) {
				$extensions[] = $ext;
			}
		}

		return $extensions;
	}
}
