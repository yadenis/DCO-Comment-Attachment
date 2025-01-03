<?php

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum DeleteAttachmentActionType: string {

	case DELETE = 'delete';
	case DETACH = 'detach';
}
