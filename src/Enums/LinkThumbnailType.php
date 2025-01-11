<?php
/**
 * Enums: Link Thumbnail Type
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum LinkThumbnailType: string {

	case NO_LINK         = 'no_link';
	case IMAGE_LIGHTBOX  = 'image_lightbox';
	case IMAGE_NEW_TAB   = 'image_new_tab';
	case ATTACHMENT_PAGE = 'attachment_page';
}
