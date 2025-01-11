<?php
/**
 * Enums: Delete Attachment Action Type
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

enum DeleteAttachmentActionType: string {

	case DELETE = 'delete';
	case DETACH = 'detach';
}
