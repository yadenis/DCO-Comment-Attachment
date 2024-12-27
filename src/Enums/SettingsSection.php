<?php

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum SettingsSection: string {

	case GENERAL         = 'general';
	case IMAGES          = 'images';
	case MULTIPLE_UPLOAD = 'multiple_upload';
	case PERMISSIONS     = 'permissions';
	case IN_ADMIN        = 'in_admin';
}
