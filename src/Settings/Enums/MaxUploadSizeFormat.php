<?php

declare(strict_types=1);

namespace DCO_CA\Settings\Enums;

defined( 'ABSPATH' ) || die;

enum MaxUploadSizeFormat {

	case FROM_SYSTEM_IN_BYTES;
	case FROM_SYSTEM_IN_MEGABYTES;
	case FROM_SETTINGS_IN_BYTES;
	case FROM_SETTINGS_IN_MEGABYTES;
	case FROM_SETTINGS_FORMATTED;
}
