<?php

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum WhoCanUploadType: string {

	case ALL_USERS         = 'all_users';
	case ONLY_LOGGED_USERS = 'only_logged_users';
}
