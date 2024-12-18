<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Settings\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class WhoCanUpload implements Setting {

	private const OPTION_NAME = 'who_can_upload';

	public function __construct(
		private Options $options,
	) {
	}

	// all_users, logged_users.
	public function get_value(): string {

		$value = $this->options->get_string_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_default_value();
		}

		return $value;
	}

	private function get_default_value(): string {

		return 'all_users';
	}
}
