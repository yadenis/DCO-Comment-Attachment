<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Enums\WhoCanUploadType;
use DCO_CA\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class WhoCanUploadSetting implements Setting {

	private const OPTION_NAME   = 'who_can_upload';
	private const DEFAULT_VALUE = WhoCanUploadType::ALL_USERS;

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): WhoCanUploadType {

		return $this->options->get_string_option( self::OPTION_NAME ) ?? $this->get_default_value();
	}

	private function get_default_value(): WhoCanUploadType {

		return self::DEFAULT_VALUE;
	}
}
