<?php

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum MaxUploadSizeFormat {

	case IN_BYTES;
	case IN_MEGABYTES;
	case FORMATTED;
}
