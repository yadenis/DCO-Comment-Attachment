<?php
/**
 * Enums: Allowed File Types Format
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

enum AllowedFileTypesFormat {

	case ARRAY;
	case GROUPED_BY_TYPE;
}
