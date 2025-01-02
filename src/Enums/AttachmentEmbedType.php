<?php

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum AttachmentEmbedType {

	case IMAGE;
	case VIDEO;
	case AUDIO;
	case MISC;
}
