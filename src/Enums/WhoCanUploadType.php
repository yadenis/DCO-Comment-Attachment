<?php

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum WhoCanUploadType: string {

	case ALL_USERS    = 'all_users';
	case LOGGED_USERS = 'logged_users';
}
