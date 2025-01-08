<?php

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum AttachmentEmbedType: string {

	case IMAGE = 'image';
	case VIDEO = 'video';
	case AUDIO = 'audio';
	case MISC  = 'misc';
}
