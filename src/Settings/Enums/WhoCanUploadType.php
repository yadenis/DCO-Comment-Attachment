<?php

declare(strict_types=1);

namespace DCO_CA\Settings\Enums;

defined( 'ABSPATH' ) || die;

enum WhoCanUploadType {

	case ALL_USERS;
	case LOGGED_USERS;
}
