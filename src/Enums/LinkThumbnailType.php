<?php

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum LinkThumbnailType: string {

	case NO_LINK         = 'no_link';
	case IMAGE_LIGHTBOX  = 'image_lightbox';
	case IMAGE_NEW_TAB   = 'image_new_tab';
	case ATTACHMENT_PAGE = 'attachment_page';
}
